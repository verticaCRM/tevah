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

/**
 * Validation for rows and columns report form 
 */
class RowsAndColumnsReportFormModel extends X2ReportFormModel {
    public $columns = array ();
    public $orderBy = array ();
    public function rules () {
        return array_merge (
            parent::rules (),
            array (
                array (
                    'columns, orderBy',
                    'application.components.validators.ArrayValidator',
                    'throwExceptions' => true,
                    'allowEmpty' => false,
                ),
                array ('orderBy', 'validateOrderBy', 'unique' => true),
                array ('columns', 'validateAttrs', 'unique' => true),
            )
        );
    }

    public function attributeLabels () {
        return array_merge (parent::attributeLabels (), array (
            'columns' => Yii::t('reports', 'Columns:'),
            'orderBy' => Yii::t('reports', 'Order by:'),
        ));
    }

}

?>
