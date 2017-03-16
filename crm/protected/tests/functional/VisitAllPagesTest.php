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

Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
abstract class VisitAllPagesTest extends X2WebTestCase {

    /**
     * visits page and checks for php errors
     * @param string $page URI of page
     */
    protected function assertNoPHPErrors ($page) {
		$this->openX2($page);
		$this->assertElementNotPresent('css=.xdebug-error');
		$this->assertElementNotPresent('css=#x2-php-error');
    }

    /**
     * @param array $pages array of URIs 
     */
    protected function visitPages ($pages) {
        foreach ($pages as $page) {
            print ('visiting page ' .$page."\n");
            $this->assertNoPHPErrors ($page);
        }
    }

	public function testServicesPages () {
        $this->visitPages (array (
            'services/index',
            'services/3',
            'services/create',
            'services/update/id/3',
            'services/servicesReport',
            'services/createWebForm',
        ));
	}

	public function testProfilePages () {
        $this->visitPages (array (
            'profile/1',
            'profile/1?publicProfile=1',
            'profile/update/1',
            'profile/settings/1',
            'profile/changePassword/1',
            'profile/manageCredentials'
        ));
	}

	public function testContactPages () {
        $this->visitPages (array (
            'contacts/index',
            'contacts/id/1195',
            'contacts/update/id/1195',
            'contacts/shareContact/id/1195',
            'contacts/viewRelationships/id/1195',
            'contacts/lists',
            'contacts/myContacts',
            'contacts/createList',
            'contacts/googleMaps',
            'contacts/savedMaps',
        ));
	}

	public function testAccountPages () {
        $this->visitPages (array (
            'accounts/index',
            'accounts/update/id/1',
            'accounts/1',
            'accounts/create',
            'accounts/shareAccount/id/1',
            'accounts/accountsReport',
        ));
	}

	public function testMarketingPages () {
        $this->visitPages (array (
            'marketing/index',
            'marketing/create',
            'marketing/5',
            'marketing/update/id/5',
            'weblist/index',
            'weblist/view?id=18',
            'weblist/update?id=18',
            'marketing/webleadForm',
            'marketing/webTracker',
        ));
	}

	public function testOpportunitiesPages () {
        $this->visitPages (array (
            'opportunities/index',
            'opportunities/51',
            'opportunities/create',
            'opportunities/51',
            'opportunities/update/id/51',
        ));
	}
}

?>
