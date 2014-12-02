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
use ebid\Entity\Bid;
use ebid\Event\BidResultEvent;
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
        $product->status = Product::INITIAL;
        $product->currentPrice = $product->startPrice;
        $MySQLParser->insert($product, array("pid"), array('startPrice', 'expectPrice','buyNowPrice','categoryId'));
        $result = new Result(Result::SUCCESS, "Product added successfully.");
        return new Response(json_encode($result));
    }

    public function editGetAction($itemId){
        $this->checkAuthentication();
        $MySQLParser = $this->container->get('MySQLParser');
        $product = new Product();
        $result = $MySQLParser->select($product, ' pid = ' . $itemId);
        if(count($result) == 0){
            $result = new Result(Result::FAILURE, 'Product cannot found.');
            return new Response(json_encode($result));
        }
        $product->set($result[0]);
        $product->imageLists = json_decode($product->imageLists);
        global $session;
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        if($user->uid != $product->seller){
            $result = new Result(Result::FAILURE, 'You can\'t modify the product which is not belong to you.');
            return new Response(json_encode($result));
        }
        $result = new Result(Result::SUCCESS, "Product get successfully.", $product);
        return new Response(json_encode($result));
    }

    public function editPostAction($itemId){
        $itemId = intval($itemId);
        $this->checkAuthentication();
        $product = new Product();
        $MySQLParser = $this->container->get('MySQLParser');
        $result = $MySQLParser->select($product, ' pid = ' . $itemId);
        if(count($result) == 0){
            $result = new Result(Result::FAILURE, 'Product cannot found.');
            return new Response(json_encode($result));
        }
        $data = json_decode($this->request->getContent());

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
        if($user->uid != $product->seller){
            $result = new Result(Result::FAILURE, 'You can\'t modify the product which is not belong to you.');
            return new Response(json_encode($result));
        }
        $MySQLParser->update($product, array("pid"), array('startPrice', 'expectPrice','buyNowPrice','categoryId'), 'pid');
        $result = new Result(Result::SUCCESS, "Product update successfully.");
        return new Response(json_encode($result));
    }

    public function itemAction($itemId){
        $itemId = intval($itemId);
        $MySQLParser = $this->container->get('MySQLParser');
        //get product result
        $product = new Product();
        $result = $MySQLParser->select($product, ' pid = ' .$itemId, NULL,array('pid','pname', 'buyNowPrice', 'currentPrice', 'startPrice','defaultImage', 'imageLists', 'endTime', 'categoryId', 'shippingType', 'shippingCost', 'auction', 'seller', '`condition`', 'status') );
        if(count($result) == 0){
            $result = new Result(Result::FAILURE, 'Can not find product.');
            return new Response(json_encode($result));
        }
        $result = $result[0];
        $status = intval($result['status']);
        if($status == Product::END || $status == Product::CLOSE){
            $result = new Result(Result::EXPIRE, 'Current Product is expired.');
            return new Response(json_encode($result));
        }
        $currentTime = time();
        $endTime = strtotime($result['endTime']);
        if(($endTime - $currentTime) <= 0){
            $status = intval($result['status']);
            if($status == Product::BIDDING || $status == Product::INITIAL){
                $product->set($result);
                $product->status = Product::END;
                $MySQLParser->updateSpecific($product, array('status'), array('status'), 'pid');
                $this->onBidFinish($itemId);
            }
            $result = new Result(Result::EXPIRE, 'Current Product is expired.');
            return new Response(json_encode($result));
        }
        $result['imageLists'] = json_decode($result['imageLists']);
        $product->set($result);
        //get seller info
        $user = new User($product->seller, "seller");
        $res = $MySQLParser->select($user, 'uid = ' . $user->uid, NULL, array('uid', 'username','email', 'state'));
        $res = $res[0];
        $product->seller = $res;
        //filter null record
        //$product = (object) array_filter((array) $product);
        //calc increment price and forceInc price;
        $forceInc = $inc = $this->getIncPrice($product->currentPrice);
        if($product->status == Product::INITIAL){
            $forceInc = 0;
        }else{
            $forceInc = $inc;
        }


        //set current price and bid number to firebase
        //global $session;
        //$ret = $session->get('bid/item/' . $itemId);
        //if($ret == null) {
            //fetch current price.
            //$firebase = $this->container->get('Firebase');
            //$ret = $firebase->get('bid/item/' . $itemId);
            //if ($ret == null) {
                $this->updateFirebase($itemId, $product->currentPrice);
            //}
            //$session->set('bid/item/' . $itemId, $ret);
        //}
        //get userminPrice
        //check is login?
        global $session;
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        if($user == null || $user == "anon."){
            //not login
            $product->userMinPrice = $product->currentPrice + $forceInc;
            //check whether user bid this product
            $product->isBid = false;
        }else{
            $bid = new Bid();
            //get bid record
            $res = $MySQLParser->select($bid, ' uid = ' . $user->uid . ' AND pid = '. $itemId, 'bidPrice DESC');
            if(count($res) > 0){
                //has bid record
                $res = $res[0];
                $maxPrice = floatval($res['bidPrice']);
                $maxPrice = ($maxPrice > $product->currentPrice)? $maxPrice : $product->currentPrice;
                $minPrice = $maxPrice + $this->getIncPrice($maxPrice);
                $product->userMinPrice = $minPrice;
                $product->isBid = true;
            }else{
                //no bid record
                $product->userMinPrice = $product->currentPrice + $forceInc;
                $product->isBid = false;
            }
        }

        $product->serverTime = date('Y-m-d H:i:s', time());

        $result = new Result(Result::SUCCESS, "get product successfully.", $product);
        return new Response(json_encode($result));
    }

    public function resultAction($itemId){
        $itemId = intval($itemId);
        $MySQLParser = $this->container->get('MySQLParser');
        //get product result
        $product = new Product();
        $result = $MySQLParser->select($product, ' pid = ' .$itemId, NULL,array('pid','pname', 'buyNowPrice', 'currentPrice', 'startPrice','defaultImage', 'endTime', 'categoryId', 'shippingType', 'shippingCost', 'auction', 'seller', '`condition`', 'status') );
        if(count($result) == 0){
            $result = new Result(Result::FAILURE, 'Can not find product.');
            return new Response(json_encode($result));
        }
        $result = $result[0];
        $product->set($result);
        //get seller info
        $user = new User($product->seller, "seller");
        $res = $MySQLParser->select($user, 'uid = ' . $user->uid, NULL, array('uid', 'username','email', 'state'));
        $res = $res[0];
        $product->seller = $res;

        $currentTime = time();
        $endTime = strtotime($product->endTime);
        $status = intval($product->status);
        if(($endTime - $currentTime) > 0 && $status != Product::END && $status != Product::CLOSE){
            //filter null record
            //$product = (object) array_filter((array) $product);

            $product->hasWinner = false;
            $product->WinnerId = -1;
            $product->userHasBid = false;
            $product->isEnd = false;
        }
        else{
            if($status == Product::BIDDING || $status == Product::INITIAL){
                $product->status = Product::END;
                $MySQLParser->updateSpecific($product, array('status'), array('status'), 'pid');
                $this->onBidFinish($itemId);
            }
            //filter null record
            //$product = (object) array_filter((array) $product);

            //get product bid record
            $bid = new Bid();
            $res = $MySQLParser->select($bid, ' pid = ' . $itemId, 'bidPrice DESC, status ASC');
            if(count($res) > 0) {
                $res = $res[0];
                $higherRecord = new Bid();
                $higherRecord->set($res);
            }
            if($higherRecord != null){
                $product->hasWinner = true;
                $product->WinnerId = intval($higherRecord->uid);
                //get bid user
                global $session;
                $securityContext = $session->get("security_context");
                $user = $securityContext->getToken()->getUser();
                if($user == null || $user == "anon."){
                    $product->userHasBid = false;
                }else{
                    //get user bid record
                    $res = $MySQLParser->select($bid, ' uid = ' . $user->uid . ' AND pid = ' . $itemId , 'bidPrice DESC');
                    //has bid record
                    if(count($res) > 0){
                        $product->userHasBid = true;
                    }
                    else{
                        $product->userHasBid = false;
                    }
                }
            }else{
                $product->hasWinner = false;
                $product->WinnerId = -1;
                $product->userHasBid = false;
            }
            $product->isEnd = true;
        }


        $result = new Result(Result::SUCCESS, "get product result successfully.", $product);
        return new Response(json_encode($result));
    }

    public function onBidFinish($itemId){
        $itemId = intval($itemId);
        $MySQLParser = $this->container->get('MySQLParser');
        //get winner record
        $winnersql = 'select User.uid, User.username, User.email, Bid.uid, Bid.status, Product.pname, Product.defaultImage, Product.currentPrice, Product.endTime from ' . _table('Bid'). ' AS Bid INNER JOIN ' . _table('User'). ' AS User ON Bid.uid = User.uid INNER JOIN '. _table('Product').' AS Product ON Product.pid = Bid.pid where Bid.pid = '. $itemId.' AND Bid.`status` = ' . Bid::WIN.' group by Bid.uid';
        $result = $MySQLParser->query($winnersql);
        $winlists = (count($result) != 0)? $result: null;
        $list = array();
        if($winlists != null){
            foreach($result as $val){
                $list[] = $val['uid'];
            }
        }

        $losersql =  'select User.uid, User.username, User.email, Bid.uid, Bid.status, Product.pname, Product.defaultImage, Product.currentPrice, Product.endTime from ' . _table('Bid'). ' AS Bid INNER JOIN ' . _table('User'). ' AS User ON Bid.uid = User.uid INNER JOIN '. _table('Product').' AS Product ON Product.pid = Bid.pid where Bid.pid = '. $itemId.' AND Bid.`status` = '. Bid::NOTWIN;
        if($winlists != null){
            $losersql .= ' AND User.uid NOT IN (' . implode(",", $list) . ')';
        }
        $losersql .= ' group by Bid.uid';
        $result = $MySQLParser->query($losersql);
        $loselists = (count($result) != 0)? $result: null;

        $event = new BidResultEvent($winlists, $loselists);
        $this->dispatcher->dispatch(BidResultEvent::BIDRESULT, $event);
    }

    public function bidAction($itemId, $price){
        $itemId = intval($itemId);
        $price = floatval($price);
        //check auth
        $this->checkAuthentication();
        $MySQLParser = $this->container->get('MySQLParser');
        $bid = new Bid();
        //get bid user
        global $session;
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        //get user bid record
        $res = $MySQLParser->select($bid, ' uid = ' . $user->uid . ' AND pid = ' . $itemId, 'bidPrice DESC');
        //has bid record
        if(count($res) > 0){
            $res = $res[0];
            $maxPrice = floatval($res['bidPrice']);
            $minPrice = $maxPrice + $this->getIncPrice($maxPrice);
            if($price < $minPrice){
                $result = new Result(Result::FAILURE, 'You must post a price greater than ' . $minPrice .'.');
                return new Response(json_encode($result));
            }
        }
        //get product infomation
        $product = new Product();
        $result = $MySQLParser->select($product, ' pid = ' .$itemId);
        $result = $result[0];
        $product->set($result);
        //check time
        $currentTime = time();
        $endTime = strtotime($product->endTime);
        if(($endTime - $currentTime) <= 0){
            $status = intval($product->status);
            if($status == Product::BIDDING || $status == Product::INITIAL){
                $product->status = Product::END;
                $MySQLParser->updateSpecific($product, array('status'), array('status'), 'pid');
                $this->onBidFinish($itemId);
            }
            $result = new Result(Result::EXPIRE, 'Current Product is expired.');
            return new Response(json_encode($result));
        }
        //check price
        if($product->status == Product::INITIAL){
            $minPrice = $product->currentPrice;
        }else{
            $minPrice = $product->currentPrice  + $this->getIncPrice($product->currentPrice);
        }

        if($minPrice > $price){
            $result = new Result(Result::FAILURE, 'You can\'t post a price less than current price(' . $product->currentPrice .').');
            return new Response(json_encode($result));
        }else if($product->currentPrice == $price){
            if($product->status != Product::INITIAL){
                $result = new Result(Result::FAILURE, 'You can\'t post a price less than current price(' . $product->currentPrice .').');
                return new Response(json_encode($result));
            }
        }
        //update product price

        //get product bid record
        $res = $MySQLParser->select($bid, ' pid = ' . $itemId, 'bidPrice DESC, status ASC');
        if(count($res) > 0) {
            $res = $res[0];
            $higherRecord = new Bid();
            $higherRecord->set($res);
        }

        $isWinner = true;
        $history= null;
        if($higherRecord == null){
            //no bid record
            $product->currentPrice = $minPrice;
            $history[] = array('username'=> $user->username, 'price' => floatval($product->currentPrice), 'post' => date('Y-m-d H:i:s', time()));
        }else{
            //highest price is the same user
            if($higherRecord->uid == $user->uid){
                //ignore
                //update higherRecord to not win
                $higherRecord->status = Bid::NOTWIN;
                $MySQLParser->updateSpecific($higherRecord, array('status'), array('status'), 'bid');
            }else{
                $preHigherUser = new User($higherRecord->uid, "higheruser");
                $res = $MySQLParser->select($preHigherUser, ' uid = ' . $higherRecord->uid);
                $res = $res[0];
                $preHigherUser->set($res);

                //if price is greater than $higherRecord
                if($price > $higherRecord->bidPrice){
                    $product->currentPrice = $higherRecord->bidPrice + $this->getIncPrice($higherRecord->bidPrice);
                    $history[] = array('username'=> $user->username, 'price' => floatval($product->currentPrice), 'post' => date('Y-m-d H:i:s', time()));
                    //update higherRecord to not win
                    $higherRecord->status = Bid::NOTWIN;
                    $MySQLParser->updateSpecific($higherRecord, array('status'), array('status'), 'bid');
                }else{
                    //price is lower or equal than higherRecord
                    if($price == $higherRecord->bidPrice){
                        $history[] = array('username'=> $user->username, 'price' => floatval($product->currentPrice), 'post' => date('Y-m-d H:i:s', time()));
                        $history[] = array('username'=> $preHigherUser->username, 'price' => floatval($higherRecord->bidPrice), 'post' => date('Y-m-d H:i:s', time()));
                        $product->currentPrice = $price;
                    }else{
                        $history[] = array('username'=> $user->username, 'price' => floatval($product->currentPrice), 'post' => date('Y-m-d H:i:s', time()));
                        $product->currentPrice = $price + $this->getIncPrice($price);
                        $history[] = array('username'=> $preHigherUser->username, 'price' => floatval($product->currentPrice), 'post' => date('Y-m-d H:i:s', time()));
                    }
                    $isWinner = false;
                }
            }
        }

        if($product->status == Product::INITIAL){
            $product->status = Product::BIDDING;
        }
        $MySQLParser->updateSpecific($product, array('currentPrice', 'status'), array('currentPrice', 'status'), 'pid');

        //insert to database
        $bid = new Bid();
        $bid->uid = $user->uid;
        $bid->status = $isWinner? Bid::WIN: Bid::NOTWIN;
        $bid->pid = $itemId;
        $bid->bidPrice = $price;
        $bid->bidTime = date('Y-m-d H:i:s', time());
        $MySQLParser->insert($bid, array("bid"), array('pid', 'bidPrice', 'status', 'uid'));


        //update firebase
        $this->updateFirebase($itemId, $product->currentPrice);

        //update bid list
        if($history != null){
            $firebase = $this->container->get('Firebase');
            foreach($history as $val){
                $firebase->push('bid/history/' . $itemId, $val);
            }
        }

        $result = new Result(Result::SUCCESS, "bid post successfully.");
        return new Response(json_encode($result));
    }

    public function updateFirebase($itemId, $currentPrice){
        $itemId = intval($itemId);
        $MySQLParser = $this->container->get('MySQLParser');
        $sql = 'SELECT COUNT(*) FROM ' . _table('Bid') .' WHERE pid = '. $itemId;
        $res = $MySQLParser->query($sql);
        $bidNumber = intval($res[0]['COUNT(*)']);
        $sql = 'SELECT * FROM ' . _table('Bid') .' WHERE pid = '. $itemId .' ORDER BY bidPrice DESC, status ASC  LIMIT 0,1';
        $res = $MySQLParser->query($sql);
        if(count($res) > 0){
            $res = $res[0];
            $bid = new Bid();
            $bid->set($res);
            $winnerId = intval($bid->uid);
        }else{
            $winnerId = -1;
        }
        $priceList = array('currentPrice' => floatval($currentPrice), 'bidNumber' => $bidNumber, 'winner' => $winnerId);
        $firebase = $this->container->get('Firebase');
        $ret = $firebase->set('bid/item/' . $itemId, $priceList);
    }

    public function descriptionAction($itemId){
        $itemId = intval($itemId);
        $MySQLParser = $this->container->get('MySQLParser');
        //get product result
        $product = new Product();
        $result = $MySQLParser->select($product, ' pid = ' .$itemId, NULL,array('description') );
        if(count($result) > 0){
            $result = $result[0]['description'];
        }else{
            $result = new Result(Result::FAILURE, 'can not find product.');
            $result = json_encode($result);
        }
        return new Response($result);
    }

    public function getIncPrice($price){
        if($price < 10){
            return 0.5;
        }else if($price < 50){
            return 1;
        }else if($price < 100){
            return 2;
        }else if($price < 300){
            return 5;
        }else if($price < 1000){
            return 10;
        }else if($price < 10000){
            return 50;
        }else if($price < 50000){
            return 500;
        }else{
            return 1000;
        }
    }
} 