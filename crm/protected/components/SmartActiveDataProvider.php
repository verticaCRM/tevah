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
 * CActiveDataProvider with persistent sort order and filters and optional id checksum calculation
 *
 * @package application.components
 */
class SmartActiveDataProvider extends CActiveDataProvider {

    public $uid = null;
    public $dbPersistentGridSettings = false;
    public $disablePersistentGridSettings = false;

    /**
     * @var bool $calculateChecksum If true, id checksum will be calculated when data is fetched.
     *  This also disables the ability to refresh the data provider 
     */
    public $calculateChecksum; 

    /**
     * @var string $_idChecksum checksum of ids joined by commas 
     */
    private $_idChecksum; 

    /**
     * @var array record ids used to calculate the checksum 
     */
    private $_recordIds; 

	private $_pagination;
    private $_countCriteria;

    /**
     * Overrides parent __construct ().
     * @param string $uid (optional) If set, will be used to uniquely identify this data
     *  provider. This overrides the default behavior of using the model name as the uid.
	 *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function __construct($modelClass,$config=array()) {
        if (isset ($config['dbPersistentGridSettings'])) {
            $this->dbPersistentGridSettings = $config['dbPersistentGridSettings'];
        }
        if (isset ($config['disablePersistentGridSettings'])) {
            $this->disablePersistentGridSettings = $config['disablePersistentGridSettings'];
        }
        if (isset ($config['uid'])) {
            $this->uid = $config['uid'];
        }
        if(is_string($modelClass))
        {
            $this->modelClass=$modelClass;
            $this->model=$this->getModel($this->modelClass);
            if (property_exists ($this->model, 'uid')) {
                $this->model->uid = $this->uid;
            }
        }
        elseif($modelClass instanceof CActiveRecord)
        {
            $this->modelClass=get_class($modelClass);
            $this->model=$modelClass;
        }
        /* x2modstart */  
        $this->attachBehaviors (array (
            'SmartDataProviderBehavior' => array (
                'class' => 'SmartDataProviderBehavior',
                'settingsBehavior' => ($this->dbPersistentGridSettings ?
                    'GridViewDbSettingsBehavior' : 'GridViewSessionSettingsBehavior'),
            )
        ));
        /* x2modend */ 
    

        /* x2modstart */     
        if ($this->uid !== null) {
            $this->setId($this->uid);
        } else {
        /* x2modend */ 
            $this->setId(CHtml::modelName($this->model));
        /* x2modstart */ 
        }
        /* x2modend */ 

        foreach($config as $key=>$value)
            $this->$key=$value;

