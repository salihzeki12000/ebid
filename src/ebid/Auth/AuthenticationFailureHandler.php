<?php
namespace ebid\Auth;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ebid\Entity\Result;
/**
 *
 * @author yanwsh
 *        
 */
class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    function __construct()
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $result = new Result(Result::FAILURE, $exception->getMessage());
        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }
}

?>