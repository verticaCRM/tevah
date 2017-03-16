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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/Relationships.js');

Yii::app()->clientScript->registerCss('contactRecordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
.show-left-bar .page-title > .x2-button {
    display: none !important;
}

");

Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');

$this->setPageTitle(empty($model->name) ? $model->firstName." ".$model->lastName : $model->name);

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerScript('hints', '
    $(".hint").qtip();
');

// find out if we are subscribed to this contact
$result = Yii::app()->db->createCommand()
        ->select()
        ->from('x2_subscribe_contacts')
        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), 
            array(':contact_id' => $model->id, 'user_id' => Yii::app()->user->id))
        ->queryAll();
$subscribed = !empty($result); // if we got any results then user is subscribed

$modTitles = array(
    'contact' => Modules::displayName(false),
    'account' => Modules::displayName(false, "Accounts"),
    'opportunity' => Modules::displayName(false, "Opportunities"),
);

$authParams['X2Model'] = $model;

$opportunityModule = Modules::model()->findByAttributes(array('name' => 'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name' => 'accounts'));
$serviceModule = Modules::model()->findByAttributes(array('name' => 'services'));

$menuOptions = array(
    'all', 'lists', 'create', 'view', 'edit', 'share', 'delete',
    'email', 'attach', 'quotes', 'print', 'editLayout',
);
$menuOptions[] = ($subscribed ? 'unsubscribe' : 'subscribe');
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions, $model, $authParams);

$modelType = json_encode("Contacts");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('subscribe', "
$(function() {
    $('body').data('subscribed', ".json_encode($subscribed).");
    $('body').data('subscribeText', ".json_encode(Yii::t('contacts', 'Subscribe')).");
    $('body').data('unsubscribeText', ".json_encode(Yii::t('contacts', 'Unsubscribe')).");
    $('body').data('modelType', $modelType);
    $('body').data('modelId', $modelId);


    $('.x2-subscribe-button').qtip();
});

// subscribe or unsubscribe from this contact
function subscribe(link) {
    $('body').data('subscribed', !$('body').data('subscribed')); // subscribe or unsubscribe
    $.post('subscribe', {ContactId: '{$model->id}', Checked: $('body').data('subscribed')}); // tell server to subscribe / unsubscribe
    if( $('body').data('subscribed') )
        link.html($('body').data('unsubscribeText'));
    else
        link.html($('body').data('subscribeText'));
    return false; // stop event propagation
}

", CClientScript::POS_HEAD);

// widget layout
$layout = Yii::app()->params->profile->getLayout();
$themeUrl = Yii::app()->theme->getBaseUrl();
?>
<?php
if(true) {//!IS_ANDROID && !IS_IPAD){
    echo '
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">';
}
?>
<div class="page-title">
    <?php 
    $this->widget('RecordAliasesWidget', array(
        'model' => $model
    ));
    $this->renderPartial('_vcrControls', array('model' => $model)); 
    if(Yii::app()->user->checkAccess('ContactsUpdate', $authParams)){
        if(!empty($model->company) && is_numeric($model->company)) {
            echo CHtml::link(
                '<span></span>', '#',
                array(
                    'class' => 'x2-button icon sync right hint',
                    'id' => $model->id.'-account-sync',
                    'title' => Yii::t('contacts', 'Clicking this button will pull any relevant '.
                        'fields from the associated {account} record and overwrite the {contact} '.
                        'data for those fields.  This operation cannot be reversed.',array(
                            '{account}' => $modTitles['account'],
                            '{contact}' => $modTitles['contact'],
                        )),
                    'submit' => array(
                        'syncAccount',
                        'id' => $model->id
                    ),
                    'confirm' => 'Are you sure you want to overwrite this record\'s fields with '.
                        'relevant Account data?'
                )
            );
        }
        echo CHtml::link(
            '', $this->createUrl('update', array('id' => $model->id)),
            array(
                'class' => 'x2-button icon edit right',
                'title' => Yii::t('app', 'Edit {module}', array(
                    '{module}' => $modTitles['contact'],
                )),
            )
        );
    }
    echo X2Html::emailFormButton();
    echo X2Html::inlineEditButtons();
    ?>
</div>
<?php
if(true){ //!IS_ANDROID && !IS_IPAD){
    echo '
    </div>
</div>
        ';
}
?>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
    <div id='contacts-detail-view'> 
    <?php 
    $this->renderPartial(
        'application.components.views._detailView', 
        array('model' => $model, 'modelName' => 'contacts')); 
    ?>
    </div>
    <?php

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'to' => '"'.$model->name.'" <'.$model->email.'>, ',
            'modelName' => 'Contacts',
            'modelId' => $model->id,
            'targetModel' => $model,
        ),
        'startHidden' => true,
    ));

    /*     * * Begin Create Related models ** */

    $linkModel = X2Model::model('Accounts')->findByAttributes(array(
        'nameId' => $model->company
    ));
    if(isset($linkModel))
        $accountName = $linkModel->name;
    else
        $accountName = '';
    $createContactUrl = $this->createUrl('/contacts/contacts/create');
    $createAccountUrl = $this->createUrl('/accounts/accounts/create');
    $createOpportunityUrl = $this->createUrl('/opportunities/opportunities/create');
    $createCaseUrl = $this->createUrl('/services/services/create');
    $assignedTo = $model->assignedTo;
    $tooltip = (
        Yii::t('contacts', 'Create a new {opportunity} associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
            '{opportunity}' => $modTitles['opportunity'],
        ))
    );
    $contactTooltip = (
        Yii::t('contacts', 'Create a new {contact} associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
        ))
    );
    $accountsTooltip = (
        Yii::t('contacts', 'Create a new {account} associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
            '{account}' => $modTitles['account'],
        ))
    );
    $caseTooltip = (
        Yii::t('contacts', 'Create a new {service} Case associated with this {contact}.', array(
            '{contact}' => $modTitles['contact'],
            '{service}' => Modules::displayName(false, "Services"),
        ))
    );
    $contactName = $model->firstName.' '.$model->lastName;
    $phone = $model->phone;
    $website = $model->website;
    $leadSource = $model->leadSource;
    $leadtype = $model->leadtype;
    $leadStatus = $model->leadstatus;
