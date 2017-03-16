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

class ContactsNameBehavior extends CActiveRecordBehavior {

    /**
     * @var bool $overrideName
     */
    public $overwriteName = true; 

	public function events() {
		return array_merge(parent::events(),array(
			'onAfterFind'=>'afterFind',
			//'onBeforeSave'=>'beforeSave',
		));
	}

    /**
     * Sets the name field (full name) on record lookup
     */
    public function afterFind ($event) {
        $this->setName();
    }

//    /**
//     * Sets the name field (full name) before saving
//     */
//    public function beforeSave ($event) {
//        $this->setName();
//    }

    public function setName() {
        if (!isset (Yii::app()->settings) || $this->owner->name && !$this->overwriteName) return;

        $admin = Yii::app()->settings;
        if (!empty($admin->contactNameFormat)) {
            $str = $admin->contactNameFormat;
            $str = str_replace('firstName', $this->owner->firstName, $str);
            $str = str_replace('lastName', $this->owner->lastName, $str);
        } else {
            $str = $this->owner->firstName . ' ' . $this->owner->lastName;
        }
        if ($admin->properCaseNames)
            $str = Formatter::ucwordsSpecific ($str, array('-', "'", '.'), 'UTF-8');

        $this->owner->name = $str;
    }
}

?>
