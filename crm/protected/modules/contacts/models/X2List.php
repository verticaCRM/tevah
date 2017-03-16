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
 * This is the model class for table "x2_lists".
 *
 * @package application.models
 */
class X2List extends X2Model {

    /**
     * Attribute name, comparison operator and comparison value arrays for
     * criteria generation.
     *
     * @var type
     */
    public $criteriaInput;

    public $supportsWorkflow = false;

    private $_itemModel = null;

    private $_itemFields = array();

    private $_itemAttributeLabels = array();

    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * Returns the name of the associated database table
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_lists';
    }

    public function behaviors(){
        return array(
            'X2LinkableBehavior' => array(
                'class' => 'X2LinkableBehavior',
                'baseRoute' => '/contacts/contacts/list',
                'autoCompleteSource' => '/contacts/contacts/getLists',
            ),
            'X2PermissionsBehavior' => array(
                'class' => 'application.components.permissions.X2PermissionsBehavior'
            ),
            'X2FlowTriggerBehavior' => array('class' => 'X2FlowTriggerBehavior'),
        );
    }

    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, createDate, lastUpdated, modelName', 'required'),
            array('id, count, visibility, createDate, lastUpdated', 'numerical', 'integerOnly' => true),
            array('name, modelName', 'length', 'max' => 100),
            array('description', 'length', 'max' => 250),
            array('assignedTo, type, logicType', 'length', 'max' => 20),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, assignedTo, name, modelName, count, visibility, description, type, createDate, lastUpdated', 'safe', 'on' => 'search'),
        );
    }

    public function relations(){
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'listItems' => array(self::HAS_MANY, 'X2ListItem', 'listId'),
            'campaign' => array(self::HAS_ONE, 'Campaign', array('listId' => 'nameId')),
            'criteria' => array(self::HAS_MANY,'X2ListCriterion','listId'),
        );
    }

    public function attributeLabels(){
        return array(
            'id' => 'ID',
            'assignedTo' => Yii::t('contacts', 'Owner'),
            'name' => Yii::t('contacts', 'Name'),
            'description' => Yii::t('contacts', 'Description'),
            'type' => Yii::t('contacts', 'Type'),
            'logicType' => Yii::t('contacts', 'Logic Type'),
            'modelName' => Yii::t('contacts', 'Record Type'),
            'visibility' => Yii::t('contacts', 'Visibility'),
            'count' => Yii::t('contacts', 'Members'),
            'createDate' => Yii::t('contacts', 'Create Date'),
            'lastUpdated' => Yii::t('contacts', 'Last Updated'),
        );
    }

    /**
     * An array of valid comparison operators for criteria in dynamic lists
     * @return array
     */
    public static function getComparisonList(){
        return array(
            '=' => Yii::t('contacts', 'equals'),
            '>' => Yii::t('contacts', 'greater than'),
            '<' => Yii::t('contacts', 'less than'),
            '<>' => Yii::t('contacts', 'not equal to'),
            'contains' => Yii::t('contacts', 'contains'),
            'noContains' => Yii::t('contacts', 'does not contain'),
            'empty' => Yii::t('contacts', 'empty'),
            'notEmpty' => Yii::t('contacts', 'not empty'),
            'list' => Yii::t('contacts', 'in list'),
            'notList' => Yii::t('contacts', 'not in list'),
        );
    }

    public function getDefaultRoute(){
        return '/contacts/contacts/list';
    }

    public function getDisplayName ($plural=true) {
        return Yii::t('contacts', '{contact} List|{contact} Lists', array(
            (int) $plural,
            '{contact}' => Modules::displayName(false, 'Contacts'),
        ));
    }

    public function createLink(){
        if(isset($this->id))
            return CHtml::link($this->name, array($this->getDefaultRoute(), 'id' => $this->id));
        else
            return $this->name;
    }

    /**
     * When a list is deleted, remove its entries in x2_list_items
     */
    public function afterDelete(){
        CActiveRecord::model('X2ListItem')->deleteAllByAttributes(array('listId' => $this->id)); // delete all the things!

        parent::afterDelete();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search(){
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('type', $this->type, true);
        $criteria->compare('logicType', $this->logicType, true);
        $criteria->compare('modelName', $this->modelName, true);
        $criteria->compare('createDate', $this->createDate, true);
        $criteria->compare('lastUpdated', $this->lastUpdated, true);

        return new CActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                ));
    }

    // get fields for listed model
    public function getItemFields(){
        if(empty($this->_itemFields)){
            if(!isset($this->_itemModel) && class_exists($this->modelName))
                $this->_itemModel = new $this->modelName;
            $this->_itemFields = $this->_itemModel->fields;
        }
        return $this->_itemFields;
    }

    // get attribute labels for listed model
    public function getItemAttributeLabels(){
        if(empty($this->_itemAttributeLabels)){
            if(!isset($this->_itemModel) && class_exists($this->modelName))
                $this->_itemModel = new $this->modelName;
            $this->_itemAttributeLabels = $this->_itemModel->attributeLabels();
        }
        return $this->_itemAttributeLabels;
    }

    public static function load($id){
        if(!Yii::app()->params->isAdmin){
            $condition = 't.visibility="1" OR t.assignedTo="Anyone"  OR t.assignedTo="'.Yii::app()->user->getName().'"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            if(!empty($groupLinks))
                $condition .= ' OR t.assignedTo IN ('.implode(',', $groupLinks).')';

            $condition .= 'OR (t.visibility=2 AND t.assignedTo IN
				 (SELECT username FROM x2_group_to_user WHERE groupId IN
					 (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
        } else{
            $condition = '';
        }
        return self::model()->findByPk((int) $id, $condition);
    }


    /**
     * Returns a CDbCriteria to retrieve all models specified by the list
     * @return CDbCriteria Criteria to retrieve all models in the list
     */
    public function queryCriteria($useAccessRules = true){
        $search = new CDbCriteria;

        if($this->type == 'dynamic'){
            $tagJoinCount = 0;
            $logicMode = $this->logicType;
            $criteria = X2Model::model('X2ListCriterion')
                ->findAllByAttributes(array('listId' => $this->id, 'type' => 'attribute'));
            foreach($criteria as $criterion){
                //if this criterion is for a date field, we perform its comparisons differently
                $dateType = false;
                //for each field in a model, make sure the criterion is in the same format
                foreach(X2Model::model($this->modelName)->fields as $field){
                    if($field->fieldName == $criterion->attribute){
                        switch($field->type){
                            case 'date':
                            case 'dateTime':
                                if(ctype_digit((string) $criterion->value) || 
                                   (substr($criterion->value, 0, 1) == '-' && 
                                    ctype_digit((string) substr($criterion->value, 1)))) {
                                    $criterion->value = (int) $criterion->value;
                                } else {
                                    $criterion->value = strtotime($criterion->value);
                                }
                                $dateType = true;
                                break;
                            case 'boolean':
                            case 'visibility':
                                $criterion->value = in_array(
                                    strtolower($criterion->value), 
                                    array('1', 'yes', 'y', 't', 'true')) ? 1 : 0;
                                break;
                        }
                        break;
                    }
                }

                if($criterion->attribute == 'tags' && $criterion->value){
                    //remove any spaces around commas, then explode to array
                    $tags = explode(',', preg_replace('/\s?,\s?/', ',', trim($criterion->value))); 
                    for($i = 0; $i < count($tags); $i++){
                        if(empty($tags[$i])){
                            unset($tags[$i]);
                            $i--;
                            continue;
                        }else{
                            if($tags[$i][0] != '#')
                                $tags[$i] = '#'.$tags[$i];
                            $tags[$i] = 'x2_tags.tag = "'.$tags[$i].'"';
                        }
                    }
                    $tagCondition = implode(' OR ', $tags);
                    $search->addCondition (
                        "EXISTS (
                            SELECT * 
                            FROM x2_tags 
                            WHERE x2_tags.itemId=t.id AND x2_tags.type='$this->modelName' AND 
                                ($tagCondition))", $logicMode);
                } else if($dateType){
                    //assume for now that any dates in a criterion are at midnight of that day
                    $thisDay = $criterion->value;
                    $nextDay = $criterion->value + 86400;
                    switch($criterion->comparison){
                        case '=':
                            $subSearch = new CDbCriteria();
                            $subSearch->compare($criterion->attribute, '>='.$thisDay, false, 'AND');
                            $subSearch->compare($criterion->attribute, '<'.$nextDay, false, 'AND');
                            $search->mergeWith($subSearch, $logicMode);
                            break;
                        case '<>':
                            $subSearch = new CDbCriteria();
                            $subSearch->compare($criterion->attribute, '<'.$thisDay, false, 'OR');
                            $subSearch->compare($criterion->attribute, '>='.$nextDay, false, 'OR');
                            $search->mergeWith($subSearch, $logicMode);
                            break;
                        case '>':
                            $search->compare($criterion->attribute, '>='.$thisDay, true, $logicMode);
                            break;
                        case '<':
                            $search->compare($criterion->attribute, '<'.$thisDay, true, $logicMode);
                            break;
                        case 'notEmpty':
                            $search->addCondition($criterion->attribute.' IS NOT NULL AND '.$criterion->attribute.'!=""', $logicMode);
                            break;
                        case 'empty':
                            $search->addCondition('('.$criterion->attribute.'="" OR '.$criterion->attribute.' IS NULL)', $logicMode);
                            break;
                        //the following comparitors are not supported for dates
                        //case 'list':
                        //case 'notList':
                        //case 'noContains':
                        //case 'contains':
                    }
                }else{
                    switch($criterion->comparison){
                        case '=':
                            $search->compare($criterion->attribute, $criterion->value, false, $logicMode);
                            break;
                        case '>':
                            $search->compare($criterion->attribute, '>='.$criterion->value, true, $logicMode);
                            break;
                        case '<':
                            $search->compare($criterion->attribute, '<='.$criterion->value, true, $logicMode);
                            break;
                        case '<>': // must test for != OR is null, because both mysql and yii are stupid
                            $search->addCondition('('.$criterion->attribute.' IS NULL OR '.$criterion->attribute.'!='.CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount.')', $logicMode);
                            $search->params[CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount++] = $criterion->value;
                            break;
                        case 'notEmpty':
                            $search->addCondition($criterion->attribute.' IS NOT NULL AND '.$criterion->attribute.'!=""', $logicMode);
                            break;
                        case 'empty':
                            $search->addCondition('('.$criterion->attribute.'="" OR '.$criterion->attribute.' IS NULL)', $logicMode);
                            break;
                        case 'list':
                            $search->addInCondition($criterion->attribute, explode(',', $criterion->value), $logicMode);
                            break;
                        case 'notList':
                            $search->addNotInCondition($criterion->attribute, explode(',', $criterion->value), $logicMode);
                            break;
                        case 'noContains':
                            $search->compare($criterion->attribute, '<>'.$criterion->value, true, $logicMode);
                            break;
                        case 'contains':
                        default:
                            $search->compare($criterion->attribute, $criterion->value, true, $logicMode);
                    }
                }
            }
        }else{
            $search->join = 'JOIN x2_list_items ON t.id = x2_list_items.contactId';
            $search->addCondition('x2_list_items.listId='.$this->id);
        }

        if($useAccessRules){
            $accessCriteria = X2Model::model('Contacts')->getAccessCriteria(); // record-level access control for Contacts
            $accessCriteria->mergeWith($search, 'AND');
            return $accessCriteria;
        }else{
            return $search;
        }
    }

    /**
     * Returns a CDbCommand to retrieve all records in the list
     * @return CDbCommand Command to retrieve all records in the list
     */
    public function queryCommand($useAccessRules = true){
        $tableSchema = X2Model::model($this->modelName)->getTableSchema();
        return $this->getCommandBuilder()->createFindCommand($tableSchema, $this->queryCriteria($useAccessRules));
    }

    /**
     * Returns a data provider for all the models in the list
     * @return CActiveDataProvider A data provider serving out all the models in the list
     */
    public function dataProvider($pageSize = null, $sort = null){
        if(!isset($sort))
            $sort = array();
        return new CActiveDataProvider($this->modelName, array(
                    'criteria' => $this->queryCriteria(),
                    'pagination' => array(
                        'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                    ),
                    'sort' => $sort
                ));
    }

    /**
     * Generates an array of links for the VCR controls based on the specified dataprovider and 
     * current ID
     * @param CActiveDataProvider $dataProvider the data provider of the most recent gridview
     * @param Integer $id the ID of the current record
     * @return Array array of VCR links and stats
     */
    public static function getVcrLinks(&$dataProvider, $modelId){

        $criteria = $dataProvider->criteria;

        $tableSchema = X2Model::model($dataProvider->modelClass)->getTableSchema();
        if($tableSchema === null)
            return false;

        // for the first query, find the current ID's row number in the list
        $criteria->select = 't.id';

        // we also need any columns that are being used in the sort
        foreach(explode(',', $criteria->order) as $token){

            // so loop through $criteria->order and extract them
            $token = preg_replace('/\s|asc|desc/i', '', $token);
            if($token !== '' && $token !== 'id' && $token != 't.id'){
                if(strpos($token, '.') != 1){
                    $criteria->select .= ',t.'.$token;
                }else{
                    $criteria->select .= ','.$token;
                }
            }
        }

        // always include "id DESC" in sorting (for order consistency with SmartDataProvider)
        if(!preg_match('/\bid\b/', $criteria->order)){
            if(!empty($criteria->order))
                $criteria->order .= ',';
            $criteria->order .= 't.id DESC';
        }

        // get search conditions (WHERE, JOIN, ORDER BY, etc) from the criteria
        $searchConditions = Yii::app()->db->getCommandBuilder()
            ->createFindCommand($tableSchema, $criteria)->getText();

        $rowNumberQuery = Yii::app()->db->createCommand('
            SELECT r-1 
            FROM (
                SELECT *,@rownum:=@rownum + 1 AS r 
                FROM ('.$searchConditions.') t1, (SELECT @rownum:=0) r) t2 
            WHERE t2.id='.$modelId
        );
        // attach params from $criteria to this query
        $rowNumberQuery->params = $criteria->params;
        $rowNumber = $rowNumberQuery->queryScalar();

        if($rowNumber === false){ // the specified record isn't in this list
            return false;
        }else{

            $criteria->select = '*'; // need to select everything to be sure ORDER BY will work

            if($rowNumber == 0){ // if we're on the first row, get 2 items, otherwise get 3
                $criteria->offset = 0;
                $criteria->limit = 2;
                $vcrIndex = 0;
            }else{
                $criteria->offset = $rowNumber - 1;
                $criteria->limit = 3;
                $vcrIndex = 1;  // index of current record in $vcrModels
            }

            $vcrModels = Yii::app()->db->getCommandBuilder()
                            ->createFindCommand($tableSchema, $criteria)->queryAll();
            $count = $dataProvider->getTotalItemCount();

            $vcrData = array();
            $vcrData['index'] = $rowNumber + 1;
            $vcrData['count'] = $dataProvider->getTotalItemCount();

            /* if($vcrIndex > 0)		// there's a record before the current one
              $vcrData['prev'] = '<li class="prev">'.CHtml::link('<',array('view/'.$vcrModels[0]['id']),array('title'=>$vcrModels[0]['name'],'class'=>'x2-button')).'</li>';
              else
              $vcrData['prev'] = '<li class="prev">'.CHtml::link('<','javascript:void(0);',array('class'=>'x2-button disabled')).'</li>';

              if(count($vcrModels) - 1 > $vcrIndex)	// there's a record after the current one
              $vcrData['next'] = '<li class="next">'.CHtml::link('>', array('view/'.$vcrModels[$vcrIndex+1]['id']), array('title'=>$vcrModels[$vcrIndex+1]['name'],'class'=>'x2-button')).'</li>';
              else
              $vcrData['next'] = '<li class="next">'.CHtml::link('>','javascript:void(0);',array('class'=>'x2-button disabled')).'</li>';
             */
            if($vcrIndex > 0 && isset($vcrModels[0])){ // there's a record before the current one
                $vcrData['prev'] = CHtml::link(
                    '<', array('view', 'id' => $vcrModels[0]['id']), 
                    array('title' => $vcrModels[0]['name'], 'class' => 'x2-button'));
            }else{
                $vcrData['prev'] = CHtml::link(
                    '<', 'javascript:void(0);', array('class' => 'x2-button disabled'));
            }

            if(count($vcrModels) - 1 > $vcrIndex){ // there's a record after the current one
                $vcrData['next'] = CHtml::link(
                    '>', array('view', 'id' => $vcrModels[$vcrIndex + 1]['id']), 
                    array('title' => $vcrModels[$vcrIndex + 1]['name'], 'class' => 'x2-button'));
            }else{
                $vcrData['next'] = CHtml::link(
                    '>', 'javascript:void(0);', array('class' => 'x2-button disabled'));
            }

            return $vcrData;
        }
    }

    /**
     * Returns an array data provider for all models in the list,
     * including the list_item status columns
     * @return CSqlDataProvider
     */
    public function statusDataProvider($pageSize = null){
        $tbl = X2Model::model($this->modelName)->tableName();
        $lstTbl = X2ListItem::model()->tableName();
        if($this->type == 'dynamic'){
            $criteria = $this->queryCriteria();
            $count = X2Model::model($this->modelName)->count($criteria);
            $sql = $this->getCommandBuilder()->createFindCommand($tbl, $criteria)->getText();
            $params = $criteria->params;
        }else{ //static type lists
            $count = X2ListItem::model()->count('listId=:listId', array('listId' => $this->id));
            $sql = "SELECT t.*, c.* FROM {$lstTbl} as t LEFT JOIN {$tbl} as c ON t.contactId=c.id WHERE t.listId=:listId;";
            $params = array('listId' => $this->id);
        }
        return new CSqlDataProvider($sql, array(
                    'totalItemCount' => $count,
                    'params' => $params,
                    'pagination' => array(
                        'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                    ),
                    'sort' => array(
                        //messing with attributes may cause columns to become unsortable
                        'attributes' => array('name', 'email', 'phone', 'address'),
                        'defaultOrder' => 'lastUpdated DESC',
                    ),
                ));
    }

    /**
     * Return a SQL data provider for a list of emails in a campaign
     * includes associated contact info with each email
     * @return CSqlDataProvider
     */
    public function campaignDataProvider($pageSize = null){
		$criteria = X2Model::model('Campaign')->getAccessCriteria();
		$conditions =$criteria->condition;

        $params = array('listId' => $this->id);

        $count = Yii::app()->db->createCommand()
                ->select('count(*)')
                ->from(X2ListItem::model()->tableName().' as list')
                ->leftJoin(X2Model::model($this->modelName)->tableName().' t', 'list.contactId=t.id')
                ->where(
                    'list.listId=:listId AND ('.$conditions.')',
                    array_merge (array(
                        ':listId' => $this->id
                    ), $criteria->params))
                ->queryScalar();

        $sql = Yii::app()->db->createCommand()
                ->select('list.*, t.*')
                ->from(X2ListItem::model()->tableName().' as list')
                ->leftJoin(X2Model::model($this->modelName)->tableName().' t', 'list.contactId=t.id')
                ->where(
                    'list.listId=:listId AND ('.$conditions.')')
                ->getText();

        return new CSqlDataProvider($sql, array(
                    'params' => array_merge ($params, $criteria->params),
                    'totalItemCount' => $count,
                    'pagination' => array(
                        'pageSize' => !empty($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                    ),
                    'sort' => array(
                        //messing with attributes may cause columns to become unsortable
                        'attributes' => array('name', 'email', 'phone', 'address', 'opened'
                        ),
                        'defaultOrder' => 'opened DESC',
                    ),
                ));
    }

    /**
     * Returns the count of items in the list that have the specified status
     * (i.e. sent, opened, clicked, unsubscribed)
     * @return integer
     */
    public function statusCount($type){
        $whitelist = array('sent', 'opened', 'clicked', 'unsubscribed');
        if(!in_array($type, $whitelist)){
            return 0;
        }

        $lstTbl = X2ListItem::model()->tableName();
        $count = Yii::app()->db->createCommand(
                        'SELECT COUNT(*) FROM '.$lstTbl.' WHERE listId = :listid AND '.$type.' > 0')
                ->queryScalar(array('listid' => $this->id));
        return $count;
    }

    /**
     * Creates, saves, and returns a duplicate static list containing the same items.
     *
     * @return X2List|null The active record model for the static clone is returned
     *  if the operation was successful; otherwise Null is returned.
     */
    public function staticDuplicate(){
        $dup = new X2List();
        $dup->attributes = $this->attributes;
        $dup->id = null;
        $dup->nameId = null;
        $dup->type = 'static';
        $dup->createDate = $dup->lastUpdated = time();
        $dup->isNewRecord = true;
        if(!$dup->save())
            return;

        $count = 0;
        $listItemRecords = array();
        $params = array();
        if($this->type == 'dynamic'){
            $itemIds = $this->queryCommand(true)->select('id')->queryColumn();
            foreach($itemIds as $id){
                $listItemRecords[] = '(NULL,:contactId'.$count.',:listId'.$count.',0)';
                $params[':contactId'.$count] = $id;
                $params[':listId'.$count] = $dup->id;
                $count++;
            }
        }else{ //static type lists
            //generate sql to replicate list items
            foreach($this->listItems as $listItem){
                if(!empty($listItem->emailAddress)){
                    $itemSql = '(:email'.$count;
                    $params[':email'.$count] = $listItem->emailAddress;
                }else{
                    $itemSql = '(NULL';
                }
                if(!empty($listItem->contactId)){
                    $itemSql .= ',:contactId'.$count;
                    $params[':contactId'.$count] = $listItem->contactId;
                }else{
                    $itemSql .= ',NULL';
                }
                $itemSql .= ',:listId'.$count.',:unsubd'.$count.')';
                $params[':listId'.$count] = $dup->id;
                $params[':unsubd'.$count] = $listItem->unsubscribed;
                $listItemRecords[] = $itemSql;
                $count++;
            }
        }
        if(count($listItemRecords) == 0)
            return;
        $sql = 'INSERT into x2_list_items
            (emailAddress, contactId, listId, unsubscribed)
            VALUES '
                .implode(',', $listItemRecords).';';
        $dup->count = $count;

        $transaction = Yii::app()->db->beginTransaction();
        try{
            Yii::app()->db->createCommand($sql)->execute($params);
            $transaction->commit();
        }catch(Exception $e){
            $transaction->rollBack();
            $dup->delete();
            Yii::log($e->getMessage(), 'error', 'application');
            $dup = null;
        }
        return $dup;
    }

    /**
     * Adds the specified ID(s) to this list (if they're not already in there)
     * @param mixed $ids a single integer or an array of integer IDs
     */
    public function addIds($ids){
        if($this->type !== 'static')
            return false;

        $ids = (array) $ids;

        $parameters = AuxLib::bindArray($ids, 'addIds');
        $existingIds = Yii::app()->db->createCommand()
                ->select('contactId')
                ->from('x2_list_items')
                ->where('listId='.$this->id.' AND contactId IN('.implode(',', array_keys($parameters)).')', $parameters) // intersection of $ids and the IDs already in this list
                ->queryColumn();

        foreach($ids as $id){
            if(in_array($id, $existingIds))
                continue;
            $listItem = new X2ListItem();
            $listItem->listId = $this->id;
            $listItem->contactId = $id;
            $listItem->save();
        }

        $this->count = CActiveRecord::model('X2ListItem')->countByAttributes(array('listId' => $this->id));
        return $this->update(array('count'));
    }

    /**
     * Save associated criterion objects for a dynamic list
     *
     * Takes data from the dynamic list criteria designer form and turns them
     * into {@link X2ListCriterion} records.
     */
    public function processCriteria(){
        X2ListCriterion::model()->deleteAllByAttributes(array('listId' => $this->id)); // delete old criteria
        foreach(array('attribute', 'comparison', 'value') as $property){
            // My lazy refactor: bring properties into the current scope as
            // temporary variables with their names pluralized
            ${"{$property}s"} = $this->criteriaInput[$property];
        }
        $comparisonList = self::getComparisonList();
        $contactModel = Contacts::model();
        $fields = $contactModel->getFields(true);

        for($i = 0; $i < count($attributes); $i++){ // create new criteria
            if((array_key_exists($attributes[$i], $contactModel->attributeLabels()) || $attributes[$i] == 'tags') && array_key_exists($comparisons[$i], $comparisonList)){
                $fieldRef = isset($fields[$attributes[$i]]) ? $fields[$attributes[$i]] : null;
                if($fieldRef instanceof Fields && $fieldRef->type == 'link'){
                    $nameList = explode(',', $values[$i]);
                    $namesParams = AuxLib::bindArray($nameList);
                    $namesIn = AuxLib::arrToStrList(array_keys($namesParams));
                    $lookupModel = X2Model::model(ucfirst($fieldRef->linkType));
                    $lookupModels = $lookupModel->findAllBySql(
                            'SELECT * FROM `'.$lookupModel->tableName().'` '
                            .'WHERE `name` IN '.$namesIn, $namesParams);
                    if(!empty($lookupModels)){
                        $values[$i] = implode(',', array_map(function($m){
                                    return $m->nameId;
                                }, $lookupModels)); //$lookup->nameId;
                    }
                }
                $criterion = new X2ListCriterion;
                $criterion->listId = $this->id;
                $criterion->type = 'attribute';
                $criterion->attribute = $attributes[$i];
                $criterion->comparison = $comparisons[$i];
                $criterion->value = $values[$i];
                $criterion->save();
            }
        }
    }

    /**
     * Removes the specified ID(s) from this list
     * @param mixed $ids a single integer or an array of integer IDs
     */
    public function removeIds($ids){
        if($this->type !== 'static')
            return false;

        $criteria = new CDbCriteria();
        $criteria->compare('listId', $this->id);
        $criteria->addInCondition('contactId', (array) $ids);

        $model = CActiveRecord::model('X2ListItem');

        // delete all the things!
        if(CActiveRecord::model('X2ListItem')->deleteAll($criteria)){
            $this->count = CActiveRecord::model('X2ListItem')->countByAttributes(array('listId' => $this->id));
            $this->update(array('count'));
            return true;
        }
        return false;
    }

    /**
     * Uses {@link queryCriteria()} to test whether this list contains the specified record ID
     * @param integer $id the ID of the record
     * @return boolean whether or not record is in this list
     */
    /*public function hasRecord($id){
        $criteria = $this->queryCriteria(false); // don't use access rules
        $criteria->compare('id', $id);

        return X2Model::model($this->modelName)->exists($criteria);
    }*/

    public function hasRecord ($model) {
        if($this->modelName !== get_class($model))
            return false;
        $listCriteria = $this->queryCriteria(false); // don't use access rules
        $listCriteria->compare('t.id',$model->id);
        return $model->exists($listCriteria);        // see if this record is on the list
    }

    public static function getRoute($id){
        if($id == 'all')
            return array('/contacts/contacts/index');
        else if($id == 'new')
            return array('/contacts/contacts/newContacts');
        else if(empty($id) || $id == 'my')
            return array('/contacts/contacts/myContacts');
        else
            return array('/contacts/contacts/list', 'id' => $id);
    }

    public static function getAllStaticListNames($controller){
        $listNames = array();

        // get all static lists
        foreach(X2List::model()->findAllByAttributes(array('type' => 'static')) as $list){
            if($controller->checkPermissions($list, 'edit')) // check permissions
                $listNames[$list->id] = $list->name;
        }
        return $listNames;
    }

    public function setAttributes($values, $safeOnly = true){
        if($this->type == 'dynamic'){
            $this->criteriaInput = array();
            foreach(array('attribute', 'comparison', 'value') as $property){
                if(isset($values[$property]))
                    $this->criteriaInput[$property] = $values[$property];
            }
        }
        parent::setAttributes($values, $safeOnly);
    }

    public function afterSave(){
        if($this->type == 'dynamic' && isset($this->criteriaInput)) {
            $this->processCriteria();
        }
        return parent::afterSave();
    }

}
