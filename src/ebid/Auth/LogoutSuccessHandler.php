<?php
namespace ebid\Auth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use ebid\Entity\Result;
/**
 *
 * @author yanwsh
 *        
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     */
    function __construct()
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        $result = new Result(Result::SUCCESS, "logout successfully.");
        $response = new Response();
        $response->setContent(json_encode($result));
        return $response;
    }
}

?>