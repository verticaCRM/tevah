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

class EmailInboxesSearchForm extends CActiveForm {

    /**
     * @var EmailInboxesSearchFormModel $formModel
     */
    public $formModel;

    public function renderInputs ($attributes) {
        foreach ($attributes as $attr) {
            $operandType = EmailInboxes::$searchOperators[$attr];
            switch ($operandType) {
                case '': 
                    echo $this->checkBox ($this->formModel, $attr);
                    echo $this->label ($this->formModel, $attr);
                    break;
                case 'date':
                    echo $this->label ($this->formModel, $attr);
                    echo '<br/>';
                    echo X2Html::activeDatePicker ($this->formModel, $attr);
                    break;
                case 'string':
                    echo $this->label ($this->formModel, $attr);
                    echo '<br/>';
                    echo $this->textField ($this->formModel, $attr);
                    break;
                default:
                    throw new CException ('Invalid search operand type: '.$operandType);
            }
            echo '<br/>';
        }

    }

}

?>
