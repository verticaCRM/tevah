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
 *
 * Base controller for all application controllers with CRUD operations
 *
 * @package application.controllers
 */
abstract class x2base extends X2Controller {
    /*
     * Class design:
     * Basic create method (mostly overridden, but should have basic functionality to avoid using Gii
     * Index method: Ability to pass a data provider to filter properly
     * Delete method -> unviersal.
     * Basic user permissions (access rules)
     * Update method -> Similar to create
     * View method -> Similar to index
     */

    /**
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/column3';

    // If true, then the content will not have a backdrop
    public $noBackdrop = false;
    
    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();
    public $portlets = array(); // This is the array of widgets on the sidebar.
    public $leftPortlets = array(); // additional menu blocks on the left mneu
    public $modelClass;
    public $actionMenu = array();
    public $leftWidgets = array();


    private $_pageTitle;

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            array(
                'application.components.filters.X2AjaxHandlerFilter',
            ),
            array(
                'application.components.filters.FileUploadsFilter'
            ),
            'setPortlets', // performs widget ordering and show/hide on each page
        );
    }

    public function behaviors() {
        return array(
            'CommonControllerBehavior' => array(
                'class' => 'application.components.CommonControllerBehavior'),
            'PermissionsBehavior' => array(
                'class' => 'application.components.permissions.X2ControllerPermissionsBehavior'),
        );
    }

    protected function beforeAction($action = null) {
        return $this->PermissionsBehavior->beforeAction($action);
    }

    public function appLockout() {
        header("HTTP/1.1 503 Unavailable");
        header("Content-type: text/plain; charset=utf-8");
        echo Yii::t('app','X2Engine is undergoing maintenance; it has been locked by an administrator. Please try again later.');
        Yii::app()->end();
    }

    public function denied() {
        throw new CHttpException(
            403, Yii::t('app','You are not authorized to perform this action.'));
    }

    /**
     * @param string $status 'success'|'failure'|'error'|'warning' 
     * @param string $message 
     */
    public function ajaxResponse ($status, $message=null) {
        $response = array ();
        $response['status'] = $status;
        if ($message !== null) $response['message'] = $message;
        return CJSON::encode ($response);
    }

    public function actions() {
        $actions = array(
            'x2GridViewMassAction' => array(
                'class' => 'X2GridViewMassActionAction',
            ),
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
        );
        if ($this->module) {
            $module = Modules::model ()->findByAttributes (array ('name' => $this->module->name));
            if ($module->enableRecordAliasing) {
                $actions = array_merge ($actions, RecordAliases::getActions ());
            }
        }
        if ($this->modelClass !== '') {
            $modelClass = $this->modelClass;
            if ($modelClass::model ()->asa ('X2ModelConversionBehavior')) {
                $actions = array_merge ($actions, X2ModelConversionBehavior::getActions ());
            }
        }
        return $actions;
    }

    /**
     * Returns rendered detail view for given model 
     * @param object $model
     */
    public function getDetailView ($model) {
        if (!is_subclass_of ($model, 'X2Model'))
            throw new CException (Yii::t ('app', '$model is not a subclass of X2Model'));

        return $this->renderPartial(
            'application.components.views._detailView', 
            array('model' => $model, 'modelName' => get_class ($model)), true, true); 
    }

    /**
     * Renders a view with any attached scripts, WITHOUT the core scripts.
     *
     * This method fixes the problem with {@link renderPartial()} where an AJAX request with
     * $processOutput=true includes the core scripts, breaking everything on the page
     * in rendering a partial view, or an AJAX response.
     *
     * @param string $view name of the view to be rendered. See {@link getViewFile} for details
     * about how the view script is resolved.
     * @param array $data data to be extracted into PHP variables and made available to the view 
     *  script
     * @param boolean $return whether the rendering result should be returned instead of being 
     *  displayed to end users
     * @return string the rendering result. Null if the rendering result is not required.
     * @throws CException if the view does not exist
     */
    public function renderPartialAjax(
        $view, $data = null, $return = false, $includeScriptFiles = false) {

        if (($viewFile = $this->getViewFile($view)) !== false) {

            // if(class_exists('ReflectionClass')) {
            // $counter = abs(crc32($this->route));
            // $reflection = new ReflectionClass('CWidget');
            // $property = $reflection->getProperty('_counter');
            // $property->setAccessible(true);
            // $property->setValue($counter);
            // }

            $output = $this->renderFile($viewFile, $data, true);

            $cs = Yii::app()->clientScript;
            Yii::app()->setComponent('clientScript', new X2ClientScript);
            $output = $this->renderPartial($view, $data, true);
            $output .= Yii::app()->clientScript->renderOnRequest($includeScriptFiles);
            Yii::app()->setComponent('clientScript', $cs);

            if ($return)
                return $output;
            else
                echo $output;
        } else {
            throw new CException(
                Yii::t('yii', '{controller} cannot find the requested view "{view}".', 
                    array('{controller}' => get_class($this), '{view}' => $view)));
        }
    }

    /**
     * Determines if we have permission to edit something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @return boolean
     * @deprecated
     */
//    public function editPermissions(&$model) {
//        if (Yii::app()->params->isAdmin || !$model->hasAttribute('assignedTo'))
//            return true;
//        else
//            return $model->assignedTo == Yii::app()->user->getName() || in_array($model->assignedTo, Yii::app()->params->groups);
//    }

    /**
     * Determines if we have permission to edit something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param string $action
     * @return boolean
     */
    public function checkPermissions(&$model, $action = null) {
        return $this->PermissionsBehavior->checkPermissions($model, $action);
    }

