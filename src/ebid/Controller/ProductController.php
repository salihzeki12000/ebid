<?php
/**
 * Created by PhpStorm.
 * User: Elaine
 * Date: 11/23/14
 * Time: 5:49 PM
 */

namespace ebid\Controller;
use ebid\Entity\Product;
use Symfony\Component\HttpFoundation\Response;

class ProductController {
    public function addAction(){
        $MySQLParser = $this->container->get('MySQLParser');
        $data = json_decode($this->request->getContent());
        $product = new Product();
        if(!$product->isValid($data)){
            $result = new Result(Result::FAILURE, 'post data is not valid.');
            return new Response(json_encode($result));
        }
        $product->set($data);
        $MySQLParser->insert($product, array("pid"), array('startPrice', 'expectPrice','buyNowPrice','categoryId'));
        return new Response("hello world");
    }
} 