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

Yii::import('zii.widgets.grid.CDataColumn');

/**
 * Adds column header dropdown menu for grouping and aggregate operations
 */

class SummationReportDataColumn extends ReportDataColumn {

	/**
	 * Renders a data cell.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 * @param integer $row the row number (zero-based)
	 */
	public function renderDataCell($row)
	{
		$data=$this->grid->dataProvider->data[$row];
		$options=$this->htmlOptions;
		if($this->cssClassExpression!==null)
		{
			$class=$this->evaluateExpression(
                $this->cssClassExpression,array('row'=>$row,'data'=>$data));
			if(!empty($class))
			{
				if(isset($options['class']))
					$options['class'].=' '.$class;
				else
					$options['class']=$class;
			}
		}
		echo CHtml::openTag('td',$options);
        /* x2modstart */       
        if ($this->name === 'subgrid-expand-button-column') {
            $this->renderSubgridExpandButton ($data);
        } else {
		    $this->renderDataCellContent($row,$data);
        }
        /* x2modend */ 
		echo '</td>';
	}

    /**
     * Renders button which expands/hides sub grid 
     * @param array $data
     */
    public function renderSubgridExpandButton ($data) {
        // group by attribute values are stored in html attribute so that they can be retrieved
        // in JS and sent to the server when the button is clicked
        echo '<button class="x2-button subgrid-expand-button" 
            title="'.Yii::t('reports', 'Expand').'"
            data-group-attr-values="'.
                CHtml::encode (CJSON::encode ($this->getGroupAttrValues ($data))).'">+</button>';
        echo '<button class="x2-button subgrid-collapse-button" 
            title="'.Yii::t('reports', 'Collapse').'" style="display: none;">-</button>';
    }

    /**
     * @param array $data
     * @return array group attr values indexed by attribute 
     */
    protected function getGroupAttrValues ($data) {
        $groupAttrs = $this->grid->groupAttrs;
        $groupAttrValues = array ();
       AuxLib::debugLogR ('$data = ');
        AuxLib::debugLogR ($data);

        foreach ($groupAttrs as $attr) {
            $groupAttrValues[$attr] = $data[$attr];
        }
        return $groupAttrValues;
    }

}