    /**
     * Displays a particular model.
     *
     * This method is called in child controllers
     * which pass it a model to display and what type of model it is (i.e. Contact,
     * Opportunity, Account).  It also creates an action history and provides appropriate
     * variables to the view.
     *
     * @param mixed $model The model to be displayed (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param String $type The type of the module being displayed
     */
    public function view(&$model,$type=null,$params=array()) {
        $this->noBackdrop = true;

        // should only happen when the model is known to have X2LinkableBehavior
        if($type === null)    // && $model->asa('X2LinkableBehavior') !== null)    
            $type = $model->module;

        if(!isset($_GET['ajax'])){
            $log=new ViewLog;
            $log->user=Yii::app()->user->getName();
            $log->recordType=get_class($model);
            $log->recordId=$model->id;
            $log->timestamp=time();
            $log->save();
            X2Flow::trigger('RecordViewTrigger',array('model'=>$model));
        }

        $this->render('view', array_merge($params,array(
            'model' => $model,
            'actionHistory' => $this->getHistory($model,$type),
            'currentWorkflow' => $this->getCurrentWorkflow($model->id,$type),
        )));
    }

    /**
     * Obtain the history of actions associated with a model.
     *
     * Returns the data provider that references the history.
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param mixed $type The association type (type of the model)
     * @return CActiveDataProvider
     */
    public function getHistory(&$model, $type = null) {

        if (!isset($type))
            $type = get_class($model);

        $filters = array(
            'actions'=>' AND type IS NULL',
            'comments'=>' AND type="note"',
            'attachments'=>' AND type="attachment"',
            'all'=>''
        );

        $history = 'all';
        if(isset($_GET['history']) && array_key_exists($_GET['history'],$filters))
            $history = $_GET['history'];

        return new CActiveDataProvider('Actions',array(
            'criteria'=>array(
                'order'=>'GREATEST(createDate, IFNULL(completeDate,0), IFNULL(dueDate,0), IFNULL(lastUpdated,0)) DESC',
                'condition'=>'associationId='.$model->id.' AND associationType="'.$type.'" '.$filters[$history].' AND (visibility="1" OR assignedTo="admin" OR assignedTo="'.Yii::app()->user->getName().'")'
            )
        ));
    }

    /**
     * Obtains the current worflow for a model of given type and id.
     * Prioritizes incomplete workflows over completed ones.
     * @param integer $id the ID of the record
     * @param string $type the associationType of the record
     * @return int the ID of the current workflow (0 if none are found)
     */
    public function getCurrentWorkflow($id, $type) {
        $currentWorkflow = Yii::app()->db->createCommand()
            ->select('workflowId,completeDate,createDate')
            ->from('x2_actions')
            ->where(
                'type="workflow" AND associationType=:type AND associationId=:id',
                array(':type'=>$type,':id'=>$id))
            ->order('IF(completeDate = 0 OR completeDate IS NULL,1,0) DESC, createDate DESC')
            ->limit(1)
            ->queryRow(false);

        if($currentWorkflow === false || !isset($currentWorkflow[0])) {

            $defaultWorkflow = Yii::app()->db->createCommand()
                ->select('id')
                ->from('x2_workflows')
                ->where('isDefault=1')
                ->limit(1)
                ->queryScalar();
            if($defaultWorkflow !== false)
                return $defaultWorkflow;
            return 0;
        }
        return $currentWorkflow[0];
    }

    /**
     * Used in function convertUrls
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    private static function compareChunks($a, $b) {
        return $a[1] - $b[1];
    }

    /**
     * Replaces any URL in text with an html link (supports mailto links)
     *
     * @todo refactor this out of controllers
     * @param string $text Text to be converted
     * @param boolean $convertLineBreaks
     */
    public static function convertUrls($text, $convertLineBreaks = true) {
        /* $text = preg_replace(
          array(
          '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+))/iex',
          '/<a([^>]*)target="?[^"\']+"?/i',
          '/<a([^>]+)>/i',
          '/(^|\s|>)(www.[^<> \n\r]+)/iex',
          '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/iex'
          ),
          array(
          "stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\">\\2</a>\\3':'\\0'))",
          '<a\\1',
          '<a\\1 target="_blank">',
          "stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\">\\2</a>\\3':'\\0'))",
          "stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
          ),
          $text
          ); */

        /* URL matching regex from the interwebs:
         * http://www.regexguru.com/2008/11/detecting-urls-in-a-block-of-text/
         */
        $url_pattern = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i';
        $email_pattern = '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/i';

        /* First break the text into two arrays, one containing <a> tags and the like
         * which should not have any replacements, and another with all the text that
         * should have URLs activated.  Each piece of each array has its offset from
         * original string so we can piece it back together later
         */

        //add any additional tags to be passed over here
        $tags_with_urls = "/(<a[^>]*>.*<\/a>)|(<img[^>]*>)|(<iframe[^>]*>.*<\/iframe>)|(<script[^>]*>.*<\/script>)/i";
        $text_to_add_links = preg_split($tags_with_urls, $text, NULL, PREG_SPLIT_OFFSET_CAPTURE);
        $matches = array();
        preg_match_all($tags_with_urls, $text, $matches, PREG_OFFSET_CAPTURE);
        $text_to_leave = $matches[0];

        // Convert all URLs into html links
        foreach ($text_to_add_links as $i => $value) {
            $text_to_add_links[$i][0] = preg_replace(
                    array($url_pattern,
                $email_pattern), array("<a href=\"\\0\">\\0</a>",
                "<a href=\"mailto:\\0\">\\0</a>"), $text_to_add_links[$i][0]
            );
        }

        // Merge the arrays and sort to be in the original order
        $all_text_chunks = array_merge($text_to_add_links, $text_to_leave);

        usort($all_text_chunks, 'x2base::compareChunks');

        $new_text = "";
        foreach ($all_text_chunks as $chunk) {
            $new_text = $new_text . $chunk[0];
        }
        $text = $new_text;

        // Make sure all links open in new window, and have http:// if missing
        $text = preg_replace(
                array('/<a([^>]+)target=("[^"]+"|\'[^\']\'|[^\s]+)([^>]+)/i',
            '/<a([^>]+href="?\'?)(www\.|ftp\.)/i'), array('<a\\1 target=\\2\\3',
            '<a\\1http://\\2'), $text
        );

        //convert any tags into links
        $matches = array();
        // avoid matches that end with </span></a>, like other record links
        preg_match('/(^|[>\s\.])(#\w\w+)(?!.*<\/span><\/a>)/u', $text, $matches);
        $tags = Yii::app()->cache->get('x2_taglinks');
        if ($tags === false) {
            $dependency = new CDbCacheDependency('SELECT MAX(timestamp) FROM x2_tags');
            $tags = Yii::app()->db->createCommand()
                    ->selectDistinct('tag')
                    ->from('x2_tags')
                    ->queryColumn();
            // cache either 10min or until a new tag is added
            Yii::app()->cache->set('x2_taglinks', $tags, 600, $dependency);
        }
        if (sizeof ($matches) > 1 && $matches[2] !== null && 
            array_search($matches[2], $tags) !== false) {

            $template = "\\1<a href=" . Yii::app()->createUrl('/search/search') . 
                '?term=%23\\2' . ">#\\2</a>";
            //$text = preg_replace('/(^|[>\s\.])#(\w\w+)($|[<\s\.])/u',$template,$text);
            $text = preg_replace('/(^|[>\s\.])#(\w\w+)/u', $template, $text);
        }

        //TODO: separate convertUrl and convertLineBreak concerns
        if ($convertLineBreaks)
            return Formatter::convertLineBreaks($text, true, false);
        else
            return $text;
    }

