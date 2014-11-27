<?php
namespace ebid\Controller;

use ebid\Entity\Result;

/**
 *
 * @author yanwsh
 *        
 */
class AuthController extends baseController
{
    public function isloginAction()
    {
        global $session;
        $res = false;
        $securityContext = $session->get("security_context");
        if (true === $securityContext->isGranted('ROLE_USER')) {
            $res = true;
            $user = $securityContext->getToken()->getUser();
            $username = $user->getUsername();
            $id = $user->getId();
        }
        $result = new Result(Result::SUCCESS, "", array( 'islogin' => $res, 'username'=> $username, 'id'=> $id));
        return parent::renderJSON($result);
    }
    
    public function loginAction(){
        return parent::accessTypeError();
    }
    
    public function logoutAction(){
        return parent::accessTypeError();
    }
}

?>