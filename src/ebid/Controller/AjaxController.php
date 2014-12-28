<?php
namespace ebid\Controller;

use ebid\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ebid\Entity\Result;
/**
 *
 * @author yanwsh
 *        
 */
class AjaxController extends baseController
{
    const APPID = "wensheng-6df0-4273-b999-c71b618344c2";
    
    public function autoCompleteProductsAction(){
        $searchTerm = urlencode($this->request->query->get('searchTerm'));
        $maxFetch = $this->request->query->get('maxFetch');
        $url = 'http://svcs.ebay.com/services/search/FindingService/v1?SECURITY-APPNAME=' . AjaxController::APPID . '&OPERATION-NAME=findItemsByKeywords&SERVICE-VERSION=1.0.0&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&keywords='.$searchTerm
                . '&paginationInput.entriesPerPage='.$maxFetch;
        $data = parent::getHttpContent($url);
        $obj = json_decode($data);
        $result = array('productFamilies'=>array());
        try{
            $items = $obj->findItemsByKeywordsResponse[0]->searchResult[0]->item;
            foreach($items as $item){
                $myitem = array(
                    'itemId'=> $item->itemId[0],
                    'title'=> $item->title[0],
                    'galleryURL' => $item->galleryURL[0]
                );
                $result['productFamilies'][] = $myitem;
            }
        }catch(\Exception $e){
        }
        return new Response(json_encode($result));
    }

    public function findProductsAction(){
        $searchTerm = urlencode($this->request->query->get('searchTerm'));
        $maxFetch = $this->request->query->get('maxFetch');
        $url = "http://open.api.ebay.com/shopping?callname=FindProducts&responseencoding=JSON&appid=". AjaxController::APPID . "&version=525&siteid=0&QueryKeywords=". $searchTerm."&MaxEntries=" . $maxFetch ."&IncludeSelector=Items,Details";
        $data = parent::getHttpContent($url);
        $obj = json_decode($data);
        $result = array('productFamilies'=>array());
        try{
            $items = $obj->Product;
            foreach($items as $item){
                $myitem = array(
                    'title'=> $item->Title,
                    'detailURL' => $item->DetailsURL,
                    'galleryURL' => $item->StockPhotoURL
                );
                if($myitem['galleryURL'] == null){
                    $myitem['galleryURL'] = 'images/noimage.png';
                }
                $result['productFamilies'][] = $myitem;
            }
        }catch(\Exception $e){
        }
        return new Response(json_encode($result));
    }

    public function getDetailAction(){
        $url = urldecode($this->request->query->get('url'));

        if(substr( $url, 0, 27 ) == 'http://syicatalogs.ebay.com'){
            $result = parent::getHttpContent($url);
            if (preg_match ("/<body.*?>([\w\W]*?)<\/body>/", $result, $regs)) {
                $result = $regs[1];
            }

        }else{
            $data = new Result(Result::FAILURE, "domain is not correct.");
            $result = json_encode($data);
        }
        return new Response($result);
    }

    public function getCategoryAction(){
        $MySQLParser = $this->container->get('MySQLParser');
        $category = new Category();
        $arr = $MySQLParser->select($category);
        $result = array();

        foreach($arr as $item){
            $row = null;
            if($item['parentId'] != null)
                $item['cname'] = '-'.$item['cname'];
            $row->categoryId = $item['categoryId'];
            $row->categoryName = $item['cname'];
            $result[] = $row;
        }
        return new Response(json_encode($result));
    }

    public function getCategoryHierarchyAction(){
        $MySQLParser = $this->container->get('MySQLParser');
        $category = new Category();
        $arr = $MySQLParser->select($category, 'parentId is NULL');

        $result = array();
        foreach ($arr as $item) {
            if($item['childrenId']!= NULL){
                $childrenArr = $this->CategoryHierarchyRecursive($item['childrenId'], $MySQLParser);
                $item['items'] = $childrenArr;
            }
            $item['expanded'] = true;
            $result['data'][] = $item;
        }
        return new Response(json_encode($result));
    }

    public function CategoryHierarchyRecursive($childrenId, $MySQLParser){
        $category = new Category();
        $arr = $MySQLParser->select($category, 'categoryId in (' . $childrenId . ')');
        $result = array();
        foreach ($arr as $item) {
            if($item['childrenId']!= NULL){
                $childrenArr = $this->CategoryHierarchyRecursive($item['childrenId'], $MySQLParser);
                $item['items'] = $childrenArr;
            }
            $result[] = $item;
        }
        return $result;
    }

