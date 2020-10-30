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
class SLExceptionInvalidFile extends BaseSLException
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
        return 'file_system_error';
    }
    
    
    /**
     * throw error for file_get_contents
     *
     * @param string $filePath
     * @return SLExceptionInvalidFile
     */
    public static function getContentError($filePath)
    {
        $userText = 'Oops - error in file';
        return new self('could not get content from file. ' . $filePath,$userText);
    }


    /**
     * throw error for file_get_contents
     *
     * @param string $filePath
     * @return SLExceptionInvalidFile
     */
    public static function getFileExistsError($filePath)
    {
        $userText = 'Oops - file does not exists';
        return new self('file does not exists in path: ' . $filePath,$userText);
    }


}