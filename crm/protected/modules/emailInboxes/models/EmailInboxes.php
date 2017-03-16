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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_email_inboxes".
 *
 * @package application.modules.emailInboxes.models
 */
class EmailInboxes extends X2Model {

    public $supportsRelationships = false;

    // Cache messages for 10m
    const IMAP_CACHE_MSG_TIMEOUT = 10;

    const OVERVIEW_PAGE_SIZE = 50;

    // The currently selected folder
    private $_currentFolder = null;

    /**
     * @var EmailInboxes $_myEmailInbox
     */
    private $_myEmailInbox; 

    public static function getLogEmailDescription () {
         return Yii::t(
            'app', 'Log email to the action history of all related contacts (sender and all '.
            'recipients in To, Cc, and Bcc lists).');
    }

    public static function getAutoLogEmailsDescription () {
         return Yii::t(
            'app', 'Automatically log outbound emails to the action history of all related '.
            'contacts (sender and all recipients in To, Cc, and Bcc lists).');
    }

    // Possible MIME types
    public static $mimeTypes = array(
        "TEXT",
        "MULTIPART",
        "MESSAGE",
        "APPLICATION",
        "AUDIO",
        "IMAGE",
        "VIDEO",
        "OTHER",
    );

    // Inverse lookup of MIME type index
    public static $mimeTypeToIndex = array(
        "TEXT" => 0,
        "MULTIPART" => 1,
        "MESSAGE" => 2,
        "APPLICATION" => 3,
        "AUDIO" => 4,
        "IMAGE" => 5,
        "VIDEO" => 6,
        "OTHER" => 7,
    );

    public static $encodingTypes = array(
        '7BIT',
        '8BIT',
        'BINARY',
        'BASE64',
        'QUOTED-PRINTABLE',
        'OTHER',
    );

    /**
     * Mapping of operators to operand type (or null of operator takes no operand)
     */
    public static $searchOperators = array(
        'all' => null, // return all messages matching the rest of the criteria
        'answered' => null, // match messages with the \\answered flag set
        'bcc'       => "string", // match messages with "string" in the bcc: field
        'before'    => "date", // match messages with date: before "date"
        'body'      => "string", // match messages with "string" in the body of the message
        'cc'        => "string", // match messages with "string" in the cc: field
        'deleted' => null, // match deleted messages
        'flagged' => null,      
        'from'      => "string", // from "string" match messages with "string" in the from: field
        'keyword'   => "string", // keyword "string" match messages with "string" as a keyword
        // match messages with the \\flagged (sometimes referred to as important or urgent) flag 
        // set
        'new' => null, // match new messages
        'old' => null, // match old messages
        'on'        => "date", // match messages with date: matching "date"
        'recent' => null, // match messages with the \\recent flag set
        'seen' => null, // match messages that have been read (the \\seen flag is set)
        'since'     => "date", // match messages with date: after "date"
        'subject'   => "string", // match messages with "string" in the subject:
        //'fullText'  => "string", // non-imap operator used to search across all overview text
        'text'      => "string", // match messages with text "string"
        'to'        => "string", // match messages with "string" in the to:
        'unanswered' => null, // match messages that have not been answered
        'undeleted' => null, // match messages that are not deleted
        'unflagged' => null, // match messages that are not flagged
        'unkeyword' => null, 
        'unkeyword' => "string", // match messages that do not have the keyword "string"
        'unseen' => null, // match messages which have not been read yet
    );

    // Maximum number of attempts to reconnect
    public $maxRetries = 3;
     
    // IMAP Stream resource
    private $_imapStream;

    // Cached string in the form "{host:port/flags}"
    private $_mailboxString;

    // Associated email inbox credentials
    private $_credentials;

    // Whether the IMAP stream is open
    private $_open = false;

    // Number of messages in the current mailbox
    private $_numMessages;

    // Number of recent messages in the current mailbox
    private $_numUnread;

    // Cached list of folders for this mailbox
    private $_folders;

    public static function model($className=__CLASS__) { return parent::model($className); }

    /**
     * @var $_cacheSuffixes
     */
    private $_cacheSuffixesToTimeout = array (
        'search' => 30, // unfiltered email messages
        //'uids' => 30, // uids of emails in inbox
        //'filteredUids' => 30, // filtered uids of emails in inbox
        'folders' => 30,
        'quota' => 5,
        'messageCount' => 5, //  number of messages in inbox
    ); 

    public function getCacheTimeout ($suffix) {
        return 60 * $this->_cacheSuffixesToTimeout[$suffix] * (YII_DEBUG ? 10 : 1);
    }

    /**
     * Helper method to create consistent cache keys
     * @param string $suffix (optional) suffix to append to key
     * @return string Cache key for this mailbox and folder
     */
    public function getCacheKey($suffix) {
        if (!in_array ($suffix, array_keys ($this->_cacheSuffixesToTimeout))) {
            throw new CException ('invalid cache suffix: '.$suffix);
        }

        // Cache dataproviders as either shared, or distinct per user
        $user = ($this->shared ? "shared" : Yii::app()->user->name);
        $folderId = $this->id."_".$this->getCurrentFolder();
        $cacheKey = 'x2_mailbox_'.$user.'_'.$folderId.(isset ($suffix) ? '_'.$suffix : '');
        return $cacheKey;
    }

