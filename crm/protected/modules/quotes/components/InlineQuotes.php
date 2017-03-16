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
 *  Class for creating quotes from a contact view.
 *  
 *  This is used for creating, updating, deleting, duplicating
 *  a quote or invoice from the contact view page. It makes heavy use of
 *  javascript and ajax calls to the QuotesController.
 * 
 * @package application.components 
 */
class InlineQuotes extends X2Widget {

	public $recordId; // quotes displayed here are related to this record
    private $_contactId; // id of associated contact (optional)
	public $contact;
	public $account = null; // name of associated account (optional)
    public $modelName;

	public $errors = array();
	public $startHidden = false;

	public function init() {
        $quotesAssetsUrl = $this->module->assetsUrl;
	
		if(isset($_POST))
			$startHidden = false;

		if($this->startHidden) {
            if($this->startHidden)
                Yii::app()->clientScript->registerScript(
                    'startQuotesHidden',"$('#quotes-form').hide();" ,CClientScript::POS_READY);
            
            // Set up the new create form:
            Yii::app()->clientScript->registerScriptFile(
                $quotesAssetsUrl.'/js/inlineQuotes.js', CClientScript::POS_HEAD);
            Yii::app()->clientScript->registerScriptFile(
                $quotesAssetsUrl.'/js/LineItems.js', CClientScript::POS_HEAD);

            Yii::app()->clientScript->registerCssFiles('InlineQuotesCss', array (
                $quotesAssetsUrl.'/css/inlineQuotes.css',
                $quotesAssetsUrl.'/css/lineItemsMain.css',
                $quotesAssetsUrl.'/css/lineItemsWrite.css',
            ), false);
            Yii::app()->clientScript->registerCoreScript('jquery.ui');

            $this->contact = X2Model::model ('Contacts')->findByPk ($this->contactId);

            //$this->contact = Contacts::model()->findByPk($this->contactId);
            $iqConfig = array(
                'contact' => ($this->contact instanceof Contacts) ? 
                    CHtml::encode($this->contact->name) : '',
                'account' => $this->account,
                'sendingQuote' => false,
                'lockedMessage' => Yii::t(
                    'quotes','This quote is locked. Are you sure you want to update this quote?'),
                'deniedMessage' => Yii::t('quotes','This quote is locked.'),
                'lockedDialogTitle' => Yii::t('quotes','Locked'),
                'failMessage' => Yii::t('quotes', 'Could not save quote.'),
                'reloadAction' => CHtml::normalizeUrl(
                    array(
                        '/quotes/quotes/viewInline',
                        'recordId' => $this->recordId,
                        'recordType' => CHtml::encode($this->modelName),
                    )
                ),
                'createAction' => CHtml::normalizeUrl(
                    array(
                        '/quotes/quotes/create',
                        'quick' => 1,
                        'recordId' => $this->recordId,
                        'recordType' => $this->modelName,
                    )
                ),
                'updateAction' => CHtml::normalizeUrl(
                    array(
                        '/quotes/quotes/update', 
                        'quick' => 1,
                    )
                ),
            );
            Yii::app()->clientScript->registerScript('quickquote-vars', '
            ;(function () {
                if(typeof x2 == "undefined"){
                    x2 = {};
                }
                var iqConfig = '.CJSON::encode($iqConfig).';
                if(typeof x2.inlineQuotes=="undefined") {
                    x2.inlineQuotes = iqConfig;
                } else {
                    $.extend(x2.inlineQuotes,iqConfig);
                }
            }) ();', CClientScript::POS_HEAD);
        }
        parent::init();
	}

    /**
     * Getter and setter for contactId will also update recordId
     * in order to remain backwards compatible.
     */
    public function getContactId() {
      return $this->_contactId;  
    }

    public function setContactId($value) {
        $this->_contactId = $value;
        $this->recordId = $value;
    }

    /**
     * Returns all related invoices or quotes 
     * @param bool $invoices
     * @return array array of related quotes models
     */
    public function getRelatedQuotes ($invoices=false) {

        if ($invoices) {
            $invoiceCondition = "type='invoice'";
        } else {
            $invoiceCondition = "type IS NULL OR type!='invoice'";
        }

        /*
        Select all quotes which have a related record with the current record's id
        */
        $quotes = Yii::app()->db->createCommand ()
            ->select ('quotes.id')
            ->from ('x2_quotes as quotes, x2_relationships as relationships')
            ->where ("(".$invoiceCondition.") AND ".
                "((relationships.firstType='Quote' AND ".
                  "relationships.secondType=:recordType AND relationships.secondId=:recordId) OR ".
                "(relationships.secondType='Quote' AND ".
                  "relationships.firstType=:recordType AND relationships.firstId=:recordId)) AND ".
                '((quotes.id=relationships.firstId AND relationships.firstType="Quote") OR '.
                 '(quotes.id=relationships.secondId AND relationships.secondType="Quote"))',
                 array (
                    ':recordId' => $this->recordId,
                    ':recordType' => $this->modelName
                ))
            ->queryAll ();

        // get models from ids
        $getId = function ($a) { return $a['id']; };
        $quotes = X2Model::model('Quote')->findAllByPk (array_map ($getId, $quotes));

        return $quotes;
    }

	public function run() {
        // Permissions that affect the behavior of the widget:
        $canDo = array();
        foreach(array('QuickDelete','QuickUpdate','QuickCreate') as $subAction) {
            $canDo[$subAction] = Yii::app()->user->checkAccess('Quotes'.$subAction);
        }

		/*$relationships = Relationships::model()->findAllByAttributes(array(
			'firstType'=>'quotes', 
			'secondType'=>'contacts', 
			'secondId'=>$this->contactId,
		));*/
		
		echo 
            '<div id="quotes-form" style="display: none;">
                <div id="wide-quote-form" class="wide x2-layout-island form" 
                 style="overflow: visible;">
                <a class="widget-close-button x2-icon-button" href="#">
                    <span class="fa fa-times fa-lg" 
                     title="'.CHtml::encode (Yii::t('app', 'Close Widget')).'">
                    </span>
                </a>
		        <div id="quote-create-form-wrapper" style="display:none"></div>
		        <span class="quotes-section-title" 
		         style="font-weight:bold; font-size: 1.5em;">'. 
                    CHtml::encode (Yii::t('quotes','Quotes')) .
                '</span>
		    <br /><br />';

		// Mini Create Quote Form
		$model = new Quote;

        if($canDo['QuickCreate']){
            $this->render('createQuote');
            echo '<br /><hr />';
        }

        $quotes = $this->getRelatedQuotes ();
        
		foreach($quotes as $quote) {
			$this->render('viewQuotes', array(
				'quote'=>$quote,
				'recordId'=>$this->recordId,
				'modelName'=>$this->modelName,
                'canDo' => $canDo
			));
		}
		
		
		echo '<br /><br />
		    <span class="quotes-section-title" 
             style="font-weight:bold; font-size: 1.5em;">'. 
                Yii::t('quotes','Invoices').'</span>
		    <br /><br />';
		
        $quotes = $this->getRelatedQuotes (true);
		
		foreach($quotes as $quote) {
			$this->render('viewQuotes', array(
				'quote'=>$quote,
				'recordId'=>$this->recordId,
				'modelName'=>$this->modelName,
                'canDo' => $canDo,
			));
		}

		
		echo "</div>";		
		echo "</div>";
	}
}
