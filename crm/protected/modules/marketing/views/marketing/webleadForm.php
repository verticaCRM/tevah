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

$this->pageTitle = Yii::t('marketing','Web Lead Form');
$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'weblead', 'webtracker', 'x2flow',
);
/* x2plastart */
$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);
/* x2plaend */
$this->insertMenu($menuOptions);

?>
<div id='content-tray'>
<div class="page-title icon marketing"><h2><?php echo Yii::t('marketing','Web Lead Form'); ?></h2></div>
<div class="form">
<?php echo Yii::t('marketing','Create a public form to receive new {module}.', array('{module}'=>lcfirst(Modules::displayName(true, "Contacts")))),'<br>',
	Yii::t('marketing','If no lead routing has been configured, all new {module} will be assigned to "Anyone".', array('{module}'=>lcfirst(Modules::displayName(false, "Contacts")))); ?>
</div>
<div class="form">
    <?php echo Yii::t('marketing','If you want to keep your current HTML forms but still get web leads into X2, please see the wiki article located here: {link}',array(
        '{link}' => CHtml::link(Yii::t('marketing','Web Lead API'),'http://wiki.x2engine.com/wiki/Web_Lead_API_(new)', array('target'=>'_blank')),
    )) ?>
</div>
<?php
$this->renderPartial ('application.components.views._createWebForm',
    array(
        'forms'=>$forms,
        'webFormType'=>'weblead'
    )
);
?>
</div>
