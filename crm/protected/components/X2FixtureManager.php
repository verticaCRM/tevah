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

Yii::import('system.test.CDbFixtureManager');

/**
 * @package application.components
 */
class X2FixtureManager extends CDbFixtureManager {

    /**
     * @var bool $loadFixture
     */
    public $loadFixtures; 

    public function __construct () {
        $this->loadFixtures = LOAD_FIXTURES;
    }

    public function prepare () {
        if ($this->loadFixtures) parent::prepare ();
    }

	/**
	 * Override of {@link CDbFixtureManager}'s resetTable 
	 * 
	 * Permits array-style definition of init scripts much like fixture files
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function resetTable($tableName) {
        /* x2modstart */ 
        if (!$this->loadFixtures) return;
        /* x2modend */ 
		$initFile = $this->basePath . DIRECTORY_SEPARATOR . $tableName . $this->initScriptSuffix;
		if (is_file($initFile)) {
			$tbl_data = require($initFile);
			if (is_array($tbl_data)) {
				Yii::app()->db->createCommand()->truncateTable($tableName);
				foreach ($tbl_data as $rec)
					Yii::app()->db->createCommand()->insert($tableName, $rec);
			}
		} else {
			$this->truncateTable($tableName);
        }
	}

    /**
	 * Override of {@link CDbFixtureManager}'s loadFixture 
     *
     * Modified to enable fixture file suffixing. A fixture file suffix can be specified by 
     *  setting a value in the fixtures array to an array with two properties:
     *      array (<tableName|modelClass>, <file suffix>)
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function loadFixture($tableName,/* x2modstart */ $suffix=''/* x2modend */)
    {
            $fileName=$this->basePath.DIRECTORY_SEPARATOR.$tableName/* x2modstart */.$suffix.
                /* x2modend */'.php';
            if(!is_file($fileName))
                    return false;

            $rows=array();
            $schema=$this->getDbConnection()->getSchema();
            $builder=$schema->getCommandBuilder();
            $table=$schema->getTable($tableName);

            foreach(require($fileName) as $alias=>$row)
            {
                    /* x2modstart */ 
                    if ($this->loadFixtures) {
                    /* x2modend */ 
                        $builder->createInsertCommand($table,$row)->execute();
                        $primaryKey=$table->primaryKey;
                        if($table->sequenceName!==null)
                        {
                                if(is_string($primaryKey) && !isset($row[$primaryKey]))
                                        $row[$primaryKey]=$builder->getLastInsertID($table);
                                elseif(is_array($primaryKey))
                                {
                                        foreach($primaryKey as $pk)
                                        {
                                                if(!isset($row[$pk]))
                                                {
                                                        $row[$pk]=$builder->getLastInsertID($table);
                                                        break;
                                                }
                                        }
                                }
                        }
                    /* x2modstart */ 
                    }
                    /* x2modend */ 
                    $rows[$alias]=$row;
            }
            return $rows;
    }

    /**
	 * Override of {@link CDbFixtureManager}'s load 
     *
     * Modified to enable fixture file suffixing. A fixture file suffix can be specified by 
     *  setting a value in the fixtures array to an array with two properties:
     *      array (<tableName|modelClass>, <file suffix>)
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function load($fixtures)
    {
            $schema=$this->getDbConnection()->getSchema();
            $schema->checkIntegrity(false);

            $this->_rows=array();
            $this->_records=array();
            foreach($fixtures as $fixtureName=>$tableName)
            {
                    /* x2modstart */  
                    $suffix = null;
                    if (is_array ($tableName))
                    {
                        $suffix = $tableName[1];
                        $tableName = $tableName[0];
                    }
                    /* x2modend */  

                    if($tableName[0]===':')
                    {
                            $tableName=substr($tableName,1);
                            unset($modelClass);
                    }
                    else
                    {
                            $modelClass=Yii::import($tableName,true);
                            $tableName=CActiveRecord::model($modelClass)->tableName();
                    }
                    if(($prefix=$this->getDbConnection()->tablePrefix)!==null)
                            $tableName=preg_replace('/{{(.*?)}}/',$prefix.'\1',$tableName);
                    /* x2modstart */ 
                    if ($this->loadFixtures) {
                        $this->resetTable($tableName);
                    }
                    /* x2modend */ 
                    $rows=$this->loadFixture($tableName/* x2modstart */,$suffix/* x2modend */);
                    if(is_array($rows) && is_string($fixtureName))
                    {
                            $this->_rows[$fixtureName]=$rows;
                            if(isset($modelClass))
                            {
                                    foreach(array_keys($rows) as $alias)
                                            $this->_records[$fixtureName][$alias]=$modelClass;
                            }
                    }
            }

            $schema->checkIntegrity(true);
    }

	/**
	 * Returns the specified ActiveRecord instance in the fixture data.
	 * @param string $name the fixture name
	 * @param string $alias the alias for the fixture data row
	 * @return CActiveRecord the ActiveRecord instance. False is returned if there is no such fixture row.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function getRecord($name,$alias)
	{
		if(isset($this->_records[$name][$alias]))
		{
			if(is_string($this->_records[$name][$alias]))
			{
				$row=$this->_rows[$name][$alias];

                /* x2modstart */ 
                if ($this->loadFixtures) {
                /* x2modend */ 
                    $model=CActiveRecord::model($this->_records[$name][$alias]);
                    $key=$model->getTableSchema()->primaryKey;
                    if(is_string($key))
                        $pk=$row[$key];
                    else
                    {
                        foreach($key as $k)
                            $pk[$k]=$row[$k];
                    }
//                    if ($model instanceof X2ListItem) {
//                        // special case for X2ListItem since it has a pseudo-composite-primary-key
//                        if (isset ($pk['contactId']) && $pk['contactId'] === null) {
//                            unset ($pk['contactId']);
//                        }
//                        $this->_records[$name][$alias]=$model->findByAttributes($pk);
//                    } else {
                        $this->_records[$name][$alias]=$model->findByPk($pk);
                    //}
                /* x2modstart */ 
                } else {
                    $model = CActiveRecord::model ($this->_records[$name][$alias]);
                    if (isset ($row['id'])) {
                        $this->_records[$name][$alias]=$model->findByPk($row['id']);
                    } else {
                        $this->_records[$name][$alias]=$model->findByAttributes($row);
                    }
                }
                /* x2modend */ 
			}
			return $this->_records[$name][$alias];
		}
		else
			return false;
	}

}

?>
