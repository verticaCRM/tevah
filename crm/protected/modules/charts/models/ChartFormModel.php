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
class ChartFormModel extends CFormModel {
    

    /**
     * @var int Id of the current report
     */    
    public $reportId;
    
    /**
     * @see rules()
     */
    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array(
                    'reportId',
                    'required'
                )
            )
        );
    }


    /**
     * @see attributeLabels()
     */
    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            'reportId' => Yii::t('reports', 'Report'),
        ));
    }

    /**
     * Returns the chart Type from this class name
     * @return string Name of the chart type
     */
    public function getChartType() {
        $match = array();
        
        if( !preg_match('/(.*)FormModel/', get_class($this), $match) ) {
            return false;
        }

        return $match[1];
    }

    public function columnType($attribute, $params) {
        if (!$this->$attribute) {
            return true;
        }

        if ($this->hasErrors())
            return false;

        AuxLib::debugLogR($params);

        $report = X2Model::model('Reports')->findByPk($this->reportId);
        $field = X2Model::model('Fields')->findByAttributes (array (
            'fieldName' => $this->$attribute,
            'modelName' => $report->setting('primaryModelType')
            )
        );

        if ($field && in_array($field->type, $params['types'])) {
            return true;
        }

        $this->addError ($attribute,
            Yii::t('charts','Field type must be one of the following: {type}', array (
                '{type}'=> implode(', ', $params['types'])
            ))
        );

        return false;

    }


    public function isValidColumn($attribute) {
        if (!$this->$attribute) {
            return true;
        }

        $report = X2Model::model('Reports')->findByPk($this->reportId);
        $columns = $report->setting('columns');

        if (!in_array($this->$attribute, $columns)) {
            $this->addError ($attribute,
                Yii::t('charts','The column "{column}" was not found in the report', array('{column}' => $this->$attribute))
            );
            return false;
        }

        return true;
    }

}

?>