//*** End Create Related models ***/

    $this->widget(
        'Attachments', array(
            'associationType' => 'contacts',
            'associationId' => $model->id,
            'startHidden' => true)); ?>
    <div id="quote-form-wrapper">
        <?php
        $this->widget('InlineQuotes', array(
            'startHidden' => true,
            'recordId' => $model->id,
            'contactId' => $model->id,
            'account' => $model->getLinkedAttribute('company', 'name'),
            'modelName' => X2Model::getModuleModelName ()
        ));
        ?>
    </div>

</div>
<?php
$this->widget('X2WidgetList', array(
    'model' => $model,
    'layoutManager' => $layoutManager,
    'widgetParamsByWidgetName' => array (
        'InlineRelationshipsWidget' => array (
            'defaultsByRelatedModelType' => array (
                'Accounts' => array (
                    'name' => $accountName,
                    'assignedTo' => $assignedTo,
                    'phone' => $phone,
                    'website' => $website
                ),
                'Contacts' => array (
                    'company' => $accountName,
                    'assignedTo' => $assignedTo,
                    'leadSource' => $leadSource,
                    'leadtype' => $leadtype,
                    'leadstatus' => $leadStatus
                ),
                'Opportunity' => array (
                    'accountName' => $accountName,
                    'assignedTo' => $assignedTo,
                ),
                'Services' => array (
                    'contactName' => $contactName,
                    'assignedTo' => $assignedTo,
                )
            )
        )
    )
));
?>
