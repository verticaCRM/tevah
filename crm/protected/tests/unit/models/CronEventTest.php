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

/* @edition:pro */

/**
 * Tests for CronEvent
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CronEventTest extends X2TestCase {

    public function testRecur() {
        // Expected use: "time" should never be in the future when this method
        // runs.
        $e = new CronEvent;
        $e->interval = 5;
        // Scenario:
        // Plain and simple. Event was less than one interval into the past.
        // This is the expected use most of the time.
        // Expected: add one interval
        $now = time();
        $time = $now-1;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time + $e->interval, $e->time);
        $this->assertEquals($now,$e->lastExecution);
        
        // Scenario:
        // Event was more than one interval into the past.
        // Expected: add two intervals
        $now = time();
        $time = $now-($e->interval+1);
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time + 2*$e->interval,$e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was exactly one interval into the past.
        // Expected: add two intervals.
        $now = time();
        $time = $now-$e->interval;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time + 2*$e->interval,$e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was right now.
        // Expected: add one interval.
        $now = time();
        $time = $now;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($time+$e->interval,$e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was multiple intervals into the past and then some.
        // Expected: add as many intervals as necessary to put the execution
        // time ahead of now.
        $now = time();
        $time = $now - (10*$e->interval) - 2;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($now + $e->interval - 2, $e->time);
        $this->assertEquals($now,$e->lastExecution);

        // Scenario:
        // Event was a round multiple of intervals into the past.
        // Expected: add that number of intervals plus one.
        $now = time();
        $time = $now - 11*$e->interval;
        $e->time = $time;
        $e->recur(false);
        $this->assertEquals($now + $e->interval, $e->time);
        $this->assertEquals($now,$e->lastExecution);
    }

}

?>
