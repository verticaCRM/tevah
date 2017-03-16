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
 * X2FlowAction that adds a comment to a record
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRecordListAdd extends X2FlowAction {
	public $title = 'Add to List';
	public $info = 'Add this record to a static list.';

	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelRequired' => 'Contacts',
			'options' => array(
				array('name'=>'listId','label'=>Yii::t('studio','List'),'type'=>'link','linkType'=>'X2List','linkSource'=>Yii::app()->controller->createUrl(
					CActiveRecord::model('X2List')->autoCompleteSource
				)),
			));
	}

	public function execute(&$params) {
        $listIdentifier=$this->parseOption('listId',$params);
        if(is_numeric($listIdentifier)){
            $list = CActiveRecord::model('X2List')->findByPk($listIdentifier);
        }else{
            $list = CActiveRecord::model('X2List')->findByAttributes(
                array('name'=>$listIdentifier));
        }
		if($list !== null && $list->modelName === get_class($params['model'])) {
			if ($list->addIds($params['model']->id)) {
                return array (true, "");
            }
		} 
        return array (false, "");
	}
}
