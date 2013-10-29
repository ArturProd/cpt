<?php


namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception as SymfonyException;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController extends Controller {
    
    public function CreateJsonResponse($data)
    {
      $response = new JsonResponse();
      $response->setData($data);
      return $response;
    }
    
    public function SendCSVFileResponse($content)
    {
        
         $response = new Response();
         
         $response->setContent($content);         
         $response->setStatusCode(200);

         $response->headers->set('Content-Type', 'text/csv');
         $response->headers->set('Content-Description', 'Submissions Export');
         $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
         $response->headers->set('Content-Transfer-Encoding', 'binary');
         $response->headers->set('Pragma', 'no-cache');
         $response->headers->set('Expires', '0');
         $response->setCharset('UTF-8');

        $response->send();
    }
    
    public function RestrictResourceNotFound($ressource=null)
    {
        if (!$ressource)
            throw new SymfonyException\NotFoundHttpException("Resource not found.");
    }
    
    public function RestrictAccessToAjax()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest())
            throw new SymfonyException\ForbiddenHttpException("Ressource cannot be accessed this way.");    
    }
    
    public function RestrictAccessToLoggedIn()
    {
        if (!$this->get('security.context')->isGranted('ROLE_USER'))
            throw new SymfonyException\AccessDeniedHttpException("Access denied.");
    }
    
    public function RestrictAccessToUser($userid, $authorizeAdmin = true)
    {
        $this->RestrictAccessToLoggedIn();                
        
        if (($authorizeAdmin)&&$this->get('security.context')->isGranted('ROLE_ADMIN'))
            return;
        else if ($userid == $this->get('security.context')->getToken()->getUser()->getId())
            return;

        throw new SymfonyException\ForbiddenHttpException("Access denied.");
    }
    
    public function RestrictBusinessRuleError($error_message = "Business rule error")
    {
        // 422 Unprocessable Entity
        throw new SymfonyException\HttpException(422, $error_message);    
    }
    
    public function ThrowInternalServerError($error_message = "Internal Error")
    {
        throw new Exception\HttpException(500,$error_message);
    }
}
?>
