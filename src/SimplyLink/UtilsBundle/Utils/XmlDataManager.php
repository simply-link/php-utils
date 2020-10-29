<?php


namespace SimplyLink\UtilsBundle\Utils;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionUnexpectedValue;

/**
 * Class XmlDataManager
 *
 * XmlDataManager contains static functions for handling ANY XML data conversion
 *
 * @package SimplyLink\UtilsBundle\Utils
 */
class XmlDataManager extends GenericDataManager
{
    /**
     * Convert string with XML format to array
     *
     * @param string $xmlString
     * @return mixed|array
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionUnexpectedValue
     */
    public static function convertXmlStringToArray($xmlString)
    {
        if(!is_string($xmlString))
            throw SLExceptionInvalidArgument::expectedString('$xmlString',$xmlString);
        
        if (self::isValidXmlString($xmlString)) {
            $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $array = json_decode($json, TRUE);
    
            return $array;
        }
        else
        {
            throw SLExceptionUnexpectedValue::expectedValidXmlString();
        }
        
        
    }

    /**
     * Check if XML string is valid XML format
     *
     * @param $xmlString string
     * @return bool
     */
    public static function isValidXmlString($xmlString)
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xmlString);
        if (!$doc) {
            return false;
        }
        return true;
    }
}
