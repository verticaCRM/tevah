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

class PublisherWidget extends SortableWidget {

    /**
     * @var CActiveRecord $model
     */
    public $model; 

    public $template = '{widgetContents}';

    public $viewFile = '_publisherWidget';

    protected $containerClass = 'sortable-widget-container x2-layout-island history';

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array (
                'model' => $this->model,
            );
        }
        return $this->_viewFileParams;
    } 

    private static $_JSONPropertiesStructure;
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'hidden' => false,
                    'containerNumber' => 2,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function run () {
        if ($this->widgetManager->layoutManager->staticLayout) return;

        // hide widget if journal view is disabled
        if (!Yii::app()->params->profile->miscLayoutSettings['enableJournalView']) {
            $this->registerSharedCss ();
            $this->render ('application.components.sortableWidget.views.'.$this->sharedViewFile,
                array (
                    'widgetClass' => get_called_class (),
                    'profile' => $this->profile,
                    'hidden' => true,
                    'widgetUID' => $this->widgetUID,
                ));
            return;
        }

        parent::run ();
    }

}

?>
