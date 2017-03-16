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
 * @package application.modules.opportunities.controllers
 */
class OpportunitiesController extends x2base {

    public $modelClass = 'Opportunity';

    public function accessRules() {
        return array(
            array('allow',
                'actions'=>array('getItems'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index','view','create','update','search','addUser','removeUser',
                                    'saveChanges','delete','shareOpportunity','inlineEmail'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','testScalability'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
                'attributesOfNewRecordToUpdate' => array (
                    'Contacts' => array (
                        'accountName' => 'company',
                    ),
                )
            ),
        ));
    }

    public function actions() {
        return array_merge(parent::actions(), array(
        ));
    }

    public function actionGetItems(){
        $sql = 'SELECT id, name as value FROM x2_opportunities WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        Yii::app()->end();
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $type = 'opportunities';
        $model = $this->loadModel($id);
        $model->associatedContacts = Contacts::getContactLinks($model->associatedContacts);
        if($this->checkPermissions($model,'view')){

            // add opportunity to user's recent item list
            User::addRecentItem('o', $id, Yii::app()->user->getId()); 

            parent::view($model, $type);
        }else{
            $this->redirect('index');
        }
    }

    public function actionShareOpportunity($id){

        $model=$this->loadModel($id);
        $body="\n\n\n\n".Yii::t('opportunities','Opportunity Record Details')." <br />
<br />".Yii::t('opportunities','Name').": $model->name
<br />".Yii::t('opportunities','Description').": $model->description
<br />".Yii::t('opportunities','Quote Amount').": $model->quoteAmount
<br />".Yii::t('opportunities','Opportunities Stage').": $model->salesStage
<br />".Yii::t('opportunities','Lead Source').": $model->leadSource
<br />".Yii::t('opportunities','Probability').": $model->probability
<br />".Yii::t('app','Link').": ".CHtml::link($model->name,'http://'.Yii::app()->request->getServerName().$this->createUrl('/opportunities/'.$model->id));

        $body = trim($body);

        $errors = array();
        $status = array();
        $email = array();
        if(isset($_POST['email'], $_POST['body'])){

            $subject = Yii::t('opportunities','Opportunity Record Details');
            $email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
            $body = $_POST['body'];
            // if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
            if($email['to'] === false)
                $errors[] = 'email';
            if(empty($body))
                $errors[] = 'body';

            if(empty($errors))
                $status = $this->sendUserEmail($email,$subject,$body);

            if(array_search('200',$status)) {
                $this->redirect(array('view','id'=>$model->id));
                return;
            }
            if($email['to'] === false)
                $email = $_POST['email'];
            else
                $email = $this->mailingListToString($email['to']);
        }
        $this->render('shareOpportunity',array(
            'model'=>$model,
            'body'=>$body,
            'currentWorkflow'=>$this->getCurrentWorkflow($model->id,'opportunities'),
            'email'=>$email,
            'status'=>$status,
            'errors'=>$errors
        ));
    }

