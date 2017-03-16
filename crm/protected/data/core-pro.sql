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

/* @edition:pro */

DROP TABLE IF EXISTS x2_forwarded_email_patterns;
/*&*/
CREATE TABLE x2_forwarded_email_patterns(
	id					INT			NOT NULL AUTO_INCREMENT PRIMARY KEY,
	custom				TINYINT		DEFAULT 1,
	groupName			VARCHAR(20)	NOT NULL,
	pattern				TEXT,
	bodyFrom			TEXT,
	description			TEXT,
	UNIQUE (groupName)
) COLLATE = utf8_general_ci;
/*&*/
/* drop this table first since one if its attributes is a foreign key to x2_cron_events */
DROP TABLE IF EXISTS x2_email_reports;
/*&*/
DROP TABLE IF EXISTS `x2_cron_events`;
/*&*/
CREATE TABLE `x2_cron_events` (
	`id`				int(11)		NOT NULL AUTO_INCREMENT,
	`type`				VARCHAR(20)	NOT NULL,
	`recurring`			TINYINT		DEFAULT 0,
	`priority`			INT			DEFAULT 1,
	`time`				BIGINT		DEFAULT NULL,
	`interval`			VARCHAR(20)	DEFAULT NULL,
	`data`				TEXT		NOT NULL,
	`createDate`		BIGINT		NOT NULL,
	`lastExecution`		BIGINT		DEFAULT NULL,
	`executionCount`	BIGINT		NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS `x2_gallery_photo`;
/*&*/
DROP TABLE IF EXISTS `x2_gallery_to_model`;
/*&*/
DROP TABLE IF EXISTS `x2_gallery`;
/*&*/
CREATE  TABLE IF NOT EXISTS `x2_gallery` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `versions_data` TEXT NOT NULL ,
  `name` TINYINT(1) NOT NULL DEFAULT 1 ,
  `description` TINYINT(1) NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) )
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
CREATE  TABLE IF NOT EXISTS `x2_gallery_photo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `gallery_id` INT NOT NULL ,
  `rank` INT NOT NULL DEFAULT 0 ,
  `name` VARCHAR(512) NOT NULL DEFAULT '',
  `description` TEXT NULL,
  `file_name` VARCHAR(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) ,
  INDEX `fk_gallery_photo_gallery1` (`gallery_id` ASC) ,
  CONSTRAINT `fk_gallery_photo_gallery1`
    FOREIGN KEY (`gallery_id` )
    REFERENCES `x2_gallery` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
CREATE TABLE IF NOT EXISTS `x2_gallery_to_model` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `galleryId` INT NOT NULL ,
    `modelName` VARCHAR(255) NOT NULL ,
    `modelId` INT NOT NULL ,
     PRIMARY KEY (`id`) ,
  INDEX `fk_gallery_to_model` (`galleryId` ASC) ,
  CONSTRAINT `fk_gallery_to_model`
    FOREIGN KEY (`galleryId` )
    REFERENCES `x2_gallery` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
CREATE TABLE x2_email_reports (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    user VARCHAR(255),
    cronId INT(11) NOT NULL,
    schedule VARCHAR(255),
    CONSTRAINT `fk_report_to_cron`
        FOREIGN KEY (`cronId`)
        REFERENCES `x2_cron_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
DROP TABLE IF EXISTS `x2_login_history`;
/*&*/
CREATE TABLE x2_login_history (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	username				VARCHAR(50)		NOT NULL,
	IP VARCHAR(40),
	timestamp BIGINT DEFAULT NULL
) COLLATE = utf8_general_ci, ENGINE=INNODB;
/*&*/
ALTER TABLE x2_admin ADD imapPollTimeout INT DEFAULT 10;
/*&*/
ALTER TABLE x2_admin ADD COLUMN emailDropbox TEXT;
/*&*/
ALTER TABLE x2_profile ADD COLUMN emailInboxes VARCHAR(255) NOT NULL DEFAULT "";
/*&*/
ALTER TABLE x2_admin ADD COLUMN appliedPackages TEXT;
/*&*/
DROP TABLE IF EXISTS x2_merge_log;
/*&*/
CREATE TABLE x2_merge_log(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    modelType VARCHAR(255),
    modelId INT,
    mergeModelId INT,
    mergeData TEXT,
    mergeDate BIGINT
) COLLATE = utf8_general_ci, ENGINE=INNODB;

