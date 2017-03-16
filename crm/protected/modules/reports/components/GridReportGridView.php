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

class GridReportGridView extends ReportGridView {
    public $itemsCssClass = 'items grid-report-items';

    public function registerClientScript() {
        parent::registerClientScript();
        if($this->enableGvSettings) {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().
                '/js/X2GridView/x2gridview.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->controller->module->getAssetsUrl ().
                    '/js/reportGridSettings.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->controller->module->getAssetsUrl ().
                    '/js/gridReportGridSettings.js', CCLientScript::POS_END);
        }
    }

    /**
     * Since grid report potentially has a large number of columns and since gvSettings request
     * param grows with column count, ajax request type is set to POST to prevent issues with GET 
     * param value length limitations 
     */
    protected function getYiiGridViewOptions () {
        return array_merge (parent::getYiiGridViewOptions (), array (
            'updateRequestType' => 'POST'
        ));
    }

    protected function getJSClassOptions () {
        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'enableColDragging' => false,
            ));
    }

}
