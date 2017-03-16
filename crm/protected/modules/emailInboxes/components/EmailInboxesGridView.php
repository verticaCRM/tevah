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

class EmailInboxesGridView extends X2GridViewGeneric {

    /**
     * @var int $emailCount
     */
    public $emailCount = 100000; 

    public $gridViewJSClass = 'emailInboxesGridSettings';
    public $enableResponsiveTitleBar = false;
    public $massActions = array (
        'MassEmailDelete', 'MassMoveToFolder', 'MassMarkAsRead', 'MassMarkAsUnread', 
        'MassAssociateEmails');
    public $pagerClass = 'EmailInboxesPager';

    /**
     * @var bool if true, grid will start hidden so message view can be displayed in its place
     */
    public $messageView = false;

    /**
     * @var EmailInboxes $mailbox 
     */
    public $mailbox;

    /**
     * @var bool $loadingMailbox set to true if messages are being loaded via ajax on page load
     */
    public $loadingMailbox = false; 

    public function renderEmptyText () {
        echo '<span class="empty-text-progress-bar"></span>';
        parent::renderEmptyText (); // uses position: absolute
        // uses position: static, a kludge to force the container to be the right size
        parent::renderEmptyText (); 
    }

    public function init () {
        if ($this->loadingMailbox) {
            $this->emptyText = Yii::t('emailInboxes', 'Loading messages...');
        }

        $this->columns = array_merge (array (
            array (
                'class' => 'X2CheckBoxColumn',
                'selectableRows' => 2,
                'headerCheckBoxHtmlOptions' => array (
                    'title' => CHtml::encode (Yii::t('emailInboxes', 'Check all')),
                ),
                'htmlOptions' => array (
                    'class' => 'check-box-cell',
                ),
                'checkBoxHtmlOptions' => array (
                    'id' => '"'.$this->namespacePrefix.'C_gvCheckbox_".$data->uid',
                ),
            ),
        ), $this->columns);
        parent::init ();
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'EmailInboxesQtipManager' => array(
                    'baseUrl' => Yii::app()->controller->module->assetsUrl,
                    'js' => array(
                        'js/EmailInboxesQtipManager.js',
                    ),
                    'depends' => array ('X2GridViewQtipManager'),
                ),
            ));
        }
        return $this->_packages;
    }

    public function registerClientScript() {
        parent::registerClientScript();
        if($this->enableGvSettings) {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().
                '/js/X2GridView/x2gridview.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->controller->module->getAssetsUrl ().
                    '/js/emailInboxesGridSettings.js', CCLientScript::POS_END);
        }
    }

    public function getJSClassOptions () {
        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'enableColDragging' => false,
                'messageView' => $this->messageView,
                'loadingMailbox' => $this->loadingMailbox,
                'myInboxId' => EmailInboxes::model ()->getMyEmailInbox ()->id,
                'translations' => array (
                    'tabSettingsDialogTitle' => Yii::t('emailInboxes', 'Inbox Tab Settings'),
                    'Cancel' => Yii::t('emailInboxes', 'Cancel'),
                    'Save' => Yii::t('emailInboxes', 'Save'),
                )
            ));
    }

    /**
     * Renders check all check box 
     */
    public function renderCheckAllButton () {
        $checkboxColumn = $this->columns[0];
        $checkboxColumn->renderHeaderCellContent ();
    }

    /**
     * Renders back button for message view page 
     */
    public function renderBackButton () {
        echo 
            '<button class="x2-button mailbox-back-button fa fa-arrow-left fa-lg" 
              style="display: none;" 
              title="'.CHtml::encode (Yii::t('emailInboxes', 'Back to index')).'">
            </button>';
    }

    /**
     * Renders inbox refresh button 
     */
    public function renderRefreshButton () {
        echo 
            '<button class="x2-button mailbox-refresh-button fa fa-refresh fa-lg"
              title="'.CHtml::encode (Yii::t('emailInboxes', 'Refresh')).'">
            </button>';
    }

    /**
     * Renders mailbox controls (mass actions, results per page selector, search form, etc.) 
     */
    public function renderMailboxControls () {
        echo '<div class="mailbox-controls grid-top-bar">';
        echo '<div class="bs-row">';
        echo $this->mailbox->renderSearchForm ();
        echo '</div>
            <div class="bs-row">';
        echo $this->renderCheckAllButton ();
        echo $this->renderBackButton ();
        echo $this->renderRefreshButton ();
        echo $this->renderMassActionButtons ();
        echo $this->renderSummary ();
        echo $this->renderTopPager ();
        echo '</div>';
        echo '<div class="clearfix"></div>';
        echo '</div>';
    }

	/**
	 * Renders the pager.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function renderPager()
	{
		if(!$this->enablePagination)
			return;

		$pager=array();
		$class='CLinkPager';
		if(is_string($this->pager))
			$class=$this->pager;
		elseif(is_array($this->pager))
		{
			$pager=$this->pager;
			if(isset($pager['class']))
			{
				$class=$pager['class'];
				unset($pager['class']);
			}
		}
		$pager['pages']=$this->dataProvider->getPagination();

        /* x2modstart */ // pager always rendered
        echo '<div class="'.$this->pagerCssClass.
            ($pager['pages']->getPageCount() <= 1 ? ' empty-pager' : '').'">';
        $this->widget($class,$pager);
        echo '</div>';
        /* x2modend */ 
	}

    public function renderMailboxTabs () {
        $visibleEmailInboxes = Yii::app()->params->profile->getEmailInboxes ();
        $tabOptions = EmailInboxes::model ()->getTabOptions ();
        echo "<ul id='email-inbox-tabs'>";
        if (count ($visibleEmailInboxes)) {
            foreach ($visibleEmailInboxes as $emailInboxName => $emailInbox) {
                $id = $emailInbox->id;
                $classes = 'email-inbox-tab';
                if ($id === $this->mailbox->id) {
                    $classes .= ' selected-tab';
                }
                $title = $emailInbox->credentials ?
                    $emailInbox->credentials->auth->email : '';
                echo "
                <li class='$classes' 
                 title='".CHtml::encode ($title)."'>
                    <a id='email-inbox-tab-$id' data-id='$id' href='#'>".
                        CHtml::encode ($emailInboxName).
                    "</a>
                </li>
                ";
            }
        } else {
        echo 
            "<li class='email-inbox-tab selected-tab'>
                <a id='email-inbox-tab-null' data-id='null' href='#'>".
                    CHtml::encode (Yii::t('emailInboxes', 'My Inbox')).
                "</a>
            </li>";
        }
        echo "
            <li class='email-inbox-tab' id='email-inbox-tab-plus' 
             title='".CHtml::encode (Yii::t('emailInboxes', 'Add an Inbox'))."'>
                <a href='#'>+</a>
            </li>
        </ul>";

        echo 
            "<div id='email-inbox-tab-settings-dialog' class='form' style='display: none;'>
                <label for='Profile[emailInboxes]'>".
                    CHtml::encode (Yii::t('emailInboxes', 'Visible tabs:')).
                "</label>".
                  CHtml::dropDownList(
                    'Profile[emailInboxes]',
                    array_map (function ($inbox) {
                        return $inbox->id;
                    }, $visibleEmailInboxes),
                    $tabOptions,
                    array(
                        'class'=>'email-inbox-tabs-multiselect',
                        'multiple'=>'multiple',
                        'size'=>8
                    ))."
            </div>";
    }

    public function setSummaryText () {

        /* add a dropdown to the summary text that let's user set how many rows to show on each 
           page */
        $this->summaryText =  Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{emailCount}</b>',
            array (
                '{emailCount}' => $this->emailCount,
            ));
        /*.'<div class="form no-border" style="display:inline;"> '
        .CHtml::dropDownList(
            'resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(),
            array(
                'ajax' => array(
                    'url' => Yii::app()->controller->createUrl('/profile/setResultsPerPage'),
                    'data' => 'js:{results:$(this).val()}',
                    'complete' => 'function(response) { 
                        $.fn.yiiGridView.update("'.$this->id.'"); 
                    }',
                ),
                'id' => 'resultsPerPage'.$this->id,
                'style' => 'margin: 0;',
                'class' => 'x2-select resultsPerPage',
            )
        ).'</div>';*/
    }

    public function setPager () {
        $this->pager = array (
            'class' => $this->pagerClass, 
            'header' => '',
            'htmlOptions' => array (
                'id' => $this->namespacePrefix . 'Pager'
            ),
            'firstPageCssClass' => '',
            'lastPageCssClass' => '',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'firstPageLabel' => '',
            'lastPageLabel' => '',
            'maxButtonCount' => 0,
        );
    }


}

?>
