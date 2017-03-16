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
 * Generates a marketing campaign based on an Account Report.
 */
class AccountCampaignAction extends CAction {

    /**
     * The first part of this is functionally idential to {@link ServicesReportAction}
     * and documentation on that can be seen there, the rest just generates a static
     * list and a marketing campaign rather than returning a dataprovider.
     */
    public function run(){
        $_GET = json_decode($_SESSION['accountsReport']['GET'], true);
        $dateRange = X2DateUtil::getDateRange();
        if(isset($_GET['dateField'], $_GET['start'], $_GET['end'], $_GET['range'])){
            if(isset($_GET['sort'])){
                $_SESSION['accountsReportSort'] = str_replace('.', ' ', $_GET['sort']);
            }
            $dateField = $_GET['dateField'];
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $attributeConditions = "($dateField BETWEEN $startDate AND $endDate)";
            $criteria = new CDbCriteria;
            if(isset($_GET['Accounts'], $_GET['Accounts']['attribute'], $_GET['Accounts']['comparison'], $_GET['Accounts']['value'])){
                $filters = $_GET['Accounts'];
                for($i = 0; $i < count($filters['attribute']); $i++){
                    $attribute = $filters['attribute'][$i];
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
                                    break;
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
            if(isset($_POST['listType']) && $_POST['listType'] == 'all'){
                $ids = array();
                $relationships = Relationships::model()->findAllBySql("SELECT * FROM x2_relationships WHERE (firstType=\"Accounts\" AND firstId IN (SELECT id FROM x2_accounts WHERE $attributeConditions) AND secondType=\"Contacts\") OR (secondType=\"Accounts\" AND secondId IN (SELECT id FROM x2_accounts WHERE $attributeConditions) AND firstType=\"Contacts\")", $criteria->params);
                foreach($relationships as $relationship){
                    list($idAttr, $typeAttr) = ($relationship->firstId == $this->id && $relationship->firstType == 'Accounts') ? array('secondId', 'secondType') : array('firstId', 'firstType');
                    if(!empty($relationship->$idAttr)){
                        $ids[] = $relationship->$idAttr;
                    }
                }
            }else{
                $ids = Yii::app()->db->createCommand()
                        ->select('id')
                        ->from('x2_contacts')
                        ->where('nameId IN (SELECT primaryContact FROM x2_accounts WHERE '.$attributeConditions.')', $criteria->params)
                        ->queryColumn();
            }
            $now=time();
            $number = Yii::app()->db->createCommand()
                    ->select('COUNT(*)')
                    ->from('x2_campaigns')
                    ->where('name LIKE "%Mailing for Account Report%"')
                    ->queryScalar()+1;
            //create static list
            $list = new X2List;
            $list->name = Yii::t('marketing', '{contacts} for {account} Report ('.$number.')', array(
                '{contacts}' => Modules::displayName(true, 'Contacts'),
                '{account}' => Modules::displayName(false, 'Accounts'),
            ));
            $list->modelName = 'Contacts';
            $list->type = 'campaign';
            $list->count = count($ids);
            $list->visibility = 1;
            $list->assignedTo = Yii::app()->user->getName();
            $list->createDate = $now;
            $list->lastUpdated = $now;

            //create campaign
            $campaign = new Campaign;
            $campaign->name = Yii::t('marketing', 'Mailing for {account} Report ('.$number.')', array(
                '{account}' => Modules::displayName(false, 'Accounts'),
            ));
            $campaign->type = 'Email';
            $campaign->visibility = 1;
            $campaign->assignedTo = Yii::app()->user->getName();
            $campaign->createdBy = Yii::app()->user->getName();
            $campaign->updatedBy = Yii::app()->user->getName();
            $campaign->createDate = $now;
            $campaign->lastUpdated = $now;

            $transaction = Yii::app()->db->beginTransaction();
            try{
                if(!$list->save())
                    throw new Exception(array_shift(array_shift($list->getErrors())));
                $campaign->listId = $list->name."_".$list->id;
                if(!$campaign->save())
                    throw new Exception(array_shift(array_shift($campaign->getErrors())));

                foreach($ids as $id){
                    $listItem = new X2ListItem;
                    $listItem->listId = $list->id;
                    $listItem->contactId = $id;
                    if(!$listItem->save())
                        throw new Exception(array_shift(array_shift($listItem->getErrors())));
                }
                $transaction->commit();
                Yii::app()->controller->redirect(Yii::app()->controller->createUrl('/marketing/marketing/update', array('id' => $campaign->id)));
            }catch(Exception $e){
                $transaction->rollBack();
                Yii::app()->user->setFlash('error', Yii::t('marketing', 'Could not create mailing').': '.$e->getMessage());
                Yii::app()->controller->redirect(Yii::app()->request->getUrlReferrer());
            }
        }
    }

}

?>
