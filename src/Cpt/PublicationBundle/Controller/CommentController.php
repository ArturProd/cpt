<?php
namespace Cpt\PublicationBundle\Controller;

use Cpt\PublicationBundle\Controller\BaseController as BaseController;
use Cpt\PublicationBundle\Interfaces\Entity\CommentInterface as CommentInterface;
use Cpt\PublicationBundle\Interfaces\Entity\PublicationInterface as PublicationInterface;

use Symfony\Component\HttpFoundation\Request;




class CommentController extends BaseController
{
    // <editor-fold defaultstate="collapsed" desc="Comment actions">

    
        /**
     * @param integer $postId
     *
     * @return Response
     */
    public function commentsAction(PublicationInterface $publication)
    {
       $publication = $this->getPostManager()->getOneById($publication->getId());
       
       $this->RestrictResourceNotFound($publication);
       
       $author = $this->getUser();

       $comment_form = $this->getCommentForm($publication, $author)->createView();
       
        return $this->RenderCommentsView($publication, $comment_form);
    }
   
    public function getNewCommentsForPostAction()
    {
         $this->RestrictAccessToAjax();

        $publicationid = $this->GetNumericParameter('publicationid'); 
        $aftercommentid = $this->GetNumericParameter('aftercommentid', -1);  
        
        $comments = $this->getCommentManager()->get_newer_comments($publicationid, $aftercommentid);
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
        $publicationid = $this->GetNumericParameter('publicationid'); 
        $beforeid = $this->GetNumericParameter('beforeid', -1);  // To only get comments after a given comment id
        $howmany = $this->GetNumericParameter('howmany'); 
        
        $comments = $this->getCommentManager()->get_older_comments($publicationid, $howmany, $beforeid );
        
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
    public function getViewAllAction($publicationid){       
        $commentmanager = $this->getCommentManager();
        $pager = $commentmanager
            ->getPager(array(
                'postId' => $publicationid,
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

       $publication = $this->getPublicationManager()->getOneById($publicationid);
       $this->RestrictResourceNotFound($publication);

       $comment_form = $this->getCommentForm($publication);
       
       $html_string = RenderCommentsView($publication, $comment_form);
  
       return $this->CreateJsonResponse($html_string);
    }

    /**
     * @throws NotFoundHttpException
     *
     * @param string $id
     *
     * @return Response
     */
    public function addCommentAction(Request $request, $publicationid)
    {                
            // Get the post from request
            //$post_id = $this->get('request')->request->get('post_id');
            $publication = $this->getPublicationManager()->getOneById($publicationid);

            $this->RestrictResourceNotFound($publication);

            // Make sure current user is allowed to comment the post
            $user = $this->getUser();
            $securitycontrext = $this->get('security.context');
            $authcomment = $this->getPublicationManager()->CanCommentPublication($publication, $user, $securitycontrext);
            $this->RestrictAccessDenied($authcomment);

           $form = $this->getCommentForm($publication);
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
    protected function getCommentForm($publication,$author)
    {
        $comment = $this->getCommentManager()->create($publication,$author);
        //return $this->get('form.factory')->createNamed('comment', 'cpt.form.comment', $comment);
        return $this->createForm('comment', $comment);
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
    
    protected function RenderCommentsView(PublicationInterface $publication, $commentform)
    {
        return $this->render('CptPublicationBundle:Comment:comments.html.twig', array(
            'publication'=> $publication,
            'commentform' => $commentform
        ));
    }
}
?>
