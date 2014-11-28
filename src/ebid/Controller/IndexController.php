<?php
namespace ebid\Controller;
use Symfony\Component\HttpFoundation\Response;
/** 
 * @author yanwsh
 * 
 */
class IndexController extends baseController
{
    public function DefaultAction()
    {
        
        return new Response('It works!');
    }
}