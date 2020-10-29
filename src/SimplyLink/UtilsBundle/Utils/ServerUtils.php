<?php
/**
 * Created by PhpStorm.
 * User: Shay Altman
 * Date: 7/13/2016
 * Time: 15:14
 */

namespace SimplyLink\UtilsBundle\Utils;

/**
 * Class ServerUtils
 *
 * ServerUtils contains static functions for handling Server data shortcuts
 *
 * @package SimplyLink\UtilsBundle\Utils
 */
class ServerUtils extends SLBaseUtils
{
    /**
     * Get the sub domain of current url
     *
     * @return string|null
     */
    public static function getSubDomain()
    {
        $url = self::GetKernelContainer()->get('request_stack')->getCurrentRequest()->server->get('HTTP_HOST');
        $parsedUrl = parse_url($url);
        $host = explode('.', isset($parsedUrl['host']) ? $parsedUrl['host'] : $parsedUrl['path']);
        if(count($host) > 0)
            return $host[0];
        return null;
    }

    /**
     * Get client ip
     * @return string|null
     */
    public static function getClientIp()
    {
        $ip = null;
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ip = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }
}