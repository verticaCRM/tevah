<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/* @edition:pro */

/**
 * Behavior for creating a link for charts. 
 * Since charts do not have their own view file, 
 * the link takes the user to the corresponding report page.
 */
class ChartsLinkableBehavior extends X2LinkableBehavior {

    public $viewRoute = "/reports";

    /**
     * Generates a url to the view of the object.
     * @return string a url to the model
     */
    public function getUrl() {
        if (isset($this->owner->report)) {
            if (Yii::app()->controller instanceof CController) // Use the controller
                return Yii::app()->controller->createAbsoluteUrl($this->viewRoute, array('id' => $this->owner->report->id));
            if (empty($url)) // Construct an absolute URL; no web request data available.
                return Yii::app()->absoluteBaseUrl . '/index.php' . $this->viewRoute . '/' . $this->owner->report->id;
        }else {
            return Yii::app()->controller->createAbsoluteUrl($this->viewRoute.'/index');
        }
    }

}
