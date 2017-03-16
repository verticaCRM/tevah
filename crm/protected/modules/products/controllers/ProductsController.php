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
 * @package application.modules.products.controllers 
 */
class ProductsController extends x2base {
    public $modelClass = 'Product';

    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'view', 'search','getItems'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','testScalability', 'create', 'update', 'delete'),
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
            ),
        ));
    }

    public function actionGetItems(){
        $sql = 'SELECT id, name as value FROM x2_products WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result); exit;
    }
    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {

        // add product to user's recent item list
        User::addRecentItem('r', $id, Yii::app()->user->getId()); 

        $model = $this->loadModel($id);
        parent::view($model);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new Product;
        $users=User::getNames();
        if(isset($_POST['Product'])) {
            $model->setX2Fields($_POST['Product']);
            // $model->price = Formatter::parseCurrency($model->price,false);
            $model->createDate=time();

            if(isset($_POST['x2ajax'])){
                $ajaxErrors = $this->quickCreate ($model);
            }else{
                if($model->save()) {
                    $this->redirect(array('view', 'id' => $model->id));
                }
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

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users=User::getNames(); 
        $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Product'));
        foreach($fields as $field){
            if($field->type=='link'){
                $fieldName=$field->fieldName;
                $type=ucfirst($field->linkType);
                if(is_numeric($model->$fieldName) && $model->$fieldName!=0){
                    eval("\$lookupModel=$type::model()->findByPk(".$model->$fieldName.");");
                    if(isset($lookupModel))
                        $model->$fieldName=$lookupModel->name;
                }
            }
        }
        if(isset($_POST['Product'])) {
            $temp=$model->attributes;
            $model->setX2Fields($_POST['Product']);
            
            // generate history
            $action = new Actions;
            $action->associationType = 'product';
            $action->associationId = $model->id;
            $action->associationName = $model->name;
            $action->assignedTo = Yii::app()->user->getName();
            $action->completedBy=Yii::app()->user->getName();
            $action->dueDate = time();
            $action->completeDate = time();
            $action->visibility = 1;
            $action->complete='Yes';
        
            $action->actionDescription = "Update: {$model->name}
            Type: {$model->type}
            Price: {$model->price}
            Currency: {$model->currency}
            Inventory: {$model->inventory}";
            $action->save();         
            parent::update($model,$temp,'0');
        }

        $this->render('update',array(
            'model'=>$model,
            'users'=>$users,
        ));
    }
    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if(Yii::app()->request->isPostRequest) {    // we only allow deletion via POST request
            $model = $this->loadModel($id);
            
            $model->clearTags();
            $model->delete();
            
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        } else {
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $model=new Product('search');
        $this->render('index', array('model'=>$model));
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='product-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Create a menu for Products
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Products = Modules::displayName();
        $Product = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete', 'print', 'import', 'export',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('products','{module} List', array(
                    '{module}'=>$Product,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('products','Create'),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('products','Update'),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('products','Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('app','Are you sure you want to delete this item?')
                )
            ),
            array(
                'name' => 'print',
                'label' => Yii::t('app', 'Print Record'),
                'url' => '#',
                'linkOptions' => array (
                    'onClick'=>"window.open('".
                        Yii::app()->createUrl('/site/printRecord', array (
                            'modelClass' => 'Product',
                            'id' => $modelId,
                            'pageTitle' => Yii::t('app', '{module}', array(
                                '{module}' => $Product,
                            )).': '.(isset($model) ? $model->name : "")
			        ))."');"
	            ),
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('products', 'Import {module}', array(
                    '{module}' => $Products,
                )),
                'url'=>array('admin/importModels', 'model'=>'Product'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('products', 'Export {module}',  array(
                    '{module}' => $Products,
                )),
                 'url'=>array('admin/exportModels', 'model'=>'Product'),
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }


}
