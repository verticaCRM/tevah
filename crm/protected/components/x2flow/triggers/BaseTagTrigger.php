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
 * X2FlowTrigger
 *
 * @package application.components.x2flow.triggers
 */
abstract class BaseTagTrigger extends X2FlowTrigger {
	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelClass' => 'modelClass',
			'options' => array(
				array(
                    'name'=>'modelClass',
                    'label'=>Yii::t('studio','Record Type'),
                    'type'=>'dropdown',
                    'options'=>X2Flow::getModelTypes(true)
                ),
				array(
                    'name'=>'tags',
                    'label'=>Yii::t('studio','Tags'),
                    'type'=>'tags'
                ),
			)
        );
	}

	public function check(&$params){
        $tags = $this->config['options']['tags']['value'];
        $tags = is_array($tags) ? $tags : Tags::parseTags($tags, true);
        if(!empty($tags) && isset($params['tags'])){ // Check passed params to be sure they're set
            if(!is_array($params['tags'])){
                $params['tags'] = explode(',', $params['tags']);
            }
            $params['tags'] = array_map(function($item){ 
                return preg_replace('/^#/','', $item); 
            }, $params['tags']);

            // must have at least 1 tag in the list:
            if(count(array_intersect($params['tags'], $tags)) > 0){
                return $this->checkConditions($params);
            }else{
                return array(false, Yii::t('studio','No tags on the record matched those in the tag trigger criteria.'));
            }
        }else{ // config is invalid or record has no tags (tags are not optional)
            return array(false, empty($tags) ? Yii::t('studio','No tags in the trigger criteria!') : Yii::t('studio','Tags parameter missing!'));
        }
    }
}
