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
 * Model class for Generic settings with structure defined in embedded model class
 */

class Settings extends CActiveRecord {

    public function tableName () {
        return 'x2_settings';
    }

    /**
     * Returns the static model of the specified AR class.
     * @return Profile the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

	public function behaviors(){
		return array(
			'JSONEmbeddedModelFieldsBehavior' => array(
				'class' => 'application.components.JSONEmbeddedModelFieldsBehavior',
				'transformAttributes' => array ('settings'),
				'templateAttr' => 'embeddedModelName',
				'encryptedFlagAttr' => false,
			),
		);
	}

}

?>
