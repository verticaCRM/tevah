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

Yii::import('application.components.util.ArrayUtil');
Yii::import('application.components.sortableWidget.*');
Yii::import('application.components.sortableWidget.dataWidgets.*');

/**
 * Allows nested JSON structures with default values to be declared. The JSON structures must be 
 * specified as the property of some class. To use this class, the structure of the
 * transformAttributes field of the behavior configuration array should looks as follows:
 *
 *  'transformAttributes' => array (
 *     <widget layout name> => array (
 *          <widget class name>
 *      )
 *  )
 * 
 * The class called <widget class name> must have a static method called 
 * getJSONPropertiesStructure () which returns the structure of the JSON field.
 * 
 * @package application.components
 */
class WidgetLayoutJSONFieldsBehavior extends NormalizedJSONFieldsBehavior {

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

        foreach ($expectedFields as $key => $val) {
            if (!isset ($fields[$key])) {
                $fields[$key] = $expectedFields[$key];
            }
        }

        return $fields;
    }
        

	/**
	 * Returns an array defining the expected structure of the JSON-bearing
	 * attribute specified by $name.
	 *
	 * @param $name
	 * @return type
	 */
	public function fields($name) {
		if(!isset($this->_fields)) {
			$this->_fields = array();
			foreach($this->transformAttributes as $attr => $alias) {
                $this->_fields[$attr] = array ();

                // get expected fields from contents of widget directory
                $widgetClasses = array_map (function ($file) {
                    return preg_replace ('/\.php$/', '', $file);
                }, array_filter (scandir(Yii::getPathOfAlias($alias)), function ($file) {
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
                $this->_fields[$attr] = $orderedFields;
            }
		}
		return $this->_fields[$name];
	}

    /**
     * Removes fields which have JSON properties structures (for the purposes of array 
     * normalization) but which should not be saved
     */
    private function removeExcludedFields (&$attribute) {
        // Templates Summary can be in saved json object but should not be added by default.
        // This is because templates summaries can be created but don't exist by default 
        $excludeList = array ('TemplatesGridViewProfileWidget');
        $attribute = array_diff_key ($attribute, array_flip ($excludeList));
    }

	/**
	 * Normalizes the attribute array to the structure defined in {@link fields}
	 * and then JSON-encodes it to prepare it for saving. Unlike in NormalizedJSONFieldsBehavior, 
     * array normalization is performed recursively on array elements.
     *
	 * @param type $name
	 * @return type
	 */
	public function packAttribute($name){
		$fields = $this->fields($name);
		$attribute = $this->getOwner()->$name;
        $attribute = is_array ($attribute) ? 
		    $this->normalizeToWidgetJSONPropertiesStructures ($fields, $attribute) : $fields; 
        $this->removeExcludedFields ($attribute);
		return CJSON::encode ($attribute);
	}

	/**
	 * JSON-decodes the value stored in the database column for the attribute,
	 * and then normalizes it to the structure defined in {@link fields}
	 * Unlike in NormalizedJSONFieldsBehavior, array normalization is performed recursively on 
     * array elements.
     *
	 * @param string $name The attribute to be unpacked
	 * @return type
	 */
	public function unpackAttribute($name){
		$fields = $this->fields($name);
		$attribute = CJSON::decode ($this->getOwner()->$name);
        $attribute = is_array ($attribute) ? 
		    $this->normalizeToWidgetJSONPropertiesStructures ($fields, $attribute) : $fields; 
        $this->removeExcludedFields ($attribute);
		return $attribute;
	}
}

?>
