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


$echoedFirstSideBarLeft = false;
// Echoes sidebar left container div if it hasn't already been echoed.
$echoFirstSideBarLeft = function (&$echoedFirstSideBarLeft) {
    if (!$echoedFirstSideBarLeft) {
        echo '<div id="x2widget_ActionMenu" class="sidebar-left">';
        $echoedFirstSideBarLeft = true;
    }
};

if(isset($this->actionMenu) && !empty($this->actionMenu)) {
    $echoFirstSideBarLeft ($echoedFirstSideBarLeft);
    $this->beginWidget('LeftWidget',array(
        'widgetLabel'=>Yii::t('app','Actions'),
        'widgetName' => 'ActionMenu',
        'id'=>'actions'
    ));

    $this->widget('zii.widgets.CMenu', array('items'=>$this->actionMenu,'encodeLabel'=>true));
    $this->endWidget();
}

foreach($this->leftPortlets as &$portlet) {
    $echoFirstSideBarLeft ($echoedFirstSideBarLeft);
    $this->beginWidget('zii.widgets.CPortlet',$portlet['options']);
    echo $portlet['content'];
    $this->endWidget();
}

if(isset($this->modelClass)){
    $module = ($this->module instanceof CModule) ? $this->module->id : $this->id;
    $controller = $this->id;
    // Determine if there's left sidebar content to render:
    $modulePath = implode(DIRECTORY_SEPARATOR,array(
        Yii::app()->basePath,
        'modules',
        $module,
        'views',
        $controller,
        '_sidebarLeftExtraContent.php'
    ));
    $controllerPath = implode(DIRECTORY_SEPARATOR,array(
        Yii::app()->basePath,
        'views',
        $controller,
        '_sidebarLeftExtraContent.php'
    ));
    $profile = $this->id == 'profile'
            && $this->action->id == 'view'
            && (!(isset($_GET['publicProfile']) && $_GET['publicProfile'])
                && $_GET['id'] == Yii::app()->params->profile->id);
    if($profile || ((file_exists($controllerPath) || file_exists($modulePath))
                    && $controller != 'profile')){
        $echoFirstSideBarLeft($echoedFirstSideBarLeft);
        $this->renderPartial('_sidebarLeftExtraContent');
    }
}

if ($echoedFirstSideBarLeft) echo "</div>";
