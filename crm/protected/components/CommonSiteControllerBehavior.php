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
Yii::import('application.components.ThemeGenerator.LoginThemeHelper');
/**
 * For code shared between mobile and full app site controllers
 *
 * @package application.controllers
 */
class CommonSiteControllerBehavior extends CBehavior {

    /**
     * Displays the login page
     * @param object $formModel
     * @param bool $isMobile Whether this was called from mobile site controller
     */
    public function login (LoginForm $model, $isMobile=false){
            
        $model->attributes = $_POST['LoginForm']; // get user input data
        Session::cleanUpSessions();

        $ip = $this->owner->getRealIp();
        /* x2plastart */
        $this->verifyIpAccess ($ip);
        /* x2plaend */
        $userModel = $model->getUser();
        $isRealUser = $userModel instanceof User;
        $effectiveUsername = $isRealUser ? $userModel->username : $model->username;
        $isActiveUser = $isRealUser && $userModel->status == User::STATUS_ACTIVE;
        /* increment count on every session with this user/IP, to prevent brute force attacks 
           using session_id spoofing or whatever */
        Yii::app()->db->createCommand(
            'UPDATE x2_sessions SET status=status-1,lastUpdated=:time WHERE user=:name AND 
            CAST(IP AS CHAR)=:ip AND status BETWEEN -2 AND 0')
                ->bindValues(
                    array(':time' => time(), ':name' => $effectiveUsername, ':ip' => $ip))
                ->execute();

        $activeUser = Yii::app()->db->createCommand() // see if this is an actual, active user
                ->select('username')
                ->from('x2_users')
                ->where('username=:name AND status=1', array(':name' => $model->username))
                ->limit(1)
                ->queryScalar(); // get the correctly capitalized username

        if(isset($_SESSION['sessionId']))
            $sessionId = $_SESSION['sessionId'];
        else
            $sessionId = $_SESSION['sessionId'] = session_id();

        $session = X2Model::model('Session')->findByPk($sessionId);

        /* get the number of failed login attempts from this IP within timeout interval. If the 
        number of login attempts exceeds maximum, display captcha */
        $badAttemptsRefreshTimeout = 900;
        $maxFailedLoginAttemptsPerIP = 100;
        $maxLoginsBeforeCaptcha = 5;
        /* x2plastart */
        if (Yii::app()->contEd('pla')) {
            $badAttemptsRefreshTimeout = Yii::app()->settings->loginTimeout;
            $maxFailedLoginAttemptsPerIP = Yii::app()->settings->maxFailedLogins;
            $maxLoginsBeforeCaptcha = Yii::app()->settings->failedLoginsBeforeCaptcha;
        }
        /* x2plaend */
        $this->pruneTimedOutBans();
        $failedLoginRecord = FailedLogins::model()->findByPk ($ip);
        $badAttemptsWithThisIp = ($failedLoginRecord) ? $failedLoginRecord->attempts : 0;
        if ($badAttemptsWithThisIp >= $maxFailedLoginAttemptsPerIP) {
            throw new CHttpException (403, Yii::t('app',
                'You are not authorized to use this application'));
        }
        // if this client has already tried to log in, increment their attempt count
        if ($session === null) {
            $session = new Session;
            $session->id = $sessionId;
            $session->user = $model->getSessionUserName();
            $session->lastUpdated = time();
            $session->status = 0;
            $session->IP = $ip;
        } else {
            $session->lastUpdated = time();
            $session->user = $model->getSessionUserName();
        }

        if($isActiveUser === false){
            $model->verifyCode = ''; // clear captcha code
            $model->validate (); // validate captcha if it's being used
            $this->recordFailedLogin ($ip);
            $session->save();
            if ($badAttemptsWithThisIp + 1 >= $maxFailedLoginAttemptsPerIP) {
                throw new CHttpException (403, Yii::t('app',
                    'You are not authorized to use this application'));
            } else if ($badAttemptsWithThisIp >= $maxLoginsBeforeCaptcha - 1) {
                $model->useCaptcha = true;
                $model->setScenario('loginWithCaptcha');
                $session->status = -2;
            }
        }else{
            if($model->validate() && $model->login()){  // user successfully logged in
                /* x2plastart */
                $this->recordSuccessfulLogin ($activeUser, $ip);
                /* x2plaend */
                if($model->rememberMe){
                    foreach(array('username','rememberMe') as $attr) {
                        // Expires in 30 days
                        AuxLib::setCookie (CHtml::resolveName ($model, $attr), $model->$attr,
                            2592000);
                    }
                }else{
                    foreach(array('username','rememberMe') as $attr) {
                        // Remove the cookie if they unchecked the box
                        AuxLib::clearCookie(CHtml::resolveName($model, $attr));
                    }
                }

                // We're not using the isAdmin parameter of the application
                // here because isAdmin in this context hasn't been set yet.
                $isAdmin = Yii::app()->user->checkAccess('AdminIndex');
                if($isAdmin && !$isMobile) {
                    $this->owner->attachBehavior('updaterBehavior', new UpdaterBehavior);
                    $this->owner->checkUpdates();   // check for updates if admin
                } else
                    Yii::app()->session['versionCheck'] = true; // ...or don't

                $session->status = 1;
                $session->save();
                SessionLog::logSession($model->username, $sessionId, 'login');
                $_SESSION['playLoginSound'] = true;

                if(YII_DEBUG && EmailDeliveryBehavior::DEBUG_EMAIL)
                    Yii::app()->session['debugEmailWarning'] = 1;

                // if ( isset($_POST['themeName']) ) {
                //     $profile = X2Model::model('Profile')->findByPk(Yii::app()->user->id);
                //     $profile->theme = array_merge( 
                //         $profile->theme, 
                //         ThemeGenerator::loadDefault( $_POST['themeName'])
                //     );
                //     $profile->save();
                // }

                LoginThemeHelper::login();

                if ($isMobile) {
                    $cookie = new CHttpCookie('x2mobilebrowser', 'true'); // create cookie
                    $cookie->expire = time() + 31104000; // expires in 1 year
                    Yii::app()->request->cookies['x2mobilebrowser'] = $cookie; // save cookie
                    $this->owner->redirect($this->owner->createUrl('/mobile/site/home'));
                } else {
                    if(Yii::app()->user->returnUrl == '/site/index') {
                        $this->owner->redirect(array('/site/index'));
                    } else {
                        // after login, redirect to wherever
                        $this->owner->redirect(Yii::app()->user->returnUrl); 
                    }
                }


            } else{ // login failed
                $model->verifyCode = ''; // clear captcha code
                $this->recordFailedLogin ($ip);
                $session->save();
                if ($badAttemptsWithThisIp + 1 >= $maxFailedLoginAttemptsPerIP) {
                    throw new CHttpException (403, Yii::t('app',
                        'You are not authorized to use this application'));
                } else if ($badAttemptsWithThisIp >= $maxLoginsBeforeCaptcha - 1) {
                    $model->useCaptcha = true;
                    $model->setScenario('loginWithCaptcha');
                    $session->status = -2;
                }
            }
        }
        $model->rememberMe = false;
    }

    public function recordFailedLogin($ip) {
        $record = FailedLogins::model()->findByPk ($ip);
        if ($record) {
            $record->attempts++;
        } else {
            $record = new FailedLogins;
            $record->IP = $ip;
            $record->attempts = 1;
        }
        $record->lastAttempt = time();
        $record->save();
    }

    private function pruneTimedOutBans() {
        $badAttemptsRefreshTimeout = 900;
        /* x2plastart */
        if (Yii::app()->contEd('pla')) {
            $badAttemptsRefreshTimeout = Yii::app()->settings->loginTimeout;
        }
        /* x2plaend */
        Yii::app()->db->createCommand()
            ->delete ('x2_failed_logins', 'lastAttempt < :timeout', array(
                ':timeout' => time() - ($badAttemptsRefreshTimeout * 60)
            ));
    }

    /* x2plastart */
    public function recordSuccessfulLogin($user, $ip) {
        $this->pruneSuccessfulLogins();
        $sql = 'INSERT INTO x2_login_history (username, IP, timestamp) '.
               'VALUES (:user, :ip, :time)';
        $params = array(
            ':user' => $user,
            ':ip' => $ip,
            ':time' => time(),
        );
        Yii::app()->db->createCommand($sql)
            ->execute($params);
    }

    /**
     * Remove the oldest successful login record if there are more than the maximum
     */
    private function pruneSuccessfulLogins() {
        $max = Yii::app()->settings->maxLoginHistory;
        $count = Yii::app()->db->createCommand()
            ->select('count(*)')
            ->from('x2_login_history')
            ->queryScalar();
        if ($count >= $max) {
            $id = Yii::app()->db->createCommand()
                ->select('id')
                ->from('x2_login_history')
                ->order('timestamp ASC')
                ->queryScalar();
            Yii::app()->db->createCommand('DELETE FROM x2_login_history WHERE id = :id')
                ->execute(array(
                    ':id' => $id,
                ));
        }
    }

    /**
     * Enforce the applications whitelist/blacklist settings
     * @param string $ip IP Address
     */
    public function verifyIpAccess($ip) {
        if (empty($ip))
            return;
        $admin = Yii::app()->settings;
        if ($admin->accessControlMethod === 'blacklist') {
            if ($this->isBannedIp ($ip))
                throw new CHttpException (403, Yii::t('app',
                    'You are not authorized to use this application'));
        } else {
            if (!$this->isWhitelistedIp ($ip))
                throw new CHttpException (403, Yii::t('app',
                    'You are not authorized to use this application'));
        }
    }

    /**
     * Determine whether an IP address is in the blacklist
     * @param string $ip IP Address
     * @return boolean Whether the IP has been banned
     */
    public function isBannedIp($ip) {
        $admin = Yii::app()->settings;
        $json = $admin->ipBlacklist;
        $bannedIps = CJSON::decode ($json);
        if (!$bannedIps)
            return false;

        return $this->checkIpList($bannedIps, $ip);
    }

    /**
     * Determine whether an IP address is in the whitelist
     * @param string $ip IP Address
     * @return boolean Whether the IP is allowed
     */
    public function isWhitelistedIp($ip) {
        $admin = Yii::app()->settings;
        $json = $admin->ipWhitelist;
        $allowedIps = CJSON::decode ($json);
        // No whitelist available: allow connections to prevent lockout
        if (!$allowedIps)
            return true;

        return $this->checkIpList($allowedIps, $ip);
    }

    /**
     * Scan a list of IP addresses looking for a match
     * @param array $list List of IP addresses
     * @param string $ip IP address to search for
     */
    private function checkIpList($list, $ip) {
        foreach ($list as $address) {
            if (preg_match('|/|', $address)) {
                if (X2IPAddress::subnetContainsIp($address, $ip))
                    return true;
            } else {
                if ($address === $ip)
                    return true;
            }
        }
        return false;
    }
    /* x2plaend */
}

