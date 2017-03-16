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

/* @edition:pla */

$this->pageTitle = 'Partner Branding How-To';
$this->layout = '//layouts/column1';
Yii::app()->clientScript->registerCss('branding-howto-css',"

#content {
    border: none;
    background: none;
}

div.page-title.x2-layout-island {
    margin-bottom:5px;
}");
?>
<div class="span-20">
    <div class="page-title x2-layout-island"><h2>Partner Branding How-To</h2></div>
<div class="form x2-layout-island">
<?php
$partnerDir = Yii::getPathOfAlias('application.partner');
$ds = DIRECTORY_SEPARATOR;
$howto = file_get_contents($howtoFile = $partnerDir.$ds.'README.md');

$md = new CMarkdown;
echo $md->transform($howto ? $howto : "File not found: $howtoFile");

?></div><!-- .form -->
</div><!-- .span-20 -->
