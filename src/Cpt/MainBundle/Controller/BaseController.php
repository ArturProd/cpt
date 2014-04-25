<?php

namespace Cpt\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception as SymfonyException;
use Cpt\BlogBundle\Interfaces\Manager\PostManagerInterface as PostManagerInterface;
use Cpt\PubliationBundle\Interfaces\Manager\PublicationManagerInterface as PublicationManagerInterface;
use Cpt\EventBundle\Interfaces\Manager\EventManagerInterface as EventManagerInterface;
use Cpt\EventBundle\Interfaces\Manager\RegistrationManagerInterface as RegistrationManagerInterface;
use Cpt\EventBundle\Interfaces\Manager\CalendarManagerInterface as CalendarManagerInterface;
use Cpt\MainBundle\Interfaces\Manager\PermissionManagerInterface as PermissionManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController extends Controller {

    const JsonResponseOk = "ok";
    const JsonResponseFailed = "failed";

    public function getCommentManager() {
        return $this->get('cpt.manager.comment');
    }
    
    public function getUserManager() {
        return $this->get('fos_user.user_manager');
    }

    /**
     * @return PostManagerInterface
     */
    public function getPostManager() {
        return $this->get('cpt.manager.post');
    }

    /**
     * @return PublicationManagerInterface
     */
    public function getPublicationManager() {
        return $this->get('cpt.manager.publication');
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager() {
        return $this->get("cpt.event.manager");
    }
    
    /**
     * @return RegistrationManagerInterface
     */
    public function getRegistrationManager() {
        return $this->get("cpt.registration.manager");
    }
    
    /**
     * 
     * @return CalendarManagerInterface
     */
    public function getCalendarManager()
    {
        return $this->get("cpt.calendar.manager");
    }

    /**
     * @return PermissionManagerInterface
     */
    public function getPermissionManager() {
        return $this->get("cpt.permission.manager");
    }

    public function getCalendR() {
        return $this->get("calendR");
    }

    public function getSerializer() {
        return $this->get('jms_serializer');
    }

    public function getSecurityContext() {
        return $this->get('security.context');
    }

    public function GetNumericParameter($parametername, $defaultvalue = null, $parameteractualvalue = null) {
        $result = $parameteractualvalue;
        if (is_null($parameteractualvalue)) {
            $result = $this->getRequest()->get($parametername, $defaultvalue);
        }

        if (empty($result)) {
            $result = $defaultvalue;
        }

        if (!is_numeric($result)) {
            $this->RestrictPageNotFound("Unable to find numeric parameter " . $parametername . " current value:" . $result);
        }

        return $result;
    }

    public function GetBoolParameter($parametername, $defaultvalue = null, $parameteractualvalue = null) {
        $result = $parameteractualvalue;
        if (is_null($parameteractualvalue)) {
            $result = $this->getRequest()->get($parametername, $defaultvalue);
        }

        if (is_null($result)) {
            $result = $defaultvalue;
        }

        if (is_string($result)) {
            if (strtoupper($result) === "TRUE") {
                $result = true;
            }

            if (strtoupper($result) === "FALSE") {
                $result = false;
            }
        }

        if (!is_bool($result)) {
            $this->RestrictPageNotFound();
        }

        return ($result === true);
    }

    public function CreateJsonFailedResponse($data, JsonResponse $response = null) {
        return $this->CreateJsonResponse($data, BaseController::JsonResponseFailed, $response);
    }

    public function CreateJsonOkResponse($data, JsonResponse $response = null) {
        return $this->CreateJsonResponse($data, BaseController::JsonResponseOk, $response);
    }

    public function CreateJsonResponse($data = null, $status = BaseController::JsonResponseOk, JsonResponse $presponse = null) {
        if (!$presponse) {
            $response = new JsonResponse();
        } else {
            $response = $presponse;
        }

        $response->setData(Array("data" => $data, "status" => $status));
        return $response;
    }

    public function CreateCSVFileResponse($response, $filename="csvfile") {

        $response->setStatusCode(200);

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Description', 'Submissions Export');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->setCharset('UTF-8');
        
        return $response;
    }

    public function isUserAdmin() {
        return $this->get('security.context')->isGranted('ROLE_ADMIN');
    }

//    public function getUser()
//    {
//        return $this->get('security.context')->getToken()->getUser();
//    }

    public function ThrowBadRequestException($error_message = "Bad request") {
        throw new SymfonyException\HttpException(400, $error_message);
    }

    public function RestrictBusinessRuleError($error_message = "Business rule error") {
        // 422 Unprocessable Entity
        throw new SymfonyException\HttpException(422, $error_message);
    }

    public function ThrowInternalServerError($error_message = "Internal Error") {
        throw new Exception\HttpException(500, $error_message);
    }
    
    
    protected function setFlashMessage($content) {
        $this->get('session')->getFlashBag()->add(
                'notice', $title
        );
    }

}
