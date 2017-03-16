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
INSERT INTO x2_dropdowns (`id`, `name`, `options`) VALUES
(100,	'Product Status',	'{"Active":"Active","Inactive":"Inactive"}'),
(101,	'Currency List',	'{"USD":"USD","EUR":"EUR","GBP":"GBP","CAD":"CAD","JPY":"JPY","CNY":"CNY","CHF":"CHF","INR":"INR","BRL":"BRL"}'),
(102,	'Lead Type',		'{"None":"None","Web":"Web","In Person":"In Person","Phone":"Phone","E-Mail":"E-Mail"}'),
(103,	'Lead Source',		'{"None":"None","Google":"Google","Facebook":"Facebook","Walk In":"Walk In"}'),
(104,	'Lead Status',		'{"Unassigned":"Unassigned","Assigned":"Assigned","Accepted":"Accepted","Working":"Working","Dead":"Dead","Rejected":"Rejected"}'),
(105,	'Sales Stage',		'{"Working":"Working","Won":"Won","Lost":"Lost"}'),
(106,	'Quote Status',		'{"Draft":"Draft","Presented":"Presented","Issued":"Issued","Won":"Won"}'),
(107,	'Campaign Type',	'{"Email":"Email","Call List":"Call List","Physical Mail":"Physical Mail"}'),
(108,	'Case Impact', 		'{"1 - Severe":"1 - Severe","2 - Critical":"2 - Critical","3 - Moderate":"3 - Moderate","4 - Minor":"4 - Minor"}'),
(109,	'Case Status', 		'{"New":"New","WIP":"WIP","Waiting for response":"Waiting for response","Needs more info":"Needs more info","Escalated":"Escalated","Reopened":"Reopened","Work around provided, waiting for fix":"Work around provided, waiting for fix","Program Manager investigation":"Program Manager investigation","Closed - Resolved":"Closed - Resolved","Closed - No Response":"Closed - No Response"}'),
(110,	'Case Main Issue',	'{"Hardware":"Hardware","Software":"Software","Internet Connection":"Internet Connection","LMS":"LMS","General Request":"General Request"}'),
(111,	'Case Sub Issue', 	'{"Laptop":"Laptop","Desktop":"Desktop","WiFi":"WiFi","Loss Connection":"Loss Connection","Windows OS":"Windows OS","MS Office":"MS Office","Class Access":"Class Access","Lost Password":"Lost Password","Download\\/Upload":"Download\\/Upload","Other":"Other"}'),
(112,	'Case Origin', 		'{"Email":"Email","Web":"Web","Phone":"Phone"}'),
(113,	'Social Subtypes',	'{"Social Post":"Social Post","Link":"Link","Announcement":"Announcement","Product Info":"Product Info","Competitive Info":"Competitive Info","Confidential":"Confidential"}'),
(114,	'Invoice Status',	'{"Pending":"Pending","Issued":"Issued","Paid":"Paid","Open":"Open","Canceled":"Canceled","Other":"Other"}'),
(115,	'Bug Status',       '{"Unconfirmed":"Unconfirmed","Confirmed":"Confirmed","In Progress":"In Progress","Closed (Resolved Internally)":"Closed (Resolved Internally)","Closed (Unable to Reproduce)":"Closed (Unable to Reproduce)","Closed (Duplicate)":"Closed (Duplicate)","Merged Into Base Code":"Merged Into Base Code"}'),
(116,	'Bug Severity',     '{"5":"Blocker","4":"Critical","3":"Major","2":"Normal","1":"Minor","0":"Feature Request"}'),
(117,	'Quick Note',       '{"Contacted":"Contacted","Not Contacted":"Not Contacted"}'),
(120,   'Action Timers',    '{\"Research\":\"Research\",\"Meeting\":\"Meeting\",\"Email\":\"Email\"}'),
(121,   'Event Subtypes',    '{\"Meeting\":\"Meeting\",\"Appointment\":\"Appointment\",\"Call\":\"Call\"}'),
(122,   'Event Statuses',    '{\"Confirmed\":\"Confirmed\",\"Cancelled\":\"Cancelled\"}'),
(123,   'Event Colors',    '{"#008000":"Green","#3366CC":"Blue","#FF0000":"Red","#FFA500":"Orange","#000000":"Black"}'),
(124,   'Priority',    '{"1":"Low","2":"Medium","3":"High"}');
/*&*/
INSERT INTO x2_dropdowns (`id`, `name`, `options`, `parent`, `parentVal`) VALUES
(118,	'Contacted Quick Note','{"Not interested.":"Not interested.","Requested follow up call.":"Requested follow up call.","Contact made.":"Contact made."}', 117, 'Contacted'),
(119,	'Not Contacted Quick Note','{"No answer.":"No answer.","Wrong number.":"Wrong number.","Left voicemail.":"Left voicemail."}', 117, 'Not Contacted');
/*&*/
ALTER TABLE x2_profile CHANGE `language` language varchar(40) DEFAULT '{language}', CHANGE `timeZone` timeZone varchar(100) DEFAULT '{timezone}';
/*&*/
ALTER TABLE x2_admin CHANGE `emailFromAddr` emailFromAddr varchar(255) NOT NULL DEFAULT '{bulkEmail}';
/*&*/
INSERT INTO x2_users (id, firstName, lastName, username, password, emailAddress, status, lastLogin, userKey)
        VALUES (1,'web','admin','{adminUsername}','{adminPass}','{adminEmail}','1','0','{adminUserKey}');
