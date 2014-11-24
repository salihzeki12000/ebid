<?php
namespace ebid\Controller;

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
                    'title'=> $item->title[0],
                    'galleryURL' => $item->galleryURL[0]
                );
                $result['productFamilies'][] = $myitem;
            }
        }catch(\Exception $e){
        }
        return new Response(json_encode($result));
    }
    
    public function getProductDetailAction($itemId){
        $url = 'http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=' . AjaxController::APPID .'&%20siteid=0&version=515&ItemID=' . $itemId .'&IncludeSelector=Description,ItemSpecifics';
        
    }
    
    public function uploadAction(){
        
        $files = $this->request->files->get('images');
        foreach ($files as $file){
            if(($file instanceof UploadedFile)&&($file->getError() == UPLOAD_ERR_OK)){
                $extension = $file->getClientOriginalExtension();
                $valid_filetypes = array('jpg','jpeg','bmp','png','gif');
                if(!in_array($extension, $valid_filetypes)){
                    throw new \Exception("uplod file unvalid. must be image file.");
                }
                $file->move(
                    $this->getUploadImagesDir(),
                    $this->getfilename($extension)
                ); 
                $result = new Result(Result::SUCCESS, "file upload successfully.");
            }

        }
        return new Response(json_encode($result));
    }
    
    public function uploadRemoveAction(){
        
    }
    
    function getfilename($extension){
        $filename = date('Ymdms'). '.'.$extension;
        return $filename;
    }
    
    function getUploadImagesDir(){
        global $root;
        return $root . '/upload/images/';
    }
}

?>