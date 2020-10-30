<?php
/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 05/05/2016
 * Time: 2:47 PM
 */

namespace Simplylink\UtilsBundle\Utils;

/**
 * Class DateTimeUtils
 *
 * DateTimeUtils contains static functions for handling Datetime calculation and conversions
 *
 * @package Simplylink\UtilsBundle\Utils
 */
class DateTimeUtils extends SLBaseUtils
{

    /**
     * Get number of days between 2 dates
     *
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @return int days
     */
    public static function getDaysDiffBetweenDates(\DateTime $fromDate,\DateTime $toDate)
    {
        $interval = date_diff($fromDate, $toDate);
        $days = (integer)$interval->format('%a');
        return $days;
    }

    
}