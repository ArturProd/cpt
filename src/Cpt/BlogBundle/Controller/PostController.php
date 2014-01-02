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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Cpt\BlogBundle\Interfaces\Entity\PostInterface as PostInterface;
//use Cpt\BlogBundle\Entity;
//use Cpt\BlogBundle\Form\Type;
use Cpt\BlogBundle\Controller\BasePostController as BasePostController;

//use Cpt\BlogBundle\Manager\PermalinkDateManager as PermalinkDateManager;



class PostController extends BasePostController {

    /**
     * Shows the main article page
     */
    public function viewAction($article_permalink) {
        $params = array(
            'article_permalink' => $article_permalink
        );

        $response = $this->render('CptBlogBundle:Post:articles.html.twig', $params);

        return $response;
    }

    public function getArticleListAction() {
        // Only ajax requests
        //      $this->RestrictAccessToAjax();
        // page can be sent from request
        $page = $this->GetNumericParameter('page', 1);
        $myarticles = $this->GetBoolParameter('myarticles', false);

        $max_article_perpage = $this->container->getParameter("cpt.blog.max_article_perpage");
        $max_page_link = $this->container->getParameter("cpt.blog.max_page_link");

        if ($myarticles) {
            $pager = $this->getPostManager()->getMyArticlesPager($page, $max_article_perpage, $max_page_link);
        } else {
            $pager = $this->getPostManager()->getAllArticlesPager($page, $max_article_perpage, $max_page_link);
        }

        $pageralaune = $this->getPostManager()->getAlauneArticlesPager();

        $pagerresult = $pager->getResults();
        $pageralauneresult = $pageralaune->getResults();
        $postlist = array_merge($pagerresult, $pageralauneresult);
        $serializer = $this->get('jms_serializer');
        $serializedpager = $serializer->serialize($pager, 'json');
        $serializedposts = $serializer->serialize($postlist, 'json');
        return $this->CreateJsonOkResponse(Array('posts' => $serializedposts, 'pager' => $serializedpager));
    }

    public function getSingleArticleListAction() {
        $article_permalink = $this->getRequest()->get('article_permalink');

        if (!preg_match('/.+?/', $article_permalink)) {
            $this->GetPermissionManager()->RestrictResourceNotFound();
        }

        $post = Array();
        $post[] = $this->getPostManager()->findOneByPermalink($article_permalink);
        $serializer = $this->get('jms_serializer');

        $serializedpost = $serializer->serialize($post, 'json');

        return $this->CreateJsonOkResponse(Array('posts' => $serializedpost, 'pager' => null));
    }

    // <editor-fold defaultstate="collapsed" desc="Single post actions ">

    /**
     * Return a ajax response as html content
     */
    public function postGetJsonViewAction() {
        // Only ajax requests
        $request = $this->getRequest();
        $this->GetPermissionManager()->RestrictAccessToAjax($request);

        $id = $request->get('id');
        if (!is_numeric($id)) {
            $this->GetPermissionManager()->RestrictResourceNotFound();
        }

        $post = $this->getPostManager()->findOneBy(array('id' => $id));

        $this->GetPermissionManager()->RestrictResourceNotFound($post);

        // Caching
//        $lastmodified = $post->getUpdatedAt() ? $post->getUpdatedAt() : $post>getcreatedAt();
//        $response = new JsonResponse();
//        $response->setLastModified($lastmodified);
//        $response->setPublic();
//        if ($response->isNotModified($this->getRequest()))
//            return $response;

        $html_string = $this->renderView('CptBlogBundle:Post:preview_post.html.twig', array(
            'post' => $post,
        ));

        //return new Response($html_string,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type
        // return $this->CreateJsonOkResponse($html_string, $response);
        return $this->CreateJsonOkResponse($html_string);
    }

    public function editPostAction(Request $request, $id = null) {
        $request = $this->getRequest();

        $this->GetPermissionManager()->RestrictAccessToAjax($request);
        $this->GetPermissionManager()->RestrictAccessToLoggedIn();

        // Get the id of the post to edit
        if (empty($id)) { // Try to get the id either from the url, or from request
            $id = $request->get('postid');
        }

        if (!is_numeric($id)) { // $id must be numeric
            $this->GetPermissionManager()->RestrictResourceNotFound($id);
        }

        $post = null;

        if ($id < 0) { // $id is null => Create a new post
            $post = $this->getPostManager()->createPostInstance();
        } else {
            // $id is not null => Retreive an existing post
            $post = $this->getPostById($id);
            $this->GetPermissionManager()->RestrictResourceNotFound($post);
        }


        $form = $this->getPostEditForm($post);

        if ($request->isMethod('POST')) {

            $form->bind($request);
            $security = $this->getSecurityContext();
            // Only admin can publish a post on the home page
            if (!$security->isGranted('ROLE_ADMIN')) {
                $post->setPublishedHomePage(false);
            }

            if ($form->isValid()) {
                $this->getPostManager()->save($post);
                return $this->CreateJsonOkResponse($this->GetPostViewData($post));
            } else {
                return $this->CreateJsonFailedResponse($this->GetPostEditView($post, $form));
            }
        }

        return $this->CreateJsonOkResponse($this->GetPostEditView($post, $form));
    }

    public function deletePostAction($id) {
        $post = $this->getPostById($id);
        if (!$post) {
            return $this->CreateJsonOkResponse(false);
        }

        $this->getPostManager()->delete($post);
        return $this->CreateJsonOkResponse(true);
    }

    // </editor-fold>


    protected function GetPostViewData($post) {
        return Array('id' => $post->getId(), 'publishhomepage' => $post->getPublishedHomePage());
    }

    protected function GetPostEditView($post, $form) {
        return $this->renderView('CptBlogBundle:Post:edit.html.twig', array(
                    'post' => $post,
                    'posteditform' => $form->createView(),
        ));
    }

    // <editor-fold defaultstate="collapsed" desc="Private and protected methods">



    protected function RedirectHome() {
        return $this->redirect($this->generateUrl('sonata_news_archive'));
    }

    protected function RedirectPostList() {
        return $this->redirect($this->generateUrl('cpt_blog_post_list'));
    }

    protected function getPostEditForm(PostInterface $post) {
        return $this->get('form.factory')->createNamed('post', 'cpt_blog_edit_post', $post, Array('attr' => Array('id' => 'posteditform')));
    }

// </editor-fold>




    /**
     * @return RedirectResponse
     */
    /*   public function homeAction()
      {
      return $this->RedirectHome();
      } */

    /*
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
     */
    /**
     * @throws NotFoundHttpException
     *
     * @param $permalink
     *
     * @return Response
     */
    /*   public function viewAction($permalink)
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
     */

    /**
     * @param string $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    /*   public function tagAction($tag)
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
      } */
    /**
     * @return Response
     */
    /* public function archiveAction()
      {
      return $this->renderArchive();
      } */

    /**
     * @param string $year
     * @param string $month
     *
     * @return Response
     */
    /*  public function archiveMonthlyAction($year, $month)
      {
      return $this->renderArchive(array(
      'date' => $this->getPostManager()->getPublicationDateQueryParts(sprintf('%d-%d-%d', $year, $month, 1), 'month')
      ));
      } */

    /**
     * @param string $year
     *
     * @return Response
     */
    /*  public function archiveYearlyAction($year)
      {
      return $this->renderArchive(array(
      'date' => $this->getPostManager()->getPublicationDateQueryParts(sprintf('%d-%d-%d', $year, 1, 1), 'year')
      ));
      } */

    /**
     * @param array $criteria
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /* public function renderArchive(array $criteria = array(), array $parameters = array())
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
      } */
}
