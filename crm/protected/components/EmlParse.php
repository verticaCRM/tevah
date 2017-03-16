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

/* @edition:pro */

Yii::import('application.components.LGPL.PlancakeEmailParser');
Yii::import('application.models.*');
Yii::import('application.models.EmlRegex');

/**
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmlParse extends PlancakeEmailParser {

    public $testing = false;

    public $_fwHeaderRegex;

    public $_forwardedFrom;

    public $forwardedPatternGroup;

    public $fwPatterns;

    public $collapsedBody = null;

    public $zapLineBreaks = false;

    private $_cleanBody;

    /**
     * Junk encountered during parsing.
     * 
     * @var array
     */
    public $junk = array();

    /**
     * Remove junk text from the email and optionall collapse linebreaks
     *
     * @param bool $collapse Whether to remove linebreaks induced by RFC standard
     * @param bool $forwardedOnly Whether to omit everything in the body before the attached forwarded message
     * @return str
     */
    public function bodyCleanup($forwardedOnly = true, $rebuild = false){
        $collapse = $this->zapLineBreaks;
        if(isset($this->_cleanBody) && !$rebuild)
            return $this->_cleanBody;
        $message = $this->getBody();
        if(count($this->junk)){
            if($forwardedOnly)
                $junkStart = min(array_map(function($s) use($message){
                                    return strpos($message, $s);
                                }, $this->junk));
            $message = substr($message, $junkStart);
            foreach($this->junk as $junk)
                $message = str_replace($junk, '', $message);
        }
        $body_v1 = explode("\n", $message);
        $body_v2 = array();
        $matches = array();

        // Refuse characters from RFC spec or certain email clients that aren't
        // removed / converted properly by Plancake in some cases:
        $strayJunk = array(
            // Special charcters
            '=[A-F0-9]{2}',
            // EOL character
            '=\s*$',
            // Stray blockquotes and extra space at line beginnings:
            '^\>\s*',
            // Stray whitespace at the beginnings of lines
            '^\s*',
            // Hideous asterisk-wrap-based faux-formatting in plaintext bodies
            '(\*{2,}|\*\s*$|^\s*\*)',
        );
        // Same category as above, only these loathsome string entities will be
        // replaced with a single whitespace (because they show up in places
        // in place of whitespaces) and after initial formatting because they
        // themselves contain no spaces. Unfortunately, in the case of this sole
        // instance so far, GMail inserts preemptive linebreaks  at word
        // boundaries to avoid splitting words when keeping to 78 columns, which
        // results in extremely bizzarre and unpredictable stray linebreaks.
        $strayJunk_space = array(
            // Faux link tag inserted by GMail in plaintext bodies
            '<http:\\/\\/[^>]+>'
        );
        // Lines to be skipped over
        $skipLines = array(
            // double-quoted sections
            '>>\s+.*',
            // a type of separator
            '\-{3}?[A-Za-z0-9]+.*',
            // multi-part message separators
            '\-{2}bound\d+--\s*',
            // another type of separator
            '\-{6}_=_.*',
            // all other forms of separators
            '\-+_(?:.*_?)+',
            // mimetype comments
            'This is a multi\-part.*',
            // dangling character set declarations
            '\s*charset="?.*"?',
            // "Image tag"
            '\[[a-z]+: \w+\].*',
        );

        // Lines at which to halt body iteration
        $fieldPattern = ltrim(EmlRegex::fieldPattern(), '^');
        $breakLines = array(
            // Hotmail signature
            '_________________________________________________________________\s*',
            // "Original Message" original quoted email header
            '-*\s*Original Message.*-*',
            // "Date wrote" original quoted email header
            '>*\s*On.*wrote:.*',
            // "Date wrote" original quoted email header with dashes
            '-{3}.*On.*wrote:.*',
            // A hideous, random ancient email software encountered once that
            // uses the windows-1252 charset and "formats" replied-to messages
            // by wrapping them in asterisks and does not properly blockquote
            // them by beginning their lines with angle brackets
            '\*From:\*.*',
            $fieldPattern, // header fields in quoted messages
        );

        $skipPattern = '/^('.implode('|', $skipLines).')$/i';
        $breakPattern = '/^('.implode('|', $breakLines).')$/i';
        $junkPattern = '/('.implode('|', $strayJunk).')/';
        $junkPattern_space = '/('.implode('|', $strayJunk_space).')/';

        // Extraneous junk removal:
        foreach($body_v1 as $line){
            if(preg_match($breakPattern, $line, $matches)){
                break;
            }elseif(preg_match($skipPattern, $line, $matches)){
                continue;
            }else{
                $body_v2[] = trim($line);
            }
        }
        // Remove special EOL characters and blockquotes
        foreach($body_v2 as $key => $line)
            $body_v2[$key] = trim(preg_replace($junkPattern, '', $line));

        $body_v3 = array();
        if($collapse){
            // Attempt to remove all the "extra" RFC-spec-induced linebreaks
            $i_v2 = 0;
            $i_v3 = 0;
            $n_lines = count($body_v2);
            while($i_v2 < $n_lines){
                $multiline = false;
                $body_v3[$i_v3] = '';
                $line = $body_v2[$i_v2]; // Next line in
                // Iterate over a paragraph, ignoring quoted lines. Lines longer
                // than 60 columns are treated as having been artificially broken.
                while(strlen($line) > 40 && $i_v2 < $n_lines - 1){
                    $body_v3[$i_v3] .= ' '.ltrim($line);
                    $i_v2++;
                    $line = $body_v2[$i_v2];
                    $multiline = true;
                }
                // Concatenate with the final line (or human-inserted linebreak)
                // at the end of the paragraph. If the most recent line didn't
                // qualify as being in a paragraph, it's simply put into the
                // current line.
                $body_v3[$i_v3] = trim($body_v3[$i_v3].' '.ltrim($line));
                // If the final line at the end of/after the paragraph was empty,
                //  add it as a new line:
                if(preg_match('/^\s*$/', $line) && $multiline)
                    $body_v3[$i_v3] .= "\n";
                $body_v3[$i_v3] = preg_replace($junkPattern_space, ' ', $body_v3[$i_v3]);
                $i_v2++;
                $i_v3++;
            }
        } else{
            foreach($body_v2 as $key => $line){
                $body_v3[$key] = trim(preg_replace($junkPattern_space, ' ', $line));
            }
        }

        // Final clean-up: Replace triple+ linebreaks with double linebreaks.
        $this->_cleanBody = trim(preg_replace('/\n{3,}/', "\n\n", implode("\n", $body_v3)));
        return $this->_cleanBody;
    }

    /**
     * Override of Plancake's getTo
     * 
     * Converts raw "to" addresses into objects of similar structure to those
     * returned by other methods by passing it through getProperAddress. This 
     * makes it so that we can rely on the return type.
     * 
     * @param type $raw
     * @return type 
     */
    public function getTo($raw = false){
        $toArr = parent::getTo();
        if($raw)
            return $toArr;
        $to = array();

        foreach($toArr as $addr){
            $toThis = explode('<', $addr);
            $address = trim(rtrim($toThis[count($toThis) > 1 ? 1 : 0], '>'));
            $to[] = $this->getProperAddress(array('name' => trim($toThis[0], " \t\n\r\0\x0B\""), 'address' => $address));
        }
        return $to;
    }

    /**
     * Override of Plancake's getFrom method. This uses the getProperFrom to 
     * return an object with placeholders (if the name in the from field is empty)
     * @return type
     * @throws Exception 
     */
    public function getFrom(){
        if((!isset($this->rawFields['from'])) || (!count($this->rawFields['from'])))
            throw new Exception("Couldn't find the sender of the email");
        else{
            $from = explode('<', $this->rawFields['from']);
            $sender = array();
            if(count($from) > 1){ // Name available in the from field
                $sender['name'] = trim($from[0], '" ');
                $sender['address'] = rtrim($from[1], "> ");
            }else{ // User doesn't have email set up to send with name in from
                $sender['name'] = '';
                $sender['address'] = trim($from[0]);
            }
            return $this->getProperAddress($sender);
        }
    }

    /**
     * Parses the "from" email address out of the body of the email, which 
     * contains a forwarded message.
     * @parameter $ignoreEmptyName
     * @return type 
     */
    public function getForwardedFrom($ignoreEmptyName=false){
        if(!isset($this->fwPatterns))
            $this->fwPatterns = X2Model::model('EmlRegex')->findAll();
        if(!isset($this->_forwardedFrom)){
            $parsed = array();
            $from = array();
            $body = $this->getBody();
            foreach($this->fwPatterns as $fwPattern){
                if($parsed = $fwPattern->matchHeader($body)){
                    foreach($parsed as $groupKey => $groupCont){
                        $value = trim($groupCont);
                        if(strstr($groupKey, '_address') || strstr($groupKey, '_name')){
                            if(!empty($value)){
                                $metameta = explode('_', $groupKey);
                                $from[$metameta[1]] = $value;
                            }
                        }elseif(strstr($groupKey, '_junk') && trim($groupCont) != null){
                            $this->junk[] = $groupCont;
                        }
                    }
                    // Must be able to find both name and address.
                    if(!((empty($from['name']) && !$ignoreEmptyName) || empty($from['address']))){
                        $this->forwardedGroupName = $fwPattern->groupName;
                        $this->_forwardedFrom = $this->getProperAddress($from);
                        if (strstr($this->_forwardedFrom->name, ",")) {
                            // Reassemble a name in the format "Last, First"
                            $names = explode(',', $this->_forwardedFrom->name);
                            if (count($names) == 2) {
                                $fullname = trim($names[1])." ".trim($names[0]);
                                $this->_forwardedFrom->name = $fullname;
                            }
                        }
                        return $this->_forwardedFrom;
                    }
                }
            }
            if((empty($this->_forwardedFrom->name) && !$ignoreEmptyName) || empty($this->_forwardedFrom->address))
                throw new Exception('Unrecognized forwarded email format!');
        }
        return $this->_forwardedFrom;
    }

    /**
     * Wrapper for the final part of the getFrom method; substitutes a placeholder
     * for the field if there is no email or full name.
     *
     * @param array $from The first index is the
     * @return type
     */
    public function getProperAddress(array $addr){
        $properFrom = $addr;

        if(empty($addr['name']) || empty($addr['address'])){
            if(!empty($addr['name']))
                $properFrom['address'] = '';
            elseif(!empty($addr['address']))
                $properFrom['name'] = "UnknownFirstName UnknownLastName";
            else
                throw new Exception('Parsed email address is empty!');
        } else if($addr['name'] === $addr['address']){
            $properFrom['name'] = "UnknownFirstName UnknownLastName";
        }
        return (object) $properFrom;
    }

}

?>
