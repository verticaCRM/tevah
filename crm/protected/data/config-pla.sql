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
ALTER TABLE x2_admin ADD enableFingerprinting TINYINT DEFAULT 1;
/*&*/
/**
 * We probably want this threshold value to be the number of attributes
 * by default so that a full match would be required.
 */
ALTER TABLE x2_admin ADD identityThreshold INT DEFAULT 13;
/*&*/
ALTER TABLE x2_admin ADD maxAnonContacts INT DEFAULT 5000;
/*&*/
ALTER TABLE x2_admin ADD maxAnonActions INT DEFAULT 10000;
/*&*/
ALTER TABLE x2_admin ADD performHostnameLookups TINYINT DEFAULT 1;
