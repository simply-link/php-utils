<?php

/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 23/07/2017
 * Time: 11:26
 */
namespace SimplyLink\UtilsBundle\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseSLException
 *
 * @package SimplyLink\UtilsBundle\Utils\Exceptions
 */
abstract class BaseSLException extends \Exception
{
    
    /**
     * @return int
     */
    abstract public function getExceptionCode();
    
    /**
     * @return int
     */
    abstract public function getExceptionType();
    
    /**
     * @var string
     */
    private $userText;
    
    
    /**
     * @var string
     * String error code
     */
    protected $type;
    
    /**
     * @var array
     */
    protected $additionalInfo = array();
    
    
    // Redefine the exception so message isn't optional
    public function __construct($message,$userText, \Exception $previous = null)
    {
        parent::__construct($message,  $this->getExceptionCode() , $previous);
        $this->setUserText($userText);
    }
    
    
    
    
    
    /**
     * Convert ApiError into array
     * Use this function to prepare error for response
     * @return array
     */
    public function toArray()
    {
        return array(
            'status' => $this->getCode(),
            'type' => $this->getType(),
            'title' => $this->getMessage(),
            'userText' => $this->getUserText(),
            'additionalInfo' => $this->getAdditionalInfo()
        );
    }
    
    
    /**
     * Convert ApiError into Symfony response.
     * ApiError will convert into an array and then to JSON.
     * Response body is json of the error, and it will include statusCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse()
    {
        $json = json_encode($this->toArray());
        $response = new Response($json,$this->getCode());
        $response->headers->set('Content-Type','application/problem+json');
        return $response;
    }
    
    /**
     * @return string
     */
    public function getUserText()
    {
        return $this->userText;
    }
    
    /**
     * @param string $userText
     * @return BaseSLException
     */
    public function setUserText($userText)
    {
        $this->userText = $userText;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param string $type
     * @return BaseSLException
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }
    
    /**
     * @param array $additionalInfo
     * @return BaseSLException
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;
        return $this;
    }
    
    
    /**
     * @param $additionalInfo
     * @return BaseSLException
     */
    public function addAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo[] = $additionalInfo;
        return $this;
    }
    
    
    
    
    
    
}