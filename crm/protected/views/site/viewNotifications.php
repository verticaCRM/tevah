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

Yii::app()->clientScript->registerCss('viewNotificationsCss',"

#clear-all-button {
    margin-top: 4px;
}

");
?>
<div class="flush-grid-view">
<?php

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'notifs-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'dataProvider'=>$dataProvider,
	'template'=>'<div class="page-title"><h2>'.Yii::t('app','Notifications').'</h2>'
		.CHtml::link(Yii::t('app','Clear All'),'#',array(
			'class'=>'x2-button right',
            'id' => 'clear-all-button',
			'submit'=>array('/notifications/deleteAll'),
			'confirm'=>Yii::t('app','Permanently delete all notifications?'
		)))
		.'<div class="title-bar right">{summary}</div></div>{items}{pager}',
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>')
    .'<div class="form no-border" style="display:inline;"> '
    .CHtml::dropDownList(
        'resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(),
        array(
            'ajax' => array(
                'url' => Yii::app()->controller->createUrl('/profile/setResultsPerPage'),
                'data' => 'js:{results:$(this).val()}',
                'complete' => 'function(response) { 
                    $.fn.yiiGridView.update("notifs-grid"); 
                }',
            ),
            'style' => 'margin: 0;',
        )
    ).'</div>',
	'columns'=>array(
		array(
			// 'name'=>'text',
			'header'=>Yii::t('actions','Notification'),
			'value'=>'$data->getMessage()',
			'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:70%'),
		),
		array(
			'name'=>'createDate',
			'header'=>Yii::t('actions','Time'),
			'value'=>'date("Y-m-d",$data->createDate)." ".date("g:i A",$data->createDate)',
			'type'=>'raw',
		),
		array(
			'class'=>'X2ButtonColumn',
			'template'=>'{delete}',
			'deleteButtonUrl'=>'Yii::app()->controller->createUrl("/notifications/delete",array("id"=>$data->id))',
			'afterDelete'=>'function(link,success,data){
                var match = $(link).attr ("href").match (/[0-9]+$/);
                if (match !== null) x2.Notifs.triggerNotifRemoval (match[0]);
            }',
			'deleteConfirmation'=>false,
			'headerHtmlOptions'=>array('style'=>'width:40px'),
		 ),
	),
	'rowCssClassExpression'=>'$data->viewed? "" : "unviewed"',
    'pager' => array (
        'class' => 'CLinkPager', 
        'header' => '',
        'firstPageCssClass' => '',
        'lastPageCssClass' => '',
        'prevPageLabel' => '<',
        'nextPageLabel' => '>',
        'firstPageLabel' => '<<',
        'lastPageLabel' => '>>',
    ),
));

?>
</div>
<?php

foreach($dataProvider->getData() as $notif) {
	if(!$notif->viewed) {
		$notif->viewed = true;
		$notif->update();
	}
}
unset($notif);

?>
