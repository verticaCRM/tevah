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

class GridReportForm extends X2ReportForm {

    /**
     * @var string $JSClass 
     */
    public $JSClass = 'GridReportForm'; 

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'GridReportFormJS' => array(
                    'baseUrl' => Yii::app()->controller->module->assetsUrl,
                    'js' => array(
                        'js/GridReportForm.js',
                    ),
                    'depends' => array ('X2ReportFormJS'),
                ),
            ));
        }
        return $this->_packages;
    }

    public function fieldDropdown (CFormModel $formModel, $attribute, array $fieldOptions) {
        $htmlOptions = array (
            'class' => 'field-dropdown',
        );
        echo $this->dropDownList ($formModel, $attribute, $fieldOptions, $htmlOptions);
        echo '</span>'; 
        echo "<span class='workflow-id-dropdown-container' style='display: none;'>"; 
        echo $this->label ($formModel, 'workflowId');
        echo $this->dropDownList (
            $formModel, 'workflowId', Workflow::getWorkflowOptions (), array (
                'class' => 'workflow-id-dropdown',
                'disabled' => 'disabled',
            ));
    }

}

?>
