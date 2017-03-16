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
 * @package application.modules.users.controllers
 */
class UsersController extends x2base {

    public $modelClass = 'User';
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'actions'=>array('createAccount'),
                'users'=>array('*')
            ),
            array('allow',
                'actions'=>array('addTopContact','removeTopContact'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('view','index','create','update','admin','delete','search','inviteUsers'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex(){
        $this->redirect('admin');
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $user=User::model()->findByPk($id);
        $dataProvider=new CActiveDataProvider('Actions', array(
            'criteria'=>array(
                'order'=>'complete DESC',
                'condition'=>'assignedTo=\''.$user->username.'\'',
        )));
        $actionHistory=$dataProvider->getData();
        $this->render('view',array(
            'model'=>$this->loadModel($id),
            'actionHistory'=>$actionHistory,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new User;
        $groups=array();
        foreach(Groups::model()->findAll() as $group){
            $groups[$group->id]=$group->name;
        }
        $roles=array();
        foreach(Roles::model()->findAll() as $role){
            $roles[$role->id]=$role->name;
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        $unhashedPassword = '';
        if(isset($_POST['User'])) {
            $model->attributes=$_POST['User'];
            //$this->updateChangelog($model);
            $unhashedPassword = $model->password;
            $model->password = md5($model->password);
            $model->userKey=substr(str_shuffle(str_repeat(
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
            $profile=new Profile;
            $profile->fullName=$model->firstName." ".$model->lastName;
            $profile->username=$model->username;
            $profile->allowPost=1;
            $profile->emailAddress=$model->emailAddress;
            $profile->status=$model->status;

            /* x2plastart */ 
            // set a default theme if there is one
            $admin = Yii::app()->settings;
            if ($admin->defaultTheme) {
                $profile->theme = $profile->getDefaultTheme ();
            }
            /* x2plaend */ 

            if($model->save()){
                $profile->id=$model->id;
                $profile->save();
                if(isset($_POST['roles'])){
                    $roles=$_POST['roles'];
                    foreach($roles as $role){
                        $link=new RoleToUser;
                        $link->roleId=$role;
                        $link->userId=$model->id;
                        $link->type="user";
                        $link->save();
                    }
                }
                if(isset($_POST['groups'])){
                    $groups=$_POST['groups'];
                    foreach($groups as $group){
                        $link=new GroupToUser;
                        $link->groupId=$group;
                        $link->userId=$model->id;
                        $link->username=$model->username;
                        $link->save();
                    }
                }
                $this->redirect(array('view','id'=>$model->id));
            }
        }
        $model->password = $unhashedPassword;

        $this->render('create',array(
            'model'=>$model,
            'groups'=>$groups,
            'roles'=>$roles,
            'selectedGroups'=>array(),
            'selectedRoles'=>array(),
        ));
    }

    public function actionCreateAccount(){
        $this->layout='//layouts/login';
        if(isset($_GET['key'])){
            $key=$_GET['key'];
            $user=User::model()->findByAttributes(array('inviteKey'=>$key));
            if(isset($user)){
                $user->setScenario('insert');
                if($key==$user->inviteKey){
                    if(isset($_POST['User'])) {
                        $model=$user;
                        $model->attributes=$_POST['User'];
                        $model->status=1;
                        //$this->updateChangelog($model);
                        $model->password = md5($model->password);
                        $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
                        $profile=new Profile;
                        $profile->fullName=$model->firstName." ".$model->lastName;
                        $profile->username=$model->username;
                        $profile->allowPost=1;
                        $profile->emailAddress=$model->emailAddress;
                        $profile->status=$model->status;

                        if($model->save()){
                            $model->inviteKey=null;
                            $model->temporary=0;
                            $model->save();
                            $profile->id=$model->id;
                            $profile->save();
                            $this->redirect(array('/site/login'));
                        }
                    }
                    $this->render('createAccount',array(
                        'user'=>$user,
                    ));
                }else{
                    $this->redirect($this->createUrl('/site/login'));
                }
            }else{
                $this->redirect($this->createUrl('/site/login'));
            }
        }else{
            $this->redirect($this->createUrl('/site/login'));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model=$this->loadModel($id);
        $groups=array();
        foreach(Groups::model()->findAll() as $group){
            $groups[$group->id]=$group->name;
        }
        $selectedGroups=array();
        foreach(GroupToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
            $selectedGroups[]=$link->groupId;
        }
        $roles=array();
        foreach(Roles::model()->findAll() as $role){
            $roles[$role->id]=$role->name;
        }
        $selectedRoles=array();
        foreach(RoleToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
            $selectedRoles[]=$link->roleId;
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (!isset($model->userAlias))
            $model->userAlias = $model->username;

        if(isset($_POST['User'])) {
            $old=$model->attributes;
            $temp=$model->password;
            $model->attributes=$_POST['User'];

            if($model->password!="")
                $model->password = md5($model->password);
            else
                $model->password=$temp;
            if(empty($model->userKey)){
                $model->userKey=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 32)), 0, 32);
            }
            if($model->save()){
                $profile = $model->profile;
                if(!empty($profile)) {
                    $profile->emailAddress = $model->emailAddress;
                    $profile->fullName = $model->firstName.' '.$model->lastName;
                    $profile->save();
                }
                if($old['username']!=$model->username){
                    $fieldRecords=Fields::model()->findAllByAttributes(array('fieldName'=>'assignedTo'));
                    $modelList=array();
                    foreach($fieldRecords as $record){
                        $modelList[$record->modelName]=$record->linkType;
                    }
                    foreach($modelList as $modelName=>$type){
                        if($modelName=='Quotes')
                            $modelName="Quote";
                        if($modelName=='Products')
                            $modelName='Product';
                        if(empty($type)){
                            $list=X2Model::model($modelName)->findAllByAttributes(array('assignedTo'=>$old['username']));
                            foreach($list as $item){
                                $item->assignedTo=$model->username;
                                $item->save();
                            }
                        }else{
                            $list=X2Model::model($modelName)->findAllBySql(
                                    "SELECT * FROM ".X2Model::model($modelName)->tableName()
                                    ." WHERE assignedTo LIKE '%".$old['username']."%'");
                            foreach($list as $item){
                                $assignedTo=explode(", ",$item->assignedTo);
                                $key=array_search($old['username'],$assignedTo);
                                if($key>=0){
                                    $assignedTo[$key]=$model->username;
                                }
                                $item->assignedTo=implode(", ",$assignedTo);
                                $item->save();
                            }
                        }
                    }

                    $profile=Profile::model()->findByAttributes(array('username'=>$old['username']));
                    if(isset($profile)){
                        $profile->username=$model->username;
                        $profile->save();
                    }

                }
                foreach(RoleToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $link->delete();
                }
                foreach(GroupToUser::model()->findAllByAttributes(array('userId'=>$model->id)) as $link){
                    $link->delete();
                }
                if(isset($_POST['roles'])){
                    $roles=$_POST['roles'];
                    foreach($roles as $role){
                        $link=new RoleToUser;
                        $link->roleId=$role;
                        $link->type="user";
                        $link->userId=$model->id;
                        $link->save();
                    }
                }
                if(isset($_POST['groups'])){
                    $groups=$_POST['groups'];
                    foreach($groups as $group){
                        $link=new GroupToUser;
                        $link->groupId=$group;
                        $link->userId=$model->id;
                        $link->username=$model->username;
                        $link->save();
                    }
                }
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $this->render('update',array(
            'model'=>$model,
            'groups'=>$groups,
            'roles'=>$roles,
            'selectedGroups'=>$selectedGroups,
            'selectedRoles'=>$selectedRoles,
        ));
    }

    public function actionInviteUsers(){

        if(isset($_POST['emails'])){
            $list=$_POST['emails'];

            $body="Hello,

You are receiving this email because your X2Engine administrator has invited you to create an account.
Please click on the link below to create an account at X2Engine!

";

            $subject="Create Your X2Engine User Account";
            $list=trim($list);
            $emails=explode(',',$list);
            foreach($emails as &$email){
                $key=substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',16)),0,16);
                $user=new User('invite');
                $email=trim($email);
                $user->inviteKey=$key;
                $user->temporary=1;
                $user->emailAddress=$email;
                $user->status=0;
                $userList=User::model()->findAllByAttributes(array('emailAddress'=>$email,'temporary'=>1));
                foreach($userList as $userRecord){
                    if(isset($userRecord)){
                        $userRecord->delete();
                    }
                }
                $user->save();
                $link=CHtml::link('Create Account',(@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('/users/users/createAccount',array('key'=>$key)));
                $mail=new InlineEmail;
                $mail->to=$email;
                // Get email password
                $cred = Credentials::model()->getDefaultUserAccount(Credentials::$sysUseId['systemResponseEmail'],'email');
                if($cred==Credentials::LEGACY_ID)
                    $cred = Credentials::model()->getDefaultUserAccount(Yii::app()->user->id,'email');
                if($cred != Credentials::LEGACY_ID)
                    $mail->credId = $cred;
                $mail->subject=$subject;
                $mail->message=$body."<br><br>".$link;
                $mail->contactFlag=false;
                if($mail->prepareBody()){
                    $mail->deliver();
                }else{
                }
            }
            $this->redirect('admin');
        }

        $this->render('inviteUsers');
    }

    public function actionDeleteTemporary(){
        $deleted=User::model()->deleteAllByAttributes(array('temporary'=>1));
        $this->redirect('admin');
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model=new User('search');
        $this->render('admin',array('model'=>$model,'count'=>User::model()->countByAttributes(array('temporary'=>1))));
    }

    public function actionDelete($id) {
        if($id != 1){
            $model=$this->loadModel($id);
            if(Yii::app()->request->isPostRequest) {
                $model->delete();
            } else {
                throw new CHttpException(
                    400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
            }
            /* if AJAX request (triggered by deletion via admin grid view), we should not redirect 
            the browser */
            if(!isset($_GET['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
        }else{
            throw new CHttpException(
                400,Yii::t('app','Cannot delete admin user.  Please do not repeat this request.'));
        }
    }

    public function actionAddTopContact() {
        if(isset($_GET['contactId']) && is_numeric($_GET['contactId'])) {

            $id = Yii::app()->user->getId();
            $model=$this->loadModel($id);

            $topContacts = empty($model->topContacts) ? array() : explode(',',$model->topContacts);

            // only add to list if it isn't already in there
            if(!in_array($_GET['contactId'],$topContacts)) {        
                array_unshift($topContacts,$_GET['contactId']);
                $model->topContacts = implode(',',$topContacts);
            }
            if ($model->update())
                $this->renderTopContacts();
            // else
                // echo print_r($model->getErrors());

        }
    }

    public function actionRemoveTopContact() {
        if(isset($_GET['contactId']) && is_numeric($_GET['contactId'])) {

            $id = Yii::app()->user->getId();
            $model=$this->loadModel($id);

            $topContacts = empty($model->topContacts)? array() : explode(',',$model->topContacts);
            $index = array_search($_GET['contactId'],$topContacts);

            if($index!==false)
                unset($topContacts[$index]);

            $model->topContacts = implode(',',$topContacts);

            if ($model->update()) {
                $this->renderTopContacts();
            } else {
                //AuxLib::debugLogR ($model->getErrors ());
            }
        }
    }

    private function renderTopContacts() {
        $this->renderPartial('application.components.views.topContacts',array(
            'topContacts'=>User::getTopContacts(),
            //'viewId'=>$viewId
        ));
    }

    /**
     * Create a menu for Users
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Users = Modules::displayName();
        $User = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'feed', 'admin', 'create', 'invite', 'view', 'profile', 'edit', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'feed',
                'label'=>Yii::t('profile','Social Feed'),
                'url'=>array('/profile/index')
            ),
            array(
                'name'=>'admin',
                'label' => Yii::t('users', 'Manage {users}', array(
                    '{users}' => $Users,
                )),
                'url'=>array('admin')
            ),
            array(
                'name'=>'create',
                'label' => Yii::t('users', 'Create {user}', array(
                    '{user}' => $User,
                )),
                'url' => array('create')
            ),
            array(
                'name'=>'invite',
                'label' => Yii::t('users', 'Invite {users}', array(
                    '{users}' => $Users,
                )),
                'url' => array('inviteUsers')
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('users','View {user}', array(
                    '{user}' => $User,
                )),
                'url'=>array('view', 'id'=>$modelId)
            ),
            array(
                'name'=>'profile',
                'label'=>Yii::t('profile','View Profile'),
                'url'=>array('/profile/view','id'=>$modelId)
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('users','Update {user}', array(
                    '{user}' => $User,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('users','Delete {user}', array(
                    '{user}' => $User,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }


}