    public function getCache () {
        return Yii::app()->cache2;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() { return 'x2_email_inboxes'; }

    public function getAttributeLabels () {
        return array_merge (parent::getAttributeLabels (), array (
            'settings[logOutboundByDefault]' => 'Auto-log outbound emails'
        ));
    }

    public function behaviors() {
        $behaviors = array_merge(parent::behaviors(), array(
            'X2LinkableBehavior' => array(
                'class' => 'X2LinkableBehavior',
                'module' => 'emailInboxes',
                'autoCompleteSource' => null,
                'viewRoute' => '/emailInboxes/emailInboxes/index'
            ),
            'JSONFieldsDefaultValuesBehavior' => array(
                'class' => 'application.components.JSONFieldsDefaultValuesBehavior',
                'transformAttributes' => array(
                    'settings' => array(
                        'logOutboundByDefault'=>1,
                    ),
                ),
                'maintainCurrentFieldsOrder' => true
            ),
        ));
        return $behaviors;
    }

    /**
     * Clean up IMAP stream when finished
     */
    public function __destruct() {
        if ($this->isOpen())
            $this->close();
    }

    /**
     * @return credentials
     */
    public function getCredentials() {
        if (!isset($this->_credentials)) {
            $this->_credentials = Credentials::model()->findByPk($this->credentialId);
        }
        return $this->_credentials;
    }

    /**
     * @return string Name of email credentials 
     */
    public function getCredentialsName () {
        $credential = $this->getCredentials ();
        if ($credential) {
            return $credential->name;
        }
    }

    /**
     * Override parent method so that credentialId field can be handled specially 
     */
    public function renderInput ($fieldName, $htmlOptions = array ()) {
        if ($fieldName === 'credentialId') {
            return Credentials::selectorField (
                $this, 'credentialId', 'email', Yii::app()->user->id, array(), true);
        } else {
            return parent::renderInput ($fieldName, $htmlOptions);
        }
    }

    /**
     * Helper function to generate a dropdown list of search operators
     * and text field for search operator argument
     * @return string dropdown list HTML
     */
    public function renderSearchForm () {
        $searchCriteria = Yii::app ()->controller->getSearchCriteria ();

        $formModel = new EmailInboxesSearchFormModel;

        $formModel->setAttributes ($searchCriteria, false);
        $request = Yii::app()->getRequest ();
        $action = $request->pathInfo;
        $params = $request->restParams;
        unset ($params['lastUid']);
        unset ($params['EmailInboxesSearchFormModel']);
        $form = Yii::app()->controller->beginWidget ('EmailInboxesSearchForm', array (
            'formModel' => $formModel,
            'action' => array_merge (array ($action), $params),
            'htmlOptions' => array (
                'id' => 'email-search-form',
            ),
        ));

        echo CHtml::activeTextField ($formModel, 'text', array(
            'id' => 'email-search-box',
        ));
        echo 
            '<button title="'.CHtml::encode (Yii::t('emailInboxes', 'Search')).'"
              id="email-search-submit" class="x2-button email-search-button">
                '.X2Html::fa('fa-search fa-lg').'
             </button>';
        echo "<span id='open-advanced-search-form-button'
               title='".CHtml::encode (Yii::t('emailInboxes', 'Advanced search'))."'>
                <img src='".
                    Yii::app()->theme->getBaseUrl ().'/images/icons/Collapse_Widget.png'."'>
                </img>
            </span>";
        echo "<div id='advanced-search-form' class='form' style='display: none;'>";
        $form->renderInputs (array_keys ($formModel->attributeLabels ()));
        echo 
            '<button id="email-advanced-search-submit" class="x2-button email-search-button">'.
              Yii::t('emailInboxes', 'Search').'
             </button>';
        echo "</div>";
        Yii::app()->controller->endWidget ();
    }

    /**
     * Return the mailbox specification string in the form "{server:port/flags}".
     * @return string Mailbox string
     */
    public function getMailbox() {
        if (!isset($this->_mailboxString)) {
            $cred = $this->credentials;
            $mailboxString = "{".$cred->auth->imapServer.":".$cred->auth->imapPort."/imap";

            // Append flags to the host:port
            if (in_array($cred->auth->imapSecurity, array('ssl', 'tls')))
                $mailboxString .= "/".$cred->auth->imapSecurity;
            if ($cred->auth->imapNoValidate)
                $mailboxString .= "/novalidate-cert";
            $mailboxString .= "}";

            $this->_mailboxString = $mailboxString;
        }
        return $this->_mailboxString;
    }

    /**
     * Fetch the associated IMAP stream
     * @return resource|null The IMAP stream, or null if it does not exist
     */
    private $_configError = false;
    public function getStream() {
        if (!$this->_configError) {
            if ($this->isOpen())
                return $this->_imapStream;
            foreach (range(1, $this->maxRetries) as $i) {
                $this->open ($this->currentFolder);
                if ($this->isOpen())
                    return $this->_imapStream;
            }
        } 
        $this->_configError = true;
        // This call clears the error stack, preventing the yii error handler from triggering
        // TODO: enhance error reporting by parsing and displaying imap errors
        imap_errors (); 
        throw new EmailConfigException (Yii::t('emailInboxes', 
            "Failed to open IMAP connection. Please check your email configuration."));
    }

    /**
     * Initialize a mailbox and connect via IMAP
     * @return boolean Whether the IMAP connection was successfully initialized
     */
    public function open($mailbox = "INBOX") {
        $this->setCurrentFolder ($mailbox);
        $cred = $this->credentials;
        $this->_imapStream = @imap_open(
            $this->mailbox . $mailbox,
            $cred->auth->email,
            $cred->auth->password);
        if (is_resource($this->_imapStream)) {
            $this->_open = true;
            return true;
        }
        return false;
    }

    /**
     * Close an IMAP connection and expunge moved/deleted messages
     */
    public function close() {
        if ($this->isOpen ()) {
            imap_close($this->_imapStream, CL_EXPUNGE);
            $this->_open = false;
        }
    }

    /**
     * Check if the IMAP stream is currently connected
     * @return boolean Whether the IMAP stream is open
     */
    public function isOpen() {
        return ($this->_open === true && is_resource($this->_imapStream) &&
            imap_ping($this->_imapStream));
    }

    /**
     * Check if this mailbox is associated with a GMail account
     */
    public function isGmail() { return ($this->credentials->modelClass === "GMailAccount"); }

    /**
     * Retrieve the mailbox status. This returns an object with the
     * properties: messages, recent, unseen, uidnext, and uidvalidity,
     * or false if the mailbox does not exist
     * @return object|false
     */
    public function status() {
        return imap_status($this->stream, $this->mailbox.$this->currentFolder, SA_ALL);
    }

    /**
     * Get the number of messages in the current mailbox
     * @return int Total number of messages
     */
    public function numMessages() {
        if (!isset($this->_numMessages))
            $this->_numMessages = imap_num_msg($this->stream);
        return $this->_numMessages;
    }

    /**
     * Get the number of unread messages in the current mailbox
     * @return int Number of unread messages
     */
    public function numUnread() {
        if (!isset($this->_numUnread)) {
            $status = $this->status();
            if ($status && isset($status->unseen))
                $this->_numUnread = $status->unseen;
        }
        return $this->_numUnread;
    }

    /**
     * Extract header overview information into an array
     * @param stdClass IMAP Header object
     * @return EmailMessage
     */
    public function parseHeader(stdClass $header) {
        $details = array();
        $headerAttributes = array('subject', 'from', 'to', 'date', 'uid', 'size', 'msgno');
        $headerFlags = array('seen', 'flagged', 'answered');

        foreach ($headerAttributes as $attr) {
            if (property_exists($header, $attr)) {
                $decodedHeader = $this->decodeHeader ($header->$attr);
                if ($attr === 'date') $decodedHeader = strtotime ($decodedHeader);
                $details[$attr] = $decodedHeader;
            }
        }
        foreach ($headerFlags as $flag) {
            if (property_exists($header, $flag))
                $details[$flag] = (isset($header->$flag) && $header->$flag)? true : false;
        }
        $email = new EmailMessage ($this, $details);
        return $email;
    }

    /**
     * Extract additional header info from message. Includes full to, cc, and reply_to fields
     * @param int $uid Unique ID of the message
     * @return array Additional header information
     */
    public function parseFullHeader($uid) {
        $headerInfo = array();
        $rawHeader = @imap_fetchheader($this->stream, $uid, FT_UID);
        if (!$rawHeader)
            throw new CHttpException(400, Yii::t("emailInboxes", "Invalid IMAP message id"));
        $header = imap_rfc822_parse_headers($rawHeader);
        foreach (array('to', 'cc', 'reply_to') as $type) {
            $collection = array();
            if (!property_exists($header, $type))
                continue;

            // If it is a single entry, just save it
            if (!is_array($header->$type)) {
                $headerInfo[$type] = $header->$type;
                continue;
            }

            // Otherwise, concatenate the entries
            foreach ($header->$type as $entry) {
                if (isset($entry->mailbox, $entry->host)) {
                    $email = $entry->mailbox."@".$entry->host;
                    if (isset($entry->personal))
                        $emailString = $this->decodeHeader($entry->personal)." <".$email.">";
                    else
                        $emailString = $email;
                    $collection[] = $emailString;
                }
            }
            $headerInfo[$type] = implode(', ', $collection);
        }
        return $headerInfo;
    }

    /**
     * Helper function to decode MIME header parts to the intended character set
     * @param string IMAP Header object
     * @return string Decoded header part
     */
    public function decodeHeader($header) {
        $result = "";
        $headerObj = imap_mime_header_decode ($header);
        foreach ($headerObj as $part) {
            $encoding = ($part->charset === 'default' ? "ISO-8859-1" : $part->charset);
            $text = mb_convert_encoding($part->text, 'UTF-8', $encoding);
            $result .= $text;
        }
        return $result;
    }

    /**
     * @return int the number of messages in the mailbox
     */
    private $_messageCount;
    public function getMessageCount () {
        if (isset ($this->_messageCount)) return $this->_messageCount;

        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('messageCount');
        $messageCount = $cache->get ($cacheKey);
        if ($messageCount !== false) return $messageCount;
        $messageCount = imap_num_msg ($this->stream);
        $cache->set ($cacheKey, $messageCount, $this->getCacheTimeout ('messageCount')); 
        $this->_messageCount = $messageCount;
        return $messageCount;
    }

    /**
     * Updates the message count cache entry
     * @param int $count 
     */
    public function setMessageCount ($count) {
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('messageCount');
        $cache->set ($cacheKey, $count, $this->getCacheTimeout ('messageCount')); 
    }

    /**
     * Wrapper around imap_search which maintains separate caches for filtered and unfiltered 
     * results 
     */
//    public function imapSearch ($criteria=null) {
//        $cache = $this->getCache ();
//        $keySuffix = $criteria === null ? 'uids' : 'filteredUids';
//        $cacheKey = $this->getCacheKey ($keySuffix);
//        $uids = $cache->get ($cacheKey);
//        if ($uids instanceof UidsCacheEntry && $uids->searchString === $criteria) {
//            return $uids->uids;
//        } 
//        $uids = imap_search($this->stream, $criteria === null ? 'ALL' : $criteria, SE_UID);
//        $cache->set (
//            $cacheKey, $this->getUidsCacheEntry ($uids, $criteria),
//            $this->getCacheTimeout ($keySuffix));
//        return $uids;
//    }

    /**
     * Search the current IMAP inbox for messages matching the given query.
     * @param string|null $searchString Search criteria
     * @param bool $searchCacheOnly if true, inbox will only be searched if results are cached 
     * @return false|CArrayDataProvider false if $searchCacheOnly is true and messages aren't 
     *  cached
     */
    public function searchInbox (
        $searchString=null, $searchCacheOnly=false, $lastUid=null) {

        $dataProvider = false;
        $cache = $this->getCache ();
        $cacheSuffix = 'search';
        $lastCachedUid = null;
        $emails = null;
        $cacheKey = $this->getCacheKey ($cacheSuffix);
        $cacheEntry = $cache->get ($cacheKey);
       //AuxLib::debugLogR ('$searchString = ');
        //AuxLib::debugLogR ($searchString);

        if ($cacheEntry instanceof EmailsCacheEntry) {
       //AuxLib::debugLogR ('$cacheEntry->searchString = ');
        //AuxLib::debugLogR ($cacheEntry->searchString);
        }

        // check if cache is valid and extract emails array if it is
        if ($cacheEntry instanceof EmailsCacheEntry && 
            $cacheEntry->searchString === $searchString) {

            //AuxLib::debugLogR ($cacheEntry->expirationTime);
            //AuxLib::debugLogR (time ());
            //AuxLib::debugLogR (gettype ($cacheEntry->expirationTime));
            //AuxLib::debugLogR (gettype (time ()));

            if ($cacheEntry->expirationTime > time ()) {
                //AuxLib::debugLogR ('cache hit');
                $emails = $cacheEntry->emails;
                $uids = $cacheEntry->uids;
            } else {
                //AuxLib::debugLogR ('expired');
                $cache->delete ($cacheKey);
                $cacheEntry = new EmailsCacheEntry;
            }
        } else {
            $cacheEntry = new EmailsCacheEntry;
        }

        if (is_array ($emails)) {
            $expandCache = false;
            if ($lastUid !== null) {
                // check if requested overviews are in the cache and expand the cache if they 
                // aren't
                $uidCount = count ($uids);
                $indexOfLastUid = ArrayUtil::numericIndexOf ($lastUid, $uids); 
                if ($indexOfLastUid === false || 
                    $indexOfLastUid + self::OVERVIEW_PAGE_SIZE > count ($emails)) {

                    //AuxLib::debugLogR ('expanding cache');
                    $lastCachedUid = $uids[$uidCount - 1];
                    $expandCache = true;
                }
            }

            if (!$expandCache) {
                $dataProvider = $this->getOverviewDataProvider ($emails);
            }
        } else {
            $emails = array ();
        }

        if ($dataProvider) 
            return $dataProvider;
        if ($searchCacheOnly) 
            return false;

        if (!$this->isOpen()) {
            $this->open ($this->currentFolder);
        }

        if (!isset ($uids)) {
            
            // Fetch a list of headers
            $uids = imap_search (
                $this->stream, $searchString === null ? 'ALL' : $searchString, SE_UID);
            if (!$uids) {
                $uids = array ();
            } else {
                $uids = array_reverse ($uids);
            }
        }

        $this->setMessageCount (count ($uids));
       //AuxLib::debugLogR ('$uids = ');
        //AuxLib::debugLogR ($uids);

        $result = $this->overview ($uids, $lastCachedUid, $lastUid);
       //AuxLib::debugLogR ('$result = ');
        //AuxLib::debugLogR ($result);

        $_SESSION[$this->lastUidSessionKey] = $this->nextUid;

        // Iterate over headers to get an array of emails
        // TODO: Make this more efficient. To preserve message order, all cached messages are 
        // iterated over. As a result, this loop gets slower as the cache grows.
        if (is_array($result)) {
            $newEmails = array ();
            foreach ($result as $header) {
                $header = $this->parseHeader ($header);
                $newEmails[$header->uid] = $header;
            }
            foreach ($emails as $uid => $message) {
                $newEmails[$uid] = $message;
            }
            $emails = $newEmails;
        }

        $cacheEntry->uids = $uids;
        $cacheEntry->emails = $emails;
        $cacheEntry->searchString = $searchString;
        $cacheEntry->expirationTime = isset ($cacheEntry->expirationTime) ? 
            $cacheEntry->expirationTime : 
            time () + $this->getCacheTimeout ($cacheSuffix);

       //AuxLib::debugLogR ('$cacheEntry->expirationTime = ');
        //AuxLib::debugLogR ($cacheEntry->expirationTime);

        
        $cache->set ($cacheKey, $cacheEntry, $cacheEntry->expirationTime - time ());

        return $this->getOverviewDataProvider ($emails);
    }

//    private function getEmailsCacheEntry (
//        array $emails, array $uids, $searchString=null, $expirationTime=null) {
//        $cacheEntry = new EmailsCacheEntry;
//        $cacheEntry->emails = $emails;
//        $cacheEntry->uids = $uids;
//        $cacheEntry->searchString = $searchString;
//        $cacheEntry->expirationTime = 
//            $expirationTime === null ? 
//                time () + $this->getCacheTimeout ('search') : $expirationTime;
//        return $cacheEntry;
//    }
//
    /**
     * For each message specified, create an email action if none exists and associate it with
     * all its related contacts
     */
    public function logMessages ($uids) {
        $dataProvider = $this->searchInbox ();
        $rawData = $dataProvider->rawData;
        $errors = array ();
        $warnings = array ();
        foreach ($uids as $uid) {
            if (isset ($rawData[$uid])) {
                $message = $rawData[$uid];
                $contacts = $message->getAssociatedContacts ();

                if (!count ($contacts)) {
                    $warnings[] = Yii::t(
                        'emailInboxes',
                        'Message "{subject}" has no associated contacts', array (
                            '{subject}' => $message->subject,
                        ));
                    continue;
                }

                $message = $this->fetchMessage ($uid);
                $action = $message->getAction ();
                foreach ($contacts as $contact) {
                    $retVal = $action->multiAssociateWith ($contact);
                    if (!$retVal) { 
                        $errors[] = Yii::t(
                            'emailInboxes',
                            'Failed to associate message "{subject}" with {contact}', array (
                                '{subject}' => $message->subject,
                                '{contact}' => $contact->name,
                            )) ;
                    } else if ($retVal === -1) {
                        $warnings[] = Yii::t(
                            'emailInboxes',
                            'Message "{subject}" already associated with {contact}', array (
                                '{subject}' => $message->subject,
                                '{contact}' => $contact->name,
                            )) ;
                    }
                }
            } else {
                $errors[] = Yii::t('emailInboxes', 'Message not found') ;
            }
        }
        return array ($errors, $warnings);
    }

    /**
     * Retrieve latest messages since last time messages were fetched
     */
    public function fetchLatest() {
        $newEmails = array();
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('search');
        $emails = $cache->get ($cacheKey);
        if ($emails instanceof EmailsCacheEntry) {
            $emails = $emails->emails;
        }
        $dataProvider = $this->getOverviewDataProvider ($emails);
            
        if (!$dataProvider || 
            !isset($_SESSION[$this->lastUidSessionKey]) ||
            $_SESSION[$this->lastUidSessionKey] === null) {

            return $this->searchInbox ();
        }

        // Return if the next uid hasn't changed, otherwise retrieve the newest headers
        if ($_SESSION[$this->lastUidSessionKey] === $this->nextUid)
            return $dataProvider;
        $criteria = $_SESSION[$this->lastUidSessionKey].':'.$this->nextUid;

        $result = $this->overview ($criteria);

        $_SESSION[$this->lastUidSessionKey] = $this->nextUid;

        // Iterate over headers to append new emails to the data provider
        if (is_array($result))
            foreach ($result as $header) {
                $header = $this->parseHeader ($header);
                $dataProvider->rawData[$header->uid] = $header;
            }
        $cache->set ($cacheKey, $dataProvider->rawData, $this->getCacheTimeout ('search'));
        //return $dataProvider;
    }

    /**
     * Return the current folders quota settings in bytes
     * @return array used and total space for quota
     */
    public function getQuota() {
        if (!($quota = $this->getCache ()->get ($this->getCacheKey ('quota')))) {
            if ($this->credentials->auth->imapServer !== 'imap-mail.outlook.com' && 
                $this->credentials->auth->imapServer !== 'imap.mail.yahoo.com') {
                $quota = @imap_get_quotaroot($this->stream, $this->currentFolder);
            } else {
                $quota = null; 
            }
            if (is_array($quota) && array_key_exists('STORAGE', $quota)) {
                // Quota settings are returned in KB
                $used = $quota['STORAGE']['usage'] * 1024;
                $total = $quota['STORAGE']['limit'] * 1024;
                $quota = array($used, $total);
            } else {
                // If the STORAGE key does not exist, we Received an invalid response
                // from the server and were instead given an array of error information
                $quota = null;
            }
            $this->getCache ()->set (
                $this->getCacheKey ('quota'), $quota, $this->getCacheTimeout ('quota'));
        }
        return $quota;
    }

    /**
     * Return a human readable summary of the current folder's quota usage
     * in the format "used / total (percent%)"
     */
    public function getQuotaString() {
        $quota = $this->quota;
        if (is_array($quota) && count($quota) === 2) {
            $used = $quota[0];
            $total = $quota[1];
            $percent = sprintf('%.1f%%', 100 * ($used / $total));
            $used = FileUtil::formatSize($used);
            $total = FileUtil::formatSize($total);
            return "$used / $total ($percent)";
        }
    }

    /**
     * Create an AJAX link to select a folder
     * @param string $folder Folder name
     */
    public function renderFolderLink($folder) {
        $options = array(
            'class' => 'folder-link'.($folder === $this->getCurrentFolder () ? 
                ' current-folder' : ''),
            'data-folder' => CHtml::encode ($folder) 
        );
        if ($folder === "INBOX")
            $folder = "Inbox";
        return CHtml::link($folder, '#', $options);
    }

    /**
     * List available folders in a given mailbox
     * @return array Folder names
     */
    public function getFolders() {
        if (isset($this->_folders) && is_array($this->_folders))
            return $this->_folders;

        if (!($this->_folders = $this->getCache ()->get ($this->getCacheKey ('folders')))) {
            $this->_folders = array();
            $folderList = imap_list($this->stream, $this->mailbox, "*");
            if ($folderList) {
                // process $folderList to make it more user friendly
                foreach ($folderList as $folder) {
                    $folderName = mb_convert_encoding($folder, 'UTF-8', 'UTF7-IMAP');
                    $folderName = str_replace($this->mailbox, "", $folderName);
                    $this->_folders[] = $folderName;
                }
            }
            $this->getCache ()->set (
                $this->getCacheKey ('folders'), $this->_folders,
                $this->getCacheTimeout ('folders'));
        }

        return $this->_folders;
    }

    /**
     * Magic getter for the current folder saved in session
     */
    public function getCurrentFolder() {
        if (is_null($this->_currentFolder))
            $this->_currentFolder = "INBOX";
        return $this->_currentFolder;
    }

    /**
     * Magic setter to handle the current folder saved in session
     */
    public function setCurrentFolder($folder) {
        $this->_currentFolder = $folder;
    }

    /**
     * Reopen the IMAP connection with a different mailbox
     * @param string $mailbox
     */
    public function selectFolder($folder) {
        $this->setCurrentFolder ($folder);
        if ($this->isOpen())
            imap_reopen($this->_imapStream, $this->mailbox . $folder);
        else
            $this->open ($folder);
    }

    /**
     * Generate a sequence string from a list of ids
     * @param mixed $ids The list of IDs to form a sequence
     * @return string Comma separated list of IDs
     */
    public function sequence($ids) {
        if (!is_array($ids))
            $ids = array($ids);
        return implode(',', $ids);
    }

    /**
     * Mark the specified messages as read
     * @param int|array $uids The message UIDs to mark as read
     * @return bool true for success, false for failure
     */
    public function markRead($uids) { return $this->setFlag ($uids, '\\Seen'); }

    /**
     * Mark the specified messages as unread
     * @param int|array $uids The message UIDs to mark as unread
     * @return bool true for success, false for failure
     */
    public function markUnread($uids) { return $this->setFlag ($uids, '\\Seen', false); }

    /**
     * Mark the specified messages as unread
     * @param int|array $uids The message UIDs to mark as unread
     * @return bool true for success, false for failure
     */
    public function markImportant($uids) { return $this->setFlag ($uids, '\\Flagged'); }

    /**
     * Mark the specified messages as unread
     * @param int|array $uids The message UIDs to mark as unread
     * @return bool true for success, false for failure
     */
    public function markNotImportant($uids) { return $this->setFlag ($uids, '\\Flagged', false); }

    /**
     * Return the overview of specified messages
     * @param int|array $uids The message UIDs to retrieve overviews for
     * @param null|int $firstUid if set, only overviews of uids after firstUid will be fetched 
     * @param null|int $lastUId if set, overviews overview page size past lastUid will not
     *  be fetched
     * @return array message overviews
     */
    public function overview ($uids, $firstUid=null, $lastUid=null) {
       //AuxLib::debugLogR ('$firstUid = ');
        //AuxLib::debugLogR ($firstUid);
       //AuxLib::debugLogR ('$lastUid = ');
        //AuxLib::debugLogR ($lastUid);


        if (is_array ($uids)) {
            //AuxLib::debugLogR ('overview 1');
            if ($lastUid !== null) {
            //AuxLib::debugLogR ('overview 2');
                $indexOfLastUid = ArrayUtil::numericIndexOf ($lastUid, $uids);
                if ($indexOfLastUid) {
                    $endIndex = min (
                        count ($uids), $indexOfLastUid + self::OVERVIEW_PAGE_SIZE + 1);
                    $uids = array_slice ($uids, 0, $endIndex);
                }
            } else {
            //AuxLib::debugLogR ('overview 3');
                $uids = array_slice ($uids, 0, self::OVERVIEW_PAGE_SIZE);
            }
            if ($firstUid !== null) {
            //AuxLib::debugLogR ('overview 4');
                $indexOfFirstUid = ArrayUtil::numericIndexOf ($lastUid, $uids);
                if ($indexOfLastUid) {
                    $uids = array_slice ($uids, $indexOfFirstUid + 1);
                }
            }
        }

        $overview = imap_fetch_overview (
            $this->stream,
            $this->sequence ($uids),
            FT_UID);
        return $overview;
    }

    /**
     * Move the selected messages to the given folder
     * @param int|array $uids The message UIDs to move
     * @param string $folder Name of the target folder
     * @return bool true if imap move succeeded, false otherwise
     */
    public function moveMessages($uids, $folder) {
        $success = imap_mail_move ($this->stream, $this->sequence($uids), $folder, CP_UID);
        imap_expunge($this->stream);
        $this->updateCachedMailbox($uids, true);

        // Fetch lastest in target folder
        $lastFolder = $this->currentFolder;
        $this->selectFolder ($folder);
        $this->fetchLatest();
        $this->selectFolder ($lastFolder);
        return $success;
    }

    /**
     * Retrieve a specific message
     * @param int Unique ID of the message
     * @return array containing message information
     */
    public function fetchMessage($uid) {
        $message = $this->cacheMessage($uid);
        if (!$message->seen) {
            $message->seen = true;
            $this->updateCachedMailbox ($uid);
        }
        return $message;
    }

    /**
     * Retrieve a message from the cache if it exists, otherwise load
     * the message and cache it
     * @param int $uid Unique ID of the message
     */
    public function cacheMessage($uid) {
        $cacheKey = $this->getMsgCacheKey ($uid);
        $cache = $this->getCache ();
        $message = $cache->get($cacheKey);
        if ($message && is_array($message->attachments))
            return $message;

        // Fetch full header of the message
        $overview = $this->overview($uid);
        if (empty($overview))
            throw new CHttpException(404, Yii::t('emailInboxes',
                'Unable to retrieve the specified message'));
        $message = $this->parseHeader($overview[0]);
        $additionalHeaders = $this->parseFullHeader($uid);
        foreach ($additionalHeaders as $type => $value)
            $message->$type = $value;

        // Fetch message body and attachments
        $structure = imap_fetchstructure($this->stream, $uid, FT_UID);
        $this->parseMessageBody ($message, $structure);

        $cache->set($cacheKey, $message, 60 * self::IMAP_CACHE_MSG_TIMEOUT);
        return $message;
    }

    /**
     * Clear a message from the cache
     * @param int $uid Unique ID of the message to remove
     */
    public function invalidateCachedMessage($uid) {
        $cache = $this->getCache ();
        $cacheKey = $this->getMsgCacheKey ($uid);
        $cache->delete ($cacheKey);
    }

    /**
     * Invalidate the current mailbox from the cache
     */
    public function invalidateCachedMailbox($folder = null) {
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('search');
        // Replace folder in cache key if specified
        if (!is_null($folder))
            $cacheKey = preg_replace('/_[^_]*$/', '_'.$folder, $cacheKey);
        $cache->delete ($cacheKey);
    }

    /**
     * Update the cached data provider for the currently selected mailbox
     * @param int|array $ids Unique IDs of the messages to update
     * @param boolean $delete Whether to delete the message from the cached data provider
     * @return boolean whether the messages were successfully updated
     */
    public function updateCachedMailbox($uids, $delete = false) {
        if (!is_array($uids))
            $uids = array($uids);
        $cache = $this->getCache ();
        $cacheKey = $this->getCacheKey ('search');
        $emails = $cache->get ($cacheKey);
        if ($emails instanceof EmailsCacheEntry) {
            $emails = $emails->emails;
        }
        $dataProvider = $this->getOverviewDataProvider ($emails);
        if (!$dataProvider)
            return false;

        $rawData = $dataProvider->rawData;

        foreach ($uids as $uid) {
            if (isset ($rawData[$uid])) {
                if ($delete) {
                    $this->invalidateCachedMessage ($uid);
                    unset($dataProvider->rawData[$uid]);
                } else {
                    $this->invalidateCachedMessage ($uid);
                    $message = $this->cacheMessage ($uid);
                    $dataProvider->rawData[$uid] = $message;
                }
            }
        }
        $cache->set ($cacheKey, $dataProvider->rawData, $this->getCacheTimeout ('search'));
        return true;
    }

    /**
     * Helper method to create consistent cache keys for individual email messages
     * @return string Cache key for this mailbox and folder
     */
    public function getMsgCacheKey($uid) {
        $user = ($this->shared ? "shared" : Yii::app()->user->name);
        $folderId = $this->id."_".$this->currentFolder;
        $cacheKey = "x2_mailbox_".$user."_msg_".$folderId."_".$uid;
        return $cacheKey;
    }

    public function deleteCache () {
        $cache = $this->getCache ();
        foreach ($this->_cacheSuffixesToTimeout as $suffix => $timeout) {
            $cache->delete ($this->getCacheKey ($suffix));
        }
    }

    /**
     * Helper method to generate a session key unique to this mailbox and folder
     * for storing the last fetched message UID
     * @return string Session key for storing lastUid
     */
    public function getLastUidSessionKey() {
        return 'lastuid_'.$this->id.'_'.$this->currentFolder;
    }

    /**
     * Handle parsing a message structure to extract the message body and attachments
     */
    public function parseMessageBody(&$message, $structure) {
        list($body, $attachments) = $this->parseBodyPart ($message->uid, $structure);
        $message->attachments = $attachments;
        if (!empty($body['html'])) {
            $message->body = $body['html'];
            if (count($message->attachments) > 0)
                $message->parseInlineAttachments();
        } else {
            $message->body = $body['plain'];
        }
    }

    /**
     * Fetch and decode a part of the message body
     * @param int Unique ID of the email message
     * @param int Message part number
     * @return string Decoded body part
     */
    public function decodeBodyPart($uid, $structure, $part) {
        // Simple, non-multipart messages can be fetched with imap_body
        if (is_null($part))
            $message = imap_body($this->stream, $uid, FT_UID);
        else
            $message = imap_fetchbody($this->stream, $uid, $part, FT_UID);

        $encoding = self::$encodingTypes[ $structure->encoding ];
        switch ($encoding) {
            case '7BIT':
                // https://stackoverflow.com/questions/12682208/parsing-email-body-with-7bit-content-transfer-encoding-php
                //$lines = explode('\r\n', $message);
                //$words = explode(' ', $lines[0]);
                //if ($lines[0] === $words[0])
                //    $message = base64_decode($message);
            case '8BIT':
                $message = quoted_printable_decode(imap_8bit($message));   break;
            case 'BINARY':
                $message = imap_binary($message); break;
            case 'BASE64':
                $message = imap_base64($message); break;
            case 'QUOTED-PRINTABLE':
                $message = imap_qprint($message); break;
        }
        return $message;
    }

    /**
     * Return the MIME type of an IMAP message structure
     * @param object IMAP message structure
     * @return string Message structure MIME type
     */
    public function getStructureMimetype($structure, $attachment = false) {
        if (isset($structure->subtype))
            $structureEncoding = self::$mimeTypes[$structure->type] . "/" . $structure->subtype;
        else if ($attachment)
            $structureEncoding = 'APPLICATION/OCTET-STREAM';
        else
            $structureEncoding = 'TEXT/PLAIN';
        return strtoupper($structureEncoding);
    }

    /**
     * Delete a specific email by UID
     * @param int|array $uids The message UIDs to delete
     */
    public function deleteMessages ($uids) {
        if ($this->isGmail()) {
            imap_mail_move (
                $this->stream, $this->sequence($uids), '[Gmail]/Trash', CP_UID);
        } else {
            // TODO check for other possible "Trash" folders
            imap_delete($this->stream, $this->sequence($uids), FT_UID);
        }
        imap_expunge($this->stream);
        $this->updateCachedMailbox($uids, true);
        if ($this->isGmail())
            $this->invalidateCachedMailbox("[Gmail]/Trash");
    }

    /**
     * @return EmailInboxes The current user's personal email inbox 
     */
    public function getMyEmailInbox ($refresh=false) {
        if ($refresh || !isset ($this->_myEmailInbox)) {
            $this->_myEmailInbox = $this->findByAttributes (array (
                'shared' => 0,
                'assignedTo' => Yii::app()->user->getName (),
            ));
        }
        return $this->_myEmailInbox;
    }

    /**
     * @return bool true if current user's personal inbox is setup, false otherwise
     */
    public function myEmailInboxIsSetUp () {
        return intval (Yii::app()->db->createCommand ("
            select count(*) from 
            x2_email_inboxes
            where shared=0 and assignedTo=:username
        ")->queryScalar (array (
            ':username' => Yii::app()->user->getName (),
        ))) === 1;
    }

