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
/* @edition:pro */
/**
 * Validation for grid report form 
 */
class TimeSeriesFormModel extends ChartFormModel {
    
    public $timeField;

    public $labelField;

    public $filterType = 'trailing';

    public $filter = 'week';

    public $filterFrom = null;

    public $filterTo = null;


    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array (
                    'timeField',
                    'required',
                ),
                array(
                    'labelField, timeField',
                    'isValidColumn'
                ),
                array(
                    'timeField',
                    'isTimeField'
                ),
                // array (
                //     'reportRow, reportColumn',
                //     'validateRowColumn'
                // ),
            )
        );
    }

    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            'timeField' => Yii::t('charts', 'Dates'),
            'labelField' => Yii::t('charts', 'Categories'),
        ));
    }

    public function optional($attribute) {
    }

    public function isTimeField($attribute) {
        if ($this->hasErrors())
            return false;

        $report = X2Model::model('Reports')->findByPk($this->reportId);
        $columns = $report->setting('columns');
        $field = X2Model::model('Fields')->findByAttributes (array (
            'fieldName' => $this->$attribute,
            'modelName' => $report->setting('primaryModelType')
            )
        );

        if ($field->type == 'date' || $field->type == 'dateTime') {
            return true;
        }

        $this->addError ($attribute,
            Yii::t('charts','Date field must only contain dates')
        );

        return false;

    }


    public function getHelpItems() {
        return array(
            'timeField' => Yii::t('charts', "Choose a column on the report grid that contains dates such as 'Create Date' or 'Updated On'"),
            'labelField' => Yii::t('charts',  "Choose a column on the report grid that contains categories such as 'Status', 'Type' or 'Assigned To'")
        );
    }
}

?>
