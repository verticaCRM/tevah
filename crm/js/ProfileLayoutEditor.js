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
;

/**
 * Class to manage the profile layout editor
 */
x2.ProfileLayoutEditor = (function() {

function ProfileLayoutEditor(argsDict) {
    var defaultArgs = {
        defaultWidth: 52,
        settingName: 'columnWidth',
        columnWidth: null,
        margin: null,
        minWidths: [24, 24],

        // selections that are resized with the first column
        column1: [
            '#profile-section-1', 
            '#activity-feed-container-outer', 
            '#profile-widgets-container-2'
        ],

        // selections that are resized with the second column
        column2: [
            '#profile-layout-editor #section-2', 
            '#profile-widgets-container'
        ],
        //Element that is resized / dragged
        draggable: '#profile-section-1',

        //overall container for the widget
        container: '#profile-layout-editor',
        
        // middle icon indicator
        indicator: '.indicator',

        // Button to open the editor
        editLayoutButton: '#edit-layout',

        // Button to close the editor
        closeButton: '.close-button',

        // Button to reset the columnWidth
        resetButton: '.reset-button',

        //URL for the misc settings action
        miscSettingsUrl: null 
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.LayoutEditor.call (this, argsDict);

}

ProfileLayoutEditor.prototype = auxlib.create (x2.LayoutEditor.prototype);


return ProfileLayoutEditor;

})();
