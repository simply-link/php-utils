<?php


namespace Simplylink\UtilsBundle\Utils;

use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;


/**
 * Class CsvDataManager
 *
 * CsvDataManager contains static functions for handling ANY CSV data conversion
 *
 * @package Simplylink\UtilsBundle\Utils
 */
class CsvDataManager extends GenericDataManager
{
    
    /**
     * Convert string in CSV format to array
     * 
     * @param string $content Content must be a valid CSV string
     * @param boolean $titlesIncluded The first row is titles
     * @return array Return the CSV data in $content as array
     * @throws SLExceptionInvalidArgument
     */
    public static function convertCsvStringToArray($content, $titlesIncluded = true)
    {
        if(!is_string($content))
            throw SLExceptionInvalidArgument::expectedString('$content',$content);


        $csvArray = self::parseCsvToArray($content);

        if(count($csvArray)==0)
        {
            return array();
        }

        $fields=[];

        if($titlesIncluded)
            $fields = $csvArray[0];

        $result = array();
        for ($i= (($titlesIncluded) ? 1 : 0) ; $i < count($csvArray); $i++)
        { 
            $itemRow = array();
            for ($j=0; $j < count($csvArray[$i]); $j++) 
            {
                if($titlesIncluded)
                    $key = (string)$fields[$j];
                else
                    $key = $j;

                $val = (string)$csvArray[$i][$j];
                $itemRow[$key] = $val;
            }	
            array_push($result, $itemRow);
        }

        return $result;
    }


    /**
     * @param string $csv_string
     * @param string $delimiter
     * @param bool $skip_empty_lines
     * @param bool $trim_fields
     * @return array
     * @throws SLExceptionInvalidArgument
     */
    private static function parseCsvToArray ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
    {
        if(!is_string($csv_string))
            throw SLExceptionInvalidArgument::expectedString('$csv_string',$csv_string);

        return array_map(
            function ($line) use ($delimiter, $trim_fields) {
                return array_map(
                    function ($field) {
                        #return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
                        return $field;
                    },
                    $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line)
                );
            },
            preg_split(
                $skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s',
                preg_replace_callback(
                    '/"(.*?)"/s',
                    function ($field) {
                        return urlencode(utf8_encode($field[1]));
                    },
                    $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string)
                )
            )
        );
    }

    /**
     * Convert array to string in CSV format
     * 
     * @param array $array
     * @return string Return CSV format string
     * @throws SLExceptionInvalidArgument
     */
    public static function convertArrayToCSVString(array $array)
    {
        $csvString = '';
        $csvArray = self::fixArrayForCSVToString($array);

        foreach ($csvArray as $row)
        { 
            $nextRowsValues = array();
            while(!self::isArrayValuesEmpty($row))
            {
                foreach ($row as $value) 
                {
                    $value = self::getCsvStringForMultipleRows($value,$nextRowsValues);
                    $csvString .= '"' . $value . '",'; // wrap value in commas
                }

                if(strlen($csvString) > 0)
                {
                    substr($csvString, 0, -1); // remove last comma in row
                }
                $csvString .= PHP_EOL; // append row

                // reset rows
                $row = $nextRowsValues;
                $nextRowsValues = array();
            }
        }

        return $csvString;
    }

    /**
     * Check if given array contains keys & values
     *
     * if yes - convert to simple array with first row as fields names (keys)
     * 
     * @param array $array
     * @return array - fixed array without keys
     */
    private static function fixArrayForCSVToString(array $array)
    {
        $fixedArray = array();

        if(count($array) > 0)
        {
            $fields = array();
            $fillFields = true;
            if(parent::isArrayAssoc($array[0]))
            {
                foreach ($array as $row)
                { 
                    $data = array();
                    foreach ($row as $key => $value) 
                    {
                        if($fillFields)
                        {
                            array_push($fields, $key);
                        }
                        array_push($data, $value);
                    }
                    if($fillFields)
                    {
                        array_push($fixedArray, $fields);
                        $fillFields = false;
                    }
                    array_push($fixedArray, $data);
                }
            }
            else
            {
                $fixedArray = $array;
            }
        }

        return $fixedArray;
    }

    /**
     * check if $string is an array and fill $nextRow values
     *
     * convert final value to CSV formatted string
     * 
     * @param string $string - value
     * @param array $nextRow - next row values
     * @return string - CSV string
     * @throws SLExceptionInvalidArgument
     */
    private static function getCsvStringForMultipleRows($string,&$nextRow)
    {
        $nextRowValue = "";
        if(is_array($string) && count($string) > 0)
        {   
            $value = array_shift($string);
            $nextRowValue = $string;
        }
        else if(is_array($string))
        {
            $value = "";
        }
        else // $string is not an array
        {
            $value = $string;
        }

        array_push($nextRow, $nextRowValue);
        return self::convertToCsvString($value);
    }


    /**
     * check if row values are empty.
     * check if value is not an empty array or an empty string
     * 
     * @param array $array - row values
     * @return boolean - is the next row empty
     */
    private static function isArrayValuesEmpty(array $array)
    {
        foreach ($array as $value) 
        {
            if((is_array($value) && count($value) > 0) || (is_string($value) && strlen($value) > 0))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * fix unsupported chars for CSV format in a value
     * 
     * @param string $string - value
     * @return string - formatted CSV string
     * @throws SLExceptionInvalidArgument
     */
    public static function convertToCsvString($string)
    {
        $string = (string)$string;
        if(!is_string($string))
            throw SLExceptionInvalidArgument::expectedString('$string',$string);

        if(strlen($string) === 0)
        {
            return "";
        }
        $string = str_replace('"', '""', $string);
        $string = str_replace(PHP_EOL, '', $string);
        $string = str_replace("\r", "", $string);
        $string = str_replace("\n", "", $string);
        return $string;
    }

}
