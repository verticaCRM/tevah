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

class ReportDataColumn extends CDataColumn {

    /**
     * @var string $attribute
     */
    public $attribute; 

    /**
     * @var string $modelType
     */
    public $modelType; 

    /**
     * @var $fns
     */
    public $fns = array (); 

    /**
     * @var CModel $_model
     */
    private $_model; 

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
			$class=$this->evaluateExpression($this->cssClassExpression,array('row'=>$row,'data'=>$data));
			if(!empty($class))
			{
				if(isset($options['class']))
					$options['class'].=' '.$class;
				else
					$options['class']=$class;
			}
		}
		echo CHtml::openTag('td',$options);
		$this->renderDataCellContent($row,$data);
		echo '</td>';
	}

    public function getModel () {
        if (!isset ($this->_model)) {
            $modelType = $this->modelType;
            if (isset ($modelType)) {
                $this->_model = $modelType::model ();
            }
        }
        return $this->_model;
    }

    public function renderDate ($value, $dateFn) {
        switch ($dateFn) {
            case 'second':
            case 'minute':
            case 'hour':
            case 'day':
            case 'year':
                return $value;
            case 'month':
                return Yii::app()->locale->getMonthName ($value, 'wide');
        }
    }

    private $_dateFn; 
    public function getDateFn () {
        if (!isset ($this->_dateFn)) {
            foreach ($this->fns as $fn) {
                if (in_array ($fn, array ('second', 'minute', 'hour', 'day', 'year', 'month'))) {
                    $this->_dateFn = $fn;
                    break;
                }
            }
        }
        return $this->_dateFn;
    }

	/**
     * Overrides {@link parent::renderDataCellContent} to add model-based rendering
	 * @param integer $row
	 * @param mixed $data
	 */
	protected function renderDataCellContent ($row, $data) {
        $model = $this->getModel ();
        $value = null;
        //AuxLib::debugLogR ('rendering attr '.$this->attribute);
        //AuxLib::debugLogR ('name: '.$this->name);
        if (isset ($data[$this->name]) && $data[$this->name] === X2Report::EMPTY_ALIAS) {
            echo $this->grid->nullDisplay;
            return;
        } elseif (isset ($data[$this->name]) && $model !== null && $this->attribute !== null && 
            $this->name !== null && $this->getDateFn () === null) {

            $attr = $this->attribute;
            $model->$attr = $data[$this->name];
            if ($attr === 'name') {
                $model->id = $data[X2Report::HIDDEN_ID_ALIAS];
                $value = $model->link;
            } else {
                $value = $model->renderAttribute ($attr);
            }
        } elseif (isset ($data[$this->name])) {
            if ($this->getDateFn ())
                $value = $this->renderDate ($data[$this->name], $this->getDateFn ());
            else
                $value = CHtml::encode ($data[$this->name]);  
        } else {
            parent::renderDataCellContent ($row, $data);
            return;
        }
		echo $value === null ? $this->grid->nullDisplay : $value;
	}
}

?>
