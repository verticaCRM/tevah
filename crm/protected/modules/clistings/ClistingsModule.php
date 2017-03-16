<?php
/**
 * @package application.modules.template 
 */
class ClistingsModule extends CWebModule {
	
	public $assetsUrl;
	
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application
		$this->assetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias('application.modules.clistings.assets'),false,-1,YII_DEBUG?true:null);
		// import the module-level models and components
		$this->setImport(array(
			'clistings.models.*',
			'clistings.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
