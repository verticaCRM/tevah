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

    public $autoLoginOnlyOnce = true;

    public static function setUpBeforeClass () {
        /* x2tempstart */ 
        // quick way of getting leads data until we extend reference fixtures to web test
        // cases
        Yii::app()->db->createCommand ("
        DELETE from x2_x2leads where id in (1, 2, 3, 4, 5);
        INSERT INTO x2_x2leads values 
            (1, 'test', 'test_1', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (2, 'test', 'test_2', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (3, 'test', 'test_3', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (4, 'test', 'test_4', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            (5, 'test', 'test_5', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        ")->execute ();
        /* x2tempend */
    }

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
    protected function visitPages ($pages, $testXss = false) {
        foreach ($pages as $page) {
            print ('visiting page ' .$page."\n");
            $this->assertNoPHPErrors ($page);
            if ($testXss)
                $this->assertElementNotPresent ('css=.TESTX2INJECTION');
        }
    }

	public function testPages () {
        $this->visitPages ( $this->allPages );
	}

    public $allPages = array(
        // contacts
        'contacts/index',
        'contacts/id/1195',
        'contacts/update/id/1195',
        'contacts/shareContact/id/1195',
        'contacts/viewRelationships/id/1195',
        'contacts/lists',
        'contacts/myContacts',
        'contacts/createList',
        // accounts
        'accounts/index',
        'accounts/update/id/1',
        'accounts/1',
        'accounts/create',
        'accounts/shareAccount/id/1',
        /* x2prostart */ 
        'accounts/accountsReport',
        /* x2proend */ 
        // marketing
        'marketing/index',
        'marketing/create',
        'marketing/5',
        'marketing/update/id/5',
        'weblist/index',
        'weblist/view?id=18',
        'weblist/update?id=18',
        'marketing/webleadForm',
        /* x2prostart */ 
        'marketing/webTracker',
        /* x2proend */ 
        // leads
        'x2Leads/index',
        'x2Leads/create',
        'x2Leads/1',
        'x2Leads/update/id/1',
        'x2Leads/delete/id/1',
        // opportunities
        'opportunities/index',
        'opportunities/51',
        'opportunities/create',
        'opportunities/51',
        'opportunities/update/id/51',
        // services
        'services/index',
        'services/3',
        'services/create',
        'services/update/id/3',
        'services/createWebForm',
        // actions
        'actions/index',
        'actions/create',
        'actions/1',
        'actions/update/id/1',
        'actions/shareAction/id/1',
        'actions/viewGroup',
        'actions/viewAll',
        // calendar
        'calendar/index',
        'calendar/myCalendarPermissions',
        'calendar/userCalendarPermissions',
        'calendar/userCalendarPermissions/id/1',
        // docs
        'docs/index',
        'docs/create',
        'docs/createEmail',
        'docs/createQuote',
        'docs/1',
        'docs/update/id/1',
        'docs/changePermissions/id/1',
        'docs/exportToHtml/id/1',
        // workflow
        'workflow/index',
        'workflow/create',
        'workflow/1?perStageWorkflowView=true',
        'workflow/1?perStageWorkflowView=false',
        'workflow/update/id/1',
        // products
        'products/index',
        'products/1',
        'products/create',
        'products/update/id/1',
        //'site/printRecord/1?modelClass=Product&pageTitle=Product%3A+Semiconductor',
        // quotes
        'quotes/index',
        'quotes/indexInvoice',
        'quotes/1',
        'quotes/convertToInvoice/id/1',
        'quotes/create',
        'quotes/update/id/1',
        /* x2prostart */ 
        // reports
        'reports/gridReport',
        'reports/leadPerformance',
        'reports/savedReports',
        'reports/dealReport',
        'reports/workflow',
        'reports/activityReport',
        'reports/servicesReport',
        /* x2proend */ 
        // charts
        'charts/leadVolume',
        'charts/marketing',
        'charts/pipeline',
        'charts/sales',
        // media
        'media/index',
        'media/1',
        'media/upload',
        'media/update/id/1',
        // groups
        'groups/index',
        'groups/1',
        'groups/update/id/1',
        'groups/create',
        // bug reports
        'bugReports/index',
        'bugReports/create',
        // site
        'site/viewNotifications',
        'site/page?view=iconreference',
        'site/page?view=about',
        'site/bugReport',
        // profile
        'profile/profiles',
        'profile/activity',
        'profile/1',
        'profile/1?publicProfile=1',
        'profile/update/1',
        'profile/settings/1',
        'profile/changePassword/1',
        'profile/manageCredentials'

    );

    public $adminPages = array(
        /* x2prostart */ 
        // studio
        'studio/flowIndex',
        'studio/flowDesigner',
        'studio/triggerLogs',
        'studio/importFlow',
        'studio/exportFlow?flowId=1',
        'studio/flowDesigner/1',
        /* x2proend */ 
        'users/admin',
        'users/1',
        'users/update/id/1',
        'users/inviteUsers',
        'users/create',
        // admin
        'admin/index',
        'admin/editRoleAccess',
        'admin/manageRoles',
        'admin/manageSessions',
        'admin/setLeadRouting',
        'admin/roundRobinRules',
        'admin/workflowSettings',
        'admin/addCriteria',
        'admin/setServiceRouting',
        'studio/flowIndex',
        'studio/importFlow',
        'admin/appSettings',
        'admin/updaterSettings',
        'admin/manageModules',
        'admin/createPage',
        'admin/googleIntegration',
        'admin/toggleDefaultLogo',
        'admin/uploadLogo',
        'admin/updater',
        'admin/activitySettings',
        'admin/publicInfo',
        'admin/lockApp',
        'admin/manageActionPublisherTabs',
        'admin/x2CronSettings',
        'admin/changeApplicationName',
        'admin/setDefaultTheme',
        'admin/emailSetup',
        'admin/emailDropboxSettings',
        'admin/importModels',
        'admin/importModels?model=X2Leads',
        'admin/importModels?model=Actions',
        'admin/importModels?model=Product',
        'admin/importModels?model=Quotes',
        'admin/importModels?model=Services',
        'admin/importModels?model=Contacts',
        'admin/importModels?model=Accounts',
        'admin/exportModels',
        'admin/exportModels?model=Actions',
        'admin/export',
        'admin/import',
        'admin/rollbackImport',
        'admin/viewChangelog',
        'admin/index?translateMode=1',
        'admin/translationManager',
        'admin/manageTags',
        'admin/userViewLog',
        'admin/createModule',
        'admin/manageFields',
        'admin/manageDropDowns',
        'admin/editor',
        'admin/deleteModule',
        'admin/importModule',
        'admin/exportModule',
        'admin/renameModules',
        'admin/convertCustomModules',
    );

}

?>
