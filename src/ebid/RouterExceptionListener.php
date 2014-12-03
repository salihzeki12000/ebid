<?php
namespace ebid;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ebid\Entity\Result;
/**
 *
 * @author yanwsh
 *        
 */
class RouterExceptionListener
{

    function __construct()
    {
    }
    
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();
        // don't handle Access Denied Exception
        if($exception instanceof AccessDeniedException){
            return;
        }
        $message = sprintf(
            'Error: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );
        
        $result = new Result(Result::INTERNAL_ERROR, $message);
    
        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent(json_encode($result));
    
        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        $response->headers->set('X-Status-Code', Response::HTTP_OK);
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    
        // Send the modified response object to the event
        $event->setResponse($response);
    }
}

?>