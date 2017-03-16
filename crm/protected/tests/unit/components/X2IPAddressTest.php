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

class X2IPAddressTest extends X2TestCase {

    public function testWildcardToCidr() {
        $testNetworks = array(
            '192.168.1.*' => '192.168.1.0/24',
            '172.16.2.*' => '172.16.2.0/24',
            '10.10.*.*' => '10.10.0.0/16',
            '*.*.*.*' => '0.0.0.0/0',
        );
        foreach ($testNetworks as $wildcard => $cidr) {
            $this->assertEquals ($cidr, X2IPAddress::wildcardToCidr($wildcard));
        }
    }

    public function testSubnetContainsIp() {
        $validSubnetHostPairs = array(
            '192.168.1.0/24' => '192.168.1.21',
            '10.0.0.0/8' => '10.128.0.10',
            '10.0.0.0/8' => '10.0.0.100',
        );
        $invalidSubnetHostPairs = array(
            '192.168.1.0/24' => '10.0.0.21',
            '10.0.0.0/8' => '100.128.0.10',
        );
        foreach ($validSubnetHostPairs as $network => $host) {
            $this->assertTrue (X2IPAddress::subnetContainsIp($network, $host));
        }
        foreach ($invalidSubnetHostPairs as $network => $host) {
            $this->assertFalse (X2IPAddress::subnetContainsIp($network, $host));
        }
    }

}
?>
