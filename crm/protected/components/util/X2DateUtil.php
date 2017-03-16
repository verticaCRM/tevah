<?php
/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * Date formatting utilities 
 * 
 * @package application.components 
 */
class X2DateUtil {

	public static function dateBox($date) {
		$str = '<div class="date-box" title="';
		$str .= date('Y-m-d H:i',$date);
		
		$str .= '"><span class="month">';
		$str .= date('M',$date);
		$str .= '</span><span class="day">';
		$str .= date('d',$date);
		$str .= '</span></div>';
		return $str;
	}
	
	public static function actionDate($date,$priority,$complete='No') {
        if($complete=="No"){
            if($priority == '3')
                $priority = ' p-3';
            elseif($priority == '2')
                $priority = ' p-2';
            else
                $priority = ' p-1';
        }else{
            $priority='';
        }
		
		$str = '<div class="date-box'.$priority.'" title="';
		$str .= date('Y-m-d H:i',$date);
		
		$str .= '"><span class="month">';
		$str .= Yii::app()->getLocale()->getMonthName(date('n',$date),'abbreviated');
		// $str .= date('M',$date);
		$str .= '</span><span class="day">';
		$str .= date('d',$date);
		$str .= '</span></div>';
		return $str;
	}

    /**
     * Meant to replace getDateRange
     */
    public static function parseDateRange (
        $range=null, $startDate=null, $endDate=null, $strict=null) {

        $dateRange = array();
        $dateRange['strict'] = false;
        if (isset($strict) && $strict)
            $dateRange['strict'] = true;

        $dateRange['range'] = 'custom';
        if (isset($range))
            $dateRange['range'] = $range;

        switch ($dateRange['range']) {

            case 'thisDay':
                $dateRange['start'] = strtotime('today'); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'thisWeek':
                $dateRange['start'] = strtotime('mon this week'); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'thisMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n'), 1); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'thisQuarter':
                $retVal = self::startOfQuarter();
                $dateRange['start'] = $retVal[0];
                $dateRange['end'] = time(); // now
                break;
            case 'thisYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1);  // first of the year
                $dateRange['end'] = time(); // now
                break;


