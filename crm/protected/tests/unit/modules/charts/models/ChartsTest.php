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

Yii::import ('application.components.*');
Yii::import ('application.modules.charts.models.*');
Yii::import ('application.modules.reports.models.*');
Yii::import ('application.components.X2Settings.*');

/**
 * @package application.tests.unit.components.sortableWidget
 */
class ChartsTest extends X2DbTestCase {

	public $fixtures = array(
		'reports' => 'Reports'
	);

	public $settings= array(
		array (
		    'TimeSeriesFormModel' => array (
	            'timeField' => 'createDate',
	            'labelField' => 'leadSource',
	            'reportId' => 1,
	        )
		),
	);

    public function testValidateSettings () {
        $this->markTestSkipped (); 
    	foreach($this->settings as $setting) {
    		$chart = new Charts;
    		$chart->settings = $setting;
            $this->assertSaves ($chart);
    		$this->assertEquals($chart->reportId, $setting['reportId']);
    	}
    }


}

?>
