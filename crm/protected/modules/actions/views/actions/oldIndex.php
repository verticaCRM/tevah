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

if (isset ($showActions)) {
    $this->showActions = $showActions;
    Yii::app()->params->profile->showActions = $this->showActions;
    Yii::app()->params->profile->save ();
} else {
    $this->showActions = Yii::app()->params->profile->showActions;
}

// if user hasn't saved a type of action to show, show uncomple actions by default
if(!$this->showActions) 
    $this->showActions = 'uncomplete';
if($this->showActions == 'uncomplete' || $this->showActions == 'overdue')
	$model->complete = 'No';
else if ($this->showActions == 'complete')
	$model->complete = 'Yes';
else
	$model->complete = '';


$menuOptions = array(
    'todays', 'my', 'everyones', 'create', 'import', 'export',
);
if($this->route === 'actions/actions/index') {
	$heading = Yii::t('actions','Today\'s {module}', array('{module}'=>Modules::displayName()));
	$dataProvider=$model->searchIndex();
} elseif($this->route === 'actions/actions/viewAll') {
	$heading = Yii::t('actions','All My {module}', array('{module}'=>Modules::displayName()));
	$dataProvider=$model->searchAll();
} else {
	$heading = Yii::t('actions','Everyone\'s {module}', array('{module}'=>Modules::displayName()));
	$dataProvider=$model->searchAllGroup();
}
$this->insertMenu($menuOptions);


// functions for completeing/uncompleting multiple selected actions
Yii::app()->clientScript->registerScript('oldActionsIndexScript', "
x2.actionFrames.afterActionUpdate = (function () {
    var fn = x2.actionFrames.afterActionUpdate;
    return function () {
        fn ();
        $('#actions-grid').yiiGridView ('update');
    };
}) ();
function toggleShowActions() {
    var show = $('#dropdown-show-actions').val(); // value of dropdown (which actions to show)
    $.post(
        ".json_encode(Yii::app()->controller->createUrl('/actions/actions/saveShowActions')).",
        {ShowActions: show}, function() {
            $.fn.yiiGridView.update('actions-grid', {
                data: $.param($('#actions-grid input[name=\"Actions[complete]\"]'))
            });
        }
    );
}
",CClientScript::POS_END);

?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php
$this->widget('X2GridView', array(
	'id'=>'actions-grid',
    'title'=>$heading,
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
    'enableQtips' => true,
    'qtipManager' => array (
        'X2GridViewQtipManager',
        'loadingText'=> addslashes(Yii::t('app','loading...')),
        'qtipSelector' => ".contact-name"
    ),
    'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
    'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon actions x2-gridview-fixed-title">'.
        '{title}{buttons}'.
        CHtml::link(
            Yii::t('actions','Switch to List'),
            array('index','toggleView'=>1),
            array('class'=>'x2-button')
        ).'{filterHint}'./* x2prostart */'{massActionButtons}'./* x2proend */'{summary}{topPager}'.
        '{items}{pager}',
    'fixedHeader' => true,
	'dataProvider'=>$dataProvider,
    'massActions' => array (
        /* x2prostart */'MassDelete', 'MassTag', 'MassUpdateFields',/* x2proend */
        'MassCompleteAction', 'MassUncompleteAction'
    ),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Actions',
	'viewName'=>'actions',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'gvCheckbox' => 30,
		'actionDescription' => 140,
		'associationName' => 165,
		'assignedTo' => 105,
		'completedBy' => 86,
		'createDate' => 79,
		'dueDate' => 77,
		'lastUpdated' => 79,
	),
	'specialColumns'=>array(
		'actionDescription'=>array(
            'header'=>Yii::t('actions','{action} Description', array('{action}'=>Modules::displayName(false))),
			'name'=>'actionDescription',
			'value'=>
                'CHtml::link(
                    ($data->actionDescription === "" ? Yii::t("actions", "View {action}", array("{action}"=>Modules::displayName(false, "Actions"))) :
                        (($data->type=="attachment") ? 
                            Media::attachmentActionText($data->actionDescription) : 
                            CHtml::encode(Formatter::trimText($data->actionDescription)))),
                    array("view","id"=>$data->id))',
			'type'=>'raw',
            'filter' => false,
            'sortable' => false,
		),
		'associationName'=>array(
			'name'=>'associationName',
			'header'=>Yii::t('actions','Association Name'),
			'value'=>
                'strcasecmp($data->associationName,"None") == 0 ? 
                    Yii::t("app","None") : 
                    CHtml::link(
                        $data->associationName,
                        array("/".$data->associationType . (($data->associationType === "product") ? "s" : "") .
                              "/".$data->associationType . (($data->associationType === "product") ? "s" : "") .
                              "/".$data->associationId),
                        array("class"=>($data->associationType=="contacts" ? 
                            "contact-name" : null)))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'fullscreen'=>true,
    'enableSelectAllOnAllPages' => false,
));
