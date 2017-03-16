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

/* @edition:pro */

// hide certain columns and ui elements in line items view so that it can fit in the publisher

Yii::app()->clientScript->registerCssFile(Yii::app()->getModule ('quotes')->assetsUrl.'/css/lineItemsMini.css');

?>

<div id='<?php echo $this->resolveId ('products'); ?>' class='publisher-form' 
 <?php echo ($startVisible ? '' : "style='display: none;'"); ?>>


    <div class="text-area-wrapper">
        <?php 
        echo $model->renderInput ('actionDescription',
            array(
                'rows' => 3,
                'cols' => 40,
                'class'=>'action-description',
                'id'=>'products-action-description',
            ));
        ?>
    </div>

    <?php
    Yii::app()->controller->renderPartial ('application.modules.quotes.views.quotes._lineItems',
        array (
            'model' => new Quote,
            'readOnly' => false,
            'module' => Yii::app()->getModule ('quotes'),
            'products' => Product::activeProducts (),
            'actionsTab' => true,
            'namespacePrefix' => $this->namespace,//'productsTab',
            'saveButtonId' => 'save-publisher'
        )
    );
    ?>

</div>
