<?php
namespace ebid\Controller;
use Symfony\Component\HttpFoundation\Response;
/** 
 * @author yanwsh
 * 
 */
class testController 
{
    public function fooAction($slug)
    {
        return new Response('Hello world! '. $slug);
    }
    
    public function foo1Action()
    {
        return new Response('Hello world!');
    }
}