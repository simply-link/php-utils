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
class SLExceptionInvalidArgument extends BaseSLException
{
    /**
     * @return int
     */
    public function getExceptionCode()
    {
        return 201;
    }
    
    public function getExceptionType()
    {
        return 'invalid_argument';
    }
    
    
    /**
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionInvalidArgument
     */
    public static function expectedString($argumentName,$argumentValue)
    {
        return self::expectedGeneric('string',$argumentName,$argumentValue);
    }

    /**
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionInvalidArgument
     */
    public static function expectedFloat($argumentName,$argumentValue)
    {
        return self::expectedGeneric('float',$argumentName,$argumentValue);
    }

    /**
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionInvalidArgument
     */
    public static function expectedInt($argumentName,$argumentValue)
    {
        return self::expectedGeneric('int',$argumentName,$argumentValue);
    }
    
    /**
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionInvalidArgument
     */
    public static function expectedObjectGotNull($argumentName,$argumentValue)
    {
        return self::expectedGeneric('object',$argumentName,$argumentValue);
    }


    /**
     * @param $expectedClass - what argument type is expected
     * @param $argumentName - the name of the argument
     * @param $argumentValue - the argument itself
     * @return SLExceptionInvalidArgument
     */
    protected static function expectedGeneric($expectedClass ,$argumentName,$argumentValue)
    {
        $typeMsg = gettype($argumentValue);
        if(gettype($argumentValue) == 'object')
        {
            $typeMsg .= ' of class ' . get_class($argumentValue);
        }
        
        if($typeMsg === null)
            $typeMsg = 'null';

        $msg = 'argument ' . $argumentName . ' expected to be ' . $expectedClass . '. given ' . $typeMsg;
        return new self($msg,'Oops - error');
    }



}