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



$user = X2Model::model('User')->findByPk(Yii::app()->user->getId());
$showCalendars = json_decode($user->showCalendars, true);

// list of user calendars current user can edit
$editableUserCalendars = X2CalendarPermissions::getEditableUserCalendarNames(); 

// User Calendars
if(isset($this->calendarUsers) && $this->calendarUsers !== null) {

    // actionTogglePortletVisible is defined in calendar controller
    $toggleUserCalendarsVisibleUrl = 
        $this->createUrl('togglePortletVisible', array('portlet'=>'userCalendars')); 
    $visible = Yii::app()->params->profile->userCalendarsVisible;

    $this->beginWidget('LeftWidget',
        array(
            'widgetLabel'=>Yii::t('calendar', 'User {calendars}', array(
                '{calendars}' => Modules::displayName()."s",
            )),
            'widgetName' => 'UserCalendars',
            'id'=>'user-calendars',
        )
    );

    $showUserCalendars = $showCalendars['userCalendars'];
    echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
    foreach($this->calendarUsers as $userName=>$user) {
        if($user=='Anyone'){
            $user=Yii::t('app',$user);
        }
        // check if current user has permission to edit calendar
        if(isset($editableUserCalendars[$userName])) {
            $editable = 'true';
        } else {
            $editable = 'false';
        }
        echo "<li>\n";
        // checkbox for each user calendar the current user is alowed to view
        echo CHtml::checkBox($userName, in_array($userName, $showUserCalendars),
            array(
                // add or remove user's actions to calendar if checked/unchecked
                'onChange'=>"toggleUserCalendarSource(
                    this.name, this.checked, $editable);", 
            )
        );
        echo "<label for=\"$userName\">$user</label>\n";
        echo "</li>";
    }
    echo "</ul>\n";
    $this->endWidget();
    if(!$visible) {
            Yii::app()->clientScript->registerScript('hideUserCalendars', "
                $(function() {
                    $('#user-calendars .portlet-content').hide();
            });",CClientScript::POS_HEAD);
    }
}

$modTitles = array();
foreach (array("Actions", "Contacts", "Accounts", "Products", "Quotes",
               "Media", "Opportunities") as $mod) {
    $modTitles[strtolower($mod)] = Modules::displayName(true, $mod);
}

// Calendar Filters
if(isset($this->calendarFilter) && $this->calendarFilter !== null) {
    $modTitles = array(
        'Accounts' => Modules::displayName (true, 'Accounts'),
        'Actions' => Modules::displayName (true, 'Actions'),
        'Contacts' => Modules::displayName (true, 'Contacts'),
        'Media' => Modules::displayName (true, 'Media'),
        'Opportunities' => Modules::displayName (true, 'Opportunities'),
        'Products' => Modules::displayName (true, 'Products'),
        'Quotes' => Modules::displayName (true, 'Quotes'),
    );
    $this->beginWidget('LeftWidget',
        array(
            'widgetLabel'=>Yii::t('calendar', 'Filter'),
            'widgetName' => 'CalendarFilter',
            'id'=>'calendar-filter',
        )
    );
    echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
    foreach($this->calendarFilter as $filterName=>$filter) {
        echo "<li>\n";
        if($filter)
            $checked = 'true';
        else
            $checked = 'false';
        $title = '';
        $class = '';
        $titles = array(
            'contacts'=>Yii::t('calendar', 'Show {actions} associated with {contacts}', array(
                '{actions}' => $modTitles["Actions"],
                '{contacts}' => $modTitles["Contacts"],
            )),
            'accounts'=>Yii::t('calendar', 'Show {actions} associated with {accounts}', array(
                '{actions}' => $modTitles["Actions"],
                '{accounts}' => $modTitles["Accounts"],
            )),
            'opportunities'=>Yii::t('calendar', 'Show {actions} associated with {opportunities}', array(
                '{actions}' => $modTitles["Actions"],
                '{opportunities}' => $modTitles["Opportunities"],
            )),
            'quotes'=>Yii::t('calendar', 'Show {actions} associated with {quotes}', array(
                '{actions}' => $modTitles["Actions"],
                '{quotes}' => $modTitles["Quotes"],
            )),
            'products'=>Yii::t('calendar', 'Show {actions} associated with {products}', array(
                '{actions}' => $modTitles["Actions"],
                '{products}' => $modTitles["Products"],
            )),
            'media'=>Yii::t('calendar', 'Show {actions} associated with {media}', array(
                '{actions}' => $modTitles["Actions"],
                '{media}' => $modTitles["Media"],
            )),
            'completed'=>Yii::t('calendar', 'Show Completed {actions}', array(
                '{actions}' => $modTitles["Actions"],
            )),
            'email'=>Yii::t('calendar', 'Show Emails'),
            'attachment'=>Yii::t('calendar', 'Show Attachments'),
        );
        if(isset($titles[$filterName])) {
            $title = $titles[$filterName];
            $class = 'x2-info';
        }
        echo CHtml::checkBox($filterName, $filter,
            array(
                // add/remove filter if checked/unchecked
                'onChange'=>"toggleCalendarFilter('$filterName', $checked);", 
                'title'=>$title,
                'class'=>$class,
            )
        );
        $filterDisplayName = ucwords($filterName); // capitalize filter name for label
        echo "<label for=\"$filterName\" class=\"$class\" title=\"$title\">".
            Yii::t('calendar',$filterDisplayName)."</label>";
        echo "</li>\n";
    }
    echo "</ul>\n";
    $this->endWidget();
}

