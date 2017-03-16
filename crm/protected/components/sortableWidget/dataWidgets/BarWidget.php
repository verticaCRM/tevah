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

/* @edition:pro */

/**
 * @package application.components
 */
class BarWidget extends DataWidget {

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Bar Chart',
                    'displayType' => 'bar',
                    'orientation' => 'rows',
                    'stack' => false
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge(
                parent::getJSSortableWidgetParams(), array (
            ));
        }
        return $this->_JSSortableWidgetParams;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'widgetUID' => $this->widgetUID,
                )
            );
        }
        return $this->_viewFileParams;
    } 

    public static function getGridData($settings, $chart) {
        $report = $chart->report;
        
        $data = $report->instance->getData('all');
        if (!$data) {
            return self::error();
        }

        foreach ($data[2] as $key => $value) {
            if ($value == null) {
                $data[2][$key] = ' ';
            }
        }
        $formattedData = array( array_merge( array('categories'), $data[2]));
        array_pop($data[0]);
        foreach($data[0] as $key => $value) {
            unset($value[X2GridReport::TOTAL_ALIAS]);
            $row = array_values($value);
            if ($row[0] == null) {
                $row[0] = ' ';
            }
            $formattedData[] = $row;
        }

        $columns = $report->setting('columnField');
        $rows = $report->setting('rowField');
        $values = $report->setting('cellDataType');


        $chartData = array (
            'data' => $formattedData,
            'labels' => array (
                'rows' => $report->getAttrLabel($rows),
                'columns' => $report->getAttrLabel($columns),
                'values' => $report->getAttrLabel($values),
                )
            );

        return $chartData;
    }


    /**
     * Retrive and format Summation Data
     */
    public static function getSummationData ($settings, $chart){
        $report = $chart->report;        

        $values = $chart->setting ('values'); // The column with the numbers
        $categories = $chart->setting ('categories'); // first label columns
        $groups = $chart->setting ('groups'); // second label column
        $columns = array(
            $categories,
            $values
        );

        if ($groups) {
            $columns[] = $groups;
        }

         /*
         * Data should look like this:
         * array (
         *   0 => array (
         *       'assignedTo' => array( 
         *           'chames', 'admin', 'anyone', 'admin', ...
         *       ), 
         *       'count' => array(
         *           153, 623, 125, 521 ... 
         *       ), 
         *       'leadSource' => array(
         *           'facebook', 'facebook', 'facebook', 'google' ... 
         *       )
         *   ),
         *   ...
         *
         **/
        $data = $report->instance->getData($columns);
        if (!$data) {
            return self::error('missingColumn');
        }

        //Artificially Create a group  if one was not chosen
        if (!$groups) {
            $groups = Yii::t('charts', 'All');
            $data[0][$groups] = array_fill(0, count($data[0][$categories]), Yii::t('charts', 'All'));
        }

        $columns = $data[0];
        $formattedData = array();
        
        // The possible group option
        $groupOptions = array_unique ($columns[$groups]);

        /** 
         * This next loop will format the data into a structure like this:
         * array(
         *     'admin' => array (
         *         'facebook' => 623,
         *         'google' => 521
         *         ...
         *     ),
         *     'chames' => array (
         *         'facebook' => 153
         *     ),
         *     ...
         * )
         **/
        for($i = 0; $i < count($columns[$categories]); $i++) {
            $category = $columns[$categories][$i];
            $value = $columns[$values][$i];
            $group = $columns[$groups][$i];

            if (!array_key_exists($category, $formattedData)) {
                $formattedData[$category] = array();
            }

            if (!array_key_exists($group, $formattedData[$category])) {
                $formattedData[$category][$group] = 0;
            }

            //If duplicate group entries exists it add them together
            $formattedData[$category][$group] += $value;
        }

        /**
         * the front-end does not like null keys so we will swap it with 'None'
         */

        foreach($groupOptions as $key => $value) {
            if ($value = null) {
                $groupOptions[$key] = Yii::t('chart', ' ');
            }
        }

        /** 
         * Finally this next loop will format the data into a structure like this:
         * array(
         *     array ('categories', facebook', 'google' ...)
         *     array ('admin', 623, 521 ...)
         *     array ('chames', 153, 0,  ...)
         *      ...
         * )
         **/
        //First row
        $finalData = array ( array_merge( array('categories'), $groupOptions));
        foreach($formattedData as $key => $value) {
            if ($key == null) { $key = ' '; }
            
            $row = array($key);

            foreach($groupOptions as $group){
                $count = 0; // Default is 0 
                if (array_key_exists($group, $value)) {
                    $count = $value[$group];
                }

                $row[] = $count;
            }

            $finalData[] = $row;
        }

        /**
         * Arrange the data to how Barwidget.js expects it
         */
        $chartData = array(
            'data' => $finalData,
            'labels' => array (
                'rows' => $report->getAttrLabel($categories),
                'columns' => $report->getAttrLabel($groups),
                'values' => $report->getAttrLabel($values)
            )
        );

        return $chartData;

    }


    public function configBarItems(){
        return array_merge( 
            parent::configBarItems(),
            array( 
                array( 
                    'id' => 'orientation',
                    'class' => 'display-type fa fa-exchange',
                    'title' => Yii::t('charts', 'Transpose Row and Columns')
                ),
                array( 
                    'id' => 'stack',
                    'class' => 'display-type fa fa-bars',
                    'title' => Yii::t('charts', 'Stack Bars')
                ),                
                array( 
                    'class' => 'spacer'
                ),
                array( 
                    'id' => 'bar',
                    'class' => 'display-type fa fa-bar-chart',
                    'title' => Yii::t('charts', 'Bar Chart')
                ),
                array( 
                    'id' => 'line',
                    'class' => 'display-type fa fa-line-chart',
                    'title' => Yii::t('charts', 'Line Chart')
                ),
                array( 
                    'id' => 'area',
                    'class' => 'display-type fa fa-area-chart',
                    'title' => Yii::t('charts', 'Area Chart')
                ),
                array( 
                    'id' => 'pie',
                    'class' => 'display-type fa fa-pie-chart',
                    'title' => Yii::t('charts', 'Pie Chart')
                ),

            )
        );
    }

}
?>