    // Deletes a note action
    public function actionDeleteNote($id) {
        $note = X2Model::model('Actions')->findByPk($id);
        if ($note->delete()) {
            $this->redirect(array('view', 'id' => $note->associationId));
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create($model, $oldAttributes, $api) {
        // $name = get_class($model);
        // $model->createDate = time();
        // if($model->hasAttribute('lastUpdated'))
            // $model->lastUpdated=time();
        // if($model->hasAttribute('lastActivity'))
            // $model->lastActivity = time();

        // if ($model->save()) {

            // relationships (now in X2Model::afterSave())
            /* if (!($model instanceof Actions)) {
                $fields = Fields::model()->findAllByAttributes(array('modelName' => $name, 'type' => 'link'));
                foreach ($fields as $field) {
                    $fieldName = $field->fieldName;
                    if (isset($model->$fieldName) && is_numeric($model->$fieldName)) {
                        if (is_null(Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
                            (firstType='$name' AND firstId='$model->id' AND secondType='" . ucfirst($field->linkType) . "' AND secondId='" . $model->$fieldName . "')
                            OR (secondType='$name' AND secondId='$model->id' AND firstType='" . ucfirst($field->linkType) . "' AND firstId='" . $model->$fieldName . "')"))) {
                            $rel = new Relationships;
                            $rel->firstType = $name;
                            $rel->secondType = ucfirst($field->linkType);
                            $rel->firstId = $model->id;
                            $rel->secondId = $model->$fieldName;
                            if ($rel->save()) {
                                $lookup = Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
                                    (firstType='$name' AND firstId='$model->id' AND secondType='" . ucfirst($field->linkType) . "' AND secondId='" . $oldAttributes[$fieldName] . "')
                                    OR (secondType='$name' AND secondId='$model->id' AND firstType='" . ucfirst($field->linkType) . "' AND firstId='" . $oldAttributes[$fieldName] . "')");
                                if (isset($lookup))
                                    $lookup->delete();
                            }
                        }
                    }
                }
            } */
            // $changes = $this->calculateChanges($oldAttributes, $model->attributes, $model);
            // $this->updateChangelog($model, $changes);


            // create event, and notification if record was reassigned - now in X2ChangeLogBehavior::afterSave()
            /* $event=new Events;
            if($model->hasAttribute('visibility')){
                $event->visibility=$model->visibility;
            }
            $event->associationType=$name;
            $event->associationId=$model->id;
            $event->user=Yii::app()->user->getName();
            $event->type='record_create';
            if(!$model instanceof Contacts || $api==0){ // Event creation already handled by web lead.
                $event->save();
            }
            if ($model->hasAttribute('assignedTo')) {
                if (!empty($model->assignedTo) && $model->assignedTo != Yii::app()->user->getName() && $model->assignedTo != 'Anyone') {

                    $notif = new Notification;
                    $notif->user = $model->assignedTo;
                    $notif->createdBy = ($api == 0) ? 'API' : Yii::app()->user->getName();
                    $notif->createDate = time();
                    $notif->type = 'create';
                    $notif->modelType = $name;
                    $notif->modelId = $model->id;
                    $notif->save();
                }
            } */
            // if ($model instanceof Actions) {
                // create reminder - now in Actions::afterCreate()
                /* if(empty($model->type)){
                    $event=new Events;
                    $event->timestamp=$model->dueDate;
                    $event->visibility=$model->visibility;
                    $event->type='action_reminder';
                    $event->associationType="Actions";
                    $event->associationId=$model->id;
                    $event->user=$model->assignedTo;
                    $event->save();
                } */
                // if($api==0){
                    // now in ActionsController::actionCreate
                    /* if (isset($_GET['inline']) || $model->type == 'note')
                        if ($model->associationType == 'product' || $model->associationType == 'products')
                            $this->redirect(array('/products/products/view', 'id' => $model->associationId));
                        //TODO: avoid such hackery
                        else if ($model->associationType == 'Campaign')
                            $this->redirect(array('/marketing/marketing/view', 'id' => $model->associationId));
                        else
                            $this->redirect(array('/' . $model->associationType . '/' . $model->associationType . '/view', 'id' => $model->associationId));
                    else
                        $this->redirect(array('view', 'id' => $model->id)); */
                // }
            // } else if ($api == 0) {
        if($model->save()) {
            if($api == 0)
                $this->redirect(array('view', 'id' => $model->id));
            else
                return true;
        } else {
            return false;
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($model, $oldAttributes, $api) {
        // $name = $this->modelClass;
        // if($model->hasAttribute('lastActivity'))
            // $model->lastActivity = time();

        // $temp = $oldAttributes;
        // $changes = $this->calculateChanges($temp, $model->attributes, $model);
        // $model = $this->updateChangelog($model, $changes);
/*        if($model->save()) {
            if( $model instanceof Contacts) {
            // now in Contacts::afterUpdate()
                // send subscribe emails if anyone has subscribed to this contact
                $result = Yii::app()->db->createCommand()
                        ->select()
                        ->from('x2_subscribe_contacts')
                        ->where("contact_id={$model->id}")
                        ->queryAll();

                $datetime = Formatter::formatLongDateTime(time());
                $modelLink = CHtml::link($model->name, $this->createAbsoluteUrl('/contacts/' . $model->id));
                $subject = "X2Engine: {$model->name} updated";
                $message = "Hello,<br>\n<br>\n";
                $message .= "You are receiving this email because you are subscribed to changes made to the contact $modelLink in X2Engine. ";
                $message .= "The following changes were made on $datetime:<br>\n<br>\n";

                foreach($changes as $attribute=>$change) {
                    if($attribute != 'lastActivity') {
                        $old = $change['old'] == ''? '-----' : $change['old'];
                        $new = $change['new'] == ''? '-----' : $change['new'];
                        $label = $model->getAttributeLabel($attribute);
                        $message .= "$label: $old => $new<br>\n";
                    }
                }

                $message .="<br>\nYou can unsubscribe to these messages by going to $modelLink and clicking Unsubscribe.<br>\n<br>\n";

                $adminProfile = Profile::model()->findByPk(1);
                foreach($result as $subscription) {
                    $profile = Profile::model()->findByPk($subscription['user_id']);
                    if($profile && $profile->emailAddress && $adminProfile && $adminProfile->emailAddress) {
                        $to = array($profile->fullName, $profile->emailAddress);
                        $from = array('name'=> $adminProfile->fullName, 'address'=>$adminProfile->emailAddress);
                        $this->sendUserEmail($to, $subject, $message, null, $from);
                    }
                }

            }*/
            // relationships, now in X2Model::afterSave()
            /* if (!($model instanceof Actions)) {

                $fields = Fields::model()->findAllByAttributes(array('modelName' => $name, 'type' => 'link'));
                foreach ($fields as $field) {
                    $fieldName = $field->fieldName;
                    if (isset($model->$fieldName) && $model->$fieldName != "") {
                        if (is_null(Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
                                (firstType=:name AND firstId=:id AND secondType=:linktype AND secondId=:fieldname)
                                OR (secondType=:name AND secondId=:id AND firstType=:linktype AND firstId=:fieldname)",array(':name'=>$name,':id'=>$model->id,':linktype'=>ucfirst($field->linkType),':fieldname'=>$model->$fieldName)))) {

                            $rel = new Relationships;
                            $rel->firstType = $name;
                            $rel->secondType = ucfirst($field->linkType);
                            $rel->firstId = $model->id;
                            $rel->secondId = $model->$fieldName;
                            if ($rel->save()) {
                                if ($field->linkType != 'contacts' && $field->linkType != 'Contacts') {
                                    if (is_numeric($oldAttributes[$fieldName]))
                                        $oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
                                    else
                                        $oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $oldAttributes[$fieldName]));
                                }
                                else {
                                    $pieces = explode(" ", $oldAttributes[$fieldName]);
                                    if (count($pieces) > 1) {
                                        if (is_numeric($oldAttributes[$fieldName]))
                                            $oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
                                        else
                                            $oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('firstName' => $pieces[0], 'lastName' => $pieces[1]));
                                    }
                                }
                                if (isset($oldRel)) {
                                    $lookup = Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
                                    (firstType=:name AND firstId=:id AND secondType=:linktype AND secondId=:oldid)
                                    OR (secondType=:name AND secondId=:id AND firstType=:linktype AND firstId=:oldid)",array(':name'=>$name,':id'=>$model->id,':linktype'=>ucfirst($field->linkType),':oldid'=>$oldRel->id));
                                    if (isset($lookup)) {
                                        $lookup->delete();
                                    }
                                }
                            }
                        }
                    } elseif ($model->$fieldName == "") {
                        if ($field->linkType != 'contacts' && $field->linkType != 'Contacts') {
                            if (is_numeric($oldAttributes[$fieldName]))
                                $oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
                            else
                                $oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $oldAttributes[$fieldName]));
                        }else {
                            $pieces = explode(" ", $oldAttributes[$fieldName]);
                            if (count($pieces) > 1) {
                                if (is_numeric($oldAttributes[$fieldName]))
                                    $oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
                                else
                                    $oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('firstName' => $pieces[0], 'lastName' => $pieces[1]));
                            }
                        }
                        if (isset($oldRel)) {
                            $lookup = Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
                                    (firstType=:name AND firstId=:id AND secondType=:linktype AND secondId=:oldid)
                                    OR (secondType=:name AND secondId=:id AND firstType=:linktype AND firstId=:oldid)",array(':name'=>$name,':id'=>$model->id,':linktype'=>ucfirst($field->linkType),':oldid'=>$oldRel->id));
                            if (isset($lookup)) {
                                $lookup->delete();
                            }
                        }
                    }
                }
            } */
            /* if ($model instanceof Actions && $api == 0) {
                if (isset($_GET['redirect']) && $model->associationType != 'none') { // if the action has an association
                    if ($model->associationType == 'product' || $model->associationType == 'products')
                        $this->redirect(array('/products/products/view', 'id' => $model->associationId));
                    //TODO: avoid such hackery
                    else if ($model->associationType == 'Campaign')
                        $this->redirect(array('/marketing/marketing/view', 'id' => $model->associationId));
                    else
                        $this->redirect(array('/' . $model->associationType . '/' . $model->associationType . '/view', 'id' => $model->associationId)); // go back to the association
                } else // no association
                    $this->redirect(array('/actions/' . $model->id)); // view the action
            } else if ($api == 0) { */

        if($model->save()) {
            if($api == 0)
                $this->redirect(array('view', 'id' => $model->id));
            else
                return true;
        } else {
            return false;
        }
    }

    /**
     * Lists all models.
     */
    public function index($model, $name) {
        $this->render('index', array('model' => $model));
    }

    /**
     * Manages all models.
     * @param $model The model to use admin on, created in a controller subclass.  The model must be constucted with the parameter 'search'
     * @param $name The name of the model being viewed (Opportunities, Actions, etc.)
     */
    public function admin($model, $name) {
        $this->render('admin', array('model' => $model));
    }

    public function createX2Grid($options=array()){
        if(empty($options)){
            $options=array(
                'id'=>'',
                'title'=>'',
                'buttons'=>array(),
                'template'=>'<div class="page-title">{title}{buttons}{filterHint}{summary}</div>{items}{pager}',
                'dataProvder'=>null,
                'filter'=>null,
                'modelName'=>$this->modelClass,
                'viewName'=>'',
                'defaultGvSettings'=>array(
                    'gvCheckbox' => 30,
                    'name' => 125,
                    'createDate' => 78,
                    'gvControls' => 73,
                ),
                'specialColumns'=>array(),
                'enableControls'=>true,
                'fullscreen'=>true,
            );
        }
        $this->widget('X2GridView', $options);
    }

    /**
     * Search for a term.  Defined in X2Base so that all Controllers can use, but
     * it makes a call to the SearchController.
     */
    public function actionSearch() {
        $term = $_GET['term'];
        $this->redirect(Yii::app()->controller->createAbsoluteUrl('/search/search',array('term'=>$term)));
    }

    /**
     * DUMMY METHOD: left to avoid breaking old custom modules (now done in X2ChangeLogBehavior)
     */
    protected function updateChangelog($model, $changes) {
        return $model;
    }

    /**
     * DUMMY METHOD: left to avoid breaking old custom modules (now done in X2ChangeLogBehavior)
     */
    protected function calculateChanges($old, $new, &$model = null) {
        return array();
    }

    /**
     * Sets the lastUpdated and updatedBy fields to reflect recent changes.
     * @param type $model The model to be updated
     * @return type $model The model with modified attributes
     */
/*     protected function updateChangelog($model, $changes) {
        $model->lastUpdated = time();
        $model->updatedBy = Yii::app()->user->getName();
        $model->save();
        $type = get_class($model);
        if(is_array($changes)){
            foreach($changes as $field=>$array){
                $changelog = new Changelog;
                $changelog->type = $type;
                if (!isset($model->id)) {
                    if ($model->save()) {

                    }
                }
                $changelog->itemId = $model->id;
                if($model->hasAttribute('name')){
                    $changelog->recordName=$model->name;
                }else{
                    $changelog->recordName=$type;
                }
                $changelog->changedBy = Yii::app()->user->getName();
                $changelog->fieldName = $field;
                $changelog->oldValue=$array['old'];
                $changelog->newValue=$array['new'];
                $changelog->timestamp = time();

                if ($changelog->save()) {

                }
            }
        }

        if ($changes != 'Create' && $changes != 'Completed' && $changes != 'Edited') {
            if ($changes != "" && !is_array($changes)) {
                $pieces = explode("<br />", $change);
                foreach ($pieces as $piece) {
                    $newPieces = explode("TO:", $piece);
                    $forDeletion = $newPieces[0];
                    if (isset($newPieces[1]) && preg_match('/<b>' . Yii::t('actions', 'color') . '<\/b>/', $piece) == false) {
                        $changes[] = $newPieces[1];
                    }

                    preg_match_all('/(^|\s|)#(\w\w+)/', $forDeletion, $deleteMatches);
                    $deleteMatches = $deleteMatches[0];
                    foreach ($deleteMatches as $match) {
                        $oldTag = Tags::model()->findByAttributes(array('tag' => substr($match, 1), 'type' => $type, 'itemId' => $model->id));
                        if (isset($oldTag))
                            $oldTag->delete();
                    }
                }
            }
        }else if ($changes == 'Create' || $changes == 'Edited') {
            if ($model instanceof Contacts)
                $change = $model->backgroundInfo;
            else if ($model instanceof Actions)
                $change = $model->actionDescription;
            else if ($model instanceof Docs)
                $change = $model->text;
            else
                $change = $model->name;
        }
        if(is_array($changes)){
            foreach ($changes as $field=>$array) {
                if(is_string($array['new'])){
                    preg_match_all('/(^|\s|)#(\w\w+)/', $array['new'], $matches);
                    $matches = $matches[0];
                }else{
                    $matches=array();
                }
                foreach ($matches as $match) {
                    if(!preg_match('/\&(^|\s|)#(\w\w+);/',$match)){
                        $tag = new Tags;
                        $tag->type = $type;
                        $tag->taggedBy = Yii::app()->user->getName();
                        $tag->type = $type;
                        //cut out leading whitespace
                        $tag->tag = trim($match);
                        if ($model instanceof Contacts)
                            $tag->itemName = $model->firstName . " " . $model->lastName;
                        else if ($model instanceof Actions)
                            $tag->itemName = $model->actionDescription;
                        else if ($model instanceof Docs)
                            $tag->itemName = $model->title;
                        else
                            $tag->itemName = $model->name;
                        if (!isset($model->id)) {
                            $model->save();
                        }
                        $tag->itemId = $model->id;
                        $tag->timestamp = time();
                        //save tags including # sign
                        if ($tag->save()) {

                        }
                    }
                }
            }
        }
        return $model;
    }

    /**
     * Delete all tags associated with a model
     */
    public function cleanUpTags($model) {
        Tags::model()->deleteAllByAttributes(array('itemId' => $model->id));
    }

    /* protected function calculateChanges($old, $new, &$model = null) {
        $arr = array();
        $keys = array_keys($new);
        for ($i = 0; $i < count($keys); $i++) {
            if ($old[$keys[$i]] != $new[$keys[$i]]) {
                $arr[$keys[$i]] = $new[$keys[$i]];
                $allCriteria = Criteria::model()->findAllByAttributes(array('modelType' => $this->modelClass, 'modelField' => $keys[$i]));
                foreach ($allCriteria as $criteria) {
                    if (($criteria->comparisonOperator == "=" && $new[$keys[$i]] == $criteria->modelValue)
                            || ($criteria->comparisonOperator == ">" && $new[$keys[$i]] >= $criteria->modelValue)
                            || ($criteria->comparisonOperator == "<" && $new[$keys[$i]] <= $criteria->modelValue)
                            || ($criteria->comparisonOperator == "change" && $new[$keys[$i]] != $old[$keys[$i]])) {

                        $users = explode(", ", $criteria->users);

                        if ($criteria->type == 'notification') {
                            foreach ($users as $user) {
                                $event=new Events;
                                $event->user=$user;
                                $event->associationType='Notifications';
                                $event->type='notif';

                                $notif = new Notification;
                                $notif->type = 'change';
                                $notif->fieldName = $keys[$i];
                                $notif->modelType = get_class($model);
                                $notif->modelId = $model->id;

                                if ($criteria->comparisonOperator == 'change') {
                                    $notif->comparison = 'change';    // if the criteria is just 'changed'
                                    $notif->value = $new[$keys[$i]];   // record the new value
                                } else {
                                    $notif->comparison = $criteria->comparisonOperator;  // otherwise record the operator type
                                    $notif->value = substr($criteria->modelValue, 0, 250); // and the comparison value
                                }
                                $notif->user = $user;
                                $notif->createdBy = Yii::app()->user->name;
                                $notif->createDate = time();

                                if($notif->save()){
                                    $event->associationId=$notif->id;
                                    $event->save();
                                }
                            }
                        } elseif ($criteria->type == 'action') {
                            $users = explode(", ", $criteria->users);
                            foreach ($users as $user) {
                                $action = new Actions;
                                $action->assignedTo = $user;
                                if ($criteria->comparisonOperator == "=") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
                                } else if ($criteria->comparisonOperator == ">") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
                                } else if ($criteria->comparisonOperator == "<") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
                                } else if ($criteria->comparisonOperator == "change") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has had its $criteria->modelField field changed from " . $old[$keys[$i]] . " to " . $new[$keys[$i]] . " by " . Yii::app()->user->getName();
                                }
                                $action->dueDate = mktime('23', '59', '59');
                                $action->createDate = time();
                                $action->lastUpdated = time();
                                $action->updatedBy = 'admin';
                                $action->visibility = 1;
                                $action->associationType = strtolower($this->modelClass);
                                $action->associationId = $new['id'];

                                $action->associationName = $model->name;
                                $action->save();
                            }
                        } elseif ($criteria->type == 'assignment') {
                            $model->assignedTo = $criteria->users;

                            if ($model->save()) {
                                $event=new Events;
                                $event->type='notif';
                                $event->user=$model->assignedTo;
                                $event->associationType='Notifications';

                                $notif = new Notification;
                                $notif->user = $model->assignedTo;
                                $notif->createDate = time();
                                $notif->type = 'assignment';
                                $notif->modelType = $this->modelClass;
                                $notif->modelId = $new['id'];
                                if($notif->save()){
                                    $event->associationId=$notif->id;
                                    $event->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        $changes=array();
        foreach ($arr as $key => $item) {
            if(is_array($old[$key]))
                $old[$key] = implode(', ',$old[$key]);
            $changes[$key]=array('old'=>$old[$key],'new'=>$new[$key]);
        }
        return $changes;
    } */

    public function decodeQuotes($str) {
        return preg_replace('/&quot;/u', '"', $str);
    }

    public function encodeQuotes($str) {
        // return htmlspecialchars($str);
        return preg_replace('/"/u', '&quot;', $str);
    }

    public function getPhpMailer($sendAs = -1) {
        $mail = new InlineEmail;
        $mail->credId = $sendAs;
        return $mail->mailer;
    }

    public function throwException($message) {
        throw new Exception($message);
    }

    /**
     * Send an email from X2Engine, returns an array with status code/message
     *
     * @param array addresses
     * @param string $subject the subject for the email
     * @param string $message the body of the email
     * @param array $attachments array of attachments to send
     * @param array|integer $from from and reply to address for the email array(name, address)
     *     or, if integer, the ID of a email credentials record to use for delivery.
     * @return array
     */
    public function sendUserEmail($addresses, $subject, $message, $attachments = null, $from = null){
        $eml = new InlineEmail();
        if(is_array($addresses) ? count($addresses)==0 : true)
            throw new Exception('Invalid argument 1 sent to x2base.sendUserEmail(); expected a non-empty array, got instead: '.var_export($addresses,1));
        // Set recipients:
        if(array_key_exists('to',$addresses) || array_key_exists('cc',$addresses) || array_key_exists('bcc',$addresses)) {
            $eml->mailingList = $addresses;
        } else
            return array('code'=>500,'message'=>'No recipients specified for email; array given for argument 1 of x2base.sendUserEmail does not have a "to", "cc" or "bcc" key.');
        // Resolve sender (use stored email credentials or system default):
        if($from === null || in_array($from,Credentials::$sysUseId)) {
            $from = (int) Credentials::model()->getDefaultUserAccount($from);
            // Set to the user's name/email if no valid defaults found:
            if($from == Credentials::LEGACY_ID)
                $from = array('name' => Yii::app()->params->profile->fullName, 'address'=> Yii::app()->params->profile->emailAddress);
        }

        if(is_numeric($from))
            $eml->credId = $from;
        else
            $eml->from = $from;
        // Set other attributes
        $eml->subject = $subject;
        $eml->message = $message;
        $eml->attachments = $attachments;
        return $eml->deliver();
    }

    public function parseEmailTo($string) {

        if (empty($string))
            return false;
        $mailingList = array();
        $splitString = explode(',', $string);

        require_once('protected/components/phpMailer/class.phpmailer.php');

        foreach ($splitString as &$token) {

            $token = trim($token);
            if (empty($token))
                continue;

            $matches = array();

            if (PHPMailer::ValidateAddress($token)) { // if it's just a simple email, we're done!
                $mailingList[] = array('', $token);
            } else if (preg_match('/^"?([^"]*)"?\s*<(.+)>$/i', $token, $matches)) {
                if (count($matches) == 3 && PHPMailer::ValidateAddress($matches[2]))
                    $mailingList[] = array($matches[1], $matches[2]);
                else
                    return false;
            } else
                return false;

            // if(preg_match('/^"(.*)"/i',$token,$matches)) {        // if there is a name like <First Last> at the beginning,
            // $token = trim(preg_replace('/^".*"/i','',$token));    // remove it
            // if(isset($matches[1]))
            // $name = trim($matches[1]);                        // and put it in $name
            // }
            // $address = trim(preg_replace($token);
            // if(PHPMailer::ValidateAddress($address))
            // $mailingList[] = array($address,$name);
            // else
            // return false;
        }
        // echo var_dump($mailingList);

        if (count($mailingList) < 1)
            return false;

        return $mailingList;
    }

    public function mailingListToString($list, $encodeQuotes = false) {
        $string = '';
        if (is_array($list)) {
            foreach ($list as &$value) {
                if (!empty($value[0]))
                    $string .= '"' . $value[0] . '" <' . $value[1] . '>, ';
                else
                    $string .= $value[1] . ', ';
            }
        }
        return $encodeQuotes ? $this->encodeQuotes($string) : $string;
    }

    /**
     * Obtain the widget list for the current web user.
     *
     * @param CFilterChain $filterChain
     */
    public function filterSetPortlets($filterChain) {
        if (!Yii::app()->user->isGuest) {
            $themeURL = Yii::app()->theme->getBaseUrl();
            $this->portlets = Profile::getWidgets();
        }
        $filterChain->run();
    }


    // This function needs to be made in your extensions of the class with similar code.
    // Replace "Opportunities" with the Model being used.
    /*     * public function loadModel($id)
      {
      $model=Opportunity::model()->findByPk((int)$id);
      if($model===null)
      throw new CHttpException(404,'The requested page does not exist.');
      return $model;
      } */

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax'])) {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Called by the InlineRelationships widget to render autocomplete widgets 
     * @param string $modelType
     */
    public function actionAjaxGetModelAutocomplete ($modelType, $name=null) {
        $htmlOptions = array ();
        if ($name) {
            $htmlOptions['name'] = $name;
        }
        X2Model::renderModelAutocomplete ($modelType, true, $htmlOptions);
    }

    /**
     * Calls renderInput for model and input type with given names and returns the result.
     */
    public function actionGetX2ModelInput ($modelName, $inputName) {
        if (!isset ($modelName) || !isset ($inputName)) {
            echo '';
            return;
        }
        $model = X2Model::model ($modelName);
        if (!$model) {
            echo '';
            return;
        }
        if ($inputName == 'associationName') {
            echo CHtml::activeDropDownList(
                $model, 'associationType', 
                array_merge(
                    array(
                        'none' => Yii::t('app', 'None'), 
                        'calendar' => Yii::t('calendar', 'Calendar')), 
                    Fields::getDisplayedModelNamesList()
                ), 
                array(
                'ajax' => array(
                    'type' => 'POST', //request type
                    'url' => CController::createUrl('/actions/actions/parseType'), //url to call.
                    //Style: CController::createUrl('currentController/methodToCall')
                    'update' => '#', //selector to update
                    'data' => 'js:$(this).serialize()',
                    'success' => 'function(data){
                                        if(data){
                                            $("#auto_select").autocomplete("option","source",data);
                                            $("#auto_select").val("");
                                            $("#auto_complete").show();
                                        }else{
                                            $("#auto_complete").hide();
                                        }
                                    }'
                )
            ));
            echo "<div id='auto_complete' style='display: none'>";
            $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                        'name' => 'auto_select',
                        'value' => $model->associationName,
                        'source' => ($model->associationType !== 'Calendar' ? 
                            $this->createUrl(X2Model::model($modelName)->autoCompleteSource) : ''),
                        'options' => array(
                            'minLength' => '2',
                            'select' => 'js:function( event, ui ) {
                            $("#'.CHtml::activeId($model, 'associationId').'").val(ui.item.id);
                            $(this).val(ui.item.value);
                            return false;
                        }',
                        ),
            ));
            echo "</div>";
        } else {
            $input = $model->renderInput ($inputName);
            echo $input;
        }

        // force loading of scripts normally rendered in view
        echo '<br /><br /><script id="x2-model-render-input-scripts">'."\n";
        if (isset (Yii::app()->clientScript->scripts[CClientScript::POS_READY])) {
            foreach(
                Yii::app()->clientScript->scripts[CClientScript::POS_READY] as $id => $script) {

                if(strpos($id,'logo')===false)
                echo "$script\n";
            }
        }
        echo "</script>";
    }

