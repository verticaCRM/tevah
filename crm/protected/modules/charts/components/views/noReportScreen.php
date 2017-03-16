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

/* @edition:pro */

/**
 * This files Creates a semi-transparent screen around a chart form for when there are no reports 
 * to choose from
 * CSS can be found in ChartForm.scss
 */

$reportsUrl = Yii::app()->createUrl('savedReports');

$screen = "<div class='opaque-screen'>
			<a href='$reportsUrl'> Create a Report </a> <br/>
			to get Started!
		    <div>";

// Remove Carriage Returns
$screen = preg_replace( "/\r|\n/", "", $screen );

$formName = $this->getName();

Yii::app()->clientScript->registerScript("${formName}ScreenJS","
	$('.chart-form form#$formName').css('position','relative');
	$('.chart-form form#$formName').append(\"$screen\");
", CClientScript::POS_END);
?>
