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

INSERT INTO `x2_auth_item_child`
(`parent`,`child`)
VALUES
('GeneralAdminSettingsTask','AdminManageActionPublisherTabs'),
('GeneralAdminSettingsTask','AdminEmailDropboxSettings'),
('GeneralAdminSettingsTask','AdminViewLog'),
('GeneralAdminSettingsTask','AdminLockApp'),
('GeneralAdminSettingsTask','AdminX2CronSettings'),
('GeneralAdminSettingsTask','AdminSecuritySettings'),
('GeneralAdminSettingsTask','AdminPackager'),
('GeneralAdminSettingsTask','AdminDisableUser'),
('GeneralAdminSettingsTask','AdminBanIp'),
('GeneralAdminSettingsTask','AdminWhitelistIp'),
('GeneralAdminSettingsTask','AdminExportLoginHistory'),
('GeneralAdminSettingsTask','AdminImportPackage'),
('GeneralAdminSettingsTask','AdminPreviewPackageImport'),
('GeneralAdminSettingsTask','AdminExportPackage'),
('GeneralAdminSettingsTask','AdminBeginPackageRevert'),
('GeneralAdminSettingsTask','AdminFinishPackageRevert'),
('GeneralAdminSettingsTask','AdminRevertPackage'),
('ReportsReadOnlyAccess','ReportsActivityReport'),
('ReportsReadOnlyAccess', 'ReportsInlineEmail'),
('ReportsReadOnlyAccess','ReportsChartDashboard'),
('ReportsReadOnlyAccess','ReportsViewChart'),
('ReportsReadOnlyAccess','ReportsCloneChart'),
('ReportsReadOnlyAccess','ReportsAddToDashboard'),
('ReportsReadOnlyAccess','ReportsCreateChart'),
('ReportsReadOnlyAccess','ReportsFetchData'),
('ReportsAdminAccess','ReportsAdmin'),
('administrator','ReportsAdminAccess'),
('ReportsReadOnlyAccess','ReportsDealReport'),
('ReportsReadOnlyAccess','ReportsDelete'),
('ReportsReadOnlyAccess','ReportsDeleteNote'),
('ReportsAdminAccess','ReportsMinimumRequirements'),
('ReportsMinimumRequirements','ReportsGetOptions'),
('ReportsReadOnlyAccess','ReportsGridReport'),
('ReportsMinimumRequirements','ReportsIndex'),
('ReportsIndex', 'ReportsX2GridViewMassAction'),
('ReportsReadOnlyAccess','ReportsLeadPerformance'),
('ReportsReadOnlyAccess','ReportsMinimumRequirements'),
('ReportsReadOnlyAccess','ReportsPrintReport'),
('ReportsReadOnlyAccess','ReportsSavedReports'),
('ReportsReadOnlyAccess','ReportsSaveReport'),
('ReportsReadOnlyAccess','ReportsSaveTempImage'),
('ReportsReadOnlyAccess','ReportsView'),
('ReportsReadOnlyAccess','ReportsCopy'),
('ReportsReadOnlyAccess','ReportsUpdate'),
('ReportsReadOnlyAccess','ReportsRowsAndColumnsReport'),
('ReportsReadOnlyAccess','ReportsSummationReport'),
('ReportsMinimumRequirements','ReportsSearch'),
('ReportsReadOnlyAccess','ReportsWorkflow'),
('ReportsMinimumRequirements','ReportsGetItems'),
('RoleAccessTask','AdminEditRoleAccess'),
('MarketingFullAccess','WeblistCreate'),
('MarketingFullAccess','WeblistDelete'),
('MarketingMinimumRequirements','WeblistIndex'),
('MarketingFullAccess','WeblistUpdate'),
('MarketingFullAccess','WeblistRemoveFromList'),
('MarketingAdminAccess','MarketingWebTracker'),
('MarketingPrivateReadOnlyAccess','WeblistView'),
('MarketingReadOnlyAccess','WeblistView'),
('ServicesAdminAccess','ServicesServicesReport'),
('ServicesAdminAccess','ServicesExportServiceReport'),
('AccountsAdminAccess','AccountsAccountsReport'),
('AccountsAdminAccess','AccountsAccountsCampaign'),
('AccountsAdminAccess','AccountsExportAccountsReport'),
('X2StudioTask','StudioImportFlow'),
('X2StudioTask','StudioExportFlow'),
('X2StudioTask','StudioAjaxGetModelAutocomplete'),
('X2StudioTask','StudioFlowIndex'),
('X2StudioTask','StudioFlowDesigner'),
('X2StudioTask','StudioDeleteFlow'),
('X2StudioTask','StudioTest'),
('X2StudioTask','StudioGetParams'),
('X2StudioTask','StudioGetFields'),
('X2StudioTask','StudioDeleteNote'),
('X2StudioTask','StudioTriggerLogs'),
('X2StudioTask','StudioDeleteAllTriggerLogs'),
('X2StudioTask','StudioDeleteAllTriggerLogsForAllFlows'),
('X2StudioTask','StudioDeleteTriggerLog'),
('X2StudioTask','StudioSearch'),
('administrator', 'EmailInboxesAdminAccess'),
('administrator', 'EmailInboxesReadOnlyAccess'),
('EmailInboxesReadOnlyAccess', 'EmailInboxesMinimumRequirements'),
('EmailInboxesAdminAccess', 'EmailInboxesSharedInboxesIndex'),
('EmailInboxesAdminAccess', 'EmailInboxesCreateSharedInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesUpdateSharedInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesDeleteSharedInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesMinimumRequirements'),
('EmailInboxesAdminAccess', 'EmailInboxesAdmin'),
('EmailInboxesAdminAccess', 'EmailInboxesDeleteNote'),
('EmailInboxesMinimumRequirements', 'EmailInboxesInlineEmail'),
('EmailInboxesMinimumRequirements', 'EmailInboxesSearch'),
('EmailInboxesMinimumRequirements', 'EmailInboxesX2GridViewMassAction'),
('EmailInboxesMinimumRequirements', 'EmailInboxesIndex'),
('EmailInboxesMinimumRequirements', 'EmailInboxesSaveTabSettings'),
('EmailInboxesMinimumRequirements', 'EmailInboxesViewMessage'),
('EmailInboxesMinimumRequirements', 'EmailInboxesViewAttachment'),
('EmailInboxesMinimumRequirements', 'EmailInboxesDownloadAttachment'),
('EmailInboxesMinimumRequirements', 'EmailInboxesMarkMessages'),
('EmailInboxesMinimumRequirements', 'EmailInboxesConfigureMyInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesAjaxGetModelAutocomplete'),
('EmailInboxesAdminAccess', 'EmailInboxesGetX2ModelInput'),
('DefaultRole','EmailInboxesReadOnlyAccess');
