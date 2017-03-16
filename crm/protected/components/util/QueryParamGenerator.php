<?php
/***********************************************************************************
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
 **********************************************************************************/

/**
 * Utility class for simplifying generation SQL parameters
 */
class QueryParamGenerator extends CComponent {

    /**
     * Number of parameters bound 
     */
    private $count = 0;

    /**
     * Parameter namespace 
     */
    private $prefix = ':QueryParamGenerator';

    /**
     * Bound parameters 
     */
    private $params = array ();

    /**
     * @param string $prefix parameter prefix which should be used to prevent parameter name 
     *  collisions
     */
    public function __construct ($prefix=':QueryParamGenerator') {
        $this->prefix = $prefix;
    }

    /**
     * Binds and array of parameters, optionally generating a string representation of the array 
     * which can be embedded into a SQL in statement
     */
    public function bindArray (array $values, $createInStmt=false) {
        if ($createInStmt) {
            $inStmt = '(';
        }
        foreach ($values as $val) {
            $currParam = $this->prefix.++$this->count;
            $this->params[$currParam] = $val;
            if ($createInStmt) {
                if ($inStmt !== '(') {
                    $inStmt .= ',';
                }
                $inStmt.=$currParam;
            }
        }
        if ($createInStmt) {
            $inStmt .= ')';
            return $inStmt;
        }
    }

    /**
     * Bind a value to a parameter name 
     * @return string the generated parameter name
     */
    public function nextParam ($val) {
        $currParam = $this->prefix.++$this->count;
        $this->params[$currParam] = $val;
        return $currParam;
    }

    /**
     * @return string the name of the most recently generated parameter
     */
    public function currParam () {
        return $this->prefix.$this->count;
    }

    public function setParam ($val) {
        $this->params[$this->currParam ()] = $val;
    }

    /**
     * @return array all generated parameters (bound values indexed by parameter names)
     */
    public function getParams () {
        return $this->params;
    }

    /**
     * Merge the internal parameters array with an arbitrary number of other parameters arrays
     * @param {...array}
     * @throws CException if parameter name collision occurs 
     */
    public function mergeParams () {
        $arguments = func_get_args ();
        foreach ($arguments as $params) {
            if (count (array_intersect (array_keys ($params), $this->params))) {
                throw new CException ('parameter name collision');
            }
            $this->params = array_merge ($this->params, $params);
        }
    }
}
