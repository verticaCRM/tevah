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

class X2ProgressBar extends X2Widget {

    /**
     * @var string $uid Used to uniquely identify the widget (in html id attributes, JS identifiers,
     *  etc.)
     */
    public $uid; 

    /**
     * @var int $max
     */
    public $max;

    /**
     * @var string $label label to display over progress bar after progress count
     */
    public $label; 

    /**
     * @var string $_JSObjectName name of JS object which manages behavior of progress bar 
     */
    private $_JSObjectName;

    /**
     * Magic getter 
     */
    public function getJSObjectName () {
        if (!isset ($this->_JSObjectName)) {
            $this->_JSObjectName = 'progressBar'.$this->uid;
        }
        return $this->_JSObjectName;
    }

    public function run () {
        $this->render ('_x2ProgressBar', array ());
    }

}
