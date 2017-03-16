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
 * Base class for all web tracker-related tests. Includes utility methods to facilitate the 
 * testing of web forms and web trackers.
 *
 * For these tests to function properly, it's necessary to add the following lines to
 * your hosts file:
 *
 * <test installation ip>    www.x2engingtestdomain.com
 * <test installation ip>    www2.x2enginetestdomain.com
 * <test installation ip>    www.x2enginetestdomain2.com
 *
 * With that hosts file configured, the following constants should be defined in your 
 * WebTestConfig.php file:
 *
 * define('TEST_BASE_URL_ALIAS_1','http://www.x2enginetestdomain.com/index-test.php/');
 * define('TEST_BASE_URL_ALIAS_2','http://www.x2enginetestdomain2.com/index-test.php/');
 * define('TEST_BASE_URL_ALIAS_3','http://www2.x2enginetestdomain.com/index-test.php/');
 * define('TEST_WEBROOT_URL_ALIAS_1','http://www.x2enginetestdomain.com/');
 * define('TEST_WEBROOT_URL_ALIAS_2','http://www.x2enginetestdomain2.com/');
 * define('TEST_WEBROOT_URL_ALIAS_3','http://www2.x2enginetestdomain.com/');
 *
 * @package application.tests.functional.modules.contacts
 * @requires OS Linux 
 */
abstract class WebTrackingTestBase extends X2WebTestCase {

    private static $_webTrackingTestBaseUrl;
    private static $_webTrackingTestWebrootUrl;
    protected static $skipAllTests = false;

    /**
     * Copy over all the test pages to the web root 
     */
    public static function setUpBeforeClass () {
        // ensure that a directory with the same name isn't already in the web root
        exec ('ls ../../', $output);
        if (TEST_BASE_URL_ALIAS_1 === '' ||
            TEST_BASE_URL_ALIAS_2 === '' ||
            TEST_BASE_URL_ALIAS_3 === '' ||
            TEST_WEBROOT_URL_ALIAS_1 === '' ||
            TEST_WEBROOT_URL_ALIAS_2 === '' ||
            TEST_WEBROOT_URL_ALIAS_3 === '') {

            VERBOSE_MODE && println ('Warning: tests are being aborted because the web tracking '.
                'test constants have not been properly configured.');
            self::$skipAllTests = true;
        } else if (in_array ('x2WebTrackingTestPages', $output)) {
            VERBOSE_MODE && println ('Warning: tests are being aborted because the directory '.
                '"x2WebTrackingTestPages" already exists in the webroot');
            self::$skipAllTests = true;
        } else {
            // copy over webscripts and perform replacement on URL tokens
            exec ('cp -rn webscripts/x2WebTrackingTestPages ../../');
            exec ('find ../../x2WebTrackingTestPages -type f', $files);
            // perform URL token replacements
            foreach ($files as $file) {
                $content = file_get_contents ($file);
                $content = preg_replace (
                    '/TEST_BASE_URL_ALIAS_1/', TEST_BASE_URL_ALIAS_1, $content);
                $content = preg_replace (
                    '/TEST_BASE_URL_ALIAS_2/', TEST_BASE_URL_ALIAS_2, $content);
                $content = preg_replace (
                    '/TEST_BASE_URL_ALIAS_3/', TEST_BASE_URL_ALIAS_3, $content);
                $content = preg_replace (
                    '/TEST_WEBROOT_URL_ALIAS_1/', TEST_WEBROOT_URL_ALIAS_1, $content);
                $content = preg_replace (
                    '/TEST_WEBROOT_URL_ALIAS_2/', TEST_WEBROOT_URL_ALIAS_2, $content);
                $content = preg_replace (
                    '/TEST_WEBROOT_URL_ALIAS_3/', TEST_WEBROOT_URL_ALIAS_3, $content);
                file_put_contents ($file, $content);
            }
        }
        parent::setUpBeforeClass ();
    }

    public function setUp () {
        if (self::$skipAllTests) {
            $this->markTestSkipped ();
        }
        parent::setUp ();
    }

    /**
     * Remove all the test pages that were copied over 
     */
    public static function tearDownAfterClass () {
        if (!self::$skipAllTests)
            exec ('rm -r ../../x2WebTrackingTestPages');
        parent::tearDownAfterClass ();
    }

    /**
     * Open a URI within the app
     * 
     * @param string $r_uri
     */
    public function openX2($r_uri) {
        return $this->open(TEST_BASE_URL_ALIAS_1 . $r_uri);
    }

    /**
     * Open a URI within the app
     * 
     * @param string $r_uri
     */
    public function openPublic($r_uri) {
        VERBOSE_MODE && print ('openPublic: '.TEST_WEBROOT_URL_ALIAS_1 . $r_uri."\n");
        return $this->open(TEST_WEBROOT_URL_ALIAS_1 . $r_uri);
    }

    /**
     * Cookies cannot be deleted in ie8 unless ie8 is visiting a page with the domain associated
     * with the cookies.
     */
    public function deleteAllVisibleCookies ($url='') {
        if ($this->isIE8 ()) {
            $this->open ($url);
        } 
        parent::deleteAllVisibleCookies ();
    }

