<?php


namespace SimplyLink\UtilsBundle\Utils;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;


/**
 * Class GenericDataManager
 *
 * GenericDataManager contains static functions for handling generic data conversion
 *
 * @package SimplyLink\UtilsBundle\Utils
 */
class GenericDataManager extends SLBaseUtils
{
    /**
     * is array associative(key/value) array or just list of values
     * 
     * @param array $array
     * @return boolean
     */
    public static function isArrayAssoc(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Get Value from array by key. if not exists return NULL
     * 
     * @param array $array array contains th key
     * @param string $key The key stored int he array
     * @param mixed $returnOnFail Return object on failure
     * @return string|array|null - value
     * @throws SLExceptionInvalidArgument
     */
    public static function getArrayValueForKey($array,$key,$returnOnFail = NULL)
    {
        if(!is_string($key))
            throw SLExceptionInvalidArgument::expectedString('$key',$key);

        if($array && array_key_exists($key, $array))
        {
            return $array[$key];
        }
        return $returnOnFail;
    }

    /**
     * Check if string contains and start with needle
     *
     * @param string $string
     * @param string $needle
     * @return bool - string is start with needle
     * @throws SLExceptionInvalidArgument
     */
    public static function stringStartsWith($string, $needle) {
        if(!is_string($string))
            throw SLExceptionInvalidArgument::expectedString('$string',$string);

        if(!is_string($needle))
            throw SLExceptionInvalidArgument::expectedString('$needle',$needle);

        $haystack=$string;
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Check if string contains and end with needle
     *
     * @param string $string
     * @param string $needle
     * @return bool - string is end with needle
     * @throws SLExceptionInvalidArgument
     */
    public static function stringEndsWith($string, $needle) {
        if(!is_string($string))
            throw SLExceptionInvalidArgument::expectedString('$string',$string);

        if(!is_string($needle))
            throw SLExceptionInvalidArgument::expectedString('$needle',$needle);

        $haystack=$string;
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * convert any string to camelCaseString
     * 
     * @param string $str
     * @param array $noStrip
     * @return string
     * @throws SLExceptionInvalidArgument
     */
    public static function convertStringToCamelCase($str, array $noStrip = [])
    {
        if(!is_string($str))
            throw SLExceptionInvalidArgument::expectedString('$str',$str);

        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);

        return $str;
    }


    /**
     * Get data from string by regex.
     * using preg_match_all() function
     *
     * @param string $data
     * @param string $regex
     * @param boolean $getFirstMatchOnly
     * @return array
     * @throws SLExceptionInvalidArgument
     */
    public static function getDataFromRegex($data,$regex,$getFirstMatchOnly = false)
    {
        if(!is_string($data))
            throw SLExceptionInvalidArgument::expectedString('$data',$data);

        if(!is_string($regex))
            throw SLExceptionInvalidArgument::expectedString('$regex',$regex);

        preg_match_all($regex,$data,$matches);
        $matchesArray = (!$getFirstMatchOnly) ? $matches : array() ;
        if(count($matches) > 0 && $getFirstMatchOnly)
        {
            foreach ($matches[0] as $match)
            {
                $matchesArray[] = trim($match);
            }
        }
        return $matchesArray;
    }


    /**
     * Get first element value from array.
     * if not exists -> return null
     *
     * @param array $array
     * @param mixed $returnOnFail Return object on failure
     * @return string|null|object
     */
    public static function getFirstElementOfArray(array $array,$returnOnFail = NULL)
    {
        if($array && is_array($array) && count($array) > 0)
            return $array[0];
        return $returnOnFail;
    }


    /**
     * Shuffle Array values into new array
     * @param array $array
     * @return array
     */
    public static function shuffleArray(array $array)
    {
        $shuffle = [];

        while(count($array) > 0)
        {
            $index = rand(0,count($array)-1);
            $shuffle[count($shuffle)] = $array[$index];

            // remove item at index
            unset($array[$index]);

            // 'reindex' array
            $array = array_values($array);
        }

        return $shuffle;
    }


    /**
     * check if number is decimal
     * @param float $val
     * @return bool - true if decimal
     */
    public static function isDecimal($val)
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    }

    
    

    /**
     * Lower string char only if $str is string.
     * if not - return $returnOnFail
     *
     * @param $str
     * @param null $returnOnFail
     * @return null|string
     */
    public static function strToLowerIfNotNull($str,$returnOnFail = null)
    {
        if(is_string($str))
            return strtolower($str);
        return $returnOnFail;
    }


    /**
     * @param string $url
     * @return bool
     * @throws SLExceptionInvalidArgument
     */
    public static function isUrlValid($url)
    {
        if(!is_string($url))
            throw SLExceptionInvalidArgument::expectedString('$url',$url);

        if(filter_var($url, FILTER_VALIDATE_URL))
            return true;

        return false;
    }


    /**
     * Convert string value to boolean value
     *
     * All strings converted to lowercase before the conversion to boolean
     *
     * @param string $string This parameter must be a string with values: "1","0","True","False"
     * @return bool Return boolean value for string
     */
    public static function convertStringToBool($string)
    {
        if(is_string($string) && strlen($string) > 0)
        {
            $string = strtolower($string);
            if($string == '1' || $string == 'true')
                return true;
            else if($string == '0' || $string == 'false')
                return false;
        }

        if(is_int($string))
        {
            return ($string > 0);
        }

        return false;
    }
    
    
    /**
     * Truncate long string to $maxLength
     *
     * If the string is not longer then $maxLength -> return string
     *
     * @param string $string The string needs to be truncate
     * @param integer $maxLength Max number of length for string
     * @param string $extension The end of truncate string, default is "..."
     * @return string
     * @throws SLExceptionInvalidArgument
     */
    public static function truncateString($string, $maxLength, $extension = '...')
    {
        if($string === null)
            return $string;
    
        if(!is_string($extension))
            throw SLExceptionInvalidArgument::expectedString('$extension',$extension);
    
    
        if(!is_string($string))
            throw SLExceptionInvalidArgument::expectedString('$string',$string);
    
        if(!is_int($maxLength))
            throw SLExceptionInvalidArgument::expectedInt('$maxLength',$maxLength);
        
        if(strlen($string) > $maxLength)
            $string = substr($string,0,($maxLength - strlen($extension))) . $extension;
        
        return $string;
    }
    
    
    /**
     * Check if a string is a valid Base64 encoded
     *
     * @param string $string - Base 64 string
     * @return bool
     */
    public static function isValidBase64($string)
    {
        $data = explode( ',', $string );
        $string = end($data);
        
        $decoded = base64_decode($string, true);
        
        // Check if there is no invalid character in string
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) return false;
        
        // Decode the string in strict mode and send the response
        if (!base64_decode($string, true)) return false;
        
        // Encode and compare it to original one
        if (base64_encode($decoded) != $string) return false;
        
        return true;
    }
    
    
    /**
     * Check if string contains the needle.
     *
     * @param string $string
     * @param string $needle
     * @return bool - true if needle found in string
     * @throws SLExceptionInvalidArgument
     */
    public static function stringContains($string,$needle)
    {
        if(!is_string($string))
            throw SLExceptionInvalidArgument::expectedString('$string',$string);
    
        if(!is_string($needle))
            throw SLExceptionInvalidArgument::expectedString('$needle',$needle);
        
        if (strpos($string, $needle) !== false) {
            return true;
        }
        return false;
    }
    
    
    /**
     * If string is an empty string then convert it to null
     *
     * @param $string
     * @return null | string
     * @throws SLExceptionInvalidArgument
     */
    public static function convertStringToNullIfEmpty($string)
    {
        if($string != null)
        {
            if(!is_string($string))
                throw SLExceptionInvalidArgument::expectedString('$string',$string);
            
            if(strlen($string) == 0)
                return null;
            
            return $string;
        }
        
        return null;
    }
    
}