// Group Calendars
if(isset($this->groupCalendars) && $this->groupCalendars !== null) {
   
    // actionTogglePortletVisible is defined in calendar controller
    $toggleGroupCalendarsVisibleUrl = 
        $this->createUrl(
            'togglePortletVisible', array('portlet'=>'groupCalendars')); 
    $visible = Yii::app()->params->profile->groupCalendarsVisible;
    $minimizeLink = CHtml::ajaxLink(
        $visible? '[&ndash;]' : '[+]', 
        $toggleGroupCalendarsVisibleUrl, 
        // javascript function togglePortletVisible defined in js/layout.js
        array(
            'success'=>'function(response) { 
                x2.LayoutManager.togglePortletVisible($("#group-calendar"), response); 
            }'
        )
    ); 
    $this->beginWidget('LeftWidget',
            array(
                'widgetLabel'=>Yii::t('calendar', 'Group {calendars}', array(
                    '{calendars}' => Modules::displayName()."s",
                )),
                'widgetName' => 'GroupCalendars',
                'id'=>'group-calendar',
            )
        );
        $showGroupCalendars = $showCalendars['groupCalendars'];
        echo '<ul style="font-size: 0.8em; font-weight: bold; color: black;">';
        foreach($this->groupCalendars as $groupId=>$groupName) {
            echo "<li>\n";
            // checkbox for each user; current user and Anyone are set to checked
            echo CHtml::checkBox($groupId, in_array($groupId, $showGroupCalendars),
                // add or remove group calendar actions to calendar if checked/unchecked
                array(
                    'onChange'=>"toggleGroupCalendarSource(this.name, this.checked);", 
                )
            );
            echo "<label for=\"$groupId\">".CHtml::encode($groupName)."</label>\n";
            echo "</li>";
        }
        echo "</ul>\n";
        $this->endWidget();
        if(!$visible) {
                Yii::app()->clientScript->registerScript('hideGroupCalendars', "
                    $(function() {
                        $('#group-calendar .portlet-content').hide();
                });",CClientScript::POS_HEAD);
        }
}
/* x2plastart */
if ($this->action->id === 'index') {
    $this->beginWidget('leftWidget',array(
        'widgetLabel'=>Yii::t('calendar','Export {calendar}', array('{calendar}'=>Modules::displayName())),
        'widgetName' => 'IcalExportUrl',
        'id'=>'ical-export-url',
    ));
    echo '<input type="text" class="x2-textfield" name="ical-export-url-field" id="ical-export-url-field" style="width:50%;display:inline-block;"></input>&nbsp;';
    echo '<a id="ical-export-url-link" href="#">['.Yii::t('calendar','link').']</a>&nbsp;';
    echo X2Html::hint(Yii::t('admin',"This link is to a special URL that displays the current {calendar} in ICS format. It is useful for setting up the {calendar} in third-party programs such as Apple iCal.", array(
            '{calendar}' => lcfirst(Modules::displayName()),
    )),false,null,true); // text, superscript, id,brackets, encode
    $this->endWidget();
}
/* x2plaend */
