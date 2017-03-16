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
INSERT INTO `x2_forwarded_email_patterns` (`id`, `custom`, `groupName`, `pattern`, `bodyFrom`, `description`) 
VALUES 
(1,0,'GMail1','^\\-+{s}*Forwarded{s}*message{s}*\\-+{s}*From:{s}*(?<GMail1_name>{name}*)?{s}*<?(?<GMail1_address>{emailAddr})>?{s}*{junkFields}*',NULL,NULL),
(2,0,'Outlook1','^From:\\s*(?<Outlook1_name>{name}*)\\s*\\[mailto:(?<Outlook1_address>{emailAddr})\\]{s}*{junkFields}*',NULL,NULL),
(3,0,'Unknown1','^\\-+{s}*Original Message{s}*\\-+{s}+{junkFields}*\\nFrom:{s}*(?<Unknown1_name>{name}*){s}*\\<(?<Unknown1_address>{emailAddr})>{s}*{junkFields}*',NULL,NULL),
(4,0,'AppleMail1','^Begin{s}*forwarded{s}*message:{s}*>?\\s*From:\\s*(?<AppleMail1_name>{name}*){s}*<?(?<AppleMail1_address>{emailAddr})>?{s}*{junkFields}*',NULL,NULL),
(5,0,'Zimbra1','^\\-{5} Original Message \\-{5}{s}*From: \"(?<Zimbra1_name>{name}*)\" <(?<Zimbra1_address>{emailAddr})>{s}*{junkFields}*',NULL,NULL),
(6,0,'Zimbra2','^\\-{5} Forwarded Message \\-{5}{s}*From: \"(?<Zimbra2_name>{name}*)\" <(?<Zimbra2_address>{emailAddr})>{s}*{junkFields}*',NULL,NULL),
(7,0,'Fastmail','\\-{5} Original message \\-{5}\\n\\nFrom: (?<Fastmail_name>{name}*) <\\[\\d*\\](?<Fastmail_address>{emailAddr})>{s}*(?:To: \"\\[\\d*\\]{emailAddr}\"{s}*<\\[\\d*\\]{emailAddr}>)?{s}+{junkFields}*',NULL,NULL);
/*&*/
INSERT INTO `x2_reports_2` (`id`, `createdBy`, `lastUpdated`, `createDate`, `name`, `settings`, `version`, `type`) VALUES 
(1, 'admin', 1414093271, 1414093271,'Services Report',
	'{\"columns\":[\"name\",\"impact\",\"status\",\"assignedTo\",\"lastUpdated\",\"updatedBy\"],\"orderBy\":[],\"primaryModelType\":\"Services\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false}','4.3','rowsAndColumns'),
(2, 'admin',1414093762, 1414093762,'Deal Report',
	'{\"columns\":[\"name\",\"assignedTo\",\"company\",\"leadscore\",\"closedate\",\"dealvalue\",\"dealstatus\",\"rating\",\"lastUpdated\"],\"orderBy\":[],\"primaryModelType\":\"Contacts\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false}','5.0','rowsAndColumns');
/*&*/
INSERT INTO `x2_reports_2` (`id`, `createdBy`, `lastUpdated`, `createDate`, `name`, `settings`, `version`, `type`, `dataWidgetLayout`) VALUES 
(3, 'admin', 1416614514, 1416614514, 'Lead Volume',
	'{\"columns\":[\"createDate\",\"leadSource\"],\"orderBy\":[],\"primaryModelType\":\"Contacts\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false,\"includeTotalsRow\":\"0\"}','5.0','rowsAndColumns','{\"BarWidget\":{\"label\":\"Bar Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"bar\",\"legend\":null},\"DataWidget\":{\"label\":\"Data Widget\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":null,\"legend\":null},\"TimeSeriesWidget\":{\"label\":\"Activity Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"line\",\"legend\":null,\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"month\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fd27d8e2a2\":{\"hidden\":false,\"minimized\":false,\"label\":\"Lead Volume\",\"chartId\":\"1\",\"uid\":\"\",\"containerNumber\":1,\"softDeleted\":false,\"displayType\":\"line\",\"legend\":[\"Portland trade show\",\"null\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"month\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fe2089f793\":{\"hidden\":false,\"minimized\":false,\"label\":\"Lead Volume\",\"chartId\":\"1\",\"uid\":\"\",\"containerNumber\":2,\"softDeleted\":false,\"displayType\":\"pie\",\"legend\":[\"Portland trade show\",\"null\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"week\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null}}'), 
(4, 'admin', 1416613128, 1416613128, 'Web Activity',
	'{\"columns\":[\"createDate\",\"type\"],\"orderBy\":[],\"primaryModelType\":\"Actions\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false,\"includeTotalsRow\":\"0\"}','5.0','rowsAndColumns','{\"BarWidget\":{\"label\":\"Bar Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"bar\",\"legend\":null},\"DataWidget\":{\"label\":\"Data Widget\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":null,\"legend\":null},\"TimeSeriesWidget\":{\"label\":\"Activity Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"line\",\"legend\":null,\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"month\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fcd14a50ab\":{\"hidden\":false,\"minimized\":false,\"label\":\"Web Activity\",\"chartId\":\"2\",\"uid\":\"\",\"containerNumber\":1,\"softDeleted\":false,\"displayType\":\"area\",\"legend\":[\"event\",\"note\",\"null\",\"email\",\"quotes\",\"attachment\",\"time\",\"workflow\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"week\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fe2966e30e\":{\"hidden\":false,\"minimized\":false,\"label\":\"Web Activity\",\"chartId\":\"2\",\"uid\":\"\",\"containerNumber\":2,\"softDeleted\":false,\"displayType\":\"gauge\",\"legend\":[\"event\",\"note\",\"null\",\"email\",\"quotes\",\"attachment\",\"time\",\"workflow\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"week\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null}}');
/*&*/
INSERT INTO `x2_charts` (`id`, `createDate`, `createdBy`, `reportId`, `lastUpdated`, `name`, `settings`, `version`, `type`) VALUES (1,1416614525,'admin',3,1416614525,'Lead Volume','{\"timeField\":\"createDate\",\"labelField\":\"leadSource\",\"filterType\":\"trailing\",\"filter\":\"week\",\"filterFrom\":null,\"filterTo\":null}','5.0','TimeSeries'), (2,1416613140,'admin',4,1416613140,'Web Activity','{\"timeField\":\"createDate\",\"labelField\":\"type\",\"filterType\":\"trailing\",\"filter\":\"week\",\"filterFrom\":null,\"filterTo\":null}','5.0','TimeSeries');
