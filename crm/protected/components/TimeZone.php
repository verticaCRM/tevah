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
 * Time Zone information widget.
 * 
 * A widget that displays time information (i.e. zone, current time there) 
 * specific to the contact being viewed.
 *  
 * @package application.components 
 */
class TimeZone extends X2Widget {

    //public $visibility;
    
    public $model;
    public $localTime = true;
    
    public function init() {    
        parent::init();
    }

    public function run() {
        $tzOffset = null;
        
        if($this->localTime) {    // local mode, no offset needed
            $tzOffset = 0;
        } else {
            if(!isset($this->model))
                return;
                
            $address = '';
            if(!empty($this->model->city))
                $address .= $this->model->city.', ';
            if(!empty($this->model->state))
                $address .= $this->model->state;
            if(!empty($this->model->country))
                $address .= ' '.$this->model->country;
                
            $tz = $this->model->timezone;
            
            try {
                $dateTimeZone = new DateTimeZone($tz);
            } catch (Exception $e) {
                $dateTimeZone = null;
            }
            $contactTime = new DateTime();
            if(@date_timezone_set($contactTime,$dateTimeZone)) {
                $tzOffset = $contactTime->getOffset();
                
                if(empty($this->model->timezone)) {            // if we just looked this timezone up,
                    $this->model->timezone = $tz;            // save it
                    $this->model->update(array('timezone'));
                }
            } elseif(!empty($this->model->timezone)) {        // if the messed up timezone was previously saved,
                $this->model->timezone = '';                // clear it
                $this->model->update(array('timezone'));
            }
        }
        
        if($tzOffset !== null) {
            $offsetJs = '';
            
            if(!$this->localTime) {
                
                $offset = $tzOffset;
                    
                $tzString = 'UTC';
                $tzString .= ($offset > 0)? '+' : '-';
                
                $offset = abs($offset);
                
                $offsetH = floor($offset/3600);
                $offset -= $offsetH*3600;
                $offsetM = floor($offset/60);
                
                $tzString .= $offsetH;
                if($offsetM > 0)
                    $tzString .= ':'.$offsetM;
                
                Yii::app()->clientScript->registerScript('timezoneClock','x2.tzOffset = '.($tzOffset*1000).'; x2.tzUtcOffset = " ('.addslashes($tzString).')";',CClientScript::POS_BEGIN);



                
                echo Yii::t('app','Current time in').'<br><b>'.$address.'</b>';
            }
            $clockType = Profile::getWidgetSetting('TimeZone','clockType');

            Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/clockWidget.js');

            Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/components/clockWidget.css');

            $this->render('timeZone', array('widgetSettings' => $clockType));


        } else
            echo Yii::t('app','Timezone not available');
    }

}
