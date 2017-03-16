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
 * This is the model class for individual Email Messages
 *
 * @package application.modules.emailInboxes.models
 */
class EmailMessage extends CModel {

    // IMAP Unique ID of message
    private $uid;

    // the associated EmailInbox
    private $inbox;

    /**
     * @var null|Actions $_action model associated with this message
     */
    private $_action; 

    // Email Message attributes
    public $msgno;
    public $subject;
    public $from;
    public $to;
    public $cc;
    public $reply_to;
    public $body;
    public $date;
    public $size;
    public $attachments;
    // Flags
    public $seen;
    public $flagged;
    public $answered;

    public function attributeNames() {
        return array(
            'uid',
            'msgno',
            'subject',
            'from',
            'to',
            'cc',
            'reply_to',
            'body',
            'date',
            'size',
            'attachments',
            'seen',
            'flagged',
            'answered',
        );
    }

    /**
     * Constructor sets the Email Inbox then loads the message attributes
     */
    public function __construct($inbox, $attributes) {
        $this->inbox = $inbox;
        // Load private email attributes
        foreach ($attributes as $attr => $value)
            $this->$attr = $value;
        if (empty($this->subject))
            $this->subject = "(No Subject)";
    }

    private static $_purifier;
    public static function getPurifier () {
        if (!isset (self::$_purifier)) {
            self::$_purifier = new CHtmlPurifier();
            self::$_purifier->options = array ( 
                'HTML.ForbiddenElements' => array (
                    'script'
                ),
            );
        }
        return self::$_purifier;
    }

    /**
     * Purifies certain attributes, removing script tags and inline JS
     */
    public function purifyAttributes () {
        $purifier = self::getPurifier ();
        $excludeList = array ('from', 'to', 'cc');
        foreach (array_diff ($this->attributeNames (), $excludeList) as $name) {
            $this->$name = $purifier->purify ($this->$name);
        }
    }

    /**
     * @return int Unique ID of the message
     */
    public function getUid() { return $this->uid; }

    /**
     * @return EmailInboxes The inbox this message belongs to
     */
    public function getInbox () { return $this->inbox; }

    public function renderDate () {
        return X2Html::dynamicDate ($this->date);
    }

    /**
     * Retrieves/creates action corresponding to this message and returns it.
     * @return Actions|null null if action doesn't exist and could not be created
     */
    public function getAction () {
        if (!isset ($this->_action)) {
            $action = Actions::model () // check for existence of action
                ->with (array (
                    'actionMetaData' => array (
                        'select' => false,
                        'joinType' => 'INNER JOIN',
                        'condition' => 
                            'actionMetaData.emailImapUid=:uid AND
                             actionMetaData.emailInboxId=:inboxId',
                        'params' => array (
                            ':uid' => $this->uid,
                            ':inboxId' => $this->inbox->id,
                        )
                    )
                ))
                ->find ();
            if ($action) {
                $this->_action = $action;
            } else { // no action exists, create one
                $action = new Actions;
                $now = time ();
                $user = $this->inbox->assignedTo;
                $action->setAttributes (array (
                    'subject' => $this->subject,
                    'type' => 'email',
                    'visibility' => 1,
                    'createDate' => $now,
                    'lastUpdated' => $now,
                    'completeDate' => $this->date,
                    'assignedTo' => $user,
                    'completedBy' => $user,
                    'associationType' => Actions::ASSOCIATION_TYPE_MULTI,
                ), false);
                $action->actionDescription = $this->body;
                $action->emailImapUid = $this->uid;
                $action->emailInboxId = $this->inbox->id;
                if ($action->save ()) {
                    $this->_action = $action;
                } else {
                    $this->_action = null;
                }
            }
        }
        return $this->_action;
    }

    /**
     * @return array of Contacts models having email addresses in the from, to, or cc fields
     */
    public function getAssociatedContacts () {
        $addresses = array ();
        $contacts = array ();
        foreach (array ('from', 'to', 'cc') as $attr) {
            $addresses = array_merge (
                $addresses, EmailDeliveryBehavior::addressHeaderToArray ($this->$attr));
        }
        $encountered = array (); // used to avoid returning duplicate contacts
        foreach ($addresses as $address) {
            list ($name, $email) = $address;
            $contact = Contacts::model ()->findByAttributes (array ('email' => $email));
            if ($contact && !isset ($encountered[$contact->id])) {
                $contacts[] = $contact;
                $encountered[$contact->id] = true;
            }
        }
        return $contacts;
    }

    /**
     * Creates tag for non-contact entity
     * @param string $name name of entity
     * @param string $emailAddress
     * @param bool $return
     */
    public function nonContactEntityTag ($name, $emailAddress, $return=false) {
        $tag = 
            "<span class='non-contact-entity-tag' 
              data-email='".CHtml::encode ($emailAddress)."'>".CHtml::encode ($name)."</span>";
        if ($return) return $tag;
        else echo $tag;
    }

    /**
     * @param array name and email address 
     * @param bool $includeEmailAddress whether to render email address in addition to name
     * @param bool $return if true, results will be returned instead of echoed
     */
    public function renderAddress ($address, $includeEmailAddress=false, $return=false) {
        list ($name, $email) = $address;
        $name = trim ($name);
        if (empty ($name)) $name = preg_replace ('/@.*/', '', $email);
        $contact = Contacts::model ()->findByAttributes (array ('email' => $email));

        if ($contact) {
            $formattedField = $contact->link;
        } else {
            $formattedField = $this->nonContactEntityTag ($name, $email, true);
        }
        if ($includeEmailAddress)
            $formattedField .= ' '.CHtml::encode ('<'.$email.'>');
        if (!$return)
            echo $formattedField;
        else
            return $formattedField;
    }

