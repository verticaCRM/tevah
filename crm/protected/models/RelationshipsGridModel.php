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
 * Handles relationship grid attribute rendering and filtering
 */

class RelationshipsGridModel extends CModel {

    /**
     * @var CActiveRecord $relatedModel
     */
    public $relatedModel; 

    /**
     * @var string $myModelName
     */
    public $myModel; 

    /**
     * @var int $id
     */
    public $id; 

    /**
    * Added these to fix the readonly error 
    */
    public $name;

    public $assignedTo;

    public $label;

    public $createDate;

    public function __construct ($scenario=null) {
        if ($scenario) {
            $this->setScenario ($scenario);
        }
        if ($scenario === 'search' && isset ($_GET[get_called_class ()])) {
            $this->setAttributes ($_GET[get_called_class ()], false);
        }
    }

    public function attributeNames () {
        return array (
            'name',
            'relatedModelName',
            'assignedTo',
            'label',
            'createDate',
        );
    }

    public function getName () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->name;
    }

    public function getLabel () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->getRelationshipLabel (
            $this->myModel->id, get_class ($this->myModel));
    }

    public function getCreateDate () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->createDate;
    }

    public function getAssignedTo () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->assignedTo;
    }

    public function renderAttribute ($name) {
        switch ($name) {
            case 'name':
                echo $this->relatedModel->link;
                break;
            case 'relatedModelName':
                echo $this->getRelatedModelName ();
                break;
            case 'assignedTo':
                echo $this->relatedModel->renderAttribute ('assignedTo');
                break;
            case 'label':
                echo $this->getLabel ();
                break;
            case 'createDate':
                echo X2Html::dynamicDate ($this->relatedModel->createDate);
                break;
        }
    }

    public function filterModels (array $gridModels) {
        $filteredModels = array ();
        $that = $this;
        $filters = array_filter ($this->attributeNames (), function ($a) use ($that) {
            return $that->$a !== '' && $that->$a !== null;
        });
        
        foreach ($gridModels as $model) {
            $filterOut = false;
            foreach ($filters as $filter) {
                $val = $this->$filter;
                switch ($filter) {
                    case 'name':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('name'));
                        break;
                    case 'relatedModelName':
                        $filterOut = $val !== get_class ($model->relatedModel);
                        break;
                    case 'assignedTo':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('assignedTo.fullName'));
                        break;
                    case 'label':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getRelationshipLabel (
                                $this->myModel->id, get_class ($this->myModel)));
                        break;
                    case 'createDate':
                        $timestampA = Formatter::parseDate ($val); 
                        $timestampB = Formatter::parseDate (
                            $model->relatedModel->getAttribute ('createDate')); 
                        $filterOut = $timestampA !== $timestampB;
                        break;
                }
                if ($filterOut) break;
            }
            if (!$filterOut)
                $filteredModels[] = $model;
        }
        return $filteredModels;
    }

//    public function sortModels (array $gridModels, $sortKey) {
//        if (!isset ($_GET[$sortKey])) return;
//        $sortOrder = explode ('.', $_GET[$sortKey]);
//        if (count ($sortOrder) > 1) $direction = $sortOrder[1];
//        else $direction = 'asc';
//        $sortAttr = $sortOrder[0];
//        @usort ($gridModels, function ($a, $b) use ($sortAttr, $direction) { 
//            if ($a->$sortAttr < $b->$sortAttr) {
//                return ($direction === 'asc' ? -1 : 1);
//            } elseif ($a->$sortAttr > $b->$sortAttr) {
//                return ($direction === 'asc' ? 1 : -1);
//            } else {
//                return 0;
//            }
//        });
//        return $gridModels;
//    }

    public function setRelatedModelName ($name) {
        $this->_relatedModelType = $name;
    }

    private $_relatedModelType; 
    public function getRelatedModelName () {
        if (!isset ($this->_relatedModelType)) {
            if (!isset ($this->relatedModel)) {
                return ($this->_relatedModelType = null);
            }
            $title = Yii::app()->db->createCommand()
                ->select("title")
                ->from("x2_modules")
                ->where("name = :name AND custom = 1")
                ->bindValues(array(":name" => get_class ($this->relatedModel)))
                ->queryScalar();
            if ($title)
                $this->_relatedModelType = $title;
            else
                $this->_relatedModelType = X2Model::getModelTitle (get_class ($this->relatedModel));
        }
        return $this->_relatedModelType;
    }

}

?>


