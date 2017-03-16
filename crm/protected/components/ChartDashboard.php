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

/* @edition:pro */

/**
 * Renders a dashboard for dataWidgets
 * @package application.components
 * @author Alex Rowe <alex@x2engine.com>
 */
class ChartDashboard extends CWidget {
	
	public $report = false;

	public $viewFile = 'chartDashboard';

	public function init() {
		parent::init();
	}

	public function run() {
		parent::run();

		if (AuxLib::getIEVer() < 9) {

			if (!$this->report) {
				X2Html::IEBanner();
			}
			return;
		}

		$this->registerPackages();
		$this->render($this->viewFile);
	}

	/**
	 * Echos the widgets in a specific container number 
	 * @param $containerNumber Number of container (1 or 2)
	 */
	public function displayWidgets ($containerNumber) {

		if ($this->report) {
			$profile = $this->report;
		} else {
		    $profile = Yii::app()->params->profile;
		}

	    $layout = $profile->dataWidgetLayout;

	    // display profile widgets in order
	    foreach ($layout as $widgetLayoutKey => $settings) {
	        if ($settings['containerNumber'] == $containerNumber) {
	            if( $this->filterReport($settings['chartId']) ){

	            	// $force = isset($this->report);
	            	SortableWidget::instantiateWidget($widgetLayoutKey, $profile, 'data');	
	            }
	        }
	    }
	}

	/*
	* Filter chart ids wheter to display or not
	*/
	public function filterReport($chartId) {

		// Check if chart Id is set
		if( !$chartId ) {
			return false;
		}

		$chart = X2Model::model('Charts')->findByPk($chartId);
		
		// Dont display chart if it is broken
		if( !$chart ) {
			$this->deleteChart($chartId);
			return false;
		}

		// Display all charts if report is not set
    	if ( !$this->report ) {
    		return true;
    	}

		// Display chart if report Id matches
		if ( $chart->report->id == $this->report->id ) {
			return true;
		}

		return false;
	}


	 /**
	 * Returns an array of charts currently soft deleted
	 */
	public function getHiddenCharts() {
	    if ($this->report) {
	    	$profile = $this->report;
	    } else {
	        $profile = Yii::app()->params->profile;
	    }
	    $settings = $profile->dataWidgetLayout;

	    $widgets = array();
	    foreach ($settings as $key => $setting) {
	        if( $setting['hidden'] && 
	        	$this->filterReport( $setting['chartId'] )) {
	            $widgets[$key] = $setting;
	        }
	    }
	    return $widgets;
	}

	/**
	 * Deletes the 
	 */
	public function deleteChart($chartId) {
		$profile = Yii::app()->params->profile;
		$layout = $profile->dataWidgetLayout;

		foreach ($layout as $widget => $settings) {
			if ($settings['chartId'] == intval($chartId)) {
				unset($layout[$widget]);
			}
		}

		$profile->dataWidgetLayout = $layout;
		$profile->update();

	}

	public function getReportList() {
		$reports = X2Model::model('Reports')->findAll();

		$options = array();
		foreach($reports as $report) {
			$link = Yii::app()->createUrl (
				'/reports', array ('id' => $report->id));
			$content = "<a href='$link?chart=1'> $report->name</a>";

			$options[] = array(
					'class' => 'report-option',
					'content' => $content
				);
		}

		if (empty($options) ){

			echo X2Html::tag('div', array(
					'id'=> 'no-reports'
				), Yii::t('charts', "Create a report to get started"));
		}

		return X2Html::ul ($options);
	}

	public function registerPackages() {
		$packages = array(
			'auxlib' => array(
			    'baseUrl' => Yii::app()->request->baseUrl,
			    'js' => array(
			        'js/auxlib.js',
			    ),
			),
			'topFlashJS' => array(
			    'baseUrl' => Yii::app()->request->baseUrl,
			    'js' => array(
			        'js/TopFlashes.js',
			    ),
			),
		    'dataWidgetManagerJS' => array(
		    	'baseUrl' => Yii::app()->baseUrl.'/js',
		        'js' => array(
		            'jquery.fullscreen-min.js',
		            'sortableWidgets/SortableWidgetManager.js', 
		            'sortableWidgets/TwoColumnSortableWidgetManager.js', 
		            'sortableWidgets/ProfileWidgetManager.js' ,
		            'PopupDropdownMenu.js', 
		            'DataWidgetManager.js'
		        ),
		        'depends' => array ('auxlib', 'topFlashJS'),
		    ),
		    'chartDashboardCSS' => array(
		    	'baseUrl' => Yii::app()->theme->baseUrl.'/css',
		        'css' => array( 
		        	'components/chartDashboard.css' 
		        )
		    )
		);

		Yii::app()->clientScript->registerPackages( $packages, CClientScript::POS_END );

		Yii::app()->clientScript->registerCSS( 'dashboardPageModificationCSS', '
		#content {
			border: none !important;
		}

		');

		$modelName = 'Reports';
		$modelId = $this->report ? $this->report->id : null;

		$jsParams = array(
			'modelName' => $modelName,
			'modelId' => $modelId,
			'onReport' => (bool) $this->report,
			'translations' => array(
				'saveChart' => Yii::t('charts', 'Report must be saved before creating a chart'),
            )
		);

		Yii::app()->clientScript->registerScript('ChartDasboardJS', 
			"x2.dataWidgetManager = new x2.DataWidgetManager(".CJSON::encode($jsParams).");"
		, CClientScript::POS_END);

	}

}
