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

mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');


Yii::app()->params->profile = Profile::model()->findByPk(1);	// use the admin's profile since the user hasn't logged in
$jsVersion = '?'.Yii::app()->params->buildDate;

// blueprint CSS framework
$themeURL = Yii::app()->theme->getBaseUrl();
Yii::app()->clientScript->registerCssFile($themeURL.'/css/screen.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/print.css'.$jsVersion,'print');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/main.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/form.css'.$jsVersion,'screen, projection');
Yii::app()->clientScript->registerCssFile($themeURL.'/css/ui-elements.css'.$jsVersion,'screen, projection');

if (AuxLib::getIEVer() < 9) {
	Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/lib/aight/aight.js');
}
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/lib/jquery-migrate-1.2.1.js');

$backgroundImg = '';
$defaultOpacity = 1;
$themeCss = '';

$checkResult = false;
$checkFiles = array(
	'themes/x2engine/images/x2footer.png'=>'1393e4af54ababdcf76fac7f075b555b',
	'themes/x2engine/images/x2-mini-icon.png'=>'153d66b514bf9fb0d82a7521a3c64f36',
);
foreach($checkFiles as $key=>$value) {
	if(!file_exists($key) || hash_file('md5',$key) != $value)
		$checkResult = true;
}
$theme2Css = '';
if($checkResult)
	$theme2Css = 'html * {background:url('.CHtml::normalizeUrl(array('/site/warning')).') !important;} #bg{display:none !important;}';

// check for background image, use it if one is set
if(empty(Yii::app()->params->profile->backgroundImg))
	$backgroundImg = CHtml::image(Yii::app()->getBaseUrl().'/uploads/defaultBg.jpg','',array('id'=>'bg'));
else
	$backgroundImg = CHtml::image(Yii::app()->getBaseUrl().'/uploads/'.Yii::app()->params->profile->backgroundImg,'',array('id'=>'bg'));

if(!empty(Yii::app()->params->profile->backgroundColor)) {
	$themeCss .= 'body {background-color:#'.Yii::app()->params->profile->backgroundColor.";}\n";

	if(!empty($backgroundImg)) {
		$shadowRgb = 'rgb(0,0,0,0.5)';	// use a black shadow if there is an image
	} else {
        // if there is no BG image, calculate a darker tone for the shadow
		$shadowColor = X2Color::hex2rgb(Yii::app()->params->profile->backgroundColor);	

		foreach($shadowColor as &$value) {
			$value = floor(0.5*$value);
		}
		$shadowRgb = 'rgb('.implode(',',$shadowColor).')';
	}
	$themeCss .= "#page {
        -moz-box-shadow: 0 0 30px $shadowRgb;
        -webkit-box-shadow: 0 0 30px $shadowRgb;
        box-shadow: 0 0 30px $shadowRgb;
    }\n";
}
if(!empty(Yii::app()->params->profile->menuBgColor))
	$themeCss .= '#main-menu-bar {background:#'.Yii::app()->params->profile->menuBgColor.";}\n";

if(!empty(Yii::app()->params->profile->menuTextColor))
	$themeCss .= '#main-menu-bar ul a, #main-menu-bar ul span {color:#'.Yii::app()->params->profile->menuTextColor.";}\n";


Yii::app()->clientScript
        ->registerCss('applyTheme',$themeCss,'screen',CClientScript::POS_HEAD)
        ->registerCss('applyTheme2',$theme2Css,'screen',CClientScript::POS_HEAD)
        ->registerCssFile(Yii::app()->theme->getBaseUrl().'/css/login.css')
        ->registerCssFile(Yii::app()->theme->getBaseUrl().'/css/fontAwesome/css/font-awesome.css')
        ->registerScriptFile(Yii::app()->getBaseUrl().'/js/auxlib.js')
        ->registerScriptFile(Yii::app()->getBaseUrl().'/js/X2Forms.js');


?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8" />
<meta name="language" content="<?php echo Yii::app()->language; ?>" />

<meta name="description" content="X2Engine - Open Source Sales CRM - Sales Software">
<meta name="keywords" content="X2Engine,X2CRM,open source sales CRM,sales software">

<link rel="icon" href="<?php echo Yii::app()->getFavIconUrl (); ?>" type="image/x-icon">
<link rel="shortcut-icon" href="<?php echo Yii::app()->getFavIconUrl (); ?>" type="image/x-icon">
<link rel="icon" href="<?php echo Yii::app()->getFavIconUrl (); ?>" type="image/x-icon">
<link rel="shortcut-icon" href="<?php echo Yii::app()->getFavIconUrl (); ?>" type="image/x-icon">

<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="<?php echo $themeURL; ?>/css/ie.css" media="screen, projection" />
<![endif]-->
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body id="body-tag"  class="login">
<meta name="viewport" content="width=device-width, initial-scale=0.8, user-scalable=no">
<!--<div class="ie-shadow" style="display:none;"></div>-->
<?php echo $content; 
?>
<div class='background'>
	<div class='stripe-container'>
		<div class='stripe small' style="float:left"></div>
		<div class='stripe' style="float:left"></div>
		<div class='stripe small' style="float:right"></div>
		<div class='stripe' style="float:right"></div>
	</div>
</div>

<?php LoginThemeHelper::render() ?>
</body>
</html>
