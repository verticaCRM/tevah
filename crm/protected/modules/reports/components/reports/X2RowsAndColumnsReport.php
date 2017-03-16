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

class X2RowsAndColumnsReport extends X2Report {

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'RowsAndColumnsDataColumn'; 

    /**
     * @var null|array $columns 
     */
    public $columns;

    /**
     * @var array $_orderByAttributes 
     */
    private $_orderByAttributes; 

    private $_formattedColumnHeaders;

    /**
     * @param array $columns 
     * @param array $rows 
     * @param null|array|string $dateFilter null, array with two elements (start and end date 
     *  timestamps), or a dynamic date range
     * @param null|string $bucketSize 
     */
    public function getData (array $columns=array (), array $rows=array ()) {
        if (array_intersect ($columns, $this->columns) !== $columns) {
            return false;
        }

        $retArray = array ();

        $this->columns = $columns;
        $this->getRawData = true;
        $columnData = $this->generate ();
        $columnData = ArrayUtil::transpose ($columnData);
        $retArray[] = array ();

        if (!$columnData) {
            foreach ($columns as $i => $col) {
                $retArray[0][$col] = array();
            }
        } else {
            foreach ($columns as $i => $col) {
                $retArray[0][$col] = $columnData[$i];
            }            
        }
        $retArray[] = array ();
        $retArray[] = $this->getFormattedColumnHeaders ();
        return $retArray;

        // $sum = 0;
        // $values = array();
        // $values['timeField'] = array();
        // for($i = 0; $i<10; $i++) {
        //     $sum += rand(60, 300)*1000;
        //     $values['timeField'][] = $sum + strtotime('-1 month')*1000;
        // }

        // $values['labelField'] = array();
        // for($i = 0; $i<10; $i++) {
        //     $values['labelField'][] = 'data'.intval(rand(1, 5));
        // }

       // $actions = X2Model::model('Actions')->findAll(array('limit'=> 1000));

       // $timeArray = array_map(function($action) {
       //     return $action->createDate;
       // }, $actions);

       // $categoryArray = array_map(function($action) {
       //     return $action->type;
       // }, $actions);

       // AuxLib::debugLogR($values);
       // $values = array(
       //     'timeField' => $timeArray,
       //     'labelField' => $categoryArray,
       // );
       // AuxLib::debugLogR($values);
       // return $values;
    }

    public function generate () {
        ini_set('memory_limit', -1);
        set_time_limit(0);

        $qpg = new QueryParamGenerator (':generateRowsAndColumnsReport');
        $primaryModel = $this->getPrimaryModel ();
        $primaryTableName = $primaryModel->tableName ();
        $joinClause = $this->getJoinClauses ($qpg);

        $whereClause = 'WHERE '.$this->buildSQLConditions (array (
            array (
                $this->getFilterConditions ('any', $this->anyFilters, $qpg), 'AND',
            ),
            array (
                $this->getFilterConditions ('all', $this->allFilters, $qpg), 'AND',
            ),
            array (
                $this->getPermissionsCondition ($qpg), 'AND',
            )
        ));

        $orderByClause = $this->getOrderByClause ($this->orderBy);

        if (count ($this->columns)) {
           // AuxLib::debugLogR ('$this->columns = ');
            // AuxLib::debugLogR ($this->columns);

            $selectClause = $this->buildSelectClause ($this->columns, true);
            $query = $this->buildQueryCommand (
                $selectClause, $primaryTableName, $joinClause, $whereClause, null, null,
                $orderByClause);

             //AuxLib::debugLogR ($query->getText ());
             //AuxLib::debugLogR ($qpg->getParams ());

            // don't fetch associative since field names might repeat (as a result of a join)
            $records = $query->queryAll (true, $qpg->getParams ());
        } else {
            $records = array ();
        }

        // AuxLib::debugLogR ('$records = ');
        // AuxLib::debugLogR ($records);

        if ($this->includeTotalsRow) 
            $totalsRow = $this->getTotalsRow ($records, $this->columns);

        if ($this->getRawData) {
            return $records;
        } elseif ($this->print || $this->email) {
            $this->printReport ($this->formatData (array_merge (
                $records, 
                ($this->includeTotalsRow ? array ($totalsRow) : array ()))));
        } elseif ($this->export) {
            $this->export (array_merge (
                array ($this->getFormattedColumnHeaders ()),
                $this->formatData (array_merge ($records, 
                    ($this->includeTotalsRow ? array ($totalsRow) : array ())))));

        } else {
            if ($this->includeTotalsRow)
                $totalsRowGridViewParams = $this->getRowsAndColumnsReportGridViewParams (
                    array ($totalsRow), 'summation-grid');

            Yii::app()->controller->renderPartial (
                'application.modules.reports.components.reports.views._rowsAndColumnsReport',
                array (
                    'gridViewParams' => $this->getRowsAndColumnsReportGridViewParams (
                        $records),
                    'totalsRowGridViewParams' => $this->includeTotalsRow ? 
                        $totalsRowGridViewParams : null,
                ), false, true);
        }

    }

