<?php
/***********************************************************************************
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


class WebFormAction extends CAction {

    public static function sanitizeGetParams () {
        //sanitize get params
        $whitelist = array(
            'fg', 'bgc', 'font', 'bs', 'bc', 'iframeHeight'
        );
        $_GET = array_intersect_key($_GET, array_flip($whitelist));
        //restrict param values, alphanumeric, # for color vals, comma for tag list, . for decimals
        $_GET = preg_replace('/[^a-zA-Z0-9#,.]/', '', $_GET);
    }

    private static function addTags ($model) {
        // add tags
        if(!empty($_POST['tags'])){
            $taglist = explode(',', $_POST['tags']);
            if($taglist !== false){
                foreach($taglist as &$tag){
                    if($tag === '')
                        continue;
                    if(substr($tag, 0, 1) != '#')
                        $tag = '#'.$tag;
                    $tagModel = new Tags;
                    $tagModel->taggedBy = 'API';
                    $tagModel->timestamp = time();
                    $tagModel->type = get_class ($model);
                    $tagModel->itemId = $model->id;
                    $tagModel->tag = $tag;
                    $tagModel->itemName = $model->name;
                    $tagModel->save();

                    X2Flow::trigger('RecordTagAddTrigger', array(
                        'model' => $model,
                        'tags' => $tag,
                    ));
                }
            }
        }
    }

    /* x2prostart */
    /*
    Helper funtion for run ().
    */
    private static function formatEmailBodyAttrs ($emailBody, $model) {
        // set the template variables
        $matches = array();

        // find all the things
        preg_match_all('/{\w+}/', $emailBody, $matches);

        if(isset($matches[0])){     // loop through the things
            foreach($matches[0] as $match){
                $match = substr($match, 1, -1); // remove { and }

                if($model->hasAttribute($match)){

                    // get the correctly formatted attribute
                    $value = $model->renderAttribute($match, false, true);
                    $emailBody = preg_replace(
                        '/{'.$match.'}/', $value, $emailBody);
                }
            }
        }
        return $emailBody;
    }
    /* x2proend */

    /* x2prostart */
    /**
     * Sets tracking key of contact model. First looks for the key generated on client (this key
     * allows the visitor to be tracked on a domain other than the one on which the crm is
     * running) then, if no key exists, generates a new key.
     *
     * $model object the contact model
     */
    private function setNewWebleadTrackingKey ($model) {
        if (empty($model->trackingKey)) { 
            if(isset($_COOKIE['x2_key']) && ctype_alnum($_COOKIE['x2_key'])) { 
                $model->trackingKey = $_COOKIE['x2_key'];
            } else {
                $model->trackingKey = Contacts::getNewTrackingKey();
            }

        }
    }
    /* x2proend */

    private function handleWebleadFormSubmission (X2Model $model, $extractedParams) {
        $newRecord = $model->isNewRecord;
        if(isset($_POST['Contacts'])) {

            $model->createEvent = false;
            $model->setX2Fields($_POST['Contacts'], true);
            // Extra sanitizing
            $p = Fields::getPurifier();
            foreach($model->attributes as $name=>$value) {
                if($name != $model->primaryKey() && !empty($value)) {
                    $model->$name = $p->purify($value);
                }
            }
            $now = time();

            //require email field, check format
            /*if(preg_match(
                "/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",
                $_POST['Contacts']['email']) == 0) {
                $this->renderPartial('application.components.views.webFormSubmit',
                    array (
                        'type' => 'weblead',
                        'error' => Yii::t('contacts', 'Invalid Email Address')
                    )
                );
                return;
            }*/

            /* x2prostart */
            if (Yii::app()->contEd('pro')) {
                foreach($extractedParams['fieldList'] as $field){
                    if($field['required'] &&
                       (!isset($model->$field['fieldName']) || $model->$field['fieldName'] == '')){
                        $model->addError($field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                    }
                }
            }
            /* x2proend */

            if (empty ($model->visibility)) $model->visibility = 1;

            $model->validate ();
            if(!$model->hasErrors()){

                $duplicates = array ();
                if(!empty($model->email)){

                    //find any existing contacts with the same contact info
                    $criteria = new CDbCriteria();
                    $criteria->compare('email', $model->email, false, "OR");
                    $emailFields = Yii::app()->db->createCommand()
                        ->select('fieldName')
                        ->from('x2_fields')
                        ->where('modelName = "Contacts" AND type = "email"')
                        ->queryColumn();
                    foreach ($emailFields as $field)
                        $criteria->compare($field, $model->email, false, "OR");
                    $duplicates = $model->findAll($criteria);
                }

                if(count($duplicates) > 0){ //use existing record, update background info
                    /**/AuxLib::debugLogR ('found dup');
                    $newBgInfo = $model->backgroundInfo;
                    $model = $duplicates[0];
                    $oldBgInfo = $model->backgroundInfo;
                    if ($newBgInfo !== $oldBgInfo) {
                        $model->backgroundInfo .= 
                            (($oldBgInfo && $newBgInfo) ? "\n" : '') . $newBgInfo;
                    }

                    /* x2prostart */
                    if(Yii::app()->contEd('pro')) {
                        foreach($_POST['Contacts'] as $index => $value){
                            if(empty($value)){
                                unset($_POST['Contacts'][$index]);
                            }
                        }
                        $this->setNewWebleadTrackingKey ($model);
                    }
                    /* x2proend */

                    /* x2plastart */
                    $attributes = (isset($_POST['fingerprintAttributes']))? 
                        json_decode($_POST['fingerprintAttributes'], true) : array();
                    if (Yii::app()->settings->enableFingerprinting && $_POST['fingerprint'])
                        $model->setFingerprint ($_POST['fingerprint'], $attributes);
                    /* x2plaend */

                    $success = $model->save();
                }else{ //create new record
                    $model->assignedTo = $this->controller->getNextAssignee();
                    $model->visibility = 1;
                    $model->createDate = $now;
                    $model->lastUpdated = $now;
                    $model->updatedBy = 'admin';

                    /* x2prostart */
                    if(Yii::app()->contEd('pro')) {
                        $this->setNewWebleadTrackingKey ($model);
                    }
                    /* x2proend */

                    $success = $model->save();

                    /* x2plastart */
                    if (Yii::app()->contEd('pla') && Yii::app()->settings->enableFingerprinting &&
                        isset ($_POST['fingerprint'])) {

                        $attributes = (isset($_POST['fingerprintAttributes']))? 
                            json_decode($_POST['fingerprintAttributes'], true) : array();

                        $anonContact = AnonContact::model ()
                            ->findByFingerprint ($_POST['fingerprint'], $attributes); 

                        // if there's not an anonyomous contact, then the fingerprint match
                        // was for an actual contact. 
                        if ($anonContact !== null) {
                            $model->mergeWithAnonContact ($anonContact);
                        } else {
                            $model->setFingerprint ($_POST['fingerprint'], $attributes);
                        }

                        $success = $success && $model->save();
                    }
                    /* x2plaend */

                    //TODO: upload profile picture url from webleadfb
                }
                
                if($success){
                    if ($extractedParams['generateLead'])
                        self::generateLead ($model, $extractedParams['leadSource']);
                    if ($extractedParams['generateAccount'])
                        self::generateAccount ($model);

                    self::addTags ($model);
                    $tags = ((!isset($_POST['tags']) || empty($_POST['tags'])) ? 
                        array() : explode(',',$_POST['tags']));
                    if($newRecord) {
                        X2Flow::trigger(
                            'WebleadTrigger', array('model' => $model, 'tags' => $tags));
                    }

                    //use the submitted info to create an action
                    $action = new Actions;
                    $action->actionDescription = Yii::t('contacts', 'Web Lead')
                            ."\n\n".Yii::t('contacts', 'Name').': '.
                            CHtml::decode($model->firstName)." ".
                            CHtml::decode($model->lastName)."\n".Yii::t('contacts', 'Email').": ".
                            CHtml::decode($model->email)."\n".Yii::t('contacts', 'Phone').": ".
                            CHtml::decode($model->phone)."\n".
                            Yii::t('contacts', 'Background Info').": ".
                            CHtml::decode($model->backgroundInfo);

                    // create action
                    $action->type = 'note';
                    $action->assignedTo = $model->assignedTo;
                    $action->visibility = '1';
                    $action->associationType = 'contacts';
                    $action->associationId = $model->id;
                    $action->associationName = $model->name;
                    $action->createDate = $now;
                    $action->lastUpdated = $now;
                    $action->completeDate = $now;
                    $action->complete = 'Yes';
                    $action->updatedBy = 'admin';
                    $action->save();

                    // create a notification if the record is assigned to someone
                    $event = new Events;
                    $event->associationType = 'Contacts';
                    $event->associationId = $model->id;
                    $event->user = $model->assignedTo;
                    $event->type = 'weblead_create';
                    $event->save();

                    /* x2prostart */
                    if (Yii::app()->contEd('pro')) {
                        // email to send from
                        $emailFrom = Credentials::model()->getDefaultUserAccount(
                            Credentials::$sysUseId['systemResponseEmail'], 'email');
                        if($emailFrom == Credentials::LEGACY_ID)
                            $emailFrom = array(
                                'name' => Yii::app()->settings->emailFromName,
                                'address' => Yii::app()->settings->emailFromAddr
                            );
                    }
                    /* x2proend */

                    if($model->assignedTo != 'Anyone' && $model->assignedTo != '') {

                        $notif = new Notification;
                        $notif->user = $model->assignedTo;
                        $notif->createdBy = 'API';
                        $notif->createDate = time();
                        $notif->type = 'weblead';
                        $notif->modelType = 'Contacts';
                        $notif->modelId = $model->id;
                        $notif->save();

                        $profile = Profile::model()->findByAttributes(
                            array('username' => $model->assignedTo));

                        /* send user that's assigned to this weblead an email if the user's email
                        address is set and this weblead has a user email template */
                        if($profile !== null && !empty($profile->emailAddress)){

                            /* x2prostart */
                            if (Yii::app()->contEd('pro') && 
                                $extractedParams['userEmailTemplate']) {

                                /* We'll be using the user's own email account to send the
                                web lead response (since the contact has been assigned) and
                                additionally, if no system notification account is available,
                                as the account for sending the notification to the user of
                                the new web lead (since $emailFrom is going to be modified,
                                and it will be that way when this code block is exited and the
                                time comes to send the "welcome aboard" email to the web lead)*/
                                $emailFrom = Credentials::model()->getDefaultUserAccount(
                                    $profile->user->id, 'email');
                                if($emailFrom == Credentials::LEGACY_ID)
                                    $emailFrom = array(
                                        'name' => $profile->fullName,
                                        'address' => $profile->emailAddress
                                    );

                                /* Security Check: ensure that at least one webform is using this
                                email template */
                                /* if(!empty($userEmailTemplate) &&
                                CActiveRecord::model('WebForm')->exists(
                                    'userEmailTemplate=:template',array(
                                        ':template'=>$userEmailTemplate))) { */

                                $template = X2Model::model('Docs')->findByPk(
                                    $extractedParams['userEmailTemplate']);
                                if($template){
                                    $emailBody = $template->text;

                                    $subject = '';
                                    if($template->subject){
                                        $subject = $template->subject;
                                    }

                                    $emailBody = self::formatEmailBodyAttrs ($emailBody, $model);

                                    $address = array(
                                        'to' => array(array('', $profile->emailAddress)));

                                    $notifEmailFrom = Credentials::model()->getDefaultUserAccount(
                                        Credentials::$sysUseId['systemNotificationEmail'], 'email');

                                    /* Use the same sender as web lead response if notification
                                    emailer not available */
                                    if($notifEmailFrom == Credentials::LEGACY_ID)
                                        $notifEmailFrom = $emailFrom;

                                    // send user template email
                                    $status = $this->controller->sendUserEmail(
                                        $address, $subject, $emailBody, null, $notifEmailFrom);

                                    if ($status['code'] !== '200') {
                                        /**/AuxLib::debugLog (
                                            'Error: sendUserEmail: '.$status['message']);
                                    }

                                }
                                // }
                            } else { /* x2proend */
                                $subject = Yii::t('marketing', 'New Web Lead');
                                $message =
                                    Yii::t('marketing',
                                        'A new web lead has been assigned to you: ').
                                    CHtml::link(
                                        $model->firstName.' '.$model->lastName,
                                        array('/contacts/contacts/view', 'id' => $model->id)).'.';
                                $address = array('to' => array(array('', $profile->emailAddress)));
                                $emailFrom = Credentials::model()->getDefaultUserAccount(
                                    Credentials::$sysUseId['systemNotificationEmail'], 'email');
                                if($emailFrom == Credentials::LEGACY_ID)
                                    $emailFrom = array(
                                        'name' => $profile->fullName,
                                        'address' => $profile->emailAddress
                                    );

                                $status = $this->controller->sendUserEmail(
                                    $address, $subject, $message, null, $emailFrom);
                            /* x2prostart */
                            }
                            /* x2proend */
                        }

                    }

                    /* x2prostart */
                    /* send new weblead an email if we have their email address and this web
                    form has a weblead email template */
                    if(Yii::app()->contEd('pro') && $extractedParams['webleadEmailTemplate'] &&
                       !empty($model->email)) {

                        /* Security Check: ensure that at least one webform is using this
                        email template */
                        /* if(CActiveRecord::model('WebForm')->exists(
                            'webleadEmailTemplate=:template',array(
                                ':template'=>$webleadEmailTemplate))){ */
                        $template = X2Model::model('Docs')->findByPk(
                            $extractedParams['webleadEmailTemplate']);
                        if($template !== null){
                            $emailBody = $template->text;

                            $subject = '';
                            if($template->subject){
                                $subject = $template->subject;
                            }

                            $emailBody = self::formatEmailBodyAttrs ($emailBody, $model);

                            $address = array('to' => array(array((isset($model->firstName) ?
                                $model->firstName : '').' '.
                                    (isset($model->lastName) ? $model->lastName :
                                ''), $model->email)));

                            // send user template email
                            $status = $this->controller->sendUserEmail(
                                $address, $subject, $emailBody, null, $emailFrom);
                            
                            if ($status['code'] !== '200') {
                                /**/AuxLib::debugLog (
                                    'Error: sendUserEmail: '.$status['message']);
                            }
                        }
                        // }
                    }

                    if (Yii::app()->contEd('pro')) {
                        if(class_exists('WebListenerAction') && $model->trackingKey !== null) {
                            WebListenerAction::setKey($model->trackingKey);
                        }

                        if(!empty($tags)){
                            X2Flow::trigger('RecordTagAddTrigger', array(
                                'model' => $model,
                                'tags' => $tags,
                            ));
                        }
                    }
                    /* x2proend */
                } else {
                    $errMsg = 'Error: WebListenerAction.php: model failed to save';
                    /**/AuxLib::debugLog ($errMsg);
                    Yii::log ($errMsg, '', 'application.debug');
                }

                $this->controller->renderPartial('application.components.views.webFormSubmit',
                    array ('type' => 'weblead'));

                Yii::app()->end(); // success!
            }
        } /* x2prostart */elseif (Yii::app()->contEd('pro') && class_exists('WebListenerAction')){
            if (isset ($_COOKIE['x2_key']))  {
                if (isset ($_SERVER['HTTP_REFERER'])) {
                    // since the web tracking script passes the website url in the web request,
                    // the web listener expects the website url to be in the $_GET superglobal
                    $_GET['url'] = $_SERVER['HTTP_REFERER'];
                }
                WebListenerAction::track();
            }
        } /* x2proend */

        self::sanitizeGetParams ();

        /* x2prostart */
        if (Yii::app()->contEd('pro')) {
            $viewParams = array (
                'model' => $model,
                'type' => 'weblead',
                'fieldList' => $extractedParams['fieldList'],
                'css' => $extractedParams['css'], 
                'header' => $extractedParams['header']
            );
            $this->controller->renderPartial('application.components.views.webForm', $viewParams);
        } else {
        /* x2proend */
            $this->controller->renderPartial(
                'application.components.views.webForm', array('type' => 'weblead'));
        /* x2prostart */
        }
        /* x2proend */

    }


    private function handleServiceFormSubmission ($model, $extractedParams) {
        if(isset($_POST['Services'])){ // web form submitted
            if(isset($_POST['Services']['firstName'])){
                $firstName = $_POST['Services']['firstName'];
                $fullName = $firstName;
            }

            if(isset($_POST['Services']['lastName'])){
                $lastName = $_POST['Services']['lastName'];
                if(isset($fullName)){
                    $fullName .= ' '.$lastName;
                }else{
                    $fullName = $lastName;
                }
            }

            if(isset($_POST['Services']['email'])){
                $email = $_POST['Services']['email'];
            }
            if(isset($_POST['Services']['phone'])){
                $phone = $_POST['Services']['phone'];
            }
            if(isset($_POST['Services']['desription'])){
                $description = $_POST['Services']['description'];
            }

            /* x2prostart */
            if (Yii::app()->contEd('pro')) {
                $model->setX2Fields($_POST['Services'],true);
            }
            /* x2proend */

            // Extra sanitizing
            $p = Fields::getPurifier();
            foreach($model->attributes as $name=>$value) {
                if($name != $model->primaryKey() && !empty($value)) {
                    $model->$name = $p->purify($value);
                }
            }

            if(isset($email) && $email) {
                $contact = Contacts::model()->findByAttributes(array('email' => $email));
            } else {
                $contact = false;
            }

            if($contact){
                $model->contactId = $contact->nameId;
            }else{
                $model->contactId = "Unregistered";
            }

            if(isset($fullName) || isset($email)){
                $model->subject = Yii::t('services', 'Web Form Case entered by {name}', array(
                            '{name}' => isset($fullName) ? $fullName : $email,
                ));
            }else{
                $model->subject = Yii::t('services', 'Web Form Case');
            }

            $model->origin = 'Web';
            if(!isset($model->impact) || $model->impact == '')
                $model->impact = Yii::t('services', '3 - Moderate');
            if(!isset($model->status) || $model->status == '')
                $model->status = Yii::t('services', 'New');
            if(!isset($model->mainIssue) || $model->mainIssue == '')
                $model->mainIssue = Yii::t('services', 'General Request');
            if(!isset($model->subIssue) || $model->subIssue == '')
                $model->subIssue = Yii::t('services', 'Other');
            $model->assignedTo = $this->controller->getNextAssignee();
            if (isset($email))
                $model->email = CHtml::encode($email);
            $now = time();
            $model->createDate = $now;
            $model->lastUpdated = $now;
            $model->updatedBy = 'admin';
            if (isset ($description))
                $model->description = CHtml::encode($description);

            /* x2prostart */
            if (Yii::app()->contEd('pro')) {
                $contactFields = array('firstName', 'lastName', 'email', 'phone');
                foreach($extractedParams['fieldList'] as $field){
                    if(in_array($field['fieldName'], $contactFields)){
                        if($field['required'] &&
                           (!isset($_POST['Services'][$field['fieldName']]) ||
                            $_POST['Services'][$field['fieldName']] == '')){

                            $model->addError($field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                        }
                    }else{
                        if($field['required'] &&
                           (!isset($model->$field['fieldName']) || $model->$field['fieldName'] == '')) {

                            $model->addError($field['fieldName'], Yii::t('app', 'Cannot be blank.'));
                        }
                    }
                }
            }
            /* x2proend */

            if(!$model->hasErrors()){

                if($model->save()){
                    $model->name = $model->id;
                    $model->update(array('name'));

                    self::addTags ($model);

                    //use the submitted info to create an action
                    $action = new Actions;
                    $action->actionDescription = Yii::t('contacts', 'Web Form')."\n\n".
                            (isset($fullName) ? (Yii::t('contacts', 'Name').': '.$fullName."\n") : '').
                            (isset($email) ? (Yii::t('contacts', 'Email').": ".$email."\n") : '').
                            (isset($phone) ? (Yii::t('contacts', 'Phone').": ".$phone."\n") : '').
                            (isset($description) ?
                                (Yii::t('services', 'Description').": ".$description) : '');

                    // create action
                    $action->type = 'note';
                    $action->assignedTo = $model->assignedTo;
                    $action->visibility = '1';
                    $action->associationType = 'services';
                    $action->associationId = $model->id;
                    $action->associationName = $model->name;
                    $action->createDate = $now;
                    $action->lastUpdated = $now;
                    $action->completeDate = $now;
                    $action->complete = 'Yes';
                    $action->updatedBy = 'admin';
                    $action->save();

                    if(isset($email)){

                        //send email
                        $emailBody = Yii::t('services', 'Hello').' '.$fullName.",<br><br>";
                        $emailBody .= Yii::t('services',
                            'Thank you for contacting our Technical Support '.
                            'team. This is to verify we have received your request for Case# '.
                            '{casenumber}. One of our Technical Analysts will contact you shortly.',
                            array('{casenumber}' => $model->id));

                        $emailBody = Yii::app()->settings->serviceCaseEmailMessage;
                        if(isset($firstName))
                            $emailBody = preg_replace('/{first}/u', $firstName, $emailBody);
                        if(isset($lastName))
                            $emailBody = preg_replace('/{last}/u', $lastName, $emailBody);
                        if(isset($phone))
                            $emailBody = preg_replace('/{phone}/u', $phone, $emailBody);
                        if(isset($email))
                            $emailBody = preg_replace('/{email}/u', $email, $emailBody);
                        if(isset($description))
                            $emailBody = preg_replace('/{description}/u', $description, $emailBody);
                        $emailBody = preg_replace('/{case}/u', $model->id, $emailBody);
                        $emailBody = preg_replace('/\n|\r\n/', "<br>", $emailBody);

                        $uniqueId = md5(uniqid(rand(), true));
                        $emailBody .= '<img src="'.$this->controller->createAbsoluteUrl(
                            '/actions/actions/emailOpened', array('uid' => $uniqueId, 'type' => 'open')).'"/>';

                        $emailSubject = Yii::app()->settings->serviceCaseEmailSubject;
                        if(isset($firstName))
                            $emailSubject = preg_replace('/{first}/u', $firstName, $emailSubject);
                        if(isset($lastName))
                            $emailSubject = preg_replace('/{last}/u', $lastName, $emailSubject);
                        if(isset($phone))
                            $emailSubject = preg_replace('/{phone}/u', $phone, $emailSubject);
                        if(isset($email))
                            $emailSubject = preg_replace('/{email}/u', $email, $emailSubject);
                        if(isset($description))
                            $emailSubject = preg_replace('/{description}/u', $description,
                                $emailSubject);
                        $emailSubject = preg_replace('/{case}/u', $model->id, $emailSubject);
                        if(Yii::app()->settings->serviceCaseEmailAccount != 
                           Credentials::LEGACY_ID) {
                            $from = (int) Yii::app()->settings->serviceCaseEmailAccount;
                        } else {
                            $from = array(
                                'name' => Yii::app()->settings->serviceCaseFromEmailName,
                                'address' => Yii::app()->settings->serviceCaseFromEmailAddress
                            );
                        }
                        $useremail = array('to' => array(array(isset($fullName) ?
                            $fullName : '', $email)));

                        $status = $this->controller->sendUserEmail(
                            $useremail, $emailSubject, $emailBody, null, $from);

                        if($status['code'] == 200){
                            if($model->assignedTo != 'Anyone'){
                                $profile = X2Model::model('Profile')->findByAttributes(
                                    array('username' => $model->assignedTo));
                                if(isset($profile)){
                                    $useremail['to'] = array(
                                        array(
                                            $profile->fullName,
                                            $profile->emailAddress,
                                        ),
                                    );
                                    $emailSubject = 'Service Case Created';
                                    $emailBody = "A new service case, #".$model->id.
                                        ", has been created in X2Engine. To view the case, click ".
                                        "this link: ".$model->getLink();
                                    $status = $this->controller->sendUserEmail(
                                        $useremail, $emailSubject, $emailBody, null, $from);
                                }
                            }
                            //email action
                            $action = new Actions;
                            $action->associationType = 'services';
                            $action->associationId = $model->id;
                            $action->associationName = $model->name;
                            $action->visibility = 1;
                            $action->complete = 'Yes';
                            $action->type = 'email';
                            $action->completedBy = 'admin';
                            $action->assignedTo = $model->assignedTo;
                            $action->createDate = time();
                            $action->dueDate = time();
                            $action->completeDate = time();
                            $action->actionDescription = '<b>'.$model->subject."</b>\n\n".
                                $emailBody;
                            if($action->save()){
                                $track = new TrackEmail;
                                $track->actionId = $action->id;
                                $track->uniqueId = $uniqueId;
                                $track->save();
                            }
                        } else {
                            $errMsg = 'Error: actionWebForm.php: sendUserEmail failed';
                            /**/AuxLib::debugLog ($errMsg);
                            Yii::log ($errMsg, '', 'application.debug');
                        }
                    }
                    $this->controller->renderPartial('application.components.views.webFormSubmit',
                        array('type' => 'service', 'caseNumber' => $model->id));

                    Yii::app()->end(); // success!
                }
            }
        }

        self::sanitizeGetParams ();

        /* x2prostart */
        if (Yii::app()->contEd('pro')) {
            $viewParams = array (
                'model' => $model,
                'type' => 'service',
                'fieldList' => $extractedParams['fieldList'],
                'css' => $extractedParams['css']
            );
            $this->controller->renderPartial('application.components.views.webForm', $viewParams);
        } else {
        /* x2proend */
            $this->controller->renderPartial (
                'application.components.views.webForm',
                array('model' => $model, 'type' => 'service'));
        /* x2prostart */
        }
        /* x2proend */
    }



    /**
     * Create a web lead form with a custom style
     *
     * There are currently two methods of specifying web form options. 
     *  Method 1 (legacy):
     *      Web form options are sent in the GET parameters (limited options: css, web form
     *      id for retrieving custom header)
     *  Method 2 (new):
     *      CSS options are passed in the GET parameters and all other options (custom fields, 
     *      custom html, and email templates) are stored in the database and accessed via a
     *      web form id sent in the GET parameters.
     *
     * This get request is for weblead/service type only, marketing/weblist/view supplies
     * the form that posts for weblist type
     *
     */
    public function run(){
        $modelClass = $this->controller->modelClass;
        if ($modelClass === 'Campaign') $modelClass = 'Contacts';

        if ($modelClass === 'Contacts')
            $model = new Contacts ('webForm');
        elseif ($modelClass === 'Services')
            $model = new Services ('webForm');

        $extractedParams = array ();

        if (isset ($_GET['webFormId'])) { 
            $webForm = WebForm::model()->findByPk($_GET['webFormId']);
        } 
        $extractedParams['leadSource'] = null;
        $extractedParams['generateLead'] = false;
        $extractedParams['generateAccount'] = false;
        if (isset ($webForm)) { // new method
            if (!empty ($webForm->leadSource)) 
                $extractedParams['leadSource'] = $webForm->leadSource;
            if (!empty ($webForm->generateLead)) 
                $extractedParams['generateLead'] = $webForm->generateLead;
            if (!empty ($webForm->generateAccount)) 
                $extractedParams['generateAccount'] = $webForm->generateAccount;
        }

        /* x2prostart */
        if (Yii::app()->contEd('pro')) {

            // retrieve list of fields (if any)
            $fieldList = array ();
            if (isset ($webForm))
                $fieldList = CJSON::decode ($webForm->fields);

            // purify fields
            $purifier = new CHtmlPurifier ();
            if (is_array ($fieldList) && sizeof ($fieldList) > 0) {
                foreach($fieldList as &$field){
                    $tempField = array();
                    foreach($field as $key => $val){
                        $key=$purifier->purify($key);
                        $tempField[$key] = $purifier->purify($val);
                    }
                    $field = $tempField;
                }
            }

            if (!is_array($fieldList)) $fieldList = array ();
            $extractedParams['fieldList'] = $fieldList;

            $css = '';
            if(isset($_GET['css'])){
                $css = $purifier->purify($_GET['css']);
            }
            $extractedParams['css'] = $css;

            if ($modelClass === 'Contacts') {
                $extractedParams['header'] = '';
                $extractedParams['userEmailTemplate'] = null;
                $extractedParams['webleadEmailTemplate'] = null;

                if (isset ($webForm)) { // new method
                    if (!empty ($webForm->header)) 
                        $extractedParams['header'] = $webForm->header;
                    if (!empty ($webForm->userEmailTemplate)) 
                        $extractedParams['userEmailTemplate'] = $webForm->userEmailTemplate;
                    if (!empty ($webForm->webleadEmailTemplate)) 
                        $extractedParams['webleadEmailTemplate'] = $webForm->webleadEmailTemplate;
                } else { // legacy method
                    if(isset($_GET['header'])){ 
                        $webFormLegacy = WebForm::model()->findByPk($_GET['header']);
                        if($webFormLegacy){
                            $extractedParams['header'] = $webFormLegacy->header;
                        }
                    }
                }
            }
        }
        /* x2proend */

        if ($modelClass === 'Contacts') {
            $this->handleWebleadFormSubmission ($model, $extractedParams);
        } else if ($modelClass === 'Services') {
            $this->handleServiceFormSubmission ($model, $extractedParams);
        }

    }

    /**
     * Creates a new lead and associates it with the contact
     * @param Contacts $contact
     * @param null|string $leadSource
     */
    private static function generateLead (Contacts $contact, $leadSource=null) {
        $lead = new X2Leads ('webForm');
        $lead->firstName = $contact->firstName;
        $lead->lastName = $contact->lastName;
        $lead->leadSource = $leadSource;
        // disable validation to prevent saving from failing if leadSource isn't set
        if ($lead->save (false)) {
            Relationships::create ('X2Leads', $lead->id, 'Contacts', $contact->id);
        }

    }

    /**
     * Generates an account from the contact's company field, if that field has a value 
     */
    private static function generateAccount (Contacts $contact) {
        if (isset ($contact->company)) {
            $account = new Accounts ();
            $account->name = $contact->company;
            if ($account->save ()) {
                $account->refresh ();
                $contact->company = $account->nameId;
                $contact->update ();
            }
        }
    }

}

?>
