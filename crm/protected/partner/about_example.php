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

// Authorized partner/reseller "About" page content

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/about-page.css');

$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->settings->appName . ' - ' . Yii::t('app','About');

Yii::app()->clientScript->registerScript('partnerAboutJS','
if(typeof x2 == "undefined") {
    x2 = {};
}
$("#about-map a").click(function(event) {
    if(this.getAttribute("href") == "") {
        event.preventDefault();
        alert('.json_encode(Yii::t('app','Replace the "href" attribute in this link to a Google Maps link to your headquarters, and the "title" attribute with an optional title, to produce a link to open it in Google Maps in a new tab.')).');
    }
});

',CClientScript::POS_READY);

?>
<?php
/* @start:about */
$logo = 'data:image/gif;base64,'.base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'partnerLogoBig_example.gif'));
echo CHtml::image($logo,'',array('class'=>'left'));
Yii::app()->clientScript->registerScript('loadJqueryVersion',"$('#jqueryVersion').html($().jquery);",CClientScript::POS_READY);
?>

<div class='center-column-container form left' >
    <em><?php echo Yii::t('app','Note, you are viewing content rendered from the file:');?></em><br /><br />
    <strong><?php echo 'protected'.str_replace(Yii::app()->basePath,'',__FILE__); ?></strong><br /><br />
    <em><?php echo Yii::t('app','See {howtolink} for instructions on how to edit this page.',array('{howtolink}'=>CHtml::link('Partner Branding How-To',array('/site/page','view'=>'brandingHowto')))); ?></em><br /><br />
    
	[Date of the current version]<br><br>
	<?php echo CHtml::encode(X2_PARTNER_PRODUCT_NAME); ?>
	<div id="about-intro">
        [Your company's info here]
	</div><!-- #about-intro -->
	<hr>
	<div id="about-credits">
		<h4><?php echo Yii::t('app','Version Info'); ?></h4>
		<ul>
            <li>[Your product name here] [Your version here]</li>
			<li><?php echo CHtml::link('X2Engine:',array('/site/page','view'=>'about'));?> <?php echo Yii::app()->params->version;?></li>
			<!--<?php echo Yii::t('app','Build'); ?>: 1234<br>-->
			<li>Yii: <?php echo Yii::getVersion(); ?></li>
			<li>jQuery: <span id="jqueryVersion"></span></li>
			<li>PHP: <?php echo phpversion(); ?></li>
			<!--jQuery Mobile: 1.0b2<br>-->
		</ul>
		<h4><?php echo Yii::t('app','Plugins/Extensions'); ?></h4>
        [List of extra plugins/extensions used by product (beyond those used by X2Engine) here]
	</div><!-- #about-credits -->
	<hr>
	<div id="about-legal">
        [Legal disclaimer here]
	</div><!-- #about-credits -->
    <br>
</div>
<div id="about-map">
<a title="[Optional title]" target="_blank" onclick="" href="">
<img src="data:image/gif;base64,<?php echo base64_encode(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'partnerMap_example.gif')); ?>">
</a>
[Information about your company's headquarters here]
</div>
<?php /* @end:about */ ?>