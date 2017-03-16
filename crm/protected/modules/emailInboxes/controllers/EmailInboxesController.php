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
 * @package application.modules.emailInboxes.controllers
 */
class EmailInboxesController extends x2base {

    public $modelClass = 'EmailInboxes';

    public static $emailActions = array(
        'refresh',
        'selectFolder',
    );

    /**
     * @var array|null $_searchCriteria
     */
    private $_searchCriteria; 

    /**
     * @var EmailInbox  $_selectedMailbox
     */
    private $_selectedMailbox;

//    public function behaviors () {
//        return array_merge (parent::behaviors (), array (
//            'ImportExportBehavior' => array ('class' => 'ImportExportBehavior'),
//        ));
//    }

    /**
     * View inboxes
     */
    public function actionIndex () {
        $mailbox = $this->getSelectedMailbox ();
        if ($mailbox instanceof EmailInboxes) {
            if ($mailbox->credentialId == null) {
                $this->render ('noCredentials');
                return;
            }
        }

        $loadMessagesOnPageLoad = true;
        if (!is_null($this->getEmailAction ())) {
            $this->dispatchEmailAction ($this->getEmailAction ());
        }
        
        if ($mailbox instanceof EmailInboxes) {
            try {
                $mailbox->getStream ();
            } catch (EmailConfigException $e) {
                $this->render ('badCredentials');
                Yii::app ()->end ();
            }
            // only search cache on initial page load, otherwise load page
            // and fetch messages via ajax
            $searchCacheOnly = !isset ($_GET['ajax']);
            $dataProvider = $this->loadMailbox ($searchCacheOnly);
            if (!$dataProvider) {
                $dataProvider = new CArrayDataProvider(array());
            } else {
                $loadMessagesOnPageLoad = false;
            }
        } else {
            $dataProvider = new CArrayDataProvider(array());
        }

        $pollTimeout = Yii::app()->settings->imapPollTimeout;
        $myEmailInboxIsSetUp = EmailInboxes::model ()->myEmailInboxIsSetUp ();
        $notConfigured = !$myEmailInboxIsSetUp || !($mailbox instanceof EmailInboxes && 
            $dataProvider instanceof CArrayDataProvider);

        $this->noBackdrop = true;
        $this->render (
            'emailInboxes',
            array (
                'dataProvider' => $dataProvider,
                'mailbox' => $mailbox,
                'pollTimeout' => $pollTimeout,
                'loadMessagesOnPageLoad' => $loadMessagesOnPageLoad,
                'notConfigured' => $notConfigured,
                'uid' => isset ($_GET['uid']) ? $_GET['uid'] : null,
            )
        );
    }

    /**
     * Fetch latest messages
     */
    public function refreshInbox() {
        $this->getSelectedMailbox ()->fetchLatest ();
    }

    /**
     * Change the current mailbox folder
     * @param string $folder Folder to select
     */
    public function selectFolder($folder) {
        $mailbox = $this->getSelectedMailbox ();
        if (!in_array($folder, $mailbox->folders))
            throw new CException (Yii::t('emailInboxes',
                'Requested folder does not exist'));
        $mailbox->selectFolder ($folder);
    }

    /**
     * View grid view of of shared inboxes
     */
    public function actionSharedInboxesIndex () {
        $model = new EmailInboxes ('search');
        $sharedCriteria = new CDbCriteria;
        $sharedCriteria->compare('shared', true);
        $emailInboxesDataProvider = $model->searchBase ($sharedCriteria);

        $this->render (
            'sharedInboxesIndex',
            array (
                'model' => $model,
                'emailInboxesDataProvider' => $emailInboxesDataProvider,
            )
        );
    }

    /**
     * Create a new shared inbox
     */
    public function actionCreateSharedInbox  () {
        $model = new EmailInboxes;
        if (isset ($_POST['EmailInboxes'])) {
            
            $model->setX2Fields ($_POST['EmailInboxes']);
            $model->shared = true;
            if ($model->save ()) {
                $tabs = CJSON::decode (Yii::app()->params->profile->emailInboxes);
                if (is_array($tabs)) {
                    $tabs[] = $model->id;
                    Yii::app()->params->profile->setEmailInboxes ($tabs);
                    Yii::app()->params->profile->save();
                }
                $this->redirect ('sharedInboxesIndex');
            }
        } 
        $this->render (
            'createSharedInbox',
            array (
                'model' => $model,
            )
        );
    }

    /**
     * Update a shared inbox 
     * @param int $id id of shared inbox
     */
    public function actionUpdateSharedInbox ($id) {
        $model = $this->loadModel($id);
        if (isset ($_POST['EmailInboxes'])) {
            
            $model->setX2Fields ($_POST['EmailInboxes']);
            if ($model->save ()) {
                $this->redirect (array('/emailInboxes/sharedInboxesIndex'));
            }
        } 
        $this->render (
            'updateSharedInbox',
            array (
                'model' => $model,
            )
        );
    }

