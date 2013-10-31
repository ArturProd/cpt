<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cpt\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\Mapping as ORM;

use Cpt\BlogBundle\Model\CommentInterface;
use Cpt\BlogBundle\Model\PostInterface;
use Cpt\BlogBundle\Entity;
use Cpt\BlogBundle\Form\Type;

use Cpt\MainBundle\Controller\BaseController as BaseController;



class PostController extends BaseController
{
    /**
     * @return RedirectResponse
     */
    public function homeAction()
    {
        return $this->RedirectHome();
    }
    
    
    /**
     * Shows the main article page
     */
    public function viewAllAction()
    {
        $response = $this->render('CptBlogBundle:Post:articles_viewall.html.twig');
       
        return $response;
    }
    
    public function getArticleListAction($page=1, $enableonly=true, $foruserid = null)
    {        
        // Only ajax requests
        $this->RestrictAccessToAjax();
        
        // Only admin user can see all posts including not enabled
        if ($this->isUserAdmin())
            $criteria['enabled'] = $enableonly ? true : 'all';    
        else
            $criteria['enabled'] = true;
        
        // Filter by user (author) if required
        if ($foruserid)
            $criteria['author'] = $foruserid;
                
        $pager = $this->getPostManager()->getPager(
            $criteria,
            $page
        ); 
        
        $pageresult = $pager->getResults();
        $idarray = array();
        foreach($pageresult as $post)
            $idarray[] = $post->getId();
            //$postarray[$post->getId()] = $post->toViewArray();
        
        return $this->CreateJsonResponse($idarray);
    }
    
