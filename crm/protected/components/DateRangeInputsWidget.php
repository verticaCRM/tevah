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
 * Creates a set of inputs: a start date datepicker, an end date datepicker,
 * and a date range select.
 * 
 * @package application.components
 */
class DateRangeInputsWidget extends CWidget {

    /**
     * @var string name of start date input
     */
    public $startDateName;

    /**
     * @var string label for star date input
     */
    public $startDateLabel;

    /**
     * @var mixed value of end date input
     */
    public $startDateValue;

    /**
     * @var string name of end date input
     */
    public $endDateName;

    /**
     * @var string label of end date input
     */
    public $endDateLabel;

    /**
     * @var mixed value of end date input
     */
    public $endDateValue;

    /**
     * @var string name of range select
     */
    public $dateRangeName;

    /**
     * @var string label of date range select
     */
    public $dateRangeLabel;

    /**
     * @var mixed value of date range select
     */
    public $dateRangeValue;

    public function run () {
        $this->render ('dateRangeInputsWidget');
    }


}
?>
