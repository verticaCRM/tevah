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

 Yii::import ('application.components.sortableWidget.GridViewWidget');

/**
 * @package application.components
 */
class ProfilesGridViewProfileWidget extends GridViewWidget {

    public $viewFile = '_gridViewLessProfileWidget';
    
    private static $_JSONPropertiesStructure;

    protected $_viewFileParams;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    protected function getModel () {
        if (!isset ($this->_model)) {
            $this->_model = new Profile ('search');
        }
        return $this->_model;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array ('label' => 'People')
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $resultsPerPage = self::getJSONProperty (
                $this->profile, 'resultsPerPage', $this->widgetType);
            $this->_dataProvider = $this->model->search (
                $resultsPerPage, get_called_class (), true);
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
                    'defaultGvSettings'=>array(
                        'isActive' => 65,
                        'fullName' => 125,
                        'tagLine' => 100,
                        'emailAddress' => 100,
                        'cellPhone' => 100,
                        'lastLogin' => 80,
                    ),
                    'template'=>
                        '<div class="page-title"><h2 class="grid-widget-title-bar-dummy-element">'.
                        '</h2>{buttons}{filterHint}'.
                        '{summary}{topPager}</div>{items}{pager}',
                    'modelAttrColumnNames'=>array (
                        'tagLine', 'username', 'officePhone', 'cellPhone', 'emailAddress', 
                        'googleId', 'isActive', 'leadRoutingAvailability',
                    ),
                    'specialColumns'=>array(
                        'fullName'=>array(
                            'name'=>'fullName',
                            'header'=>Yii::t('profile', 'Full Name'),
                            'value'=>'CHtml::link($data->user->fullName,array("view","id"=>$data->id))',
                            'type'=>'raw',
                        ),
                        'lastLogin'=>array(
                            'name'=>'lastLogin',
                            'header'=>Yii::t('profile', 'Last Login'),
                            'value'=>'($data->user->lastLogin == 0 ? "" : '.
                                'Yii::app()->dateFormatter->formatDateTime ('.
                                    '$data->user->lastLogin, "medium"))',
                            'type'=>'raw',
                        ),
                        'isActive'=>array(
                            'name'=>'isActive',
                            'header'=>Yii::t('profile', 'Active'),
                            'value'=>'"<span title=\''.
                                '".(Session::isOnline ($data->username) ? '.
                                 '"'.Yii::t('profile', 'Active User').'" : "'.
                                    Yii::t('profile', 'Inactive User').'")."\''.
                                ' class=\'".(Session::isOnline ($data->username) ? '.
                                '"active-indicator" : "inactive-indicator")."\'></span>"',
                            'type'=>'raw',
                        ),
                        'username' => array(
                            'name' => 'username',
                            'header' => Yii::t('profile','Username'),
                            'value' => 'CHtml::encode($data->user->alias)',
                            'type' => 'raw'
                        ),
                        'leadRoutingAvailability' => array(
                            'name' => 'leadRoutingAvailability',
                            'header' => Yii::t('profile','Lead Routing Availability'),
                            'value' => 'CHtml::encode($data->leadRoutingAvailability ? 
                                Yii::t("profile", "Available") :
                                Yii::t("profile", "Unavailable"))',
                            'type' => 'raw'
                        ),
                    ),
                    'enableControls'=>false,
                )
            );
        }
        return $this->_gridViewConfig;
    }

}
?>
