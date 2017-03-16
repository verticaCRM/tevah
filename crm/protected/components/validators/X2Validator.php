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
 * Miscellaneous additions to CValidator including option to have errors reported in the form 
 * of exceptions. Certain bad inputs will not occur during normal form submission and indicate
 * either a programming error a malicious request. In either of these cases it makes sense to 
 * throw an exception instead of adding errors to the model.
 */

abstract class X2Validator extends CValidator {

    /**
     * @var bool if true, instead of adding error messages to the model, exceptions will be thrown 
     *  with error message
     */
    public $throwExceptions = false;

    /**
     * @var CException type of exception that will get thrown if $throwExceptions is true
     */
    public $exceptionClass = 'CHttpException';

    /**
     * @var bool if true attribute will validate if it's empty 
     */
    public $allowEmpty = false;

    protected $object;

    protected $attribute;

    /**
     * A nicer-to-use version of CValidator's validateAttribute ()
     */
    abstract protected function validateValue (CModel $object, $value, $attribute);

    /**
     * Allows use of validateValue in place of CValidator validateAttribute. Also sets $object
     * and $attribute instance properties.
     */
    protected function validateAttribute ($object, $attribute) {
        $value = $object->$attribute;
        if ($this->allowEmpty && empty ($value)) return;
        $this->object = $object;
        $this->attribute = $attribute;
        return $this->validateValue ($object, $value, $attribute);
    } 

    /**
     * Adds error to model or if $throwExceptions is true, throws an exception.
     * @throws CException if validateAttribute () hasn't been called 
     */
    protected function error ($message) {
        if (!isset ($this->object) || !isset ($this->attribute)) {
            throw new CException (
                'Precondition violated: validateAttribute must be called before this method');
        }
        if ($this->throwExceptions) {
            if ($this->exceptionClass === 'CHttpException') {
                throw new $this->exceptionClass (400, $message);
            } else {
                throw new $this->exceptionClass ($message);
            }
        } else {
            $this->addError ($this->object, $this->attribute, $message);
        }
    }
}
