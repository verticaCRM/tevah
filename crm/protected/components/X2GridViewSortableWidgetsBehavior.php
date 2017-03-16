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

/**
 * Used to display gridviews within sortable widgets. This allows sortable widget gridviews to have
 * their own results per page settings.
 * @package application.components
 */
class X2GridViewSortableWidgetsBehavior extends CBehavior {

    /**
     * @var object an instance of SortableWidget 
     */
    public $sortableWidget;

    /**
     * Called by X2GridView's init () method to set the summaryText property. This method allows
     * the results per page drop down to display/set the results per page for an individual 
     * sortable widget
     */
    public function setSummaryTextForSortableWidgets () {
        $widgetClass = get_class ($this->owner->sortableWidget);
        $resultsPerPage = $widgetClass::getJSONProperty (
            $this->owner->sortableWidget->profile, 'resultsPerPage', 
            $this->owner->sortableWidget->widgetType);

        // add a dropdown to the summary text that let's user set how many rows to show on each page
        $this->owner->summaryText = Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>').
            '<div class="form no-border" style="display:inline;">'.
            CHtml::dropDownList(
                $widgetClass.'resultsPerPage', 
                $resultsPerPage,
                Profile::getPossibleResultsPerPage(), 
                array(
                    'class' => 'x2-minimal-select',
                    'onchange' => '$.ajax ({'.
                        'data: {'.
                            'key: "resultsPerPage",'.
                            'value: $(this).val(),'.
                            'widgetClass: "'.get_class ($this->owner->sortableWidget).'",'.
                            'widgetType: "'.$this->owner->sortableWidget->widgetType.'"'.
                        '},'.
                        'type: "POST",'.
                        'url: "'.Yii::app()->controller
                            ->createUrl('/profile/setWidgetSetting').'",'.
                        'complete: function (response) {'.
                            'x2.DEBUG && console.log ("setResultsPerPage after ajax");'.
                            '$.fn.yiiGridView.update("'.$this->owner->id.'", {'.
                                (isset($this->owner->modelName) ?
                                    'data: {'.$this->owner->modelName.'_page: 1},' : '') .
                                    'complete: function () {'.
                                    '}'.
                            '});'.
                        '}'.
                    '});'
                )). 
            '</div>';
    }

}
?>