    /**
     * Delete a shared inbox 
     * @param int $id id of shared inbox
     */
    public function actionDeleteSharedInbox ($id) {
        $model = $this->loadModel($id);
        $model->delete ();
    }

    /**
     * Save user's tab settings 
     */
    public function actionSaveTabSettings () {
        if (isset ($_POST['Profile']['emailInboxes']) && 
            is_array ($_POST['Profile']['emailInboxes'])) {

            $emailInboxes = $_POST['Profile']['emailInboxes'];
            $username = Yii::app()->user->getName ();
            $tabs = array ();

            // ensure that user has view permissions for all selected inboxes
            foreach ($emailInboxes as $id) {
                $mailbox = EmailInboxes::model ()->findByPk ($id);
                if (!$mailbox ||
                    $mailbox->shared && !$mailbox->isAssignedTo ($username) ||
                    !$mailbox->shared && $mailbox->assignedTo !== $username) {

                    throw $this->badRequestException ();
                }
                $tabs[] = $id;
            }

            // ensure that user's personal inbox is selected
            if (!in_array (EmailInboxes::model ()->getMyEmailInbox ()->id, $tabs)) {
                throw $this->badRequestException ();
            }
            Yii::app()->params->profile->setEmailInboxes ($tabs);
            if (Yii::app()->params->profile->save ()) {
                echo 'success';
            } else {
                echo 'failure';
            }
        } else {
            throw $this->badRequestException ();
        }

    }

    /**
     * Configure the current user's personal email inbox
     */
    public function actionConfigureMyInbox () {
        $model = EmailInboxes::model ()->getMyEmailInbox ();   
        if (!$model) {
            $model = new EmailInboxes;
            // set default personal inbox name
            $model->name = Yii::t('emailInboxes', 'My Inbox');
        }
        if (isset ($_POST['EmailInboxes'])) {
            $model->setX2Fields ($_POST['EmailInboxes'], false, true);
            $model->assignedTo = Yii::app ()->user->getName ();
            $model->settings = $_POST['EmailInboxes']['settings'];
            $model->shared = 0;
            $emailInboxes = Yii::app()->params->profile->getEmailInboxes ();
            if ($model->save ()) {
                $model->refresh ();
                $model->deleteCache ();
                if (!count ($emailInboxes)) {
                    Yii::app()->params->profile->setEmailInboxes (array ($model->id));
                    Yii::app()->params->profile->save ();
                }
                $this->redirect ('index');
            }
        } 
        $this->render (
            'configureMyInbox',
            array (
                'model' => $model,
            )
        );
    }

    /**
     * Render a JSON-encoded array with message details
     * @param int $uid Unique ID of the email message
     */
    public function actionViewMessage($uid) {
        $mailbox = $this->getSelectedMailbox ();
        if (!isset($mailbox))
            $this->redirect('index');

        $this->getCurrentFolder(true);
        $message = $mailbox->fetchMessage ($uid);
        $message->purifyAttributes ();
        $this->renderPartial (
            '_emailMessage',
            array (
                'message' => $message,
            ),
            false,
            isset ($_GET['ajax'])
        );
    }

    /**
     * Download a specific attachment
     * @param int $id Unique ID of the message
     * @param float $part Multipart part number
     */
    public function actionDownloadAttachment($uid, $part) {
        $this->fetchAttachment ($uid, $part);
    }

    /**
     * View an inline attachment
     * @param int $id Unique ID of the message
     * @param float $part Multipart part number
     */
    public function actionViewAttachment($uid, $part) {
        $this->fetchAttachment ($uid, $part, true);
    }

    /**
     * Toggle flags for the given messages
     * @param int|array $id Unique ids of the specified messages
     * @param string $flag Message flag to set
     */
    public function actionMarkMessages() {
        if (!Yii::app()->request->isPostRequest)
            throw new CException('Invalid request');
        $flag = isset($_POST['flag']) ? $_POST['flag'] : null;
        $uids = $this->specifiedUids;
        $mailbox = $this->getSelectedMailbox ();
        $this->getCurrentFolder(true);
        $success = true;
        switch ($flag) {
            case 'read':         $success = $mailbox->markRead ($uids);         break;
            case 'unread':       $success = $mailbox->markUnread ($uids);       break;
            case 'important':    $success = $mailbox->markImportant ($uids);    break;
            case 'notimportant': $success = $mailbox->markNotImportant ($uids); break;
            default:             throw new CException(Yii::t('emailInboxes',
                                    "Unknown flag: ".CHtml::encode($flag)));
        }
        echo $success ? 'success' : 'failure';
    }

    /**
     * Helper function to grab the current folder from GET parameters. Also sets the selected 
     * mailbox's folder
     * @param bool $openImap Whether or not to open the IMAP stream
     * @return string|null The currently selected folder
     */
    public function getCurrentFolder($openImap = false) {
        $mailbox = $this->getSelectedMailbox ();

        if (isset($_GET['emailFolder'])) {
            $currentFolder = $_GET['emailFolder'];
        } else if (isset($_POST['emailFolder'])) {
            $currentFolder = $_POST['emailFolder'];
        } else {
            return null;
        }

        if ($openImap)
            $mailbox->selectFolder ($currentFolder);
        else
            $mailbox->setCurrentFolder ($currentFolder);
        return $currentFolder;
    }

