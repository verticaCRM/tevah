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
 * @package application.modules.media.controllers
 */
class PortfolioMediaController extends x2base {

    public $modelClass = "PortfolioMedia";

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            /*
            uncomment when media module supports custom forms
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),*/
        ));
    }

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){

        // add media object to user's recent item list
        User::addRecentItem('m', $id, Yii::app()->user->getId()); 

        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Forces download of specified media file
     */
    public function actionDownload($id){
        $model = $this->loadModel($id);
        $filePath = $model->getPath();
        if ($filePath != null)
            $file = Yii::app()->file->set($filePath);
        else
            throw new CHttpException(404);
        if($file->exists)
            $file->send();
        //Yii::app()->getRequest()->sendFile($model->fileName,@file_get_contents($fileName));
        $this->redirect(array('view', 'id' => $id));
    }

    /**
     * Alias for actionUpload
     */
    public function actionCreate () {
        $this->actionUpload ();
    }

    private function createAttachmentAction ($model) {
        if(!empty($model->associationType) && !empty($model->associationId) && 
            is_numeric($model->associationId)){

            $note = new Actions;
            $note->createDate = time();
            $note->dueDate = time();
            $note->completeDate = time();
            $note->complete = 'Yes';
            $note->visibility = '1';
            $note->completedBy = Yii::app()->user->getName();
            if($model->private){
                $note->assignedTo = Yii::app()->user->getName();
                $note->visibility = '0';
            }else{
                $note->assignedTo = 'Anyone';
            }
            $note->type = 'attachment';
            $note->associationId = $model->associationId;
            $note->associationType = $model->associationType;
            if($modelName = X2Model::getModelName($model->associationType)){
                $association = X2Model::model($modelName)->findByPk($model->associationId);
                if($association != null){
                    $note->associationName = $association->name;
                }
            }
            $note->actionDescription = $model->fileName.':'.$model->id;
            return $note->save();
        }
        return false;
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionUpload(){
        $model = new PortfolioMedia;

        if(isset($_POST['PortfolioMedia'])){

            $temp = TempFile::model()->findByPk($_POST['TempFileId']);

            $userFolder = Yii::app()->user->name; // place uploaded files in a folder named with the username of the user that uploaded the file
            $userFolderPath = 'uploads/media/'.$userFolder;
            // if user folder doesn't exit, try to create it
            if(!(file_exists($userFolderPath) && is_dir($userFolderPath))){
                if(!@mkdir('uploads/media/'.$userFolder, 0777, true)){ // make dir with edit permission
                    // ERROR: Couldn't create user folder
                    var_dump($userFolder);
                    exit();
                }
            }

            rename($temp->fullpath(), $userFolderPath.'/'.$temp->name);

            // save media info
            $model->fileName = $temp->name;
            $model->createDate = time();
            $model->lastUpdated = time();
            $model->uploadedBy = Yii::app()->user->name;
            $model->associationType = $_POST['PortfolioMedia']['associationType'];
            $model->associationId = $_POST['PortfolioMedia']['associationId'];
            $model->private = $_POST['PortfolioMedia']['private'];
            $model->path; // File type setter is embedded in the magic getter for path
            $model->name = $_POST['PortfolioMedia']['name'];
            if (empty($model->name))
                $model->name = $model->fileName;
            if($_POST['PortfolioMedia']['description'])
                $model->description = $_POST['PortfolioMedia']['description'];

            /*     
            uncomment when media module supports custom forms
            if(isset($_POST['x2ajax'])){
                $ajaxErrors = $this->quickCreate ($model);
                if (!$ajaxErrors) {
                    $this->createAttachmentAction ($model);
                }
            }else{*/
                if($model->save()){
                    $this->createAttachmentAction ($model);
                    $this->redirect(array('view', 'id' => $model->id));
                }
            //}
        }

        /*
        uncomment when media module supports custom forms
        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        } else {*/
            $this->render('upload', array(
                'model' => $model,
            ));
        //}
    }

    public function actionQtip($id){
        $model = PortfolioMedia::model()->findByPk($id);
        $this->renderPartial('qtip', array('model' => $model));
    }

    /**
     * Lists all models.
     */
    public function actionIndex(){
        $model = new PortfolioMedia('search');
        if(isset($_GET['PortfolioMedia'])){
            foreach($_GET['PortfolioMedia'] as $key => $value){
                if($model->hasAttribute($key))
                    $model->$key = $value;
            }
        }
        $this->render('index', array(
            'model' => $model,
        ));
    }

//    public function actionTestDrive(){
//        $admin = Yii::app()->settings;
//        if(isset($_REQUEST['logout'])){
//            unset($_SESSION['access_token']);
//        }
//        require_once('protected/components/GoogleAuthenticator.php');
//        $auth = new GoogleAuthenticator();
//        if($auth->getAccessToken()){
//            $service = $auth->getDriveService();
//        }
//        $createdFile = null;
//        if(isset($service, $_SESSION['access_token'], $_FILES['upload'])){
//            $file = new Google_DriveFile();
//            $file->setTitle($_FILES['upload']['name']);
//            $file->setDescription('Uploaded by X2Engine');
//            $file->setMimeType($_FILES['upload']['type']);
//
//            $data = file_get_contents($_FILES['upload']['tmp_name']);
//            try{
//                $createdFile = $service->files->insert($file, array(
//                    'data' => $data,
//                    'mimeType' => $_FILES['upload']['type'],
//                        ));
//                if(is_array($createdFile)){
//                    $media = new PortfolioMedia;
//                    $media->fileName = $createdFile['id'];
//                    $media->name = $createdFile['title'];
//                    $media->associationType = 'Contacts';
//                    $media->associationId = 955;
//                    $media->uploadedBy = Yii::app()->user->getName();
//                    $media->mimetype = $createdFile['mimeType'];
//                    $media->filesize = $createdFile['fileSize'];
//                    $media->drive = 1;
//                    $media->save();
//                }
//            }catch(Google_AuthException $e){
//                unset($_SESSION['access_token']);
//                $auth->setErrors($e->getMessage());
//                $service = null;
//                $createdFile = null;
//            }
//        }
//
//        $this->render('testDrive', array(
//            'auth' => $auth,
//            'createdFile' => $createdFile,
//            'service' => isset($service) ? $service : null,
//            'baseFolder' => isset($service) ? $this->printFolder('root', $auth) : null
//        ));
//    }

    public function actionRecursiveDriveFiles($folderId){
        $ret = $this->printFolder($folderId);
        echo $ret;
    }

    public function printFolder($folderId, $auth = null){
        if(is_null($auth)){
            $auth = new GoogleAuthenticator();
        }
        $service = $auth->getDriveService();
        try{
            if($service){
                $ret = "";
                $files = $service->files;
                $fileList = $files->listFiles(array('q' => 'trashed=false and "'.$folderId.'" in parents'));
                $folderList = array();
                $fileArray = array();
                foreach($fileList['items'] as $file){
                    if($file['mimeType'] == 'application/vnd.google-apps.folder'){
                        $folderList[] = $file;
                    }else{
                        $fileArray[] = $file;
                    }
                }
                $fileList = array_merge($folderList, $fileArray);
                foreach($fileList as $file){
                    if($file['mimeType'] == 'application/vnd.google-apps.folder'){
                        $ret .= "<div class='drive-wrapper'><div class='drive-item'><div class='drive-icon' style='background:url(\"".$file['iconLink']."\") no-repeat'></div><a href='#' class='toggle-file-system drive-link' data-id='{$file['id']}'> ".$file['title']."</a></div></div>";
                        $ret .= "<div class='drive' id='{$file['id']}' style='display:none;'>";
                        $ret .= "</div>";
                    }else{
                        $ret .= "<div class='drive-wrapper'><div class='drive-item'><div class='drive-icon' style='background:url(\"".$file['iconLink']."\") no-repeat'></div> <a class='x2-link drive-link media' href='".$file['alternateLink']."' target='_blank'>".$file['title']."</a></div></div>";
                    }
                }
                return $ret;
            }else{
                return false;
            }
        }catch(Google_AuthException $e){
            if(isset($_SESSION['access_token']) || isset($_SESSION['token'])){ // If these are set it's possible the token expired and there is a refresh token available
                $auth->flushCredentials(false); // Only flush the recently received information
                return $this->printFolder($folderId); // Try again, it will use a refresh token if available this time, otherwise it will fail.
            }else{
                $auth->flushCredentials();
                $auth->setErrors($e->getMessage());
                return false;
            }
        }catch(Google_ServiceException $e){
            $auth->setErrors($e->getMessage());
            return false;
        }
    }

    public function actionRefreshDriveCache(){
        $auth = new GoogleAuthenticator();
        if($auth->getAccessToken()){
            if(isset($_SESSION['driveFiles'])){
                unset($_SESSION['driveFiles']);
            }
            echo $_SESSION['driveFiles'] = $this->printFolder('root');
        }
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model){
        if(isset($_POST['ajax']) && $_POST['ajax'] === 'media-form'){
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    public function actionToggleUserMediaVisible($user){
        $widgetSettings = json_decode(Yii::app()->params->profile->widgetSettings, true);
        $mediaSettings = $widgetSettings['MediaBox'];
        $hideUsers = $mediaSettings['hideUsers'];
        $ret = '';

        if(($key = array_search($user, $hideUsers)) !== false){ // user is not visible, make them visible
            unset($hideUsers[$key]);
            $hideUsers = array_values($hideUsers); // reindex array so json is consistent
            $ret = 1;
        }else{ // user is visible, make them not visible
            $hideUsers[] = $user;
            $ret = 0;
        }

        $mediaSettings['hideUsers'] = $hideUsers;
        $widgetSettings['MediaBox'] = $mediaSettings;
        Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
        Yii::app()->params->profile->update();

        echo $ret;
    }

    public function actionGetItems(){
        $model = X2Model::model ($this->modelClass);
        if (isset ($model)) {
            $tableName = $model->tableName ();
            $sql = 
                'SELECT media_id, portfolio_id as value
                 FROM '.$tableName.' 
                 WHERE listing_id LIKE :qterm 
                 ORDER BY buyer_id ASC';
            $command = Yii::app()->db->createCommand($sql);
            $qterm = $_GET['term'].'%';
            $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
            $result = $command->queryAll();
            echo CJSON::encode($result);
        }
        Yii::app()->end();
    }

    /**
     * Create a menu for Media
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Media = Modules::displayName();
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'upload', 'view', 'edit', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('media', 'All {media}', array(
                    '{media}' => $Media,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'upload',
                'label'=>Yii::t('media', 'Upload'),
                'url'=>array('upload')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('media', 'Update'),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('media', 'Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('media','Are you sure you want to delete this item?'))
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