/*&*/
INSERT INTO x2_users (firstName, lastName, username, password, emailAddress, status, lastLogin)
        VALUES ('API','User','api','{apiKey}','{adminEmail}' ,'0', '0');
/*&*/
INSERT INTO x2_profile (fullName, username, emailAddress, status)
		VALUES ('Web Admin', '{adminUsername}', '{adminEmail}','1');
/*&*/
INSERT INTO x2_profile (fullName, username, emailAddress, status)
		VALUES ('API User', 'api', '{adminEmail}','0');
/*&*/
INSERT INTO x2_profile (id, fullName, username, emailAddress, status)
		VALUES (-1, '', '__x2_guest_profile__', '', '0');
/*&*/
INSERT INTO x2_social (`type`, `data`) VALUES ('motd', 'Please enter a message of the day!');
/*&*/
INSERT INTO x2_admin (timeout,webLeadEmail,emailFromAddr,currency,installDate,updateDate,quoteStrictLock,unique_id,edition,serviceCaseFromEmailAddress,serviceCaseFromEmailName,serviceCaseEmailSubject,serviceCaseEmailMessage,eventDeletionTime,eventDeletionTypes,appName,appDescription) VALUES (
	'3600',
	'{adminEmail}',
	'{bulkEmail}',
	'{currency}',
	'{time}',
	0,
	0,
	'{unique_id}',
	'{edition}',
	'{adminEmail}',
	'Tech Support',
	'Tech Support',
	'Hello {first} {last},\n\nJust wanted to check in with you about the support case you created. It is number {case}. We will get back to you as soon as possible.',
    0,
    '["record_create","record_deleted","action_reminder","action_complete","calendar_event","case_escalated","email_opened","email_sent","notif","weblead_create","web_activity","workflow_complete","workflow_revert","workflow_start"]',
    '{app}',
    'Your App Description'
);
/*&*/
UPDATE x2_profile SET `widgets`='0:1:1:1:1:1:0:0:0:0:0:0:0:0',
	`widgetOrder`='OnlineUsers:TimeZone:SmallCalendar:GoogleMaps:ChatBox:TagCloud:TwitterFeed:MessageBox:QuickContact:NoteBox:ActionMenu:MediaBox:DocViewer:TopSites';
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable, pseudoModule)
VALUES
('x2Activity', 'Activity', 1, 0, 0, 0, 0, 0, 0, 1);
/*&*/
UPDATE `x2_modules` SET `visible`=0;
/*&*/
UPDATE `x2_modules` SET `visible`=1 WHERE `name` IN {visibleModules};
/*&*/
UPDATE `x2_modules` SET `itemName`= "Bug Report" WHERE `name` = "bugReports";
/*&*/
INSERT INTO `x2_tips` (`tip`, `edition`, `admin`, `module`) VALUES
("You can click on the icon with 4 outward arrows in the top right to hide the widget sidebar.",'opensource',0,'Layout'),
("An action's priority determines its color in the list. Gray actions have already been completed.",'opensource',0,'Actions'),
("Clicking on an item in the Action list will slide a window over with more information.",'opensource',0,'Actions'),
("The gear icon in the top right can be used to restore any widgets you have hidden.",'opensource',0,'Layout'),
("You can drag and drop widgets on the right sidebar to re-arrange their order.",'opensource',0,'Layout'),
("The options in the \"Quick Note\" menu on the publisher can be changed in the Dropdown Editor.",'opensource',1,'Admin'),
("You can see the history of related records by clicking \"Relationships\" on the History widget. Accounts do this by default.",'opensource',0,'Relationships');
