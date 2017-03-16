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

/*
Public/private profile page. If the requested profile belongs to the current user, profile widgets
get displayed in addition to the activity feed/profile information sections. 
*/

$widths = $this->getColumnWidths();

if ($container == 1): ?>
	<div id='profile-widgets-container' 
	style="width: <?php echo $widths[1] ?>">
	    <div id='profile-widgets-container-inner' class='connected-sortable-profile-container'>
	    <?php $this->displayWidgets (1); ?>
	    </div>
	</div>
<?php endif; ?>

<?php if ($container == 2): ?> 
	<div id='profile-widgets-container-2' class='connected-sortable-profile-container' 
	style="width: <?php echo $widths[0] ?>">
	    <?php $this->displayWidgets (2); ?>
	</div>
<?php endif; ?>



<?php 
/*********************************
* Sortable Widget Menus
********************************/
echo $this->model->getHiddenProfileWidgetMenu ();
?>

<div id='create-profile-widget-dialog' class='form' style='display: none;'>
    <label for='' class='left-label'><?php echo Yii::t('app', 'Widget Type: '); ?></label>
    <?php
    $widgetSubtypeOptions = SortableWidget::getWidgetSubtypeOptions ('profile');
    asort ($widgetSubtypeOptions);
    
    /* x2prostart */
    $widgetSubtypeOptions['DataWidget'] = Yii::t('app', 'Charting Widget');
    /* x2proend */
    
    echo CHtml::dropDownList ('widgetType', '', $widgetSubtypeOptions);

    /* x2prostart */
   	echo $this->getChartingWidgetDropdown();
    /* x2proend */
    ?>
</div>
