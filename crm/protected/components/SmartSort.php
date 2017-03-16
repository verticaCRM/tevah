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
 * Filters GET parameters by unique id to prevent issues that arise when multiple instances of X2GridView  * are on the same page.
 *
 * @package application.components
 */
class SmartSort extends CSort {

    public $uniqueId;

    /**
     * Overrides parent __construct ().
     * @param string $uniqueId (optional) If set, will be used to uniquely identify this sort
     *  instance.
	 *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function __construct($modelClass=null, $uniqueId=null)
    {
        $this->modelClass=$modelClass;
        /* x2modstart */ 
        if ($uniqueId !== null) {
            $this->uniqueId = $uniqueId;
            $this->sortVar = $uniqueId."_sort";
        }
        /* x2modend */ 
    }

    /**
     * Added filtering of GET params so that only those related to the current sort get used to
     * generate the url.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function createUrl($controller,$directions)
    {
        $sorts=array();
        foreach($directions as $attribute=>$descending)
            $sorts[]=$descending ? $attribute.$this->separators[1].$this->descTag : $attribute;
        /* x2modstart */ 
        $explicitParams = $this->params!==null; 
        /* x2modend */ 
        $params=$this->params===null ? $_GET : $this->params;
        $params[$this->sortVar]=implode($this->separators[0],$sorts);
        /* x2modstart */ 
        if (!$explicitParams) {
            foreach ($params as $key => $val) {
                 if (!preg_match ("/(^id$)|(^".
                    (isset ($this->uniqueId) ? $this->uniqueId : $this->modelClass)."_)/", $key)) {

                    unset ($params[$key]);
                 }
            }
        }
        /* x2modend */ 

        return $controller->createUrl($this->route,$params);
    }
}
