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

/* @edition:pla */
ALTER TABLE x2_admin ADD COLUMN api2 TEXT;
/*&*/
ALTER TABLE x2_admin ADD accessControlMethod VARCHAR(15) DEFAULT 'blacklist';
/*&*/
ALTER TABLE x2_admin ADD ipWhitelist TEXT NULL;
/*&*/
ALTER TABLE x2_admin ADD ipBlacklist TEXT NULL;
/*&*/
ALTER TABLE x2_admin ADD loginTimeout INT DEFAULT 900;
/*&*/
ALTER TABLE x2_admin ADD failedLoginsBeforeCaptcha INT DEFAULT 5;
/*&*/
ALTER TABLE x2_admin ADD maxFailedLogins INT DEFAULT 100;
/*&*/
ALTER TABLE x2_admin ADD maxLoginHistory INT DEFAULT 5000;
