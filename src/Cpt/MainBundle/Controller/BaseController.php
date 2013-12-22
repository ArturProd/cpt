<?php


namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception as SymfonyException;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController extends Controller {

    const JsonResponseOk = "ok";
    const JsonResponseFailed = "failed";
    
    public function getCommentManager()
    {
        return $this->get('cpt.manager.comment');   
    }
    
    public function getPostManager()
    {
        return $this->get('cpt.blog.manager.post');
    }
    
    public function getPublicationManager()
    {
        return $this->get('cpt.manager.publication');
    }

    public function getSecurityContext()
    {
        return $this->get('security.context');
    }
            
    public function GetNumericParameter($parametername,  $defaultvalue = null, $parameteractualvalue = null)
    {
        $result = $parameteractualvalue;
        if (is_null($parameteractualvalue))
            $result = $this->getRequest()->get($parametername, $defaultvalue);   

        if (empty($result))
            $result = $defaultvalue;
        
        if (!is_numeric($result))
            $this->RestrictPageNotFound("Unable to find numeric parameter ".$parametername. " current value:".$result);
        
        return $result;
    }
    
    public function GetBoolParameter($parametername,  $defaultvalue = null, $parameteractualvalue = null)
    {
        $result = $parameteractualvalue;
        if (is_null($parameteractualvalue))
            $result = $this->getRequest()->get($parametername, $defaultvalue);
        
        if (is_null($result))
            $result = $defaultvalue;
        
        if (is_string($result))
        {
            if(strtoupper($result)==="TRUE")
                $result = true;

            if(strtoupper($result)==="FALSE")
                $result = false;
        }        
        
        if (!is_bool($result))
            $this->RestrictPageNotFound();
    
        return ($result === true);
    }
    
    public function CreateJsonResponse($data = null, $status = BaseController::JsonResponseOk)
    {
      $response = new JsonResponse();
      $response->setData(Array("data" => $data, "status" => $status));
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
    
    public function RestrictPageNotFound($message = "Page not found")
    {
         throw new SymfonyException\NotFoundHttpException($message);
    }
    
    public function RestrictAccessToAjax()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest())
            throw new SymfonyException\ForbiddenHttpException("Ressource cannot be accessed this way.");    
    }
    
    public function RestrictAccessToLoggedIn()
    {
        $this->RestrictAccessDenied($this->get('security.context')->isGranted('ROLE_USER'));
    }
    
    public function RestrictAccessDenied($allow_access=false)
    {
        if (!$allow_access)
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
    
    public function isUserAdmin()
    {
        return $this->get('security.context')->isGranted('ROLE_ADMIN');
    }
    
//    public function getUser()
//    {
//        return $this->get('security.context')->getToken()->getUser();
//    }
    
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
