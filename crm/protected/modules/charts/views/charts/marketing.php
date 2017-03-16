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


<div class="page-title icon charts"><h2>
    <?php
        echo Yii::t('app', '{marketing} Dashboard', array(
            '{marketing}' => Modules::displayName(true, "Marketing"),
        )); ?>
&nbsp;&nbsp;</h2></div>
<div class="form">
    <br>

    <?php
    $form = $this->beginWidget('CActiveForm', array('id' => 'chart'));
    $range = $model->dateRange;
    $userName = Yii::app()->user->getName();
    $filters = array(
        "leadDate > (unix_timestamp() - ($range*24*3600))",
        "((visibility = 0 AND assignedTo='$userName') OR (visibility = 1) OR (visibility = 2 and assignedTo='$userName'))"
    );
    ?>
    <div class="x2-chart-container-controls">
        <div class="x2-chart-control">
            <div class="row">
                <?php 
                echo $form->label(
                    $model, 'dateRange', 
                    array(
                        'label' => Yii::t('charts', 'Select {leads} received in the last', array(
                            '{leads}' => strtolower(Modules::displayName(true, "X2Leads")),
                        )).
                            ' &nbsp;&nbsp;&nbsp;&nbsp;'
                    )
                ); 
                echo $form->dropDownList(
                    $model, 'dateRange', array(
                    10 => Yii::t('charts', '{n} days',array('{n}'=>'10')),
                    30 => Yii::t('charts', '{n} days',array('{n}'=>'30')),
                    60 => Yii::t('charts', '{n} days',array('{n}'=>'60')),
                    90 => Yii::t('charts', '{n} days',array('{n}'=>'90')),
                    120 => Yii::t('charts', '{n} days',array('{n}'=>'120')),
                    360 => Yii::t('charts', '{n} days',array('{n}'=>'360'))
                ));
                ?>
                <a onclick="x2.forms.submitForm('chart');" href="#" class="x2-button"><span><?php echo Yii::t('app','Go'); ?></span></a>
            </div>
        </div>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large-pie">
        <?php
        $range = $model->dateRange;
        $chart1 = $this->widget('X2PieChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 3,
                'x-axis' => array('column' => 'leadSource')),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Source', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'insideGrid')
            )
                ));
        ?>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large-pie">
        <?php
        $this->widget('X2PieChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 1,
                'x-axis' => array('column' => 'leadType')
            ),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Type', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'legend' => array('show' => true, 'location' => 'ne', 'placement' => 'insideGrid')
            )
        ));
        ?>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large">
        <?php
        $this->widget('X2StackedBarChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 2,
                'x-axis' => array('column' => 'assignedToName'),
                'x-axis1' => array('column' => 'leadStatus')
            ),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Distribution and Status', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'axes' => array(
                    'xaxis' => array('label' => Yii::t('charts', 'Assigned To')),
                    'yaxis' => array('label' => Yii::t('charts', 'Count'))
                )
            )
        ));
        ?>
    </div>
    <p class="x2-chart-separator"/>
    <div class="x2-chart-container-center-large">
        <?php
        $this->widget('X2StackedBarChart', array(
            'model' => $sqlView,
            'options' => array(
                'other-threshold' => 0,
                'orderby' => 'leadDate asc',
                'x-axis' => array('column' => "from_unixtime(leadDate,'%b-%Y')"),
                'x-axis1' => array('column' => 'dealStatus')
            ),
            'filters' => $filters,
            'chartOptions' => array(
                'title' => Yii::t('charts', '{lead} Conversion', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'axes' => array(
                    'xaxis' => array('label' => Yii::t('charts', '{lead} Received', array(
                        '{lead}' => Modules::displayName(false, "X2Leads"),
                    ))),
                    'yaxis' => array('label' => Yii::t('charts', 'Count'))
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

