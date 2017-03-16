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
 * Email delivery methods.
 *
 * @package application.components
 * @property Credentials $credentials (read-only) The SMTP account to use for
 *  delivery, if applicable.
 * @property array $from The sender of the email.
 * @property PHPMailer $mailer PHPMailer instance
 * @property Profile $userProfile Profile, i.e. for email sender and signature
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailDeliveryBehavior extends CBehavior {

    const DEBUG_EMAIL = 0;

    /**
     * Stores the email credentials, if an account has been defined and is used.
     * @var mixed
     */
    private $_credentials;

    /**
     * ID of the credentials record to use for SMTP authentication
     * @var integer
     */
    private $_credId = null;

    /**
     * @var array Sender address
     */
    private $_from;

    /**
     * Stores an instance of PHPMailer
     * @var PHPMailer
     */
    private $_mailer;

    /**
     * Stores value of {@link userProfile}
     * @var Profile
     */
    private $_userProfile;
        
    /**
     * @var array Status codes
     */
    public $status = array();


    /**
     * Parses a To, CC, or BCC header into an array compatible with PHPMailer.
     * 
     * Each element of the array corresponds to an email addressee; the first
     * element is the name, the second, the value.
     *
     * The special case of "LastName, FirstName" is covered (splitting on commas
     * will break in this case) is covered by using a bit of RegExp from an idea
     * shared here:
     * 
     * http://stackoverflow.com/a/2202489/1325798
     * 
     * @param type $header
     */
    public static function addressHeaderToArray($header,$ignoreInvalidAddresses=false) {
        // First, tokenize all pieces of the header to avoid splitting inside of
        // recipient names:
        preg_match_all('/"(?:\\\\.|[^\\\\"])*"|[^,\s]+/', $header, $matches);
        $tokenCount = 0;
        $values = array();
        foreach($matches[0] as $matchedPiece) {
            $piece = trim($matchedPiece,',');
            $token = "\{token_$tokenCount\}";
            $values[$token] = $piece;
            $tokenCount++;
        }
        $tokens = array_flip($values);
        $delimiter = '-&@&-'; // Something highly unlikely to ever appear in an email header
        $tokenizedHeader = str_replace(',',$delimiter,strtr($header,$tokens));
        $headerPieces = explode($delimiter,strtr($tokenizedHeader,$values));
        $headerArray = array();
        foreach($headerPieces as $recipient){
            $recipient = trim($recipient);
            if(empty($recipient))
                continue;
            $matches = array();
            $emailValidator = new CEmailValidator;

            // if it's just a simple email, we're done!
            if($emailValidator->validateValue($recipient)) {
                $headerArray[] = array('', $recipient);
            } elseif(strlen($recipient) < 255 && 

                preg_match('/^"?((?:\\\\"|[^"])*)"?\s*<(.+)>$/i', $recipient, $matches)){
                // otherwise, it must be of the variety <email@example.com> "Bob Slydel"

                // (with or without quotes)
                if(count($matches) == 3 && $emailValidator->validateValue($matches[2])){  
                    $headerArray[] = array($matches[1], $matches[2]);
                }else{
                    if (!$ignoreInvalidAddresses)
                        throw new CException(Yii::t('app', 'Invalid email address list.'));
                }
            }else{
                if (!$ignoreInvalidAddresses)
                    throw new CException(Yii::t('app', 'Invalid email address list:'.$recipient));
            }
        }
        return $headerArray;            
    }

    /**
     * Adds email addresses to a PHPMail object
     * @param type $phpMail
     * @param type $addresses
     */
    public function addEmailAddresses(&$phpMail, $addresses){

        if(isset($addresses['to'])){
            foreach($addresses['to'] as $target){
                if(count($target) == 2)
                    $phpMail->AddAddress($target[1], $target[0]);
            }
        } else{
            if(count($addresses) == 2 && !is_array($addresses[0])){ 
                // this is just an array of [name, address], not an array of arrays
                $phpMail->AddAddress($addresses[1], $addresses[0]); 
            }else{
                foreach($addresses as $target){ //this is an array of [name, address] subarrays
                    if(count($target) == 2)
                        $phpMail->AddAddress($target[1], $target[0]);
                }
            }
        }
        if(isset($addresses['cc'])){
            foreach($addresses['cc'] as $target){
                if(count($target) == 2)
                    $phpMail->AddCC($target[1], $target[0]);
            }
        }
        if(isset($addresses['bcc'])){
            foreach($addresses['bcc'] as $target){
                if(count($target) == 2)
                    $phpMail->AddBCC($target[1], $target[0]);
            }
        }
    }

    /**
     * @param int size
     * @throws Exception if size exceeds limit 
     * @return true if file size is acceptable
     */
    public function validateFileSize ($size) {
        if($size > (10 * 1024 * 1024)) { // 10mb file size limit
            throw new Exception(
                "Attachment '{$attachment['filename']}' exceeds size ".
                "limit of 10mb.");
        }
        return true;
    }

    /**
     * Perform the email delivery with PHPMailer.
     *
     * Any special authentication and security should take place in here.
     *
     * @param array $addresses This array must contain "to", "cc" and/or "bcc"
     *  keys, and values must be arrays of recipients. Each recipient is expressed
     *  as a 2-element array with the first element being the name, and the second
     *  the email address.
     * @throws Exception
     * @return array
     */
    public function deliverEmail($addresses, $subject, $message, $attachments = array()){
        if(YII_DEBUG && self::DEBUG_EMAIL) {
            // Fake a successful send
            /**/AuxLib::debugLog(
                'Faking an email delivery to address(es): '.var_export($addresses,1));
            return $this->status = $this->getDebugStatus();
        }

        $phpMail = $this->mailer;

        try{
            $this->addEmailAddresses($phpMail, $addresses);

            $phpMail->Subject = $subject;
            // $phpMail->AltBody = $message;
            $phpMail->MsgHTML($message);
            // $phpMail->Body = $message;
            // add attachments, if any
            if($attachments){
                foreach($attachments as $attachment){
                    $type = $attachment['type'];
                    switch ($type) {
                        case 'temp': // stored as a temp file?
                            $file = 'uploads/media/temp/'.$attachment['folder'].'/'.
                                $attachment['filename'];
                            if(file_exists($file)) // check file exists
                                if ($this->validateFileSize (filesize ($file))) {
                                    $phpMail->AddAttachment($file);
                                }
                            break;
                        case 'media': // stored in media library
                            $file = 'uploads/media/'.$attachment['folder'].'/'.
                                $attachment['filename'];
                            if(file_exists($file)) // check file exists
                                if ($this->validateFileSize (filesize ($file))) {
                                    $phpMail->AddAttachment($file);
                                }
                            break;
                        /* x2prostart */ 
                        case 'emailInboxes':
                            if ($this->validateFileSize (intval ($attachment['size']))) {
                                $phpMail->addStringAttachment (
                                    $attachment['string'], $attachment['filename'], 'binary',
                                    $attachment['mimeType']);
                            }
                            break;
                        /* x2proend */ 
                        default:
                            throw new CException ('Invalid attachment type');
                    }
                }
            }

            $phpMail->Send();

            // delete temp attachment files, if they exist
            if($attachments){
                foreach($attachments as $attachment){
                    $type = $attachment['type'];
                    if($type === 'temp'){
                        $file = 'uploads/media/temp/'.$attachment['folder'].'/'.
                            $attachment['filename'];
                        $folder = 'uploads/media/temp/'.$attachment['folder'];
                        if(file_exists($file))
                            unlink($file); // delete temp file
                        if(file_exists($folder))
                            rmdir($folder); // delete temp folder
                        TempFile::model()->deleteByPk($attachment['id']);
                    }
                }
            }

            $this->status['code'] = '200';
            $this->status['exception'] = null;
            $this->status['message'] = Yii::t('app', 'Email Sent!');
        }catch(Exception $e){
            $this->status['code'] = '500';
            $this->status['exception'] = $e;
            $this->status['message'] = $e->getMessage()." ".$e->getFile()." L".$e->getLine();
        }
        return $this->status;
    }

    /**
     * Getter for {@link credentials}
     * returns Credentials
     */
    public function getCredentials(){
        if(!isset($this->_credentials)){
            if($this->credId == Credentials::LEGACY_ID)
                $this->_credentials = false;
            else{
                $cred = Credentials::model()->findByPk($this->credId);
                $this->_credentials = empty($cred) ? false : $cred;
            }
        }
        return $this->_credentials;
    }

    public function getCredId() {
        return $this->_credId;
    }

    /**
     * Gets the status used when "faking" an email send.
     */
    public function getDebugStatus() {
        return require(implode(DIRECTORY_SEPARATOR,array(
            Yii::app()->basePath,
            'tests',
            'data',
            'marketing',
            'debugStatus.php'
        )));
    }

    /**
     * Getter for {@link from}
     * @return array
     */
    public function getFrom(){
        if(!isset($this->_from)) {
			if($this->credentials) {
				$this->_from = array(
					'name' => $this->credentials->auth->senderName,
					'address' => $this->credentials->auth->email
				);
			} else {
                if(empty($this->userProfile) ||
                        $this->userProfile->username === Profile::GUEST_PROFILE_USERNAME) {
                    // The application:
                    $this->_from = array(
                        'name' => Yii::app()->settings->appName,
                        'address' => Yii::app()->settings->emailFromAddr
                    );
                }else{
                    // Current acting user:
                    $this->_from = array(
                        'name' => $this->userProfile->fullName,
                        'address' => $this->userProfile->emailAddress
                    );
                }
            }
		}
        return $this->_from;
    }

    /**
     * Magic getter for {@link phpMailer}
     * @return \PHPMailer
     */
    public function getMailer(){
        if(!isset($this->_mailer)){
            require_once(
                realpath(Yii::app()->basePath.'/components/phpMailer/class.phpmailer.php'));

            // the true param means it will throw exceptions on errors, which we need to catch
            $phpMail = new PHPMailer(true); 
            $phpMail->CharSet = 'utf-8';

            $cred = $this->credentials;
            if($cred){ // Use an individual user email account if specified and valid
                $phpMail->IsSMTP();
                $phpMail->Host = $cred->auth->server;
                $phpMail->Port = $cred->auth->port;
                $phpMail->SMTPSecure = $cred->auth->security;
                if(!empty($cred->auth->password)&& $cred->auth->security!=''){
                    $phpMail->SMTPAuth = true;
                    $cred->auth->emailUser('user');
                    $phpMail->Username = $cred->auth->user;
                    $phpMail->Password = $cred->auth->password;
                }
                // Use the specified credentials (which should have the sender name):
                $phpMail->AddReplyTo($cred->auth->email, $cred->auth->senderName);
                $phpMail->SetFrom($cred->auth->email, $cred->auth->senderName);
                $this->from = array(
                    'address' => $cred->auth->email, 'name' => $cred->auth->senderName);
            }else{ // Use the system default (legacy method)
                switch(Yii::app()->settings->emailType){
                    case 'sendmail':
                        $phpMail->IsSendmail();
                        break;
                    case 'qmail':
                        $phpMail->IsQmail();
                        break;
                    case 'smtp':
                        $phpMail->IsSMTP();

                        $phpMail->Host = Yii::app()->settings->emailHost;
                        $phpMail->Port = Yii::app()->settings->emailPort;
                        $phpMail->SMTPSecure = Yii::app()->settings->emailSecurity;
                        if(Yii::app()->settings->emailUseAuth == 'admin'){
                            $phpMail->SMTPAuth = true;
                            $phpMail->Username = Yii::app()->settings->emailUser;
                            $phpMail->Password = Yii::app()->settings->emailPass;
                        }


                        break;
                    case 'mail':
                    default:
                        $phpMail->IsMail();
                }
                // Use sender specified in attributes/system (legacy method):
                $from = $this->from;
                if($from == null){ // if no from address (or not formatted properly)
                    if(empty($this->userProfile->emailAddress))
                        throw new Exception('Your profile doesn\'t have a valid email address.');

                    $phpMail->AddReplyTo(
                        $this->userProfile->emailAddress, $this->userProfile->fullName);
                    $phpMail->SetFrom(
                        $this->userProfile->emailAddress, $this->userProfile->fullName);
                } else{
                    $phpMail->AddReplyTo($from['address'], $from['name']);
                    $phpMail->SetFrom($from['address'], $from['name']);
                }
            }

            $this->_mailer = $phpMail;
        }
        return $this->_mailer;
    }

    /**
     * Magic getter for {@link userProfile}
     * @return Profile
     */
    public function getUserProfile(){
        if(!isset($this->_userProfile)){
            if(empty($this->_userProfile)){
                if(Yii::app()->params->noSession){
                    // As a last resort: use admin
                    $this->_userProfile = Profile::model()->findByPk(1);
                }else{
                    // By default: if no profile was defined, and it's in a web
                    // session, use the current user's profile.
                    $this->_userProfile = Yii::app()->params->profile;
                }
            }
        }
        return $this->_userProfile;
    }

    public function setCredId($value) {
        $this->_credId = $value;
    }

    public function setFrom($from){
        $this->_from = $from;
    }

    /**
     * Magic setter for {@link userProfile}
     * @param Profile $profile
     */
    public function setUserProfile(Profile $profile){
        $this->_userProfile = $profile;
    }

    public function testUserCredentials($email, $password, $server, $port, $security) {
        require_once(realpath(Yii::app()->basePath.'/components/phpMailer/class.phpmailer.php'));
        require_once(realpath(Yii::app()->basePath.'/components/phpMailer/class.smtp.php'));
        $phpMail = new PHPMailer(true);

        $phpMail->isSMTP();
        $phpMail->SMTPAuth = true;
        $phpMail->Username = $email;
        $phpMail->Password = $password;
        $phpMail->Host = $server;
        $phpMail->Port = $port;
        $phpMail->SMTPSecure = $security;

        try {
            $validCredentials = $phpMail->SmtpConnect();
        } catch(phpmailerException $error) {
            $validCredentials = false;
        }
        return $validCredentials;
    }

}

?>
