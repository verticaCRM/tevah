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

class ChartCreator extends CWidget {
	
	/**
	 * @var string Viewfile to render
	 */
	public $viewFile = 'chartCreator';

	/**
	 * @var The report object if on a report page
	 * On the users chart dashboard, this stays null
	 */
	public $report;

	/**
	 * @var boolean if true, the dialog will automaticcaly open
	 */
	public $autoOpen;

	/**
	 * @var array an array of supported chart types for the 
	 * Current chart type
	 */
	public $chartTypes;

	/**
	 * Registers Javascript and CSS
	 */
	public function init() {
		$packages = array(
			'ChartCreatorJS' => array(
				'baseUrl' => Yii::app()->request->baseUrl.'/js',
				'js' => array(
					'ChartCreator.js'
				),
			),
			'ChartCreatorCSS' => array(
				'baseUrl' => Yii::app()->theme->baseUrl.'/css',
				'css' => array(
					'ChartCreator.css',
				),
			),
		);
		Yii::app()->clientScript->registerPackages ($packages, true);

		// Load entries into the chart Types
		$this->chartTypes = array();
		foreach (Charts::$chartTypes as $chartType) {
	    	if ($this->report->chartSupports ($chartType)) {
	    		$this->chartTypes[] = $chartType;
			}
		}
	}	

	/**
	 * @see run()
	 */
	public function run(){		
		parent::run();

		$jsParams = CJSON::encode (array(
			'report' => $this->report->attributes,
			'translations' => $this->getTranslations(),
			'autoOpen' => $this->autoOpen
		));

		Yii::app()->clientScript->registerScript('ChartCreatorRunJS',"
			x2.chartCreator = new x2.ChartCreator($jsParams);
		", CClientScript::POS_END);

		$this->render ($this->viewFile);
	}


	/**
	 * The function to render the different FormModels for each form type. 
	 * the list of charts is static in Charts Model and there should be a corresponding FormModel 
     * and Form class, with corresponding view files and client Scripts
	 */
	public function renderForms(){

	    foreach($this->chartTypes as $chartType) {
	    	$formName = 'ChartForm';

	    	if (class_exists (Charts::toFormName($chartType))) {
	    		$formName = Charts::toFormName($chartType);
	    	}

	        $form = $this->beginWidget($formName, array(
	        	'report' => $this->report,
	        	'chartType' => $chartType
	        ));
	        $form->render (null);
	        $this->endWidget();

	    }

	}

	/**
	 * @return array Array of translations for the front-end
	 */
	public function getTranslations() {
		return array (
			'exitSelection' => Yii::t('charts', 'Click to select'),
			'inSelection' => Yii::t('charts', 'Click on a row or column'),
			'inSelectionrow' => Yii::t('charts', 'Click on a row'),
			'inSelectioncolumn' => Yii::t('charts', 'Click on a column'),
		);
	}

}
