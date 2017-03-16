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
 * X2IPAddress helper component for useful IP address methods
 */

class X2IPAddress {
    /**
     * Convert a network using wildcard notation (192.168.1.*) to CIDR
     * @param string $network The network IP address
     * @return string The network address in CIDR notation, or NULL if it cannot be converted
     */
    public static function wildcardToCidr($network) {
        $cidrNetwork = array();
        $octets = explode('.', $network);
        $prefix = 0;
        foreach ($octets as $octet) {
            if ($octet === '*') {
                $cidrNetwork = array_pad ($cidrNetwork, 4, '0');
                $cidrNetwork = implode('.', $cidrNetwork);
                $cidrNetwork .= "/${prefix}";
                return $cidrNetwork;
            } else {
                $cidrNetwork[] = $octet;
            }
            $prefix += 8;
            if ($prefix > 32)
                break;
        }
    }

    /**
     * @param string $network The network IP address, in CIDR notation
     * @param string $host The host IP address
     */
    public static function subnetContainsIp($network, $host) {
        list($subnet, $prefix) = explode('/', $network);

        // Convert addresses to decimal
        $subnetLong = ip2long($subnet);
        $hostLong = ip2long($host);

        // Now calculate the subnet the host belongs to, given the prefix that we know
        $hostsSubnet = $hostLong & ~((1 << (32 - $prefix)) - 1);

        // Return whether the subnets match
        return $hostsSubnet === $subnetLong;
    }
}
?>
