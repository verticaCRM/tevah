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
 * Class for formatting pretty console messages.
 *
 * Formatting methods are intended to be chained, i.e.
 *
 * $formatter->bgColor($backgroundColor)->bold()->color($textColor)->format();
 *
 * Only "format" returns the message with the escape sequences. Note also,
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package application.commands
 */
class ConsoleFormatterUtil {

    public $msg;

    /**
     * ANSI escape sequence codes
     * @var type
     */
    private $_seq = array(
        'clr' => '0',
        'bold' => '1',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'purple' => '35',
        'cyan' => '36',
        'white' => '37',
        'bg_black' => '40',
        'bg_red' => '41',
        'bg_magenta' => '45',
        'bg_yellow' => '43',
        'bg_green' => '42',
        'bg_blue' => '44',
        'bg_cyan' => '46',
        'bg_light_gray' => '47',
    );

    public function __construct($msg){
        $this->msg = $msg;
    }

    public function bgColor($color) {
        $color = isset($this->_seq["bg_$color"]) ? $this->_seq["bg_$color"] : $color;
        $this->msg = "\033[".$color.'m'.$this->msg;
        return $this;
    }

    /**
     * Returns a message in boldface.
     * @param type $msg
     */
    public function bold(){
        $this->msg = "\033[".$this->_seq['bold'].'m'.$this->msg;
        return $this;
    }

    /**
     * Returns a message with color sequences applied.
     *
     * @param type $msg
     * @param type $color
     * @return type
     */
    public function color($color){
        $color = isset($this->_seq[$color]) ? $this->_seq[$color] : $color;
        $this->msg = "\033[".$color.'m'.$this->msg;
        return $this;
    }

    public function format(){
        return $this->msg."\033[".$this->_seq['clr'].'m';
    }
}

?>
