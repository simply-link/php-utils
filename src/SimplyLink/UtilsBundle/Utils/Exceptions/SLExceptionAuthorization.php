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
class SLExceptionAuthorization extends BaseSLException
{
    /**
     * @return int
     */
    public function getExceptionCode()
    {
        return 403;
    }
    
    public function getExceptionType()
    {
        return 'not_authorized';
    }
    
    
    public function __construct()
    {
        $msg = 'The provided user is not authorized for this action';
        parent::__construct($msg,'You are not authorized for this action');
    }


}