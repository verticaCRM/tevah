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
Yii::import ('application.components.X2Settings.*');
Yii::import ('application.components.sortableWidget.*');

/**
 * @package application.tests.unit.components.sortableWidget
 */
class SortableWidgetTest extends X2DbTestCase {

    public $fixtures = array (
        'profiles' => 'Profile',
    );

    /**
     * Helper method to create a new profile widget 
     */
    private function createProfileWidget ($profile, $widgetSubtype) {
        list ($success, $uid) = SortableWidget::createSortableWidget (
            $profile, $widgetSubtype, 'profile');
        // ensure widget was created successfully
        $this->assertTrue ($success);
        return $uid;
    }

    /**
     * Attempt to create a new widget 
     */
    public function testCreateSortableWidget () {
        $profile = $this->profiles ('adminProfile');
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        // clone the contacts grid widget
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $uid = $this->createProfileWidget ($profile, $widgetSubtype);

        $widgetLayoutAfter = $profile->profileWidgetLayout;
        $createdWidgetAttr = array_diff_key ($widgetLayoutAfter, $widgetLayoutBefore);
        VERBOSE_MODE && print_r ($createdWidgetAttr);
        // ensure that widget settings were saved correctly
        $keys = array_keys ($createdWidgetAttr);
        $this->assertEquals ($widgetSubtype.'_'.$uid, array_pop ($keys));
        $this->assertEquals (
            $createdWidgetAttr[$widgetSubtype.'_'.$uid],
            $widgetSubtype::getJSONPropertiesStructure ());
    }

    /**
     * Attempt to create a widget of an invalid subtype 
     */
    public function testCreateSortableWidgetError () {
        $profile = $this->profiles ('adminProfile');
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $widgetSubtype = 'NotAWidgetClass';
        $this->assertFalse (SortableWidget::subtypeisValid ('profile', $widgetSubtype));
        list ($success, $uid) = SortableWidget::createSortableWidget (
            $profile, $widgetSubtype, 'profile');
        // ensure widget wasn't created
        $this->assertFalse ($success);
    }

    /**
     * Clone a default widget then delete it 
     */
    public function testDeleteSortableWidget () {
        $profile = $this->profiles ('adminProfile');
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $uid = $this->createProfileWidget ($profile, $widgetSubtype);

        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $success = SortableWidget::deleteSortableWidget ($profile, $widgetSubtype, $uid, 'profile');
        $this->assertTrue ($success);
        $widgetLayoutAfter = $profile->profileWidgetLayout;
        unset ($widgetLayoutBefore[$widgetSubtype.'_'.$uid]);
        $this->assertEquals ($widgetLayoutBefore, $widgetLayoutAfter);
    }

    /**
     * Test soft deletion of default widgets
     */
    public function testSoftDeletion () {
        $profile = $this->profiles ('adminProfile');
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $widgetSettings = $widgetLayoutBefore[$widgetSubtype];
        $this->assertFalse ($widgetSettings['softDeleted']);
        $success = SortableWidget::deleteSortableWidget ($profile, $widgetSubtype, '', 'profile');
        $this->assertTrue ($success);
        $widgetLayoutAfter = $profile->profileWidgetLayout;
        // old and new layout should contain same entries (with different settings) since deletion
        // of a default widget is merely a soft deletion
        $this->assertEquals (
            array_keys ($widgetLayoutBefore), array_keys ($widgetLayoutAfter));
        $widgetSettings = $widgetLayoutAfter[$widgetSubtype];
        $this->assertTrue ($widgetSettings['softDeleted']);
    }

    /**
     * Delete a default widget and ensure that it can be recreated
     */
    public function testDefaultWidgetRecreation () {
        $profile = $this->profiles ('adminProfile');
        $widgetSubtype = 'ContactsGridViewProfileWidget';
        $success = SortableWidget::deleteSortableWidget ($profile, $widgetSubtype, '', 'profile');
        $this->assertTrue ($success);
        $widgetLayoutBefore = $profile->profileWidgetLayout;
        $widgetSettings = $widgetLayoutBefore[$widgetSubtype];
        $this->assertTrue ($widgetSettings['softDeleted']);
        list ($success, $uid) = SortableWidget::createSortableWidget (
            $profile, $widgetSubtype, 'profile');
        $this->assertTrue ($success);
        $widgetLayoutAfter = $profile->profileWidgetLayout;
        $widgetSettings = $widgetLayoutAfter[$widgetSubtype];
        $this->assertFalse ($widgetSettings['softDeleted']);
        $this->assertEquals (
            array_keys ($widgetLayoutBefore), array_keys ($widgetLayoutAfter));
    }

}

?>
