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

use Cpt\BlogBundle\Model\PostInterface;
use Cpt\BlogBundle\Entity;
use Cpt\BlogBundle\Form\Type;

use Cpt\BlogBundle\Controller\BasePostController as BaseController;



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
    
    
    /**
     * @return \Cpt\BlogBundle\Model\BlogInterface
     */
    protected function getBlog()
    {
        return $this->container->get('cpt.blog.blog');
    }

// </editor-fold>
}
