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

class SummationReportGridView extends ReportGridView {

    /**
     * @var array $hiddenColumns Columns which aren't displayed but which get used to render the
     *  atributes in the group header
     */
    public $hiddenColumns; 

    public $itemsCssClass = 'items grid-report-items';

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'SummationReportDataColumn'; 

    public $gridViewJSClass = 'summationReportGvSettings';

    /**
     * @var array fields indexed by opt group names. Used to populate column selection dropdown
     */
    public $allColumnOptions;

    /**
     * @var bool $rememberColumnSort
     */
    public $rememberColumnSort = false; 

    /**
     * @var array $groupAttrs attributes by which the data is grouped
     */
    public $groupAttrs; 

    /**
     * @var array $reportConfig configuration array used when generating the report
     */
    public $reportConfig; 

    /**
     * @var array $_allColumnsByName Used by renderGroupHeader to speed up column search. Hidden
     *  columns and columns indexed by name
     */
    private $_allColumnsByName; 

    public function renderItems () {
        $dataColumnClass = $this->dataColumnClass;
        //$dataColumnClass::renderHeaderOptionDropdown ();
        parent::renderItems ();
    }

    public function registerClientScript() {
        parent::registerClientScript();
        if($this->enableGvSettings) {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().
                '/js/X2GridView/x2gridview.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->controller->module->getAssetsUrl ().
                    '/js/reportGridSettings.js', CCLientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->controller->module->getAssetsUrl ().
                    '/js/summationReportGridSettings.js', CCLientScript::POS_END);
        }
    }

    /**
     * Initializes hidden columns 
     */
    public function initColumns () {
        if (count ($this->hiddenColumns)) {
            $tmp = $this->columns;
            $this->columns = $this->hiddenColumns;
            parent::initColumns ();
            $this->hiddenColumns = $this->columns;
            $this->columns = $tmp;
        }
        parent::initColumns ();
    }

    protected function getJSClassOptions () {
        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'enableColDragging' => false,
                'reportConfig' => $this->reportConfig 
            ));
    }

    /**
     * Magic getter for $_allColumnsByName 
     */
    private function getAllColumnsByName () {
        if (!isset ($this->_allColumnsByName)) {
            $this->_allColumnsByName = array ();
            $allColumns = array_merge ($this->columns, $this->hiddenColumns);
            foreach ($allColumns as $column) {
                $this->_allColumnsByName[$column->name] = $column;
            }
        }
        return $this->_allColumnsByName;
    }

}
