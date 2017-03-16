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
 * @package application.modules.actions.controllers
 */
class ActionsController extends x2base {

    public $modelClass = 'Actions';
    public $showActions = null;

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'ActionsQuickCreateRelationshipBehavior' => array(
                'class' => 'ActionsQuickCreateRelationshipBehavior',
                'attributesOfNewRecordToUpdate' => array(
                )
            ),
        ));
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules(){
        return array(
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('invalid', 'sendReminder', 'emailOpened'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'createSplash', 'createInline', 'viewGroup', 'complete', //quickCreate
                    'completeRedirect', 'update', 'quickUpdate', 'saveShowActions', 'viewAll', 'search', 'completeNew', 'parseType', 'uncomplete', 'uncompleteRedirect', 'delete', 'shareAction', 'inlineEmail', 'publisherCreate','saveShowActions', 'copyEvent'),
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
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xeeeeee,
            ),
            'timerControl' => array(
                'class' => 'application.modules.actions.components.TimerControlAction',
            ),
        ));
    }
    public function actionSaveShowActions(){
        if(isset($_POST['ShowActions'])){
            $profile = Profile::model()->findByPk(Yii::app()->user->id);
            $profile->showActions = $_POST['ShowActions'];
            $profile->update();
        }
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){
        $action = CActiveRecord::model('Actions')->findByPk($id);

        if($action === null)
            $this->redirect('index');

        $users = User::getNames();
        $association = $this->getAssociation($action->associationType, $action->associationId);

        if($this->checkPermissions($action, 'view')){

            X2Flow::trigger('RecordViewTrigger', array('model' => $action));

            User::addRecentItem('t', $id, Yii::app()->user->getId()); //add action to user's recent item list
            $this->render('view', array(
                'model' => $this->loadModel($id),
                'associationModel' => $association,
                'users' => $users,
            ));
        } else
            $this->redirect('index');
    }

    public function actionViewEmail($id){
        $this->redirectOnNullModel = false;
        $action = $this->loadModel($id);
        if(!Yii::app()->user->isGuest || 
            Yii::app()->user->checkAccess(ucfirst($action->associationType).'View')){

            if(!Yii::app()->user->isGuest){
                echo preg_replace(
                    '/<\!--BeginOpenedEmail-->(.*?)<\!--EndOpenedEmail--!>/s', '', 
                    $action->actionDescription);
            }else{
                // Strip out the action header since it's being viewed directly:
                $actionHeaderPattern = InlineEmail::insertedPattern('ah', '(.*)', 1, 'mis');
                if(!preg_match($actionHeaderPattern, $action->actionDescription, $matches)){
                    // Legacy action header
                    echo preg_replace('/<b>(.*?)<\/b>(.*)/mis', '', $action->actionDescription); 
                }else{
                    // Current action header
                    echo preg_replace($actionHeaderPattern, '', $action->actionDescription); 
                }
            }
        }
    }

    public function actionViewAction($id, $publisher = false){
        $this->redirectOnNullModel = false;
        $this->throwOnNullModel = false;
        $model = $this->loadModel($id);
        if(isset($model)){
            if(in_array($model->type, Actions::$emailTypes)){
                $this->actionViewEmail($id);
                return;
            }
            X2Flow::trigger('RecordViewTrigger', array('model' => $model));
            $this->renderPartial('_viewFrame', array(
                'model' => $model,
                'publisher' => $publisher,
            ));
        }else{
            echo "<b>Error: 404</b><br><br>Unable to find the requested action.";
        }
    }

    public function actionShareAction($id){

        $model = $this->loadModel($id);
        $body = "\n\n\n\n".Yii::t('actions', "Reminder, the following action is due")." ".Formatter::formatLongDateTime($model->dueDate).":<br />
<br />".Yii::t('actions', 'Description').": $model->actionDescription
<br />".Yii::t('actions', 'Type').": $model->type
<br />".Yii::t('actions', 'Associations').": ".$model->associationName."
<br />".Yii::t('actions', 'Link to the action').": ".CHtml::link('Link', 'http://'.Yii::app()->request->getServerName().$this->createUrl('/actions/'.$model->id));
        $body = trim($body);

        $errors = array();
        $status = array();
        $email = array();
        if(isset($_POST['email'], $_POST['body'])){

            $subject = Yii::t('actions', "Reminder, the following action is due")." ".date("Y-m-d", $model->dueDate);
            $email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
            $body = $_POST['body'];
            // if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
            if($email['to'] === false)
                $errors[] = 'email';
            if(empty($body))
                $errors[] = 'body';

            if(empty($errors))
                $status = $this->sendUserEmail($email, $subject, $body);

            if(array_search('200', $status)){
                $this->redirect(array('view', 'id' => $model->id));
                return;
            }
            if($email['to'] === false)
                $email = $_POST['email'];
            else
                $email = $this->mailingListToString($email['to']);
        }
        $this->render('shareAction', array(
            'model' => $model,
            'body' => $body,
            'email' => $email,
            'status' => $status,
            'errors' => $errors
        ));
    }

    /*public function actionSendReminder(){

        $dataProvider = new CActiveDataProvider('Actions', array(
                    'criteria' => array(
                        'condition' => '(dueDate<"'.mktime(23, 59, 59).'" AND dueDate>"'.mktime(0, 0, 0).'" AND complete="No")',
                        )));

        $actionArray = $dataProvider->getData();

        foreach($actionArray as $action){
            if($action->reminder == 1){
                $action->sendEmailRemindersToAssignees ();
            }
        }
    }*/

    public function create($model, $oldAttributes, $api){

        /* if($model->associationId=='')
          $model->associationId=0;
          //if($model->

          $model->createDate = time();	// created now, full datetime
          //$model->associationId=$_POST['Actions']['associationId'];
          if(!is_numeric($model->dueDate)){
          $dueDate = Formatter::parseDateTime($model->dueDate);
          $model->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM
          } */

        //if($type=='none')
        //	$model->associationId=0;
        //$model->associationType=$type;

        /* $association = $this->getAssociation($model->associationType,$model->associationId);

          if($association != null) {
          $model->associationName = $association->name;
          if($association->hasAttribute('lastActivity') && $api==0) {
          $association->lastActivity = time();
          $association->update(array('lastActivity'));
          }
          } else {
          $model->associationName='None';
          //$model->associationId = 0;
          }
          if($model->associationName=='None' && $model->associationType!='none'){
          $model->associationName=ucfirst($model->associationType);
          } */
//		$this->render('test', array('model'=>$model));
        /* if($model->type != 'event' && isset($_POST['submit']) && ($_POST['submit']=='0' || $_POST['submit']=='2') && $model->calendarId == null) {	// if user clicked "New Comment" rather than "New Action"
          $model->createDate = time();
          $model->dueDate = time();
          $model->completeDate = time();
          $model->complete='Yes';
          $model->visibility='1';
          $model->assignedTo=Yii::app()->user->getName();
          $model->completedBy=Yii::app()->user->getName();
          $model->type=$_POST['submit']==2?'note':'call';
          } else if($model->type == 'event') {
          if($model->completeDate) {
          $model->completeDate = Formatter::parseDateTime($model->completeDate);
          }
          } */

        // $model->syncGoogleCalendar();
        // google sync
        /* if(!is_numeric($model->assignedTo)) { // assigned to user
          $profile = Profile::model()->findByAttributes(array('username'=>$model->assignedTo));
          if(isset($profile))
          $profile->syncActionToGoogleCalendar($model); // sync action to Google Calendar if user has a Google Calendar
          } else { // Assigned to group
          $groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
          foreach($groups as $group) {
          $profile = Profile::model()->findByPk($group['userId']);
          if(isset($profile))
          $profile->syncActionToGoogleCalendar($model);
          }
          } */

        if($api == 0){
            parent::create($model, $oldAttributes, $api);
        }else
            return parent::create($model, $oldAttributes, $api);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate(){

        $model = new Actions;
        $users = User::getNames();

        if(isset($_POST['Actions'])){
            $model->setX2Fields($_POST['Actions']);
            if (isset($_POST['x2ajax'])) {
                $ajaxErrors = $this->quickCreate($model);
            } elseif($model->save()){
                if(isset($_POST['Actions']['reminder']) && $_POST['Actions']['reminder']){
                    $model->createNotifications (
                        $_POST['notificationUsers'],
                        $model->dueDate - ($_POST['notificationTime'] * 60), 'action_reminder');
                }
                $model->syncGoogleCalendar('create');
                $this->redirect(array('index'));
            }
        }
        if(empty($model->assignedTo)){
            $model->assignedTo = Yii::app()->user->getName();
        }
        if (isset($_POST['x2ajax'])) {
            $this->renderInlineForm ($model, false);
        } else {
            $this->render('create', array(
                'model' => $model,
            ));
        }
    }

    public function actionPublisherCreate(){
        if(isset($_POST['SelectedTab'], $_POST['Actions']) && 
           (!Yii::app()->user->isGuest || 
            Yii::app()->user->checkAccess($_POST['Actions']['associationType'].'View'))) {

            Yii::app()->clientScript->scriptMap['*.css'] = false;

            // if association name is sent without id, try to lookup the record by name and type
            if (isset ($_POST['calendarEventTab']) && $_POST['calendarEventTab'] &&
                isset ($_POST['Actions']['associationName']) && 
                empty ($_POST['Actions']['associationId'])) {

                $associatedModel = X2Model::getModelOfTypeWithName (
                    $_POST['Actions']['associationType'], $_POST['Actions']['associationName']);
                if ($associatedModel) {
                    $_POST['Actions']['associationId'] = $associatedModel->id;
                } else {
                    echo CJSON::encode (
                        array ('error' => Yii::t('actions', 'Invalid association name')));
                    Yii::app()->end ();
                }
            }

            if(!Yii::app()->user->isGuest){
                $model = new Actions;
            }else{
                $model = new Actions('guestCreate');
                $model->verifyCode = $_POST['Actions']['verifyCode'];
            }
            $model->setX2Fields($_POST['Actions']);
            // format dates,
            if (isset ($_POST[get_class($model)]['dueDate'])) {
                $model->dueDate = Formatter::parseDateTime($_POST[get_class($model)]['dueDate']);
            }

            if($_POST['SelectedTab'] == 'new-event' || 
                $_POST['SelectedTab'] == 'new-small-calendar-event'){

                $model->disableBehavior('changelog');
                $event = new Events;
                $event->type = 'calendar_event';
                $event->visibility = $model->visibility;
                $event->associationType = 'Actions';
                $event->timestamp = $model->dueDate;
                $model->type = 'event';
                if($model->completeDate){
                    $model->completeDate = Formatter::parseDateTime($model->completeDate);
                }else{
                    $model->completeDate = $model->dueDate;
                }
            } 

            // format association
            if($model->associationId == '')
                $model->associationId = 0;

            $association = $this->getAssociation($model->associationType, $model->associationId);

            if($association){
                /* x2plastart */
                if (Yii::app()->contEd('pla') && $model->associationType === 'anoncontact')
                    $model->associationName = "Anonymous Contact #".$model->associationId;
                else
                /* x2plaend */
                $model->associationName = $association->name;
                if($association->hasAttribute('lastActivity')){
                    $association->lastActivity = time();
                    $association->update(array('lastActivity'));
                    X2Flow::trigger('RecordUpdateTrigger', array(
                        'model' => $association,
                    ));
                }
            } else
                $model->associationName = 'none';

            if($model->associationName == 'None' && $model->associationType != 'none')
                $model->associationName = ucfirst($model->associationType);

            if(in_array($_POST['SelectedTab'],array(/* x2prostart */'products',/* x2proend */'log-a-call','new-comment','log-time-spent'))){
                // Set the complete date accordingly:
                if(!empty($_POST[get_class($model)]['completeDate'])) {
                    $model->completeDate = Formatter::parseDateTime(
                        $_POST[get_class($model)]['completeDate']);
                }
                foreach(array('dueDate','completeDate') as $attr)
                    if(empty($model->$attr))
                        $model->$attr = time();
                if($model->dueDate > $model->completeDate) {
                    // User specified a negative time range! Let's say that the
                    // starting time is equal to when it ended (which is earlier)
                    $model->dueDate = $model->completeDate;
                }
                $model->complete = 'Yes';
                $model->visibility = '1';
                $model->assignedTo = Yii::app()->user->getName();
                $model->completedBy = Yii::app()->user->getName();
                if($_POST['SelectedTab'] == 'log-a-call') {
                    $model->type = 'call';
                } elseif($_POST['SelectedTab'] == 'log-time-spent') {
                    $model->type = 'time';
                /* x2prostart */ 
                } elseif($_POST['SelectedTab'] == 'products') {
                    $model->type = 'products';
                /* x2proend */ 
                } else {
                    $model->type = 'note';
                }
            }
            if(in_array($model->type, array('call','time','note'))){
                $event = new Events;
                $event->associationType = 'Actions';
                $event->type = 'record_create';
                $event->user = Yii::app()->user->getName();
                $event->visibility = $model->visibility;
                $event->subtype = $model->type;
            }
            // save model
            $model->createDate = time();

            if(!empty($model->type))
                $model->disableBehavior('changelog');

            /* x2prostart */
            if(!empty($_POST['timers'])) {
                $model->skipActionTimers = true;
            }
            /* x2proend */
            if($model->save()){ // action saved to database *
                if(isset($_POST['Actions']['reminder']) && $_POST['Actions']['reminder']){
                    $model->createNotifications(
                            $_POST['notificationUsers'], 
                            $model->dueDate - ($_POST['notificationTime'] * 60),
                            'action_reminder');
                }
                /* x2prostart */
                // Adjust the time according to timers given and associate all
                // timer records with this action
                if(!empty($_POST['timers'])){
                    $timerIds = explode(',', $_POST['timers']);
                    $params = array();
                    foreach($timerIds as $id){
                        $params[":timer$id"] = $id;
                    }
                    $wherein = '('.implode(',', array_keys($params)).')';
                    Yii::app()->db->createCommand()
                            ->update(ActionTimer::model()->tableName(), 
                                     array('actionId' => $model->id),
                                     "`id` IN ".$wherein, $params);
                    $timeSpent = ActionTimer::actionTimeSpent($model->id);
                    if($timeSpent > 0){
                        $model->timeSpent = $timeSpent;
                        $model->update(array('timeSpent'));
                    }
                }
                if (isset ($_POST['lineitem'])) {
                   $model->setActionLineItems ($_POST['lineitem']);
                }
                /* x2proend */
                X2Model::updateTimerTotals(
                    $model->associationId,X2Model::getModelName($model->associationType));

                if(isset($event)){
                    $event->associationId = $model->id;
                    $event->save();
                }
                $model->syncGoogleCalendar('create', true);
            }else{
                if($model->hasErrors('verifyCode')){
                    echo CJSON::encode (array ('error' => $model->getError('verifyCode')));
                    Yii::app()->end ();
                }
            }
            echo CJSON::encode (array ('success'));
            Yii::app()->end ();
        } else {
            throw new CHttpException (400, Yii::t('app', 'Bad request'));
        }
    }

    /**
     * Create a menu for Actions
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Action = Modules::displayName(false);
        $Actions = Modules::displayName();
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'list', 'todays', 'my', 'everyones', 'create', 'view', 'edit', 'share',
         *     'delete', 'import', 'export',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'list',
                'label'=>Yii::t('actions','{module} List', array(
                    '{module}' => Modules::displayName(false),
                )),
                'url'=>array('index'),
            ),
            array(
                'name'=>'todays',
                'label'=>Yii::t('actions','Today\'s {module}', array(
                    '{module}' => $Actions,
                )),
                'url'=>array('index'),
            ),
            array(
                'name'=>'my',
                'label'=>Yii::t('actions','All My {module}', array(
                    '{module}' => $Actions,
                )),
                'url'=>array('viewAll')
            ),
            array(
                'name'=>'everyones',
                'label'=>Yii::t('actions','Everyone\'s {module}', array(
                    '{module}' => $Actions,
                )),
                'url'=>array('viewGroup')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('actions','Create {module}', array(
                    '{module}' => $Action,
                )),
                'url'=>array('create','param'=>Yii::app()->user->getName().";none:0")
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('actions','View'),
                'url'=>array('view', 'id'=>$modelId),
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('actions','Edit {module}', array(
                    '{module}' => $Action,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'share',
                'label'=>Yii::t('contacts','Share {module}', array(
                    '{module}' => $Action,
                )),
                'url'=>array('shareAction','id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('actions','Delete {module}', array(
                    '{module}' => $Action,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?')
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('actions', 'Import {module}', array(
                    '{module}' => $Actions,
                )),
                'url'=>array('admin/importModels', 'model'=>'Actions'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('actions', 'Export {module}', array(
                    '{module}' => $Actions,
                )),
                'url'=>array('admin/exportModels', 'model'=>'Actions'),
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

    public function update($model, $oldAttributes, $api){

        // now in Actions::beforeSave()
        /* $model->dueDate = Formatter::parseDateTime($model->dueDate);

          if($model->completeDate)
          $model->completeDate = Formatter::parseDateTime($model->completeDate);

          $association = $this->getAssociation($model->associationType,$model->associationId);

          if($association != null) {
          $model->associationName = $association->name;
          } else {
          $model->associationName = 'None';
          $model->associationId = 0;
          } */

        // now in Actions::synchGoogleCalendar()
        /* if( !is_numeric($model->assignedTo)) { // assigned to user
          $profile = Profile::model()->findByAttributes(array('username'=>$model->assignedTo));
          if(isset($profile)) // prevent error for actions assigned to 'Anyone'
          $profile->updateGoogleCalendarEvent($model); // update action in Google Calendar if user has a Google Calendar
          } else { // Assigned to group
          $groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
          foreach($groups as $group) {
          $profile = Profile::model()->findByPk($group['userId']);
          if(isset($profile)) // prevent error for actions assigned to 'Anyone'
          $profile->updateGoogleCalendarEvent($model);
          }
          } */

        if($api == 0)
            parent::update($model, $oldAttributes, $api);
        else
            return parent::update($model, $oldAttributes, $api);
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id){
        $model = $this->loadModel($id);
        $users = User::getNames();
        $notifications = X2Model::model('Notification')->findAllByAttributes(array(
            'modelType' => 'Actions',
            'modelId' => $model->id,
            'type' => 'action_reminder'
                ));
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Actions'])){
            $oldAttributes = $model->attributes;
            $model->setX2Fields($_POST['Actions']);
            if($model->lastUpdated != $oldAttributes['lastUpdated']){
                $model->disableBehavior('X2TimestampBehavior');
            }
            if($model->dueDate != $oldAttributes['dueDate']){
                $event = CActiveRecord::model('Events')->findByAttributes(array('type' => 'action_reminder', 'associationType' => 'Actions', 'associationId' => $model->id));
                if(isset($event)){
                    $event->timestamp = $model->dueDate;
                    $event->update(array('timestamp'));
                }
            }

            /* x2prostart */
            if(isset($_POST['ActionTimer'])) {
                // Update the action timers according to the form data
                $oldTimers = $model->timers;
                $timersById = array();
                foreach($oldTimers as $timer) {
                    $timersById[$timer->id] = $timer;
                    $oldTimerIDs[] = $timer->id;
                }
                $newTimerIDs = $_POST['ActionTimer']['id'];
                
                // If they were deleted from the form they won't show up in form data
                $dateTimeFormat = Yii::app()->locale->getDateFormat('medium')
                    .' '.Yii::app()->locale->getTimeFormat('medium');
                foreach($newTimerIDs as $ind => $id) {
                    foreach(array('timestamp','endtime') as $attr) {
                        $timersById[$id]->$attr = CDateTimeParser::parse(
                            $_POST['ActionTimer'][$attr][$ind],
                            $dateTimeFormat
                        );
                    }
                    $timersById[$id]->type = $_POST['ActionTimer']['type'][$ind];
                    $timersById[$id]->save();
                }

                // Delete all timer records that were removed from the form:
                $delCriteria = new CDbCriteria();
                $delCriteria->addInCondition('id',array_diff($oldTimerIDs,$newTimerIDs));
                ActionTimer::model()->deleteAll($delCriteria);
                X2Model::updateTimerTotals($model->associationId,X2Model::getModelName($model->associationType));
            }
            if (isset ($_POST['lineitem'])) {
               $model->setActionLineItems ($_POST['lineitem']);
            }
            /* x2proend */

            // $this->update($model,$oldAttributes,'0');
            if($model->save()){
                if(isset($_POST['Actions']['reminder']) && $_POST['Actions']['reminder']
                        && X2Model::model('Notification')->countByAttributes(array(
                            'modelType' => 'Actions',
                            'modelId' => $model->id,
                            'type' => 'action_reminder'
                                )
                        ) != 0){
                    $notifications = X2Model::model('Notification')->findAllByAttributes(array(
                        'modelType' => 'Actions',
                        'modelId' => $model->id,
                        'type' => 'action_reminder'
                            ));
                    // TODO: unit test. refactor notifications deletion into model
                    // if new notifications were added, delete the old ones
                    foreach($notifications as $notification){
                        if ($model->isAssignedTo ($notification->user, true) && 
                           ($_POST['notificationUsers'] == 'assigned' || 
                            $_POST['notificationUsers'] == 'both')){

                            $notification->delete();
                        }elseif($notification->user == Yii::app()->user->getName() && 
                            ($_POST['notificationUsers'] == 'me' || $_POST['notificationUsers'] == 'both')){

                            $notification->delete();
                        }
                    }
                }elseif(isset($_POST['Actions']['reminder']) && $_POST['Actions']['reminder']){
                    $model->createNotifications (
                        $_POST['notificationUsers'],
                        $model->dueDate - ($_POST['notificationTime'] * 60), 'action_reminder');
                }
                if(Yii::app()->user->checkAccess('ActionsAdmin') || Yii::app()->settings->userActionBackdating){
                    $events = X2Model::model('Events')->findAllByAttributes(array(
                        'associationType' => 'Actions',
                        'associationId' => $model->id,
                    ));
                    foreach($events as $event) {
                        $event->timestamp = $model->getRelevantTimestamp();
                        $event->update(array('timestamp'));
                    }
                }
                $model->syncGoogleCalendar('update');
                if(isset($_GET['redirect']) && $model->associationType != 'none'){ // if the action has an association
                    if($model->associationType == 'product' || $model->associationType == 'products')
                        $this->redirect(array('/products/products/view', 'id' => $model->associationId));
                    //TODO: avoid such hackery
                    elseif($model->associationType == 'Campaign')
                        $this->redirect(array('/marketing/marketing/view', 'id' => $model->associationId));
                    else
                        $this->redirect(array('/'.$model->associationType.'/'.$model->associationType.'/view', 'id' => $model->associationId)); // go back to the association
                } elseif(!Yii::app()->request->isAjaxRequest){ // no association
                    $this->redirect(array('index')); // view the action
                }else{
                    echo $this->renderPartial('_viewIndex', array('data' => $model), true);
                    return;
                }
            }
        }
        if(count($notifications) > 0){
            if(count($notifications) > 1){
                $notifType = 'both';
            }else{
                $notifType = 'assigned';
            }
            $notifTime = ($model->dueDate - $notifications[0]->createDate) / 60;
        }else{
            $notifType = '';
            $notifTime = '';
        }

        /* Set assignedTo back into an array only before re-rendering the input box with assignees 
           selected */
        $model->assignedTo = array_map(function($n){
            return trim($n,',');
        },explode(' ',$model->assignedTo));

        $this->render('update', array(
            'model' => $model,
            'users' => $users,
            'notifType' => $notifType,
            'notifTime' => $notifTime,
        ));
    }

    public function actionCopyEvent ($id) {
        $modelClass = $this->modelClass;
        $model = $this->loadModel ($id);
        $model->setX2Fields ($_POST[$modelClass]);
        $model->id = null;
        $copy = new $modelClass;
        $copy->setAttributes ($model->getAttributes (), false);
        if ($copy->save ()) {
            $copy->syncGoogleCalendar('create');
            echo $this->ajaxResponse ('success');
        } else {
            echo $this->ajaxResponse ('failure');
        }
    }

    public function actionQuickUpdate($id){
        $model = $this->loadModel($id);
        if(isset($_POST['Actions'])){
            $model->setX2Fields($_POST['Actions']);

            $model->dueDate = Formatter::parseDateTime($model->dueDate);
            if($model->completeDate){
                $model->completeDate = Formatter::parseDateTime($model->completeDate);
            }elseif(empty($model->completeDate)){
                $model->completeDate = $model->dueDate;
            }
            if($model->save()){
                $model->syncGoogleCalendar('update');
            }
            if (isset($_POST['isEvent']) && $_POST['isEvent']) {
                // Update calendar event
                $event = X2Model::model('Events')->findByAttributes(array(
                    'associationType' => 'Actions',
                    'associationId' => $model->id,
                ));
                if ($event !== null) {
                    $event->timestamp = $model->dueDate;
                    $event->update(array('timestamp'));
                }
            }
        }
    }

    public function actionToggleSticky($id){
        $action = X2Model::model('Actions')->findByPk($id);
        if(isset($action)){
            $action->sticky = !$action->sticky;
            $action->update(array('sticky'));
            echo $action->sticky;
        }
    }

    // Postpones due date (and sets action to incomplete)
    /* public function actionTomorrow($id) {
      $model = $this->loadModel($id);
      $model->complete='No';
      $model->dueDate=time()+86400;	//set to tomorrow
      if($model->save()){
      if($model->associationType!='none')
      $this->redirect(array($model->associationType.'/'.$model->associationId));
      else
      $this->redirect(array('view','id'=>$id));
      }
      } */

    /**
     * API method to delete an action
     * @param integer $id The id of the action
     */
    public function delete($id){
        $model = $this->loadModel($id);
        $this->cleanUpTags($model);
        $model->delete();
    }

    /**
     * Deletes an action
     * @param integer $id The id of the action
     */
    public function actionDelete($id){

        $model = $this->loadModel($id);
        if(Yii::app()->request->isPostRequest){
            // $this->cleanUpTags($model);	// now in TagBehavior
            $event = new Events;
            $event->type = 'record_deleted';
            $event->associationType = $this->modelClass;
            $event->associationId = $model->id;
            $event->text = $model->name;
            $event->visibility = $model->visibility;
            $event->user = Yii::app()->user->getName();
            $event->save();
            Events::model()->deleteAllByAttributes(array('associationType' => 'Actions', 'associationId' => $id, 'type' => 'action_reminder'));

            $model->syncGoogleCalendar('delete');

            /* if(!is_numeric($model->assignedTo)) { // assigned to user
              $profile = Profile::model()->findByAttributes(array('username'=>$model->assignedTo));
              if(isset($profile))
              $profile->deleteGoogleCalendarEvent($model); // update action in Google Calendar if user has a Google Calendar
              } else { // Assigned to group
              $groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
              foreach($groups as $group) {
              $profile = Profile::model()->findByPk($group['userId']);
              if(isset($profile))
              $profile->deleteGoogleCalendarEvent($model);
              } */

            $model->delete();
        }else{
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']) && !Yii::app()->request->isAjaxRequest)
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        // Only report the success of a deleted record if this request wasn't made via mass actions
        else if (!isset($_POST['gvSelection']))
            echo 'success';
    }

    /**
     * Marks an action as complete and redirects back to the page it was completed on.
     * @param integer $id The id of the action
     */
    public function actionComplete($id){
        $model = $this->loadModel($id);
        if(isset($_GET['notes'])){
            $notes = $_GET['notes'];
        }else{
            $notes = null;
        }

        if($model->isAssignedTo (Yii::app()->user->getName ()) ||
           Yii::app()->params->isAdmin){ // make sure current user can edit this action

            if(isset($_POST['note']) && !empty($_POST['note']))
                $model->actionDescription = $model->actionDescription."\n\n".$_POST['note'];

            // $model = $this->updateChangelog($model,'Completed');
            $model->complete(null, $notes);

            // Actions::completeAction($id);
            // $this->completeNotification('admin',$model->id);

            $createNew = isset($_GET['createNew']) || ((isset($_POST['submit']) && ($_POST['submit'] == 'completeNew')));
            $redirect = isset($_GET['redirect']) || $createNew;

            if($redirect){
                if($model->associationType != 'none' && !$createNew){ // if the action has an association
                    $this->redirect(array('/'.$model->associationType.'/'.$model->associationType.'/view', 'id' => $model->associationId)); // go back to the association
                }else{ // no association
                    if($createNew)
                        $this->redirect(array('/actions/actions/create'));  // go to blank 'create action' page
                    else
                        $this->redirect(array('index')); // view the action
                }
            } elseif(Yii::app()->request->isAjaxRequest){
                echo "Success";
            }else{
                $this->redirect(array('index'));
            }
        }elseif(Yii::app()->request->isAjaxRequest){
            echo "Failure";
        }else{
            $this->redirect(array('/actions/actions/invalid'));
        }
    }

    /**
     * Marks an action as incomplete and clears the completedBy field.
     * @param integer $id The id of the action
     */
    public function actionUncomplete($id){
        $model = $this->loadModel($id);
        if($model->uncomplete()){
            if(Yii::app()->request->isAjaxRequest) {
                echo 'success';
            }else{
                $this->redirect(array('/actions/'.$id));
            }
        }
    }

    /**
     * Called when a Contact opens an email sent from Inline Email Form. Inline Email Form
     * appends an image to the email with src pointing to this function. This function
     * creates an action associated with the Contact indicating that the email was opened.
     *
     * @param integer $uid The unique id of the recipient
     * @param string $type 'open', 'click', or 'unsub'
     *
     */
    public function actionEmailOpened($uid, $type){
        // If the request is coming from within the web application, ignore it.
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $baseUrl = Yii::app()->request->getBaseUrl(true);
        $fromApp = strpos($referrer, $baseUrl) === 0;

        if($type == 'open' && !$fromApp){
            $track = TrackEmail::model()->findByAttributes(array('uniqueId' => $uid));
            if($track && $track->opened == null){
                $action = $track->action;
                if($action){
                    $note = new Actions;
                    switch($action->type){
                        case 'email_quote':
                        case 'email_invoice':
                            $subType = str_replace('email_','',$action->type);
                            $note->type = "emailOpened_$subType";
                            $quote = Quote::model()->findByPk($action->associationId);
                            if($quote instanceof Quote) {
                                $contact = $quote->associatedContactsModel;
                                if($contact instanceof Contacts){
                                    $note->associationType = 'contacts';
                                    $note->associationId = $contact->id;
                                }
                            }
                            break;
                        default:
                            $note->type = 'emailOpened';
                            $note->associationType = $action->associationType;
                            $note->associationId = $action->associationId;
                    }
                    $now = time();
                    $note->createDate = $now;
                    $note->lastUpdated = $now;
                    $note->completeDate = $now;
                    $note->complete = 'Yes';
                    $note->updatedBy = 'admin';
                    
                    $note->associationName = $action->associationName;
                    $note->visibility = $action->visibility;
                    $note->assignedTo = $action->assignedTo;
                    $note->actionDescription = Yii::t('marketing', 'Contact has opened the email sent on ');
                    $note->actionDescription .= Formatter::formatLongDateTime($action->createDate)."<br>";
                    $note->actionDescription .= $action->actionDescription;
                    if($note->save()){
                        $event = new Events;
                        $event->type = 'email_opened';
                        switch($action->type){
                            case 'email_quote':
                                $event->subtype = 'quote';
                                break;
                            case 'email_invoice':
                                $event->subtype = 'invoice';
                                break;
                            default:
                                $event->subtype = 'email';
                        }
                        
                        $contact = isset($quote) && $quote instanceof Quote
                                ? $quote->associatedContactsModel
                                : X2Model::model('Contacts')->findByPk($action->associationId);
                        if(isset($contact)){
                            $event->user = $contact->assignedTo;
                        }
                        $event->associationType = 'Contacts';
                        $event->associationId = isset($contact) ? $contact->id : $note->associationId;
                        if($action->associationType == 'services'){
                            $case = X2Model::model('Services')->findByPk($action->associationId);
                            if(isset($case) && is_numeric($case->contactId)){
                                $event->associationId = $case->contactId;
                            }elseif(isset($case)){
                                $event->associationType = 'Services';
                                $event->associationId = $case->id;
                            }
                        }
                        $event->save();
                        $track->opened = $now;
                        $track->update();
                    }
                }
            }
        }
        //return a one pixel transparent png
        header('Content-Type: image/png');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAAXNSR0IArs4c6QAAAAJiS0dEAP+Hj8y/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAC0lEQVQI12NgYAAAAAMAASDVlMcAAAAASUVORK5CYII=');
    }

    // Lists all actions assigned to this user
    public function actionIndex(){
        if(isset($_GET['toggleView']) && $_GET['toggleView']){
            if(Yii::app()->params->profile->oldActions){
                Yii::app()->params->profile->oldActions = 0;
            }else{
                Yii::app()->params->profile->oldActions = 1;
            }
            Yii::app()->params->profile->update(array('oldActions'));
            $this->redirect(array('index'));
        }
        $model = new Actions('search');
        if(!isset(Yii::app()->params->profile->oldActions) || 
           !Yii::app()->params->profile->oldActions){

            if(!empty($_POST) || !empty(Yii::app()->params->profile->actionFilters)){
                if(isset($_POST['complete'], $_POST['assignedTo'], $_POST['dateType'],
                    $_POST['dateRange'], $_POST['orderType'], $_POST['order'], $_POST['start'],
                    $_POST['end'])){

                    $complete = $_POST['complete'];
                    $assignedTo = $_POST['assignedTo'];
                    $dateType = $_POST['dateType'];
                    $dateRange = $_POST['dateRange'];
                    $orderType = $_POST['orderType'];
                    $order = $_POST['order'];
                    $start = $_POST['start'];
                    $end = $_POST['end'];
                    if($dateRange != 'range'){
                        $start = null;
                        $end = null;
                    }
                    $filters = array(
                        'complete' => $complete, 'assignedTo' => $assignedTo,
                        'dateType' => $dateType, 'dateRange' => $dateRange,
                        'orderType' => $orderType, 'order' => $order, 'start' => $start,
                        'end' => $end);
                }elseif(!empty(Yii::app()->params->profile->actionFilters)){
                    $filters = json_decode(Yii::app()->params->profile->actionFilters, true);
                }
                $condition = Actions::createCondition($filters);
                $dataProvider = $model->search($condition);
                $params = $filters;
            }else{
                $dataProvider = $model->search();
                $params = array();
            }
            $this->render('index', array(
                'model' => $model,
                'dataProvider' => $dataProvider,
                'params' => $params,
            ));
        }else{
            $this->render('oldIndex', array('model' => $model));
        }
    }

    // List all public actions
    public function actionViewAll(){
        $model = new Actions('search');
        $profile = Profile::model()->findByPk(Yii::app()->user->id);

        $this->render(
            'oldIndex',
            array(
                'model' => $model,
                'showActions' => $profile->showActions,
            )
        );
    }

    public function actionViewGroup(){
        $model = new Actions('search');
        $this->render('oldIndex', array('model' => $model));
    }

    // display error page
    public function actionInvalid(){
        $this->render('invalid');
    }

    public function actionParseType(){
        if(isset($_POST['Actions']['associationType'])){
            $type = $_POST['Actions']['associationType'];
            if($modelName = X2Model::getModelName($type)){
                $linkModel = $modelName;
                if(class_exists($linkModel)){
                    if($linkModel == "X2Calendar")
                        $linkSource = ''; // Return no data to disable autocomplete on actions/update
                    else
                        $linkSource = $this->createUrl(X2Model::model($linkModel)->autoCompleteSource);
                }else{
                    $linkSource = "";
                }
                echo $linkSource;
            }else{
                echo '';
            }
        }else{
            echo '';
        }
    }

    public function getAssociation($type, $id){
        return X2Model::getAssociationModel($type, $id);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id){
        $model = CActiveRecord::model('Actions')->findByPk((int) $id);
        //$dueDate=$model->dueDate;
        //$model=Actions::changeDates($model);
        // if($model->associationId!=0) {
        // $model->associationName = $this->parseName(array($model->associationType,$model->associationId));
        // } else
        // $model->associationName = 'None';

        if($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }


    public function actionGetItems(){
        $model = X2Model::model ($this->modelClass);
        if (isset ($model)) {
            $tableName = $model->tableName ();
            $sql = 
                'SELECT id, subject as value
                 FROM '.$tableName.' WHERE subject LIKE :qterm ORDER BY subject ASC';
            $command = Yii::app()->db->createCommand($sql);
            $qterm = $_GET['term'].'%';
            $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
            $result = $command->queryAll();
            echo CJSON::encode($result);
        }
        Yii::app()->end();
    }

    


    /***********************************************************************
    * protected static methods
    ***********************************************************************/

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model){
        if(isset($_POST['ajax']) && $_POST['ajax'] === 'actions-form'){
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
