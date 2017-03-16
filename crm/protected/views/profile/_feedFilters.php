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
Yii::app()->clientScript->registerCss('feedFiltersCSs',"
#feed-filters .x2-multiselect-dropdown-menu {
    margin-right: 3px;
    margin-bottom: 4px;
}


#feed-filters-button {
    color: inherit;
    text-decoration: none;
    opacity: 0.5;
    margin-right: 9px;
    margin-top: 3px;
}
#feed-filters-button > span {
    display: block;
}
#feed-filters-button:hover {
    opacity: 0.7;
}

a#toggle-filters-link {
    margin-bottom: 4px;
}


#full-controls-button-container {
    margin-top: 5px;
}

#full-controls-button-container > a {
    margin-right: 3px;
}

#full-controls,
#simple-controls {
    padding-left: 5px;
}

#execute-feed-filters-button {
    margin-left: 11px;
    height: 18px;
    line-height: 18px;
}

#filter-default {
    margin-top: 6px;
    margin-left: 8px;
}

#feed-filters .x2-button-group {
    margin-top: 8px;
}

#filter-controls > .portlet-content {
    padding: 5px 0px !important;
}

#filter-controls > .portlet-content > .x2-button-group {
    margin-bottom: 5px;
}

");
?>
<div id='feed-filters' style='display: none;'>
<?php

if(isset($_SESSION['filters'])){
    $filters=$_SESSION['filters'];
}else{
    $filters=array(
        'visibility'=>array(),
        'users'=>array(),
        'types'=>array(),
        'subtypes'=>array(),
    );
}

$visibility=array(
    '1'=>'Public',
    '0'=>'Private',
);
$socialSubtypes=json_decode(Dropdowns::model()->findByPk(113)->options,true);
$users=User::getNames();
$eventTypeList=Yii::app()->db->createCommand()
        ->select('type')
        ->from('x2_events')
        ->group('type')
        ->queryAll();
$eventTypes=array();
foreach($eventTypeList as $key=>$value){
    if($value['type']!='comment')
        $eventTypes[$value['type']]=Events::parseType($value['type']);
}
$profile=Yii::app()->params->profile;


echo '<div class="x2-button-group">';
echo '<a href="#" class="simple-filters x2-button'.
    ($profile->fullFeedControls?"":" disabled-link").'" style="width:42px">'.
    Yii::t('app','Simple').'</a>';
echo '<a href="#" class="full-filters x2-button x2-last-child'.
    ($profile->fullFeedControls?" disabled-link":"").'" style="width:42px">'.
    Yii::t('app','Full').'</a>';
echo "</div>\n";

echo "<div id='full-controls'".($profile->fullFeedControls?"":"style='display:none;'").">";

echo CHtml::dropDownList (
    'visibilityFilters', 
    // remove unselected filters and then map 1/0 to 'Public'/'Private'
    array_map (
        function ($a) use ($visibility) { return $visibility[$a]; }, 
        array_diff (array_keys ($visibility), $filters['visibility'])), 
    array_combine (array_values ($visibility), $visibility), 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'visibility setting(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);


echo CHtml::dropDownList (
    'relevantUsers', 
    array_diff (array_keys ($users), $filters['users']), 
    $users, 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'user(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);


echo CHtml::dropDownList (
    'eventTypes', 
    array_diff (array_keys ($eventTypes), $filters['types']), 
    $eventTypes, 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'event type(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);

$subFilters=$filters['subtypes'];
echo CHtml::dropDownList (
    'socialSubtypes', 
    array_diff (array_keys ($socialSubtypes), $subFilters), 
    $socialSubtypes, 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'social subtype(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);


echo "<br />";

echo "<div id='full-controls-button-container'>";
echo CHtml::link(
    Yii::t('app','Unselect All'),'#',
    array('class'=>'toggle-filters-link x2-button'));
echo CHtml::link(
    Yii::t('app','Apply Filters'),'#',
    array('class'=>'x2-button', 'id'=>'apply-feed-filters'));
echo CHtml::checkBox('setDefault', false,
    array(
        'title'=>'',
        'class'=>'default-filter-checkbox',
        'id'=>'filter-default'
    )
);
echo "<label for='filter-default'>".Yii::t('app','Set Default')."</label>";
/* x2prostart */
echo CHtml::link(
        Yii::t('app','Create Report'),'#',
        array('class'=>'x2-button x2-hint','style'=>'color:#000;margin-left:5px;','id'=>'create-activity-report',
            'title'=>Yii::t('app','Create an email report using the selected filters which will be mailed to you periodically.')));
/* x2proend */
echo "</div>";
echo "</div>";

echo "<div id='simple-controls'".
    ($profile->fullFeedControls?"style='display:none;'":"").">";

?>
<span><?php echo addslashes (Yii::t('app', 'Show me ')); ?></span>
<?php

echo CHtml::dropDownList (
    'simpleEventTypes', 
    '',
    array ('' => Yii::t('app', 'All')) + $eventTypes,
    array (
        'class' => 'x2-select'
    )
);

?>
<span><?php echo addslashes (Yii::t('app', ' events associated with ')); ?></span>
<?php

echo CHtml::dropDownList (
    'simpleUserFilter', 
    '',
    array (
        '' => Yii::t('app', 'anyone'),
        'myGroups' => Yii::t('app', 'my groups'),
        'justMe' => Yii::t('app', 'just me'),
    ),
    array (
        'class' => 'x2-select'
    )
);

?>
<a id='execute-feed-filters-button' href='#' class='x2-button highlight'><?php echo Yii::t('app', 'Go'); ?></a>
<?php

echo "</div>";
?>
</div>
<?php
