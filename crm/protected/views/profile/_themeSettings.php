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
* This page renders the theme selector and th appropriate javascript
*/

Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/themes/x2engine/css/profile/themeSelector.css');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ThemeSelector.js', CClientScript::POS_END);

$user = Yii::app()->user->name;

$params = CJSON::encode(array(
	'defaults' => array(ThemeGenerator::$defaultLight, ThemeGenerator::$defaultDark),
	'active' => $selected,
	'user' => $user,
	'translations' => array(
		'createNew' => Yii::t('profile', 'Create a new theme to edit'),
	)
));


Yii::app()->clientScript->registerScript('schemeJS', "


$(function () {
    new x2.ThemeSelector($params);
});

", CClientScript::POS_END);


echo "<div class='theme-picker' id='theme-picker'>";

$settings = ThemeGenerator::$settingsList;

$themes = $myThemes->data;
foreach($themes as $theme){
	$scheme = CJSON::decode ($theme->description);
	if (!is_array($scheme)){
		continue;
	}

	$fileName = $theme->fileName;
	if (strlen($fileName) > 15) {
		$fileName = substr($fileName, 0, 15).'...';
	}


	$uploadedBy = $theme->uploadedBy;

	echo CHtml::openTag ('div', array(
		'class'=>"scheme-container",
		'name'=> $fileName,
		)
	);
		echo CHtml::openTag ('div', array( 
			'class'=> 'scheme-container-inner', 
			'style' => "
				background: #$scheme[content];
				color: #$scheme[text];"
			)
		);

		echo "<div id='name' > $fileName </div> ";
		if ($fileName == ThemeGenerator::$defaultLight || $fileName == ThemeGenerator::$defaultDark) {
			$uploadedByName = '';
		} else {
			$uploadedByName = $uploadedBy;
		}
		echo "<div id='uploadedBy' value='$uploadedBy' >$uploadedByName</div>";
		echo "<div class='clear'></div>";

			foreach($scheme as $key => $color){
				if (!in_array($key, $settings))
					continue;

				$display = in_array($key, array ('text', 'content')) ? 'display: none;' : '';

				echo CHtml::tag ('div', array(
					'class'=>"scheme-color", 
					'name' => "$key",
					'color'=> $color,
					'style'=>"background: #$color; $display")
				, ' ');
			}

		// echo "<div class='hidden' id='backgroundTiling' value='$scheme[backgroundTiling]' ></div>";
		// echo "<div class='hidden' id='backgroundImg' value='$scheme[backgroundImg]' ></div>";
		echo "<div class='clear'></div>";
		echo '</div>';
	// echo CHtml::button('edit', array('id' =>'edit')); 
	echo "</div>";
}

?>

</div>