    /**
     * @return array 
     */
    public function getOrderByAttributes () {
        if (!isset ($this->_orderByAttributes)) {
            $this->_orderByAttributes = array_map (function ($a) { 
                return $a[0]; 
            }, $this->orderBy);
        }
        return $this->_orderByAttributes;
    }

    /**
     * Determine relevant related models by searching through specified attributes
     */
    protected function getRelatedModelsByLinkField () {
        if (!isset ($this->_relatedModelsByLinkField)) {
            $this->_relatedModelsByLinkField = $this->_getRelatedModelsByLinkField (
                array_merge (
                    $this->columns, $this->getAnyFilterAttrs (), $this->getAllFilterAttrs (),
                    $this->getOrderByAttributes ()));
        }
        return $this->_relatedModelsByLinkField;
    }

    protected function printReport (array $data) {
        $formattedColumnHeaders = $this->getFormattedColumnHeaders ();
        $columns = array_reverse ($this->columns);
        $gridColumns = array ();
        foreach ($formattedColumnHeaders as $header) {
            $gridColumns[] = array (
                'name' => array_pop ($columns),
                'header' => $header,
                'type' => 'raw',
            );
        }

        $reportDataProvider = new CArrayDataProvider ($data, array(
            'id' => $this->_gridId,
            'keyField' => false, 
            'pagination' => array ('pageSize'=>PHP_INT_MAX),
        ));

    
        Yii::app()->controller->renderPartial (
            'application.modules.reports.components.reports.views._printReport', array (
            'dataProvider' => $reportDataProvider,
            'columns' => $gridColumns,
        ), false, !$this->email);
    }

    /**
     * Use related models to format column headers
     * @return array headers
     */
    protected function getFormattedColumnHeaders () {
        if (!isset ($this->_formattedColumnHeaders)) {
            $formattedHeaders = array ();
            foreach ($this->columns as $col) {
                list ($columnAttrModel, $columnAttr) = $this->getModelAndAttr ($col);
                $formattedHeaders[] = $columnAttrModel->getAttributeLabel ($columnAttr);
            }
            $this->_formattedColumnHeaders = $formattedHeaders;
        }
        return $this->_formattedColumnHeaders;
    }

    /**
     * @param array $reportRecords
     * @return array parameters which can be used to instantiate report grid view 
     */
    protected function getRowsAndColumnsReportGridViewParams (
        $reportRecords, $id=null) {

        $id = $id ? $id : $this->_gridId;

        $columns = $this->columns;
        $relatedModelsByLinkField = $this->getRelatedModelsByLinkField ();

        // build columns array for CGridView
        $gridColumns = array ();
        foreach ($columns as $col) {
            // get grid column label
            list ($columnAttrModel, $columnAttr, $fns) = $this->getModelAndAttr ($col);
            $column = array (
                'name' => $col,
                'header' => $this->getAttributeLabel ($columnAttrModel, $columnAttr, $fns),
                'type' => 'raw',
                'fns' => $fns,
                'attribute' => $columnAttr,
                'modelType' => get_class ($columnAttrModel)
            );
        
            $gridColumns[] = $column;
        }
       // AuxLib::debugLogR ('$gridColumns = ');
        // AuxLib::debugLogR ($gridColumns);

        // AuxLib::debugLogR ('$columns = ');
        // AuxLib::debugLogR ($this->columns);

        $reportDataProvider = new CArrayDataProvider($reportRecords,array(
            'id' => $id,
            'keyField' => false, 
            'pagination' => array('pageSize'=>Profile::getResultsPerPage()),
        ));

        $reportDataProvider->pagination->route = Yii::app()->request->pathInfo;
        $reportDataProvider->pagination->params = $_GET;

        // AuxLib::debugLogR ($reportDataProvider->getData ());

        return array (
            'dataProvider' => $reportDataProvider,
            'id' => $id,
            'columns' => $gridColumns,
        );
    }

    protected function formatData (array $data) {
        $relatedModelsByLinkField = $this->getRelatedModelsByLinkField ();

        $colModelsAndAttrs = array ();
        foreach ($this->columns as $col) {
            list ($colModel, $colAttr) = $this->getModelAndAttr ($col);
            $colModelsAndAttrs[] = array ($colModel, $colAttr);
        }

        $rowCount = count ($data);
        $colCount = count ($data[0]);
        for ($i = 0; $i < $rowCount; $i++) {
            $row = &$data[$i];
            $j = 0;
            foreach ($row as $col => &$val) {
                if ($val === self::EMPTY_ALIAS || $col === self::HIDDEN_ID_ALIAS) { 
                    $val = '';
                    $j++;
                    continue;
                }

                $colModelAndAttr = $colModelsAndAttrs[$j];
                $colModel = $colModelAndAttr[0];
                $colAttr = $colModelAndAttr[1];
                $colModel->$colAttr = $val;
                $val = $colModel->renderAttribute ($colAttr, false, true, false);
                $j++;
            }
        }
        return $data;
    }

}
