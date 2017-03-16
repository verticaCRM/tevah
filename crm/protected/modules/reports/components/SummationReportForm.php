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

class SummationReportForm extends X2ReportForm {

    /**
     * @param CModel $formModel
     * @param string $name name of input
     * @param array $options attribute options for pill box dropdown
     */
    public function aggregatesPillBox (CModel $formModel, $name, array $options,
        array $htmlOptions = array ()) {

        if ($formModel->hasErrors($name)) X2Html::addErrorCss ($htmlOptions);
        CHtml::resolveNameID ($formModel, $name, $htmlOptions);
        return $this->widget ('AggregatesPillBox', array (
            'id' => $htmlOptions['id'],
            'name' => $htmlOptions['name'],
            'optionsHeader' => Yii::t('reports', 'Select an attribute:'),
            'value' => $formModel->$name,
            'htmlOptions' => $htmlOptions,
            'translations' => array (
                'delete' => Yii::t('reports', 'Delete attribute'),
                'max' => Yii::t('reports', 'maximum'),
                'min' => Yii::t('reports', 'minimum'),
                'avg' => Yii::t('reports', 'average'),
                'sum' => Yii::t('reports', 'sum'),
            ),
            'options' => $options,
        ), true);
    }

}

?>