    /**
     * Helper function to set/clear a message flag
     * @return bool true for success, false for failure
     */
    private function setFlag($uids, $flag, $value = true) {
        if (!is_array($uids))
            $uids = array($uids);
        $operation = ($value ? 'imap_setflag_full' : 'imap_clearflag_full');
        if ($operation ($this->stream, $this->sequence($uids), $flag, ST_UID)) {
            $this->updateCachedMailbox ($uids);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Instantiates data provider of email headers to display in mailbox grid view
     * @param null|array $emails
     * @return CArrayDataProvider
     */
    private function getOverviewDataProvider ($emails) {
        if ($emails === null) return null;
        $dataProvider = new CArrayDataProvider($emails, array(
            'keyField' => 'uid',
            'sort' => array(
                'defaultOrder' => 'msgno DESC',
                'attributes' => array(
                    'msgno',
                    'subject',
                    'from',
                ),
            ),
            'pagination' => array(
                'class' => 'EmailInboxesPagination', 
                'pageSize' => 50,
                'messageCount' => $this->getMessageCount (),
            ),
        ));
        $dataProvider->getPagination ()->dataProvider = $dataProvider;
        return $dataProvider;
    }

    /**
     * Recursively handle message body parts
     * @return array (message body, attachments)
     */
    private function parseBodyPart($uid, $structure, $part = null) {
        $attachments = array();
        $body = array('plain' => '', 'html' => '');
        if (isset($structure->parts) && count($structure->parts) > 0 &&
                $structure->type === self::$mimeTypeToIndex['MULTIPART']) {
            // Recursively parse the multipart message
            foreach ($structure->parts as $i => $subPart) {
                $nextPart = is_null($part) ? ($i + 1) : $part.".".($i + 1);
                list($newBody, $newAttachments) = $this->parseBodyPart ($uid, $subPart, $nextPart);
                if (!empty($newBody['html']))
                    $body['html'] .= $newBody['html'];
                if (!empty($newBody['plain']))
                    $body['plain'] .= $newBody['plain'];
                $attachments = array_merge($attachments, $newAttachments);
            }
        } else {
            // Handle parsing body part
            $structureEncoding = $this->getStructureMimetype ($structure);
            if ($structureEncoding === 'TEXT/HTML') {
                $body['html'] = $this->decodeBodyPart ($uid, $structure, $part);
            } else if ($structureEncoding === 'TEXT/PLAIN') {
                $body['plain'] = $this->decodeBodyPart ($uid, $structure, $part);
            } else if (isset($structure->ifdisposition) && $structure->ifdisposition && 
                in_array($structure->disposition, array('ATTACHMENT', 'INLINE')) &&
                isset ($structure->dparameters)) {

                $filename = $structure->dparameters[0]->value;
                $size = $structure->bytes;
                $type = ($structure->disposition === 'ATTACHMENT' ? 'attachment' : 'inline');
                $mimeType = $this->getStructureMimetype($structure, true);
                $partNumber = is_null($part) ? 1 : $part;
                if (isset($structure->id))  {
                    // Retrieve inline attachment content ids
                    if (preg_match('/<(.*?)>/', $structure->id, $matches))
                        $cid = $matches[1];
                }

                // Save attachment info
                $attachments[] = array(
                    'filename' => $filename,
                    'cid' => isset($cid) ? $cid : null,
                    'part' => $partNumber,
                    'mimetype' => $mimeType,
                    'type' => $type,
                    'link' => CHtml::link($filename, array(
                        'downloadAttachment',
                        'uid' => $uid,
                        'part' => $partNumber,
                    )),
                );
            }
        }
        return array($body, $attachments);
    }

    /**
     * @param array $criteria email attributes values indexed by attribute name
     * @param array $emails 
     */
//    private function filterEmails (array $criteria, $emails) {
//        $filteredEmails = array ();
//        $searchOperators = self::$searchOperators;
//        foreach ($emails as $email) {
//            $meetsCriteria = true;
//            if (isset ($criteria['fullText'])) {
//                $fullText = implode (' ', array_values ($email->getAttributes (array (
//                    'uid', 'msgno', 'subject', 'from', 'to', 'cc', 'reply_to', 'body', 'date',
//                    'size', 'seen', 'flagged', 'answered',
//                ))));
//            }
//            foreach ($criteria as $operator => $val) {
//                $operandType = $searchOperators[$operator];
//                switch ($operator) {
//                    case 'fullText':
//                        if (stripos ($fullText, $val) === false) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                    case 'subject':
//                    case 'from':
//                    case 'to':
//                    case 'cc':
//                    case 'body':
//                        if (stripos ($email->$operator, $val) === false) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                    case 'seen':
//                    case 'flagged':
//                    case 'answered':
//                        if (!$email->$operator) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                    case 'unseen':
//                    case 'unflagged':
//                    case 'unanswered':
//                        $operator = preg_replace ('/^un/', '', $operator);
//                        if ($email->$operator) {
//                            $meetsCriteria = false;
//                        }
//                        break;
//                }
//                if (!$meetsCriteria) break;
//            }
//            if ($meetsCriteria) {
//                $filteredEmails[] = $email;
//            }
//        }
//        return $filteredEmails;
//    }

    /**
     * @return array of EmailInboxes visible to current user 
     */
    public function getVisibleInboxes () {
        $criteria = $this->getAccessCriteria ();
        $criteria->addCondition ('shared=1', 'AND');
        $criteria->addCondition ('shared=0 AND assignedTo=:username', 'OR');
        $criteria->params = array_merge ($criteria->params, array (
            ':username' => Yii::app ()->user->getName (),
        ));
        return $this->findAll ($criteria);
    }

    /**
     * @return array visible inbox names indexed by id
     */
    public function getTabOptions () {
        $visibleInboxes = $this->getVisibleInboxes ();
        return array_combine (array_map (function ($inbox) {
            return $inbox->id;
        }, $visibleInboxes), 
        array_map (function ($inbox) {
            return $inbox->name;
        }, $visibleInboxes));
    }

    /**
     * Return the next UID to use for this mailbox
     * @return int|null next available UID
     */
    public function getNextUid() {
        $status = $this->status();
        if (!isset ($status->uidnext)) return null;
        $nextUid = $status->uidnext;
        return $nextUid;
    }
}

/**
 * Thrown when imap stream cannot be opened 
 */
class EmailConfigException extends CException {}

class EmailsCacheEntry {

    /**
     * @param array $emails 
     */
    public $emails;

    /**
     * @var array $uids
     */
    public $uids; 

    /**
     * @param string $searchString 
     */
    public $searchString = null;

    /**
     * @var $expirationTime
     */
    public $expirationTime; 
}

//class UidsCacheEntry {
//     
//    /**
//     * @param array $uids 
//     */
//    public $uids;
//
//    /**
//     * @param string $searchString 
//     */
//    public $searchString = null;
//}
