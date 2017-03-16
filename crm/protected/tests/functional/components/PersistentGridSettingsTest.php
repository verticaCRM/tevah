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
 * Tests behavior of SmartDataProviderBehavior and ERememberFiltersBehavior 
 */

class PersistentGridSettingsTest extends X2DbTestCase {

    /**
     * Ensure that DB persistent sort settings get set properly for all children of X2Model
     */
    public function testX2ModelSort () {
        Yii::app()->params->profile->generalGridViewSettings = '';
        Yii::app()->params->profile->save ();
        $models = X2Model::getModelNames (); 
        foreach ($models as $class => $title) {
            VERBOSE_MODE && println ('testing sort for '.$class);
            $uid = rand ();
            $_GET["{$uid}_sort"] = 'test';
            $_GET[$class] = array (
                'id' => 0,
            );
            $searchModel = new $class ('search', $uid, true);
            $dataProvider = $searchModel->search ();
            $this->assertNotNull (
                $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort'));
            $this->assertNotNull (
                $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters'));
        }
    }

    /**
     * Ensure that DB persistent sort settings get set properly
     */
    public function testContactSort () {
        Yii::app()->params->profile->generalGridViewSettings = '';
        Yii::app()->params->profile->save ();
        $_GET['Contacts'] = array (
            'firstName' => 'test',
            'lastName' => 'test',
        );
        $uid = 'testUID';
        $_GET["{$uid}_sort"] = 'firstName';
        $contact = new Contacts ('search', $uid, true);
        VERBOSE_MODE && print_r ($contact->getAttributes ());
        $dataProvider = $contact->search ();
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort'));
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters'));
    }

    /**
     * Ensure that DB persistent sort settings get set properly
     */
    public function testProfileSort () {
        Yii::app()->params->profile->generalGridViewSettings = '';
        Yii::app()->params->profile->save ();
        $_GET['Profile'] = array (
            'username' => 'test',
        );
        $uid = 'testUID';
        $_GET["{$uid}_sort"] = 'username';
        $profile = new Profile ('search', $uid, true);
        $dataProvider = $profile->search ();
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort'));
        $this->assertNotEmpty (
            $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters'));
    }

    /**
     * Ensure that sort order and filters in GET params get saved to session correctly 
     */
    public function testSessionSettings () {
        $_SESSION = array ();

        $_GET['Contacts'] = array (
            'firstName' => 'test',
            'lastName' => 'test',
            'email' => 'test@test.com',
        );
        $_GET["Contacts_sort"] = 'firstName';
        $contact = new Contacts ('search');
        VERBOSE_MODE && print_r ($contact->getAttributes ());
        $dataProvider = $contact->search ();
        VERBOSE_MODE && print_r ($_SESSION);

        $sort = $contact->asa ('ERememberFiltersBehavior')->getSetting ('sort');
        $filters = $contact->asa ('ERememberFiltersBehavior')->getSetting ('filters');
        $this->assertEquals ($filters, $_GET['Contacts']);
        $this->assertEquals ($sort, $_GET['Contacts_sort']);

    }


    /**
     * For each child of X2Model, ensure that filters and sort order get saved in session correctly
     * and that analogous methods in ERememberFiltersBehavior and SmartDataProviderBehavior behave
     * the same
     */
    public function testX2ModelSessionSettings () {
        $models = X2Model::getModelNames (); 
        foreach ($models as $class => $title) {
            $_SESSION = array ();
            VERBOSE_MODE && println ('testing sort for '.$class);
            $uid = rand ();
            $_GET["{$class}_sort"] = 'test';
            $_GET[$class] = array (
                'id' => 0,
            );
            $searchModel = new $class ('search');
            $dataProvider = $searchModel->search ();
            $sort = $searchModel->asa ('ERememberFiltersBehavior')->getSetting ('sort');
            $filters = $searchModel->asa ('ERememberFiltersBehavior')->getSetting ('filters');
            $sort2 = $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('sort');
            $filters2 = $dataProvider->asa ('SmartDataProviderBehavior')->getSetting ('filters');
            $this->assertEquals ($filters, $_GET[$class]);
            $this->assertEquals ($filters, $filters2);
            $this->assertEquals ($sort, $_GET[$class.'_sort']);
            $this->assertEquals ($sort, $sort2);
        }
    }

// tests functions that don't get called in the app
    /**
     * Set filters, then try unsetting filters not in a specified list of attributes
     */
//    public function testUnsetFiltersNotIn () {
//        $_SESSION = array ();
//
//        $_GET['Contacts'] = array (
//            'firstName' => 'test',
//            'lastName' => 'test',
//            'email' => 'test@test.com',
//        );
//        $_GET["Contacts_sort"] = 'firstName';
//        $contact = new Contacts ('search');
//        VERBOSE_MODE && print_r ($contact->getAttributes ());
//        $dataProvider = $contact->search ();
//
//        $contact->asa ('ERememberFiltersBehavior')
//            ->unsetFiltersNotIn (array ('firstName', 'lastName'));
//        $filters = $contact->asa ('ERememberFiltersBehavior')->getSetting ('filters');
//        unset ($_GET['Contacts']['email']);
//        VERBOSE_MODE && print_r ($filters);
//        $this->assertEquals ($filters, $_GET['Contacts']);
//    }
//
//    /**
//     * Set sort order, then try unsetting it
//     */
//    public function testUnsetSortOrderIfNotIn () {
//        $_SESSION = array ();
//
//        $_GET['Contacts'] = array (
//            'firstName' => 'test',
//            'lastName' => 'test',
//            'email' => 'test@test.com',
//        );
//        $_GET["Contacts_sort"] = 'firstName.desc';
//        $contact = new Contacts ('search');
//        $dataProvider = $contact->search ();
//        $dataProvider->asa ('SmartDataProviderBehavior')
//            ->unsetSortOrderIfNotIn (array ('lastName', 'email', 'firstName'));
//        $sort = $contact->asa ('ERememberFiltersBehavior')->getSetting ('sort');
//        $this->assertEquals ($sort, $_GET['Contacts_sort']);
//        $dataProvider->asa ('SmartDataProviderBehavior')
//            ->unsetSortOrderIfNotIn (array ('lastName', 'email'));
//        $sort = $contact->asa ('ERememberFiltersBehavior')->getSetting ('sort');
//        $this->assertEquals ('', $sort);
//    }
}
?>
