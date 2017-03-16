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

/**
 * Handles list/grid view rendering.
 *
 * @package application.components.filters
 */
class X2AjaxHandlerFilter extends CFilter {

    protected function preFilter($filterChain){
        if (Yii::app()->request->getIsAjaxRequest() && isset($_GET["ajax"])) {
            if($_GET['ajax']=='history'){
                if(isset($_GET['id'])){
                    $type = $filterChain->controller->id;
                    /* x2plastart */
                    if (Yii::app()->contEd('pla') && $type === 'marketing' &&
                            $filterChain->controller->getAction()->id === 'anonContactView')
                        $type = 'AnonContact';
                    /* x2plaend */
                    $filterChain->controller->widget('History', array('associationType' => $type, 'associationId' => $_GET['id']));
                    Yii::app()->end();
                }
            }
        }
        return true;
    }
}
?>