    public function getAllChildId($categoryId, $MySQLParser){
        $category = new Category();
        $arr = $MySQLParser->select($category, 'categoryId in (' . $categoryId . ')', NULL, array('categoryId', 'childrenId'));
        $result = array();
        foreach ($arr as $item) {
            if($item['childrenId']!= NULL){
                $childrenArr = $this->getAllChildId($item['childrenId'], $MySQLParser);
                $result = array_merge($result, $childrenArr);
            }
            $result[] = $item['categoryId'];
        }
        return $result;
    }

    public function getProductByCategoryAction($categoryId){
        $page = $this->request->query->get('page');
        $pageSize = $this->request->query->get('pageSize');
        $MySQLParser = $this->container->get('MySQLParser');
        if($categoryId != 'all'){
            $categoryId = intval($categoryId);
            $arr = $this->getAllChildId($categoryId, $MySQLParser);
            $categoryId = implode(',', $arr);
            $sql = "SELECT Product.pid, Product.pname, Product.buyNowPrice, Product.currentPrice, Product.startPrice, Product.defaultImage, Product.endTime, Product.categoryId, Product.shippingType, Product.shippingCost, Product.auction, Product.seller, Product.`condition`, Product.`status`, User.uid, User.username FROM " . _table('Product') ." AS Product INNER JOIN " . _table('User'). " AS User ON User.uid = Product.seller WHERE (Product.endTime - now()) > 0 AND (Product.status = 0 OR Product.status = 1)  AND categoryId IN (" .$categoryId . ") ";
        }
        else{
            $sql = "SELECT Product.pid, Product.pname, Product.buyNowPrice, Product.currentPrice, Product.startPrice, Product.defaultImage, Product.endTime, Product.categoryId, Product.shippingType, Product.shippingCost, Product.auction, Product.seller, Product.`condition`, Product.`status`, User.uid, User.username FROM " . _table('Product') ." AS Product INNER JOIN " . _table('User'). " AS User ON User.uid = Product.seller WHERE (Product.endTime - now()) > 0 AND (Product.status = 0 OR Product.status = 1) ";
        }
        $start = ($page - 1) * $pageSize;
        $sql .= ' LIMIT '. $start . ' , ' . $pageSize;


        $result = $MySQLParser->query($sql);
        $result = new Result(Result::SUCCESS, "fetch product list successfully", $result);
        //calc total
        if($categoryId != 'all'){
            $sql = "SELECT COUNT(*) FROM " . _table('Product') ." WHERE categoryId IN (" .$categoryId . ") AND (endTime - now()) > 0 AND (status = 0 OR status = 1)";
        }else{
            $sql = "SELECT COUNT(*) FROM " . _table('Product') . " WHERE (endTime - now()) > 0 AND (status = 0 OR status = 1)";
        }
        $count = $MySQLParser->query($sql);
        $result->total = intval($count[0]['COUNT(*)']);
        return new Response(json_encode($result));
    }
    
    public function getProductByIdAction($itemId){
        $url = 'http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=' . AjaxController::APPID .'&%20siteid=0&version=515&ItemID=' . $itemId .'&IncludeSelector=Description,ItemSpecifics';
        $data = parent::getHttpContent($url);
        $obj = json_decode($data);
        $result = array();
        try{
            $item = $obj->Item;
            $result = array(
                'Description'=> $item->Description,
                'PictureURL' => $item->PictureURL,
                'GalleryURL' => $item->GalleryURL
            );
        }catch(\Exception $e){
        }
        return new Response(json_encode($result));
    }
    
    public function uploadAction(){
        global $session;
        $this->checkAuthentication();
        $files = $this->request->files->get('images');
        $data = array();
        foreach ($files as $file){
            if(($file instanceof UploadedFile)&&($file->getError() == UPLOAD_ERR_OK)){
                $extension = strtolower($file->getClientOriginalExtension());
                $valid_filetypes = array('jpg','jpeg','bmp','png','gif');
                if(!in_array($extension, $valid_filetypes)){
                    throw new \Exception("uplod file unvalid. must be image file.");
                }
                $securityContext = $session->get("security_context");
                $user = $securityContext->getToken()->getUser();
                $username = $user->getUsername();
                $savefilename = $this->generatePicName($file->getClientOriginalName(), $file->getClientOriginalExtension(), 57);
                $file->move(
                    $this->getUploadImagesDir($username),
                    $savefilename
                );
                $name = $this->generateThumb($username, $savefilename, $file->getClientOriginalName(), $file->getClientOriginalExtension());
                $name = basename($name);
                $data[] = array(
                    'ImageName' => $file->getClientOriginalName(),
                    'ImageURL' => $this->getRelativeImageDir($username) . $name
                );
                $result = new Result(Result::SUCCESS, "file upload successfully.", $data);
            }else{
                throw new \Exception("file upload failed.");
            }

        }

        return new Response(json_encode($result));
    }
    
