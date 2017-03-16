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

$this->noBackdrop = true;

$halfWidthThreshold = 1200; // content width past which publisher moves to the right of calendar
Yii::app()->clientScript->registerCss('calendarResponsiveCss',"

#calendar .fc-day-number > a {
    text-decoration: none;
}

#calendar,
#publisher-form {
    max-width: ".$halfWidthThreshold."px;
}

.responsive-page-title.fc-header {
    border-radius: 4px 4px 0 0 ;
    -moz-border-radius: 4px 4px 0 0;
    -webkit-border-radius: 4px 4px 0 0;
    -o-border-radius: 4px 4px 0 0;
}


#calendar.half-width {
    float: left;
    width: 70%;
}

#publisher-form.half-width {
    overflow: hidden;
    margin-top: -15px;
}
#publisher-form.half-width > #publisher {
    padding-left: 8px;
}

#publisher-tabs-row-1 {
    float: none !important;
}
");

// register fullcalendar css and js
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl() .'/js/fullcalendar-1.6.1/fullcalendar/fullcalendar.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/fullcalendar-1.6.1/fullcalendar/fullcalendar.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/fullcalendar-1.6.1/fullcalendar/gcal.js');
Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl . '/js/calendar.js',
    CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl ().'/js/X2Dialog.js');


Yii::app()->clientScript->registerCss('calendarIndexCss',"

#publisher-tabs-row-1 {
    border-right: 1px solid rgb(204, 204, 204); 
}


.ui-dialog {
    height: auto !important;
}

.ui-dialog .ui-dialog-content {
    height: auto !important;
}

.fc-first.fc-last {
    background: white;
}

#content {
    background: none !important;
    border: none !important;
}
#main-column {
    margin-top: 0 !important;
}

#publisher .ui-tabs-anchor {
    font-weight: bold;
    color: rgb(88, 88, 88);
}

.calendarViewEventDialog .ui-dialog-buttonpane button {
    padding: 0 2px 0 2px !important;
    margin: 4px 0 4px 4px !important;
    font-size: 9pt !important;
}

/* make publisher tab look like ordinary section title */

#publisher.ui-tabs .ui-tabs-nav {
    background: none;
    padding: 0px;
    display: block;
    margin-right: 1px !important;
}

#publisher li.ui-tabs-active {
    width: 100%;
    margin-right: 6px;
    display: block;
    border-bottom: none;
    margin: auto;
    margin-bottom: -2px;
}

#publisher > .form {
   border-radius: 0px 0px 4px 4px;
   -moz-border-radius: 0px 0px 4px 4px;
   -webkit-border-radius: 0px 0px 4px 4px;
   -o-border-radius: 0px 0px 4px 4px;
}

");

// register jquery timepicker css and js
// (used inside js dialog because CJuiDateTimePicker is a php library that won't work inside a js dialog)
//Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl() .'/protected/extensions/CJuiDateTimePicker/assets/jquery-ui-timepicker-addon.css');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/protected/extensions/CJuiDateTimePicker/assets/jquery-ui-timepicker-addon.js');

$menuOptions = array(
    'index', 'myPermissions',
);
if (Yii::app()->params->isAdmin)
    $menuOptions[] = 'userPermissions';
if (Yii::app()->settings->googleIntegration)
    $menuOptions[] = 'sync';
$this->insertMenu($menuOptions);

$this->calendarUsers = X2CalendarPermissions::getViewableUserCalendarNames();
$this->groupCalendars = X2Calendar::getViewableGroupCalendarNames();

//$this->sharedCalendars = X2Calendar::getViewableCalendarNames();
//$this->googleCalendars = X2Calendar::getViewableGoogleCalendarNames();
$this->calendarFilter = X2Calendar::getCalendarFilters();

// urls for ajax (and other javascript) calls
$urls = X2Calendar::getCalendarUrls();
$user = User::model()->findByPk(Yii::app()->user->getId());
$showCalendars = json_decode($user->showCalendars, true);

// fix showCalendars['groupCalendars']
if(!isset($showCalendars['groupCalendars'])){
    $showCalendars['groupCalendars'] = array();
    $user->showCalendars = json_encode($showCalendars);
    $user->update();
}

