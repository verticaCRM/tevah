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

/* @edition:pla */

/**
 * Allows methods to be shared between classes assocatiated with fingerprint records (Contacts,
 * AnonContacts).
 *
 * @package application.components
 */
class FingerprintBehavior extends CBehavior {

	public function events() {
		return array_merge(parent::events(),array(
			'onAfterDelete'=>'afterDelete',
		));
	}


    /**
     * Update the fingerprint record associated with this record.
     */
    public function setFingerprint($fingerprint, $attributes) {
        if (ctype_digit($fingerprint)) {
            if (isset($this->owner->fingerprintId)) {
                // already associated with a fingerprint
                $fingerprintRecord = X2Model::model('Fingerprint')->findByPk(
                    $this->owner->fingerprintId);
            } else {
                // lookup fingerprint by hash
                $fingerprintRecord = X2Model::model('Fingerprint')->findByAttributes(
                    array('fingerprint' => $fingerprint));
                if (!isset($fingerprintRecord)) {
                    // create a new fingerprint
                    $fingerprintRecord = new Fingerprint();
                    $fingerprintRecord->createDate = time();
                    if (get_class ($this->owner) === 'AnonContact')
                        $fingerprintRecord->anonymous = true;
                    else
                        $fingerprintRecord->anonymous = false;
                }
            }

            // update the fingerprint hash
            $fingerprintRecord->fingerprint = $fingerprint;

            // update the fingerprint attributes
            foreach ($attributes as $attr => $value) {
                if (is_array($value))
                    $value = json_encode($value);
                $fingerprintRecord->$attr = $value;
            }

            if (!$fingerprintRecord->save()) {
                AuxLib::debugLogR ($fingerprintRecord->getErrors ());
            }

            // update the fingerprint pseudo-foreign key
            $this->owner->fingerprintId = $fingerprintRecord->id;
        }
    }

    /**
     * Record the last hostname or IP address associated
     * with a fingerprint
     */
    public function recordAddress() {
        $contact = $this->owner;
        $ip = Yii::app()->controller->getRealIp();
        $contact->reverseIp = (Yii::app()->settings->performHostnameLookups && !empty($ip))?
            gethostbyaddr($ip) : $ip;
        if (!$contact->isNewRecord)
            $contact->update(array('reverseIp'));
    }

    /**
     * Delete fingerprint if no other record links to it 
     */
    public function afterDelete () {
        $fingerprint = $this->owner->fingerprint; 
        if ($fingerprint instanceof Fingerprint) {
            $contacts = Contacts::model ()->findAllByAttributes (array (
                'fingerprintId' => $fingerprint->id
            ));
            $anonContacts = AnonContact::model ()->findAllByAttributes (array (
                'fingerprintId' => $fingerprint->id
            ));

            if (sizeof ($contacts) === 0 && sizeof ($anonContacts) === 0) {
                $fingerprint->delete ();
            }
        }
    }

}
?>
