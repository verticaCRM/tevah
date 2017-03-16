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
 * Provides an inline form for sending email from a view page.
 *
 * @property X2Model $targetModel The model of the form.
 * @property integer $template The default template to use when opening the form.
 * @property string $templateType The class of template. Different templates are meant for different models and scenarios.
 * @property InlineEmail $model The form model for email handling and delivery
 * @property array $insertableAttributes Can be manually set to specify insertable attributes for this scenario. Otherwise, {@link InlineEmail::getInsertableAtributes()} will be used.
 * @property string $type Type; null is default (plain email). Specifies which list of templates to fetch.
 * @property bool $startHidden If true, it will not be visible on page load
 * @property string $specialFields Extra HTML to render inside the form.
 * @property bool $postReplace If true, variable replacement will be run on non-template user input.
 * @property bool $skipEvent If true, no event record will be created.
 * @package application.components
 */
class InlineEmailForm extends X2Widget {

    public $attributes;

    public $template = null;

    public $templateType = 'email';

    public $model;

    public $targetModel;

    public $contactFlag = 1;

    public $insertableAttributes;

    public $errors = array();

    public $startHidden = false;

    public $specialFields = '';

    public $postReplace = 0;

    public $skipEvent = 0;

    /**
     * @var bool $hideFromField
     */
    public $hideFromField = false;  

    /**
     * @var bool $disableTemplates
     */
    public $disableTemplates = false;  

    /**
     * @var string the association type of the email templates
     */
    public $associationType = null;

    /**
     * @var string $JSClass
     */
    public $JSClass = 'InlineEmailEditorManager'; 

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'InlineEmailEditorManager' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/InlineEmailResizable.js',
                        'js/inlineEmailForm.js',
                    ),
                    'depends' => array ('jquery.ui'),
                ),
            ));
        }
        return $this->_packages;
    }

    public function init(){
        $this->disableTemplates = $this->disableTemplates ||
            in_array ($this->associationType, 
                array_keys (Docs::modelsWhichSupportEmailTemplates ()));

        // Prepare the model for initially displayed input:
        $this->model = new InlineEmail();
        if(isset($this->targetModel)) {
            $this->model->targetModel = $this->targetModel;
        }

        if (!$this->associationType) {
            $this->associationType = X2Model::getModelName (Yii::app()->controller->module->name);
        }

        // Bring in attributes set in the configuration:
        $this->model->attributes = $this->attributes;

        if (empty ($this->template)) {
            // check for a default template
            $defaultTemplateId = Yii::app()->params->profile->getDefaultEmailTemplate (
                Yii::app()->controller->module->name);

            // if there's a default set for this module
            if ($defaultTemplateId !== null) {
                $defaultTemplateDoc = Docs::model()->findByPk ($defaultTemplateId);

                // ensure that template is still a valid default
                if ($defaultTemplateDoc && 
                    ($defaultTemplateDoc->associationType === $this->associationType ||
                    $defaultTemplateDoc->type === 'quote' && 
                    $this->model->targetModel instanceof Quote)) {

                    $this->template = $defaultTemplateId;
                }
            }
        }

        if(empty($this->template)){
            if(empty($this->model->message))
                $this->model->message = InlineEmail::emptyBody();
            $this->model->insertSignature();
        }else{
            // Fill in the body with a template:
            $this->model->scenario = 'template';
            if (!empty ($this->template))
                $this->model->template = $this->template;
            $this->model->prepareBody();
        }

        // If insertable attributes aren't set, use the inline email model's 
        // getInsertableAttributes() method to generate them.
        if((bool) $this->model->targetModel && !isset($this->insertableAttributes)){
            $this->insertableAttributes = $this->model->insertableAttributes;
        }

        $this->registerJSClassInstantiation ();

        // Load resources:
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');
        if(!empty($this->insertableAttributes)){
            Yii::app()->clientScript->registerScript('setInsertableAttributes', 
            'x2.insertableAttributes = '.CJSON::encode($this->insertableAttributes).';', 
            CClientScript::POS_HEAD);
        }
        Yii::app()->clientScript->registerScript('storeOriginalInlineEmailMessage', 
            'x2.inlineEmailOriginalBody = $("#email-message").val();', 
        CClientScript::POS_READY); 
            //'.CJSON::encode($this->model->message).';',CClientScript::POS_READY);

        Yii::app()->clientScript->registerScript('toggleEmailForm', 
            ($this->startHidden ? 
            "window.hideInlineEmail = true;\n" : 
            "window.hideInlineEmail = false;\n"
        ), CClientScript::POS_HEAD);

        $this->registerPackages ();
        parent::init();
    }

    public function registerJSClassInstantiation () {
        Yii::app()->clientScript->registerScript('InlineEmailFormJS',"
        x2.inlineEmailEditorManager = new x2.{$this->JSClass} ({
            translations: ".CJSON::encode (array (
                'defaultTemplateDialogTitle' => 
                    Yii::t('app', 'Set a Default Email Template'),
                'Cancel' => Yii::t('app', 'Cancel'),
                'Save' => Yii::t('app', 'Save'),
                'New Message' => Yii::t('app', 'New Message'),
            )).",
            disableTemplates: ".CJSON::encode ($this->disableTemplates).",
            saveDefaultTemplateUrl: '".
                Yii::app()->controller->createUrl (
                    '/profile/profile/ajaxSaveDefaultEmailTemplate')."',
            tmpUploadUrl: '".Yii::app()->createUrl('/site/tmpUpload')."', 
            rmTmpUploadUrl: '".Yii::app()->createUrl('/site/removeTmpUpload')."'
        });
        ", CClientScript::POS_END);
    }

    public function run(){
        $this->render('application.components.views.inlineEmailForm', array(
            'type' => $this->templateType,
            'associationType' => $this->associationType,
            'specialFields' => $this->specialFields,
        ));
    }

}
