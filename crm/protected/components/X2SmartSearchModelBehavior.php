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
 * Allows instances of CActiveRecord to save grid sort/filters in the session. Similar to X2Model's
 * searchBase () method.
 * @package application.components
 */
class X2SmartSearchModelBehavior extends CBehavior {

    public function getSort(){
        $attributes = array();
        foreach($this->owner->attributes as $name => $val) {
            $attributes[$name] = array(
                'asc' => 't.'.$name.' ASC',
                'desc' => 't.'.$name.' DESC',
            );
        }
        return $attributes;
    }

    public function smartSearch ($criteria, $pageSize=null) {
        $sort = new SmartSort (get_class($this->owner), isset ($this->owner->uid) ? 
            $this->owner->uid : get_class ($this->owner));
        $sort->multiSort = false;
        $sort->attributes = $this->owner->getSort();
        $sort->defaultOrder = 't.lastUpdated DESC, t.id DESC';

        if (!$pageSize) {
            if (!Yii::app()->user->isGuest) {
                $pageSize = Profile::getResultsPerPage();
            } else {
                $pageSize = 20;
            }
        }

        $dataProvider = new SmartActiveDataProvider(get_class($this->owner), array(
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
            'criteria' => $criteria,
            'uid' => $this->owner->uid,
            'dbPersistentGridSettings' => $this->owner->dbPersistentGridSettings));
        $sort->applyOrder($criteria);
        return $dataProvider;
    }
}
?>
