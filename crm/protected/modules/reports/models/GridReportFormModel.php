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
 * Validation for grid report form 
 */
class GridReportFormModel extends X2ReportFormModel {
    public $rowField;
    public $workflowId;
    public $columnField;
    public $cellDataType;
    public $cellDataField;

    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array (
                    'rowField, columnField, cellDataType',
                    'required',
                ),
                array (
                    'rowField, columnField', 'validateFields',
                ),
                array ('cellDataType', 'validateCellDataType'),
                array ('cellDataField', 'validateAttr', 'empty' => true),
                array ('rowField, columnField', 'validateAttr'),
            )
        );
    }

    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            'rowField' => Yii::t('reports', 'Row Field'),
            'columnField' => Yii::t('reports', 'Column Field'),
            'cellDataType' => Yii::t('reports', 'Cell Data Type'),
            'workflowId' => Yii::t('reports', 'Process'),
        ));
    }

    public function validateFields ($attribute) {
        $value = $this->$attribute;
        if ($this->primaryModelType === 'Actions' && $attribute === 'stageNumber' &&
            !isset ($this->workflowId)) {

            throw new CHttpException (400, 'Invalid workflow id');
        }
    }

    public function validateCellDataType ($attribute) {
        $value = $this->$attribute;
        if (!in_array ($value, array ('count', 'sum', 'avg'))) {
            throw new CHttpException (400, 'Invalid cell data type');
        }
        // for count, the field doesn't matter, but sum and average require the value of some 
        // attribute 
        if (in_array ($value, array ('sum', 'avg')) && !isset ($this->cellDataField)) {
            throw new CHttpException (400, 'Invalid cell data field');
        }
    }
}

?>
