<?php
namespace ebid\Controller;
use Symfony\Component\HttpFoundation\Response;
/**
 * @author yanwsh
 *
 */
class adminController
{
    public function indexAction()
    {
        return new Response('Hello admin!');
    }
    
    public function successAction(){
        return new Response('success!');
    }
}