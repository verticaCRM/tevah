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

 Yii::import ('application.components.sortableWidget.views');

/**
 * @package application.components.compontents.sortableWidget.datawidget
 */
class TimeSeriesWidget extends DataWidget {

    /**
     * @see SortableWidget::$_JSONPropertiesStructure
     */
    private static $_JSONPropertiesStructure;

    /**
     * @see SortableWidget::getJSONPropertiesStructure()
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Activity Chart',
                    'displayType' => 'line',
                    'subchart' => false,
                    'timeBucket' => 'day',
                    'filter' => 'month',
                    'filterType' => 'trailing',
                    'filterFrom' => null,
                    'filterTo' => null,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * @see SortableWidget::getJSSortableWidgetParams()
     */
    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge(
                parent::getJSSortableWidgetParams(),
                array (
                    'primaryModelType' => $this->chart->report->setting ('primaryModelType')
                )
            );
        }
        return $this->_JSSortableWidgetParams;
    }


    /**
     * @see SortableWidget::getPackages()
     */
    public function getPackages () {
        $widgetClass = get_called_class();
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'momentJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/lib/moment-with-locales.min.js',
                        )
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * Gets the data for a RowsAndColumns Report 
     * @see DataWidget::getData()
     */
    public static function getRowsAndColumnsData ($settings, $chart){
        $report = $chart->report;

        $timeField = $chart->setting('timeField');
        $labelField = $chart->setting('labelField');

        $timeFrame = self::getTimeFrame($settings);
        $filters = $report->setting('allFilters');
        

        if ($timeFrame['start']) {
            array_push ( 
                $filters,
                array( 
                    'name' => $timeField,
                    'operator' => '>',
                    'value' => $timeFrame['start']
                )
            );
        }

        if ($timeFrame['end']) {
            array_push ( 
                $filters,
                array(
                    'name' => $timeField,
                    'operator' => '<',
                    'value' => $timeFrame['end']
                )
            );
        }

        $report->changeSetting('allFilters', $filters);

        $columns = array($timeField);
        if ($labelField) {
            $columns[] = $labelField;
        }

        $data = $report->instance->getData ($columns);
        if (!$data) {
            return self::error('missingColumn');
        }

        $chartData = array (
            'timeField' => $data[0][$timeField],    
            'timeFrame' => $timeFrame,
            'labels' => array(
                'timeField' => $data[2][0]
            )
        );

        if($labelField) {
            $chartData['labelField'] = $data[0][$labelField];
            $chartData['labels']['labelField'] = $data[2][1];
        }

        return $chartData;
    }


    /**
     * Uses {@link X2DateUtil} to compute a timeframe from chart settings
     * @param $settings array Chart settings must have keys
     * @return array 
     * @see X2DateUtil::parseDateRange
     */
    public static function getTimeFrame($settings) {
        $filter = $settings ['filter'];
        $filterType = $settings ['filterType'];

        if ($filter == 'custom') {
            $filterFrom = $settings['filterFrom'];
            $filterTo = $settings['filterTo'];
            $range = 'custom';
            return X2DateUtil::parseDateRange($range, $filterFrom, $filterTo);
        }

        $key = $filterType.ucfirst($filter);
        $range = X2DateUtil::parseDateRange($key);
        return $range;
    }

    /**
     * @see SortableWidget:: renderWidgetContent()
     */
    public function renderWidgetContents() {
        $this->render('application.components.sortableWidget.views._filterMenu', array(
            'relativeTimeOptions' => $this->relativeTimeOptions(),
            'timeUnitOptions' => $this->timeUnitOptions(),
            'timeBucketOptions' => $this->timeBucketOptions(),
            ));
        parent::renderWidgetContents();
    }

    /**
     * @see SortableWidget::getTranslations()
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (
                parent::getTranslations (),
                array (
                    'this hour' => Yii::t('charts', 'this hour'),
                    'this day' => Yii::t('charts', 'today'),
                    'this month' => Yii::t('charts', 'this month'),
                    'this week' => Yii::t('charts', 'this week'),
                )
            );
        }
        return $this->_translations;
    }

    /**
     * @see SortableWidget:: configBarItem()
     */
    protected function configBarItems(){
        return array_merge( 
            parent::configBarItems(),
            array(
                array(
                    'class' => 'fa fa-filter',
                    'id' => 'filter',
                    'title' => Yii::t('charts', 'Show filter options')
                ),
            ),
            array(
                array(
                    'class' => 'fa fa-toggle-down',
                    'id' => 'subchart',
                    'title' =>Yii::t('charts',  'Toggle mini-chart')
                ),
            ),
            array(
                array(
                   'class' => 'spacer',
                )
            ),
            self::displayTypeItems()
        );
    }

    private static function displayTypeItems(){
        return array( 
            array( 
                'id' => 'line',
                'class' => 'display-type fa fa-line-chart',
                'title' => Yii::t('charts', 'Line Chart') ),
            array( 
                'id' => 'area',
                'class' => 'display-type fa fa-area-chart',
                'title' => Yii::t('charts', 'Line Chart') ),
            array( 
                'id' => 'bar',
                'class' => 'display-type fa fa-bar-chart',
                'title' => Yii::t('charts', 'Line Chart') ),
            array( 
                'id' => 'pie',
                'class' => 'display-type fa fa-pie-chart',
                'title' => Yii::t('charts', 'Pie Chart') ),
            array( 
                'id' => 'gauge',
                'class' => 'display-type fa fa-tachometer',
                'title' => Yii::t('charts', 'Gauge Chart') ),
            array( 
                'class' => 'spacer')
            );
    }

    private function timeBucketOptions(){
        return  array( 
            array( 
                'content' => Yii::t('charts', 'show per'),
                'class' => 'indicator time-option',
                'title' => Yii::t('charts', '') ),
            array( 
                'id' => 'hour',
                'content' => Yii::t('charts', 'hour'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per hour') ),
            array( 
                'id' => 'day',
                'content' => Yii::t('charts', 'day'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per day') ),
            array( 
                'id' => 'week',
                'content' => Yii::t('charts', 'week'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per week') ),
            array( 
                'id' => 'month',
                'content' => Yii::t('charts', 'month'),
                'class' => 'time-option',
                'title' => Yii::t('charts', 'per month') ),
            );
    }

    private function relativeTimeOptions() {
        return array(
            array(
                'id' => 'trailing', 
                'content' => Yii::t('charts', 'trailing')
            ),
            array(
                'id' => 'this', 
                'content' => Yii::t('charts', 'this')
            ),
        );
    }

    private function timeUnitOptions() {
        return array(
            array('id' => 'day',    'content' => Yii::t('charts', 'day')),
            array('id' => 'week',   'content' => Yii::t('charts', 'week')),
            array('id' => 'month',  'content' => Yii::t('charts', 'month')),
            array('id' => 'quarter','content' => Yii::t('charts', 'quarter')),
            array('id' => 'year',   'content' => Yii::t('charts', 'year')),
            array('id' => 'custom', 'content' => Yii::t('charts', 'custom')),
        );
    }
}
?>
