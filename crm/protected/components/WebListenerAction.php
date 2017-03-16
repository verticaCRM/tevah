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

/* @edition:pro */

/*
 * Records a contact's visit to a website.
 *
 * Used by embedding an iframe with weblistener.php or index.php/api/webListener
 * on the website.
 * Tracks using a cookie set by filling out a web capture form.
 * Looks up the contact by trackingKey, and creates a "webactivity" type action.
 */
class WebListenerAction extends CAction {

    const DEBUG_TRACK = 1;

    public function run() {
        self::track();
    }

    /* x2plastart */ 
    /**
     * Retrieve valid fingerprint attributes from the GET parameters 
     * @return array fingerprint attributes
     */
    private static function getFingerPrintAttributes () {
        $fingerprintAttributes = array_diff (
            array_keys (Fingerprint::model()->getAttributes ()), 
            array ('id', 'createDate', 'lastUpdated'));

        $attributes = array ();
        foreach ($fingerprintAttributes as $attr) {
            if (isset ($_GET[$attr])) {
                $attributes[$attr] = $_GET[$attr];
            }
        }
        return $attributes;
    }
    /* x2plaend */ 

    /**
     * Lookup contact by key. There are two types of keys: GET keys and cookie keys.
     * Cookie keys are set in the browser cookies when the contact submits the web lead form,
     * when the visit a page that contains the web tracker, or when the contact clicks on a
     * tracking link/campaign tracking link.
     * A GET key may come from either a campaign tracking link or a generic tracking link. 
     * Contact lookups are performed until a contact is found with precedence given to GET keys.
     *
     * @param boolean $getContent if set to true, the return value of any flow triggered by this
     *  method will be returned.
     */
    public static function track($getContent=false) {
        if(!Yii::app()->settings->enableWebTracker)
            return;


        /* x2plastart */
        $fingerprint = isset($_GET['fingerprint']) ? $_GET['fingerprint'] : null;

        $attributes = self::getFingerPrintAttributes ();
        /* x2plaend */

        $keys = array();
        // key set from generic tracking link or campaign tracking link
        if(isset($_GET['get_key']) && ctype_alnum($_GET['get_key'])) {
            $keys['GETKey'] = $_GET['get_key'];
        } 

        // web tracker key
        if(isset($_COOKIE['x2_key']) && ctype_alnum($_COOKIE['x2_key'])) { 
            $keys['cookieKey'] = $_COOKIE['x2_key'];
        }

        $url = isset($_GET['url']) ? $_GET['url'] : '';

        $contact = false;
        $retArr = null;

        /* x2plastart */ 
        // get fingerprint tracking to work even if there aren't any keys
        if (!sizeof ($keys) && Yii::app()->contEd('pla') && 
            Yii::app()->settings->enableFingerprinting) {

            $keys['dummyKey'] = null;
        }
        /* x2plaend */  

        // look up the link key, then the cookie key
        foreach($keys as $keyName => $key) {
            if ($key !== null) {
                $contact = self::trackCampaignClick($key,$url); // see if this key is a campaign key
            } else {
                $contact = false;
            }

            if($contact === true) {// means there's no contact record (newsletter type campaign)
                continue; 
            }

            if($contact === false) { // no campaign key found,
                // see if the key is a generic tracking key
                $retArr = self::trackGeneric(
                    $key, $url, $getContent /* x2plastart */, $fingerprint,
                    $attributes /* x2plaend */);
                $contact = $retArr['contact'];
            }

            if($contact instanceof Contacts) { // we found the contact!
                // The contact is not being edited by any user, and this will
                // cause errors, so we shall disable the behavior.
                $contact->disableBehavior('changelog');

                $contact->lastActivity = time (); // update lastActivity

                // make sure contact has a generic tracking key
                if(empty($contact->trackingKey)) {
                    $contact->trackingKey = Contacts::getNewTrackingKey ();
                } 

                $contact->update(array('lastActivity','trackingKey'));

                /* x2plastart */
                // Since the contact has been found, update their browser fingerprint, which
                // may have changed since it was last computed, due to browser updates and such.
                if (Yii::app()->settings->enableFingerprinting && (bool) $fingerprint) {
                    $contact->setFingerprint($fingerprint, $attributes);
                    $contact->update(array('fingerprintId'));
                }

                // Only set the cookie if the contact was identified with a cookie
                if (!isset($retArr['probability']) || $retArr['probability'] >= 100)
                /* x2plaend */
                self::setKey($contact->trackingKey);
            }
        }

        // no contact-specific targeted content was generated
        if (!$retArr && $getContent) { // trigger default content request
            $retArr = array (
                'flowRetVal' =>
                    X2Flow::trigger('TargetedContentRequestTrigger', array(
                        'model'=>null,
                        'url'=>$url,
                        'flowId'=>$_GET['flowId']
                    ))
            );
        }
        
        if ($getContent) {
            return $retArr['flowRetVal'];
        }
    }


