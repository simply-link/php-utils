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
class SLExceptionPageNotFound extends BaseSLException
{
    /**
     * @return int
     */
    public function getExceptionCode()
    {
        return 404;
    }
    
    
    public function getExceptionType()
    {
        return 'about:blank';
    }
    
    
    public function __construct()
    {
        $msg = 'Not Found';
        parent::__construct($msg,'Oops - page not found');
    }


}