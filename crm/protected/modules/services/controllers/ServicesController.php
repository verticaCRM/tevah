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
 * Track service/support cases among contacts.
 *
 * Every Service Case must be associated with a contact. It's possible to
 * create a service case from a contacts view via ajax by clicking the
 * "Create Case" button. (the new case is associated with the contact).
 *
 * @package application.modules.services.controllers
 */
class ServicesController extends x2base {

    public $modelClass = 'Services';
    public $serviceCaseStatuses = null;

    public function accessRules(){
        return array(
            array('allow',
                'actions' => array('getItems', 'webForm'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'update', 'search', 'saveChanges', 'delete', 'inlineEmail', 'createWebForm', 'statusFilter'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'testScalability'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actions(){
        return array_merge(parent::actions(), array(
            'webForm' => array(
                'class' => 'WebFormAction',
            ),
            'createWebForm' => array(
                'class' => 'CreateWebFormAction',
            ),
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
        ));
    }

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'ServiceRoutingBehavior' => array(
                'class' => 'ServiceRoutingBehavior'
            ),
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),
        ));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){
        $model = $this->loadModel($id);

        // add service case to user's recent item list
        User::addRecentItem('s', $id, Yii::app()->user->getId()); 

        parent::view($model, 'services');
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    /* public function create($model,$oldAttributes, $api){
      //		$model->annualRevenue = Formatter::parseCurrency($model->annualRevenue,false);
      $model->createDate=time();
      $model->lastUpdated=time();
      $model->updatedBy = Yii::app()->user->name;
      if($api==0) {
      parent::create($model,$oldAttributes,'1');
      if( !$model->isNewRecord ) {
      $model->name = $model->id;
      $model->update();
      if($model->escalatedTo != '') {
      $event=new Events;
      $event->type='case_escalated';
      $event->user=Yii::app()->user->getName();
      $event->associationType=$this->modelClass;
      $event->associationId=$model->id;
      if($event->save()){
      $notif = new Notification;
      $notif->user = $model->escalatedTo;
      $notif->createDate = time();
      $notif->createdBy = Yii::app()->user->name;
      $notif->type = 'escalateCase';
      $notif->modelType = $this->modelClass;
      $notif->modelId = $model->id;
      $notif->save();
      }
      }

      $this->redirect(array('view', 'id' => $model->id));
      }
      } else {
      return parent::create($model,$oldAttributes,$api);
      }
      } */

    /**
     * Create a new Service Case
     *
     * This action can be called normally (by clicking the Create button in Service module)
     * or it can be called via ajax by clicking the "Create Case" button in a contact view.
     *
     */
    public function actionCreate(){
        $model = new Services;
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id] = $group->name;
        }

        if(isset($_POST['Services'])){
            $temp = $model->attributes;
            foreach($_POST['Services'] as $name => &$value){
                if($value == $model->getAttributeLabel($name))
                    $value = '';
            }
            $model->setX2Fields($_POST['Services']);

            if(isset($_POST['x2ajax'])){ // we're creating a case with "Create Case" button in contacts view
                /* every model needs a name field to work with X2GridView and a few other places, 
                   for service cases the id of the case is the name */
                $model->name = $model->id; 
                $ajaxErrors = $this->quickCreate ($model);

            }elseif($model->save()){
                $this->redirect(array('view', 'id' => $model->id));
                // $this->create($model,$temp, '0');
            }
        }