        /* x2modstart */ 
        $this->storeSettings ();
        /* x2modend */ 
    }

	/**
	 * Returns the pagination object.
	 * @return CPagination the pagination object. If this is false, it means the pagination is 
     *  disabled.
	 */
	public function getPagination() {
		if($this->_pagination===null) {
            $this->_pagination = $this->getSmartPagination ();
        } 
        return $this->_pagination;
	}

	/**
     * Overrides parent fetchData ().
	 * Fetches the data from the persistent data storage.
	 *
	 * Modified to always sort by id DESC as well as the chosen sort
	 * @return array list of data items
     * @throws CException if checksum couldn't be generated correctly
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	protected function fetchData() {
		$criteria=clone $this->getCriteria();
        /* x2modstart */ 
        $criteria->with = array();
        /* x2modend */ 

		if(($pagination=$this->getPagination())!==false) {
			$pagination->setItemCount($this->getTotalItemCount());
			$pagination->applyLimit($criteria);
		}

        // prevent side effects to model's criteria by cloning criteria and then restoring
		$baseCriteria=$this->model->getDbCriteria(false);

		if(($sort=$this->getSort())!==false) {
			// set model criteria so that CSort can use its table alias setting
			if($baseCriteria!==null) {
				$c=clone $baseCriteria;
				$c->mergeWith($criteria);
				$this->model->setDbCriteria($c);
			} else
				$this->model->setDbCriteria($criteria);
			$sort->applyOrder($criteria);
		}

        /* x2modstart */ 
		$orderBy = $criteria->order;
		if(!preg_match('/\bid\b/',$orderBy)) {
			if(!empty($orderBy))
				$orderBy .= ',';
			$orderBy .= 't.id DESC';
			$criteria->order = $orderBy;
		}
        /* x2modend */ 

		$this->model->setDbCriteria($baseCriteria!==null ? clone $baseCriteria : null);
		$data=$this->model->findAll($criteria);
		$this->model->setDbCriteria($baseCriteria);  // restore original criteria

        /* x2modstart */ 
        // using the same criteria, calculate and save the checksum of the ids 
		$this->model->setDbCriteria($baseCriteria!==null ? clone $baseCriteria : null);
        if ($this->calculateChecksum) {
            $critClone = clone $criteria;
            $critClone->limit = -1;
            $critClone->offset = -1;
            $critClone->select = array ('t.id');
            $command = $this->model->findAll ($critClone, array (), true);
            if (!$command) {
                throw new CException ('could not generate checksum, invalid command');
            }
            $ids = $command->queryColumn ();

            // attempt to verify ids array
            if (count ($ids) !== intval ($this->totalItemCount)) {
                throw new CException ('could not generate checksum');
            }
            $this->_idChecksum = $this->calculateChecksumFromIds ($ids);
            $this->_recordIds = $ids;
        }
		$this->model->setDbCriteria($baseCriteria);  // restore original criteria
        /* x2modend */ 

		return $data;
	}

    /* x2modstart */ 

    /**
     * @throws CException if fetchData has not yet been called
     */
    public function getIdChecksum () {
        if (!$this->calculateChecksum) {
            throw new CException ('calculcateChecksum is set to false');
        }
        if (!isset ($this->_idChecksum)) {
            throw new CException ('fetchData must be called before getting the id checksum');
        }
        return $this->_idChecksum;
    }

    /**
     * @throws CException if fetchData has not yet been called
     */
    public function getRecordIds () {
        if (!$this->calculateChecksum) {
            throw new CException ('calculcateChecksum is set to false');
        }
        if (!isset ($this->_recordIds)) {
            throw new CException ('fetchData must be called before getting the id checksum');
        }
        return $this->_recordIds;
    }
    /* x2modend */ 

	/**
	 * Returns the data items currently available.
	 * @param boolean $refresh whether the data should be re-fetched from persistent storage.
	 * @return array the list of data items currently available in this data provider.
	 */
	public function getData($refresh=false)
	{
        /* x2modstart */ 
        if ($this->calculateChecksum && $refresh) {
            throw new CException ('refresh cannot be called if calculcateChecksum is set to true');
        }
        /* x2modend */ 
        return parent::getData ($refresh);
	}

	/**
	 * Returns the key values associated with the data items.
	 * @param boolean $refresh whether the keys should be re-calculated.
	 * @return array the list of key values corresponding to {@link data}. Each data item in 
     *   {@link data}
	 * is uniquely identified by the corresponding key value in this array.
	 */
	public function getKeys($refresh=false)
	{
        /* x2modstart */ 
        if ($this->calculateChecksum && $refresh) {
            throw new CException ('refresh cannot be called if calculcateChecksum is set to true');
        }
        /* x2modend */ 
        return parent::getKeys ($refresh);
	}

	/**
	 * Returns the total number of data items.
	 * When {@link pagination} is set false, this returns the same value as {@link itemCount}.
	 * @param boolean $refresh whether the total number of data items should be re-calculated.
	 * @return integer total number of possible data items.
	 */
	public function getTotalItemCount($refresh=false)
	{
        /* x2modstart */ 
        if ($this->calculateChecksum && $refresh) {
            throw new CException ('refresh cannot be called if calculcateChecksum is set to true');
        }
        /* x2modend */ 
        return parent::getTotalItemCount ($refresh);
	}

    /**
     * Generates the item count without eager loading, to improve performance.
     * @return type
     */
    public function calculateTotalItemCount(){
        if($this->model instanceof X2Model) {
            if(!isset($this->_countCriteria)) {
                $this->_countCriteria = clone $this->getCriteria();
                $this->_countCriteria->with = array();
            }
            return X2Model::model($this->modelClass)->count($this->_countCriteria);
        }else{
            return parent::calculateTotalItemCount();
        }
    }

    /* x2modstart */ 
    /**
     * @param array ids array on integers
     * @return string
     */
    public static function calculateChecksumFromIds (array $ids) {
        return md5 (implode (',', $ids));
    }
    /* x2modend */ 

    /* x2modstart */ 
    /**
     * Applies default order to criteria 
     */
    private function applyDefaultOrder ($criteria) {
		$orderBy = $criteria->order;
		if(!preg_match('/\bid\b/',$orderBy)) {
			if(!empty($orderBy))
				$orderBy .= ',';
			$orderBy .= 't.id DESC';
			$criteria->order = $orderBy;
		}
    }
    /* x2modend */ 

    /**
     * Moved out of fetchData so that it could be reused elsewhere 
     */
    private function applySortOrder ($criteria) {
		if(($sort=$this->getSort())!==false) {
            /* x2modstart */ 
            // prevent side effects to model's criteria by cloning & modifying it, then restoring
            // to original.
		    $baseCriteria=$this->model->getDbCriteria(false);
            /* x2modend */ 

			// set model criteria so that CSort can use its table alias setting
			if($baseCriteria!==null) {
				$c=clone $baseCriteria;
				$c->mergeWith($criteria);
				$this->model->setDbCriteria($c);
			} else
				$this->model->setDbCriteria($criteria);
			$sort->applyOrder($criteria);

            /* x2modstart */ 
		    $this->model->setDbCriteria($baseCriteria);
            /* x2modend */ 
		}
    }

}
