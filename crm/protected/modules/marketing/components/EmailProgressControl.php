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
 *
 * @property integer $campaignSize The total number of items in the campaign.
 * @property array $listItems The list item IDs for this campaign (that haven't
 *  already been sent)
 * @property integer $sentCount Number of emails sent already (or undeliverable)
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailProgressControl extends X2Widget {

    private $_listItems;
    private $_sentCount;
    public $campaign;
    public $JSClass = 'EmailProgressControl'; 


    public function getCampaignSize() {
        return count($this->listItems) + $this->sentCount;
    }

    public function getListItems() {
        if(!isset($this->_listItems,$this->_sentCount)) {
            $allListItems = CampaignMailingBehavior::deliverableItems($this->campaign->list->id);
            $this->_listItems = array();
            $this->_sentCount = 0;
            foreach($allListItems as $listItem) {
                if($listItem['sent'] == 0) 
                    $this->_listItems[] = $listItem['id'];
                else
                    $this->_sentCount++;
            }
        }
        return $this->_listItems;
    }

    public function getSentCount() {
        if(!isset($this->_sentCount)) {
            $this->getListItems(); // This will set _sentCount
        }
        return $this->_sentCount;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages(), array (
                'emailProgressControl' => array(
                    'baseUrl' => $this->module->assetsUrl, 
                    'css' => array (
                        '/css/emailProgressControl.css'
                    ),
                    'js' => array(
                       '/js/emailProgressControl.js'
                    ),
                ))
            );
        }
        return $this->_packages;
    }

    public function getJSClassParams () {
        $totalEmails = count($this->listItems) + $this->sentCount;

        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge ( parent::getJSClassParams(),
                array(
                    'sentCount' => $this->sentCount, 
                    'totalEmails' => $totalEmails,
                    'listItems' => $this->listItems,
                    'sendUrl' => Yii::app()->controller->createUrl ('/marketing/marketing/mailIndividual'),
                    'campaignId' => $this->campaign->id,
                    'paused' => !(isset($_GET['launch']) && $_GET['launch'])
                )
            );
        }
        return $this->_JSClassParams;
    }

    public function run() {
        $this->render('emailProgressControl');
        $this->registerPackages ();
        $this->instantiateJSClass(true);
    }

    public function getTranslations() {
        return array(
            'confirm' => Yii::t('marketing', 'You have unsent mail in this campaign. Are you sure you want to forcibly mark this campaign as complete?'),
            'pause' => Yii::t('marketing', 'Pause'),
            'complete' => Yii::t('marketing', 'Email Delivery Complete'),
            'resume' => Yii::t('marketing', 'Resume'),
            'error' => Yii::t('marketing', 'Could not send email due to an error in the request to the server.')
        );
    }
}

?>
