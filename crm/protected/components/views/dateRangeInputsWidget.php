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

?>

<div class="cell">
    <?php 
    echo CHtml::label ($this->startDateLabel, $this->startDateName); 
    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
    
    $this->widget('CJuiDateTimePicker',array(
        'name'=>$this->startDateName,
        'value'=>Formatter::formatDate($this->startDateValue),
        'mode'=>'date',
        'options'=>array(
            'dateFormat'=>Formatter::formatDatePicker(),
            'changeMonth'=>true,
            'changeYear'=>true,

        ), // jquery plugin options
        'htmlOptions'=>array('class'=>'start-date','width'=>20),
        'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
    ));
    ?>
</div>
<div class="cell">
    <?php 
    echo CHtml::label ($this->endDateLabel, $this->endDateName); 
    $this->widget('CJuiDateTimePicker',array(
        'name'=>$this->endDateName,
        'value'=>Formatter::formatDate($this->endDateValue),
        'mode'=>'date', 
        'options'=>array(
            'dateFormat'=>Formatter::formatDatePicker(),
            'changeMonth'=>true,
            'changeYear'=>true,
        ),
        'htmlOptions'=>array('class'=>'end-date','width'=>20),
        'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
    ));
    ?>
</div>
<div class="cell">
    <?php 
    echo CHtml::label ($this->dateRangeLabel, $this->dateRangeName);
    echo CHtml::dropDownList($this->dateRangeName,$this->dateRangeValue,array(
        'custom'=>Yii::t('charts','Custom'),
        'thisWeek'=>Yii::t('charts','This Week'),
        'thisMonth'=>Yii::t('charts','This Month'),
        'lastWeek'=>Yii::t('charts','Last Week'),
        'lastMonth'=>Yii::t('charts','Last Month'),
        'thisYear'=>Yii::t('charts','This Year'),
        'lastYear'=>Yii::t('charts','Last Year'),
        'all'=>Yii::t('charts','All Time'),
    ),array('class'=>'date-range'));
    ?>
</div>

