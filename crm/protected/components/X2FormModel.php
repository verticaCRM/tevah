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

class X2FormModel extends CFormModel {

    /**
     * @var bool if true, instead of adding error messages to the model, exceptions will be thrown 
     *  with error message
     */
    public $throwExceptions = false;

    /**
     * @var CException type of exception that will get thrown if $throwExceptions is true
     */
    public $exceptionClass = 'CHttpException';

    public function validate ($attributes=null, $clearErrors=true) {
        $valid = parent::validate ($attributes, $clearErrors);
        if (!$valid && $this->throwExceptions)
            throw new $exceptionClass (400, CJSON::encode ($this->getErrors ()));
        return $valid;
    }

}

?>
