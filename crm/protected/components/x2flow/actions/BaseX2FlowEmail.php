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
 * Base action class for email related x2flow actions
 *
 * @package application.components.x2flow.actions
 */
abstract class BaseX2FlowEmail extends X2FlowAction {

    public function paramRules(){
        if(Yii::app()->isInSession){
            $credOptsDict = Credentials::getCredentialOptions(null, true);
            $credOpts = $credOptsDict['credentials'];
            $selectedOpt = $credOptsDict['selectedOption'];
            foreach($credOpts as $key => $val){
                if($key == $selectedOpt){
                    $credOpts = array($key => $val) + $credOpts; // move to beginning of array
                    break;
                }
            }
        }else{
            $credOpts = array();
        }
        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array(
                    'name' => 'from', 
                    'label' => Yii::t('studio', 'Send As:'),
                    'type' => 'dropdown',
                    'options' => $credOpts
                ),
            // 'time' => array('dateTime'),
            ));
    }

    /**
     * @param <array of strings> comma separated recipient addresses
     * @return array error flag and message
     */
    protected function checkDoNotEmailFields (InlineEmail $eml) {
        if (Yii::app()->settings->x2FlowRespectsDoNotEmail &&  
            !$eml->checkDoNotEmailFields ()) {
            return array (
                false, Yii::t('studio', 'Email could not be sent because at least one of the '.
                    'addressees has their "Do not email" attribute checked'));
        }
        return array (true, '');
    }

}
