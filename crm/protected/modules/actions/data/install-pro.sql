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
DROP TABLE IF EXISTS `x2_action_timers`;
/*&*/
CREATE TABLE IF NOT EXISTS `x2_action_timers` (
    `id`                INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `userId`            INT NOT NULL,
    `associationId`     INT DEFAULT NULL,
    `associationType`   VARCHAR(250) DEFAULT NULL,
    `type`              VARCHAR(250) DEFAULT NULL,
    `timestamp`         BIGINT DEFAULT NULL,
    `endtime`           BIGINT DEFAULT NULL,
    `actionId`          INT DEFAULT NULL,
    `data`              TEXT
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
ALTER TABLE `x2_action_meta_data` ADD COLUMN emailImapUid INT UNSIGNED NULL;
/*&*/
ALTER TABLE `x2_action_meta_data` ADD COLUMN emailInboxId INT UNSIGNED NULL;
/*&*/
ALTER TABLE `x2_action_meta_data` ADD CONSTRAINT UNIQUE (emailImapUid, emailInboxId);
