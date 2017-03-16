<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/* @edition:pla */

Yii::import('application.models.embedded.Api2Settings');

/**
 * @package application.tests.unit.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2SettingsTest extends X2TestCase {

    public function testBanIp() {
        $s = new Api2Settings;
        $s->banIP('127.0.0.1');
        $s->banIP('192.168.1.1');
        $s->banIP('127.0.0.1');
        $this->assertEquals('127.0.0.1,192.168.1.1',$s->ipBlacklist);
    }

    public function testBruteforceExempt() {
        $s = new Api2Settings;
        $s->ipWhitelist = '127.0.0.1,192.168.1.1';
        $s->exemptWhitelist = false;
        $this->assertFalse($s->bruteforceExempt('192.168.1.1'));
        $this->assertFalse($s->bruteforceExempt('10.0.0.0'));
        $s->exemptWhitelist = true;
        $this->assertTrue($s->bruteforceExempt('192.168.1.1'));
        $this->assertFalse($s->bruteforceExempt('10.0.0.0'));
    }

    public function testInList() {
        $s = new Api2Settings;
        $s->ipBlacklist = '127.0.0.1,192.168.1.1,10.0.0.0';
        foreach(explode(',',$s->ipBlacklist) as $ip) {
            $this->assertTrue($s->inBlacklist($ip));
        }
    }

    public function testIsIpBlocked() {
        $s = new Api2Settings;
        $s->whitelistOnly = false;
        $s->ipBlacklist = '127.0.0.1,192.168.1.1';
        $s->ipWhitelist = '192.168.1.17';
        // Blacklisted IPs should always be blocked. Non-blacklisted IPs should
        // not be blocked if "whitelistOnly" is false.
        $this->assertTrue($s->isIpBlocked('192.168.1.1'));
        $this->assertFalse($s->isIpBlocked('192.168.1.171'));
        // Non-whitelisted IPs should be blocked if "whitelist only" is enabled
        // and the whitelist is empty:
        $s->whitelistOnly = true;
        $this->assertFalse($s->isIpBlocked('192.168.1.17'));
        $this->assertTrue($s->isIpBlocked('192.168.1.171'));
    }
}

?>
