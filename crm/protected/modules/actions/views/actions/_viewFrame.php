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
Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
$jsVersion = '?'.Yii::app()->params->buildDate;
$themeUrl = Yii::app()->theme->getBaseUrl();
$baseUrl = Yii::app()->request->getBaseUrl();
$dateFormat = Formatter::formatDatePicker('medium');
$timeFormat = Formatter::formatTimePicker();
$amPm = Formatter::formatAMPM() ? 'true' : 'false';
$language = (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage();
?>
<!DOCTYPE html>
<!--[if lt IE 9]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>" class="lt-ie9">
<![endif]-->
<!--[if gt IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<![endif]-->
<!--[if !IE]> -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
    <!-- <![endif]-->
    <head>
        <meta charset="UTF-8" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/main.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/details.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/form.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/ui-elements.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/x2forms.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/responsiveLayout.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/responsiveUIElements.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo $themeUrl; ?>/css/responsiveX2Forms.css?<?php echo $jsVersion ?>" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/css/base/jquery-ui.css'; ?>" />
        <!--/* x2prostart */ used for products actions -->
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule ('quotes')->assetsUrl.'/css/lineItemsMain.css'; ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule ('quotes')->assetsUrl.'/css/lineItemsRead.css'; ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule ('quotes')->assetsUrl.'/css/lineItemsWrite.css'; ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule ('quotes')->assetsUrl.'/css/lineItemsMini.css'; ?>" />
        <!-- /* x2proend */ -->
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jquery.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/js/jquery-ui.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->clientScript->coreScriptUrl.'/jui/js/jquery-ui-i18n.min.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/jquery-ui-timepicker-addon.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/qtip/jquery.qtip.min.js'; ?>"></script>
        <?php
        $mmPath = Yii::getPathOfAlias('application.extensions.moneymask.assets');
        $aMmPath = Yii::app()->getAssetManager()->publish($mmPath);
        ?>
        <script type="text/javascript" src="<?php echo "$aMmPath/jquery.maskMoney.js"; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/auxlib.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getBaseUrl().'/js/X2Forms.js'; ?>"></script>
        <script>
            x2.forms = new x2.Forms ({
                translations: <?php echo CJSON::encode (array (
                    'Check All' => Yii::t('app', 'Check All'),
                    'Uncheck All' => Yii::t('app', 'Uncheck All'),
                    'selected' => Yii::t('app', 'selected'),
                )); ?>
            });
        </script>
        <!--/* x2prostart */ used for products actions-->
        <script><?php echo Yii::app()->clientScript->getCurrencyConfigScript (); ?></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getModule ('quotes')->assetsUrl.'/js/LineItems.js'; ?>"></script>
        <!--/* x2proend */ -->
        <script type="text/javascript">
            $(document).ready(function() {
                // links inside iframe to pages in app should redirect parent window 
                $("a").click(function(event){
                    // exceptions: vcr buttons, products dropdown items
                    if(!$(this).hasClass('vcr-button') && !$(this).hasClass ('ui-corner-all')) {
                        event.preventDefault();
                        var thiswindow = window, i = 0;
                        while(thiswindow != top && i < 10) {
                            thiswindow = thiswindow.parent;
                            i++;
                        }
                        thiswindow.location = this.getAttribute("href");
                    }
                });
            });
        </script>
        <style>
            #header-content {
                display: inline-block;
                margin-left:5px;
                width: 100%;
                height: 26px;
            }
            #controls {
                height: 17px;
                display: inline-block;
                padding-top: 5px;
            }
            .control-button{
                display:inline-block;
                margin-top:-5px;
                padding-right:10px;
                vertical-align:middle;
                cursor:pointer;
            }
            .vcrPager {
                height: 32px;
                margin: -3px 0px 5px 0px !important;
            }
            a.vcr-button{
                padding: 1px 15px;
                margin-top:-5px;
            }
            .model-link a{
                text-decoration:none;
                color:#06c;
            }
            [for="Actions_dueDate"],
            [for="Actions_completeDate"] {
                display: inline-block !important;
                margin-right: 2px;
            }
            #header-info > span {
                display: block;
                margin-top: -2px;
                float:left;
            }
            #header-info > .field-value {
                float: left;
                margin-right: 7px;
                height: 21px;
            }
            #header-info .hidden-frame-form input {
                margin-top: 0 !important;
            }
            #actionHeader {
                padding-bottom: 6px;
                border-bottom:1px solid #ccc;
            }
            .due-date-container {
                float: left;
                margin-right: 9px;
            }
            .complete-date-container {
                float: left;
            }
            @media (max-width: 536px) {
                #actionHeader {
                    margin-top: 10px;
                }
                #header-content {
                    height: 40px;
                }
                #header-info {
                    display: block;
                    margin-top: -5px;
                    margin-bottom: 1px;
                    height: 21px;
                }
            }
        </style>
        <title><?php Yii::t('actions', 'Action View Frame'); ?></title>
    </head>
    <body>
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'actions-frameUpdate-form',
            'enableAjaxValidation' => false,
            'action' => 'update?id='.$model->id,
            'htmlOptions' => array (
                'data-action-type' => $model->type,
            ),
        ));
        ?>
        <div class='form'>
        <div id="actionHeader">
            <span id="header-content">
                <span id="header-info" style="font-weight:bold;">
                    <?php
                    if($model->complete != 'Yes'){
                        if(!empty($model->dueDate)){
                            echo "<span class='hidden-frame-form' style='display: none;'>";
                            echo "<div class='due-date-container'>";
                            echo $form->label($model, 'dueDate');
                            if(is_numeric($model->dueDate)){
                                $model->dueDate = Formatter::formatDateTime($model->dueDate);
                            }
                            echo $form->textField($model, 'dueDate');
                            echo "</div>";
                            if (in_array ($model->type, array ('event', 'time', 'call'))) {
                                echo "<div class='complete-date-container'>";
                                echo $form->label($model, 'completeDate');
                                if(is_numeric($model->completeDate)){
                                    $model->completeDate = Formatter::formatDateTime(
                                        $model->completeDate);
                                }
                                echo $form->textField($model, 'completeDate');
                                echo "</div>";
                            }
                            echo "</span>";
                            echo "<div class='field-value'>";
                            echo 
                                "<span style='color:grey'>".
                                    $model->getAttributeLabel ('dueDate', true).':'.
                                " </span>".
                                '<b>'.$model->formatDueDate ().'</b>';
                            echo "</div>";
                            if (in_array ($model->type, array ('event', 'time', 'call'))) {
                                echo "<div class='field-value'>";
                                echo "<span style='color:grey'>".
                                    $model->getAttributeLabel ('completeDate', true).':'.
                                    " </span>".
                                    '<b>'.Formatter::formatDateTime ($model->completeDate).'</b>';
                                echo "</div>";
                            }
                        }elseif(!empty($model->createDate)){
                            echo Yii::t('actions', 'Created:')." ".
                                Formatter::formatLongDateTime($model->createDate).'</b>';
                        }else{
                            echo "&nbsp;";
                        }
                    }else{
                        echo Yii::t('actions', 'Completed {date}', 
                            array('{date}' => Formatter::formatCompleteDate($model->completeDate)));
                    }
                    ?>
                </span>
                <span>
                    <span id="controls">
                        <?php 
                            if(Yii::app()->user->checkAccess(
                                'ActionsComplete', array('X2Model' => $model))){

                                if($model->complete != 'Yes'){ ?>
                                    <div class="control-button icon complete-button"
                                     title="<?php echo Yii::t('actions', 'Complete action'); ?>">
                                    </div>
                                <?php 
                                } else { 
                                ?>
                                    <div class="control-button icon uncomplete-button"
                                     title="<?php echo Yii::t('actions', 'Uncomplete action'); ?>">
                                    </div>
                                <?php 
                                }
                        }
                        if(Yii::app()->user->checkAccess(
                            'ActionsUpdate', array('X2Model' => $model))){ ?>
                            <div class="control-button icon edit-button"
                             title="<?php echo Yii::t('actions', 'Edit action'); ?>"></div>
                        <?php 
                        } 
                        if(Yii::app()->user->checkAccess(
                            'ActionsDelete', array('X2Model' => $model))){ ?>
                            <div class="control-button icon delete-button" alt="[x]"
                             title="<?php echo Yii::t('actions', 'Delete action'); ?>"></div>
                        <?php 
                        } 
                        if(Yii::app()->user->checkAccess(
                            'ActionsToggleSticky', array('X2Model' => $model))){

                            if(!$model->sticky){ ?>
                                <div class="control-button icon sticky-button" 
                                 title="Click to flag this action as sticky."></div>
                            <?php }else{ ?>
                                <div class="control-button icon sticky-button unsticky" 
                                 title="Click to unpin this action."></div>
                        <?php }
                    }
                    ?>
                    </span>
                    <?php if(!$publisher){ ?>
                        <div class="vcrPager">
                        <div class='x2-button-group'>
                    <?php 
                        echo CHtml::link(
                            '<', '#', 
                            array(
                                'class' => 'x2-button vcr-button control-button',
                                'id' => 'back-button')); 
                        echo CHtml::link(
                            '>', '#', 
                            array(
                                'class' => 'x2-button vcr-button control-button',
                                'id' => 'forward-button')); ?>
                        </div>
                        </div>
                    <?php } ?>
                </span>
            </span>
            <br />
        </div>
        <br />
        <div id="content" style="margin-left:5px;margin-right:5px;">
            <div id="actionBody" class="form">
                <?php
                echo "<span class='hidden-frame-form' style='display: none;'><span style='display:inline-block;'>";
                echo $form->labelEx($model, 'subject');
                echo $form->textField($model, 'subject', array('class'=>'x2-xxwide-input'));
                echo "</span></span> ";
                echo "<span class='hidden-frame-form' style='display: none;'><span style='display:inline-block;'>";
                echo $form->labelEx($model, 'priority');
                echo $form->dropDownList($model, 'priority',$model->priorityLabels);
                echo "</span></span>";
                echo "<span class='field-value'>";
                if(!empty($model->subject)){
                    echo "<b>".$model->renderAttribute('subject', false)."</b><br><br>";
                }elseif(!empty($model->type)){
                    echo "<b>".ucfirst($model->type)."</b><br><br>";
                }
                echo "</span>";
                echo "<span class='hidden-frame-form' style='display: none;'>";
                echo $form->labelEx($model, 'actionDescription');
                echo $form->textArea(
                    $model, 'actionDescription', array('class'=>'x2-xxwide-input', 'rows' => (6)));
                echo "</span>";
                echo "<span class='field-value'>";
                echo Formatter::convertLineBreaks($model->actionDescription);
                echo "</span>";
                /* x2prostart */ // used for products actions
                if ($model->type === 'products') {
                    $quote = $model->getActionsDummyQuote ();
                    if (!$quote) $quote = new Quote;
                    echo '<div class="field-value">';
                    $this->renderPartial ('application.modules.quotes.views.quotes._lineItems',
                        array (
                            'model' => $quote,
                            'readOnly' => true,
                            'module' => Yii::app()->getModule ('quotes'),
                            'actionsTab' => true,
                            'products' => Product::activeProducts (),
                            'namespacePrefix' => 'actionIframeproductsTabView'
                        )
                    );
                    echo '</div>';
                    echo '<span class="hidden-frame-form" style="display: none;">';
                    $this->renderPartial ('application.modules.quotes.views.quotes._lineItems',
                        array (
                            'model' => $quote,
                            'readOnly' => false,
                            'module' => Yii::app()->getModule ('quotes'),
                            'actionsTab' => true,
                            'products' => Product::activeProducts (),
                            'namespacePrefix' => 'actionIframeproductsTabEdit',
                            'saveButtonId' => 'action-edit-submit-butotn'
                        )
                    );
                    echo '</span>';
                }
                /* x2proend */ 
                echo '<div>';
                echo CHtml::ajaxSubmitButton(
                    Yii::t('app', 'Submit'), 'update?id='.$model->id, array(), 
                    array(
                        'id' => 'action-edit-submit-button',
                        'style' => 'display:none;float:left;',
                        /* x2prostart */ // used for products actions
                        'onClick' => ($model->type === 'products' ? 
                            'return x2.actionIframeproductsTabEditlineItems.validateAllInputs ();' : ''),
                        /* x2proend */  
                        'class' => 'hidden-frame-form x2-button highlight')); 
                echo CHtml::link(
                    Yii::t('actions', 'View Full Edit Page'), 
                    array(
                        'update', 'id' => $model->id), array('style' => 'float:right;display:none;',
                        'target' => '_parent', 'class' => 'x2-button hidden-frame-form')); 
                echo '</div>';

                ?>
            </div>
            </div>
                <?php $this->endWidget(); ?>
                <?php if(!empty($model->associationType) && is_numeric($model->associationId) && !is_null(X2Model::getAssociationModel($model->associationType, $model->associationId)) && ($publisher == 'false' || !$publisher)){ ?>
                <div id="recordBody" class="form">
                    <?php echo '<div class="page-title"><h3>'.Yii::t('actions', 'Associated Record').'</h3></div>'; ?>
                    <?php
                    if($model->associationType == 'contacts'){
                        $this->renderPartial('application.modules.contacts.views.contacts._detailViewMini', array(
                            'model' => X2Model::model('Contacts')->findByPk($model->associationId),
                            'actionModel' => $model,
                        ));
                    }else{
                        echo ucwords(
                            Events::parseModelName(X2Model::getModelName($model->associationType))).
                            ": <span class='model-link'>".
                                X2Model::getModelLink(
                                    $model->associationId, 
                                    X2Model::getModelName($model->associationType)).
                            "</span>";
                    }
                    ?>
                </div>
<?php } ?>
        </div>
        <div id="actionFooter">
        </div>
    </body>