$userCalendars = $showCalendars['userCalendars'];
$groupCalendars = $showCalendars['groupCalendars'];
$sharedCalendars = $showCalendars['sharedCalendars'];
$googleCalendars = $showCalendars['googleCalendars'];

$checkedUserCalendars = '';
foreach($userCalendars as $user){
    if(isset($this->calendarUsers[$user])){
        $userCalendarFeed = $this->createUrl('jsonFeed', array('user' => $user));
        $checkedUserCalendars .= '
        $("#calendar").fullCalendar("addEventSource",{
            url: "'.$userCalendarFeed.'"
        });';
    }
}

$checkedGroupCalendars = '';
foreach($groupCalendars as $groupId){
    if(isset($this->groupCalendars[$groupId])){
        $checkedGroupCalendars .= '
        $("#calendar").fullCalendar("addEventSource",{
            url:"'.$urls['jsonFeedGroup'].'?groupId='.$groupId.'"
        });';
    }
}
?>


<script type="text/javascript">


/**************************************************************
*                       Declare Calendar
**************************************************************/

$(function() {

    $('#calendar').fullCalendar({
        theme: true,
        weekMode: 'liquid',
        header: {
            left: 'title',
            center: '',
            right: 'month agendaWeek agendaDay prev,next'
        },
        eventRender: function(event, element, view) {
            // prevent rendering of duplicate events on same view
            var potentialDuplicates = 
                $.makeArray ($('[data-action-uid="' + view.name + '-action-id-' + event.id + '"]'));

            // duplicate events are fetched when:
            //  1. An event is assigned to multiple users
            //  2. An event spans multiple weeks
            //  3. An event is viewed in multiple views (day, week, month)
            // Only the first case is erroneous. 
            // We avoid removing duplicates associated with case 2 by ensuring that duplicate 
            // events in the same view are part of the same calendar.
            // We avoid removing duplicates associated with case 3 by adding the event view to the 
            // event uid. 
            for (var i in potentialDuplicates) {
                if ($(potentialDuplicates[i]).attr ('data-action-calendarAssignment') !== 
                    event.calendarAssignment) {

                    element.remove (); 
                    return;
                }
            }

            $(element).attr ('data-action-uid', view.name + '-action-id-' + event.id);
            $(element).attr ('data-action-calendarAssignment', event.calendarAssignment);

            $(element).css('font-size', '0.8em');
            /*if(view.name == 'month' || view.name == 'basicWeek')
                $(element).find('.fc-event-time').remove();*/
            if(event.associationType == 'contacts')
                element.attr('title', event.associationName);
        },
        // Day Clicked!! Scroll to Publisher and set date to the day that was clicked
        dayClick: function(date, allDay, jsEvent, view) { 
            if ($(jsEvent.target).hasClass ('day-number-link')) {
                return x2.calendarManager.dayNumberClick ($(jsEvent.target));
            }

            // value of window's scrollbar to make publisher visible
            var scrollPublisher = x2.publisher.getForm ().offset().top + 
                x2.publisher.getForm ().height() + 5 - $(window).height(); 
            if($(window).scrollTop() < scrollPublisher) {
                $('html,body').animate({ scrollTop: scrollPublisher });
            }


            x2.calendarManager.insertDate(date, view);
        },
        // drop onto a different day
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) { 
            if(event.source.source == 'google') { // moving event from Google Calendar
                $.post(
                    '<?php echo $urls['moveGoogleEvent']; ?>?calendarId=' + event.source.calendarId, 
                    {
                        EventId: event.id, 
                        dayChange: dayDelta, 
                        minuteChange: minuteDelta, 
                        isAllDay: allDay
                    }
                );
            } else {
                $.post('<?php echo $urls['moveAction']; ?>', {
                    id: event.id, dayChange: dayDelta, minuteChange: minuteDelta, isAllDay: allDay
                });
            }
        },
        eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
            if(event.source.source == 'google') { // moving event from Google Calendar
                $.post(
                    '<?php echo $urls['resizeGoogleEvent']; ?>?calendarId=' + 
                        event.source.calendarId, 
                    {
                        EventId: event.id, 
                        dayChange: dayDelta, 
                        minuteChange: minuteDelta
                    }
                );
            } else {
                $.post('<?php echo $urls['resizeAction']; ?>', {
                    id: event.id, dayChange: dayDelta, minuteChange: minuteDelta});
            }
        },
        eventClick: function(event) { // Event Click! Pop up a dialog with info about the event

            // prevent duplicate dialog windows
            if ($('[id="dialog-content_' + event.id + '"]').length != 0) { 
                return;
            }

            if(event.source.type == 'googleFeed')
                return;

            // dialog box (opened at the end of this function)
            var viewAction = $('<div></div>', {id: 'dialog-content' + '_' + event.id});  
            var focusButton = 'Close';
            var dialogWidth = 390;
            var translatedModelTitles = <?php echo CJSON::encode (
                X2Model::getTranslatedModelTitles (true));  ?>; 
            var associations = {};
            for (var associationType in x2.associationModels) {
                associations[associationType] = 
                    translatedModelTitles[x2.associationModels[associationType]];
            }

            var boxButtons =  [ // buttons on bottom of dialog
                {
                    text: '<?php echo CHtml::encode (Yii::t('app', 'Close')); ?>',
                    click: function() {
                        $(this).x2Dialog('close');
                    }
                },
            ];

            if(event.source.source == 'google') {
                var boxTitle = '<?php echo CHtml::encode (Yii::t('calendar', 'Google Event')); ?>';
                if(event.editable){
                    dialogWidth = 600;
                    $.post(
                        '<?php echo $urls['editGoogleEvent']; ?>', {
                            EventId: event.id, 
                            CalendarId: event.source.calendarId
                        }, function(data) {
                            $(viewAction).append(data);
                            $(viewAction).x2Dialog('open');
                        }
                    );
                    boxButtons.unshift({
                        // update event
                        text: '<?php echo CHtml::encode (Yii::t('app', 'Save')); ?>', 
                        click: function() {
                            // delete event from database
                            $.post(
                                '<?php echo $urls['saveGoogleEvent']; ?>?calendarId=' + 
                                    event.source.calendarId, 
                                $(viewAction).find('form').serializeArray(),
                                function() {
                                    $('#calendar').fullCalendar('refetchEvents');
                                }
                            ); 
                            $(this).x2Dialog('close');
                        }
                    });
                    boxButtons.unshift({
                        // delete event
                        text: '<?php echo CHtml::encode (Yii::t('app', 'Delete')); ?>', 
                        click: function() {
                            if(confirm(
                                '<?php echo Yii::t(
                                    "calendar",
                                    "Are you sure you want to delete this action?") ?>')) {
                                    o
                                // delete event from Google Calendar
                                $.post('<?php echo $urls['deleteGoogleEvent']; ?>?calendarId=' + 
                                    event.source.calendarId, {EventId: event.id}); 
                                $('#calendar').fullCalendar('removeEvents', event.id);
                                $(this).x2Dialog('close');
                            }
                        }
                    });
                } else {
                    $.post('<?php echo $urls['viewGoogleEvent']; ?>', {
                        EventId: event.id, CalendarId: event.source.calendarId}, function(data) {

                        $(viewAction).append(data);
                        $(viewAction).x2Dialog('open');
                    });
                }
            } else {

                if(event.editable){

                    dialogWidth = 600;
                    $.post(
                        '<?php echo $urls['editAction']; ?>', {
                            'ActionId': event.id, 'IsEvent': event.type=='event'
                        }, function(data) {
                            $(viewAction).append(data);
                            //open dialog after its filled with action/event
                            viewAction.x2Dialog('open'); 
                        }
                    );
                    boxButtons.unshift({
                        text: '<?php echo CHtml::encode (Yii::t('app', 'Save')); ?>', // delete event
                        'class': 'save-event-button',
                        click: function() {
    //                        var description = $(eventDescription).val();
                            // delete event from database
                            $.post(
                                '<?php echo $urls['saveAction']; ?>?id=' + event.id, 
                                $(viewAction).find('form').serialize(),
                                function() {
                                    $('#calendar').fullCalendar('refetchEvents');
                                }
                            ); 
    //                        event.title = description.substring(0, 30);
    //                        event.description = description;
    //                        $('#calendar').fullCalendar('updateEvent', event);
                            $(this).x2Dialog('close');
                        }
                    });
                    boxButtons.unshift({
                        // delete event
                        'class': 'event-delete-button',
                        html: '<span title="<?php 
                            echo CHtml::encode (Yii::t('app', 'Delete')); 
                        ?>" class="fa fa-trash fa-lg"></span>', 
                        click: function() {
                            var deleteMsg = '<?php
                                echo Yii::t('calendar','Are you sure you want to delete this {action}?',array(
                                    '{action}' => lcfirst(Modules::displayName(false, "Actions"))
                                ));
                            ?>';
                            if(confirm(deleteMsg)) {
                                // delete event from database
                                $.post('<?php echo $urls['deleteAction']; ?>', {id: event.id}); 
                                $('#calendar').fullCalendar('removeEvents', event.id);
                                $(this).x2Dialog('close');
                            }
                        }
                    });
                    boxButtons.unshift({
                        html: '<span title="<?php 
                            echo CHtml::encode (Yii::t('app', 'Copy')); 
                        ?>" class="fa fa-copy fa-lg"></span>', 
                        'class': 'event-copy-button',
                        click: function() {
                            var dialogOuter$ = $(this).closest ('.ui-dialog');
                            dialogOuter$.find ('.event-copy-button').hide ();
                            dialogOuter$.find ('.ui-dialog-title').append ($('<span>', {
                                html: '&nbsp;<?php echo CHtml::encode (Yii::t('app', '(Copy)')); ?>'
                            }));
                            var that = this;  
                            dialogOuter$.find ('.event-delete-button').unbind ('click').
                                bind ('click', function () {
                                    $(that).x2Dialog ('close');
                                });
                            dialogOuter$.find ('.save-event-button').unbind ('click').bind ('click',
                                function () {
                                    $.ajax({
                                        type: 'post',
                                        url: yii.scriptUrl + '/actions/copyEvent?id=' + event.id,
                                        data: $(viewAction).find('form').serializeArray(),
                                        success: function() {
                                            $('#calendar').fullCalendar('refetchEvents');
                                        }
                                    }); 
                                    $(that).x2Dialog('close');
                                });
                        }
                    });
                    /*if (event.type === 'event') {
                        boxButtons.unshift({
                            html: '<span title="<?php 
                                echo CHtml::encode (Yii::t('app', 'Send invitation')); 
                            ?>" class="fa fa-envelope-o fa-lg"></span>', 
                            'class': 'event-email-button',
                            click: function() {
                            }
                        });
                    }*/
                } else { // non-editable event/action
                    $.post(
                        '<?php echo $urls['viewAction']; ?>', {
                            'ActionId': event.id, 
                            'IsEvent': event.type=='event'
                        }, function(data) {
                            $(viewAction).append(data);
                            //open dialog after its filled with action/event
                            viewAction.x2Dialog('open'); 
                        }
                    );
                }

                if(event.associationType == 'calendar') { // calendar event clicked
                    var boxTitle = 'Event';
                } else if(event.associationType != '' && event.associationType != 'contacts' && 
                          event.associationType != undefined) {

                    if(typeof associations[event.associationType]!='undefined'){
                        var associationType=associations[event.associationType];
                    }else{
                        var associationType=event.associationType;
                    }

                    if(event.linked) {
                        viewAction.prepend(
                            '<b><a href="' + event.associationUrl + '">' + event.associationName + 
                            '</a></b><br />');
                    }

                    boxButtons.unshift({  //prepend button
                        text: '<?php echo CHtml::encode (Yii::t('calendar', 'View')); ?> '+associationType,
                        click: function() {
                            window.location = event.associationUrl;
                        }
                    });

                    if(event.editable && event.type != 'event') {
                        if(event.complete == 'Yes') {
                            boxButtons.unshift({  // prepend button
                                text: '<?php echo CHtml::encode (Yii::t('actions', 'Uncomplete')); ?>',
                                click: function() {
                                    $.post('<?php echo $urls['uncompleteAction']; ?>', {id: event.id});
                                    event.complete = 'No';
                                    $(this).x2Dialog('close');
                                }
                            });
                        } else {
                            boxButtons.unshift({  // prepend button
                                html: '<span title="<?php 
                                    echo CHtml::encode (Yii::t('actions', 'Complete')); 
                                ?>" class="fa fa-check fa-lg"></span>', 
                                click: function() {
                                    $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                    event.complete = 'Yes';
                                    $(this).x2Dialog('close');
                                }
                            });
                        }
                    }
                } else if(event.associationType == 'contacts') { 
                    // action associated with a contact clicked
                    if(event.type == 'event')
                        boxTitle = '<?php echo Yii::t('calendar','Contact Event') ?>';
                    else
                        boxTitle = '<?php echo Yii::t('calendar','Contact Action') ?>';

                    if(event.linked) {
                        viewAction.prepend(
                            '<b><a href="' + event.associationUrl + '">' + event.associationName + 
                            '</a></b><br />');
                    }

                    boxButtons.unshift ({
                        lineBreak: true
                    });
                    boxButtons.unshift({  //prepend button
                        text: '<?php echo CHtml::encode (Yii::t('contacts', 'View {contact}', array(
                            '{contact}' => Modules::displayName(false, "Contacts"),
                        ))); ?>',
                        'class': 'view-contact-button',
                        click: function() {
                            window.location = event.associationUrl;
                        }
                    });
                    if(event.editable && event.type != 'event') {
                        if(event.complete == 'Yes') {
                            boxButtons.unshift({  // prepend button
                                text: '<?php echo CHtml::encode (Yii::t('actions', 'Uncomplete')); ?>',
                                click: function() {
                                    $.post('<?php echo $urls['uncompleteAction']; ?>', {
                                        id: event.id});
                                    event.complete = 'No';
                                    $(this).x2Dialog('close');
                                }
                            });
                        } else {
                            boxButtons.unshift({  // prepend button
                                text: '<?php 
                                    echo CHtml::encode (Yii::t('actions', 'Complete')); ?>',
                                click: function() {
                                    $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                    event.complete = 'Yes';
                                    $(this).x2Dialog('close');
                                }
                            });
                            boxButtons.unshift({  // prepend button
                                text: '<?php echo CHtml::encode (
                                    Yii::t('actions', 'Complete and View {contact}', array(
                                        '{contact}' => Modules::displayName(false, "Contacts"),
                                    ))); ?>',
                                click: function() {
                                    $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                    window.location = event.associationUrl;
                                }
                            });
                        }
                    }
                } else { // action clicked
                    var boxTitle = 'Action';
                    if(event.editable) {
                        if(event.complete == 'Yes') {
                            boxButtons.unshift({  // prepend button
                                text: '<?php echo CHtml::encode (Yii::t('actions', 'Uncomplete')); ?>',
                                click: function() {
                                    $.post('<?php echo $urls['uncompleteAction']; ?>', {id: event.id});
                                    event.complete = 'No';
                                    $(this).x2Dialog('close');
                                }
                            });
                        } else {
                            boxButtons.unshift({  // prepend button
                                text: '<?php echo CHtml::encode (Yii::t('actions', 'Complete')); ?>',
                                click: function() {
                                    $.post('<?php echo $urls['completeAction']; ?>', {id: event.id});
                                    event.complete = 'Yes';
                                    $(this).x2Dialog('close');
                                }
                            });
                        }
                    }
                }
            }

            var buttonpaneHeight;
            //var textareaHeight;

            viewAction.x2Dialog({
                title: boxTitle,
                dialogClass: 'calendarViewEventDialog',
                autoOpen: false,
                resizable: true,
                height: 'auto',
                width: dialogWidth,
                show: 'fade',
                hide: 'fade',
                buttons: boxButtons,
                open: function() {
                    $('.ui-dialog-buttonpane').find('button:contains(\"' + focusButton + '\")')
                        .addClass('highlight')
                        .focus();
                    $('.ui-dialog-buttonpane').find('button').css('font-size', '0.85em');
                    $('.ui-dialog-title').css('font-size', '0.8em');
                    $('.ui-dialog-titlebar').css('padding', '0.2em 0.4em');
                    $('.ui-dialog-titlebar-close').css({
                        'height': '18px',
                        'width': '18px'
                        });
                    $(viewAction).css('font-size', '0.75em');
                },
                close: function () {
                      $('[id="dialog-content_' + event.id + '"]').remove ();
                    cleanUpDialog ();
                },
                resizeStart: function () {
                    // resize buttonpane init
                      /*var elem = $(this).parents ('.ui-dialog');
                    buttonpaneHeight = $(elem).find ('.ui-dialog-buttonpane').height ();*/

                    // resize textarea init
                    //textareaHeight = $(this).find ('textarea').height ();
                },
                resize: function (event, ui) {
                    // resize buttonpane to make room for stacked buttons
                 /*     var elem = $(this).parents ('.ui-dialog');
                    var newButtonpaneHeight = $(elem).find ('.ui-dialog-buttonpane').height ();
                    if (newButtonpaneHeight !== buttonpaneHeight) {
                         $(elem).height ($(elem).height () + (newButtonpaneHeight - buttonpaneHeight));
                    }*/

                    // resize textarea
                    /*if (ui.size !== ui.originalSize) {
                        var textarea = $(this).find ('textarea');
                        if (textarea.length !== 0) {
                            $(textarea).height (textareaHeight + (ui.size.height - ui.originalSize.height));
                        }
                    }*/
                }
            });
        },
        editable: true,
        // translate (if local not set to english)
        buttonText:     <?php echo X2Calendar::translationArray('buttonText') ?>,
        monthNames:     <?php echo X2Calendar::translationArray('monthNames') ?>,
        monthNamesShort:<?php echo X2Calendar::translationArray('monthNamesShort') ?>,
        dayNames:       <?php echo X2Calendar::translationArray('dayNames') ?>,
        dayNamesShort:  <?php echo X2Calendar::translationArray('dayNamesShort') ?>,

    });
    
    /*
    *   This section is meant to pre-render the events given to it on loading, 
    *   but causes an amount of problems due to event sources.

    $('#calendar').fullCalendar('addEventSource', <?php //echo CJSON::encode($events) ?>);

    var pushed = false;
    // Once the view is switched, erase the prerendered events and add the others
    $('.fc-button-next, .fc-button-prev').bind('click', function(event){
        //$('.fc-button-next, .fc-button-prev').unbind(event); should work but doesnt...
        if(pushed){
            return;
        }
        pushed = true;
        $('#calendar').fullCalendar('removeEventSources');

        <?php echo $checkedUserCalendars; ?>
        <?php echo $checkedGroupCalendars; ?>

    });*/

