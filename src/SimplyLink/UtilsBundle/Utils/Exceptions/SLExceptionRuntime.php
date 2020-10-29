<?php

/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 23/07/2017
 * Time: 11:30
 */

namespace SimplyLink\UtilsBundle\Utils\Exceptions;

/**
 *
 * @package SimplyLink\UtilsBundle\Utils\Exceptions
 */
class SLExceptionRuntime extends BaseSLException
{
    /**
     * @return int
     */
    public function getExceptionCode()
    {
        return 102;
    }
    
    public function getExceptionType()
    {
        return 'internal_server_error';
    }
    
    
    /**
     * @param string $arrayKey
     * @return SLExceptionRuntime
     */
    public static function arrayKeyAlreadyExists($arrayKey)
    {
        return new self('Runtime exception -> array key already exists. key: ' . $arrayKey,'Oops - unexpected error');
    }
    
    


}