    public function listPostsAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException("You do not have the authorization to access this page");
        }
        
       $user= $this->get('security.context')->getToken()->getUser();
        
       $criteria = array();
       
       // Admin can see all posts
       if (!$this->get('security.context')->isGranted('ROLE_ADMIN', $user))
           $criteria['author'] = $user->getId();

       $criteria['enabled'] = 'all';
       
       $pager = $this->getPostManager()->getPager(
            $criteria,
            $this->getRequest()->get('page', 1, 100)
        );
       
        $parameters['pager'] = $pager;
        $parameters['route'] = $this->getRequest()->get('_route');
        $parameters['route_parameters'] = $this->getRequest()->get('_route_params') ;
       

        $response = $this->render(sprintf('CptBlogBundle:Post:list.html.twig', $this->getRequest()->getRequestFormat()), $parameters);

        if ('rss' === $this->getRequest()->getRequestFormat()) {
            $response->headers->set('Content-Type', 'application/rss+xml');
        }

        return $response;
    }
    
    // <editor-fold defaultstate="collapsed" desc="Single post actions ">
 
    
         /**
        * Return a ajax response as html content
        */
        public function postGetJsonViewAction(){
            // Only ajax requests
            $this->RestrictAccessToAjax();
        
            $id = $this->getRequest()->get('id');   
            if (!is_numeric($id))
                $this->RestrictResourceNotFound($id);
            
            $post = $this->getPostManager()->findOneBy(array('id' => $id));

            if (!$post || !$post->isPublic())
                $this->RestrictResourceNotFound($id);

            $this->SetPermissions($post);
            
            $html_string = $this->renderView('CptBlogBundle:Post:preview_post.html.twig', array(
                'post'  => $post,
            ));

           //return new Response($html_string,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type

           return $this->CreateJsonResponse($html_string);
        }
    
    /**
     * @throws NotFoundHttpException
     *
     * @param $permalink
     *
     * @return Response
     */
    public function viewAction($permalink)
    {
        $post = $this->getPostManager()->findOneByPermalink($permalink, $this->container->get('cpt.blog.blog'));

        if (!$post || !$post->isPublic()) {
            throw new NotFoundHttpException('Unable to find the post');
        }

       
        $this->SetPermissions($post);

        
        if ($seoPage = $this->getSeoPage()) {
            $seoPage
                ->setTitle($post->getTitle())
                ->addMeta('property', 'og:title', $post->getTitle())
                ->addMeta('property', 'og:type', 'blog')
                ->addMeta('property', 'og:url',  $this->generateUrl('sonata_news_view', array(
                    'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true)
                ), true))
            ;
        }

        $this->SetPermissions($post);
                    
        return $this->render('CptBlogBundle:Post:view.html.twig', array(
            'post' => $post,
            'form' => false,
            'blog' => $this->get('cpt.blog.blog'),
        ));
    }
    
    public function editPostAction(Request $request, $id = null)
    {
        // Only publisher can edit a post
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException("You do not have the authorization to publish an article.");
        }
        
        $user= $this->get('security.context')->getToken()->getUser();
                   
        $post = null;
        
        if ((null == $id)||(-1 == $id))
        {
            // Create a new post case
            $post = $this->getPostManager()->create();

            $post->setAuthor($user);
        }
        else
        {
            // Retreive an existing post case
            $post = $this->getPostById($id);
            if (!$post)
                 return $this->RedirectHome();
        }

        $this->SetPermissions($post);

        $form = $this->getPostEditForm($post);

        if ($seoPage = $this->getSeoPage()) {
            $seoPage
                ->setTitle($post->getTitle())
                ->addMeta('property', 'og:title', $post->getTitle())
                ->addMeta('property', 'og:type', 'blog')
                ->addMeta('property', 'og:url',  $this->generateUrl('sonata_news_view', array(
                    'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post, true)
                ), true))
            ;
        }
        
        if ($request->isMethod('POST')) {
            
            $post_published_homepage = $post->getPublishedHomePage();
            
            $form->bind($request);
            
            // Only admin can publish a post on the home page
            if (!$this->get('security.context')->isGranted('ROLE_ADMIN'))
                    $post->setPublishedHomePage($post_published_homepage);
            
            // Checking permission to modigy the post
            $this->CanModifyPost($post->getId());
            

             if ($form->isValid()) {
            
                $this->getPostManager()->save($post);
    
                //return $this->GetRedirectToPostViewResponse($post);
                return $this->RedirectPostList();
            } else{
                return $this->GetPostEditView($post, $form);
            }
        }

        return $this->GetPostEditView($post, $form);
    }
    
    public function deletePostAction($id)
    {
        $post = $this->getPostById($id);
        if (!$post)
            return $this->CreateJsonResponse(false);

        $this->EnsureCanModifyPost($id);
        $this->getPostManager()->delete($post);
        return $this->CreateJsonResponse(true);
    }
    
  // </editor-fold>
  
    // <editor-fold defaultstate="collapsed" desc="Unsupported actions">
    
     /**
     * @param string $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function tagAction($tag)
    {
        $tag = $this->get('cpt.blog.manager.tag')->findOneBy(array(
            'slug' => $tag,
            'enabled' => true
        ));

        if (!$tag) {
            throw new NotFoundHttpException('Unable to find the tag');
        }

        if (!$tag->getEnabled()) {
            throw new NotFoundHttpException('Unable to find the tag');
        }

        return $this->renderArchive(array('tag' => $tag), array('tag' => $tag));
    }

    /**
     * @param $category
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function categoryAction($category)
    {
        $category = $this->get('cpt.blog.manager.category')->findOneBy(array(
            'slug' => $category,
            'enabled' => true
        ));

        if (!$category) {
            throw new NotFoundHttpException('Unable to find the category');
        }

        if (!$category->getEnabled()) {
            throw new NotFoundHttpException('Unable to find the category');
        }

        return $this->renderArchive(array('category' => $category), array('category' => $category));
    }

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Post archive actions">
    
     /**
     * @return Response
     */
    public function archiveAction()
    {
        return $this->renderArchive();
    }
    
    /**
     * @param string $year
     * @param string $month
     *
     * @return Response
     */
    public function archiveMonthlyAction($year, $month)
    {
        return $this->renderArchive(array(
            'date' => $this->getPostManager()->getPublicationDateQueryParts(sprintf('%d-%d-%d', $year, $month, 1), 'month')
        ));
    }

    /**
     * @param string $year
     *
     * @return Response
     */
    public function archiveYearlyAction($year)
    {
        return $this->renderArchive(array(
            'date' => $this->getPostManager()->getPublicationDateQueryParts(sprintf('%d-%d-%d', $year, 1, 1), 'year')
        ));
    }
    
        /**
     * @param array $criteria
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderArchive(array $criteria = array(), array $parameters = array())
    {
        $pager = $this->getPostManager()->getPager(
            $criteria,
            $this->getRequest()->get('page', 1)
        );
        
        foreach($pager as $post)
            $this->SetPermissions($post);
        
        $parameters = array_merge(array(
            'pager' => $pager,
            'blog'  => $this->get('cpt.blog.blog'),
            'form' => false,
            'tag'   => false,
            'route' => $this->getRequest()->get('_route'),
            'route_parameters' => $this->getRequest()->get('_route_params')
        ), $parameters);

        $response = $this->render(sprintf('CptBlogBundle:Post:archive.%s.twig', $this->getRequest()->getRequestFormat()), $parameters);

        if ('rss' === $this->getRequest()->getRequestFormat()) {
            $response->headers->set('Content-Type', 'application/rss+xml');
        }

        return $response;
    }

    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Comment actions">

    
        /**
     * @param integer $postId
     *
     * @return Response
     */
    public function commentsAction($postId)
    {
        $pager = $this->getCommentManager()
            ->getPager(array(
                'postId' => $postId,
                'status'  => CommentInterface::STATUS_VALID
            ), 1, 500); //no limit

        return $this->render('CptBlogBundle:Post:comments.html.twig', array(
            'pager'  => $pager,
        ));
    }
    
    
    /**
    * Return a ajax response as html content
    */
    public function commentsGetPlainAction($postId){
        
        
        $pager = $this->getCommentManager()
            ->getPager(array(
                'postId' => $postId,
                'status'  => CommentInterface::STATUS_VALID
            ), 1, 500); //no limit

       
       $user = $this->get('security.context')->getToken()->getUser();
               
       // Toggle the tag saying if the comment can be modified (for GUI)
       foreach($pager->getResults() as $comment)
       {
           $comment->setCanModify($this->CanModifyComment($comment, $user));            
       }
       
       $html_string = $this->renderView('CptBlogBundle:Post:comments.html.twig', array(
            'pager'  => $pager,
            'post_id'=> $postId
        ));
       
       //return new Response($html_string,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type
   
       return new Response($html_string,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type
    }

    /**
     * @param $postId
     * @param bool $form
     *
     * @return Response
     */
    public function addCommentFormAction($postId, $form = false)
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
    }

    /**
     * @throws NotFoundHttpException
     *
     * @param string $id
     *
     * @return Response
     */
    public function addCommentAction(Request $request)
    {
         $logger = $this->get('logger');
         $logger->info('Enter addComment');
                
        if ($request->isMethod('POST')) {
            
            $post_id = $this->get('request')->request->get('post_id');

            $post = $this->getPostManager()->findOneBy(array(
                'id' => $post_id
            ));

            if (!$post) {
                throw new NotFoundHttpException(sprintf('Post (%d) not found', $post_id));
            }        

            $user = $this->get('security.context')->getToken()->getUser();

            if (!$this->CanCommentPost($post, $user)) 
                throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException("You do not have the permission to add a comment");


           $form = $this->getCommentForm($post);

           $form->bindRequest($request); 

            if ($form->isValid()) {

                $comment = $form->getData();
                if ($comment->getMessage() === NULL)
                     return new Response('Cannot add an empty comment',500);
                
                $comment->setAuthor($this->getUser());
                $comment->setMessage(nl2br($comment->getMessage()));



                //$this->get('cpt.blog.mailer')->sendCommentNotification($comment);
                $comment->setCanModify($this->CanModifyComment($comment, $user));
                 $this->getCommentManager()->save($comment);

                $html_string = $this->renderView('CptBlogBundle:Post:comment.html.twig', array(
                           'comment'  => $comment
                       ));

                return new Response($html_string,200);//make sure it has the correct content type

            }

                return new Response($html_string,500);
        }
    }

    public function deleteCommentAction(Request $request)
    {
        $comment_id = $this->get('request')->request->get('id');

        $comment = $this->getCommentManager()->findOneBy(array(
            'id' => $comment_id
        ));

        if (!$comment) {
            return new Response("KO - comment doesnt exist: ".$comment_id,500);
        }        
        
        $user = $this->get('security.context')->getToken()->getUser();
        
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
    
    // </editor-fold>

    protected function GetPostEditView($post, $form)
    {
        return $this->render('CptBlogBundle:Post:edit.html.twig', array(
                'post' => $post,    
                'posteditform' => $form->createView(),
                ));
    }
    
  
    protected function SetPermissions($post)
    {
        $user= $this->get('security.context')->getToken()->getUser();
        $post->setCanBeCommented($this->CanCommentPost($post, $user));
        $post->setCanBeModified($this->CanModifyPost($post->getId()));
    }

    
    // <editor-fold defaultstate="collapsed" desc="Private and protected methods">
    
    /*
     * Only logged in users can comment post
     */
    protected function CanCommentPost($post, $user)
    {        
        if (!$post)
            return false;
        
        if (!$user)
            return false;
        
        if (!$post->isCommentable())
            return false;
        
        if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED', $user))
            return false;
        
        return true;
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
    
    protected function CanModifyPost($post_id)
    {
         $usr= $this->get('security.context')->getToken()->getUser();
        
        // Check if logged in user has admin rights
        if ($this->get('security.context')->isGranted('ROLE_ADMIN'))
                return true;
        
        // Excepted admin, only publishers can modify a post
        // TODO: unless associated with an event, in that case event publisher can modify it too
        if (!$this->get('security.context')->isGranted('ROLE_USER'))
                return false;
        
         // Retreiving a post from database (to check that it exists)
         $existing_post = $this->getPostManager()->findOneBy( array('id' => $post_id) );
         
        // Checking logged in user is the author, in  the case the post exists in db
        if ($existing_post && ($existing_post->getAuthor()->getId() == $usr->getId()))
            return true;
        
       return false;
    }
            
    protected function EnsureCanModifyPost($post_id)
    {
        if (!$this->CanModifyPost($post_id))
            throw new AccessDeniedException("You do not have the authorization to modify this article.");
    }
    
    protected function RedirectHome()
    {
        return $this->redirect($this->generateUrl('sonata_news_archive'));
    }
    
    protected function RedirectPostList()
    {
        return $this->redirect($this->generateUrl('cpt_blog_post_list'));
    }
    /**
     * @return \Sonata\SeoBundle\Seo\SeoPageInterface
     */
    protected function getSeoPage()
    {
        if ($this->has('sonata.seo.page')) {
            return $this->get('sonata.seo.page');
        }

        return null;
    }

    protected function GetRedirectToPostViewResponse($post)
    {
        return new RedirectResponse($this->generateUrl('sonata_news_view', array(
                'permalink'  => $this->getBlog()->getPermalinkGenerator()->generate($post)
            )));
    }
    
    protected function getPostEditForm(PostInterface $post)
    {
        return $this->get('form.factory')->createNamed('post', 'cpt_blog_edit_post', $post, Array('attr'=> Array('id' => 'posteditform')));       
    }
    
    
    protected function getPostById($id)
    {
        $post = $this->getPostManager()->findOneBy( array('id' => $id) );

        return $post;
    }

  /**
     * @return \Cpt\BlogBundle\Model\PostManagerInterface
     */
    protected function getPostManager()
    {
        return $this->get('cpt.blog.manager.post');
    }

    /**
     * @return \Cpt\BlogBundle\Model\CommentManagerInterface
     */
    protected function getCommentManager()
    {
        return $this->get('cpt.blog.manager.comment');
    }

        /**
     * @param $post
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function getCommentForm(PostInterface $post)
    {
        $comment = $this->getCommentManager()->create();
        $comment->setPost($post);
        $comment->setStatus($post->getCommentsDefaultStatus());
        return $this->get('form.factory')->createNamed('comment', 'sonata_post_comment', $comment);
    }
    /**
     * @return \Cpt\BlogBundle\Model\BlogInterface
     */
    protected function getBlog()
    {
        return $this->container->get('cpt.blog.blog');
    }

// </editor-fold>
}
