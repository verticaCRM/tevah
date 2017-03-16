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

// editor javascript files
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/emailEditor.js');

Yii::app()->clientScript->registerCss('docFormCss',"

#doc-name,
#email-to-field,
#doc-subject {
    width: 260px;
}

#create-button-container {
    width: auto !important;
    margin-top: 6px;
}

");

Yii::app()->clientScript->registerResponsiveCss('responsiveDocFormCss',"

@media (max-width: 657px) {
    #doc-name,
    #email-to-field,
    #doc-subject {
        width:160px;
    }
}

");

$modTitles = array(
    'contact' => Modules::displayName(false, "Contacts"),
    'account' => Modules::displayName(false, "Accounts"),
    'quote' => Modules::displayName(false, "Quotes"),
);

$autosaveUrl = $this->createUrl('autosave').'?id='.$model->id;

$js = '';

if($model->type==='email' || $model->type ==='quote') {
	$attributes = array();
	if($model->type === 'email')
		foreach(X2Model::model('Contacts')->getAttributeLabels() as $fieldName => $label)
			$attributes[$label] = '{'.$fieldName.'}';
	else {
		$accountAttributes = array();
		$contactAttributes = array();
		$quoteAttributes = array();
        foreach(Contacts::model()->getAttributeLabels() as $fieldName => $label) {
            AuxLib::debugLog ('Iterating over contact attributes '.$fieldName.'=>'.$label);
            $index = Yii::t('contacts',"{contact}", array(
                '{contact}' => $modTitles['contact'],
            )).": $label";
            $contactAttributes[$index] = "{".$modTitles['contact'].".$fieldName}";
        }
        foreach(Accounts::model()->getAttributeLabels() as $fieldName => $label) {
            AuxLib::debugLog ('Iterating over account attributes '.$fieldName.'=>'.$label);
            $index = Yii::t('accounts',"{account}", array(
                '{account}' => $modTitles['account'],
            )).": $label";
            $accountAttributes[$index] = "{".$modTitles['account'].".$fieldName}";
        }

        $Quote = Yii::t('quotes', "{quote}: ", array('{quote}' => $modTitles['quote']));
        $quoteAttributes[$Quote.Yii::t('quotes',"Item Table")] = '{'.$modTitles['quote'].'.lineItems}';
        $quoteAttributes[$Quote.Yii::t('quotes',"Date printed/emailed")] = '{'.$modTitles['quote'].'.dateNow}';
        $quoteAttributes[$Quote.Yii::t('quotes','{quote} or Invoice', array('{quote}'=>$modTitles['quote']))] = '{'.$modTitles['quote'].'.quoteOrInvoice}';
        foreach(Quote::model()->getAttributeLabels() as $fieldName => $label) {
            $index = $Quote."$label";
            $quoteAttributes[$index] = "{".$modTitles['quote'].".$fieldName}";
        }
	}
	if($model->type === 'email') {
		$js = 'x2.insertableAttributes = '.CJSON::encode(array(Yii::t('contacts','{contact} Attributes', array('{contact}'=>$modTitles['contact']))=>$attributes)).';';
	} else {
		$js = 'x2.insertableAttributes = '.CJSON::encode(array(
			Yii::t('docs','{contact} Attributes', array('{contact}'=>$modTitles['contact'])) => $contactAttributes,
			Yii::t('docs','{account} Attributes', array('{account}'=>$modTitles['account'])) => $accountAttributes,
			Yii::t('docs','{quote} Attributes', array('{quote}'=>$modTitles['quote'])) => $quoteAttributes
		)).';';
	}
}

if($model->type === 'email'){ 

// allowable association types
$associationTypeOptions = Docs::modelsWhichSupportEmailTemplates ();

// insertable attributes by model type
$insertableAttributes = array ();
foreach ($associationTypeOptions as $modelName=>$label) {
    $insertableAttributes[$modelName] = array ();
    foreach(X2Model::model($modelName)->getAttributeLabels() as $fieldName => $label) {
        $insertableAttributes[$modelName][$label] = '{'.$fieldName.'}';
    }
}


Yii::app()->clientScript->registerScript('createEmailTemplateJS',"

(function () {

var insertableAttributes = ".CJSON::encode ($insertableAttributes).";

// reinitialize ckeditor instance with new set of insertable attributes whenever the record type
// selector is changed
$('#email-association-type').change (function () {
    
    var data = window.docEditor.getData ();
    window.docEditor.destroy (true);
    $('#input').val (data);
    var recordInsertableAttributes = {};
    recordInsertableAttributes[$(this).val () + ' Attributes'] = 
        insertableAttributes[$(this).val ()];
    instantiateDocEditor (recordInsertableAttributes);
});

}) ();

");

}

$js .='
var typingTimer;

function autosave() {
	window.docEditor.updateElement();
	$("#savetime").html("'.addslashes(Yii::t('app','Saving...')).'");
	$.post("'.$autosaveUrl.'", $("form").serializeArray(), function(response) {
		$("#savetime").html(response);
	});
}

if(window.docEditor)
	window.docEditor.destroy(true);

function instantiateDocEditor (insertableAttributes) {
    var insertableAttributes = typeof insertableAttributes === "undefined" ? 
        x2.insertableAttributes : insertableAttributes; 

    window.docEditor = createCKEditor("input",{
            '.($model->type==='email' || $model->type == 'quote' ? 
                'insertableAttributes:insertableAttributes,':'').'
        // toolbar:"Full",
        fullPage:true,
        height:600
    }'.($model->isNewRecord? '' : ',setupAutosave').');
}

