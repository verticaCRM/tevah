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

/* @edition:pla */

/**
 * Component class to render calendar actions in the
 * iCal format, see RFC5545: http://tools.ietf.org/html/rfc5545
 * Usage: Load up the relevent actions into an Ical object,
 * and then call render.
 *    $ical = new Ical;
 *    $ical->setActions($actions);
 *    $ical->render();
 * @author Raymond Colebaugh <raymond@x2engine.com>
 */
class Ical {
    private $_actions;

    public function setActions($actions) {
        if (is_array($actions))
            $this->_actions = $actions;
    }
    
    public function getActions() {
        return $this->_actions;
    }
    
    /**
     * Escape the following characters: ,'";\ and \n
     * according to the RFC spec
     */
    public function escapeText($str) {
        $str = preg_replace('/([,\'";\\\\])/', "\\\\$1", $str);
        return preg_replace('/\n/', '\\\\n', $str);
    }

    /**
     * Writes output for the set $_actions in the iCal format
     */
    public function render() {
        echo "BEGIN:VCALENDAR\r\n";
        echo "VERSION:2.0\r\n";
        echo "PRODID:-//X2Engine//NONSGML X2 Calendar//EN\r\n";
        
        if (is_array($this->_actions)) {
            $actionsPrinted = array();
            $users = array();
            foreach($this->_actions as $action) {
                // skip events showing up more than once (i.e. combined cal)
                if(!($action instanceof Actions) || !empty($actionsPrinted[$action->id])) 
                    continue;
                // Render a VEVENT for each action
                echo "BEGIN:VEVENT\r\n";
                // Treat the first assignee as the one to use for generating the "organizer" 
                // and all of the assignees for the unique ID field
                $assignees = $action->getAssignees();
                if(!empty($assignees)) {
                    $first = reset($assignees);
                    echo "UID:".$action->assignedTo."-".$action->id."@".$_SERVER['SERVER_NAME']."\r\n";
                                   
                    if (!empty($first) && !array_key_exists($first, $users)) {
                        // cache user emails
                        $user = User::model()->findByAttributes(array('username'=>$first));
                        $users[$first] = array(
                            'name'=>$user->name, 
                            'email'=>$user->emailAddress,
                            'timeZone'=>$user->profile->timeZone
                        );
                    }
                    $tz = $users[$first]['timeZone'];
                    echo "ORGANIZER;CN=\"".$users[$first]['name']."\":mailto:".$users[$first]['email']."\r\n";
                } else {
                    // Default app timezone (which is actually stored as the column default value)
                    $tz = Profile::model()->tableSchema->columns['timeZone']->defaultValue;
                    echo "UID:Anyone-{$action->id}@{$_SERVER['SERVER_NAME']}\r\n";
                }
                $start = new DateTime();
                $end = new DateTime();
                $tzOb = new DateTimeZone($tz);
                $start->setTimestamp($action->dueDate);
                $end->setTimestamp($action->completeDate);
                $start->setTimezone($tzOb);
                $end->setTimezone($tzOb);
                echo "DTSTART;TZID=$tz:".$start->format('Ymd\THis')."\r\n";
                echo "DTEND;TZID=$tz:".$end->format('Ymd\THis')."\r\n";
                echo "SUMMARY:".self::escapeText($action->actionText->text)."\r\n";
                echo "END:VEVENT\r\n";
                $actionsPrinted[$action->id] = true;
            }
        }

        echo "END:VCALENDAR\r\n";
    }
}
