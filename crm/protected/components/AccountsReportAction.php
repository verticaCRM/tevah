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
 * This class is included as a pro action to generate a report of Accounts based
 * on user filters of Account fields. From here, this information can be exported
 * to a CSV {@link ExportAccountsReportAction} or a campaign can be generated
 * for the Contacts linked to these accounts {@link AccountCampaignAction}.
 */
class AccountsReportAction extends CAction {

    /**
     * This method is functionally identical to the {@link ServicesReportAction}
     * see there for more detailed comments on what's going on.
     */
    public function run(){
        $model = X2Model::model('Accounts');
        $_SESSION['accountsReport']=array();
        $_SESSION['accountsReport']['accountsReportFile']='accountsReport.csv';
        $dateRange = X2DateUtil::getDateRange();
        $sqlParams = array ();
        $dateFieldQuery = Yii::app()->db->createCommand()
                ->select('fieldName, attributeLabel')
                ->from('x2_fields')
                ->where('modelName="Accounts" AND (type="date" OR type="dateTime")')
                ->queryAll();
        $dateFields = array();
        foreach($dateFieldQuery as $row){
            $dateFields[$row['fieldName']] = $model->getAttributeLabel($row['fieldName']);
        }
        $dateRange = X2DateUtil::getDateRange();
        $dateFieldQuery = Yii::app()->db->createCommand()
                ->select('fieldName, attributeLabel')
                ->from('x2_fields')
                ->where('modelName="Accounts" AND (type="date" OR type="dateTime")')
                ->queryAll();
        $dateFields = array();
        foreach($dateFieldQuery as $row){
            $dateFields[$row['fieldName']] = $model->getAttributeLabel($row['fieldName']);
        }
        $fieldNames = Yii::app()->db->createCommand()
                ->select('fieldName')
                ->from('x2_fields')
                ->where('modelName="Accounts"')
                ->queryColumn();
        if(isset($_GET['dateField'], $_GET['start'], $_GET['end'], $_GET['range'])){
            if(isset($_GET['sort'])){
                $_SESSION['accountsReportSort'] = str_replace('.', ' ', $_GET['sort']);
            }
            $dateField = $_GET['dateField'];
            Accounts::checkThrowAttrError ($dateField);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $attributeConditions = "($dateField BETWEEN :startDate AND :endDate)";
            $sqlParams = array_merge ($sqlParams, 
                array (
                    ':startDate' => $startDate,
                    ':endDate' => $endDate));
            $criteria = new X2DbCriteria;
            if(isset($_GET['Accounts'], $_GET['Accounts']['attribute'], $_GET['Accounts']['comparison'], $_GET['Accounts']['value'])){
                $filters = $_GET['Accounts'];
                for($i = 0; $i < count($filters['attribute']); $i++){
                    $attribute = $filters['attribute'][$i];
                    Accounts::checkThrowAttrError ($attribute);
                    $comparison = $filters['comparison'][$i];
                    $value = $filters['value'][$i];
                    foreach(X2Model::model('Accounts')->fields as $field){
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
                    switch($comparison){
                        case '=':
                            $criteria->compare($attribute, $value, false, 'AND', true, false);
                            break;
                        case '>':
                            $criteria->compare($attribute, '>='.$value, true, 'AND', true, false);
                            break;
                        case '<':
                            $criteria->compare($attribute, '<='.$value, true, 'AND', true, false);
                            break;
                        case '<>': // must test for != OR is null, because both mysql and yii are stupid
                            $criteria->addCondition(
                                '('.$attribute.' IS NULL OR '.$attribute.'!='.CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount.')');
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
            $sql = 'SELECT * FROM x2_accounts WHERE '.$attributeConditions;
            $count = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_accounts')
                ->where($attributeConditions, array_merge ($criteria->params, $sqlParams))
                ->queryScalar();
            $dataProvider = new CSqlDataProvider($sql, array(
                        'totalItemCount' => $count,
                        'params' => array_merge ($criteria->params, $sqlParams),
                        'sort' => array(
                            'attributes' => $fieldNames,
                            'defaultOrder' => isset($_SESSION['accountsReportSort']) ? $_SESSION['accountsReportSort'] : "$dateField ASC"
                        ),
                        'pagination' => array(
                            'pageSize' => Profile::getResultsPerPage(),
                        ),
                    ));
        }else{
            unset($_SESSION['accountsReportSort']);
        }
        $this->controller->render('accountsReport', array(
            'dateRange' => $dateRange,
            'dateFields' => $dateFields,
            'dataProvider' => isset($dataProvider) ? $dataProvider : null,
        ));
    }

}
