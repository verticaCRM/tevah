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
 * Made for the purposes of getting pagingation to work properly.
 *
 * @package application.components
 */
class SmartDataProviderBehavior extends CBehavior {

    public $settingsBehavior;

	private $_pagination;

    public function attach ($owner) {
        parent::attach ($owner);
        $this->attachBehaviors (array (
            'settingsBehavior' => array (
                'class' => $this->settingsBehavior,
                'uid' => $this->owner->uid,
                'modelClass' => $this->owner->modelClass,
            )
        ));
    }

    /**
     * Unsets sort order if sort is on an attribute not in list of specified attributes
     * @param array $attrs names of model attributes
     * @return bool true if sort order was unset, false otherwise
     */
//    public function unsetSortOrderIfNotIn (array $attrs) {
//        $sortOrder = $this->getSetting ('sort'); 
//        $sortOrderUnset = false;
//        if (!empty ($sortOrder) && !in_array (preg_replace ('/\.desc$/', '', $sortOrder), $attrs)) {
//            $sortOrder = '';
//            unset ($_GET[$this->owner->modelClass][$this->getSortKey ()]);
//            $sortOrderUnset = true;
//        }
//        $this->saveSetting ('sort', $sortOrder); 
//        return $sortOrderUnset;
//
//    }

    public function getSortKey () {
        return $this->owner->getId()!='' ? $this->owner->getId().'_sort' : 'sort';
    }
    
    public function getPageKey () {
        return $this->owner->getId()!='' ? $this->owner->getId().'_page' : 'page';
    }

    public function storeSettings () {

		//Sort and page saving code modified from:
		//http://www.stupidannoyingproblems.com/2012/04/yii-grid-view-remembering-filters-pagination-and-sort-settings/

        // sort order gets saved in db or session depending on settingsBehavior
		$key = $this->getSortKey ();

		if(!empty($_GET[$key])){
            if (!$this->owner->disablePersistentGridSettings)
			    $val = $this->saveSetting ('sort', $_GET[$key]);
		} else {
            if (!$this->owner->disablePersistentGridSettings)
			    $val = $this->getSetting ('sort');
			if(!empty($val))
				$_GET[$key] = $val;
		}

        // active page always gets stored in session
		$key = $this->getPageKey ();
        $statePrefix = $this->getStatePrefix ();
		if(!empty($_GET[$key])){
			Yii::app()->user->setState($statePrefix.$key, $_GET[$key]);
		} elseif(!empty($_GET["ajax"])){
			// page 1 passes no page number, just an ajax flag
			Yii::app()->user->setState($statePrefix.$key, 1);
		} else {
			$val = Yii::app()->user->getState($statePrefix.$key);
			if(!empty($val))
				$_GET[$key] = $val;
		}

	}

	/**
	 * Returns the pagination object.
	 * @return CPagination the pagination object. If this is false, it means the pagination is 
     *  disabled.
	 */
	public function getSmartPagination() {
		if($this->_pagination===null) {
			$this->_pagination=new RememberPagination;
			if(($id=$this->owner->getId())!='')
				$this->_pagination->pageVar=$id.'_page';
		}
		return $this->_pagination;
	}

}
