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

Yii::import('zii.widgets.grid.CGridView');
Yii::import('X2GridViewBase');

/**
 * @package application.components.X2GridView
 */
class X2GridViewGeneric extends X2GridViewBase {

    /**
     * @var bool $rememberColumnSort whether or not to preserve order of columns in gvSettings
     */
    public $rememberColumnSort = true;  

    /**
     * Used to populate allFieldNames property with attribute labels indexed by
     * attribute names.
     */
    protected function addFieldNames () {
        foreach ($this->columns as $column) {
            $header = (isset ($column['header'])) ? $column['header'] : '';
            $name = (isset ($column['name'])) ? $column['name'] : '';
            $this->allFieldNames[$name] = $header;
        }
    }

    protected function generateColumns () {
        $unsortedColumns = array ();

        foreach ($this->columns as &$column) {
            $name = (isset ($column['name'])) ? $column['name'] : '';
            if (!isset ($column['id'])) {
                if (isset ($column['class']) && 
                    is_subclass_of ($column['class'], 'CCheckboxColumn')) {

                    $column['id'] = $this->namespacePrefix.'C_gvCheckbox'.$name;
                } else {
                    $column['id'] = $this->namespacePrefix.'C_'.$name;
                }
            } else {
                $column['id'] = $this->namespacePrefix.$column['id'];
            }
            if (!isset ($this->gvSettings[$name])) {
                $unsortedColumns[] = $column;
                continue;
            }
            $width = $this->gvSettings[$name];
            $width = $this->formatWidth ($width);
            $column['headerHtmlOptions'] = array('style'=>'width:'.$width.';');
            $column['htmlOptions'] = X2Html::mergeHtmlOptions (
                isset ($column['htmlOptions']) ? 
                    $column['htmlOptions'] : array (), array ('width' => $width));
        }
        unset ($column); // unset lingering reference

        if (isset ($this->gvSettings['gvControls']) && $this->enableControls) {
            $width = $this->gvSettings['gvControls'];
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $this->columns[] =  $this->getGvControlsColumn ($width);
        }
        if (isset ($this->gvSettings['gvCheckBox'])) {
            $width = $this->gvSettings['gvCheckBox'];
            $width = (!empty($width) && is_numeric($width))? $width : null;
            $this->columns[] =  $this->getGvCheckboxColumn ($width);
        }

        if ($this->rememberColumnSort) {
            $sortedColumns = array ();
            foreach ($this->gvSettings as $columnName => $width) {
                foreach ($this->columns as $column) {
                    $name = (isset ($column['name'])) ? $column['name'] : '';
                    if ($name === $columnName) {
                        $sortedColumns[] = $column;
                        break;
                    } 
                }
            }
            $this->columns = array_merge ($sortedColumns, $unsortedColumns);
        } 
    }


    public function setSummaryText () {

        /* add a dropdown to the summary text that let's user set how many rows to show on each 
           page */
        $this->summaryText =  Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>')
        .'<div class="form no-border" style="display:inline;"> '
        .CHtml::dropDownList(
            'resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(),
            array(
                'ajax' => array(
                    'url' => Yii::app()->controller->createUrl('/profile/setResultsPerPage'),
                    'data' => 'js:{results:$(this).val()}',
                    'complete' => 'function(response) { 
                        $.fn.yiiGridView.update("'.$this->id.'"); 
                    }',
                ),
                'id' => 'resultsPerPage'.$this->id,
                'style' => 'margin: 0;',
                'class' => 'x2-select resultsPerPage',
            )
        ).'</div>';
    }

}
?>