    /**
     * Looks up the contact with the generic tracking key provided
     * and creates actions/events/notifications
     *
     * @param string $key the generic tracking key to find the contact by
     * @param string $url the referring URL
     * @return mixed the contact if it is found, FALSE otherwise
     */
    public static function trackGeneric(
        $key, $url, $getContent /* x2plastart */, $fingerprint = null, 
        $attributes = null /* x2plaend */) {


        /* x2plastart */
        $byFingerprint = false;
        /* x2plaend */

        $contact = null; 
        if ($key !== null) {
            $contact = CActiveRecord::model('Contacts')
                ->findByAttributes(array('trackingKey'=>$key));
            /* x2plastart */
            if (isset($contact))
                $contact->recordAddress();
            /* x2plaend */
        }

        if($contact === null) {
            /* x2plastart */
            if (Yii::app()->contEd('pla') && Yii::app()->settings->enableFingerprinting && 
                 $fingerprint !== null) {

                list($contact,$bits) = 
                    X2Model::model('Fingerprint')->track($fingerprint, $attributes);
                if ($contact instanceof Contacts || $contact instanceof AnonContact)
                    $contact->recordAddress();
            }
            if ($contact === null)
            /* x2plaend */
            return null;
            /* x2plastart */
            else
                $byFingerprint = true;
            /* x2plaend */
        }

        $now = time();
        $flowRetVal = null;

        // let's see if there's already a recent record about this
        /* x2plastart */
        if (Yii::app()->contEd('pla') && $contact instanceof AnonContact)
            $where = 'associationId=:id AND associationType="AnonContact" AND type="webactivity"';
        else /* x2plaend */
        $where = 'associationId=:id AND associationType="contacts" AND type="webactivity"';

        $latest = Yii::app()->db->createCommand()
            ->select('MAX(completeDate)')
            ->from('x2_actions')
            ->where($where, array(':id'=>$contact->id))
            ->queryScalar();

        if ($getContent) {
            $flowRetVal = X2Flow::trigger('TargetedContentRequestTrigger', array(
                'model'=>$contact,
                'url'=>$url,
                'flowId'=>$_GET['flowId']
            ));
        }
    
        // ignore if it's been < 1 min since the last visit
        if((YII_DEBUG && self::DEBUG_TRACK) ||
           $latest === null || $latest < $now - Yii::app()->settings->webTrackerCooldown) { 

            $webActivityTriggerParams = array(
                'model' => $contact,
                'url' => $url,
            );

            /* x2plastart */
            if (Yii::app()->contEd('pla')) {
                $probability = 100;
                if ($byFingerprint && $bits !== null) {
                    $probability = Fingerprint::calculateProbability ($attributes);
                    $probabilityText = "({$probability}% probability)";
                }
                $webActivityTriggerParams['probability'] = $probability;
            }
            /* x2plaend */

            // run automation
            X2Flow::trigger('WebActivityTrigger', $webActivityTriggerParams);

            $action = new Actions('webTracker');
            $action->associationType = 'contacts';
            /* x2plastart */
            if (Yii::app()->contEd('pla') && $contact instanceof AnonContact)
                $action->associationType = 'anoncontact';
            /* x2plaend */
            $action->associationId = $contact->id;
            $action->type = 'webactivity';
            $action->assignedTo = ($contact instanceof Contacts)? $contact->assignedTo : 'admin';
            $action->visibility = '1';
            $action->associationName = ($contact instanceof Contacts)?
                $contact->name : $contact->email;
            $action->actionDescription = $url;
            /* x2plastart */
            if (Yii::app()->contEd('pla') && $byFingerprint && $bits !== null) {
                $action->actionDescription .= " $probabilityText";
            }
            /* x2plaend */
            $action->createDate = $now;
            $action->lastUpdated = $now;
            $action->completeDate = $now;
            $action->complete= 'Yes';
            $action->updatedBy = 'admin';
            $action->save();

            if ($contact instanceof Contacts) {
                $event = new Events;
                $event->level=1;
                $event->user = $contact->assignedTo;
                $event->type = "web_activity";
                $event->associationType = 'Contacts';
                $event->associationId = $contact->id;
                /* x2plastart */
                if (Yii::app()->contEd('pla') && $byFingerprint)
                    $event->text = "$url $probabilityText";
                /* x2plaend */
                $event->save();
            }

            // create a notification if the record is assigned to someone
            if($contact instanceof Contacts && $contact->assignedTo != 'Anyone' && 
                $contact->assignedTo != '') {

                $notif = new Notification;
                $notif->user = ($contact instanceof Contacts)? $contact->assignedTo : 'admin';
                $notif->createdBy = 'API';
                $notif->createDate = time();
                $notif->type = 'webactivity';
                $notif->modelType = 'Contacts';
                $notif->modelId = $contact->id;
                /* x2plastart */
                if (Yii::app()->contEd('pla') && $byFingerprint) {
                    $notif->text = $probabilityText;
                    if ($contact instanceof AnonContact)
                        $notif->modelType = 'AnonContact';
                }
                /* x2plaend */
                $notif->save();
            }
        }
        $retArr = array (
            'contact' => $contact,
            'flowRetVal' => $flowRetVal
        );

        /* x2plastart */
        if (isset($probability))
            $retArr['probability'] = $probability;
        /* x2plaend */
        return $retArr;
    }

