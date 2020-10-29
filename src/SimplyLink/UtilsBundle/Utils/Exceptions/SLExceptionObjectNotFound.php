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
class SLExceptionObjectNotFound extends BaseSLException
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
        return 'object_not_found';
    }
    
    
}