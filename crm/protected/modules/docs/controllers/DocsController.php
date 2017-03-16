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
 * @package application.modules.docs.controllers
 */
class DocsController extends x2base {

	public $modelClass = 'Docs';
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	// public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','createEmail','update','exportToHtml','changePermissions', 'delete', 'getItems', 'getItem', 'ajaxCheckEditPermission'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionGetItems(){
		$sql = 'SELECT id, name as value FROM x2_docs WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = '%'.$_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}

	public function actionGetItem($id) {
        $model = $this->loadModel($id);
        if((($model->visibility==1 || ($model->visibility==0 && $model->createdBy==Yii::app()->user->getName())) || Yii::app()->params->isAdmin)){
            echo $model->text;
        }
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$model = CActiveRecord::model('Docs')->findByPk($id);
		if(isset($model)){
			$permissions=explode(", ",$model->editPermissions);
			if(in_array(Yii::app()->user->getName(),$permissions))
				$editFlag=true;
			else
				$editFlag=false;
		}
		//echo $model->visibility;exit;
		if (!isset($model) ||
			   !(($model->visibility==1 ||
				($model->visibility==0 && $model->createdBy==Yii::app()->user->getName())) ||
				Yii::app()->params->isAdmin|| $editFlag))
			$this->redirect(array('/docs/docs/index'));

        // add doc to user's recent item list
        User::addRecentItem('d', $id, Yii::app()->user->getId());
        X2Flow::trigger('RecordViewTrigger',array('model'=>$model));
		$this->render('view', array(
			'model' => $model,
		));
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionFullView($id,$json=0,$replace=0) {
		$model = $this->loadModel($id);
        $response = array(
            'body' => $model->text,
            'subject' => $model->subject,
            'to' => $model->emailTo
        );
        if($replace)
            foreach(array_keys($response) as $key)
                $response[$key] = str_replace('{signature}', Yii::app()->params->profile->signature, $response[$key]);
        if($json){
            header('Content-type: application/json');
            echo json_encode($response);
        }else{
            echo $response['body'];
        }
	}

	/**
	 * Creates a new doc.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($duplicate = false) {
		$users = User::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
		unset($users[Yii::app()->user->getName()]);
		$model = new Docs;

		if($duplicate) {
			$copiedModel = Docs::model()->findByPk($duplicate);
			if(!empty($copiedModel)) {
				foreach($copiedModel->attributes as $name=>$value)
					if($name != 'id')
						$model->$name = $value;
			}
			$model->name .= ' ('.Yii::t('docs','copy').')';
		}

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['Docs'])) {
			$temp = $model->attributes;
			$model->attributes=$_POST['Docs'];
            $model->visibility=$_POST['Docs']['visibility'];

			$arr = $model->editPermissions;
			if(isset($arr))
				if(is_array($arr))
					$model->editPermissions = Fields::parseUsers($arr);

			$model->createdBy = Yii::app()->user->getName();
			$model->createDate = time();
			// $changes=$this->calculateChanges($temp,$model->attributes);
			// $model=$this->updateChangeLog($model,'Create');
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
		));
	}

	/**
	 * Creates an email template.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreateEmail() {
		$users = User::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
		unset($users[Yii::app()->user->getName()]);
		$model = new Docs;
		$model->type = 'email';
		$model->associationType = 'Contacts';

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Docs'])) {
			$temp = $model->attributes;
			$model->attributes = $_POST['Docs'];
            $model->visibility = $_POST['Docs']['visibility'];
			$model->editPermissions = '';
			// $arr=$model->editPermissions;
			// if(isset($arr))
				// $model->editPermissions=Fields::parseUsers($arr);

			$model->createdBy = Yii::app()->user->getName();
			$model->createDate = time();
			// $changes = $this->calculateChanges($temp,$model->attributes);
			// $model = $this->updateChangeLog($model,'Create');
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>null,
		));
	}

	public function actionCreateQuote() {
		$users = User::getNames();
		unset($users['Anyone']);
		unset($users['admin']);
		unset($users[Yii::app()->user->getName()]);
		$model = new Docs;
		$model->type = 'quote';

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Docs'])) {
			$temp = $model->attributes;
			$model->attributes = $_POST['Docs'];
            $model->visibility = $_POST['Docs']['visibility'];
			$model->editPermissions = '';
			// $arr=$model->editPermissions;
			// if(isset($arr))
				// $model->editPermissions=Fields::parseUsers($arr);

			$model->createdBy = Yii::app()->user->getName();
			$model->createDate = time();
			// $changes = $this->calculateChanges($temp,$model->attributes);
			// $model = $this->updateChangeLog($model,'Create');
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
			'users'=>null,
		));
	}

	public function actionChangePermissions($id){
		$model = $this->loadModel($id);
		if(Yii::app()->params->isAdmin || Yii::app()->user->getName()==$model->createdBy) {
			$users = User::getNames();
			unset($users['admin']);
			unset($users['Anyone']);
			$str = $model->editPermissions;
			$pieces = explode(", ",$str);
			$model->editPermissions=$pieces;

			if(isset($_POST['Docs'])) {
				$model->attributes = $_POST['Docs'];
				$arr=$model->editPermissions;

				$model->editPermissions = Fields::parseUsers($arr);
				if($model->save()) {
					$this->redirect(array('view','id'=>$id));
				}
			}

			$this->render('editPermissions',array(
				'model'=>$model,
				'users'=>$users,
			));
		} else {
			$this->redirect(array('view','id'=>$id));
		}
	}

	public function actionExportToHtml($id){
		$model = $this->loadModel($id);
		$file = $this->safePath(($uid = uniqid()).'-doc.html');
		$fp = fopen($file,'w+');
		$data="<style>
				#wrap{
					width:6.5in;
					height:9in;
					margin-top:auto;
					margin-left:auto;
					margin-bottom:auto;
					margin-right:auto;
				}
				</style>
				<div id='wrap'>
			".$model->text."</div>";
		fwrite($fp, $data);
		fclose($fp);
		$link = CHtml::link(Yii::t('app','Download').'!',array('downloadExport','uid'=>$uid,'id'=>$id));
		$this->render('export',array(
			'model'=>$model,
			'link'=>$link,
		));
	}

    /**
     * Download an exported doc file.
     * @param type $uid Unique ID associated with the file
     * @param type $id ID of the doc exported
     */
    public function actionDownloadExport($uid,$id) {
        if(file_exists($this->safePath($filename = $uid.'-doc.html'))) {
            $this->sendFile($filename,false);
        } else {
            $this->redirect(array('exportToHtml','id'=>$id));
        }
    }

    public function titleUpdate($old_title, $new_title) {
        if ((sizeof(Modules::model()->findAllByAttributes(array('name' => $new_title))) == 0) && ($old_title != $new_title)) {
            Yii::app()->db->createCommand()->update('x2_modules',
                    array('title' => $new_title,),
                    'title=:old_title', array(':old_title' => $old_title));
        }
    }

    /**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
        $model = $this->loadModel($id);
       if($model->type == null)
        {
            $model->scenario = 'menu';
        }
        $old_title= $model->name;
        $new_title = $old_title;

        if (isset($_POST['Docs']))
        {
            $new_title = $_POST['Docs']['name'];
        }
        $perm = $model->editPermissions;
        $pieces = explode(', ', $perm);
        if (Yii::app()->user->checkAccess('DocsAdmin') || Yii::app()->user->getName() == $model->createdBy || array_search(Yii::app()->user->getName(), $pieces) !== false || Yii::app()->user->getName() == $perm) {
            if (isset($_POST['Docs'])) {
                $model->attributes = $_POST['Docs'];
                $model->visibility = $_POST['Docs']['visibility'];
                if ($model->save()) {
                    $this->titleUpdate($old_title, $new_title);
                    $event = new Events;
                    $event->associationType = 'Docs';
                    $event->associationId = $model->id;
                    $event->type = 'doc_update';
                    $event->user = Yii::app()->user->getName();
                    $event->visibility = $model->visibility;
                    $event->save();
                    $this->redirect(array('update', 'id' => $model->id, 'saved' => true, 'time' => time()));
                }
            }

			$this->render('update',array(
				'model'=>$model,
			));
		} else {
			$this->redirect(array('view','id'=>$id));
		}
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$model = $this->loadModel($id);
			$this->cleanUpTags($model);
			$model->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		} else throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model = new Docs('search');

		$attachments=new CActiveDataProvider('Media',array(
			'criteria'=>array(
			'order'=>'createDate DESC',
			'condition'=>'associationType="docs"'
		)));

		$this->render('index',array(
			'model'=>$model,
			'attachments'=>$attachments,
		));
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='docs-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionAutosave($id) {
		$model = $this->loadModel($id);

        $old_title= $model->name;
        $new_title = $old_title;
       if (isset($_POST['Docs']))
        {
            $new_title = $_POST['Docs']['name'];
        }

		if(isset($_POST['Docs'])) {
			$model->attributes = $_POST['Docs'];
			// $model = $this->updateChangeLog($model,'Edited');

            if($model->save()) {
                   if ($old_title != $new_title) {
                      $this->titleUpdate($old_title, $new_title);
                 }
               echo Yii::t('docs', 'Saved at') . ' ' . Yii::app()->dateFormatter->format(Yii::app()->locale->getTimeFormat('medium'), time());
			};
		}
    }

    /**
     * Echoes 'true' if User has permission, 'false' otherwise
     * @param int id id of doc model  
     */
    public function actionAjaxCheckEditPermission ($id) {
        if (!isset ($id)) {
            echo 'failure';
            return;
        }
        $doc = Docs::model ()->findByPk ($id);
        if (isset ($doc)) {
            $canEdit = $doc->checkEditPermission () ? 'true' : 'false';
        } else {
            $canEdit = 'false';
        }
        echo $canEdit;
        return;
    }

    public function behaviors() {
        return array_merge(parent::behaviors(),array(
            'ImportExportBehavior' => array('class' => 'ImportExportBehavior'),
        ));
    }

    /**
     * Create a menu for Docs
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Docs = Modules::displayName();
        $Doc = Modules::displayName(false);
        $user = Yii::app()->user->name;
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'createEmail', 'createQuote', 'view', 'edit', 'delete',
         *     'permissions', 'exportToHtml', 'import', 'export',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('docs','List {module}', array(
                    '{module}'=>$Docs,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('docs','Create {module}', array(
                    '{module}' => $Doc,
                )),
                'url'=>array('create')
            ),
            array(
                'name'=>'createEmail',
                'label'=>Yii::t('docs','Create Email'),
                'url'=>array('createEmail')
            ),
            array(
                'name'=>'createQuote',
                'label'=>Yii::t('docs','Create {quote}', array(
                    '{quote}' => Modules::displayName(false, "Quotes"),
                )),
                'url'=>array('createQuote')
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('docs','View'),
                'url'=>array('view','id'=>$modelId)
            ),
            array(
                'name'=>'edit',
                'label' => Yii::t('docs', 'Edit {doc}', array(
                    '{doc}' => $Doc,
                )),
                'url' => array('update', 'id' => $modelId)
            ),
            array(
                'name'=>'delete',
                'label' => Yii::t('docs', 'Delete {doc}', array(
                    '{doc}' => $Doc,
                )),
                'url' => 'javascript:void(0);',
                'linkOptions' => array(
                    'submit' => array('delete', 'id' => $modelId),
                    'confirm' => Yii::t('docs', 'Are you sure you want to delete this item?')
                ),
            ),
            array(
                'name'=>'permissions',
                'label' => Yii::t('docs', 'Edit {doc} Permissions', array(
                    '{doc}' => $Doc,
                )),
                'url' => array('changePermissions', 'id' => $modelId),
                'visible' => isset($model) && (Yii::app()->params->isAdmin ||
                            $user == $model->createdBy ||
                            array_search($user, explode(", ",$model->editPermissions)) ||
                            $user == $model->editPermissions)
            ),
            array(
                'name'=>'exportToHtml',
                'label' => Yii::t('docs', 'Export {doc}', array(
                    '{doc}' => $Doc,
                )),
                'url' => array('exportToHtml', 'id' => $modelId)
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('docs', 'Import {module}', array(
                    '{module}' => $Docs,
                )),
                'url'=>array('admin/importModels', 'model'=>'Docs'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('docs', 'Export {module}', array(
                    '{module}' => $Docs,
                )),
                'url'=>array('admin/exportModels', 'model'=>'Docs'),
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