    /**
     * During FF selenium tests, checking for indexedDB throws an error, but only outside of 
     * iframes. This causes exact fingerprint matches to fail.
     * @return bool true if checking indexedDB would cause an error, false otherwise 
     */
    protected function checkForIndexedDBError () {
        $this->storeEval (
            "try { window.indexedDB; false; } catch (e) { true; } ", 'error');
        return $this->getExpression ('${error}') === 'true';
    }

    /**
     * @return bool true if browser that's currently being used is ie8, false otherwise
     */
    protected function isIE8 () {
        $this->storeEval (
            "!!window.navigator.userAgent.match(/msie 8/i)", 'isIE8');
        return $this->getExpression ('${isIE8}') === 'true';
    }

    /**
     * @return bool true if browser that's currently being used is Opera, false otherwise
     */
    protected function isOpera () {
        $this->storeEval (
            "!!window.navigator.userAgent.match(/opera/i)", 'isOpera');
        return $this->getExpression ('${isOpera}') === 'true';
    }

    /* x2plastart */ 
    protected function setIdentityThreshold ($threshold) {
        $admin = Admin::model()->findByPk (1);
        $admin->identityThreshold = $threshold;
        return $admin->save ();
    }
    /* x2plaend */ 

    /**
     * Submits the web lead form and ensures successful submission
     */
    protected function submitWebForm ($formVersion='') {
        if ($formVersion === 'differentDomain') {
            $this->openPublic('x2WebTrackingTestPages/webFormTestDifferentDomain.html');
        } else if ($formVersion === 'differentSubdomain') {
            $this->openPublic('x2WebTrackingTestPages/webFormTestDifferentSubdomain.html');
        } else {
            $this->openPublic('x2WebTrackingTestPages/webFormTest.html');
        }

        // the waitFor condition doesn't seem to work on Opera, so just wait a fixed amount of time
        if ($this->isOpera ()) $this->pause (5000);
        $this->waitForCondition (
            "window.document.getElementsByName ('web-form-iframe').length && window.document.getElementsByName ('web-form-iframe')[0].contentWindow.document.readyState === 'complete'", 4000);

        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->click("css=#submit");

        // wait for iframe to load new page
        $this->waitForCondition (
            "window.document.getElementsByName ('web-form-iframe').length && window.document.getElementsByName ('web-form-iframe')[0].contentWindow.document.getElementById ('web-form-submit-message') !== null", 4000);
    }

    /**
     * To be called after submitWebForm to assert that contact was created by web form submission 
     * @return Contact the contact that was created
     */
    protected function assertContactCreated () {
        $contact = Contacts::model()->findByAttributes (array (
            'firstName' => 'test',
            'lastName' => 'test',
            'email' => 'test@test.com',
        ));
        $this->assertTrue ($contact !== null);
        VERBOSE_MODE && println (
            'contact created. new contact\'s tracking key = '.$contact->trackingKey);
        return $contact;
    }

    protected function clearContact () {
        Yii::app()->db->createCommand ('delete from x2_contacts where email="test@test.com"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_contacts
             where email="test@test.com"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Used in conjunction with assertWebActivityGeneration (). 
     * Clears web activity actions so that we can easily test later that a new web activity action
     * was generated
     */
    protected function clearWebActivity () {
        Yii::app()->db->createCommand ('delete from x2_actions where type="webactivity"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Used in conjunction with clearWebActivity (). Ensures that a web activity action was 
     * generated.
     */
    protected function assertWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity"')
             ->queryScalar ();
        VERBOSE_MODE && println ($newCount);
        $this->assertTrue ($newCount === '1');
    }

    /**
     * Visits page with web tracker on it and asserts that the contact is being tracked
     */
    public function assertWebTrackerTracksWithCookie () {
        $this->clearWebActivity ();

        // visit the page with the web tracker on it
        $this->openPublic('x2WebTrackingTestPages/webTrackerTest.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->pause (5000); // wait for database changes to enact
        $this->assertWebActivityGeneration ();
    }

    /**
     * Visits page with the legacy web tracker on it and asserts that the contact is being tracked
     */
    public function assertLegacyWebTrackerTracksWithCookie () {
        $this->clearWebActivity ();

        // visit the page with the web tracker on it
        $this->openPublic('x2WebTrackingTestPages/legacyWebTrackerTest.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->pause (5000); // wait for database changes to enact
        $this->assertWebActivityGeneration ();
    }

    /**
     * Used in conjunction with clearWebActivity (). Ensures that a web activity action was not
     * generated.
     */
    protected function assertNoWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity"')
             ->queryScalar ();
        VERBOSE_MODE && println ($newCount);
        $this->assertTrue ($newCount === '0');
    }

    /**
     * Visits page with web tracker on it and asserts that the contact is being note tracked
     */
    public function assertWebTrackerCannotTrackWithCookie () {
        $this->clearWebActivity ();

        // visit the page with the web tracker on it
        $this->openPublic('x2WebTrackingTestPages/webTrackerTest.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->pause (5000); // wait for database changes to enact
        $this->assertNoWebActivityGeneration ();
    }

}

?>
