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
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(
    Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css');
Yii::app()->clientScript->registerCss('manageRolesCss',"
#content {
    border: none; background: none; 
}
.multiselect {
	width: 460px;
	height: 200px;
}
#switcher {
	margin-top: 20px;
}

#roles-grid-container {
    padding-bottom: 5px;
}

#set-session-timeout-row {
    margin: 5px 0;
}


");

?>
<div id='roles-grid-container' class='x2-layout-island'>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'roles-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
	'template'=> '<div class="page-title"><h2>'.
        Yii::t('admin','Role List').'</h2><div class="title-bar">'
		.'{summary}</div></div>{items}{pager}',
		'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		'name',
        array(
            'name'=>'timeout',
            'value'=>'isset($data->timeout)? $data->timeout / 60 : null',
            'header'=>Yii::t('admin', 'Session Timeout')
        ),
	),
)); 
X2Html::getFlashes(); ?>
<br>
<a style='margin-left: 5px;' href="#" onclick="$('#addRole').toggle();$('#deleteRole').hide();$('#editRole').hide();$('#exception').hide();" class="x2-button">Add Role</a>
<a href="#" onclick="$('#deleteRole').toggle();$('#addRole').hide();$('#editRole').hide();$('#exception').hide();" class="x2-button">Delete Role</a>
<a href="#" onclick="$('#editRole').toggle();$('#addRole').hide();$('#deleteRole').hide();$('#exception').hide();" class="x2-button">Edit Role</a>
<a href="#" onclick="$('#exception').toggle();$('#addRole').hide();$('#deleteRole').hide();$('#editRole').hide();" class="x2-button">Add Exception</a>
<br>
</div>
<br>
<div id="addRole"<?php if(!$model->hasErrors()) echo ' style="display:none;"';?> class='x2-layout-island'>
<?php $this->renderPartial('roleEditor',array(
    'model'=>$model,
)); ?>
</div>

<div id="deleteRole" style="display:none;" class='x2-layout-island'>
<?php $this->renderPartial('deleteRole',array(
    'roles'=>$roles,
)); ?>
</div>

<div id="editRole" style="display:none;" class='x2-layout-island'>
<?php $this->renderPartial('editRole',array(
    'model'=>$model,
)); ?>
</div>
<div id="exception" style="display:none;" class='x2-layout-island'>
<?php $this->renderPartial('roleException',array(
    'model'=>$model,
    'workflows'=>$workflows,
)); ?>
</div>