</html>
<script>
    $(document).on('ready',function(){
        $.datepicker.setDefaults( $.datepicker.regional[ '<?php echo $language; ?>' ] );
        $('#Actions_dueDate').datetimepicker(
            jQuery.extend(
                {showMonthAfterYear:false}, 
                jQuery.datepicker.regional['<?php echo $language; ?>'], 
                {
                    'dateFormat':'<?php echo $dateFormat; ?>',
                    'timeFormat':'<?php echo $timeFormat; ?>',
                    'ampm':<?php echo $amPm; ?>,
                    'changeMonth':true,
                    'changeYear':true
                }
            ));
        $('#Actions_completeDate').datetimepicker(
            jQuery.extend(
                {showMonthAfterYear:false}, 
                jQuery.datepicker.regional['<?php echo $language; ?>'], 
                {
                    'dateFormat':'<?php echo $dateFormat; ?>',
                    'timeFormat':'<?php echo $timeFormat; ?>',
                    'ampm':<?php echo $amPm; ?>,
                    'changeMonth':true,
                    'changeYear':true
                }
            ));
        $('#actions-frameUpdate-form').submit(function(e){
            var data=$(this).serializeArray();
            var id=<?php echo $model->id; ?>;
            e.preventDefault();
            $.ajax({
                url:'update?id='+id,
                type:'POST',
                data:data,
                success:function(data){
                    $('#action-frame', parent.document).attr(
                        'src', $('#action-frame', parent.document).attr('src'));
                    <?php
                    if ($publisher) {
                        echo "window.parent.x2.actionFrames.afterActionUpdate ();";
                    } else {
                        echo "window.parent.$('#history-'+id).replaceWith(data);";
                    }
                    ?>
                }
            });
        });
    });
</script>
