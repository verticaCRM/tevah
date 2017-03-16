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
Parameters:
    gridId - the id property of the X2GridView instance
    gridObj - object - the x2gridview instance
Preconditions:
    - {pager} must be in the grid's template and the pager must have previous and next buttons
*/

Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2GridView/X2GridViewTopPagerManager.js', CClientScript::POS_END);

Yii::app()->clientScript->registerCss ('topPagerCss', "
.x2-gridview-top-pager {
    display: inline-block;
    margin-right: 2px;
    margin-top: 1px;
    height: 0;
    float: right;
}
.x2-gridview-top-pager a {
    padding: 0 7px;
    margin-right: 0;
}
.x2-gridview-top-pager a.x2-last-child {
    margin-left: -4px !important;
}
");

$gridObj->addToAfterAjaxUpdate ("
    //console.log ('after ajax update top pager');
    if (typeof x2.".$namespacePrefix."TopPagerManager !== 'undefined') 
        x2.".$namespacePrefix."TopPagerManager.reinit (); 
    $('#".$gridId." .x2-gridview-updating-anim').hide ();
");

Yii::app()->clientScript->registerScript($namespacePrefix.'TopPagerInitScript',"
    if (typeof x2.".$namespacePrefix."TopPagerManager === 'undefined') {
        x2.".$namespacePrefix."TopPagerManager = new X2GridViewTopPagerManager ({
            gridId: '".$gridId."',
            gridSelector: '#".$gridId."',
            namespacePrefix: '".$namespacePrefix."'
        });
    }
", CClientScript::POS_READY);

?>
<div id='<?php echo $gridId; ?>-top-pager' class='x2-gridview-top-pager'>
    <div class='x2-button-group'>
        <a class='top-pager-prev-button x2-button' 
         title='<?php echo Yii::t('app', 'Previous page'); ?>'>&lt;</a>
        <a class='top-pager-next-button x2-button x2-last-child'
         title='<?php echo Yii::t('app', 'Next page'); ?>'>&gt;</a>
    </div>
</div>
