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
class X2ButtonColumn extends CButtonColumn {

    public $viewButtonImageUrl = false; 
    public $updateButtonImageUrl = false; 
    public $deleteButtonImageUrl = false; 
    public $name;

    /**
	 * Registers the client scripts for the button column.
	 */
	protected function registerClientScript()
	{
		$js=array();
		foreach($this->buttons as $id=>$button)
		{
			if(isset($button['click']))
			{
				$function=CJavaScript::encode($button['click']);
				$class=preg_replace('/\s+/','.',$button['options']['class']);
                /* x2modstart */ 
				$js[]= "
                    $(document).unbind ('click.CButtonColumn".$id."');
                    $(document).on (
                        'click.CButtonColumn".$id."','#{$this->grid->id} a.{$class}',$function);
                ";
                /* x2modend */ 
			}
		}

		if($js!==array())
			Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id, implode("\n",$js));
	}


	/**
	 * Initializes the default buttons (view, update and delete).
	 */
	protected function initDefaultButtons()
	{
        $this->viewButtonLabel = 
            "<span class='fa fa-search' title='".CHtml::encode (Yii::t('app', 'View record'))."'>
             </span>";
        $this->updateButtonLabel = 
            "<span class='fa fa-edit' title='".CHtml::encode (Yii::t('app', 'Edit record'))."'>
             </span>";
        $this->deleteButtonLabel = 
            "<span class='fa fa-times x2-delete-icon' 
              title='".CHtml::encode (Yii::t('app', 'Delete record'))."'></span>";
        parent::initDefaultButtons ();
	}

}