    /**
     * Helper function to grab the mailbox id GET param
     * @return EmailInbox The currently selected mailbox
     */
    public function getSelectedMailbox() {
        if (!isset ($this->_selectedMailbox)) {
            $inboxModel = EmailInboxes::model();
            if (isset($_GET['id']) && ctype_digit($_GET['id']))
                $this->_selectedMailbox = $inboxModel->findByPk ($_GET['id']);
            else if ($inboxModel->myEmailInboxIsSetUp())
                $this->_selectedMailbox = $inboxModel->myEmailInbox;
            else 
                $this->_selectedMailbox = null;
        }
        return $this->_selectedMailbox;
    }

    /**
     * Retrieve the specified UIDs from POST
     * @return array of message uids
     */
    public function getSpecifiedUids() {
        $uids = isset($_POST['uids']) ? $_POST['uids'] : null;
        if (!is_numeric($uids) && !is_array($uids))
            throw new CException(Yii::t('emailInboxes',
                'You must specify UIDs'));
        else if (is_array($uids)) {
            foreach ($uids as $uid) {
                if (!is_numeric($uid))
                    throw new CException(Yii::t('emailInboxes',
                        'Invalid UID specified!'));
            }
        }
        return $uids;
    }

    /**
     * Process and return the search terms and operators
     * @return array
     */
    public function getSearchCriteria () {
        if (!isset ($this->_searchCriteria)) {
            if (isset ($_GET['EmailInboxesSearchFormModel'])) {
                $formModel = new EmailInboxesSearchFormModel;
                $formModel->setAttributes ($_GET['EmailInboxesSearchFormModel'], false);
                $this->_searchCriteria = $formModel->composeSearchString ();
            } else {
                $this->_searchCriteria = null;
            }
        }
        return $this->_searchCriteria;
    }

    /**
     * Retrieve the chosen email action from POST
     * @return string|null The email action, or null if none is specified
     */
    public function getEmailAction() {
        if (!isset($_GET['emailAction']))
            return null;
        else
            $action = $_GET['emailAction'];

        if (!in_array($action, self::$emailActions)) {
            throw new CException (Yii::t('emailInboxes',
                'Unsupported email action '.CHtml::encode($action)));
        }
        return $action;
    }

    /**
     * Load message header overviews for the given inbox
     * @param bool $searchCacheOnly if true, inbox will only be searched if messages are cached 
     * @return false|CArrayDataProvider of message headers
     */
    public function loadMailbox($searchCacheOnly=false) {
        $this->getCurrentFolder (true);
        if (isset ($_GET['lastUid'])) $lastUid = $_GET['lastUid'];
        else $lastUid = null;
        return $this->getSelectedMailbox ()->searchInbox (
            $this->getSearchCriteria (), $searchCacheOnly, $lastUid);
    }

    /**
     * Create a menu for EmailInboxes
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        /**
         * To show all options:
         * $menuOptions = array(
         *     'inbox', 'configureMyInbox', 'sharedInboxesIndex', 'createSharedInbox',
         * );
         */

        $menuItems = array(
            array(
                'name' => 'inbox',
                'label' => Yii::t('emailInboxes', 'Inbox'),
                'url' => array('index')
            ),
            array(
                'name' => 'configureMyInbox',
                'label' => Yii::t('emailInboxes', 'Configure My Inbox'),
                'url' => array('configureMyInbox')
            ),
            array(
                'name' => 'sharedInboxesIndex',
                'label' => Yii::t('emailInboxes', 'Shared Inboxes'),
                'url' => array('sharedInboxesIndex')
            ),
            array(
                'name' => 'createSharedInbox',
                'label' => Yii::t('emailInboxes', 'Create Shared Inbox'),
                'url' => array('createSharedInbox')
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

    /**
     * Perform the specified email action, as allowed by self::$emailActions
     * @param string $action Email Action to perform
     */
    private function dispatchEmailAction($action) {
        switch ($action) {
            case 'refresh':
                $this->refreshInbox(); 
                break;
            case 'selectFolder':
                $this->getCurrentFolder(true);
                break;
            default:
                throw new CException (Yii::t('emailInboxes',
                    'Unsupported email action '.CHtml::encode($action)));
        }
    }

    /**
     * Helper function to handle retrieving attachments
     * @param int $uid IMAP Message UID
     * @param float $part IMAP multipart message part number
     * @param boolean $inline Whether it is an inline attachment
     */
    private function fetchAttachment($uid, $part, $inline = false) {
        $mailbox = $this->getSelectedMailbox ();
        if (!isset($mailbox))
            $this->redirect('index');

        $this->getCurrentFolder(true);
        $message = $mailbox->fetchMessage ($uid);
        $message->downloadAttachment ($part, $inline);
    }

}
