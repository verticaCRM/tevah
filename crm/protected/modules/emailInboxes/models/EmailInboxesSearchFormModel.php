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

class EmailInboxesSearchFormModel extends CFormModel {

    //public $all;
    public $answered;
    //public $bcc;
    public $before;
    //public $body;
    //public $cc;
    //public $deleted;
    public $flagged;
    public $from;
    //public $keyword;
    //public $new;
    //public $old;
    public $on;
    //public $recent;
    public $seen;
    public $since;
    public $subject;
    public $to;
    public $unanswered;
    //public $undeleted;
    public $unflagged;
    //public $unkeyword;
    public $unseen;
    public $text;

    //public $fullText;

    /**
     * Filters attributes to only those which need to be checked when emails are filtered
     * @return array non-null values indexed by attribute
     */
//    public function getSearchCriteria () {
//        $searchOperators = EmailInboxes::$searchOperators;
//        $searchCriteria = array ();
//        foreach ($this->getAttributes () as $operator => $val) {
//            $operandType = $searchOperators[$operator];
//            // ignore unchecked null type operators, operators not set, and string type operators
//            // set to the empty string
//            if (($operandType !== null || intval ($val) === 1) && $val !== null && 
//                $val !== '') {
//
//                $searchCriteria[$operator] = $val;
//            }
//        }
//        return $searchCriteria;
//    }

    /**
     * Generates search string from attributes
     * @return string to pass to imap_search 
     */
    public function composeSearchString () {
        $searchOperators = EmailInboxes::$searchOperators;
        $searchString = '';
        $first = true;
        foreach ($this->getAttributes () as $operator => $val) {
            $operandType = $searchOperators[$operator];
            if (($operandType !== null || intval ($val) === 1) && $val !== null && 
                $val !== '') {

                if (!$first) {
                    $searchString .= ' ';
                    $first = false;
                }
                if ($operandType === null) {
                    $searchString .= strtoupper ($operator);
                } elseif ($operandType === 'date' || $operandType === 'string') {
                    $val = preg_replace ('/"/', '\"', $val);
                    $searchString .= strtoupper ($operator).' "'.$val.'"';
                } else {
                    throw new CException ('Invalid search operand type');
                }
            }

        }
        return $searchString;
    }

    /**
     * Attribute labels in order of appearance in form 
     */
    public function attributeLabels () {
        return array (
            'from' => Yii::t('emailInboxes', 'From:'),
            'to' => Yii::t('emailInboxes', 'To:'), 
            //'cc' => Yii::t('emailInboxes', 'Cc:'),
            //'bcc' => Yii::t('emailInboxes', 'Bcc:'),
            'subject' => Yii::t('emailInboxes', 'Subject:'), 
            'on' => Yii::t('emailInboxes', 'On:'),
            'before' => Yii::t('emailInboxes', 'Before:'),
            'since' => Yii::t('emailInboxes', 'Since:'),
            'seen' => Yii::t('emailInboxes', 'read'),
            'unseen' => Yii::t('emailInboxes', 'unread'), 
            'answered' => Yii::t('emailInboxes', 'answered'),
            'unanswered' => Yii::t('emailInboxes', 'unanswered'), 
            'flagged' => Yii::t('emailInboxes', 'starred'), 
            'unflagged' => Yii::t('emailInboxes', 'not starred'),
            //'body' => Yii::t('emailInboxes', 'Body:'),
            //'unkeyword' => Yii::t('emailInboxes', 'Keyword:'),
            //'keyword' => Yii::t('emailInboxes', 'Keyword:'),
            //'text' => Yii::t('emailInboxes', 'Text:'), 
            //'all' => Yii::t('emailInboxes', ''),
            //'deleted' => Yii::t('emailInboxes', ''),
            //'new' => Yii::t('emailInboxes', 'new'),
            //'old' => Yii::t('emailInboxes', 'old'),
            //'recent' => Yii::t('emailInboxes', 'recent'),
            //'undeleted' => Yii::t('emailInboxes', ''),
        );
    }

    public function rules () {
        return array ();
    }

}
?>
