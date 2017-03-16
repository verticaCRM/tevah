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

return "

#email-mini-module .bottom-row-outer {
    background: $colors[content]
}

#inline-email-form .upload-file-container {
    background: $colors[light_content]
    border-color: $colors[border]
    color: $colors[text]
}

.seen-message-row, .seen-message-row td, .seen-message-row .from-cell {
  background: $colors[content]
  border-color: $colors[border] 
}

#my-email-inbox-set-up-instructions-container{
  background: $colors[content]
  border-color: $colors[border] 
}

.unseen-message-row td, .unseen-message-row .from-cell {
  border-color: $colors[border]
  background: $colors[light_content] 
}

#yw0, .pager, .x2grid-body-container {
  background: $colors[content]
  border-color: $colors[border] 
}

.row.ui-droppable, .row.buttons.last-button-row,
.row.buttons.last-button-row + .clearfix {
  background: $colors[content]
  border-color: $colors[border] 
}

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

.credentials-list .credentials-view {
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
  #email-list .x2grid-body-container.x2grid-no-pager .items tr td {
    border-color: $colors[content] 
}

.empty-text-progress-bar {
  background: $colors[highlight2] 
}

.folder-link.current-folder {
  background: $colors[light_content] 
}

.mailbox-controls {
  border-color: $colors[border] 
}

#email-mini-module .email-to-row.show-input-name::before, #cc-row.show-input-name::before, #bcc-row.show-input-name::before {
    color: $colors[text]
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

#message-container {
  background: #EEE; 
}

.reply-more-menu,
.more-drop-down-list {
  background: $colors[content] 
}
  .reply-more-menu li,
  .more-drop-down-list li {
    color: $colors[text] 
}
    .reply-more-menu li:hover,
    .more-drop-down-list li:hover {
      background: $colors[light_content] 
}

#my-email-inbox-reconfigure-instructions-container {
  background: $colors[content]
  border-color: $colors[border]
}

.mailbox-controls {
  background: $colors[content]
}

.email-sync-check-box-container .right-label{
  color: $colors[text]
}

"; ?>