    /**
     * Helper method to hide specific menu options or unset
     * links before the menu is rendered
     * @param array $menuItems Original menu items
     * @param array|true $selectOptions Menu items to include. If set to true, all default menu
     *  items will get displayed
     */
    protected function prepareMenu(&$menuItems, $selectOptions) {
        if ($selectOptions === true) {
            $selectOptions = array_map (function ($item) {
                return $item['name'];
            }, $menuItems);
        }
        $curAction = $this->action->id;
        for ($i = count($menuItems) - 1; $i >= 0; $i--) {
            // Iterate over the items from the end to avoid consistency issues
            // while items are being removed
            $item = $menuItems[$i];

            // Remove requested items
            if (!in_array($item['name'], $selectOptions)) {
                unset($menuItems[$i]);
            }
            // Hide links to requested items
            else if ((is_array($item['url']) && in_array($curAction, $item['url']))
                    || $item['url'] === $curAction) {
                unset($menuItems[$i]['url']);
            }
        }
    }

    protected function renderLayout ($layoutFile, $output) {
        $output = $this->renderFile (
            $layoutFile,
            array (
                'content'=>$output
            ), true);
        return $output;
    }

    /**
     * Override parent method so that layout business logic can be moved to controller 
     */
    public function render($view,$data=null,$return=false)
    {
        if($this->beforeRender($view))
        {
            $output=$this->renderPartial($view,$data,true);

            /* x2modstart */ 
            if(($layoutFile=$this->getLayoutFile($this->layout))!==false) {
                $output = $this->renderLayout ($layoutFile, $output);
            }
            /* x2modend */ 

            $this->afterRender($view,$output);

            $output=$this->processOutput($output);

            if($return)
                return $output;
            else
                echo $output;
        }
    }