            case 'lastWeek':
                $dateRange['start'] = strtotime('mon last week'); // first of last month
                $dateRange['end'] = strtotime('mon this week') - 1;  // first of this month
                break;
            case 'lastMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n') - 1, 1); // first of last month
                $dateRange['end'] = mktime(0, 0, 0, date('n'), 1) - 1;  // first of this month
                break;
            case 'lastYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1, date('Y') - 1);  // first of last year
                $dateRange['end'] = mktime(0, 0, 0, 1, 1, date('Y')) - 1;   // first of this year
                break;

            case 'trailingDay':
                $dateRange['start'] = strtotime('- 24 hours');
                $dateRange['end'] = time(); // now
                break;
            case 'trailingWeek':
                $dateRange['start'] = strtotime('- 7 days');
                $dateRange['end'] = time(); // now
                break;
            case 'trailingMonth':
                $dateRange['start'] = strtotime('- 1 month');
                $dateRange['end'] = time(); // now
                break;
            case 'trailingQuarter':
                $dateRange['start'] = strtotime('- 3 months');
                $dateRange['end'] = time(); // now
                break;
            case 'trailingYear':
                $dateRange['start'] = strtotime('- 1 year');
                $dateRange['end'] = time(); // now
                break;


            case 'all':
                $dateRange['start'] = 0;        // every record
                $dateRange['end'] = time();
                if (isset($endDate)) {
                    $dateRange['end'] = Formatter::parseDate($endDate);
                    if ($dateRange['end'] == false)
                        $dateRange['end'] = time();
                    else
                        $dateRange['end'] = strtotime('23:59:59', $dateRange['end']);
                }
                break;

            case 'custom':
            default:
                $dateRange['end'] = time();
                if (isset($endDate)) {
                    $dateRange['end'] = Formatter::parseDate($endDate);
                    if ($dateRange['end'] == false)
                        $dateRange['end'] = time();
                    else
                        $dateRange['end'] = strtotime('23:59:59', $dateRange['end']);
                }

                $dateRange['start'] = strtotime('1 month ago', $dateRange['end']);
                if (isset($startDate)) {
                    $dateRange['start'] = Formatter::parseDate($startDate);
                    if ($dateRange['start'] == false)
                        $dateRange['start'] = strtotime('-30 days 0:00', $dateRange['end']);
                    else
                        $dateRange['start'] = strtotime('0:00', $dateRange['start']);
                }
        }
        return $dateRange;
    }


    /**
     * Legacy Method: Should be replaced with dateRangeToDates
     * 
     * This function returns a date range to be used for generating a report
     * based on the dropdown value the user selected. I think it might occur elsewhere
     * in the code and could probably be refactored.
     * @return array An array with the date range values
     */
    public static function getDateRange(
        $startKey='start',$endKey='end',$rangeKey='range', $defaultRange='custom') {

        $dateRange = array();
        $dateRange['strict'] = false;
        if (isset($_GET['strict']) && $_GET['strict'])
            $dateRange['strict'] = true;

        $dateRange['range'] = $defaultRange;
        if (isset($_GET[$rangeKey]))
            $dateRange['range'] = $_GET[$rangeKey];

        switch ($dateRange['range']) {

            case 'thisWeek':
                $dateRange['start'] = strtotime('mon this week'); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'thisMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n'), 1); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'lastWeek':
                $dateRange['start'] = strtotime('mon last week'); // first of last month
                $dateRange['end'] = strtotime('mon this week') - 1;  // first of this month
                break;
            case 'lastMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n') - 1, 1); // first of last month
                $dateRange['end'] = mktime(0, 0, 0, date('n'), 1) - 1;  // first of this month
                break;
            case 'thisYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1);  // first of the year
                $dateRange['end'] = time(); // now
                break;
            case 'lastYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1, date('Y') - 1);  // first of last year
                $dateRange['end'] = mktime(0, 0, 0, 1, 1, date('Y')) - 1;   // first of this year
                break;
            case 'all':
                $dateRange['start'] = 0;        // every record
                $dateRange['end'] = time();
                if (isset($_GET[$endKey])) {
                    $dateRange['end'] = Formatter::parseDate($_GET[$endKey]);
                    if ($dateRange['end'] == false)
                        $dateRange['end'] = time();
                    else
                        $dateRange['end'] = strtotime('23:59:59', $dateRange['end']);
                }
                break;

            case 'custom':
            default:
                $dateRange['end'] = time();
                if (isset($_GET[$endKey])) {
                    $dateRange['end'] = Formatter::parseDate($_GET[$endKey]);
                    if ($dateRange['end'] == false)
                        $dateRange['end'] = time();
                    else
                        $dateRange['end'] = strtotime('23:59:59', $dateRange['end']);
                }

                $dateRange['start'] = strtotime('1 month ago', $dateRange['end']);
                if (isset($_GET[$startKey])) {
                    $dateRange['start'] = Formatter::parseDate($_GET[$startKey]);
                    if ($dateRange['start'] == false)
                        $dateRange['start'] = strtotime('-30 days 0:00', $dateRange['end']);
                    else
                        $dateRange['start'] = strtotime('0:00', $dateRange['start']);
                }
        }
        return $dateRange;
    }

    public static function partialDateRange($input) {
        $datePatterns = array(
            array('/^(0-9)$/', '000-01-01', '999-12-31'),
            array('/^([0-9]{2})$/', '00-01-01', '99-12-31'),
            array('/^([0-9]{3})$/', '0-01-01', '9-12-31'),
            array('/^([0-9]{4})$/', '-01-01', '-12-31'),
            array('/^([0-9]{4})-$/', '01-01', '12-31'),
            array('/^([0-9]{4})-([0-1])$/', '0-01', '9-31'),
            array('/^([0-9]{4})-([0-1][0-9])$/', '-01', '-31'),
            array('/^([0-9]{4})-([0-1][0-9])-$/', '01', '31'),
            array('/^([0-9]{4})-([0-1][0-9])-([0-3])$/', '0', '9'),
            array('/^([0-9]{4})-([0-1][0-9])-([0-3][0-9])$/', '', ''),
        );

        $inputLength = strlen($input);

        $minDateParts = array();
        $maxDateParts = array();

        if ($inputLength > 0 && preg_match($datePatterns[$inputLength - 1][0], $input)) {

            $minDateParts = explode('-', $input . $datePatterns[$inputLength - 1][1]);
            $maxDateParts = explode('-', $input . $datePatterns[$inputLength - 1][2]);

            $minDateParts[1] = max(1, min(12, $minDateParts[1]));
            $minDateParts[2] = max(1, min(cal_days_in_month(CAL_GREGORIAN, $minDateParts[1], $minDateParts[0]), $minDateParts[2]));

            $maxDateParts[1] = max(1, min(12, $maxDateParts[1]));
            $maxDateParts[2] = max(1, min(cal_days_in_month(CAL_GREGORIAN, $maxDateParts[1], $maxDateParts[0]), $maxDateParts[2]));

            $minTimestamp = mktime(0, 0, 0, $minDateParts[1], $minDateParts[2], $minDateParts[0]);
            $maxTimestamp = mktime(23, 59, 59, $maxDateParts[1], $maxDateParts[2], $maxDateParts[0]);

            return array($minTimestamp, $maxTimestamp);
        } else
            return false;
    }

    public static function startOfQuarter() {
        $current_month = date('m');
        $current_year = date('Y');

        if($current_month>=1 && $current_month<=3) {
            $start_date = strtotime('1-January-'.$current_year);
            $end_date = strtotime('1-April-'.$current_year);
        } else  if($current_month>=4 && $current_month<=6) {
            $start_date = strtotime('1-April-'.$current_year);
            $end_date = strtotime('1-July-'.$current_year); 
        } else  if($current_month>=7 && $current_month<=9) {
            $start_date = strtotime('1-July-'.$current_year);
            $end_date = strtotime('1-October-'.$current_year);
        } else  if($current_month>=10 && $current_month<=12) {
            $start_date = strtotime('1-October-'.$current_year);
            $end_date = strtotime('1-Janauary-'.($current_year+1));
        }

        return array($start_date, $end_date);
    }

}
?>
