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
class SLExceptionAuthentication extends BaseSLException
{
    /**
     * @return int
     */
    public function getExceptionCode()
    {
        return 401;
    }
    
    public function getExceptionType()
    {
        return 'authentication_required';
    }
    
    
    
    public function __construct()
    {
        $msg = 'Authentication is required. Please provide user credentials';
        parent::__construct($msg,'This action require authentication');
    }


}