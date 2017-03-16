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

Yii::app()->clientScript->registerScriptFile($this->module->getAssetsUrl ().'/js/chartManager.js',
    CClientScript::POS_BEGIN);

require_once("protected/modules/charts/chartsConfig.php");

$menuOptions = array(
    'leadVolume', 'marketing', 'pipeline', 'opportunities',
);
$this->insertMenu($menuOptions);

?>


<div class="page-title icon charts"><h2><?php echo Yii::t('charts', 'Pipeline Dashboard'); ?>&nbsp;&nbsp;</h2></div>
<div class="form">
	<br>

	<?php
	$form = $this->beginWidget('CActiveForm', array('id' => 'chart'));
	$range = $model->dateRange;
	$assignedTo = $model->assignedTo;
	$confidence = $model->confidence;
	$userName = Yii::app()->user->getName();
	$slice = $model->limitTo;
	$filters = array(
		"closeDate > unix_timestamp()",
		"closeDate < (unix_timestamp() + ($range*24*3600))",
		"dealstatus = 'Working'",
		"((visibility = 0 AND assignedTo='$userName') OR (visibility = 1
		) OR (visibility = 2 and assignedTo='$userName'))"
	);
	if (strcmp($assignedTo,'0') != 0)
		$filters[] = "(assignedTo = '$assignedTo')";
	if ($confidence >= 0)
		$filters[] = "(confidence = $confidence)";
	?>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php 
                echo $form->label(
                    $model, 'dateRange', 
                    array('label' => Yii::t('charts', 'Select deals closing in').
                        '&nbsp;&nbsp;&nbsp;&nbsp;')); 
				echo $form->dropDownList($model, 'dateRange', array(
					5 => Yii::t('charts', '{n} days',array('{n}'=>'5')),
					10 => Yii::t('charts', '{n} days',array('{n}'=>'10')),
					15 => Yii::t('charts', '{n} days',array('{n}'=>'15')),
					20 => Yii::t('charts', '{n} days',array('{n}'=>'20')),
					30 => Yii::t('charts', '{n} days',array('{n}'=>'30')),
					60 => Yii::t('charts', '{n} days',array('{n}'=>'60')),
					90 => Yii::t('charts', '{n} days',array('{n}'=>'90'))
                ));
				?>
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'confidence', array('label' => Yii::t('charts', 'Limit to confidence is').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->dropDownList($model, 'confidence', array(
					-1 => Yii::t('charts', 'Any'),
					0 => Yii::t('charts', 'None'),
					1 => Yii::t('charts', 'Low'),
					2 => Yii::t('charts', 'Growing'),
					3 => Yii::t('charts', 'Forecast'),
					4 => Yii::t('charts', 'Committed'),
					5 => Yii::t('charts', 'In The Bag')
						)
				)
				?>
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'limitTo', array('label' => Yii::t('charts', 'Limit selected deals to').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->dropDownList($model, 'limitTo', array(
					1 => Yii::t('charts', 'Smallest'),
					3 => Yii::t('charts', 'Largest'),
					2 => Yii::t('charts', 'Others'),
					0 => Yii::t('charts', 'All')
						)
				)
				?>
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<div class="x2-chart-container-controls">
		<div class="x2-chart-control">
			<div class="row">
				<?php echo $form->label($model, 'assignedTo', array('label' => Yii::t('charts', 'Select deals assigned to').'&nbsp;&nbsp;&nbsp;&nbsp;')); ?>
				<?php
				echo $form->dropDownList($model, 'assignedTo', array_merge(array('0' => 'All'), Groups::getNames(), User::getNames()));
				?>
				<a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
			</div>
		</div>
	</div>
	<p class="x2-chart-separator"/>
	<div class="x2-chart-container-center-large">
		<?php
		$this->widget('X2BubbleChart', array(
			'model' => $sqlView,
			'options' => array(
				'other-threshold' => 2,
				'statistic' => 'none',
				'orderby' => 'dealValue asc',
				'slice' => array('part' => $slice),
				'x-axis' => array('column' => "round((closeDate-unix_timestamp())/(24*3600))"),
				'y-axis' => array('column' => 'round(dealValue)'),
				'r-axis' => array(
					'column' => 'confidence',
					'label' => "concat('$',format(dealValue,2))")
			),
			'filters' => $filters,
			'chartOptions' => array(
				'title' => Yii::t('charts', 'Deals'),
				'axes' => array(
					'yaxis' => array(
						'label'=>Yii::t('charts','Value'),
						'tickOptions' => array(
							'formatString'=>'$%d'
						)
					),
					'xaxis' => array(
						'label'=>Yii::t('charts','Days To Close'),
					)
				)
			)
		));
		?>
	</div>
	<p class="x2-chart-separator"/>
<?php
$form = $this->endWidget();
?>
</div>

