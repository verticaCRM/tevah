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

abstract class WidgetLayout extends JSONEmbeddedModel {

    /**
     * @var string $alias
     */
    protected $alias; 

	protected $_fields;

    /**
     * Ensures that each subarray in $currentFields corresponds to a JSON properties structure
     * definition defined in some SortableWidget subclass. 
     *
	 * @param array $expectedFields The array with key => default value pairs
	 * @param array $currentFields The array to copy values from
	 * @return array
     */
    private function normalizeToWidgetJSONPropertiesStructures ($expectedFields, $currentFields) {
        $fields = array ();
        foreach ($currentFields as $key => $val) {
            // widget class name can optionally be followed by a sequence of digits. This is
            // used for widget cloning
            $widgetClassName = preg_replace ("/_\w+$/", '', $key);
            if (is_array ($val) && isset ($currentFields[$key]) && 
                is_array ($expectedFields[$widgetClassName])) {

                // JSON property structure definitions can be nested 
                $fields[$key] = ArrayUtil::normalizeToArrayR (
                    $expectedFields[$widgetClassName], $currentFields[$key]);
            } 
        }

        foreach ($expectedFields as $widgetClassName => $val) {
            if (!$widgetClassName::$createByDefault) {
                continue;
            }

            if (!isset ($fields[$widgetClassName])) {
                $fields[$widgetClassName] = $expectedFields[$widgetClassName];
            }
        }

        return $fields;
    }
        

	/**
	 * Returns an array defining the expected structure of the JSON-bearing
	 * attribute 
	 * @return array
	 */
	public function fields() {
		if(!isset($this->_fields)) {
			$this->_fields = array();

            // get expected fields from contents of widget directory
            $widgetClasses = array_map (function ($file) {
                return preg_replace ('/\.php$/', '', $file);
            }, array_filter (scandir(Yii::getPathOfAlias($this->alias)), function ($file) {
                return preg_match ('/\.php$/', $file);
            }));

            $ordered = array ();

            // get JSON structure from widget class property
            $unordered = array ();
            foreach($widgetClasses as $widgetName) {
                if (method_exists ($widgetName, 'getJSONPropertiesStructure')) {
                    $unordered[$widgetName] = 
                        $widgetName::getJSONPropertiesStructure ();
                    if ($widgetName::$position !== null) {
                        $ordered[$widgetName] = $widgetName::$position;
                    }
                } 
            }
            asort ($ordered);
            $orderedFields = array ();
            foreach ($ordered as $widgetName => $position) {
                $orderedFields[$widgetName] = $unordered[$widgetName];
            }
            foreach (array_diff ($widgetClasses, array_keys ($ordered)) as $widgetName) {
                $orderedFields[$widgetName] = $unordered[$widgetName];
            }
            $this->_fields = $orderedFields;
		}
		return $this->_fields;
	}

    /**
     * Removes fields which have JSON properties structures (for the purposes of array 
     * normalization) but which should not be saved
     */
    private function removeExcludedFields (&$attribute) {
        // Templates Summary can be in saved json object but should not be added by default.
        // This is because templates summaries can be created but don't exist by default 
        $excludeList = array (
            'TemplatesGridViewProfileWidget',
            'TransactionalViewWidget',
            'RecordViewWidget',
        );
        $attribute = array_diff_key ($attribute, array_flip ($excludeList));
    }

	/**
     * Normalize attribute to properties array structures defined in widget classes
	 * @return string
	 */
    private $_attributes = null;
	public function setAttributes ($values, $safeOnly=true){
		$fields = $this->fields();
        $attribute = is_array ($values) ? 
		    $this->normalizeToWidgetJSONPropertiesStructures ($fields, $values) : $fields; 
        $this->removeExcludedFields ($attribute);
        $this->_attributes = $attribute;
	}

	/**
     * Normalize attribute to properties array structures defined in widget classes
	 * @return $attribute
	 */
    public function getAttributes ($names=null) {
		$fields = $this->fields();
        $exoAttr = $this->exoAttr;
		$attribute = $this->_attributes;
        $attribute = is_array ($attribute) ? 
		    $this->normalizeToWidgetJSONPropertiesStructures ($fields, $attribute) : $fields; 
        $this->removeExcludedFields ($attribute);
		return $attribute;
    }

}

?>
