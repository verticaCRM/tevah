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
 * Class to handle the main profile page
 * @package application.components
 * @author Alex Rowe <alex@x2engine.com>
 */
class ProfileDashboard extends CWidget {

	/**
	 * @var model Profile to be rendered
	 */
	public $model;

	/**
	 * @var string The view file to be rendered
	 */
	public $viewFile = 'profileDashboard';

	/**
	 * @var float The default width of the first column (%percent)
	 */
	public $columnWidth = 52;

	/**
	 * @var float the margin on each percentage. 
	 * 50% column width with 1% margin would result in 49% / 49% columns
	 */
	public $columnMargin = 0.0;

	public function init() {
		Yii::app()->clientScript->registerPackages($this->getPackages(), true);

		$miscLayoutSettings = Yii::app()->params->profile->miscLayoutSettings;
		if (isset($miscLayoutSettings['columnWidth'])) {
			$this->columnWidth = $miscLayoutSettings['columnWidth'];
		}

		$this->instantiateJS();

		parent::init ();
	}

	public function run() {
		$this->render('layoutEditor', array ('namespace' => 'profile'));
		parent::run ();
	}

	public function renderContainer ($container) {
		$this->render('profileDashboard', array('container' => $container));
	}

	public function getPackages () {
        $baseUrl = Yii::app()->getBaseUrl ();
		$packages = array(
            'layoutEditorCss' => array(
                'baseUrl' => Yii::app()->theme->getBaseUrl (),
                'css' => array(
                    '/css/components/views/layoutEditor.css',
                )
            ),
            'X2WidgetJS' => array (
		        'baseUrl' => $baseUrl.'/js',
		    	'js' => array(
		    		'X2Widget.js',
				),
				'depends' => array('auxlib')
		    ),
		    'sortableWidgetJS' => array(
                'baseUrl' => $baseUrl.'/js/sortableWidgets/',
		    	'js' => array(
		    		'SortableWidget.js',
				    'SortableWidgetManager.js',
				    'TwoColumnSortableWidgetManager.js',
				    'ProfileWidgetManager.js',
				),
				'depends' => array('auxlib', 'X2WidgetJS')
            ),
	        'layoutEditorJS' => array(
	            'baseUrl' => $baseUrl.'/js/',
	        	'js' => array(
                    'LayoutEditor.js',
                    'ProfileLayoutEditor.js',
                )
	        )
		);

		return $packages;
	}

	/**
	 * Instantiates widgets in a contiainer, echoing them out.
	 * @param containerNumber int the number of container. (1 or 2 currently)
	 */
	public function displayWidgets ($containerNumber){
		$layout = $this->model->profileWidgetLayout;

		foreach ($layout as $widgetClass => $settings) {
		    if ($settings['containerNumber'] == $containerNumber) {
		        SortableWidget::instantiateWidget ($widgetClass, $this->model);
		    }

		}
	}

	/**
	 * Instantiates the JS fo rthe profile dashboard
	 */
	public function instantiateJS () {
        $miscSettings = Yii::app()->params->profile->miscLayoutSettings;
        $layoutEditorParams = array (
        	'miscSettingsUrl' => Yii::app()->controller->createUrl('saveMiscLayoutSetting'),
        	'margin' => $this->columnMargin
        );

        $columnWidth = $this->columnWidth;
        if ($columnWidth) {
        	$layoutEditorParams['columnWidth'] = $columnWidth;
        }

        $layoutEditorParams = CJSON::encode($layoutEditorParams);

		$widgetManagerParams = CJSON::encode (array(
            'setSortOrderUrl' => Yii::app()->controller->createUrl ('/profile/setWidgetOrder'),
            'showWidgetContentsUrl' => 
                Yii::app()->controller->createUrl ('/profile/view', array ('id' => 1)),
            'connectedContainerSelector' => '.connected-sortable-profile-container',
            'translations' => $this->getTranslations(),
            'createProfileWidgetUrl' => 
                Yii::app()->controller->createUrl ('/profile/createProfileWidget'),
            /* x2prostart */
            'createChartingWidgetUrl' => 
                Yii::app()->controller->createUrl ('/reports/addToDashboard'),
            /* x2proend */
        ));

        $script = "
        	x2.profileWidgetManager = new ProfileWidgetManager ($widgetManagerParams);
        	x2.profileLayoutManager = new x2.ProfileLayoutEditor ($layoutEditorParams);

        	new PopupDropdownMenu ({
        	    containerElemSelector: '#x2-hidden-profile-widgets-menu-container',
        	    openButtonSelector: '#show-profile-widget-button',
        	    defaultOrientation: 'left'
        	});
			
        ";

        Yii::app()->clientScript->registerScript(
            'profilePageInitScript', $script, CClientScript::POS_END);
       
	}

	/* x2prostart */
	/**
	 * Creates a dropdown showing the different charts that can be created
	 * The values of the options are a JSON string that give the proper fields
	 * to call the action AddToDashboard in the charts controller.
	 * @return string HTML for a dropdown
	 */
	public function getChartingWidgetDropdown () {
		    $options = array();
		    $reports = Reports::model()->findAll();
		    foreach ($reports as $report) {
		    	foreach ($report->dataWidgetLayout as $key => $value) {
		    		list($class, $uid) = SortableWidget::parseWidgetLayoutKey ($key);
		    		if (!$value['chartId']) {
		    			continue;
		    		}

		    		$key = CJSON::encode(array(
		    			'modelId' => $report->id,
		    			'widgetClass' => $class,
		    			'widgetUID' => $uid
		     		));
			    	$options[$key] = $value['label'];
		    		
		    	}
		    }	
		    if (count($options) == 0) {
		    	$options['noCharts'] = Yii::t('charts', 'No charts have been created');
		    }

		    return CHtml::tag('span', 
			    array ( 'style' => 'display:none', 
			    		'id' => 'chart-name-container' ),
			    CHtml::dropDownList ('chartName', '', $options).
			    X2Html::hint(Yii::t('profile', 'You can create new charts in the reports module'))
		    );

	}
	/* x2proend */

	/**
	 * Calculates the widths of the columns
	 * @return array(string, string) a pair of the column widths for a css rule. 
	 */
	public function getColumnWidths () {

		if(!$this->columnWidth) {
			return array('', '');
		}

		$column1 = $this->columnWidth;
		$column2 = 100 - $column1;

		$column1 = ($column1 - $this->columnMargin).'%';
		$column2 = ($column2 - $this->columnMargin).'%';

		return array(
			$column1,
			$column2
		);
	}


	public function getTranslations () {
		return array(
			'createProfileWidgetDialogTitle' => Yii::t('profile', 'Create Profile Widget'),
			'Create' => Yii::t('app',  'Create'),
			'Cancel' => Yii::t('app',  'Cancel'),
		);
	}

}

?>
