<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * Behavior to provide requisite methods for checking for potential duplicate
 * records. Currently only implemented in Contacts and Accounts.
 */
class X2DuplicateBehavior extends CActiveRecordBehavior {

    // Set constants so that we can change these in the future without issue
    CONST DUPLICATE_FIELD = 'dupeCheck';
    CONST DUPLICATE_LIMIT = 5;

    /**
     * Returns whether or not any duplicate records exist in the database. 
     * 
     * Commonly used as a gate in an if statement for other duplicate 
     * checking functionality.
     * @return boolean 
     */
    public function checkForDuplicates() {
        if ($this->owner->{X2DuplicateBehavior::DUPLICATE_FIELD} == 0) {
            $criteria = $this->getDuplicateCheckCriteria();
            return $this->owner->count($criteria) > 0;
        }
        return false;
    }

    /**
     * Return a list of potential duplicate records.
     * 
     * Capts at 5 records unless a special parameter is provided so as to prevent
     * possible server crashes from attempting to render large numbers of records.
     * @param boolean $getAll Whether to return all records or just 5
     * @return CActiveDataProvider
     */
    public function getDuplicates($getAll = false) {
        $criteria = $this->getDuplicateCheckCriteria();
        if ($getAll && !empty($criteria->limit)) {
            $criteria = $this->getDuplicateCheckCriteria(true);
        }
        if (!$getAll) {
            $criteria->limit = X2DuplicateBehavior::DUPLICATE_LIMIT;
        }
        return $this->owner->findAll($criteria);
    }

    /**
     * Returns the total number of duplicates found (unrestricted by the limit on
     * getDuplicates)
     * @return int
     */
    public function countDuplicates() {
        $criteria = $this->getDuplicateCheckCriteria();
        return $this->owner->count($criteria);
    }

    /**
     * Mark a record as a duplicate.
     * 
     * Set all relevant fields to the proper values for marking a record as duplicate.
     * A duplicate record is private and assigned to the admin user, and if there
     * are options for "doNotCall" and "doNotEmail" they need to be turned on.
     * Alternatively, the "delete" string can be passed to delete the record instead
     * of hiding it. This functionality exists in case some future code requires
     * more things to be done on deleting duplicates.
     * @param string $action
     */
    public function markAsDuplicate($action = 'hide') {
        if ($action === 'hide') {
            if ($this->owner->hasAttribute('visibility')) {
                $this->owner->visibility = 0;
            }
            if ($this->owner->hasAttribute('assignedTo')) {
                $this->owner->assignedTo = Yii::app()->params->adminProfile->username;
            }
            if ($this->owner->hasAttribute('doNotCall')) {
                $this->owner->doNotCall = 1;
            }
            if ($this->owner->hasAttribute('doNotEmail')) {
                $this->owner->doNotEmail = 1;
            }
            $this->owner->{X2DuplicateBehavior::DUPLICATE_FIELD} = 1;
            $this->owner->update();
        } elseif ($action === 'delete') {
            $this->owner->delete();
        }
    }

    /**
     * Reset dupeCheck field if duplicate defining fields are changed.
     * 
     * Records have a concept of "duplicate-defining fields" which are the fields
     * that are checked when searching for duplicates (name, email, etc.). If one
     * of those fields is changed in an update, the dupeCheck parameter needs to
     * be reset and the record needs to be checked for possible duplicates again.
     * @param CEvent $event
     */
    public function afterSave($event) {
        if (!$this->owner->getIsNewRecord()) {
            $dupeFields = $this->owner->duplicateFields();
            $oldAttributes = $this->owner->getOldAttributes();
            foreach ($dupeFields as $field) {
                if (array_key_exists($field, $oldAttributes) &&
                        $oldAttributes[$field] !== $this->owner->$field) {
                    $this->resetDuplicateField();
                    break;
                }
            }
        }
    }

    /**
     * Update the dupeCheck field to reflect that a record has been checked.
     * 
     * Set the value in the current record and use updateByPk so that no validation
     * or behaviors from afterSave are called.
     */
    public function duplicateChecked() {
        if ($this->owner->{X2DuplicateBehavior::DUPLICATE_FIELD} == 0) {
            $this->owner->{X2DuplicateBehavior::DUPLICATE_FIELD} = 1;
            $this->owner->updateByPk($this->owner->id, array(X2DuplicateBehavior::DUPLICATE_FIELD => 1));
        }
    }

    /**
     * Reset the dupeCheck field to its unchecked state.
     */
    public function resetDuplicateField() {
        $this->owner->{X2DuplicateBehavior::DUPLICATE_FIELD} = 0;
        $this->owner->updateByPk($this->owner->id, array(X2DuplicateBehavior::DUPLICATE_FIELD => 0));
    }

    /**
     * Hide all potential duplicate records.
     * 
     * This is equivalent to a mass version of "markAsDuplicate" but it affects
     * records other than the currenly loaded one.
     */
    public function hideDuplicates() {
        $criteria = $this->getDuplicateCheckCriteria(false, null);
        $attributes = array(
            X2DuplicateBehavior::DUPLICATE_FIELD => 1,
        );
        if ($this->owner->hasAttribute('visibility')) {
            $attributes['visibility'] = 0;
        }
        if ($this->owner->hasAttribute('assignedTo')) {
            $attributes['assignedTo'] = Yii::app()->params->adminProf->username;
        }
        if ($this->owner->hasAttribute('doNotCall')) {
            $attributes['doNotCall'] = 1;
        }
        if ($this->owner->hasAttribute('doNotEmail')) {
            $attributes['doNotEmail'] = 1;
        }
        $this->owner->updateAll($attributes, $criteria);
    }

    /**
     * Delete all potential duplicate records.
     */
    public function deleteDuplicates() {
        $criteria = $this->getDuplicateCheckCriteria(false, null);
        $this->owner->deleteAll($criteria);
    }

    /**
     * Private helper function to get the duplicate criteria.
     * 
     * Caches criteria for later use.
     * @param boolean $refresh Force refresh of cached criteria
     * @return CDbCriteria
     */
    private $_duplicateCheckCriteria = array ();
    private function getDuplicateCheckCriteria($refresh = false, $alias='t') {
        if (!$refresh && isset($this->_duplicateCheckCriteria[$alias])) {
            return $this->_duplicateCheckCriteria[$alias];
        }
        $dupeFields = $this->owner->duplicateFields();
        $criteria = new CDbCriteria();
        foreach ($dupeFields as $fieldName) {
            if (!empty($this->owner->$fieldName)) {
                $criteria->compare($fieldName, $this->owner->$fieldName, false, "OR");
            }
        }
        if (empty($criteria->condition)) {
            $criteria->condition = "FALSE";
        } else {
            $criteria->compare('id', "<>" . $this->owner->id, false, "AND");
            if ($this->owner->asa('permissions')) {
                $criteria->mergeWith($this->owner->getAccessCriteria($alias));
            }
        }
        $this->_duplicateCheckCriteria[$alias] = $criteria;
        return $this->_duplicateCheckCriteria[$alias];
    }

}
