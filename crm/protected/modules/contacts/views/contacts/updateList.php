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

$authParams['X2Model'] = $model;
$menuOptions = array(
    'all', 'lists', 'create', 'createList', 'viewList', 'editList', 'deleteList',
);
$this->insertMenu($menuOptions, $model, $authParams);

?>

<div class="page-title icon contacts"><h2><?php echo Yii::t('contacts','Edit List'); ?></h2></div>

<?php
echo $this->renderPartial('_listForm', array(
	'model'=>$model,
	'criteriaModels'=>$criteriaModels,
	// 'attributeList'=>$attributeList,
	'comparisonList'=>$comparisonList,
	'users'=>$users,
	'listTypes'=>$listTypes,
	'itemModel'=>$itemModel,
));
?>
