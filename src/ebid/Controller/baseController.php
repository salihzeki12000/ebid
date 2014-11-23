<?php
namespace ebid\Controller;

use Symfony\Component\HttpFoundation\Response;
use ebid\Entity\Result;


/**
 *
 * @author yanwsh
 *        
 */
class baseController
{
    protected $request;
    protected $db;
    protected $container;
    
    function __construct()
    {
        global $container, $request;
        $this->container = $container;
        $this->db = $container->get('db');
        $this->request = $request;
    }
    
    function renderJSON($obj){
        $response = new Response();
        $response->setContent(json_encode($obj));
        return $response;
    }
    
    function accessTypeError(){
        $result = new Result(Result::FAILURE, "You must use post method.");
        return $this::renderJSON($result);
    }
    
    function getHttpContent($Url = NULL){
        if(!isset($Url)){
            return NULL;
        } 
        $data = $this::get_data($Url);
        if($data == false){
            return NULL;
        }
        return $data;
    }
    
    function get_data($url) {
        $handle = fopen ($url, "rb");
        $contents = "";
        do {
            $data = fread($handle, 1024);
            if (strlen($data) == 0) {
                break;
            }
            $contents .= $data;
        } while(true);
        fclose ($handle);
        return $contents;
    }
    
}

?>