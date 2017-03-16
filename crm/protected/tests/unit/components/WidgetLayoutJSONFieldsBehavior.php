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

Yii::import('application.models.*');
Yii::import('application.components.*');
Yii::import('application.components.util.*');
Yii::import('application.components.X2Settings.*');
Yii::import('application.components.sortableWidget.*');
Yii::import('application.components.sortableWidget.profileWidgets.*');

/**
 * @package application.tests.unit.components
 */
class WidgetLayoutJSONFieldsBehaviorTest extends CActiveRecordBehaviorTestCase {

    /**
     * Ensure that fields array is populated from files in components/sortableWidget/profileWidgets 
     */
    public function testFields () {
		$report = new Reports();
        $fields = $report->asa ('WidgetLayoutJSONFieldsBehavior')->fields ('dataWidgetLayout');
        
        $expectedFieldKeys = array_map (function ($a) {
                return preg_replace ('/\.php$/', '', $a);
            }, array_filter (
                scandir(
                    Yii::getPathOfAlias(SortableWidget::DATA_WIDGET_PATH_ALIAS)),
                    function ($a) {
                        return preg_match ('/\.php$/', $a);
                    })
            );
        $actualFieldKeys = array_keys ($fields);
        sort ($expectedFieldKeys);
        sort ($actualFieldKeys);
        $this->assertEquals ($expectedFieldKeys, $actualFieldKeys);
    }

    public function testUnpackAttribute () {
		$report = new Reports();
        $fields = $report->asa ('WidgetLayoutJSONFieldsBehavior')->fields ('dataWidgetLayout');
        $unpackedAttribute = $report->asa ('WidgetLayoutJSONFieldsBehavior')
            ->unpackAttribute ('dataWidgetLayout');
        unset ($fields['TemplatesGridViewProfileWidget']);

        // since layout hasn't been set, unpacked attributes should match expected fields with 
        // exception of TemplatesGridViewProfileWidget
        $this->assertEquals ($fields, $unpackedAttribute);
    }

}

?>
