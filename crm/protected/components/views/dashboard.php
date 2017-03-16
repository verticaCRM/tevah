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

/* @edition:pro */

$hiddenGraphs = $this->getHiddenGraphs ();
?>

<div class='chart-dashboard'>
    <!-- Dashboard -->
        <div class='toolbar'>
        <h2> Charts on this report
        </h2>
            <span id="minimize-dashboard" >
                <i class='fa fa-caret-down fa-lg'></i>
            </span>
            <span id="hidden-data-widgets-button" class="x2-button">
                <i class='fa fa-toggle-down'></i>
                <?php
                echo CHtml::encode (Yii::t('app', 'Hidden Charts'))   
                ?>
            </span>
            <span id="create-chart-button" class="x2-button">
                <i class='fa fa-plus'></i>
                <?php
                echo CHtml::encode (Yii::t('app', 'Create Chart'))   
                ?>
            </span>
            <span id="refresh-charts-button" class="x2-button">
                <i class='fa fa-refresh'></i>
                <?php
                echo CHtml::encode (Yii::t('app', 'Refresh Charts'))   
                ?>
            </span>
            <span id="save-chart-message" class='x2-button'>
                <?php echo Yii::t('charts', 'Save report to create a chart')?>
            </span>

            <div class='clear'></div>
        </div>

        <!-- <span id="dashboard-fullscreen-button" class="x2-button"> -->
        <!-- Fullscreen -->
        <!-- </span> -->
    <!-- </div> -->

    <div id="x2-hidden-data-widgets-menu-container" class="popup-dropdown-menu">
        <ul id="x2-hidden-data-widgets-menu" class="closed" >
        <?php 
            echo '<span class="no-hidden-data-widgets-text">'.Yii::t('charts','No Hidden Charts')."</span>";
            foreach($hiddenGraphs as $name => $widget) {
                echo "<li><span class='x2-hidden-widgets-menu-item data-widget' id='$name'>
                    $widget[label]</span></li>";
            } 
        ?>
        </ul>
    </div>

    <?php 
        if (!$this->report) {
            echo '<div id="report-list">';
            echo $this->getReportList();
            echo '</div>';
        } 
    ?>

    <div class="dashboard-inner">

    <div id='data-widgets-container' style='background: blue'>
        <div id='data-widgets-container-inner' class='connected-sortable-data-container'>

        <?php
        $this->displayWidgets (1);
        ?>
        <!-- </div> -->
        </div>
    </div>

    <div id='data-widgets-container-2' class='connected-sortable-data-container'>
        <?php
        $this->displayWidgets (2);
        ?>
    </div>

    <div class='clear'></div>
    </div>
</div>
