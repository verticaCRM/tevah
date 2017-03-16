/*********************************************************************************
 * Copyright (C) 2012 X2Engine Inc.
 * All rights reserved.
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
 * THIS SOFTWARE IS PROVIDED “AS IS” AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

DROP TABLE IF EXISTS x2_charts;
/*&*/
DROP TABLE IF EXISTS x2_reports_2;
/*&*/
CREATE TABLE x2_reports_2 (
    id         INT SIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    createDate BIGINT,
    createdBy  VARCHAR(250),
    lastUpdated BIGINT,
    name       VARCHAR(128),
    settings   TEXT,
    dataWidgetLayout   TEXT,
    version    VARCHAR(16),
    type       VARCHAR(250)
) Engine=InnoDB, AUTO_INCREMENT=1000, COLLATE = utf8_general_ci;
/*&*/
CREATE TABLE x2_charts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    createDate BIGINT,
    createdBy  VARCHAR(250),
    reportId   INT SIGNED,
    lastUpdated BIGINT,
    name       VARCHAR(128),
    settings   TEXT,
    version    VARCHAR(16),
    type       VARCHAR(250)
) Engine=InnoDB, COLLATE = utf8_general_ci;
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Charts', 'id', 'ID', 0, 0, 'varchar', 0, 1, NULL, 0, 0, '', 1, 1, 'PRI'),
('Charts', 'reportId', 'Report ID', 0, 0, 'INT', 0, 0, 'Reports', 0, 0, '', 0, 1, 'FOR'),
('Charts', 'name', 'Name', 0, 0, 'varchar', 1, 0, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'createDate', 'Create Date', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'createdBy', 'Created By', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'lastUpdated', 'Last Updated', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'settings', 'Settings', 0, 0, 'text', 0, 0, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'version', 'Version', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Charts', 'type', 'Chart Type', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL);
/*&*/
INSERT INTO x2_modules
(`name`, title, visible, menuPosition, searchable, editable, adminOnly, custom, toggleable, pseudoModule)
VALUES
("reports", "Reports", 1, 14, 0, 0, 0, 0, 0, 0),
("charts", "Charts", 1, 15, 0, 0, 0, 0, 0, 1);
/*&*/
INSERT INTO x2_fields
(modelName, fieldName, attributeLabel, modified, custom, `type`, required, readOnly, linkType, searchable, isVirtual, relevance, uniqueConstraint, safe, keyType)
VALUES
('Reports', 'id', 'ID', 0, 0, 'varchar', 0, 1, NULL, 0, 0, '', 1, 1, 'PRI'),
('Reports', 'name', 'Name', 0, 0, 'varchar', 1, 0, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'createDate', 'Create Date', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'createdBy', 'Created By', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'lastUpdated', 'Last Updated', 0, 0, 'dateTime', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'dataWidgetLayout', 'Chart Layout', 0, 0, 'text', 0, 0, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'version', 'Version', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL),
('Reports', 'type', 'Report Type', 0, 0, 'varchar', 1, 1, NULL, 0, 0, '', 0, 1, NULL);
