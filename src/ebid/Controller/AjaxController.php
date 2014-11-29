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
        $data = [];
        foreach ($files as $file){
            if(($file instanceof UploadedFile)&&($file->getError() == UPLOAD_ERR_OK)){
                $extension = $file->getClientOriginalExtension();
                $valid_filetypes = array('jpg','jpeg','bmp','png','gif');
                if(!in_array($extension, $valid_filetypes)){
                    throw new \Exception("uplod file unvalid. must be image file.");
                }
                $securityContext = $session->get("security_context");
                $user = $securityContext->getToken()->getUser();
                $username = $user->getUsername();

                $file->move(
                    $this->getUploadImagesDir($username),
                    $file->getClientOriginalName()
                );

                $data[] = array(
                    'ImageName' => $file->getClientOriginalName(),
                    'ImageURL' => $this->getRelativeImageDir($username) . $file->getClientOriginalName()
                );
            }

        }
        $result = new Result(Result::SUCCESS, "file upload successfully.", $data);
        return new Response(json_encode($result));
    }
    
    public function uploadRemoveAction(){
        global $session;
        $this->checkAuthentication();
        $filename = $this->request->get('fileNames');
        $securityContext = $session->get("security_context");
        $user = $securityContext->getToken()->getUser();
        $username = $user->getUsername();
        unlink($this->getUploadImagesDir($username) . $filename);
        $result = new Result(Result::SUCCESS, "file delete successfully.");
        return new Response(json_encode($result));
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
        $sql = "SELECT Product.pid, Product.pname, Product.currentPrice, Product.defaultImage, Product.endTime, Product.categoryId, count(*) as count, User.uid, User.username FROM " . _table('Product')." AS Product LEFT JOIN " . _table('Bid')." AS Bid  ON Product.pid = Bid.pid INNER JOIN " . _table('User'). " AS User ON User.uid = Product.seller WHERE (Product.endTime - now()) > 0 AND (Product.status = 0 OR Product.status = 1) group by Product.pid order by count(*) desc limit " . $size;
        $result = $MySQLParser->query($sql);
        $result = new Result(Result::SUCCESS, "fetch list successfully.", $result);
        return new Response(json_encode($result));
    }
}

?>