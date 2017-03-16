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

class RecordAliasesWidget extends X2Widget {

    /**
     * @var X2Model $model
     */
    public $model; 

    /**
     * @var RecordAliases $aliasModel
     */
    public $aliasModel; 

    /**
     * @var bool $formOnly
     */
    public $formOnly = false; 

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'RecordAliasesWidget' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/RecordAliasesWidget.js',
                ),
                'depends' => array ('auxlib'),
            ),
        ));
    }

    public function getAliases () {
        return RecordAliases::getAliases ($this->model);
    }

    public function run () {
        $this->registerPackages ();
        if (!isset ($this->aliasModel)) {
            $this->aliasModel = new RecordAliases;
            $this->aliasModel->recordId = $this->model->id;
            $this->aliasModel->recordType = get_class ($this->model);
        }
        $this->render ('_recordAliasesWidget', array (
            'aliasModel' => $this->aliasModel,
        ));
    }

}

?>
