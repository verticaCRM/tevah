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

/**
 * Class for displaying tags on a record.
 * 
 * @package application.components.sortableWidget
 */
class InlineTagsWidget extends SortableWidget {

    /**
     * @var CActiveRecord Model whose tags are displayed in this widget
     */
	public $model;

    public $viewFile = '_inlineTagsWidget';

    private static $_JSONPropertiesStructure;

    private $_tags;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Tags',
                    'hidden' => false,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'InlineTagsWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/X2Tags/TagContainer.js',
                            'js/X2Tags/TagCreationContainer.js',
                            'js/X2Tags/InlineTagsContainer.js',
                        ),
                        'depends' => array ('auxlib'),
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * @return array Tags associated with $this->model 
     */
    public function getTags () {
        if (!isset ($this->_tags)) {
            $this->_tags = Yii::app()->db->createCommand()
                ->select('COUNT(*) AS count, tag')
                ->from('x2_tags')
                ->where('type=:type AND itemId=:itemId',
                    array(
                        ':type'=>get_class($this->model),
                        ':itemId'=>$this->model->id
                    ))
                ->group('tag')
                ->order('count DESC')
                ->limit(20)
                ->queryAll();
        }
        return $this->_tags;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'model' => $this->model,
                    'tags' => $this->tags,
                )
            );
        }
        return $this->_viewFileParams;
    } 

    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array_merge (
                parent::getCss (),
                array (
                'inlineTagsWidgetCSS' => "
                    #x2-tags-container {
                        padding: 7px;
                    }
                ")
            );
        }
        return $this->_css;
    }


}
