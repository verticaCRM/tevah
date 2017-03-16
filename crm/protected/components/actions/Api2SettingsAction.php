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

/* @edition:pla */

/**
 * Action for viewing/updating advanced API settings
 * 
 * @package application.components.actions
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2SettingsAction extends CAction {
    
    public function run() {
        $admin = &Yii::app()->settings;
		if (isset($_POST['Admin'])) {
			$admin->attributes = $_POST['Admin'];
			$admin->save();
		}
		$this->controller->render('api2Settings',array('model'=>$admin));
    }
}

?>
