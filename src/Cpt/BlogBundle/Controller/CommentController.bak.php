<?php
namespace Cpt\BlogBundle\Controller;

use Cpt\BlogBundle\Controller\BasePostController as BaseController;
use Cpt\BlogBundle\Interfaces\Entity\CommentInterface as CommentInterface;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\Request;




class CommentController extends BaseController
{
    // <editor-fold defaultstate="collapsed" desc="Comment actions">

    
        /**
     * @param integer $postId
     *
     * @return Response
     */
    public function commentsAction($postId)
    {
       $post = $this->getPostById($postId);
       
       $this->RestrictResourceNotFound($post);
       
       $comment_form = $this->getCommentForm($post)->createView();
       
        return $this->render('CptBlogBundle:Post:comments.html.twig', array(
            'post_id'=> $postId,
            'post' => $post,
            'commentform' => $comment_form
        ));
    }
   
    public function getNewCommentsForPostAction()
    {
         $this->RestrictAccessToAjax();

        $postid = $this->GetNumericParameter('postid'); 
        $aftercommentid = $this->GetNumericParameter('aftercommentid', -1);  
        
        $comments = $this->getCommentManager()->get_newer_comments($postid, $aftercommentid);
        $user = $this->getUser();

        $view_comments = Array();
        foreach($comments  as $comment)
        {
            $this->SetCanModify($comment, $user);
            $view_comments[] = $comment->toViewArray();
        }
            
        return $this->CreateJsonResponse($view_comments);
        
    }
            
    /**
     * Returns JSON array of comments
     */
    public function getCommentsForPostAction()
    {
        $this->RestrictAccessToAjax();
     
        $user = $this->getUser();
        $postid = $this->GetNumericParameter('postid'); 
        $beforeid = $this->GetNumericParameter('beforeid', -1);  // To only get comments after a given comment id
        $howmany = $this->GetNumericParameter('howmany'); 
        
        $comments = $this->getCommentManager()->get_older_comments($postid, $howmany, $beforeid );
        
        $view_comments = Array();
        foreach($comments  as $comment)
        {
            $this->SetCanModify($comment, $user);
            $view_comments[] = $comment->toViewArray();
        }
            
        return $this->CreateJsonResponse($view_comments);
    }
 
    
    /**
    * Return a ajax response as html content
    */
    public function commentsGetPlainAction($postId){       
        $commentmanager = $this->getCommentManager();
        $pager = $commentmanager
            ->getPager(array(
                'postId' => $postId,
                'status'  => CommentInterface::STATUS_VALID
            ), 1, 500); //no limit

       
       $user = $this->getUser();
               
       // Toggle the tag saying if the comment can be modified (for GUI)
       foreach($pager->getResults() as $comment)
       {
           $comment->setCanModify($this->CanModifyComment($comment, $user));            
       }
       
       //if (!$this->CanCommentPost($post, $user)) 
       //         throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("You do not have the permission to add a comment");


       $comment_form = $this->getCommentForm($post);
       
       $html_string = $this->renderView('CptBlogBundle:Post:comments.html.twig', array(
            'pager'  => $pager,
            'post_id'=> $postId,
            'comment_form' => $comment_form
        ));
  
       return $this->CreateJsonResponse($html_string);
    }

    /**
     * @param $postId
     * @param bool $form
     *
     * @return Response
     */
 /*   public function addCommentFormAction($postId, $form = false)
    {
        if (!$form) {
            $post = $this->getPostManager()->findOneBy(array(
                'id' => $postId
            ));

            $form = $this->getCommentForm($post);
        }

        return $this->render('CptBlogBundle:Post:comment_form.html.twig', array(
            'form'      => $form->createView(),
            'post_id'   => $postId
        ));
    }*/

    /**
     * @throws NotFoundHttpException
     *
     * @param string $id
     *
     * @return Response
     */
    public function addCommentAction(Request $request, $postid)
    {                
            // Get the post from request
            //$post_id = $this->get('request')->request->get('post_id');
            $post = $this->getPostById($postid);

            $this->RestrictResourceNotFound($post);

            // Make sure current user is allowed to comment the post
            $user = $this->getUser();            
            $this->RestrictAccessDenied($this->CanCommentPost($post, $user));

           $form = $this->getCommentForm($post);
           $form->bind($request); 

            if ($form->isValid()) {

                $comment = $form->getData();
                if ($comment->getMessage() === NULL)
                     return new Response('Cannot add an empty comment',500);
                
                $comment->setAuthor($this->getUser());
                $comment->setMessage(nl2br(htmlspecialchars($comment->getMessage()),false));

                //$this->get('cpt.blog.mailer')->sendCommentNotification($comment);
                $comment->setCanModify($this->CanModifyComment($comment, $user));
                 $this->getCommentManager()->save($comment);

                return $this->CreateJsonResponse($comment->toViewArray());
            }

            return new Response($html_string,500);
    }

    public function deleteCommentAction(Request $request)
    {
        $comment_id = $this->get('request')->request->get('id');

        $comment = $this->getCommentManager()->findOneBy(array(
            'id' => $comment_id
        ));

        
        $this->RestrictResourceNotFound($comment);        
        
        $user = $this->getUser();
        
        if (!$this->CanModifyComment($comment, $user)) 
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("You do not have the permission to delete a comment");

        
        $this->getCommentManager()->delete($comment);
          
        return new Response("OK",200);    
    }

     /**
     * @param string $commentId
     * @param string $hash
     * @param string $status
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function commentModerationAction($commentId, $hash, $status)
    {
        $comment = $this->getCommentManager()->findOneBy(array('id' => $commentId));

        if (!$comment) {
            throw new AccessDeniedException();
        }

        $computedHash = $this->get('cpt.blog.hash.generator')->generate($comment);

        if ($computedHash != $hash) {
            throw new AccessDeniedException();
        }

        $comment->setStatus($status);

        $this->getCommentManager()->save($comment);

        return new RedirectResponse($this->generateUrl('sonata_news_view', array(
            'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($comment->getPost())
        )));
    }
    
     /**
     * @param $post
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function getCommentForm($post)
    {
        $comment = $this->getCommentManager()->create();
        $comment->setPost($post);
        $comment->setStatus($post->getCommentsDefaultStatus());
        return $this->get('form.factory')->createNamed('comment', 'sonata_post_comment', $comment);
    }
    
    // </editor-fold>
    
    protected function SetCanModify($comment,$user)
    {
       $comment->setCanModify($this->CanModifyComment($comment, $user));
    }
        /**
     * Returns true if a user can modify (delete..) a comment
     * 
     * @param type $comment
     * @param type $user
     * @return boolean
     */
    protected function CanModifyComment($comment, $user)
    {
        if (!$comment)
            return false;
        
        if (!$user)
            return false;

        // Anonymous users can never modify a comment
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED', $user))
            return false;
        
        // Admin can modify any comment
        if ($this->get('security.context')->isGranted('ROLE_ADMIN', $user))
            return true;
        
        // Comment without author.. should not happen
        if (!$comment || (!($comment->getAuthor())))
            return false;
               
        // The owner of a post can modify any comment of this post
        if ($comment->getPost()->getAuthor()->getId() == $user->getId())
            return true;
        
        // Owner of a comment can delete its own comment
        if ($comment->getAuthor()->getId() == $user->getId())
            return true;
        
        
        return false;
    }
}
?>