    public function uploadRemoveAction(){
        global $session;
        $this->checkAuthentication();
        $filename = $this->request->get('fileNames');
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        $username = $user->getUsername();
        $extension = end(explode('.', $filename));
        unlink($this->getUploadImagesDir($username) . $this->generatePicName($filename, $extension, 12));
        unlink($this->getUploadImagesDir($username) . $this->generatePicName($filename, $extension, 57));
        $result = new Result(Result::SUCCESS, "file delete successfully.");
        return new Response(json_encode($result));
    }

    public function generatePicName($filename, $extension, $num){
        return basename($filename, ".".$extension). '$_' . $num .'.'.strtoupper($extension);
    }

    public function generateThumb($username, $filename, $originalname, $extension){
        $phpThumb = new \phpThumb();
        $phpThumb->setSourceData(file_get_contents($this->getRelativeImageDir($username).$filename));
        $thumbnail_width = 350;
        $output_filename = $this->getUploadImagesDir($username). $this->generatePicName($originalname, $extension, 12);
        $phpThumb->setParameter('w', $thumbnail_width);
        if ($phpThumb->GenerateThumbnail()) {
            if ($phpThumb->RenderToFile($output_filename)) {
                return $output_filename;
            }else{
                throw new \Exception('Failed:<pre>'.implode("\n\n", $phpThumb->debugmessages).'</pre>');
            }
        }else{
            throw new \Exception('Failed:<pre>'.$phpThumb->fatalerror."\n\n".implode("\n\n", $phpThumb->debugmessages).'</pre>');
        }
    }

    function getRelativeImageDir($username){
        return 'upload/images/'. $username . '/';
    }

    
    function getUploadImagesDir($username){
        global $root;
        return $root . '/upload/images/' . $username . '/';
    }

    public function commingsoonProductListAction($size){
        $size = intval($size);
        if($size == 0){
            $size = 10;
        }
        $MySQLParser = $this->container->get('MySQLParser');
        $sql = "SELECT Product.pid, Product.pname, Product.currentPrice, Product.defaultImage, Product.endTime, Product.categoryId, User.uid, User.username FROM " . _table('Product')." AS Product INNER JOIN " . _table('User'). " AS User ON User.uid = Product.seller WHERE (Product.endTime - now()) > 0 AND (Product.status = 0 OR Product.status = 1) ORDER BY (Product.endTime - now()) limit " . $size;
        $result = $MySQLParser->query($sql);
        $result = new Result(Result::SUCCESS, "fetch list successfully.", $result);
        return new Response(json_encode($result));
    }

    public function hotBiddingProductListAction($size){
        $size = intval($size);
        if($size == 0){
            $size = 10;
        }
        $MySQLParser = $this->container->get('MySQLParser');
        $sql = "SELECT Product.pid, Product.pname, Product.currentPrice, Product.defaultImage, Product.endTime, Product.categoryId, count(*) as count, User.uid, User.username FROM " . _table('Product')." AS Product INNER JOIN " . _table('Bid')." AS Bid  ON Product.pid = Bid.pid INNER JOIN " . _table('User'). " AS User ON User.uid = Product.seller WHERE (Product.endTime - now()) > 0 AND (Product.status = 0 OR Product.status = 1) group by Product.pid order by count(*) desc limit " . $size;
        $result = $MySQLParser->query($sql);
        if(count($result) < $size){
            $num = $size - count($result);
            $pidList = array();
            foreach($result as $val){
                $pidList[] = $val['pid'];
            }
            $pids = implode(',',$pidList);

            $sql = "SELECT Product.pid, Product.pname, Product.currentPrice, Product.defaultImage, Product.endTime, Product.categoryId, User.uid, User.username FROM " . _table('Product')." AS Product INNER JOIN " . _table('User'). " AS User ON User.uid = Product.seller WHERE (Product.endTime - now()) > 0 AND (Product.status = 0 OR Product.status = 1) " ;
            if($pids != ""){
                $sql .= " AND Product.pid NOT IN ( {$pids} ) ";
            }
            $sql .="group by Product.pid desc limit " . $num;
            $result1 = $MySQLParser->query($sql);
            foreach($result1 as $val){
                $val['count'] = 0;
            }
            $result = array_merge($result , $result1);
        }
        $result = new Result(Result::SUCCESS, "fetch list successfully.", $result);
        return new Response(json_encode($result));
    }
}

?>