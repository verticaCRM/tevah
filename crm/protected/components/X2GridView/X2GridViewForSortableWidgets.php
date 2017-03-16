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

Yii::import('X2GridViewSortableWidgetsBehavior');


/**
 * Used to display gridviews within sortable widgets. This allows sortable widget gridviews to have
 * their own results per page settings.
 * @package application.components
 */
class X2GridViewForSortableWidgets extends X2GridView {

    public function __construct ($owner=null) {
        $this->attachBehavior (
            'X2GridViewSortableWidgetsBehavior', new X2GridViewSortableWidgetsBehavior);
        parent::__construct ($owner);
    }

}
?>
