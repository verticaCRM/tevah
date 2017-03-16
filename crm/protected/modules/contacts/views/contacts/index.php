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
$modTitles = array(
    'contact' => Modules::displayName(false),
    'contacts' => Modules::displayName(),
);

$heading = '';

$opportunityModule = Modules::model()->findByAttributes(array('name' => 'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name' => 'accounts'));

$menuOptions = array(
    'all', 'lists', 'create', 'import', 'export', 'map', 'savedMaps',
);
if ($this->route == 'contacts/contacts/index') {
    $heading = Yii::t('contacts', 'All {module}', array('{module}' => $modTitles['contacts']));
    
    $dataProvider = $model->searchAll();
    //unset($menuItems[0]['url']);
    //unset($menuItems[4]); // View List
} elseif ($this->route == 'contacts/contacts/myContacts') {
    $heading = Yii::t('contacts', 'My {module}', array('{module}' => $modTitles['contacts']));
    $dataProvider = $model->searchMyContacts();
    $menuOptions = array_merge($menuOptions, array('createList', 'viewList'));
} elseif ($this->route == 'contacts/contacts/newContacts') {
    $heading = Yii::t('contacts', 'Today\'s {module}', array('{module}' => $modTitles['contacts']));
    $dataProvider = $model->searchNewContacts();
    $menuOptions = array_merge($menuOptions, array('createList', 'viewList'));
}
if ($opportunityModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions);

Yii::app()->clientScript->registerScript('search', "
/*$('.search-button').unbind('click').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});*/

$('#content').on('mouseup','#contacts-grid a',function(e) {
	document.cookie = 'vcr-list=" . $this->getAction()->getId() . "; expires=0; path=/';
});
", CClientScript::POS_READY);
?>


<div class="search-form" style="display:none">
    <?php
    $this->renderPartial('_search', array(
        'model' => $model,
        'users' => User::getNames(),
    ));
    ?>
</div><!-- search-form -->
<form>
    <?php
    $this->widget('X2GridView', array(
        'id' => 'contacts-grid',
        'enableQtips' => true,
        'qtipManager' => array(
            'X2GridViewQtipManager',
            'loadingText' => addslashes(Yii::t('app', 'loading...')),
            'qtipSelector' => ".contact-name"
        ),
        'title' => $heading,
        'enableSelectAllOnAllPages' => true,
        'buttons' => array('advancedSearch', 'clearFilters', 'columnSelector', 'autoResize', 'showHidden'),
        'template' =>
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">' .
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">' .
        '<div id="x2-gridview-page-title" ' .
        'class="page-title icon contacts x2-gridview-fixed-title">' .
        '{title}{buttons}{filterHint}{massActionButtons}{summary}{topPager}' .
        '{items}{pager}',
        'fixedHeader' => true,
        'dataProvider' => $dataProvider,
        // 'enableSorting'=>false,
        // 'model'=>$model,
        'filter' => $model,
        'pager' => array('class' => 'CLinkPager', 'maxButtonCount' => 10),
        // 'columns'=>$columns,
        'modelName' => 'Contacts',
        'viewName' => 'contacts',
        // 'columnSelectorId'=>'contacts-column-selector',
        'defaultGvSettings' => array(
            'gvCheckbox' => 30,
            'name' => 125,
            'email' => 165,
            'leadSource' => 83,
            'leadstatus' => 91,
            'phone' => 107,
            'lastActivity' => 78,
            'gvControls' => 73,
        ),
        'specialColumns' => array(
            'name' => array(
                'name' => 'name',
                'header' => Yii::t('contacts', 'Name'),
                'value' => '$data->link',
                'type' => 'raw',
            ),
        ),
        'massActions' => array(
            /* x2prostart */'MassDelete', 'MassTag', 'MassUpdateFields', 'MergeRecords',
            /* x2proend */ 'MassAddToList', 'NewListFromSelection'
        ),
        'enableControls' => true,
        'enableTags' => true,
        'fullscreen' => true,
    ));
    ?>

</form>