instantiateDocEditor ();


function setupAutosave() {
	if($.browser.msie)
		return;
	// save after 1.5 seconds when the user is done typing

	window.docEditor.document.on("keyup",function(e) {
		clearTimeout(typingTimer);
		typingTimer = setTimeout(autosave, 1500);
	});
	window.docEditor.on("saveSnapshot",function(e) {
		clearTimeout(typingTimer);
		typingTimer = setTimeout(autosave, 1500);
	});
	window.docEditor.document.on("keydown",function(){ clearTimeout(typingTimer); });
}';

Yii::app()->clientScript->registerScript('doc-editor',$js,CClientScript::POS_READY);

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'docs-form',
	'enableAjaxValidation'=>false,
)); ?>
<div class="form no-border">
	<div class="row">
		<div class="cell">
			<?php 
            echo $form->errorSummary($model); 
			echo $form->label($model,'name'); 
			echo $form->textField(
                $model,'name',
                array('maxlength'=>100,'id'=>'doc-name')); 
			echo $form->error($model,'name'); 
            ?>
		</div>
		<div class="cell">
			<?php echo $form->label($model,'visibility'); ?>
			<?php echo $form->dropDownList($model,'visibility',array(1=>Yii::t('app','Public'),0=>Yii::t('app','Private'))); ?>
			<?php echo $form->error($model,'visibility'); ?>
		</div>
		<div class="cell right" id='create-button-container'>
			<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button float')); ?>
		</div>
	</div>
     
    <?php
    if($model->type === 'email'){ 
    ?>
	<div class="row">
    <?php
        echo $form->label($model,'associationType'); 
        echo $form->dropdownList($model,'associationType', $associationTypeOptions, array (
            'id' => 'email-association-type'
        ));
        echo $form->error($model,'associationType'); 
    ?>
	</div>
	<div class="row">
    <?php
        echo $form->label($model,'emailTo'); 
        echo $form->textField($model,'emailTo', array (
            'id' => 'email-to-field'
        ));
        echo $form->error($model,'emailTo'); 
        echo X2Html::hint (
            Yii::t('docs', 
            'Leaving this field blank will preserve its default behavior.'), false);
    ?>
	</div>
    <?php
    }
    ?>
	<div class="row">
        <?php 
        if(in_array($model->type,array('email','quote'))){ 
            echo $form->label($model,'subject'); 
            echo $form->textField(
                $model,'subject',
                array('maxlength'=>255,'id'=>'doc-subject')); 
            echo $form->error($model,'subject'); 
        } 
        ?>
		<span id="savetime">
			<?php if(isset($_GET['saved'])){
				$date=date("g:i:s A",$_GET['time']);
				echo Yii::t('docs', 'Saved at') ." $date";
			} ?>
		</span>
	</div><?php  ?>
	<div class="row" style="margin-top:5px;">
		<?php
		if($model->isNewRecord && isset($users) && !in_array($model->type,array('email','quote'))){
			echo $form->label($model,'editPermissions');
			echo $form->dropDownList($model,'editPermissions',$users,array('multiple'=>'multiple','size'=>'5'));
			echo $form->error($model,'editPermissions');
		}
		if($model->type == 'email'){
?>
		<div class="row">
	<?php echo Yii::t('docs', '<b>Note:</b> You can use dynamic variables such as {firstName}, {lastName} or {phone} in your template. When you email a record of the specified type, these will be replaced by the appropriate value.'); ?>
		</div><?php }elseif($model->type == 'quote'){ ?>
		<div class="row">
    <?php echo Yii::t('docs', '<strong>Note:</strong> You can use dynamic variables such as {{contact}.firstName}, {{quote}.dateCreated}, {{account}.name} etc. in your template. When you email or print the {quoteLc}, these will be replaced with the appropriate values from the {quoteLc} or its associated {contactLc}/{accountLc}.', array(
        '{contact}' => $modTitles['contact'],
        '{account}' => $modTitles['account'],
        '{quote}' => $modTitles['quote'],
        '{contactLc}' => lcfirst($modTitles['contact']),
        '{accountLc}' => lcfirst($modTitles['account']),
        '{quoteLc}' => lcfirst($modTitles['quote']),
    )); ?>
		</div>
<?php
}
		echo $form->error($model,'text');
		echo $form->textArea($model,'text',array('id'=>'input'));
		?>
	</div>

</div>
<?php echo $form->error($model,'text'); ?>

<?php $this->endWidget(); ?>
