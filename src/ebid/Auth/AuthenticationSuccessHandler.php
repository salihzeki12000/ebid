<?php
namespace ebid\Auth;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $result = new Result(Result::SUCCESS, "login successfully");
        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }
}

?>