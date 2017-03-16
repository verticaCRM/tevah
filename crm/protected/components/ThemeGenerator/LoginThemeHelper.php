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

class LoginThemeHelper {

	/**
	 * @var constant name of cookie that saves the current profile theme
	 */
	const PROFILE_COOKIE = 'profileTheme';
	
	/**
	 * @var constant Name of the cookie tha is the current theme of the login screen
	 */
	const LOGIN_THEME_COOKIE = 'themeName';

	/**
	 * @var constant Name of the cookie that defines the login background color
	 */
	const LOGIN_BACKGROUND_COOKIE= 'loginBackground';

	/**
	 * @var int length of the cookies set
	 */
	public static $cookieLength = 1209600; // Two weeks

	/**
	 * @var string name of the next theme. This will be the dark theme if the current theme is default
	 */
	public $nextTheme;

	/**
	 * @var string name of the currently applied theme.
	 */
	public $currentTheme;

	/**
	 * @var string color name of the background color currently set.
	 */
	public $currentBackground;

	/**
	 * The constructor does most of the work. Handles Posting expected on the login screen. 
	 */
	public function __construct() {
		$loginTheme = ThemeGenerator::$defaultLight;
		$darkTheme = ThemeGenerator::$defaultDark;


		// Set the dark theme to be a different than default
		if ( isset( $_COOKIE[self::PROFILE_COOKIE]) && 
			$_COOKIE[self::PROFILE_COOKIE] != ThemeGenerator::$defaultLight) {
			$darkTheme = $_COOKIE[ self::PROFILE_COOKIE ];
		}

		// Check if the login theme is set
		if ( isset($_POST[self::LOGIN_THEME_COOKIE] ) ) {
			
			//Set a cookie if a post was mode
			AuxLib::setCookie(  self::LOGIN_THEME_COOKIE, $_POST[self::LOGIN_THEME_COOKIE], self::$cookieLength);
			$loginTheme = $_POST[self::LOGIN_THEME_COOKIE];

		} else if ( isset($_COOKIE[self::LOGIN_THEME_COOKIE] ) ) {
			$loginTheme = $_COOKIE[self::LOGIN_THEME_COOKIE];	
		} 


		
		// get the button post value; The opposite of what theme is set. 
		$nextTheme = ( $loginTheme == ThemeGenerator::$defaultLight ) ? $darkTheme : ThemeGenerator::$defaultLight;

		$this->currentTheme = $loginTheme;
		$this->nextTheme = $nextTheme;

		$this->currentColor = null;	
		if ( isset($_COOKIE[self::LOGIN_BACKGROUND_COOKIE]) )
			$this->currentColor = $_COOKIE[self::LOGIN_BACKGROUND_COOKIE];


		$this->registerJS();
	}

	public static function render(){
		$th = new LoginThemeHelper;
		ThemeGenerator::renderTheme($th->currentTheme);
		echo $th->formHtml();
	}

	public function formHtml(){
		$html  = '';
		$html .= CHtml::beginForm('','post', array(
			'id'=>'dark-theme-form',
		));
		$html .= CHtml::hiddenField('themeName', $this->nextTheme);
		$html .= CHtml::endForm();
		return $html;

	}

	/**
	 * Helper action upon login
	 * expects a post of the theme and sets it to be the current theme ONLY if the current theme is not already set.
	 */
	public static function login() {
		if ( !isset($_POST[self::LOGIN_THEME_COOKIE]) ) {
			return;
		}

		$themeName = $_POST[self::LOGIN_THEME_COOKIE];
	    $profile = X2Model::model('Profile')->findByPk(Yii::app()->user->id);

	    if( $profile->theme['themeName'] == '' || $profile->theme['themeName'] == ThemeGenerator::$defaultLight) {
	        $profile->theme = ThemeGenerator::loadDefault( $themeName );
	        $profile->save();
	    }

	}

	/**
	 * Saves a profile Theme to the cookies
	 * @param string $themeName name of the theme to be set. 
	 */
	public static function saveProfileTheme($themeName){
		//Set a cookie for the profile theme set
		if( $themeName != ThemeGenerator::$defaultLight) {
		    AuxLib::setCookie(self::PROFILE_COOKIE, $themeName, self::$cookieLength);
		}

		// Set a cookie for the login screen 
		if( isset($_COOKIE[self::LOGIN_THEME_COOKIE]) ) {
		    AuxLib::setCookie(self::LOGIN_THEME_COOKIE, $themeName, self::$cookieLength);
		}

	}

	/**
	 * Registers necessary JS and passes is the proper arguments
	 * Checks for POST
	 */
	public function registerJS() {
		Yii::app()->clientScript->registerCoreScript('cookie', 		  CClientScript::POS_READY);
		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/LoginThemeHelper.js', CClientScript::POS_END);		

		/* This part will create a part of the theme selector specific to the current theme */

		if( $this->currentTheme == ThemeGenerator::$defaultLight ) {
			$theme = ThemeGenerator::loadDefault( $this->nextTheme );
		} else {
			$theme = ThemeGenerator::loadDefault( $this->currentTheme );
		}

		if (!isset($theme['background'])) {
			$theme['background'] = '#000';
		}

		$themeBG = array ( 
			$theme['background'],
			X2Color::brightness( $theme['background'], -0.1),
		);

		$JSON = array(
			'themeColorCookie' => self::LOGIN_BACKGROUND_COOKIE,
			'cookieLength' => self::$cookieLength,
			'open' => isset($_POST[self::LOGIN_THEME_COOKIE]),
			'currentColor' => $this->currentColor,
			'currentThemeBG' => $themeBG
		);

		$JSON = CJSON::encode($JSON);

		Yii::app()->clientScript->registerScript('LoginThemeHelperJS', "
			new x2.LoginThemeHelper($JSON);
		", CClientScript::POS_END);
	}

}

?>
