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
    public function getCommentsContainerAction(PublicationInterface $publication)
    {
       $this->RestrictResourceNotFound($publication);
              
       return $this->RenderCommentsView($publication);
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
            
        return $this->CreateJsonOkResponse($view_comments);
        
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
        if ($beforeid == -1) // If we are retreiving the most recent comments, we use the initial number of comments to be retreived
            $howmany = $this->container->getParameter('cpt.publication.comment.initialnbretreive');
        else
            $howmany = $this->container->getParameter('cpt.publication.comment.nbretreive');
        
        $comments = $this->getCommentManager()->get_older_comments($publicationid, $howmany, $beforeid );
        
        $view_comments = Array();
        foreach($comments  as $comment)
        {
            $this->SetCanModify($comment, $user);
            $view_comments[] = $comment->toViewArray();
        }
            
        return $this->CreateJsonOkResponse($view_comments);
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

           $form = $this->getCommentForm($publication, $user);
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

                return $this->CreateJsonOkResponse($comment->toViewArray());
            }

            return new Response($html_string,500);
    }

    public function deleteCommentAction($commentid)
    {
        $comment = $this->getCommentManager()->findOneBy(array(
            'id' => $commentid
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
        if ($comment->getPublication()->getAuthor()->getId() == $user->getId())
            return true;
        
        // Owner of a comment can delete its own comment
        if ($comment->getAuthor()->getId() == $user->getId())
            return true;
        
        
        return false;
    }
    
    protected function RenderCommentsView(PublicationInterface $publication)
    {
        $commentformview = null;
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
        {
            $comment = $this->getCommentManager()->create($publication,$this->getUser());
            $commentformview = $this->createForm('comment', $comment)->createView();
        }
        
        return $this->render('CptPublicationBundle:Comment:comments.html.twig', array(
            'publication'=> $publication,
            'commentform' => $commentformview
        ));
    }
}
?>
