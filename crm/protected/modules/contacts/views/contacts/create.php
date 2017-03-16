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

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

$menuOptions = array(
    'all', 'lists', 'create',
);
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions);

?>
<div class="page-title icon contacts">
	<h2><?php echo Yii::t('contacts','Create {module}', array('{module}'=>Modules::displayName(false))); ?></h2>
</div>
<?php 

echo $this->renderPartial(
    'application.components.views._form', 
    array(
        'model'=>$model,
        'users'=>$users,
        'modelName'=>'contacts',
        'defaultsByRelatedModelType' => array (
            'Accounts' => array (
                'phone' => 'js: $("div.formInputBox #Contacts_phone").val();',
                'website' => 'js: $("div.formInputBox #Contacts_website").val();',
                'assignedTo' => 'js: $("#Contacts_assignedTo_assignedToDropdown").val();'
            )
        )
    )); 

if(isset($_POST['x2ajax'])) {
    echo "<script>\n";
    Yii::app()->clientScript->echoScripts();
    echo "\n</script>";
}
?>