    /**
     * @param array of arrays of names and email addresses
     * @param bool $includeEmailAddress whether to render email address in addition to name
     * @param bool $return if true, results will be returned instead of echoed
     */
    public function renderAddresses ($addresses, $includeEmailAddress=false, $return=false) {
        $formattedAddresses = array ();
        foreach ($addresses as $address) {
            $formattedAddresses[] = $this->renderAddress ($address, $includeEmailAddress, true);
        }
        if (!$return) {
            echo implode (', ', $formattedAddresses);
        } else {
            return implode (', ', $formattedAddresses);
        }
    }

    public function renderFromField ($includeEmailAddress=false) {
        $addresses = EmailDeliveryBehavior::addressHeaderToArray ($this->from, true);
        $this->renderAddress (array_shift ($addresses));
    }

    public function renderToField ($includeEmailAddress=false) {
        $addresses = EmailDeliveryBehavior::addressHeaderToArray ($this->to, true);
        $this->renderAddresses ($addresses, false, false);
    }

    /**
     * Generate a link to toggle this message's importance
     */
    public function renderToggleImportant() {
        echo 
            '<div class="flagged-toggle'.($this->flagged ? ' flagged' : '').'"
              data-uid="'.CHtml::encode ($this->uid).'"
              title="'.
                ($this->flagged ? 
                    CHtml::encode (
                        Yii::t('emailInboxes', 'Click to remove star')) :
                    CHtml::encode (Yii::t('emailInboxes', 'Click to add star'))
                ).
              '">
            </div>';
    }

    /**
     * Download a specific attachment
     * @param int $part Message part number
     */
    public function downloadAttachment($part, $inline = false, $return=false) {
        $stream = $this->inbox->stream;
        $partStruct = imap_bodystruct($stream, imap_msgno($stream, $this->uid), $part);
        if (!$partStruct) {
            throw new CHttpException (404, Yii::t('emailInboxes',
                'Unable to find the requested message part'));
        }

        $filename = utf8_decode($partStruct->dparameters[0]->value);
        $message = $this->inbox->decodeBodyPart ($this->uid, $partStruct, $part);
        $size = strlen($message); // $partStruct->bytes; is not accurate due to encoding
        $mimeType = $this->inbox->getStructureMimetype ($partStruct, true);

        if (!$return) {
            // Render the attachment

            header("Content-Description: File Transfer");
            header("Content-Type: ".$mimeType);
            if (!$inline)
                header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".$size);
            header("Expires: 0");
            header("Cache-Control: must-revalidate");
            header("Pragma: public");
            echo $message;
        } else {
            return array ($mimeType, $filename, $size, $message);
        }
    }

    /**
     * Generates a link to an attachment
     * @param array $attachment
     * @param string $type ('view'|'download')
     * @return string
     */
    public function getAttachmentLink (array $attachment, $type='view') {
        if ($type === 'view') {
            $action = 'viewAttachment';
        } else {
            $action = 'downloadAttachment';
        }
        return Yii::app()->controller->createUrl ('emailInboxes/'.$action, array (
            'uid' => $this->uid,
            'part' => $attachment['part'],
            'emailFolder' => $this->inbox->getCurrentFolder (),
        ));
    }

    /**
     * Find and replace inline attachments in the email with links to view each
     * inline attachment.
     */
    public function parseInlineAttachments() {
        $inlineAttachments = array_filter ($this->attachments, function ($attachment) {
            return 'inline' === $attachment['type'];
        });

        // Construct an array of replacements
        $replacements = array();
        foreach ($inlineAttachments as $attachment) {
            $link = $this->getAttachmentLink ($attachment);
            $replacements['src="cid:'.$attachment['cid'].'"'] = 'src="'.$link.'"';
        }

        $this->body = strtr ($this->body, $replacements);
    }

    /**
     * Renders links to non-inline attachments
     */
    public function renderAttachmentLinks () {
        $attachments = array_filter ($this->attachments, function ($attachment) {
            return 'attachment' === $attachment['type'];
        });

        foreach ($attachments as $attachment) {
            list ($topLevelTypeName, $subtypeName, $params) = 
                $this->parseMimeType ($attachment['mimetype']);

            switch ($topLevelTypeName) {
                case 'audio':
                    $attachmentIconClass = 'fa-file-sound-o';
                    break;
                case 'image':
                    $attachmentIconClass = 'fa-file-picture-o';
                    break;
                case 'video':
                    $attachmentIconClass = 'fa-file-video-o';
                    break;
                case 'text':
                    $attachmentIconClass = 'fa-file-text-o';
                    break;
                /*case 'application':
                case 'message':
                case 'model':
                case 'multipart':*/
                default:
                    $attachmentIconClass = 'fa-file-o';
            }

            echo "
                <div class='message-attachment'>
                    <span class='fa {$attachmentIconClass} attachment-type-icon'></span>
                    <span class='attachment-filename'>".
                        CHtml::encode ($attachment['filename'])."</span>
                    <a class='attachment-download-link fa fa-download x2-button'
                     title='".CHtml::encode (Yii::t('emailInboxes', 'Download attachment'))."'
                     href='#'
                     data-href='".$this->getAttachmentLink ($attachment, 'download')."'>
                    </a>
                </div>
            ";
        }
    }

    /**
     * Parses the mime type returning the top-level type, the subtype, and the parameters
     * @param string $mimeType 
     */
    public function parseMimeType ($mimeType) {
        $parts = explode ('/', $mimeType);
        if (!count ($parts)) return null;
        $topLevelTypeName = $parts[0];
        $parts = explode (';', $parts[1]);
        $subtypeName = array_shift ($parts);
        $params = $parts;
        return array ($topLevelTypeName, $subtypeName, $params);
    }
}

