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
class BarFormModel extends ChartFormModel {
    
    public $categories;
    
    public $values;

    public $groups;

    public $isRow = true;

    public $scenario = 'grid';

    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array ( 
                    'values', 
                    'required',
                    'except' => 'grid'
                ),
                array (
                    'categories', 
                    'required',
                    'except' => 'grid'
                ),
                array (
                    'groups', 
                    'columnType',
                    'types' => array('dropdown', 'assignment')
                ),
                // array (
                //     'groups', 
                //     'optional'
                // )
                // array (
                //     'reportRow, reportColumn',
                //     'validateRowColumn'
                // ),
            )
        );
    }

    public function attributeLabels () {
    return array_merge (parent::attributeLabels (), array (
            'categories' => Yii::t('charts', 'Names'),
            'values' => Yii::t('charts', 'Values'),
            'groups' => Yii::t('charts', 'Groups'),
            'isRow' => Yii::t('charts', 'Row or Column'),
        ));
    }

    public function getHelpItems() {
        return array(
            'categories' => Yii::t('charts', "Choose a report column that will appear as the labels on the bar chart, such as 'Assigned To', or 'Lead Source'"),
            'values' => Yii::t('charts', "Choose a numerical column that will appear as the bar heights such as 'count' or 'Deal Value'"),
            'groups' => Yii::t('charts', "Choose a column to further organize the chart into groups such as 'Assigned To', or 'Lead Source'"),
        );
    }


}

?>
