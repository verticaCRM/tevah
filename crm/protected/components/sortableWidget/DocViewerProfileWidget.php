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
class DocViewerProfileWidget extends SortableWidget {

    public $viewFile = '_docViewerProfileWidget';

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}{editButton}</div>{widgetContents}';

    private static $_JSONPropertiesStructure;

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'docId' => self::getJSONProperty (
                        $this->profile, 'docId', $this->widgetType),
                    'height' => self::getJSONProperty (
                        $this->profile, 'height', $this->widgetType),
                )
            );
        }
        return $this->_viewFileParams;
    } 

    public function renderEditButton () {
        $themeUrl = Yii::app()->theme->getBaseUrl();
        echo "<a href='#' class='widget-edit-button right x2-icon-button' style='display:none;'>".
            CHtml::image(
                $themeUrl.'/images/icons/Edit.png', Yii::t('app', 'Edit Document'),
                array ('title' => Yii::t('app', 'Edit Document'))).
            "</a>";
    }

    public function getSettingsMenuContent () {
        $htmlStr = '<div class="widget-settings-menu-content" style="display:none;">';
        $htmlStr .= '<ul><li class="select-a-document-button">'.
            Yii::t('profile', 'Select a Document').'</li></ul>';
        $htmlStr .= '</div>';
        $htmlStr .= '<div id="select-a-document-dialog" style="display: none;">'.
            '<p>'.Yii::t('profile', 'Enter the name of a Doc:').'</p>'.
            '<input class="selected-doc">'.
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
                    'docId' => '',  // id of the doc record to be displayed
                    'label' => Yii::t('app', 'Doc Viewer'),
                    'height' => '200',
                    'hidden' => true
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * overrides parent method. A sub prototype of SortableWidget.js is instantiated.
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $docId = self::getJSONProperty ($this->profile, 'docId', $this->widgetType);
            if ($docId !== '') {
                $doc = Docs::model ()->findByPk ($docId);
            } else {
                $docId = '\'\'';
            }
            if (isset ($doc)) {
                $canEdit = $doc->checkEditPermission () ? 1 : 0;
            } else {
                $canEdit = 0;
            }
            $widgetClass = get_called_class ();
            $this->_setupScript = "
                $(function () {
                    x2.".$widgetClass." = new DocViewerProfileWidget ({
                        widgetClass: '".$widgetClass."',
                        setPropertyUrl: '".Yii::app()->controller->createUrl (
                            '/profile/setWidgetSetting')."',
                        cssSelectorPrefix: '".$this->widgetType."',
                        widgetType: '".$this->widgetType."',
                        translations: {
                            dialogTitle: '".addslashes (Yii::t('profile', 'Select a Doc'))."',
                            closeButton: '".addslashes (Yii::t('profile', 'Close'))."',
                            selectButton: '".addslashes (Yii::t('profile', 'Select'))."',
                            docError: '".
                                addslashes (Yii::t('profile', 'Please select an existing Doc'))."'
                        },
                        getItemsUrl: '".Yii::app()->createUrl ("/docs/docs/getItems")."',
                        getDocUrl: '".Yii::app()->createUrl("/docs/docs/getItem")."',
                        enableResizing: true,
                        editDocUrl: '".
                            Yii::app()->controller->createAbsoluteUrl ('/docs/docs/update')."',
                        docId: ".$docId.",
                        canEdit: ".$canEdit.",
                        checkEditPermissionUrl: '".
                            Yii::app()->controller->createUrl (
                                "/docs/docs/ajaxCheckEditPermission")."'
                    });
                });
            ";
        }
        return $this->_setupScript;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'DocViewerProfileWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/sortableWidgets/DocViewerProfileWidget.js',
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
                    'docViewerProfileWidgetCss' => "
                        #".get_called_class()."-widget-content-container {
                            padding-bottom: 1px;
                        }

                        #select-a-document-dialog p {
                            display: inline;
                            margin-right: 5px;
                        }

                        .widget-edit-button {
                            margin-right: 10px;
                            margin-top: 3px;
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
