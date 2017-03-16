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
/* @edition:pla */

Yii::import('application.components.CommonSiteControllerBehavior');

class CommonSiteControllerBehaviorTest extends X2TestCase {
    protected $testIps = array(
        '10.0.1.20',
        '10.0.4.40',
        '172.16.0.0/12',
        '10.1.0.0/16',
    );

    public function testIsBannedIp() {
        Yii::app()->settings->ipBlacklist = CJSON::encode ($this->testIps);
        $behaviorClass = new CommonSiteControllerBehavior;

        // Ensure IPs that are no banned return false
        $testAllowed = array(
            '',
            '127.0.0.1',
            '10.0.1.21',
            '10.2.1.1',
        );
        foreach ($testAllowed as $ip)
            $this->assertFalse ($behaviorClass->isBannedIp ($ip),
                "Failed to allow $ip");

        // Ensure banned IPs return true
        $testBans = array(
            '10.0.1.20',
            '172.16.0.60',
            '172.16.0.128',
            '10.1.121.10',
        );
        foreach ($testBans as $ip)
            $this->assertTrue ($behaviorClass->isBannedIp ($ip),
                "Failed to assert ban for $ip");
    }

    public function testIsWhitelistedIp() {
        Yii::app()->settings->ipWhitelist = CJSON::encode ($this->testIps);
        $behaviorClass = new CommonSiteControllerBehavior;

        // Ensure whitelisted IPs will be allowed
        $testAllowed = array(
            '10.0.1.20',
            '10.0.4.40',
            '10.1.120.5',
            '10.1.2.128',
            '172.16.12.143',
            '172.16.112.49',
        );
        foreach ($testAllowed as $ip)
            $this->assertTrue ($behaviorClass->isWhitelistedIp ($ip),
                "Failed to allow $ip");

        // Ensure IPs not whitelisted will be denied
        $testNotAllowed = array(
            '',
            '127.0.0.1',
            '10.0.1.21',
            '10.0.4.41',
            '10.2.14.4',
            '172.15.20.20',
        );
        foreach ($testNotAllowed as $ip)
            $this->assertFalse ($behaviorClass->isWhitelistedIp ($ip),
                "Failed to deny $ip");
    }

    public function testVeifyIpAccessWithBannedIp() {
        $testIps = array('10.0.1.20');
        Yii::app()->settings->accessControlMethod = 'blacklist';
        Yii::app()->settings->ipBlacklist = CJSON::encode ($testIps);
        $behaviorClass = new CommonSiteControllerBehavior;

        $this->setExpectedException ('CHttpException');
        $behaviorClass->verifyIpAccess ('10.0.1.20');
    }

    public function testVeifyIpAccessWithAllowedIp() {
        $testIps = array('10.0.1.20');
        Yii::app()->settings->accessControlMethod = 'whitelist';
        Yii::app()->settings->ipWhitelist = CJSON::encode ($testIps);
        $behaviorClass = new CommonSiteControllerBehavior;

        $behaviorClass->verifyIpAccess ('10.0.1.20');
        $this->assertTrue (true); // an exception shouldn't have been raised
    }
}