    /* public function create($model,$oldAttributes,$api=0) {

        // process currency into an INT
//        $model->quoteAmount = Formatter::parseCurrency($model->quoteAmount,false);

        if(isset($model->associatedContacts))
            $model->associatedContacts = Opportunity::parseContacts($model->associatedContacts);
        $model->createDate = time();
        $model->lastUpdated = time();
        // $model->expectedCloseDate = Formatter::parseDate($model->expectedCloseDate);
        if($api == 1) {
            return parent::create($model,$oldAttributes,$api);
        } else {
            parent::create($model,$oldAttributes,'0');
        }
    } */

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new Opportunity;
        $users = User::getNames();
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id]=$group->name;
        }
        unset($users['admin']);
        unset($users['']);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Opportunity'])) {
            $temp=$model->attributes;

            $model->setX2Fields($_POST['Opportunity']);

            if(isset($_POST['x2ajax'])) {
                $ajaxErrors = $this->quickCreate ($model);
            } else {
                if($model->save())
                    $this->redirect(array('view','id'=>$model->id));
            }
        }

        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create',array(
                'model'=>$model,
                'users'=>$users,
            ));
        }
    }

    /* public function update($model,$oldAttributes,$api=0){

        // process currency into an INT
        // $model->quoteAmount = Formatter::parseCurrency($model->quoteAmount,false);

        $arr=$model->associatedContacts;
        if(isset($model->associatedContacts)) {
            foreach($model->associatedContacts as $contact) {
                $rel=new Relationships;
                $rel->firstType='Contacts';
                $rel->firstId=$contact;
                $rel->secondType='Opportunity';
                $rel->secondId=$model->id;
                if($rel->firstId!="" && $rel->secondId!="")
                    $rel->save();
            }
                $model->associatedContacts=Opportunity::parseContacts($arr);
        }
        $model->lastUpdated = time();
        // if($model->expectedCloseDate!=""){
            // $model->expectedCloseDate=strtotime($model->expectedCloseDate);
        // }

        parent::update($model,$oldAttributes,'0');
    } */

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model=$this->loadModel($id);
        if(!empty($model->associatedContacts))
            $model->associatedContacts = explode(' ',$model->associatedContacts);

        if(isset($_POST['Opportunity'])) {
            $model->setX2Fields($_POST['Opportunity']);
            if(!empty($model->associatedContacts))
                $model->associatedContacts=implode(', ',$model->associatedContacts);

            // $this->update($model,$temp);
            if($model->save())
                $this->redirect(array('view','id'=>$model->id));
        }
        /* Set assignedTo back into an array only before re-rendering the input box with assignees 
           selected */
        $model->assignedTo = array_map(function($n){
            return trim($n,',');
        },explode(' ',$model->assignedTo));

        $this->render('update',array(
            'model'=>$model,
        ));
    }
    /*
    public function actionSaveChanges($id) {
        $opportunity=$this->loadModel($id);
        if(isset($_POST['Opportunity'])) {
            $temp=$opportunity->attributes;
            foreach($opportunity->attributes as $field=>$value){
                            if(isset($_POST['Opportunity'][$field])){
                                $opportunity->$field=$_POST['Opportunity'][$field];
                            }
                        }

            // process currency into an INT
            $opportunity->quoteAmount = Formatter::parseCurrency($opportunity->quoteAmount,false);


            if($opportunity->expectedCloseDate!=""){
                $opportunity->expectedCloseDate=strtotime($opportunity->expectedCloseDate);
            }
            $changes=$this->calculateChanges($temp,$opportunity->attributes, $opportunity);
            $opportunity=$this->updateChangelog($opportunity,$changes);
            $opportunity->save();
            $this->redirect(array('view','id'=>$opportunity->id));
        }
    }
        */
    public function actionAddUser($id) {
        $users=User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id]=$group->name;
        }
        $model=$this->loadModel($id);
        $users=Opportunity::editUserArray($users,$model);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Opportunity'])) {
            $temp=$model->assignedTo;
                        $tempArr=$model->attributes;
            $model->attributes=$_POST['Opportunity'];
            $arr=$_POST['Opportunity']['assignedTo'];


            $model->assignedTo=Opportunity::parseUsers($arr);
            if($temp!="")
                $temp.=", ".$model->assignedTo;
            else
                $temp=$model->assignedTo;
            $model->assignedTo=$temp;
            // $changes=$this->calculateChanges($tempArr,$model->attributes);
            // $model=$this->updateChangelog($model,$changes);
            if($model->save())
                $this->redirect(array('view','id'=>$model->id));
        }

        $this->render('addUser',array(
            'model'=>$model,
            'users'=>$users,
            'action'=>'Add'
        ));
    }

    public function actionRemoveUser($id) {

        $model=$this->loadModel($id);

        $pieces=explode(', ',$model->assignedTo);
        $pieces=Opportunity::editUsersInverse($pieces);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Opportunity'])) {
                        $temp=$model->attributes;
            $model->attributes=$_POST['Opportunity'];
            $arr=$_POST['Opportunity']['assignedTo'];


            foreach($arr as $id=>$user){
                unset($pieces[$user]);
            }

            $temp=Opportunity::parseUsersTwo($pieces);

            $model->assignedTo=$temp;
            // $changes=$this->calculateChanges($temp,$model->attributes, $model);
            // $model=$this->updateChangelog($model,$changes);
            if($model->save())
                $this->redirect(array('view','id'=>$model->id));
        }

        $this->render('addUser',array(
            'model'=>$model,
            'users'=>$pieces,
            'action'=>'Remove'
        ));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $model=new Opportunity('search');
        $this->render('index', array('model'=>$model));
    }

    public function delete($id) {
        $model = $this->loadModel($id);

        CActiveDataProvider::model('Actions')->deleteAllByAttributes(
            array('associationType'=>'opportunities','associationId'=>$id));

        $this->cleanUpTags($model);
        $model->delete();
    }

    public function actionDelete($id) {
        $model=$this->loadModel($id);

        if(Yii::app()->request->isPostRequest) {
            $event=new Events;
            $event->type='record_deleted';
            $event->associationType=$this->modelClass;
            $event->associationId=$model->id;
            $event->text=$model->name;
            $event->user=Yii::app()->user->getName();
            $event->save();
            Actions::model()->deleteAll('associationId='.$id.' AND associationType=\'opportunities\'');
            $this->cleanUpTags($model);
            $model->delete();
        } else
            throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser

        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    public function actionGetTerms(){
        $sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result); exit;
    }

    public function actionQtip($id){
        $model = $this->loadModel($id);
        $this->renderPartial('qtip', array('model' => $model));
    }

    /**
     * Create a menu for Opportunities
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Opportunities = Modules::displayName();
        $Opportunity = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'share', 'delete', 'attach', 'import', 'export', 'quick',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('opportunities','{opportunities} List', array(
                    '{opportunities}'=>$Opportunities,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('opportunities','Create {opportunity}', array(
                    '{opportunity}'=>$Opportunity,
                )),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('opportunities','Edit {opportunity}', array(
                    '{opportunity}'=>$Opportunity,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'share',
                'label'=>Yii::t('accounts','Share {opportunity}', array(
                    '{opportunity}'=>$Opportunity,
                )),
                'url'=>array('shareOpportunity','id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('opportunities','Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?')
            ),
            array(
                'name'=>'attach',
                'label'=>Yii::t('app','Attach A File/Photo'),
                'url'=>'#',
                'linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')
            ),
            array(
                'name'=>'quotes',
                'label' => Yii::t('quotes', 'Quotes/Invoices'), 'url' => 'javascript:void(0)',
                'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')),
            array(
                'name'=>'import',
                'label'=>Yii::t('opportunities', 'Import {opportunities}', array(
                    '{opportunities}'=>$Opportunities,
                )),
                'url'=>array('admin/importModels', 'model'=>'Opportunity'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('opportunities', 'Export {opportunities}', array(
                    '{opportunities}'=>$Opportunities,
                )),
                'url'=>array('admin/exportModels', 'model'=>'Opportunity'),
            ),
            array(
                'name'=>'quick',
                'label'=>Yii::t('app', 'Quick Create'),
                'url'=>array('/site/createRecords', 'ret'=>'opportunities'),
                'linkOptions'=>array(
                    'id'=>'x2-create-multiple-records-button',
                    'class'=>'x2-hint',
                    'title'=>Yii::t('app', 'Create a {contact}, {account}, and {opportunity}.', array(
                        '{opportunity}'=>$Opportunity,
                        '{contact}'=>Modules::displayName(false, "Contacts"),
                        '{account}'=>Modules::displayName(false, "Accounts"),
                    )))
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}
