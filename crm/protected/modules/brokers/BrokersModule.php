<?php

/**
 * @package application.modules.template 
 */
class BrokersModule extends CWebModule {
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'brokers.models.*',
			'brokers.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
