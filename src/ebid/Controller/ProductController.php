<?php
/**
 * Created by PhpStorm.
 * User: Elaine
 * Date: 11/23/14
 * Time: 5:49 PM
 */

namespace ebid\Controller;
use ebid\Entity\Product;
use ebid\Entity\Result;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends baseController {
    public function addAction(){
        $this->checkAuthentication();
        $MySQLParser = $this->container->get('MySQLParser');
        $data = json_decode($this->request->getContent());
        $product = new Product();
        if(!$product->isValid($data)){
            $result = new Result(Result::FAILURE, 'post data is not valid.');
            return new Response(json_encode($result));
        }
        $product->set($data);
        if($product->defaultImage == null || $product->defaultImage = ""){
            if($product->imageLists != null && count($product->imageLists)){
                $product->defaultImage = $product->imageLists[0];
            }
        }
        $MySQLParser->insert($product, array("pid"), array('startPrice', 'expectPrice','buyNowPrice','categoryId'));
        $result = new Result(Result::SUCCESS, "Product added successfully.");
        return new Response(json_encode($result));
    }
} 