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

 Yii::import ('application.components.sortableWidget.ProfileGridViewWidget');

/**
 * @package application.components
 */
class ContactsGridViewProfileWidget extends ProfileGridViewWidget {

    public $canBeDeleted = true;

    public $defaultTitle = 'Contacts Summary';

    public $relabelingEnabled = true;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';
 
    private static $_JSONPropertiesStructure;

    protected $_viewFileParams;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    protected function getModel () {
        if (!isset ($this->_model)) {
            $this->_model = new Contacts ('search',
                $this->widgetKey,
                $this->getWidgetProperty ('dbPersistentGridSettings'));

            $this->afterGetModel ();
        }
        return $this->_model;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array ('label' => 'Contacts Summary')
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $resultsPerPage = self::getJSONProperty (
                $this->profile, 'resultsPerPage', $this->widgetType, $this->widgetUID);
            $this->_dataProvider = $this->model->searchAll (
                $resultsPerPage);
        }
        return $this->_dataProvider;
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'enableQtips' => true,
                    'qtipManager' => array (
                        'X2GridViewQtipManager',
                        'loadingText'=> addslashes(Yii::t('app','loading...')),
                        'qtipSelector' => ".contact-name"
                    ),
                    'moduleName' => 'Contacts',
                    'defaultGvSettings'=>array(
                        'gvCheckbox' => 30,
                        'name' => 125,
                        'email' => 165,
                        'leadSource' => 83,
                        'leadstatus' => 91,
                        'phone' => 107,
                    ),
                    'specialColumns'=>array(
                        'name'=>array(
                            'name'=>'name',
                            'header'=>Yii::t('contacts','Name'),
                            'value'=>'$data->link',
                            'type'=>'raw',
                        ),
                    ),
                    'massActions'=>array(
                        /* x2prostart */'MassDelete', 'MassTag', 'MassUpdateFields', 
                        /* x2proend */'MassAddToList', 'NewListFromSelection',
                    ),
                    'enableTags'=>true,
                )
            );
        }
        return $this->_gridViewConfig;
    }

}
?>
