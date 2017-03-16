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

$listId = Yii::app()->user->getState('vcr-list');
if(empty($listId))
	$listId = 'index';

/*Yii::app()->clientScript->registerScript('vcrListCookie', "
// $('#content').on('mouseup','#contacts-grid a',function(e) {
	// document.cookie = 'vcr-list=".$listId."; expires=0; path=/';
// });
",CClientScript::POS_READY);*/

$vcrControls = array();
$tagFlag=false;
//listId should be either a number (for a list), 'index', or 'admin'
//convert numbers to list/# for uniform url path
if(is_numeric($listId)){
	$path = 'list/' . $listId;
}elseif(strpos($listId,'#')===0){
    $tagFlag=true;
	$path = $listId;
}else{
    $path = $listId;
}


//try to get the saved sort and filters from the session if applicable
/* x2tempstart */
// Violates abstraction by depending on implementation details of SmartDataProviderBehavior and
// ERememberFiltersBehavior. 
$searchModel = new Contacts('search', 'contacts/contacts/'.$path.'Contacts');
$order = $searchModel->asa ('ERememberFiltersBehavior')->getSetting ('sort');
/* x2tempend */

//convert session var to sql
$order = preg_replace('/\.desc$/', ' DESC', $order);

// ensure that order attribute is valid
$orderAttr = preg_replace ('/ DESC$/', '', $order);
if (!is_string ($orderAttr) || !Contacts::model ()->hasAttribute (trim ($orderAttr))) {
    $order = '';
}

//look up all ids of the list we are currently viewing
//find position of model in the list

$moduleTitle = Modules::displayName();

// decide which data provider to use
if(is_numeric($listId)) {
	$list = X2Model::model('X2List')->findByPk($listId);
    if(isset($list)){
        $listLink = CHtml::link($list->name,array('/contacts/contacts/list','id'=>$listId));
        $vcrDataProvider = $searchModel->searchList($listId);
    }else{
        // default to All Contacts
        $listLink = CHtml::link(
            Yii::t('contacts','All {module}', array('{module}'=>$moduleTitle)),
            array('/contact/contacts/index')
        );
        $vcrDataProvider = $searchModel->searchAll();
    }
} elseif($listId=='myContacts') {
    $listLink = CHtml::link(
        Yii::t('contacts','My {module}', array('{module}'=>$moduleTitle)),
        array('/contacts/contacts/myContacts'));
	$vcrDataProvider = $searchModel->searchMyContacts();
} elseif($listId=='newContacts') {
    $listLink = CHtml::link(
        Yii::t('contacts','New {module}', array('{module}'=>$moduleTitle)),
        array('/contacts/contacts/newContacts'));
	$vcrDataProvider = $searchModel->searchNewContacts();
} elseif($tagFlag){
    $listLink = CHtml::link(
        Yii::t('contacts','Tag Search'),array('/search/search','term'=>$listId));
    $_GET['tagField']=$listId;
    $vcrDataProvider = $searchModel->searchAll();
} else {
    $listLink = CHtml::link(
        Yii::t('contacts','All {module}', array('{module}'=>$moduleTitle)),
        array('/contacts/contacts/index'));	// default to All Contacts
	$vcrDataProvider = $searchModel->searchAll();
}


if(empty($order) && !$tagFlag)
	$order = $vcrDataProvider->sort->getOrderBy();
elseif(empty($order) && $tagFlag)
	$order = $vcrDataProvider->criteria->order;

if(!empty($order))
	$vcrDataProvider->criteria->order = $order;

// run SQL to get VCR links
$vcrData = X2List::getVcrLinks($vcrDataProvider,$model->id);

// if this contact isn't on the list, default to All Contacts (unless we already tried that)
if($vcrData === false && $listId !== 'index') {
	$listLink = CHtml::link(Yii::t('contacts','All {module}', array('{module}'=>$moduleTitle)),array('/contacts/'.$path));
	$vcrDataProvider = $searchModel->searchAll();

	if(empty($order))
		$order = $vcrDataProvider->sort->getOrderBy();
	if(!empty($order))
		$vcrDataProvider->criteria->order = $order;


	$vcrData = X2List::getVcrLinks($vcrDataProvider,$model->id);
}

if(is_array($vcrData) && count($vcrData)) {


?>
<div class="vcrPager">
	<div class="summary">
		<?php 
        if(isset($listLink)) echo $listLink; 
		echo Yii::t(
            'contacts','<b>{m}</b> of <b>{n}</b>',
            array('{m}'=>$vcrData['index'],'{n}'=>$vcrData['count'])
        ); ?>
	</div>
    <div class='x2-button-group'>
	<?php
	//echo CHtml::tag('ul',array('class'=>'vcrPager'),$vcrData['prev']."\n".$vcrData['next']);
	if(isset($vcrData['prev']))
		echo $vcrData['prev'];
	if(isset($vcrData['next']))
		echo $vcrData['next'];
	?>
    </div>
</div>
<?php

}
