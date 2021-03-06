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

Yii::import('application.components.util.ResponseUtil');

/**
 * Behavior class providing utilities for responding in a uniform yet also
 * context-sensitive manner. Utilizes the standalone class {@link ResponseUtil}.
 *
 * @property boolean $exitNonFatal (write-only) Sets the value of
 *  {@link ResponseUtil::$exitNonFatal}.
 * @property boolean $isConsole If true, run methods as though there's no HTTP
 *  request happening.
 * @property string $logCategory The log category to which informational output
 *  should be sent.
 * @property boolean $longErrorTrace (write-only) Sets the value of
 *  {@link ResponseUtil::$longErrorTrace}
 * @property ResponseUtil $response The response utility singleton
 * @property boolean $shutdown (write-only) Sets the value of
 *  {@link ResponseUtil::$shutdown}
 * @package application.components
 */
class ResponseBehavior extends CBehavior {

    /**
     * If true: the error handling methods of {@link ResponseUtil} should be
     * used.
     */
    public $handleErrors = false;
    
    /**
     * If true: the exception handling method
     * {@link ResponseUtil::respondWithException} should be used
     * @var type
     */
    public $handleExceptions = false;

	private $_isConsole;

    /**
     * These properties will be automatically "mirrored" in instances of this
     * class. In other words, the setter method for this class will map the
     * property to the similarly-named property in {@link ResponseUtil}.
     * @var array
     */
    private static $_ruProperties = array(
        'errorCode',
        'exitNonFatal',
        'longErrorTrace',
        'shutdown'
    );

    private $_logCategory = 'application';

    public function __construct(){
        // Establish a graceful shutdown method by default:
        $this->ruProperty('shutdown',"Yii::app()->end();");
    }

    /**
     * 
     * @param type $owner
     */
    public function attach($owner){
        parent::attach($owner);
        $this->ruProperty('includeExtraneousOutput',YII_DEBUG);
        if($this->handleErrors) {
            if(method_exists('ResponseUtil','respondWithError'))
                set_error_handler('ResponseUtil::respondWithError');
            if(method_exists('ResponseUtil','respondFatalErrorMessage'))
                register_shutdown_function('ResponseUtil::respondFatalErrorMessage');
        }
        if($this->handleExceptions
                && method_exists('ResponseUtil','respondWithException')) {
    		set_exception_handler('ResponseUtil::respondWithException');
        }

    }

    ////////////////////
    // Getter Methods //
    ////////////////////

	/**
	 * {@link isConsole}
	 * @return bool
	 */
	public function getIsConsole(){
        if(!isset($this->_isConsole)) {
            $this->_isConsole = ResponseUtil::isCli();
        }
		return $this->_isConsole;
	}

    /**
     * {@link logCategory}
     * @return type
     */
    public function getLogCategory() {
        return $this->_logCategory;
    }

	/**
	 * Returns the response utility object in use.
	 */
	public function getResponse(){
        if(!ResponseUtil::getObject()) {
            // Instantiate a new object
            new ResponseUtil();
        }
		return ResponseUtil::getObject();
	}

	/**
	 * Incorporate more properties into the response.
     * 
	 * @param array $properties
	 */
	public function mergeResponse(array $properties) {
        foreach($properties as $name => $value) {
            $this->response[$name] = $value;
        }
	}
    
    /**
     * A web-safe wrapper for {@link respond()}
     *
     * For use when logging (and in console commands, output) are needed, but
     * halting is not.
     *
     * @param string $msg Message to log/respond with
     * @param bool $error Whether an error has occurred
     * @param bool $halt If true (default) and the $level argument is "error",
     *  the application will halt after printing the error message; otherwise it
     *  will continue.
     */
    public function output($msg,$error=false) {
        Yii::log($msg,$error ? 'error' : 'trace',$this->_logCategory);
        if($this->isConsole) {
            // Perform both logging and response:
            $this->respond($msg,$error);
        }
    }

    /**
     * Wrapper method for
     * @param type $msg
     * @param type $error
     */
    public function respond($msg,$error=false) {
        ResponseUtil::respond($msg,$error);
    }

    ////////////////////
    // Setter Methods //
    ////////////////////

    /**
     * Sets a named static property of {@link ResponseUtil}, if it exists.
     *
     * This is a means of hedging the behavior against backwards compatibility
     * glitches of versions 3.5 - 3.7.5 wherein ResponseUtil was not declared
     * as a dependency (despite how it was later) and thus not updated during
     * self-refreshes.
     *
     * @param type $name
     * @param type $value
     */
    public function ruProperty($name,$value) {
        if(property_exists('ResponseUtil',$name))
            ResponseUtil::${$name} = $value;
    }

    /**
     * Set the default error code in {@link ResponseUtil}
     * @param integer $value
     */
    public function setErrorCode($value) {
        $this->ruProperty('errorCode',(integer) $value);
    }

	/**
	 * Sets {@link ResponseUtil::$exitNonFatal}
	 * @return bool
	 */
	public function setExitNonFatal($value){
		$this->ruProperty('exitNonFatal',(bool) $value);
	}

	/**
	 * {@link isConsole}
	 */
	public function setIsConsole($value){
		$this->_isConsole = $value;
	}

    /**
     * {@link logCategory}
     */
    public function setLogCategory($value) {
        $this->_logCategory = $value;
    }

    /**
	 * {@link longErrorTrace}
	 */
	public function setLongErrorTrace($value){
		$this->ruProperty('longErrorTrace',(bool) $value);
    }

    public function setShutdown($value) {
        $this->ruProperty('shutdown',(bool) $value);
    }
}

?>
