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
 * @package application.tests.unit.modules.contacts.models
 */
class X2LeadsTest extends X2DbTestCase {

    public $fixtures = array(
        'x2Leads' => array('X2Leads', '.X2LeadsTest'),
    );


    public function testConvertToOpportunity () {
        $lead1 = $this->x2Leads ('lead1');

        $leadAttrs = $lead1->getAttributes ();

        $opportunity = $lead1->convert ('Opportunity');

        $opportunityAttrs = $opportunity->getAttributes ();

        unset ($leadAttrs['id']);
        unset ($leadAttrs['nameId']);
        unset ($leadAttrs['firstName']);
        unset ($leadAttrs['lastName']);
        unset ($leadAttrs['createDate']);
        unset ($opportunityAttrs['id']);
        unset ($opportunityAttrs['nameId']);
        unset ($opportunityAttrs['createDate']);

        VERBOSE_MODE && print_r ($leadAttrs);
        VERBOSE_MODE && print_r ($opportunityAttrs);

        // ensure that opportunity has all attributes of lead, with exceptions
        $this->assertTrue (sizeof (array_diff_assoc ($leadAttrs, $opportunityAttrs)) === 0);

        // test the testing method itself
        $leadAttrs['name'] = '';
        $this->assertFalse (sizeof (array_diff_assoc ($leadAttrs, $opportunityAttrs)) === 0);

    }

}

?>
