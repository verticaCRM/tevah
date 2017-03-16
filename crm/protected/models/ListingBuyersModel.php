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

class ListingBuyersModel extends CModel {

    /**
     * @var CActiveRecord $relatedModel
     */
    public $relatedModel = 'clistings';

    /**
     * @var string $myModelName
     */
    public $myModel = 'clistings';

    /**
     * @var int $id
     */
    public $id;

    /**
     * Added these to fix the readonly error
     */
    public $name;

    public $assignedTo;

    public $description;

    public $c_release_status;

    public $c_is_hidden;

    public $phone;

    public $nameId;

    public $phone2;

    public $email;

    public $createDate;

    public $c_date_released;

    public $city;

    public $c_name_dba_c;

    public $c_listing_id;

    public $state;

    public $country;

    public $c_seller;

    public $c_listing_date_approved_c;

    public $leadstatus;

    public $c_create_by_buyer;

    public $c_created_by_user;

    public $c_added_from;

    public $c_released_by;

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
            'assignedTo',
            'description',
            'createDate',
            'c_date_released',
            'c_release_status',
            'c_is_hidden',
            'phone',
            'nameId',
            'phone2',
            'state',
            'country',
            'city',
            'c_listing_id',
            'c_name_dba_c',
            'c_seller' ,
            'c_listing_date_approved_c',
            'leadstatus',
            'c_create_by_buyer',
            'c_created_by_user',
            'c_added_from',
            'c_released_by'


        );
    }

    public function getName () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->name;
    }

    public function getDescription () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->description;
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

    public function getReleaseStatus () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->c_release_status;
    }

    public function getHiddenStatus () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->c_is_hidden;
    }

    public function getListingCity () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->phone;
    }

    public function getCreatedByUser () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->c_created_by_user;
    }

    public function getCreatedByBuyer () {
        if (!isset ($this->relatedModel)) return null;
        return $this->relatedModel->c_create_by_buyer;
    }

    public function renderAttribute ($name) {
        switch ($name) {
            case 'name':
                echo $this->relatedModel->link;
                break;
            case 'assignedTo':
                echo $this->relatedModel->renderAttribute ('assignedTo');
                break;
            case 'c_release_status':
                echo $this->relatedModel->renderAttribute ('c_release_status');
                break;
            case 'c_date_released':
                echo $this->relatedModel->renderAttribute ('c_date_released');
                break;
            case 'c_is_hidden':
                echo $this->relatedModel->renderAttribute ('c_is_hidden');
                break;
            case 'description':
                echo $this->relatedModel->renderAttribute ('description');
                break;
            case 'phone':
                echo $this->relatedModel->Contacts->renderAttribute ('phone');
                break;
            case 'nameId':
                echo $this->relatedModel->Contacts->renderAttribute ('nameId');
                break;
            case 'phone2':
                echo $this->relatedModel->Contacts->renderAttribute ('phone2');
                break;
            case 'state':
                echo $this->relatedModel->Contacts->renderAttribute ('state');
                break;
            case 'country':
                echo $this->relatedModel->Contacts->renderAttribute ('country');
                break;
            case 'city':
                echo $this->relatedModel->Contacts->renderAttribute ('city');
                break;
            case 'c_listing_id':
                echo $this->relatedModel->renderAttribute ('c_listing_id');
                break;
            case 'c_create_by_buyer':
                echo $this->relatedModel->renderAttribute ('c_create_by_buyer');
                break;
            case 'c_created_by_user	':
                echo $this->relatedModel->renderAttribute ('c_created_by_user	');
                break;
            case 'c_added_from':
                echo $this->relatedModel->renderAttribute ('c_added_from');
                break;
            case 'c_released_by':
                echo $this->relatedModel->renderAttribute ('c_released_by');
                break;
            case 'c_name_dba_c':
                echo $this->relatedModel->Contacts->renderAttribute ('c_name_dba_c');
                break;
            case 'leadstatus':
                echo $this->relatedModel->Contacts->renderAttribute ('leadstatus');
                break;
            case 'c_seller':
                echo $this->relatedModel->Clisings->renderAttribute ('c_seller');
                break;
            case 'c_listing_date_approved_c':
                echo $this->relatedModel->Clisings->renderAttribute ('c_listing_date_approved_c');
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
                    case 'description':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('description'));
                        break;
                    case 'relatedModelName':
                        $filterOut = $val !== get_class ($model->relatedModel);
                        break;
                    case 'assignedTo':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('assignedTo.fullName'));
                        break;
                    case 'c_is_hidden':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_is_hidden'));
                        break;
                    case 'c_listing_id':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_listing_id'));
                        break;
                    case 'c_create_by_buyer':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_create_by_buyer'));
                        break;
                    case 'c_created_by_user':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_created_by_user'));
                        break;
                    case 'c_added_from':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_added_from'));
                        break;
                    case 'c_released_by':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_released_by'));
                        break;
                    case 'c_release_status':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->getAttribute ('c_release_status'));
                        break;
                    case 'phone':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Contacts->getAttribute ('phone'));
                        break;
                    case 'nameId':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Contacts->getAttribute ('nameId'));
                        break;
                    case 'phone2':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Contacts->getAttribute ('phone2'));
                        break;
                     case 'state':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Contacts->getAttribute ('state'));
                        break;
                    case 'c_name_dba_c':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Contacts->getAttribute ('c_name_dba_c'));
                        break;
                    case 'city':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Contacts->getAttribute ('city'));
                        break;
                    case 'leadstatus':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Contacts->getAttribute ('leadstatus'));
                        break;
                    case 'c_seller':
                        $filterOut = !preg_match (
                            '/'.$val.'/i',
                            $model->relatedModel->Clisings->getAttribute ('c_seller'));
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

    public function sortModels (array $gridModels, $sortKey) {
        if (!isset ($_GET[$sortKey])) return;
        $sortOrder = explode ('.', $_GET[$sortKey]);
        if (count ($sortOrder) > 1) $direction = $sortOrder[1];
        else $direction = 'asc';
        $sortAttr = $sortOrder[0];
        @usort ($gridModels, function ($a, $b) use ($sortAttr, $direction) {
            if ($a->$sortAttr < $b->$sortAttr) {
                return ($direction === 'asc' ? -1 : 1);
            } elseif ($a->$sortAttr > $b->$sortAttr) {
                return ($direction === 'asc' ? 1 : -1);
            } else {
                return 0;
            }
        });
        return $gridModels;
    }


}

?>


