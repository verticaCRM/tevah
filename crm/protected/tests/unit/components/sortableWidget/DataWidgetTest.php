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

Yii::import ('application.components.*');
Yii::import ('application.modules.charts.models.*');
Yii::import ('application.components.X2Settings.*');
Yii::import ('application.components.sortableWidget.dataWidgets.*');

/**
 * @package application.tests.unit.components.sortableWidget
 */
class DataWidgetTest extends X2DbTestCase {

    public $fixtures = array(
        'charts' => 'Charts',
        'reports' => 'Reports'
    );

    public function testError() {
    	$errors = array( 
    		'ajoaijwf',
    		'general',
    		'missingColumn',
    		);

    	//Ensure theres not an error 
    	foreach ($errors as $key => $value) {
	    	$this->assertNotEmpty(DataWidget::error());
    	}

    	//Ensure the general key is the default
    	$this->assertEquals(DataWidget::error('UF83ld!@'), DataWidget::error('general'));
    }

    public function testGetSettings() {
    	$widget = new DataWidget;
    	$widget->profile = $this->reports('testReport1');
    	$widget->widgetUID = '546fe2089f793';
    	$settings = $widget->settings;

    	$keys = DataWidget::getJSONPropertiesStructure();

    	foreach($keys as $key => $value) {
    		$this->assertArrayHasKey($key, $settings);
    	}

    	$this->assertEquals(count($keys), count($settings));

    }



}

?>
