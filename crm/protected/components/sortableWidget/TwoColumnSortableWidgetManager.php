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

abstract class TwoColumnSortableWidgetManager extends SortableWidgetManager {

    public $layoutManager;

    /**
     * @var string $JSClass
     */
    public $JSClass = 'TwoColumnSortableWidgetManager'; 

    public $widgetLayoutName = '';

    public $connectedContainerClass = 'connected-sortable-widget-container';

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'TwoColumnSortableWidgetManagerJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
				        'js/sortableWidgets/TwoColumnSortableWidgetManager.js',
                    ),
                    'depends' => array ('SortableWidgetManagerJS')
                ),
            ));
        }
        return $this->_packages;
    }

    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'connectedContainerSelector' => '.'.$this->connectedContainerClass,
            ));
        }
        return $this->_JSClassParams;
    }

	public function displayWidgets ($containerNumber){
        $widgetLayoutName = $this->getWidgetLayoutName ();
		$layout = $this->model->$widgetLayoutName;

		foreach ($layout as $widgetClass => $settings) {
		    if ($settings['containerNumber'] == $containerNumber) {
		        SortableWidget::instantiateWidget ($widgetClass, $this->model);
		    }
		}
	}

	public function init() {
        if (!isset ($this->layoutManager)) {
            $this->layoutManager = new RecordViewLayoutManager (65);
        }

        $this->registerPackages ();
        $this->instantiateJSClass ();

		parent::init ();
	}

    public function run () {
        $this->render ('_twoColumnSortableWidgetLayout');
    }

}

?>