    /**
     * Looks up the campaign list item with the provided unique ID, updates
     * the campaign and sometimes creates actions, events and notifications
     *
     * @param string $uniqueId The unique id of the recipient
     * @param string $url the referring URL
     * @return mixed the contact model, if found, TRUE if uniqueId was found
     * but there was no contact record (newsletter type campaign), or FALSE
     * if uniqueId was not found in campaigns
     */
    public static function trackCampaignClick($uniqueId,$url) {
        $item = CActiveRecord::model('X2ListItem')->
            with('contact','list')->findByAttributes(array('uniqueId'=>$uniqueId));

        if(!isset($item))
            return false;

        $list = $item->list;
        if(empty($list) || !(bool)$list->campaign) // Nonexistent list/campaign
            return false;

        $campaign = $list->campaign;
        $item->markClicked($url);

        $action = new Actions;
        $action->type = 'email_clicked';
        $action->completeDate = time();
        $action->complete = 'Yes';
        $action->updatedBy = 'API';

        if((bool)$item->contact) { // Contact is present and in the email campaign

            $action->associationType = 'contacts';
            $action->associationId = $item->contact->id;
            $action->associationName = $item->contact->name;
            $action->visibility = $item->contact->visibility;
            $action->assignedTo = $item->contact->assignedTo;

            $action->actionDescription = Yii::t('marketing','Campaign') .': '. $campaign->name.
                "\n\n".Yii::t('marketing','{Contact} has clicked a link', array(
                    '{Contact}' => Modules::displayName(false, 'Contacts')
                )) .":\n". urldecode($url);

            // create event
            $event = new Events;
            $event->level = 3;
            $event->associationId = $action->associationId;
            $event->associationType = 'Contacts';
            $event->type = 'email_clicked';
            $event->save();

            // create notification
            if($action->assignedTo !== '' && $action->assignedTo !== 'Anyone') {
                $notif = new Notification;
                $notif->type = 'email_clicked';
                $notif->user = $action->assignedTo;
                $notif->modelType = 'Contacts';
                $notif->modelId = $action->associationId;
                $notif->createDate = time();
                $notif->value = $campaign->getLink();
                $notif->save();
            }

            X2Flow::trigger('CampaignWebActivityTrigger',array(
                'model'=>$item->contact,
                'campaign'=>$campaign->name,
                'url'=>$url,
            ));

        } else { // Contact not set; was deleted or part of a newsletter-type campaign
            $action->actionDescription = Yii::t('marketing','Campaign') .': '. $campaign->name.
                "\n\n".$item->emailAddress ." ". Yii::t('marketing','has clicked a link') .":\n". 
                urldecode($url);

            if(isset($item->list)) {
                $action->associationType = 'X2List';
                $action->associationId = $item->list->id;
                $action->associationName = $item->list->name;
                $action->visibility = $item->list->visibility;
                $action->assignedTo = $item->list->assignedTo;
            } else { //should be never
                $action->visibility = 1;
                $action->assignedTo = 'Anyone';
            }

            X2Flow::trigger('NewsletterWebActivityTrigger',array(
                'item'=>$item,
                'campaign'=>$campaign->name,
                'url'=>$url,
            ));
        }
        $action->save();

        if($item->contact)
            return $item->contact;

        return true;
    }

    /**
     * Sets the key as a cookie
     * @param string $key The contact's web tracking key 
     */
    public static function setKey($key) {
        $serverName = Yii::app()->request->getServerName();

        // strip out subdomain, leaving leading '.' to set cookie for all subdomains
        if (preg_match ('/^[^.]+\..+/', $serverName))
            $serverName = preg_replace ("/^[^.]*/", '', $serverName); 

        // set cookie for 1 year
        setcookie('x2_key', $key, time()+31536000, '/', $serverName);
    }
}
