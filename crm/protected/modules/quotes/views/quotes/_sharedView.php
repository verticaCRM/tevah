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

$templateRec = Yii::app()->db->createCommand()->select('nameId,name')->from('x2_docs')->where("type='quote'")->queryAll();
$templates = array();
$templates[null] = '(none)';
foreach($templateRec as $tmplRec){
	$templates[$tmplRec['nameId']] = $tmplRec['name'];
}
echo '<div style="display:inline-block; margin: 8px 11px;" ' .
    'id="quote-template-dropdown">';
echo '<strong>'.$form->label($model, 'template').'</strong>&nbsp;';
echo $form->dropDownList($model, 'template', $templates).'&nbsp;'
    .X2Html::hint2 (
        Yii::t('quotes', 
            'To create a template for {quotes} and invoices, go to the {docs} module and select '.
            '"Create {quote}".', array(
                '{quotes}' => lcfirst(Modules::displayName()),
                '{quote}' => Modules::displayName(false),
                '{docs}' => Modules::displayName(true, "Docs"),
            )));
echo '</div><br />'; 
echo '	<div class="row buttons" style="padding-left:0;">'."\n";
echo $quick?CHtml::button(Yii::t('app','Cancel'),array('class'=>'x2-button right','id'=>'quote-cancel-button','tabindex'=>24))."\n":'';
echo CHtml::submitButton(Yii::t('app', $action), array('class' => 'x2-button'.($quick?' highlight':''), 'id' => 'quote-save-button', 'tabindex' => 25, 'onClick' => 'return x2.quoteslineItems.validateAllInputs ();'))."\n";
echo "	</div>\n";
echo '<div id="quotes-errors"></div>';

?>