<?php echo $checkedGroupCalendars; ?>
<?php echo $checkedUserCalendars; ?>
<?php //echo $checkedSharedCalendars;  ?>
<?php //echo $checkedGoogleCalendars;  ?>

    });

    /* x2plastart */ /* Begin: calendar export code (generates URL for third-party ical format reader) */
    x2CalendarExporter = {'users':<?php echo json_encode(array_merge($userCalendars,$groupCalendars)); ?>};
    // Adds or removes a user from the calendar export URL
    x2CalendarExporter.toggleUser = function(user,add) {
        // New list of user/group calendars
        var newList = [];
        var found = false;
        if(add) {
            for (var i=0;i<this.users.length;i++) {
                newList.push(this.users[i]);
                if (this.users[i] == user)
                    found = true;
            }
            if(!found)
                newList.push(user);
        } else {
            for (var i=0;i<this.users.length;i++) {
                if (this.users[i] != user)
                    newList.push(this.users[i]);
            }
        }
        this.users = newList;
        this.updateUrl();
    };
    
    x2CalendarExporter.updateUrl = function() {
        <?php
        $userModel = Yii::app()->suModel;
        if($userModel->calendarKey == '') {
            // Set a calendar key if one hasn't already been set
            $userModel->calendarKey = EncryptUtil::secureUniqueIdHash64();
            $userModel->update(array('calendarKey'));
        }
        ?>
        var url = <?php echo json_encode($this->createAbsoluteUrl('/calendar/calendar/ical',array(
            'user'=>Yii::app()->user->name,
            'key' => $userModel->calendarKey
        ))); ?>+'?calendars='+this.users.join(',');
        var container = $('#ical-export-url');
        container.find('a#ical-export-url-link').attr('href',url);
        container.find('input#ical-export-url-field').val(url);
    };

    x2CalendarExporter.updateUrl();
    /* End calendar export code */ /* x2plaend */

    // view/hide actions associated with a user
    function toggleUserCalendarSource(user, on) {
        if(user == '')
            user = 'Anyone';
        if(on) {
            $('#calendar').fullCalendar('addEventSource', {
                url: '<?php echo $urls['jsonFeed']; ?>?user=' + user
            });
        } else {
            $('#calendar').fullCalendar('removeEventSource', {
                url: '<?php echo $urls['jsonFeed']; ?>?user=' + user
            });
            //This is to remove the prepopulated events
            $('#calendar').fullCalendar('removeEvents', function(event){
                if( event.calendarAssignment == user ){
                    return true;
                }
            });
        }
        // Update the calendar share/export URL:
        /* x2plastart */ x2CalendarExporter.toggleUser(user,on); /* x2plaend */

        $.post('<?php echo $urls['saveCheckedCalendar']; ?>', {
            Calendar: user, Checked: on, Type: 'user'
        });
    }

    function toggleGroupCalendarSource(groupId, on) {
        if (on) {
            $('#calendar').fullCalendar('addEventSource', {
                url: '<?php echo $urls['jsonFeedGroup']; ?>?groupId=' + groupId
            });
        } else {
            $('#calendar').fullCalendar('removeEventSource', {
                url: '<?php echo $urls['jsonFeedGroup']; ?>?groupId=' + groupId
            });
            // This is to remove the prepopulated events
            $('#calendar').fullCalendar('removeEvents', function(event){
                if( event.calendarAssignment == groupId ){
                    return true;
                }
            });
        }
        /* x2plastart */ x2CalendarExporter.toggleUser(groupId,on); /* x2plaend */
        $.post('<?php echo $urls['saveCheckedCalendar']; ?>', {
            Calendar: groupId, 
            Checked: on, 
            Type: 'group'
        });
    }

    // filter calendar actions
    function toggleCalendarFilter(filterName, on) {
        $.post('<?php echo $urls['saveCheckedCalendarFilter']; ?>', {
                Filter: filterName, 
                Checked: on
            }).done(function() { 
                $('#calendar').fullCalendar('refetchEvents'); 
            });
    }

    // remove id's so we can create another dialog
    function cleanUpDialog() {
        $('#dialog-Actions_dueDate').remove();
        $('#dialog-Actions_startDate').remove();
        $('#dialog_actionsAssignedToDropdown').remove();
        $('#dialog_groupCheckbox').remove();
        $('body').off('click','#dialog_groupCheckbox');
        $('#dialog_Actions_visibility').remove();
    }

    // Put the function in this scope
    function giveSaveButtonFocus() {
        return x2.calendarManager.giveSaveButtonFocus();
    }

    $(function () {
    x2.layoutManager.setUpCalendarTitleBarResponsiveness ();
    x2.layoutManager.setHalfWidthSelector ('#calendar, #publisher-form');
    x2.layoutManager.setHalfWidthThreshold (<?php echo $halfWidthThreshold; ?>);
    $('#calendar .day-number-link').attr ('title', '<?php echo Yii::t('app', 'Show Day View'); ?>');
    $(window).resize ();
});

</script>

<div id="calendar">

</div>

<br />

<?php

$this->widget('Publisher', array(
    'associationType' => 'calendar',
    'tabs' => array (
        new PublisherEventTab ()
    ),
    'selectedTab' => 'new-event'
));
?>