    /**
     * Overrides parent method so that x2base's _pageTitle property is used instead of 
     * CController's.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function setPageTitle($value) {
        $this->_pageTitle = $value;
    }

    /**
     * Overrides parent method so that configurable app name is used instead of name
     * from the config file.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function getPageTitle() {
        if($this->_pageTitle!==null) {
            return $this->_pageTitle;
        } else {
            $name=ucfirst(basename($this->getId()));

            // Try and load the configured module name
            $moduleName = Modules::displayName(true, $name);
            if (!empty($moduleName))
                $name = $moduleName;

            if($this->getAction()!==null && 
               strcasecmp($this->getAction()->getId(),$this->defaultAction)) {

                return $this->_pageTitle=
                    /* x2modstart */Yii::app()->settings->appName/* x2modend */.' - '.
                        ucfirst($this->getAction()->getId()).' '.$name;
            } else {
                return $this->_pageTitle=
                    /* x2modstart */Yii::app()->settings->appName/* x2modend */.' - '.$name;
            }
        }
    }

    /**
     * Assumes that convention of (<module name> === ucfirst (<modelClass>)) is followed. 
     * @return Module module associated with this controller.  
     */
    /*public function getModuleModel () {
        return Modules::model()->findByAttributes (array ('name' => ucfirst ($this->modelClass)));
    }*/

    /**
     * Overridden to add $run param
     * 
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function widget($className,$properties=array(),$captureOutput=false,$run=true)
    {
        if($captureOutput)
        {
            ob_start();
            ob_implicit_flush(false);
            $widget=$this->createWidget($className,$properties);
            /* x2modstart */ 
            if ($run) $widget->run();
            /* x2modend */ 
            return ob_get_clean();
        }
        else
        {
            $widget=$this->createWidget($className,$properties);
            /* x2modstart */ 
            if ($run) $widget->run();
            /* x2modend */ 
            return $widget;
        }
    }

    public function badRequest ($message=null) {
        throw $this->badRequestException ($message);
    }

    public function isAjaxRequest () {
        return 
            isset ($_POST['x2ajax']) && $_POST['x2ajax'] || 
            isset ($_POST['ajax']) && $_POST['ajax'] || 
            isset ($_GET['x2ajax']) && $_GET['x2ajax'] || 
            isset ($_GET['ajax']) && $_GET['ajax'];
    }

    /**
     * @return CHttpException 
     */
    protected function badRequestException ($message=null) {
        if ($message === null) $message = Yii::t('app', 'Bad request.');
        return new CHttpException (400, $message);
    }

}
