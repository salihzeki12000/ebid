<?php

namespace ebid\firebase;

use ebid\firebase\FirebaseInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use ebid\firebase\FirebaseTokenGenerator;

/**
 * Firebase PHP Client Library
 *
 * @author Tamas Kalman <ktamas77@gmail.com>
 * @link   https://www.firebase.com/docs/rest-api.html
 *
 */

/**
 * Firebase PHP Class
 *
 * @author Tamas Kalman <ktamas77@gmail.com>
 * @link   https://www.firebase.com/docs/rest-api.html
 *
 */
class Firebase implements FirebaseInterface
{
    private $_baseURI;
    private $_timeout;
    private $_token;

    /**
     * Constructor
     *
     * @param String $baseURI Base URI
     *
     * @return void
     */
    function __construct($baseURI = '', $generator = NULL, $token = '')
    {
        if ($baseURI == '') {
            throw new Exception('You must provide a baseURI variable.', E_USER_ERROR);
        }

        //if (!extension_loaded('curl')) {
        //   throw new Exception('Extension CURL is not loaded.', E_USER_ERROR);
        //}

        $this->setBaseURI($baseURI);
        $this->setTimeOut(10);
        //global $session;
        //$token = $session->get("firebase_token");
        if($token == null  || empty($token) ){
            $token = $generator->createToken(array("uid" => "admin"), array("admin" => True));
            //$session->set("firebase_token", $token);
        }
        $this->setToken($token);
    }

    /**
     * Sets Token
     *
     * @param String $token Token
     *
     * @return void
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * Sets Base URI, ex: http://yourcompany.firebase.com/youruser
     *
     * @param String $baseURI Base URI
     *
     * @return void
     */
    public function setBaseURI($baseURI)
    {
        $baseURI .= (substr($baseURI, -1) == '/' ? '' : '/');
        $this->_baseURI = $baseURI;
    }

    /**
     * Returns with the normalized JSON absolute path
     *
     * @param String $path to data
     */
    private function _getJsonPath($path)
    {
        $url = $this->_baseURI;
        $path = ltrim($path, '/');
        $auth = ($this->_token == '') ? '' : '?auth=' . $this->_token;
        return $url . $path . '.json' . $auth;
    }

    /**
     * Sets REST call timeout in seconds
     *
     * @param Integer $seconds Seconds to timeout
     *
     * @return void
     */
    public function setTimeOut($seconds)
    {
        $this->_timeout = $seconds;
    }

    /**
     * Writing data into Firebase with a PUT request
     * HTTP 200: Ok
     *
     * @param String $path Path
     * @param Mixed  $data Data
     *
     * @return Array Response
     */
    public function set($path, $data)
    {
      return $this->_writeData($path, $data, 'PUT');
    }

    /**
     * Pushing data into Firebase with a POST request
     * HTTP 200: Ok
     *
     * @param String $path Path
     * @param Mixed  $data Data
     *
     * @return Array Response
     */
    public function push($path, $data)
    {
      return $this->_writeData($path, $data, 'POST');
    }

    /**
     * Updating data into Firebase with a PATH request
     * HTTP 200: Ok
     *
     * @param String $path Path
     * @param Mixed  $data Data
     *
     * @return Array Response
     */
    public function update($path, $data)
    {
      return $this->_writeData($path, $data, 'PATCH');
    }

    /**
     * Reading data from Firebase
     * HTTP 200: Ok
     *
     * @param String $path Path
     *
     * @return Array Response
     */
    public function get($path)
    {
        try {
            $return = $this->rest_helper($this->_getJsonPath($path), null, null, 'GET');
            //$ch = $this->_getCurlHandler($path, 'GET');
            //$return = curl_exec($ch);
            //curl_close($ch);
        } catch (Exception $e) {
            $return = null;
        }
        return $return;
    }

    /**
     * Deletes data from Firebase
     * HTTP 204: Ok
     *
     * @param type $path Path
     *
     * @return Array Response
     */
    public function delete($path)
    {
        try {
            $return = $this->rest_helper($this->_getJsonPath($path) , null, null, 'DELETE');
            //$ch = $this->_getCurlHandler($path, 'DELETE');
            //$return = curl_exec($ch);
            //curl_close($ch);
        } catch (Exception $e) {
            $return = null;
        }
        return $return;
    }

    /**
     * Returns with Initialized CURL Handler
     *
     * @param String $mode Mode
     *
     * @return CURL Curl Handler
     */
    private function _getCurlHandler($path, $mode)
    {
        $url = $this->_getJsonPath($path);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode);
        return $ch;
    }

    private function _writeData($path, $data, $method = 'PUT')
    {
        $jsonData = json_encode($data);
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        );
        try {
            $return = $this->rest_helper($this->_getJsonPath($path) ,$header, $jsonData,$method);
            //$ch = $this->_getCurlHandler($path, $method);
            //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            //$return = curl_exec($ch);
            //curl_close($ch);
        } catch (Exception $e) {
            $return = null;
        }
        return $return;
    }

    function rest_helper($url, $optional_headers = null, $params = null, $verb = 'GET', $format = 'json')
    {
        $cparams = array(
            'http' => array(
                'method' => $verb,
                'ignore_errors' => true
            )
        );
        if ($optional_headers !== null) {
            $cparams['http']['header'] = $optional_headers;
        }
        if ($params !== null) {
            if ($verb != 'GET') {
                $cparams['http']['content'] = $params;
            } else {
                $params = http_build_query($params);
                $url .= '?' . $params;
            }
        }

        $context = stream_context_create($cparams);
        $fp = fopen($url, 'rb', false, $context);
        if (!$fp) {
            $res = false;
        } else {
            // If you're trying to troubleshoot problems, try uncommenting the
            // next two lines; it will show you the HTTP response headers across
            // all the redirects:
            // $meta = stream_get_meta_data($fp);
            // var_dump($meta['wrapper_data']);
            $res = stream_get_contents($fp);
        }

        if ($res === false) {
            throw new Exception("$verb $url failed: $php_errormsg");
        }

        switch ($format) {
            case 'json':
                $r = json_decode($res);
                if ($r === null) {
                    throw new Exception("failed to decode $res as json");
                }
                return $r;

            case 'xml':
                $r = simplexml_load_string($res);
                if ($r === null) {
                    throw new Exception("failed to decode $res as xml");
                }
                return $r;
        }
        return $res;
    }

}
