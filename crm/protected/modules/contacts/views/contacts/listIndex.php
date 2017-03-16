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

$heading = Yii::t('contacts','{module} Lists', array('{module}'=>Modules::displayName(false))); 
$this->pageTitle = $heading;

$opportunityModule = Modules::model()->findByAttributes(array('name'=>'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

$menuOptions = array(
    'all', 'lists', 'create', 'createList',
);
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
} ?>

<?php
$attributeLabels = CActiveRecord::model('X2List')->attributeLabels();

$this->widget('X2GridViewGeneric', array(
	'id'=>'lists-grid',
	//'enableSorting'=>tru,
	//'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	//'htmlOptions'=>array('class'=>'grid-view contact-lists fullscreen'),
	'template'=> '<div class="page-title icon contacts"><h2>'.$heading.'</h2>{summary}</div>{items}{pager}',
	'summaryText' => Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>')
		. '<div class="form no-border" style="display:inline;"> '
		. CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(),Profile::getPossibleResultsPerPage(),array(
				'ajax' => array(
					'url' => $this->createUrl('/profile/setResultsPerPage'),
					'data' => 'js:{results:$(this).val()}',
					'complete' => 'function(response) { $.fn.yiiGridView.update("lists-grid"); }',
				),
				// 'style' => 'margin: 0;',
			))
		. ' </div>',
	'dataProvider'=>$contactLists,
    'gvSettingsName' => 'listsGrid',
	// 'filter'=>$model,
	//'rowCssClassExpression'=>'$data["id"]==="all"?"bold":"$this->rowCssClass[$row%"',
	'rowCssClassExpression'=>'$this->rowCssClass[$row%2].($data["id"]==="all"?" bold":"")',
    'defaultGvSettings' => array (
        'name' => 180,
        'type' => 180,
        'assignedTo' => 180,
        'count' => 180,
        'gvControls' => 75,
    ),
	'columns'=>array(
		array(
			'name'=>'name',
			'header'=>$attributeLabels['name'],
			'type'=>'raw',
			'value'=>'CHtml::link($data["name"],X2List::getRoute($data["id"]))',
		),
		array(
			'name'=>'type',
			'header'=>$attributeLabels['type'],
			'type'=>'raw',
			'value'=>'$data["type"]=="static"? Yii::t("contacts","Static") : Yii::t("contacts","Dynamic")',
		),
		array(
			'name'=>'assignedTo',
			'header'=>$attributeLabels['assignedTo'],
			'type'=>'raw',
			'value'=>'User::getUserLinks($data["assignedTo"])',
		),
		array(
			'name'=>'count',
			'header'=>$attributeLabels['count'],
			'headerHtmlOptions'=>array('class'=>'contact-count'),
			'htmlOptions'=>array('class'=>'contact-count'),
			'value'=>'Yii::app()->locale->numberFormatter->formatDecimal($data["count"])',
		),
        array (
            'id' => 'C_gvControls',
            'class' => 'X2ButtonColumn',
            'header' => Yii::t('app','Tools'),
            'updateButtonUrl' => 
                "Yii::app()->createUrl ('/contacts/updateList', array ('id' => \$data['id']))",
            'cssClassExpression' =>
                "!is_numeric (\$data['id']) ? 'hide-edit-delete-buttons' : ''",
            'viewButtonUrl' => 
                "X2List::getRoute (\$data['id'])",
            'deleteButtonUrl' => 
                "Yii::app()->createUrl ('/contacts/deleteList', array ('id' => \$data['id']))",
        ),
	),
)); ?>
<div class="form">
<?php echo CHtml::link('<span>'.Yii::t('app','New List').'</span>',array('/contacts/contacts/createList'),array('class'=>'x2-button')); ?>
</div>
