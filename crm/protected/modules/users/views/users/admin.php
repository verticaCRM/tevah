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

$menuOptions = array(
    'feed', 'admin', 'create', 'invite',
);
$this->insertMenu($menuOptions);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('users-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

if(isset($_GET['offset']))
    $offset = $_GET['offset'];
else
    $offset = 'first day of this week';
?>


<div class="search-form" style="display:none">
    <?php
    $this->renderPartial('_search', array(
        'model' => $model,
    ));
    ?>
</div><!-- search-form -->
<div class='flush-grid-view'>
<?php
$this->widget('X2GridViewGeneric', array(
    'id' => 'users-grid',
	'buttons'=>array('clearFilters','autoResize'),
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview/',
    'title' => Yii::t('users', 'Manage {users}', array(
        '{users}' => Modules::displayName(),
    )),
    'template' => '<div class="page-title icon users">{title}'.
        '{buttons}{filterHint}{summary}</div>{items}{pager}',
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>')
        .'<div class="form no-border" style="display:inline;"> '
        .CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(), array(
            'ajax' => array(
                'url' => $this->createUrl('/profile/setResultsPerPage'),
                'data' => 'js:{results:$(this).val()}',
                'complete' => 'function(response) { $.fn.yiiGridView.update("users-grid"); }',
            ),
            'style' => 'margin: 0;',
        ))
        .' </div>',
    'gvSettingsName' => 'users-grid',
    'viewName' => 'admin',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'defaultGvSettings' => array (
        'username' => 90,
        'firstName' => 90,
        'lastName' => 90,
        'login' => 90,
        'emailAddress' => 60
    ),
    'columns' => array(
        array(
            'name' => 'username',
            'value' => 'CHtml::link(CHtml::encode($data->alias),array("/users/users/view","id"=>$data->id))',
            'type' => 'raw',
        ),
        array (
            'name' => 'firstName',
        ),
        array (
            'name' => 'lastName',
        ),
        array(
            'name' => 'login',
            'header' => Yii::t('users', 'Last Login'),
            'value' => '$data->login?date("Y-m-d",$data->login):"n/a"',
            'type' => 'raw',
        ),
//        array(
//            'header' => '<b>'.Yii::t('users', 'Records Updated').'</b>',
//            'value' => '(Changelog::model()->countByAttributes(array(),"changedBy=\"$data->username\" AND timestamp > '.strtotime("$offset").'"))',
//            'type' => 'raw',
//        ),
        array(
            'header' => Yii::t('app', 'Active'),
            'value' => '$data->status? Yii::t("app","Yes") : Yii::t("app","No")',
            'type' => 'raw',
            'headerHtmlOptions' => array('style' => 'width:60px;')
        ),
        array (
            'name' => 'emailAddress'
        )
    //'cellPhone',
    //'homePhone',
    //'address',
    //'officePhone',
    //'emailAddress',
    //'status',
    ),
));
?>
</div>
<?php if($count > 0){ ?>
    <br />
    <h2><?php echo Yii::t('users', "Invited {users}", array('{users}'=>Modules::displayName())); ?></h2>
    <div class="form">
        <b><?php echo Yii::t('users', "{n} {user}(s) have been invited but have not yet completed registration.", array(
            '{n}' => $count,
            '{user}' => Modules::displayName(false),
        )); ?></b>
        <br /><br />
        <?php echo Yii::t('users', "To delete all {users} who have not completed their invite, "
            ."click the button below.", array(
                '{users}'=>Modules::displayName()
        )); ?>
        <br /><br />
        <?php echo CHtml::link(
            Yii::t('users', 'Delete Unregistered'), '#', array(
                'class' => 'x2-button',
                'submit' => 'deleteTemporary',
                'confirm' => Yii::t('users', 'Are you sure you want to delete these {users}?', array(
                    '{users}'=>Modules::displayName()))
        )); ?>
    </div>
<?php } ?>
