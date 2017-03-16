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

class X2CheckBoxColumn extends CCheckBoxColumn {

    /**
     * @var array $headerHtmlOptions  
     */
    public $headerHtmlOptions =  array ();
    public $headerCheckBoxHtmlOptions =  array ();

    /**
	 * Renders the header cell content.
	 * This method will render a checkbox in the header when {@link selectableRows} is greater than 1
	 * or in case {@link selectableRows} is null when {@link CGridView::selectableRows} is greater than 1.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function renderHeaderCellContent()
	{
		if(trim($this->headerTemplate)==='')
		{
			echo $this->grid->blankDisplay;
			return;
		}

		$item = '';
		if($this->selectableRows===null && $this->grid->selectableRows>1)
            /* x2modstart */ 
			$item = CHtml::checkBox(
                $this->id.'_all',false,
                array_merge (
                    array('class'=>'select-on-check-all'), $this->headerCheckBoxHtmlOptions));
            /* x2modend */
		elseif($this->selectableRows>1)
            /* x2modstart */    
			$item = CHtml::checkBox(
                $this->id.'_all',false, $this->headerCheckBoxHtmlOptions);
            /* x2modend */ 
		else
		{
			ob_start();
			parent::renderHeaderCellContent();
			$item = ob_get_clean();
		}

		echo strtr($this->headerTemplate,array(
			'{item}'=>$item,
		));
	}

	/**
	 * Renders the data cell content.
	 * This method renders a checkbox in the data cell.
	 * @param integer $row the row number (zero-based)
	 * @param mixed $data the data associated with the row
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	protected function renderDataCellContent($row,$data)
	{
		if($this->value!==null)
			$value=$this->evaluateExpression($this->value,array('data'=>$data,'row'=>$row));
		elseif($this->name!==null)
			$value=CHtml::value($data,$this->name);
		else
			$value=$this->grid->dataProvider->keys[$row];

		$checked = false;
		if($this->checked!==null)
			$checked=$this->evaluateExpression($this->checked,array('data'=>$data,'row'=>$row));

		$options=$this->checkBoxHtmlOptions;
		if($this->disabled!==null)
			$options['disabled']=$this->evaluateExpression($this->disabled,array('data'=>$data,'row'=>$row));

		$name=$options['name'];
		unset($options['name']);
		$options['value']=$value;
        /* x2modstart */ 
        // made id customizable through interface
        if (isset ($options['id'])) {
            $options['id'] = $this->evaluateExpression (
                $options['id'], array ('data' => $data, 'row' => $row));
        }
        if (!isset ($options['id']))
        /* x2modend */ 
		    $options['id']=$this->id.'_'.$row;
		echo CHtml::checkBox($name,$checked,$options);
	}
}

?>
