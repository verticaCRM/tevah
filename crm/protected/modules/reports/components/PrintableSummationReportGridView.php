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


class PrintableSummationReportGridView extends PrintableReportsGridView {

	/**
	 * Renders a table body row.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 * @param integer $row the row number (zero-based).
	 */
	public function renderTableRow($row)
	{
		$htmlOptions=array();
		if($this->rowHtmlOptionsExpression!==null)
		{
			$data=$this->dataProvider->data[$row];
			$options=$this->evaluateExpression($this->rowHtmlOptionsExpression,array('row'=>$row,'data'=>$data));
			if(is_array($options))
				$htmlOptions = $options;
		}

		if($this->rowCssClassExpression!==null)
		{
			$data=$this->dataProvider->data[$row];
			$class=$this->evaluateExpression($this->rowCssClassExpression,array('row'=>$row,'data'=>$data));
		}
		elseif(is_array($this->rowCssClass) && ($n=count($this->rowCssClass))>0)
			$class=$this->rowCssClass[$row%$n];

		if(!empty($class))
		{
			if(isset($htmlOptions['class']))
				$htmlOptions['class'].=' '.$class;
			else
				$htmlOptions['class']=$class;
		}

        /* x2modstart */ 
        $data=$this->dataProvider->data[$row];
        if (!isset ($data[X2SummationReport::GROUP_HEADER_TOKEN])) {
            // add in rows of nested grids

            // if previous row is not part of this nested grid, that means this row is a nested
            // grid header row
            if ($row !== 0) {
                $prevRow=$this->dataProvider->data[$row - 1];
                if (isset ($prevRow[X2SummationReport::GROUP_HEADER_TOKEN]))
                    $htmlOptions['class'] .= ' group-header-row';
            }
		    echo CHtml::openTag('tr', $htmlOptions)."\n";
            $dataCount = count ($data);
            $colCount = count ($this->columns);
            $this->renderDrillDownRow ($data);

            // add extra empty cells to fill out the grid
            for ($i = $dataCount; $i < $colCount; $i++) {
                echo '<td></td>';
            }
        } else {
		    echo CHtml::openTag('tr', $htmlOptions)."\n";
            foreach($this->columns as $column)
                $column->renderDataCell($row);
        }
        /* x2modend */ 
		echo "</tr>\n";
	}

    public function renderDrillDownRow (array $data) {
        foreach ($data as $datum) {
            echo CHtml::openTag('td');
            if ($datum !== X2Report::EMPTY_ALIAS)
                echo CHtml::encode ($datum);
            echo '</td>';
        }
    }

}

?>
