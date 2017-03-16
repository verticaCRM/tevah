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

Yii::import('application.models.embedded.*');

/**
 * Authentication data for using a Twitter app to enable Twitter integration.
 *
 * @package application.models.embedded
 */
class TwitterApp extends JSONEmbeddedModel {

    public $oauthAccessToken = '';
    public $oauthAccessTokenSecret = '';
    public $consumerKey = '';
    public $consumerSecret = '';

    public function attributeLabels(){
        return array(
            'oauthAccessToken' => Yii::t('app','Access Token'),
            'oauthAccessTokenSecret' => Yii::t('app','Access Token Secret'),
            'consumerKey' => Yii::t('app','Consumer Key (API Key)'),
            'consumerSecret' => Yii::t('app','Consumer Secret (API Secret)'),
        );
    }

    public function modelLabel() {
        return Yii::t('app','Twitter App');
    }

    public function renderInput ($attr) {
        echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr, array (
            'class' => 'twitter-credential-input',
        )));
    }

    public function renderInputs(){
        $this->attributes = array (
            'oauthAccessToken' => null,
            'oauthAccessTokenSecret' => null,
            'consumerKey' => null,
            'consumerSecret' => null,
        );
        echo CHtml::activeLabel($this, 'consumerKey');
        $this->renderInput ('consumerKey');
        echo CHtml::activeLabel($this, 'consumerSecret');
        $this->renderInput ('consumerSecret');
        echo CHtml::activeLabel($this, 'oauthAccessToken');
        $this->renderInput ('oauthAccessToken');
        echo CHtml::activeLabel($this, 'oauthAccessTokenSecret');
        $this->renderInput ('oauthAccessTokenSecret');
        echo CHtml::errorSummary($this);
    }

    public function rules(){
        return array(
            array('oauthAccessToken,oauthAccessTokenSecret,consumerKey,consumerSecret', 'required'),
        );
    }

}

?>
