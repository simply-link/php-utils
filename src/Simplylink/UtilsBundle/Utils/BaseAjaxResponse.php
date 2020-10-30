<?php
/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 22/02/2017
 * Time: 12:51
 */

namespace Simplylink\UtilsBundle\Utils;

use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;
use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime;

/**
 * Class BaseAjaxResponse
 *
 * Use this class to return any AJAX response from server to client
 * Each ajax response contain success indication, data array and errors array.
 *
 * @package Simplylink\UtilsBundle\Utils
 */
class BaseAjaxResponse extends SLBaseUtils
{

    /**
     * Represent if the Ajax request success or failed
     *
     * @var boolean
     */
    private $success = false;
    
    /**
     * Data return from the Ajax request
     *
     * Could be in any format
     *
     * @var array
     */
    private $data = array();

    /**
     * Errors from the Ajax request
     *
     * Errors can return even if the request success
     *
     * @var array
     */
    private $errors = array();
    
    
    
    /**
     * @var int
     */
    private $responseCode = 200;
    
    
    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return BaseAjaxResponse
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data Key Value pair errors
     * @return BaseAjaxResponse
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors Key Value pair errors
     * @return BaseAjaxResponse
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }


    /**
     * @param string $key
     * @param string $value
     * @return BaseAjaxResponse
     */
    public function addError($key, $value)
    {
        $this->errors[$key] = $value;
        return $this;

    }

    /**
     * Data is stored in a key - value pairs
     *
     * @param string $key
     * @param mixed $value
     * @param bool $override  Override value if key already exists. if false exception might throw
     * @return BaseAjaxResponse
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionRuntime
     */
    public function addData($key, $value, $override = false)
    {
        if(!is_string($key))
            throw SLExceptionInvalidArgument::expectedString('$key',$key);
    
        $exists = array_key_exists($key,$this->data);
        if($exists)
        {
            if(!$override)
                throw SLExceptionRuntime::arrayKeyAlreadyExists($key);
        }
        
        
        $this->data[$key] = $value;
        return $this;
    }


    /**
     * Generate AJAX response in JSON format
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws SLExceptionRuntime
     */
    public function generateJsonResponse()
    {
        return SLResponseManager::JSONResponse($this,$this->getResponseCode());
    }

    /**
     * Generate AJAX response in XML format
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws SLExceptionRuntime
     */
    public function generateXmlResponse()
    {
        return SLResponseManager::XMLResponse($this,$this->getResponseCode());
    }
    
    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }
    
    /**
     * @param int $responseCode
     * @return BaseAjaxResponse
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    

}