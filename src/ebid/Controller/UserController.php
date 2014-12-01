<?php
namespace ebid\Controller;
use Symfony\Component\HttpFoundation\Response;
use ebid\Entity\User;
use ebid\Entity\Result;
use ebid\Event\RegisterEvent;

/**
 *
 * @author yanwsh
 *        
 */
class UserController extends baseController
{
    function registerAction(){
        global $encoderFactory;
        $MySQLParser = $this->container->get('MySQLParser');
        $data = json_decode($this->request->getContent());
        $user = new User(0, $data->username);
        if(!$user->isValid($data)){
            $result = new Result(Result::FAILURE, 'post data is not valid.');
            return new Response(json_encode($result));
        }
        $user->set($data);
        $arr = $MySQLParser->select($user, "username = '{$user->username}'");
        if(count($arr) != 0){
            $result = new Result(Result::FAILURE, 'username is duplicate, please input another username.');
        }else{
            $encoder = $encoderFactory->getEncoder($user);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->password = $password;
            $user->roles = 'ROLE_USER';

            $registerEvent = new RegisterEvent($user);
            $this->dispatcher->dispatch(RegisterEvent::REGISTER, $registerEvent);

            $MySQLParser->insert($user, array('uid'));


            $result = new Result(Result::SUCCESS, 'register successfully.');
        }
        return new Response(json_encode($result));
    }

    function IndexAction($size){
        $size = intval($size);
        $this->checkAuthentication();
        $MySQLParser = $this->container->get('MySQLParser');
        global $session;
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        $result = array('user' => array('id'=> $user->uid, 'username' => $user->username, 'email' => $user->email, 'address' => $user->address, 'state' => $user->state, 'zipcode'=> $user->zipcode));
        $sql ='SELECT pid, pname, buyNowPrice, currentPrice, startPrice, defaultImage, endTime, categoryId, shippingType, shippingCost, auction, seller, `condition`, status  FROM ' . _table('Product'). ' WHERE seller = '. $user->uid . ' ORDER BY pid desc LIMIT ' . $size;
        $list = $MySQLParser->query($sql);
        $result['seller'] = $list;
        $sql = 'SELECT Product.pid, Product.pname, Product.buyNowPrice, Product.currentPrice, Product.startPrice, Product.defaultImage, Product.endTime, Product.categoryId, Product.shippingType, Product.shippingCost, Product.auction, Product.seller, Product.`condition`, Product.`status` as ProductStatus, Bid.`status` AS BidStatus, Bid.bidPrice FROM (SELECT * FROM ' . _table('Bid') . ' WHERE uid = ' . $user->uid . ' ORDER BY bidPrice desc) AS Bid INNER JOIN '. _table('Product'). ' AS Product ON Bid.pid = Product.pid GROUP BY Bid.pid ORDER BY Product.pid DESC  LIMIT ' . $size;
        $list = $MySQLParser->query($sql);
        $result['bid'] = $list;
        $result = new Result(Result::SUCCESS, 'get user list successfully.', $result);
        return new Response(json_encode($result));
    }
}

?>