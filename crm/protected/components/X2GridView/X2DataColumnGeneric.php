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

class X2DataColumnGeneric extends CDataColumn {

    /**
     * @var string $filterType
     */
    public $filterType; 

	/**
	 * Renders the filter cell content.
	 * This method will render the {@link filter} as is if it is a string.
	 * If {@link filter} is an array, it is assumed to be a list of options, and a dropdown selector will be rendered.
	 * Otherwise if {@link filter} is not false, a text field is rendered.
	 * @since 1.1.1
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	protected function renderFilterCellContent()
	{
		if(is_string($this->filter)) {
			echo $this->filter;
		} elseif($this->filter!==false && $this->grid->filter!==null && $this->name!==null && 
            strpos($this->name,'.')===false) {

            /* x2modstart */ 
            if (isset ($this->filterType)) {
                echo $this->renderFilterCellByType ();
            /* x2modend */ 
			} elseif(is_array($this->filter)) {
                /* x2modstart */ 
                // removed prompt
				echo CHtml::activeDropDownList(
                    $this->grid->filter, $this->name, $this->filter,
                    array('id'=>false));
                /* x2modend */
			} elseif($this->filter===null) {
				echo CHtml::activeTextField($this->grid->filter, $this->name, array('id'=>false));
            }
		} else {
			parent::renderFilterCellContent();
        }
	}

    public function renderFilterCellByType () {
        $model = $this->grid->filter;
        switch ($this->filterType) {
            case 'date':
                return X2Html::activeDatePicker ($model, $this->name);
                break;
            case 'dateTime':
                return X2Html::activeDatePicker ($model, $this->name, array (), 'datetime');
                break;
        }
    }
}

?>
