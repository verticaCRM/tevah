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
 * This is the model class for table "x2_widgets".
 *
 * @package application.models
 * @property integer $id(auto-incremented)
 * @property string $name
 * @property integer $showPROFILE
 * @property integer $adminALLOWS
 * @property integer $showDASH
 * @property integer $userID
 * @property integer $position
 */
class Widgets extends CActiveRecord{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Roles the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'x2_widgets';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name,position', 'required'),
			array('name', 'length', 'max'=>250),
			array('id', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('name,showPROFILE,adminALLOWS,showDASH,userID,position', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'name' => 'Name',
			'showPROFILE' => 'Show Profile',
            'adminALLOWS' => 'Admin Allows',
            'showDASH' => 'Show Dash',
            'userID' => 'User ID',
            'position' => 'Position',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('name',$this->name);
		$criteria->compare('showPROFILE',$this->showPROFILE,true);
        $criteria->compare('adminALLOWS',$this->adminALLOWS,true);
        $criteria->compare('showDASH',$this->showDASH,true);
        $criteria->compare('userID',$this->userID,true);
        $criteria->compare('position',$this->userID,true);
		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
