<?php
/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 23/07/2017
 * Time: 11:30
 */

namespace Simplylink\UtilsBundle\Utils\Exceptions;


/**
 *
 * @package Simplylink\UtilsBundle\Utils\Exceptions
 */
class SLExceptionUnexpectedValue extends BaseSLException
{
    /**
     * @return int
     */
    public function getExceptionCode()
    {
        return 500;
    }
    
    public function getExceptionType()
    {
        return 'unexpected_value';
    }
    
    
    /**
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionUnexpectedValue
     */
    public static function expectedPositiveNumber($argumentName,$argumentValue)
    {
        $errorMsg =  'expected positive number, got ' . $argumentValue;
        return self::expectedGeneric($errorMsg,$argumentName);
    }

    /**
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionUnexpectedValue
     */
    public static function expectedUrl($argumentName,$argumentValue)
    {
        $errorMsg =  'expected valid url, got ' . $argumentValue;
        return self::expectedGeneric($errorMsg,$argumentName);
    }
    
    /**
     * @return SLExceptionUnexpectedValue
     */
    public static function expectedValidXmlString()
    {
        $errorMsg =  'expected valid XML string';
        return new self($errorMsg,'Xml string invalid');
    }
    
    /**
     * @return SLExceptionUnexpectedValue
     */
    public static function expectedValidJSONString()
    {
        $errorMsg =  'expected valid JSON string';
        return new self($errorMsg,'JSON string invalid');
    }
    
    
    /**
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionUnexpectedValue
     */
    public static function expectedNotEmptyString($argumentName,$argumentValue)
    {
        $errorMsg =  'expected not empty string, got ' . $argumentValue;
        return self::expectedGeneric($errorMsg,$argumentName);
    }

    /**
     * @param string $errorMsg
     * @param string $argumentName
     * @return SLExceptionUnexpectedValue
     */
    protected static function expectedGeneric($errorMsg ,$argumentName)
    {
        return new self('Unexpected value for variable ' . $argumentName . '. ' . $errorMsg,'Oops - unexpected value');
    }

}