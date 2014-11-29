<?php
namespace ebid\Auth;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use ebid\Entity\Result;
/**
 *
 * @author yanwsh
 *        
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    function __construct()
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        global $session;
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        $data = array('username' => $user->getUsername(), 'id' => $user->getId());
        $result = new Result(Result::SUCCESS, "login successfully", $data);
        $response = new Response();
        $response->headers->setCookie(new Cookie('id', $user->getId(), 0, '/', null, false, false));
        $response->headers->setCookie(new Cookie('username', $user->getUsername(), 0, '/', null, false, false));
        $response->setContent(json_encode($result));
        return $response;
    }
}

?>