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

$pieces = explode(", ",$model->editPermissions);
$user = Yii::app()->user->getName();

$action = $this->action->id;
$menuOptions = array(
    'index', 'create', 'createEmail', 'createQuote', 'view', 'exportToHtml', 'permissions',
);
if ($model->checkEditPermission() && $action != 'update')
    $menuOptions[] = 'edit';
if (Yii::app()->user->checkAccess('DocsDelete', array('createdBy' => $model->createdBy)))
    $menuOptions[] = 'delete';
$this->insertMenu($menuOptions, $model);

?>
<div class="page-title icon docs"><h2>
    <?php echo Yii::t('docs','Export {module}', array(
        '{module}' => Modules::displayName(false),
    ));?>
</h2></div>
<div class="form"><div class="span-10">
<?php echo Yii::t('docs','Please right click the link below and select "Save As" to download the document!  Left clicking opens the document in a printer-friendly mode.');?><br /><br />
<?php echo $link; ?>
</div>
</div>
