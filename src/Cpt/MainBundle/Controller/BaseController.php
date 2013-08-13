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
}
?>
