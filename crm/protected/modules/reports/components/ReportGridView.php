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

class ReportGridView extends X2GridViewGeneric {
    public $itemsCssClass = 'items grid-report-items';
    public $enableDbPersistentGvSettings = false;

    /**
     * @var array $reportButtons buttons displayed in reports title bar
     */
    public $reportButtons = array ('print', 'email', 'export'); 

    /**
     * @var string $dataColumnClass
     */
    public $dataColumnClass = 'ReportDataColumn'; 

    public function run () {
        if (isset ($this->htmlOptions['class']) && 
            !preg_match ('/grid-view/', $this->htmlOptions['class'])) {

            $this->htmlOptions['class'] .= ' grid-view';
        }
        parent::run ();
    }

	/**
	 * Renders the pager.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function renderPager()
	{
		if(!$this->enablePagination)
			return;

		$pager=array();
		$class='CLinkPager';
		if(is_string($this->pager))
			$class=$this->pager;
		elseif(is_array($this->pager))
		{
			$pager=$this->pager;
			if(isset($pager['class']))
			{
				$class=$pager['class'];
				unset($pager['class']);
			}
		}
		$pager['pages']=$this->dataProvider->getPagination();

		if($pager['pages']->getPageCount()>1)
		{
			echo '<div class="'.$this->pagerCssClass.'">';
			$this->widget($class,$pager);
			echo '</div>';
		}
		else {
            /* x2modstart */ 
			echo '<div class="'.$this->pagerCssClass.' empty-pager">';
			$this->widget($class,$pager);
			echo '</div>';
            /* x2modend */ 
        }
	}

    public function renderExportButton () {
        echo '<a title="'.Yii::t('app', 'Export to CSV').'"
            class="x2-button report-export-button">'.Yii::t('reports', 'Export').'</a>';
    }

    public function renderPrintButton () {
        echo '<a title="'.Yii::t('app', 'Print Report').'"
            class="x2-button report-print-button">'.Yii::t('reports', 'Print').'</a>';
    }

    public function renderChartButton () {
        echo '<a title="'.Yii::t('app', 'Create to Charts').'"
            class="x2-button report-chart-button">'.Yii::t('reports', 'Chart').'</a>';
    }

    public function renderEmailButton () {
        echo '<a title="'.Yii::t('app', 'Email Report').'"
            class="x2-button report-email-button">'.Yii::t('reports', 'Email').'</a>';
    }

    public function renderReportButtons () {
        echo '<div class="x2-button-group">';
        foreach ($this->reportButtons as $button) {
            switch ($button) {
                case 'export':
                    $this->renderExportButton ();
                    break;
                case 'print':
                    $this->renderPrintButton ();
                    break;
                case 'email':
                    $this->renderEmailButton ();
                    break;                
                case 'chart':
                    $this->renderChartButton ();
                    break;
            }
        }
        echo '</div>';
    }

    protected function getJSClassOptions () {
        $currPageRawData = $this->dataProvider->getData ();
        $arrayData = array ();
        // convert associated array to non-associative array to prevent 
        // JSONification from changing ordering
        $i = 0;
        foreach ($currPageRawData as $row) {
            $arrayData[] = array ();
            foreach ($row as $key => $val) {
                if ($key === X2Report::HIDDEN_ID_ALIAS) continue;
                $arrayData[$i][] = array ($key, $val);
            }
            $i++;
        }

        return array_merge (
            parent::getJSClassOptions (), 
            array (  
                'currPageRawData' => $arrayData,
                'headers' => array_map (function ($col) {
                    return isset ($col['name']) ? $col['name'] : null;
                }, $this->columns),
            ));
    }


}
