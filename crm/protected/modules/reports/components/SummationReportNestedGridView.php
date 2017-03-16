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

class SummationReportNestedGridView extends ReportGridView {

    public $enableResponsiveTitleBar = false;

    public $gridViewJSClass = 'summationReportNestedGvSettings';

    public function run () {
        // remove any external CSS files
        Yii::app()->clientScript->scriptMap['*.css'] = false;

        if(!isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = '';
        $this->htmlOptions['class'] .= ' x2-subgrid';

        parent::run ();
    }

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
                    '/js/summationReportNestedGridSettings.js', CCLientScript::POS_END);
        }
    }

    /**
     * Disable column dragging
     */
    protected function getJSClassOptions () {
        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'enableColDragging' => false,
            ));
    }

}

?>
