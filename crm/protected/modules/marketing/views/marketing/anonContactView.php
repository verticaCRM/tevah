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

/* @edition:pla */

Yii::app()->clientScript->registerCss('anonContactRecordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
.show-left-bar .page-title > .x2-button {
    display: none !important;
}

");


$this->pageTitle = Yii::t('marketing','Anonymous Contact {email}', array('{email}'=>$model->email));
$menuOptions = array(
    'all', 'create', 'viewAnon', 'deleteAnon', 'lists', 'newsletters', 'weblead', 'webtracker',
    'anoncontacts', 'fingerprints', 'x2flow', 'email'
);
$this->insertMenu($menuOptions, $model);


if(true) {//!IS_ANDROID && !IS_IPAD){
    echo '
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">';
}
?>
<div class="page-title icon contacts">
    <h2><?php 
    if (isset ($model->email)) {
        echo CHtml::encode($model->email); 
    } else {
        echo CHtml::encode(Yii::t('marketing', 'Anonymous Contact')); 
    }
    ?></h2>
    <?php echo X2Html::emailFormButton(); ?>
</div>

<?php
if(true){ //!IS_ANDROID && !IS_IPAD){
    echo '
    </div>
</div>
        ';
}
?>
<div id="main-column">
    <div id='contacts-detail-view'> 
    <?php 
    $this->renderPartial(
        'application.components.views._detailView', 
        array('model' => $model, 'modelName' => $modelName)); 
    ?>
    </div>
    <?php

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'to' => $model->email,
            'modelName' => $modelName,
            'modelId' => $model->id,
            'targetModel' => $model,
        ),
        'startHidden' => true,
    ));
    ?>
</div>

<div class="history half-width">
    <?php
    $this->widget('Publisher', array(
        'associationType' => 'AnonContact',
        'associationId' => $model->id,
        'assignedTo' => Yii::app()->user->getName(),
        'calendar' => false
            )
    );

    $this->widget('History', array('associationType' => 'AnonContact', 'associationId' => $model->id));
    ?>
</div>