        // we're creating a case with "Create Case" button in contacts view
        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        }else{
            $this->render('create', array(// normal (non-ajax) create
                'model' => $model,
                'users' => $users,
            ));
        }
    }

    /* public function update($model, $oldAttributes,$api){

      $ret = parent::update($model,$oldAttributes,'1');

      if($model->escalatedTo != '' && $model->escalatedTo != $oldAttributes['escalatedTo']) {
      $event=new Events;
      $event->type='case_escalated';
      $event->user=Yii::app()->user->getName();
      $event->associationType=$this->modelClass;
      $event->associationId=$model->id;
      if($event->save()){
      $notif = new Notification;
      $notif->user = $model->escalatedTo;
      $notif->createDate = time();
      $notif->createdBy = Yii::app()->user->name;
      $notif->type = 'escalateCase';
      $notif->modelType = $this->modelClass;
      $notif->modelId = $model->id;
      $notif->save();
      }
      }

      if($api==0)
      $this->redirect(array('view', 'id' => $model->id));
      else
      return $ret;
      } */

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id){
        $model = $this->loadModel($id);
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id] = $group->name;
        }

        if(isset($_POST['Services'])){
            $temp = $model->attributes;
            foreach($_POST['Services'] as $name => &$value){
                if($value == $model->getAttributeLabel($name))
                    $value = null;
            }
            $model->setX2Fields($_POST['Services']);

            if($model->contactId != '' && !is_numeric($model->contactId)) // make sure an existing contact is associated with this case, otherwise don't create it
                $model->addError('contactId', Yii::t('services', 'Contact does not exist'));

            // $this->update($model,$temp,'0');
            if($model->save()){
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('update', array(
            'model' => $model,
            'users' => $users,
        ));
    }

    public function delete($id){

        $model = $this->loadModel($id);
        $dataProvider = new CActiveDataProvider('Actions', array(
                    'criteria' => array(
                        'condition' => 'associationId='.$id.' AND associationType=\'services\'',
                        )));

        $actions = $dataProvider->getData();
        foreach($actions as $action){
            $action->delete();
        }
        $this->cleanUpTags($model);
        $model->delete();
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id){
        $model = $this->loadModel($id);
        if(Yii::app()->request->isPostRequest){
            $event = new Events;
            $event->type = 'record_deleted';
            $event->associationType = $this->modelClass;
            $event->associationId = $model->id;
            $event->text = $model->name;
            $event->user = Yii::app()->user->getName();
            $event->save();
            Actions::model()->deleteAll('associationId='.$id.' AND associationType=\'services\'');
            $this->cleanUpTags($model);
            $model->delete();
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex(){

        $model = new Services('search');
        $this->render('index', array('model' => $model));
    }

    public function actionGetItems(){
        // We need to select the id both as 'id' and 'value' in order to correctly populate the association form.
        $sql = 'SELECT id, id as value FROM x2_services WHERE id LIKE :qterm ORDER BY id ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        exit;
    }

    /**
     *  Show or hide a certain status in the gridview
     *
     *  Called through ajax with a status and if that status should be shown or hidden.
     *  Saves the result in the user's profile.
     *
     */
    public function actionStatusFilter(){

        if(isset($_POST['all'])){ // show all the things!!
            Yii::app()->params->profile->hideCasesWithStatus = CJSON::encode(array()); // hide none
            Yii::app()->params->profile->update(array('hideCasesWithStatus'));
        }elseif(isset($_POST['none'])){ // hide all the things!!!!11
            $statuses = array();

            $dropdownId = Yii::app()->db->createCommand() // get the ID of the statuses dropdown via fields table
                    ->select('linkType')
                    ->from('x2_fields')
                    ->where('modelName="Services" AND fieldName="status" AND type="dropdown"')
                    ->queryScalar();
            if($dropdownId !== null)
                $statuses = Dropdowns::getItems($dropdownId); // get the actual statuses

            Yii::app()->params->profile->hideCasesWithStatus = CJSON::encode($statuses);
            Yii::app()->params->profile->update(array('hideCasesWithStatus'));
        } elseif(isset($_POST['checked'])){

            $checked = CJSON::decode($_POST['checked']);
            $status = isset($_POST['status']) ? $_POST['status'] : false;

            // var_dump($checked);
            // var_dump($status);

            $hideStatuses = CJSON::decode(Yii::app()->params->profile->hideCasesWithStatus); // get a list of statuses the user wants to hide
            if($hideStatuses === null || !is_array($hideStatuses))
                $hideStatuses = array();

            // var_dump($checked);
            // var_dump(in_array($status, $hideStatuses));
            if($checked && ($key = array_search($status, $hideStatuses)) !== false){ // if we want to show the status, and it's not being shown
                unset($hideStatuses[$key]); // show status
            }else if(!$checked && !in_array($status, $hideStatuses)){ // if we want to hide the status, and it's not being hidden
                $hideStatuses[] = $status;
            }

            Yii::app()->params->profile->hideCasesWithStatus = CJSON::encode($hideStatuses);
            Yii::app()->params->profile->update(array('hideCasesWithStatus'));
        }
    }

    /**
     * Create a menu for Services
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Services = Modules::displayName();
        $Service = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete', 'email', 'attach', 'quotes',
         *     'createWebForm', 'print', 'import', 'export',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('services','All Cases'),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('services','Create Case'),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('services','Edit Case'),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('services','Delete Case'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?')
            ),
            array(
                'name'=>'email',
                'label'=>Yii::t('app','Send Email'),
                'url'=>'#',
                'linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;')
            ),
            array(
                'name'=>'attach',
                'label'=>Yii::t('app','Attach a File/Photo'),
                'url'=>'#',
                'linkOptions'=>array('onclick'=>'toggleAttachmentForm(); return false;')
            ),
            array(
                'name'=>'quotes',
                'label' => Yii::t('quotes', '{quotes}/Invoices', array(
                    '{quotes}' => Modules::displayName(true, "Quotes"),
                )),
                'url' => 'javascript:void(0)',
                'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')
            ),
            array(
                'name'=>'createWebForm',
                'label'=>Yii::t('services','Create Web Form'),
                'url'=>array('createWebForm')
            ),
            array(
                'name'=>'print',
                'label' => Yii::t('app', 'Print Record'),
                'url' => '#',
                'linkOptions' => array (
                    'onClick'=>"window.open('".
                        Yii::app()->createUrl('/site/printRecord', array (
                            'modelClass' => 'Services',
                            'id' => $modelId,
                            'pageTitle' => Yii::t('app', '{service} Case', array(
                                '{service}' => $Service,
                            )).': '.(isset($model) ? $model->name : "")
                        ))."');"
                )
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('services', 'Import {services}', array(
                    '{services}' => $Services,
                )),
                'url'=>array('admin/importModels', 'model'=>'Services'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('services', 'Export {services}', array(
                    '{services}' => $Services,
                )),
                'url'=>array('admin/exportModels', 'model'=>'Services'),
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}
