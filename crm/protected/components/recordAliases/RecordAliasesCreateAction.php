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

class RecordAliasesCreateAction extends CAction {

    public function run (array $RecordAliases) {
        if (Yii::app()->user->isGuest) {
            Yii::app()->controller->redirect(Yii::app()->controller->createUrl('/site/login'));
        }

        $recordAliases = new RecordAliases;
        $recordAliases->setAttributes ($RecordAliases);
        $recordAliases->recordType = Yii::app()->controller->modelClass;

        $model = $recordAliases->getModel ();
        if (!Yii::app()->params->isAdmin && 
            !Yii::app()->controller->checkPermissions ($model, 'edit')) {

            Yii::app()->controller->denied ();
        }

        if ($recordAliases->validate ()) {
            $recordAliases->save ();
            echo CJSON::encode (array (
                'success' => array (
                    'alias' => $recordAliases->renderAlias (),
                    'id' => $recordAliases->id,
                ),
            ));
        } else {
            $model = $recordAliases->getModel ();
            echo CJSON::encode (array (
                'failure' => 
                    Yii::app()->controller->widget('RecordAliasesWidget', array(
                        'model' => $model,
                        'formOnly' => true,
                        'aliasModel' => $recordAliases
                    ), true),
            ));
        }
    }

}

?>
