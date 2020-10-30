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
class SLExceptionLogic extends BaseSLException
{
    /**
     * @return int
     */
    public function getExceptionCode()
    {
        return 101;
    }
    
    public function getExceptionType()
    {
        return 'internal_server_error';
    }


}