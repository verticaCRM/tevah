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

/**
 * 
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class FormatterTest extends X2DbTestCase {

    public $fixtures = array(
        'contacts' => 'Contacts',
        'accounts' => 'Accounts'
    );

    public function testParseFormula() {
        $formula = "='My name is '.".'{name}'.".' and I work at '.".'{company.name}'.".' which '.".'{company.description};';
        $contact = $this->contacts('testFormula');
        $evald = Formatter::parseFormula($formula,array('model'=>$contact));
        $this->assertTrue($evald[0],$evald[1]);
        $this->assertEquals('My name is '.$this->contacts('testFormula')->name
                .' and I work at '.$this->accounts('testQuote')->name
                .' which '.$this->accounts('testQuote')->description,
                $evald[1]);

        // Now let's throw some bad code in there:
        //
        // system call:
        $formula = '=exec("echo YOU SHOULD NOT SEE THIS MESSAGE, EVER");';
        $evald = Formatter::parseFormula($formula,array('model'=>$contact));
        $this->assertFalse($evald[0]);
        $formula = '="Unfortunately, string expressions in formulae with anything
            aside from spaces, alphanumerics and underscores aren\'t supported yet."';
        $evald = Formatter::parseFormula($formula,array());
        $this->assertFalse($evald[0]);

        // Test typecasting:
        //
        // integer:
        $contact->createDate = '1';
        $evald = Formatter::parseFormula("={createDate}+2",array('model'=>$contact));
        $this->assertEquals(3,$evald[0]);
        // boolean:
        $contact->doNotEmail = true;
        $evald = Formatter::parseFormula("={doNotEmail} or false",array('model'=>$contact));
        $this->assertTrue($evald[0]);
        // double:
        $contact->dealvalue = '25.3';
        $evald = Formatter::parseFormula("={dealvalue}*44.1",array('model'=>$contact));
        $this->assertEquals(1115.73,$evald[0]);

    }

    /**
     * Ensure that yiiDateFormatToJQueryDateFormat correctly translates between format languages
     */
    public function testYiiDateFormatToJQueryDateFormat () {
        $formats = array (
            'd MMM y' => 'd M yy',
            'd/MMM/y' => 'd/M/yy',
            'd/MM/y' => 'd/mm/yy',
            'd/MM M/y' => 'd/mm m/yy',
            'd/MM M/y MMM' => 'd/mm m/yy M'
        );
        foreach ($formats as $fmt => $expected) {
            $newFormat = Formatter::yiiDateFormatToJQueryDateFormat ($fmt);
            $this->assertEquals ($expected, $newFormat);
        }
    }

}

?>
