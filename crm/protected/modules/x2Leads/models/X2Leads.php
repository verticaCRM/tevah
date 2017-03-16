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
 * This is the model class for table "x2_leads".
 *
 * @package application.modules.x2Leads.models
 */
class X2Leads extends X2Model {

	/**
	 * Returns the static model of the specified AR class.
	 * @return X2Leads the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_x2leads'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'x2Leads'
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
			'X2ModelConversionBehavior' => array(
				'class' => 'application.components.recordConversion.X2ModelConversionBehavior',
			),
            'ContactsNameBehavior' => array(
                'class' => 'application.components.ContactsNameBehavior',
                'overwriteName' => false,
            ),
		));
	}

	public static function getNames() {
		$arr = X2Leads::model()->findAll();
		$names = array(0=>'None');
		foreach($arr as $x2Leads)
			$names[$x2Leads->id] = $x2Leads->name;

		return $names;
	}

	public static function getX2LeadsLinks($accountId) {
		$allX2Leads =
            X2Model::model('X2Leads')->findAllByAttributes(array('accountName'=>$accountId));

		$links = array();
		foreach($allX2Leads as $model) {
			$links[] = CHtml::link($model->name,array('/x2Leads/x2Leads/view','id'=>$model->id));
		}
		return implode(', ',$links);
	}

	public function search($resultsPerPage=null, $uniqueId=null) {
		$criteria=new CDbCriteria;
		$parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));
        
		return $this->searchBase($criteria, $resultsPerPage);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}

}
