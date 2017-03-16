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
$this->setPageTitle(CHtml::encode($model->name));
$themeUrl = Yii::app()->theme->getBaseUrl();


Yii::app()->getClientScript()->registerScript('docIframeAutoExpand','
$("#docIframe").load(function() {
	$(this).height($(this).contents().height());
});
$(window).resize(function() {
	$("#docIframe").height($("#docIframe").height(650).contents().height());
});
',CClientScript::POS_READY);

$title = Yii::t('docs','Document:');
$this->renderPartial('_docPageHeader',compact('title','model'));
?>

<iframe src="<?php echo $this->createUrl('/docs/docs/fullView',array('id'=>$model->id)); ?>" id="docIframe" frameBorder="0" scrolling="no" height="650" width="100%" style="background:#fff;overflow:hidden;"></iframe>
