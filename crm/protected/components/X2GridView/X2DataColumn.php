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
 * Display column for attributes of X2Model subclasses.
 */
class X2DataColumn extends CDataColumn {

    private $_fieldType;

    protected $_data;

    public $fieldModel;

    public function getFieldType() {
        return isset($this->fieldModel['type']) ? $this->fieldModel['type'] : null;
    }

    /**
     * Renders the data cell content.
     * This method evaluates {@link value} or {@link name} and renders the result.
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent($row, $data){
        $this->data = $data;
        $value = null;
        if($this->value !== null) {
            $value = $this->evaluateExpression(
                $this->value, array('data' => $this->data, 'row' => $row));
        } elseif($this->name !== null){
            $value = $this->data->renderAttribute(
                $this->name, false, true); 
            if($this->data->getField($this->name)->type == 'text')
                $value = preg_replace("/\<br ?\/?\>/"," ",$value);
        }
        echo $value === null ? $this->grid->nullDisplay : $value; 
    }

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function renderDataCell($row)
	{
		$data=$this->grid->dataProvider->data[$row];
		$options=$this->htmlOptions;
        /* x2modstart */  
        if (isset ($options['title'])) 
            $options['title'] = Expression::evaluate (
                $options['title'], array('row'=>$row,'data'=>$data));
        /* x2modend */ 
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
    public function getData(){
        return $this->_data;
    }

    public function setData($data){
        if(is_array($data)){
            if(isset($this->grid, $this->grid->modelName)){
                $model = X2Model::model($this->grid->modelName);
                foreach($data as $key=>$value){
                    if($model->hasAttribute($key)){
                        $model->$key=$value;
                    }
                }
                $this->_data = $model;
            }
        }else{
            $this->_data = $data;
        }
    }

    public function renderFilterCellContent() {
        switch($this->fieldType){
            case 'boolean':
                echo CHtml::activeDropdownList(
                    $this->grid->filter, $this->name, 
                    array(
                        '' => '- '.Yii::t('app', 'Select').' -', 
                        '1' => Yii::t('app', 'Yes'),
                        'false' => Yii::t('app', "No")
                    ), 
                    array(
                        'class' => 'x2-minimal-select-filtercol'
                    )
                );
                break;
            case 'dropdown':
                $dropdown = Dropdowns::model()->findByPk($this->fieldModel['linkType']);
				
                if($dropdown instanceof Dropdowns) {
					
                    $options = json_decode($dropdown->options,1);
                    if (!$dropdown->multi) {
						
                    $defaultOption = array('' => '- '.Yii::t('app', 'Select').' -');
                    $options = is_array($options) ? 
                        array_merge($defaultOption,$options) : $defaultOption;
                    }
					
					
                    //$selected = isset($options[$this->grid->filter->{$this->name}]) ? 
                        //$this->grid->filter->{$this->name} : '';
						
						
						$dataHideSelectAll=false;
						if($this->name=="c_businesscategories")
							$dataHideSelectAll=true;
						
						echo CHtml::activeDropdownList(
                        $this->grid->filter, $this->name, $options,
						
                        array(
                            'class' => 'x2-minimal-select-filtercol'.
                                ($dropdown->multi ? 
                                 ' x2-multiselect-dropdown x2-datacolumn-multiselect' : ''),
                            'multiple' => $dropdown->multi ? 'multiple' : '',
                            'data-selected-text' => $dropdown->multi ? 'option(s)' : '',
							 'data-hide-selectall' => $dataHideSelectAll,
							 
                        )
                    );
                } else {
                    parent::renderFilterCellContent();
                }
                break;
            case 'visibility':
                echo CHtml::activeDropDownList($this->grid->filter, $this->name,
                    array(
                        '' => '- '.Yii::t('app', 'Select').' -', 1 => Yii::t('app', 'Public'),
                        0 => Yii::t('app', 'Private'), 2 => Yii::t('app', 'User\'s Groups')),
                    array('class' => 'x2-minimal-select-filtercol'));
                break;
            case 'dateTime':
            case 'date':
                Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                $that = $this;
                $renderWidget = function () use ($that) {
                    echo Yii::app()->controller->widget('CJuiDateTimePicker', 
                        array(
                            'model' => $that->grid->filter, //Model object
                            'attribute' => $that->name, //attribute name
                            'mode' => 'date', //use "time","date" or "datetime" (default)
                            'options' => array(// jquery options
                                'dateFormat' => Formatter::formatDatePicker ('medium'),
                            ),
                            'htmlOptions' => array(
                                'id' => 'datePicker'.$that->name,
                                'class' => 'datePicker x2-gridview-filter-datepicker'
                            ),
                            'language' => (Yii::app()->language == 'en') ? 
                                '' : Yii::app()->getLanguage(),
                        ), true);
                };
                if ($this->grid->ajax) {
                    X2Widget::ajaxRender ($renderWidget);
                } else {
                    $renderWidget ();
                }
                break;
            default:
                parent::renderFilterCellContent();
        }
    }
}
