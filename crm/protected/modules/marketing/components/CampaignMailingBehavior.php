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

Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.docs.models.*');

/**
 * Behavior class for email delivery in email marketing campaigns.
 *
 * Static methods are used for batch emailing; all non-static methods assume that
 * an individual email is being sent.
 *
 * @property Campaign $campaign Campaign model for the current email
 * @property boolean $isNewsletter True if sending to a newsletter list (not a contacts list)
 * @property X2List $list The list corresponding to the current campaign being operated on
 * @property X2ListItem $listItem List item of the
 * @property Contacts $recipient The contact of the current recipient that the
 *  email is being sent to. If it's not a campaign, but a newsletter, this will
 *  be an ad-hoc contact model with its email address set to that of the list
 *  item.
 * @package application.modules.marketing.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CampaignMailingBehavior extends EmailDeliveryBehavior {

    /**
     * Filename of lock file in protected/runtime, to signal that emailing is
     * already in progress and other processes should not attempt to send email
     * (as this may result in race conditions and duplicate emails)
     */
    const EMLLOCK = 'campaign_emailing.lock';

    /**
     * Error code for the bulk limit being reached
     */
    const STATE_BULKLIMIT = 1;

    /**
     * Error code for an email already sending.
     */
    const STATE_RACECOND = 2;

    /**
     * Error code for an item whose address has suddenly gone blank
     */
    const STATE_NULLADDRESS = 3;

    /**
     * Error code for an unsubscribed / do-not-email contact
     */
    const STATE_DONOTEMAIL = 4;

    /**
     * Error code for another email process beating us to the punch
     */
    const STATE_SENT = 5;

    /**
     * Stores the time that the batch operation started (when calling this
     * class' methods statically)
     * @var type
     */
    public static $batchTime;

    /**
     * @var Campaign The current campaign model being operated on
     */
    public $_campaign;

    /**
     * True if the campaign is getting sent to a web list (not corresponding to
     * contacts).
     * @var boolean
     */
    private $_isNewsletter;

    /**
     * List model corresponding to the campaign.
     * @var type X2List
     */
    private $_list;

    /**
     * List item model
     */
    private $_listItem;

    /**
     * Contact record corresponding to the recipient of the current mail being
     * delivered.
     * 
     * @var Contacts
     */
    private $_recipient;

    /**
     * Whether the campaign mailing process should halt as soon as possible
     * @var type 
     */
    public $fullStop = false;

    /**
     * The ID of the campaign list item corresponding to the current recipient.
     * @var integer
     */
    public $itemId;

    /**
     * Indicates whether the mail cannot be sent due to a recent change in the
     * list item or contact record.
     * @var boolean
     */
    public $stateChange = false;

    /**
     * Indicates the type of state change that should block email delivery.
     * This purpose is not relegated to {@link status} ("code" element) because
     * that array is intended for PHPMailer codes.
     * @var integer
     */
    public $stateChangeType = 0;


    /**
     * Whether the current email could not be delivered due to bad RCPT or something
     * that's not a critical PHPMailer error
     */
    public $undeliverable = false;

    /////////////////////////
    // INDEPENDENT METHODS //
    /////////////////////////
    //
    // Used whether bulk-sending or individually sending

    /**
     * Prepares the subject and body of a campaign email.
     *
     * Any and all features of the campaign that are dynamically added at the
     * last minute to the email body are added in this method right before
     * the sending of the email.
     *
     * Returns an array; 
     * 
     * First element: email subject
     * Second element: email body
     * Third element: unique ID assigned to the current email
     *
     * @param Campaign $campaign Campaign of the current email being sent
     * @param Contacts $contact Contact to whom the email is being sent
     * @param type $email
     * @return type
     * @throws Exception
     */
    public static function prepareEmail (Campaign $campaign, Contacts $contact) {
        $email = $contact->email;
        $now = time();
        $uniqueId = md5 (uniqid (mt_rand (), true));

        // Add some newlines to prevent hitting 998 line length limit in
        // phpmailer and rfc2821
        $emailBody = preg_replace('/<br>/', "<br>\n", $campaign->content);

        // Add links to attachments
        try{
            $attachments = $campaign->attachments;
            if(sizeof($attachments) > 0){
                $emailBody .= "<br>\n<br>\n";
                $emailBody .= '<b>'.Yii::t('media', 'Attachments:')."</b><br>\n";
            }
            foreach($attachments as $attachment){
                $media = $attachment->mediaFile;
                if($media){
                    if($file = $media->getPath()){
                        if(file_exists($file)){ // check file exists
                            if($url = $media->getFullUrl()){
                                $emailBody .= CHtml::link($media->fileName, $media->fullUrl).
                                    "<br>\n";
                            }
                        }
                    }
                }
            }
        }catch(Exception $e){
            throw $e;
        }

        // Insert unsubscribe link placeholder in the email body if there is
        // none already:
        if(!preg_match('/\{_unsub\}/', $campaign->content)){
            $unsubText = "<br/>\n-----------------------<br/>\n".
                Yii::t('marketing', 'To stop receiving these messages, click here').": {_unsub}";
            // Insert
            if(strpos($emailBody,'</body>')!==false) {
                $emailBody = str_replace('</body>',$unsubText.'</body>',$emailBody);
            } else {
                $emailBody .= $unsubText;
            }
        }

        if ($campaign->enableRedirectLinks) {
            // Replace links with tracking links
            $url = Yii::app()->controller->createAbsoluteUrl (
                'click', array ('uid' => $uniqueId, 'type' => 'click'));
            $emailBody = preg_replace_callback (
                '/(<a[^>]*href=")([^"]*)("[^>]*>)/', 
                function (array $matches) use ($url) {
                    return $matches[1].$url.'&url='.urlencode ($matches[2]).''.
                        $matches[3];
                }, $emailBody);
        }

        // Insert unsubscribe link(s):
        $unsubUrl = Yii::app()->createExternalUrl('/marketing/marketing/click', array(
            'uid' => $uniqueId,
            'type' => 'unsub',
            'email' => $email
                ));
        $emailBody = preg_replace(
            '/\{_unsub\}/', '<a href="'.$unsubUrl.'">'.Yii::t('marketing', 'unsubscribe').'</a>',
            $emailBody);

        // Replace attribute variables:
        $replacementParams = array(
            '{trackingKey}' => $uniqueId, // Use the campaign key, not the general contact key
        );
        // Get the assignee of the campaign, for signature replacement.
        $user = User::model()->findByAttributes(array('username' => $campaign->assignedTo));
        if($user instanceof User) {
            $replacementParams['{signature}'] = $user->profile->signature;
        } else {
            $replacementParams['{signature}'] = '';
        }
        // Replacement in body
        $emailBody = Docs::replaceVariables($emailBody, $contact, $replacementParams);
        // Replacement in subject
        $subject = Docs::replaceVariables($campaign->subject, $contact);

        // Add the transparent tracking image:
        $trackingImage = '<img src="'.Yii::app()->createExternalUrl('/marketing/marketing/click', array('uid' => $uniqueId, 'type' => 'open')).'"/>';
        if(strpos($emailBody,'</body>')!==false) {
            $emailBody = str_replace('</body>',$trackingImage.'</body>',$emailBody);
        } else {
            $emailBody .= $trackingImage;
        }
        
        return array($subject, $emailBody, $uniqueId);
    }

    public static function emailLockFile() {
        return implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'runtime',self::EMLLOCK));
    }

    public static function lockEmail($lock = true) {
        $lf = self::emailLockFile();
        if($lock)
            file_put_contents($lf,time());
        else
            unlink($lf);
    }

   /**
    * Mediates lockfile checking.
    */
    public static function emailIsLocked() {
        $lf = self::emailLockFile();
        if(file_exists($lf)) {
            $lock = file_get_contents($lf);
            if(time() - (int) $lock > 3600) { // No operation should take longer than an hour
                unlink($lf);
                return false;
            } else
                return true;
        }
        return false;
    }

    /**
     * For a given list ID, find all contact/list item entries such that sending
     * is possible, is permissible, or has happened.
     *
     * The criteria are:
     * - x2_list_item.listId matches the list being operated on
     * - x2_list_item.unsubscribed and x2_contacts.doNotEmail are both zero
     *  (contact has not specified that email is unwelcome)
     * - One of x2_list_item.emailAddress or x2_contacts.email is non-empty, so
     *  that there is actually an email address to send to
     *
     * @param integer $listId The ID of the list operating on
     * @param boolean $unsent Constrain (if true) the query to unsent entries.
     * @return array An array containing the "id", "sent" and "uniqueId" columns.
     */
    public static function deliverableItems($listId,$unsent = false) {
        $where = ' WHERE 
            i.listId=:listId
            AND i.unsubscribed=0
            AND (c.doNotEmail!=1 OR c.doNotEmail IS NULL)
            AND NOT ((c.email IS NULL OR c.email="") AND (i.emailAddress IS NULL OR i.emailAddress=""))';
        if($unsent)
            $where .= ' AND i.sent=0';
        return Yii::app()->db->createCommand('SELECT
            i.id,i.sent,i.uniqueId
            FROM x2_list_items AS i
            LEFT JOIN x2_contacts AS c ON c.id=i.contactId '.$where)
                    ->queryAll(true,array(':listId'=>$listId));
    }

    public static function recordEmailSent(Campaign $campaign, Contacts $contact){
        $action = new Actions;
        // Disable the unsightly notifications for loads of emails:
        $action->scenario = 'noNotif';
        $now = time();
        $action->associationType = 'contacts';
        $action->associationId = $contact->id;
        $action->associationName = $contact->firstName.' '.$contact->lastName;
        $action->visibility = $contact->visibility;
        $action->type = 'email';
        $action->assignedTo = $contact->assignedTo;
        $action->createDate = $now;
        $action->completeDate = $now;
        $action->complete = 'Yes';
        $action->actionDescription = '<b>'.Yii::t('marketing', 'Campaign').': '.$campaign->name."</b>\n\n"
                .Yii::t('marketing', 'Subject').": ".Docs::replaceVariables($campaign->subject, $contact)."\n\n".Docs::replaceVariables($campaign->content, $contact);
        if(!$action->save())
            throw new CException('Campaing email action history record failed to save with validation errors: '.CJSON::encode($action->errors));
    }

    /////////////////////////////////
    // INDIVIDUAL DELIVERY METHODS //
    /////////////////////////////////
    //
    // When used as a behavior, the class is geared towards sending individual
    // emails.

    /**
     * Campaign model for this e-mail
     * @return type
     */
    public function getCampaign() {
        return $this->_campaign;
    }

    /**
     * Credentials record to be used. Overrides {@link EmailDeliveryBehavior::sendAs}
     * in order to configure SMTP delivery.
     * @return type
     */
    public function getCredId() {
        return $this->campaign->sendAs;
    }

    public function getIsNewsletter() {
        if(!isset($this->_isNewsletter)) {
            $this->_isNewsletter = empty($this->listItem->contactId);
        }
        return $this->_isNewsletter;
    }

    /**
     * Getter for {@link list}
     */
    public function getList() {
        if(!isset($this->_list)) {
            $this->_list = $this->campaign->list;
        }
    }

    /**
     * Getter for {@link listItem}
     */
    public function getListItem() {
        if(!isset($this->_listItem)) {
            $this->_listItem = X2ListItem::model()->findByAttributes(array (
                'id' => $this->itemId,
            ));
        }
        return $this->_listItem;
    }

    /**
     * Getter for {@link recipient}
     * @return type
     */
    public function getRecipient() {
        if(!isset($this->_recipient)) {
            if(!empty($this->listItem->contact))
                $this->_recipient = $this->listItem->contact;
            else {
                // Newsletter
                $this->_recipient = new Contacts;
                $this->_recipient->email = $this->listItem->emailAddress;
            }
        }
        return $this->_recipient;
    }

    /**
     * One final check for whether the mail should be sent, and enable the
     * 'sending' flag.
     * 
     * This is a safeguard for the use case of batch emailing when a user
     * subscribes or a value in the database changes between loading the list
     * items to deliver and when the actual delivery takes place.
     * @return bool True if we're clear to send; false otherwise.
     */
    public function mailIsStillDeliverable() {
        // Check if the batch limit has been reached:
        $admin = Yii::app()->settings;
        if($admin->emailCountWillExceedLimit() && !empty($admin->emailStartTime)) {
            $this->status['code'] = 0;
            $t_now = time();
            $t_remain = ($admin->emailStartTime + $admin->emailInterval) - $t_now;
            $params = array();
            if($t_remain > 60) {
                $params['{units}'] = $t_remain >= 120 ? Yii::t('app','minutes') : Yii::t('app','minute');
                $params['{t}'] = round($t_remain/60);
            } else {
                $params['{units}'] = $t_remain == 1 ? Yii::t('app','second') : Yii::t('app','seconds');
                $params['{t}'] = $t_remain;
            }

            $this->status['message'] = Yii::t('marketing', 'The email sending limit has been reached.').' '.Yii::t('marketing','Please try again in {t} {units}.',$params);
            $this->fullStop = true;
            $this->stateChange = true;
            $this->stateChangeType = self::STATE_BULKLIMIT;
            return false;
        }

        // Sending flag check:
        //
        // Perform the update operation to flip the flag, and if zero rows were
        // affected, that indicates it's already sending.
        $sendingItems = Yii::app()->db->createCommand()
                ->update($this->listItem->tableName(), array('sending' => 1), 'id=:id AND sending=0', array(':id' => $this->listItem->id));
        // If no rows matched, the message is being sent right now.
        $this->stateChange = $sendingItems == 0;
        if($this->stateChange) {
            $this->status['message'] = Yii::t('marketing','Skipping {email}; another concurrent send operation is handling delivery to this address.',array('{email}'=>$this->recipient->email));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_RACECOND;
            return false;
        }

        // Additional checks
        //
        // Email hasn't been set blank:
        if($this->stateChange = $this->stateChange || $this->recipient->email == null) {
            $this->status['message'] = Yii::t('marketing','Skipping delivery for recipient {id}; email address has been set to blank.',array('{id}'=>$this->itemId));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_NULLADDRESS;
            return false;
        }

        // Contact unsubscribed suddenly
        if($this->stateChange = $this->stateChange || $this->listItem->unsubscribed!=0 || $this->recipient->doNotEmail!=0) {
            $this->status['message'] = Yii::t('marketing','Skipping {email}; the contact has unsubscribed.',array('{email}'=>$this->recipient->email));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_DONOTEMAIL;
            return false;
        }
        
        // Another mailing process sent it already:
        $this->listItem->refresh();
        if($this->stateChange = $this->stateChange || $this->listItem->sent !=0) {
            $this->status['message'] = Yii::t('marketing','Email has already been sent to {address}',array('{address}'=>$this->recipient->email));
            $this->status['code'] = 0;
            $this->stateChangeType = self::STATE_SENT;
            return false;
        }
        
        return true;
    }


    /**
     * Records the date of delivery and marks the list record with the unique id.
     *
     * This method will not just update the current list item; it selects all
     * list items if their email address and list ID are the same. This is to
     * avoid sending duplicate messages.
     *
     * If mail is non-deliverable, it should still be marked as sent but with a
     * null unique ID, to designate it as a bad email address.
     * 
     * @param type $uniqueId
     * @param bool $unsent If false, perform the opposite operation (mark as not
     *  currently sending).
     */
    public function markEmailSent($uniqueId,$sent = true) {
        $params = array(
            ':listId' => $this->listItem->listId,
            ':emailAddress' => $this->recipient->email,
            ':email' => $this->recipient->email,
            ':setEmail' => $this->recipient->email,
            ':id' => $this->itemId,
            ':sent' => $sent?time():0,
            ':uniqueId' => $sent?$uniqueId:null,
        );
        $condition = 'i.id=:id OR (i.listId=:listId AND (i.emailAddress=:emailAddress OR c.email=:email))';
        $columns = 'i.sent=:sent,i.uniqueId=:uniqueId,sending=0,emailAddress=:setEmail';
        Yii::app()->db->createCommand('UPDATE x2_list_items AS i LEFT JOIN x2_contacts AS c ON c.id=i.contactId SET '.$columns.' WHERE '.$condition)->execute($params);
    }

    /**
     * Send an email.
     */
    public function sendIndividualMail() {
        if(!$this->mailIsStillDeliverable()) {
            return;
        }
        $addresses = array(array('',$this->recipient->email));
        list($subject,$message,$uniqueId) = self::prepareEmail($this->campaign,$this->recipient);
        $this->deliverEmail($addresses, $subject, $message);
        if($this->status['code'] == 200) {
            // Successfully sent email. Mark as sent.
            $this->markEmailSent($uniqueId);
            if(!$this->isNewsletter) // Create action history records; sent to contact list
                self::recordEmailSent($this->campaign,$this->recipient);
            $this->status['message'] = Yii::t('marketing','Email sent successfully to {address}.',array('{address}' => $this->recipient->email));
        } else if ($this->status['exception'] instanceof phpmailerException) {
            // Undeliverable mail. Mark as sent but without unique ID, designating it as a bad address
            $this->status['message'] = Yii::t('marketing','Email could not be sent to {address}. The message given was: {message}',array(
                '{address}'=>$this->recipient->email,
                '{message}'=>$this->status['exception']->getMessage()
            ));
            if($this->status['exception']->getCode() != PHPMailer::STOP_CRITICAL){
                $this->undeliverable = true;
                $this->markEmailSent(null);
            }else{
                $this->fullStop = true;
                $this->markEmailSent(null,false);
            }
        } else if($this->status['exception'] instanceof phpmailerException && $this->status['exception']->getCode() == PHPMailer::STOP_CRITICAL) {
        } else {
            // Mark as "not currently working on sending"...One way or another, it's done.
            $this->listItem->sending = 0;
            $this->listItem->update(array('sending'));
        }

        // Keep track of this email as part of bulk emailing
        Yii::app()->settings->countEmail();

        // Update the last activity on the campaign
        $this->campaign->lastActivity = time();
        // Finally, if the campaign is totally done, mark as complete.
        if(count(self::deliverableItems($this->campaign->list->id, true)) == 0) {
            $this->status['message'] = Yii::t('marketing','All emails sent.');
            $this->campaign->active = 0;
            $this->campaign->complete = 1;
            $this->campaign->update(array('lastActivity','active','complete'));
        } else {
            $this->campaign->update(array('lastActivity'));
        }
    }

    public function setCampaign(Campaign $value) {
        $this->_campaign = $value;
    }


    //////////////////////////
    // BULK MAILING METHODS //
    //////////////////////////

   /**
    * Send mail for any active campaigns, in a batch.
    *
    * This method is made public and static to allow it to be called from elsewhere,
    * without instantiation.
    *
    * @param integer $id The ID of the campaign to return status messages for
    */
    public static function sendMail($id = null, $t0 = null){
        self::$batchTime = $t0 === null ? time() : $t0;
        $admin = Yii::app()->settings;
        $messages = array();
        $totalSent = 0;
        try{
            // Get all campaigns that could use mailing
            $campaigns = Campaign::model()->findAllByAttributes(
                    array('complete' => 0, 'active' => 1, 'type' => 'Email'), 'launchdate > 0 AND launchdate < :time', array(':time' => time()));
            foreach($campaigns as $campaign){
                try{
                    list($sent, $errors) = self::campaignMailing($campaign);
                }catch(CampaignMailingException $e){
                    $totalSent += $e->return[0];
                    $messages = array_merge($messages, $e->return[1]);
                    $messages[] = Yii::t('marketing', 'Successful email sent').': '.$totalSent;
                    $wait = ($admin->emailInterval + $admin->emailStartTime) - time();
                    return array('wait' => $wait, 'messages' => $messages);
                }
                $messages = array_merge($messages, $errors);
                $totalSent += $sent;
                if(time() - self::$batchTime > Yii::app()->settings->batchTimeout)
                    break;

            }
            if(count($campaigns) == 0){
                $messages[] = Yii::t('marketing', 'There is no campaign email to send.');
            }

        }catch(Exception $e){
            $messages[] = $e->getMessage();
        }
        $messages[] = $totalSent == 0 ? Yii::t('marketing', 'No email sent.') : Yii::t('marketing', 'Successful email sent').': '.$totalSent;
        $wait = ($admin->emailInterval + $admin->emailStartTime) - time();
        return array('wait' => $wait, 'messages' => $messages);
    }

    /**
     * Send mail for one campaign
     *
     * @param Campaign $campaign The campaign to send
     * @param integer $limit The maximum number of emails to send
     *
     * @return Array [0]=> The number of emails sent, [1]=> array of applicable error messages
     */
    protected static function campaignMailing(Campaign $campaign, $limit = null){
        $class = __CLASS__;
        $totalSent = 0;
        $errors = array();
        $items = self::deliverableItems($campaign->list->id,true);
        foreach($items as $item) {
            $mailer = new $class;
            $mailer->campaign = $campaign;
            $mailer->itemId = $item['id'];
            $mailer->sendIndividualMail();
            if($mailer->fullStop) {
                $errors[] = $mailer->status['message'];
                throw new CampaignMailingException(array($totalSent,$errors));
            } elseif($mailer->status['code'] != 200) {
                $errors[] = $mailer->status['message'];
            } else {
                $totalSent++;
            }
            if(time() - self::$batchTime > Yii::app()->settings->batchTimeout) {
                $errors[] = Yii::t('marketing','Batch timeout limit reached.');
                break;
            }
        }
        return array($totalSent, $errors);
    }
}

/**
 * Campaign mailing instant halt exception class that retains data regarding the
 * current operation.
 */
class CampaignMailingException extends CException {
    public $return;

    public function __construct($return,$message=null, $code=0, $previous=null){
        parent::__construct($message, $code, $previous);
        $this->return = $return;
    }
}

?>
