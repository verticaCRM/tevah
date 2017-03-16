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

DROP TABLE IF EXISTS `x2_email_inboxes`;
/*&*/
CREATE TABLE x2_email_inboxes (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(255) NOT NULL DEFAULT '',
    /* pseudo-foreign key which points to a credentials record */
    credentialId       INT UNSIGNED NULL,
    /* whether or not the inbox is shared or personal */
    shared             TINYINT DEFAULT 0,
    /* users who can view the inbox */
    assignedTo         VARCHAR(255),
    lastUpdated        BIGINT,
    settings           VARCHAR(1024)
) ENGINE=InnoDB COLLATE = utf8_general_ci;
/*&*/
INSERT INTO `x2_modules`
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable)
VALUES
('emailInboxes', 'Email', 1, 9, 1, 0, 0, 0, 0);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
("EmailInboxes", "assignedTo", "Visible To", 0, 0, "assignment", 1, 0, 'multiple', 0, 0, "", 0, 1, NULL),
("EmailInboxes", "shared", "Shared", 0, 0, "boolean", 0, 1, null, 0, 1, "", 0, 1, NULL),
('EmailInboxes', 'name', 'Name', 0, 0, 'varchar', 1, 0, NULL, 1, 0, 'High', 0, 1, NULL), 
('EmailInboxes', 'lastUpdated', 'LastUpdated', 0, 0, 'dateTime', 0, 1, NULL, 0, 0, '', 0, 1, NULL), 
('EmailInboxes', 'credentialId', 'Email Credentials', 0, 0, 'int', 1, 0, 'Credentials', 0, 0, '', 0, 1, 'FOR');
