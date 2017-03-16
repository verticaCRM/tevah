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
Yii::import('application.tests.functional.VisitAllPagesTest');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class VisitAllPagesAsAdminTest extends VisitAllPagesTest {

    /**
     * Account which is used to crawl the app
     * @var array
     */
    public $login = array(
        'username' => 'admin',
        'password' => 'admin',
    );

    public function testX2FlowPages () {
        $this->visitPages (array (
            'studio/flowIndex',
            'studio/flowDesigner',
            'studio/triggerLogs',
            'studio/importFlow',
            'studio/exportFlow?flowId=1',
            'studio/flowDesigner/1',
        ));
    }

    public function testAdminPages () {
        $this->visitPages (array (
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
            'admin/exportModels',
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
        ));
    } 

}

?>
