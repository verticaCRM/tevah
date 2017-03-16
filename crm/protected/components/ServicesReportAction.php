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
 * Generates a list of service cases within a user specified date range and matching
 * particular criteria, can then be exported to a CSV file.
 */
class ServicesReportAction extends CAction {

    public function run(){
        $model = X2Model::model('Services');
        $_SESSION['serviceReport']=array();
        $_SESSION['serviceReport']['serviceReportFile']='serviceReport.csv';
        $dateRange = X2DateUtil::getDateRange();
        // Get a list of valid possible date fields to set a date range for.
        $dateFieldQuery = Yii::app()->db->createCommand()
                ->select('fieldName, attributeLabel')
                ->from('x2_fields')
                ->where('modelName="Services" AND (type="date" OR type="dateTime")')
                ->queryAll();
        $dateFields = array();
        foreach($dateFieldQuery as $row){
            $dateFields[$row['fieldName']] = $model->getAttributeLabel($row['fieldName']);
        }
        // Select a list of all valid fields for Services
        $fieldNames = Yii::app()->db->createCommand()
                ->select('fieldName')
                ->from('x2_fields')
                ->where('modelName="Services"')
                ->queryColumn();
        if(isset($_GET['dateField'], $_GET['start'], $_GET['end'], $_GET['range'])){
            if(isset($_GET['sort'])){
                // Need to replace the way Yii generates sort string labels
                $_SESSION['servicesReportSort'] = str_replace('.', ' ', $_GET['sort']);
            }
            $dateField = $_GET['dateField'];
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $attributeConditions = "($dateField BETWEEN $startDate AND $endDate)";
            $criteria = new CDbCriteria;
            // Check for GET parameter filter conditions
            if(isset($_GET['Services'], $_GET['Services']['attribute'], $_GET['Services']['comparison'], $_GET['Services']['value'])){
                $filters = $_GET['Services'];
                // Loop through the filters and convert data to machine readable
                for($i = 0; $i < count($filters['attribute']); $i++){
                    $attribute = $filters['attribute'][$i];
                    $comparison = $filters['comparison'][$i];
                    $value = $filters['value'][$i];
                    foreach(X2Model::model('Services')->fields as $field){
                        if($field->fieldName == $attribute){
                            switch($field->type){
                                case 'date':
                                case 'dateTime':
                                    if(ctype_digit((string) $value) || (substr($value, 0, 1) == '-' && ctype_digit((string) substr($value, 1))))
                                        $value = (int) $value;
                                    else
                                        $value = strtotime($value);
                                    $dateType = true;
                                    break;
                                case 'link':
                                    if(!ctype_digit((string) $value))
                                        $value = Fields::getLinkId($field->linkType, $value); break;
                                case 'boolean':
                                case 'visibility':
                                    $value = in_array(strtolower($value), array('1', 'yes', 'y', 't', 'true')) ? 1 : 0;
                                    break;
                            }
                            break;
                        }
                    }
                    // Add criteria based on the selected comparison operator
                    switch($comparison){
                        case '=':
                            $criteria->compare($attribute, $value, false);
                            break;
                        case '>':
                            $criteria->compare($attribute, '>='.$value, true);
                            break;
                        case '<':
                            $criteria->compare($attribute, '<='.$value, true);
                            break;
                        case '<>': // must test for != OR is null, because both mysql and yii are stupid
                            $criteria->addCondition('('.$attribute.' IS NULL OR '.$attribute.'!='.CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount.')');
                            $criteria->params[CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount++] = $value;
                            break;
                        case 'notEmpty':
                            $criteria->addCondition($attribute.' IS NOT NULL AND '.$attribute.'!=""');
                            break;
                        case 'empty':
                            $criteria->addCondition('('.$attribute.'="" OR '.$attribute.' IS NULL)');
                            break;
                        case 'list':
                            $criteria->addInCondition($attribute, explode(',', $value));
                            break;
                        case 'notList':
                            $criteria->addNotInCondition($attribute, explode(',', $value));
                            break;
                        case 'noContains':
                            $criteria->compare($attribute, '<>'.$value, true);
                            break;
                        case 'contains':
                        default:
                            $criteria->compare($attribute, $value, true);
                    }
                }
                $attributeConditions.=" AND ".$criteria->condition;
            }
            // Set our SQL query for the data provider
            $sql = 'SELECT * FROM x2_services WHERE '.$attributeConditions;
            $count = Yii::app()->db->createCommand()
                    ->select('COUNT(*)')
                    ->from('x2_services')
                    ->where($attributeConditions, $criteria->params)
                    ->queryScalar();
            $dataProvider = new CSqlDataProvider($sql, array(
                        'totalItemCount' => $count,
                        'params' => $criteria->params,
                        'sort' => array(
                            'attributes' => $fieldNames,
                            'defaultOrder' => isset($_SESSION['servicesReportSort']) ? $_SESSION['servicesReportSort'] : "$dateField ASC"
                        ),
                        'pagination' => array(
                            'pageSize' => Profile::getResultsPerPage(),
                        ),
                    ));
        }else{
            unset($_SESSION['servicesReportSort']);
        }
        $this->controller->render('servicesReport', array(
            'dateRange' => $dateRange,
            'dateFields' => $dateFields,
            'dataProvider' => isset($dataProvider) ? $dataProvider : null,
        ));
    }

}
