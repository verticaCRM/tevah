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

/**
 * Advanced REST API settings
 * 
 * @package application.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2Settings extends JSONEmbeddedModel {

    public $enabled = true;
    public $disableLegacy = false;
    public $rawInput = false;
    public $maxNHooks = 3;
    public $hookTimeout = 3;
    public $maxPageSize = null;
    public $maxRequests = 0;
    public $requestInterval = 0;
    public $maxAuthFail = 0;
    public $lockoutTime = 0;
    public $permaBan = false;
    public $exemptWhitelist = true;
    public $whitelistOnly = false;
    public $ipWhitelist = '';
    public $ipBlacklist = '';

    public function attributeLabels(){
        return array(
            'enabled' => Yii::t('admin','Enable API access'),
            'disableLegacy' => Yii::t('admin','Disable the legacy API'),
            // Data I/O settings
            'rawInput' => Yii::t('admin','Enable raw input'),
            // Hooks settings
            'hookTimeout' => Yii::t('admin','Hook Timeout'),
            'maxNHooks' => Yii::t('admin','Maximum number of API hooks'),
            // Rate limiting settings
            'maxPageSize' => Yii::t('admin','Maximum page size'),
            'maxRequests' => Yii::t('admin','Maximum API requests per interval'),
            'requestInterval' => Yii::t('admin','Interval length'),
            // Security settings
            'maxAuthFail' => Yii::t('admin','Maximum authentication failures'),
            'lockoutTime' => Yii::t('admin','Lock out time'),
            'permaBan' => Yii::t('admin','Permanent lock out'),
            'exemptWhitelist' => Yii::t('admin','White-listed hosts exempt'),
            'whitelistOnly' => Yii::t('admin','Allow white-listed hosts only'),
            'ipWhitelist' => Yii::t('admin','Client IP address whitelist'),
            'ipBlacklist' => Yii::t('admin','Client IP address blacklist'),
        );
    }

    /**
     * Append an IP address to the blacklist
     * @param type $ip
     */
    public function banIP($ip) {
        if(!empty($this->ipBlacklist)){
            if(!$this->inBlacklist($ip)){
                $this->ipBlacklist .= ','.trim($ip);
            }
        }else{
            $this->ipBlacklist = trim($ip);
        }
    }

    /**
     * Returns whether the IP address is exempt from authentication failure limits
     * @param type $ip
     * @return boolean
     */
    public function bruteforceExempt($ip) {
        return $this->exemptWhitelist && $this->inWhitelist($ip);

    }

    /**
     * Returns whether the IP is in the blacklist
     */
    public function inBlacklist($ip) {
        return $this->inList($ip,'black');
    }

    /**
     * Returns true or false based on whether an IP address is in a list.
     * @param type $ip IP address
     * @param type $bw "black" or "white"; which list to compare against
     * @return type
     */
    public function inList($ip,$bw) {
        $attr = 'ip'.ucfirst($bw).'list';
        return in_array($ip,array_map('trim',explode(',',$this->$attr)));
    }

    /**
     * Returns whether the IP is in the whitelist
     */
    public function inWhitelist($ip) {
        return $this->inList($ip,'white');
    }

    /**
     * Returns whether to block an IP address.
     * @param type $ip
     */
    public function isIpBlocked($ip) {
        return $this->inBlacklist($ip)
                || (
                    $this->whitelistOnly
                    && !empty($this->ipWhitelist)
                    && !$this->inWhitelist($ip)
                );
    }

    public function modelLabel(){
        return Yii::t('admin', 'REST API settings');
    }

    public function renderInputs(){
        Yii::import('application.controllers.Api2Controller');
        $labelOpts = function($opts){
            $opts = array_merge($opts, array(
                'style' => 'margin-right:5px;display: inline-block;'
            ));
            $opts['for'] = $opts['name'];
            unset($opts['name']);
            return $opts;
        };
        $numberFieldOpts = function($opts){
            return array_merge($opts, array(
                'style' => 'max-width:50px',
                'min' => 0,
            ));
        };

        $nameOpts = $this->htmlOptions('enabled');
        echo CHtml::activeLabel($this, 'enabled', $labelOpts($nameOpts));
        echo CHtml::activeCheckBox($this, 'enabled', $nameOpts);
        echo "<br />";
        $nameOpts = $this->htmlOptions('disableLegacy');
        echo CHtml::activeLabel($this, 'disableLegacy', $labelOpts($nameOpts));
        echo CHtml::activeCheckBox($this, 'disableLegacy', $nameOpts);
        echo '&nbsp;'.X2Html::hint(Yii::t('admin', 'This is not recommended. The '
                . 'legacy API still serves a number of important functions '
                . 'including VoIP notifications, email dropbox, and cron tasks '
                . 'via web request. If none of these are needed, it can be '
                . 'disabled for extra security.')).'<br />';
        echo "</br /><br />";

        $nameOpts = $this->htmlOptions('rawInput');
        echo "<strong>".Yii::t('admin','Data Format')."</strong><hr />";
        echo CHtml::activeLabel($this, 'rawInput', $labelOpts($nameOpts));
        echo CHtml::activeCheckBox($this, 'rawInput', $nameOpts);
        echo '&nbsp;'.X2Html::hint(Yii::t('admin', 'If enabled, any user in '
                . 'the X2Engine system will be able to send data to the server '
                . 'and have it go directly into persistent storage, verbatim. '
                . 'Otherwise, all data is first transformed as if submitted '
                . 'from a form inside X2Engine. This requires the data be '
                . 'properly formatted for entry. It also disables certain key '
                . 'model behaviors, such as automatically setting timestamp '
                . 'fields for creation and time updated. Note, this will '
                . 'allow users who know their API key to completely circumvent '
                . 'field-level permissions via API requests.')).'<br />';
        echo "</br /><br />";

        // API hooks (push data requests)
        echo "<strong>".Yii::t('admin','API Pull Requests (Hooks)')."</strong><hr />";
        $nameOpts = $this->htmlOptions('maxNHooks');
        echo CHtml::activeLabel($this, 'maxNHooks', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'Maximum number of outgoing requests '
                . 'that can be made as part of an integration with a third'
                . '-party service implementing API hooks in X2Engine as opposed '
                . 'to polling for pushing data.'))."<br />";
        echo CHtml::activeNumberField($this, 'maxNHooks', $numberFieldOpts($nameOpts));
        echo "<br />";
        $nameOpts = $this->htmlOptions('hookTimeout');
        echo CHtml::activeLabel($this, 'hookTimeout', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'When sending a request to pull data '
                . 'from X2Engine (or directly sending a payload), wait this '
                . 'number of seconds for a response.'))."<br />";
        echo CHtml::activeNumberField($this, 'hookTimeout', $numberFieldOpts($nameOpts));
        echo "</br /></br />";

        // Rate limiting
        echo "<strong>".Yii::t('admin','Rate Limiting')."</strong><hr />";
        $nameOpts = $this->htmlOptions('maxPageSize');
        echo CHtml::activeLabel($this, 'maxPageSize', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'Maximum number of records that can be '
                        .'retrieved in a single API query, i.e. a search over '
                        .'Contacts. If left empty, this defaults to {default}', array(
                    '{default}' => Api2Controller::MAX_PAGE_SIZE
        ))).'<br />';
        echo CHtml::activeNumberField($this, 'maxPageSize', $numberFieldOpts($nameOpts));
        echo "<br />";
        $nameOpts = $this->htmlOptions('maxRequests');
        echo CHtml::activeLabel($this, 'maxRequests', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'The maximum number of API requests '
                        .'that can be made from any single source IP address. '
                        .'Zero implies an unlimited number of requests can be made.'))
                .'<br />';
        echo CHtml::activeNumberField($this, 'maxRequests', $numberFieldOpts($nameOpts));
        echo "<br />";
        $nameOpts = $this->htmlOptions('requestInterval');
        echo CHtml::activeLabel($this, 'requestInterval', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'The time (in seconds) over which to '
                        .'count API requests. Zero implies no reset time.')).'<br />';
        echo CHtml::activeNumberField($this, 'requestInterval', $numberFieldOpts($nameOpts));
        echo "<br /></br />";

        // Security
        echo "<strong>".Yii::t('admin','Security')."</strong><hr />";
        $nameOpts = $this->htmlOptions('maxAuthFail');
        echo CHtml::activeLabel($this, 'maxAuthFail', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'The maximum number of authentication '
                        .'failures that can be made from any single source IP address. '
                        .'Zero implies an unlimited number of failures. Setting this '
                        .'to a sensible value may help protect your API against brute '
                        .'force attacks.')).'<br />';
        echo CHtml::activeNumberField($this, 'maxAuthFail', $numberFieldOpts($nameOpts));
        echo "<br />";
        $nameOpts = $this->htmlOptions('lockoutTime');
        echo CHtml::activeLabel($this, 'lockoutTime', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'The time (in seconds) to lock out a '
                        .'client that has made too many failed authentication attempts.'))
        .'<br />';
        echo CHtml::activeNumberField($this, 'lockoutTime', $numberFieldOpts($nameOpts));
        echo "<br />";
        $nameOpts = $this->htmlOptions('permaBan');
        echo CHtml::activeLabel($this, 'permaBan', $labelOpts($nameOpts));
        echo CHtml::activeCheckBox($this, 'permaBan', $nameOpts);
        echo '&nbsp;'.X2Html::hint(Yii::t('admin', 'Automatically append repeat '
                . 'authentication failure offenders to the IP address blacklist.'
                . ' Note, this setting supersedes the lock out time setting.'));
        echo "<br /><br />";
        $nameOpts = $this->htmlOptions('ipBlacklist');
        echo CHtml::activeLabel($this, 'ipBlacklist', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'A comma-delineated list of IP addresses '
                        .'to block from accessing the API.')).'<br />';
        echo CHtml::activeTextArea($this, 'ipBlacklist',$nameOpts);
        echo "<br />";
        $nameOpts = $this->htmlOptions('ipWhitelist');
        echo CHtml::activeLabel($this, 'ipWhitelist', $labelOpts($nameOpts));
        echo X2Html::hint(Yii::t('admin', 'A comma-delineated list of IP addresses '
                        .'to allow access to the API.')).'<br />';
        echo CHtml::activeTextArea($this, 'ipWhitelist', $nameOpts);
        echo "<br />";
        $nameOpts = $this->htmlOptions('exemptWhitelist');
        echo CHtml::activeLabel($this, 'exemptWhitelist', $labelOpts($nameOpts));
        echo CHtml::activeCheckBox($this, 'exemptWhitelist', $nameOpts);
        echo '&nbsp;'.X2Html::hint(Yii::t('admin', 'White-listed IP addresses should be '
                . 'exempt from the authentication failure limits.'));
        echo "<br /><br />";
        $nameOpts = $this->htmlOptions('whitelistOnly');
        echo CHtml::activeLabel($this, 'whitelistOnly', $labelOpts($nameOpts));
        echo CHtml::activeCheckBox($this, 'whitelistOnly', $nameOpts);
        echo '&nbsp;'.X2Html::hint(Yii::t('admin', 'Allow only white-listed IP addresses.'));
        echo '<br /><br />';
    }

    public function rules() {
        return array(
            array('maxPageSize,maxRequests,requestInterval,maxAuthFail,lockoutTime','numerical','integerOnly' => true,'allowEmpty'=>true,'min'=>0),
            array('hookTimeout,maxNHooks','numerical','integerOnly' => true,'allowEmpty'=>false,'min'=>0),
            array('ipWhitelist,ipBlacklist','safe'),
            array('enabled,rawInput,permaBan,exemptWhitelist,whitelistOnly,disableLegacy','boolean'),
        );
    }

}

?>
