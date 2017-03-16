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

return ""; $x="

.seen-message-row td,
.seen-message-row .from-cell,
#my-email-inbox-set-up-instructions-container {
	background: $colors[content]
	border-color: $colors[border]
}

.unseen-message-row td,
.unseen-message-row .from-cell{
	border-color: $colors[border]
	background: $colors[light_content]
}

#yw0,
.pager,
.x2grid-body-container,
.row.ui-droppable ,
.row.buttons.last-button-row,
.row.buttons.last-button-row + .clearfix,
#email-inbox-tabs .email-inbox-tab {
	background: $colors[content]
	border-color: $colors[border]
}

.email-inputs .row {
	border-color: $colors[border]
}

#email-quota {
	color: $colors[text]
}

.credentials-list .credentials-view{
	color: $colors[text]
}

.credentials-list .default-state {
	background: $colors[content]
	color: $colors[text]
	border-color: $colors[border]
}

.credentials-list .default-state-set {
	background: $colors[highlight2]
	color: $colors[smart_text2]
	border-color: $colors[light_highlight2]
}

#email-list .x2grid-body-container.x2grid-no-pager .items tr {
	background: $colors[content]
}

#email-list .x2grid-body-container.x2grid-no-pager .items tr td{
	border-color: $colors[content]
}

.empty-text-progress-bar {
	background: $colors[highlight2];
}

.folder-link.current-folder {
	background: $colors[light_content]
}

.mailbox-controls {
	border-color: $colors[border]
}

.flagged-toggle::before {
	color: $colors[darker_content]
}


.flagged-toggle.flagged::before {
	color: $colors[darker_highlight2]
}

.flagged-toggle.flagged::after {
	color: $colors[highlight2]
}

.mailbox-controls {
	background: $colors[content]
}


"; ?>
