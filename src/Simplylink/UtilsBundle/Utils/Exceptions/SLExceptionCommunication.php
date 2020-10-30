<?php
/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 13/04/2017
 * Time: 13:17
 */

namespace Simplylink\UtilsBundle\Utils\Exceptions;



/**
 * Use this class to create ApiError for any validation errors
 * Easy errors extraction from FORM object - use addFormErrors() function
 *
 * Class ApiErrorValidation
 */
class SLExceptionCommunication extends BaseSLException
{
    public function getExceptionCode()
    {
        return 500;
    }
    
    
    public function getExceptionType()
    {
        return 'communication_error';
    }
    
    
}