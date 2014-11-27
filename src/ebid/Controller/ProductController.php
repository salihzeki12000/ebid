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
use ebid\Entity\User;
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
        if($product->defaultImage == null || $product->defaultImage == ""){
            if($product->imageLists != null && count($product->imageLists)){
                $product->defaultImage = $product->imageLists[0];
            }
        }
        global $session;
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        $product->seller = $user->uid;
        if (preg_match ("/<body.*?>([\w\W]*?)<\/body>/", $product->description, $regs)) {
            $product->description = $regs[1];
        }
        $MySQLParser->insert($product, array("pid"), array('startPrice', 'expectPrice','buyNowPrice','categoryId'));
        $result = new Result(Result::SUCCESS, "Product added successfully.");
        return new Response(json_encode($result));
    }

    public function itemAction($itemId){
        $MySQLParser = $this->container->get('MySQLParser');
        $product = new Product();
        $result = $MySQLParser->select($product, ' pid = ' .$itemId );
        $result = $result[0];
        $result['imageLists'] = json_decode($result['imageLists']);
        $product->set($result);
        $user = new User($product->seller, "seller");
        $res = $MySQLParser->select($user, 'uid = ' . $user->uid, NULL, array('uid', 'username','email', 'state'));
        $res = $res[0];
        $product->seller = $res;
        if (preg_match ("/<body.*?>([\w\W]*?)<\/body>/", $product->description, $regs)) {
            $product->description = $regs[1];
        }
        $result = new Result(Result::SUCCESS, "get product successfully.", $product);
        return new Response(json_encode($result));
    }
} 