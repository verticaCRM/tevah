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

Yii::import ('application.components.sortableWidget.SortableWidget');
Yii::import('application.components.sortableWidget.SortableWidgetResizeBehavior');

/**
 * @package application.components
 */
class IframeWidget extends SortableWidget {

    public $canBeDeleted = true;

    public $defaultTitle = 'Website Viewer';

    public $sortableWidgetJSClass = 'IframeWidget';

    public $relabelingEnabled = true;

    public $viewFile = '_iframeWidget';

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    private static $_JSONPropertiesStructure;

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'url' => self::getJSONProperty (
                        $this->profile, 'url', $this->widgetType, $this->widgetUID),
                    'height' => self::getJSONProperty (
                        $this->profile, 'height', $this->widgetType, $this->widgetUID),
                )
            );
        }
        return $this->_viewFileParams;
    } 

    protected function getSettingsMenuContent () {
        $htmlStr = 
            '<div class="widget-settings-menu-content" style="display:none;">
                <ul>'. 
                    ($this->relabelingEnabled ? 
                    '<li class="relabel-widget-button">'.
                        Yii::t('app', 'Rename Widget').
                    '</li>' : '').
                    ($this->canBeDeleted ? 
                        '<li class="delete-widget-button">'.
                            Yii::t('app', 'Delete Widget').
                        '</li>' : '').
                    '<li class="change-url-button">'.
                        Yii::t('profile', 'Change URL').
                    '</li>
                </ul>
            </div>';
        if ($this->relabelingEnabled) {
            $htmlStr .= 
                '<div id="relabel-widget-dialog-'.$this->widgetUID.'" style="display: none;">
                    <div>'.Yii::t('app', 'Enter a new name:').'</div>  
                    <input class="new-widget-name">
                </div>';
        }
        if ($this->canBeDeleted) {
            $htmlStr .= 
                '<div id="delete-widget-dialog-'.$this->widgetUID.'" style="display: none;">
                    <div>'.
                        Yii::t('app', 'Performing this action will cause this widget\'s settings '.
                            'to be lost. This action cannot be undone.').
                    '</div>  
                </div>';
        }

        $htmlStr .= 
            '<div id="change-url-dialog-'.$this->widgetUID.'" class="change-url-dialog" 
              style="display: none;">'.
                '<div>'.Yii::t('profile', 'Enter a URL:').'</div>'.
                '<input class="iframe-url">'.
            '</div>';
        return $htmlStr;
    }


    /**
     * overrides parent method
     */
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'url' => '', 
                    'label' => Yii::t('app', 'Website Viewer'),
                    'height' => '200',
                    'hidden' => true
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge (parent::getJSSortableWidgetParams (),
                array (
                    'enableResizing' => true,
                )
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (parent::getTranslations (), array (
                'dialogTitle'=> Yii::t('profile', 'Change URL'),
                'closeButton'=> Yii::t('profile', 'Close'),
                'selectButton'=> Yii::t('profile', 'Change'),
                'urlError'=> Yii::t('profile', 'URL cannot be blank'),
            ));
        }
        return $this->_translations;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'IframeWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/sortableWidgets/IframeWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * Magic getter. Returns this widget's css
     * @return array key is the proposed name of the css string which should be passed as the first
     *  argument to yii's registerCss. The value is the css string.
     */
    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array_merge (
                parent::getCss (),
                array (
                    'iframeWidgetCss' => "
                        #".get_called_class()."-widget-content-container {
                            padding-bottom: 1px;
                        }

                        .change-url-dialog p {
                            display: inline;
                            margin-right: 5px;
                        }

                        .default-text-container {
                            text-align: center;
                            position: absolute;
                            top: 0;
                            bottom: 0;
                            left: 0;
                            right: 0;
                        }

                        .default-text-container a {
                            height: 17%;
                            text-decoration: none;
                            font-size: 16px;
                            margin: auto;
                            position: absolute;
                            left: 0;
                            top: 0;
                            right: 0;
                            bottom: 0;
                        }
                    "
                )
            );
        }
        return $this->_css;
    }

}
?>
