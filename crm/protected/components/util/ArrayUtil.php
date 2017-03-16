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
 * ********************************************************************************/

/**
 * Standalone class with miscellaneous array functions
 * 
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>, Derek Mueller <derek@x2engine.com>
 */
class ArrayUtil {

	/**
	 * Given two associative arrays, returns an array with the same set of keys
	 * as the first, but with key/value pairs from the second if they are present.
	 * Any keys in the second and not in the first will be ignored/dropped.
	 *
	 * @param array $expectedFields The array with key => default value pairs
	 * @param array $currentFields The array to copy values from
	 * @return array
	 */
	public static function normalizeToArray($expectedFields, $currentFields){
		// Expected keys: defined in expectedFields
		$expKeys = array_keys($expectedFields);
		// Current keys: in the array to compare against
		$curKeys = array_keys($currentFields);
		// Keys to save: both already present in the current fields and defined in the expected 
        // fields
		$savKeys = array_intersect($expKeys, $curKeys);
		// New keys: that are not present in the current fields but defined in the expected fields
		$newKeys = array_diff($expKeys, $curKeys);
		// The array to return, with normalized data:
		$fields = array();

		// Use existing values
		foreach($savKeys as $fieldName)
			$fields[$fieldName] = $currentFields[$fieldName];
		// Use default values as defined in the expected fields
		foreach($newKeys as $fieldName)
			$fields[$fieldName] = $expectedFields[$fieldName];

		return $fields;
	}

    /**
     * A recursive version of normalizeToArray () which optionally maintains order of current 
     * fields. 
     *
	 * @param array $expectedFields The array with key => default value pairs
	 * @param array $currentFields The array to copy values from
	 * @return array
     */
	public static function normalizeToArrayR ($expectedFields, $currentFields,$maintainOrder=true) {
        $fields = array ();

        /* 
        Use values in current fields if they are present, otherwise use default values in
        expected fields. If the default value is an array, apply array normalization 
        recursively.
        */
        foreach ($expectedFields as $key => $val) {
            if (is_array ($val) && isset ($currentFields[$key]) && 
                is_array ($currentFields[$key])) {

                $fields[$key] = self::normalizeToArrayR (
                    $expectedFields[$key], $currentFields[$key]);
            } else if (isset ($currentFields[$key])) {
                $fields[$key] = $currentFields[$key];
            } else {
                $fields[$key] = $expectedFields[$key];
            }
        }

        if ($maintainOrder) {
            /*
            Maintain array ordering of current fields
            */
            $orderedFields = array ();
            foreach ($currentFields as $key => $val) {
                if (in_array ($key, array_keys ($fields))) {
                    $orderedFields[$key] = $fields[$key];
                    unset ($fields[$key]);
                }
            }

            /* 
            Add fields not specified in currentFields. These fields can't be sorted so they are 
            simply appended.
            */
            foreach ($fields as $key => $val) {
                $orderedFields[$key] = $fields[$key];
            }

            $fields = $orderedFields;
        }

        return $fields;
    }

    /**
     * Determines whether a given array is associative
     *
     * @param array $array The array for which the check is made
     * @return bool True if $array is associative, false otherwise
     */
    public static function is_assoc ($array) {
        $keys = array_keys ($array);
        $type;
        foreach ($keys as $key) {
            if (gettype ($key) === 'string') {
                return true;
            }
        }
        return false;
    }


    /**
     * Similar to array_search but recursive, doesn't return needle of there's only one match, and
     * allows for regex searching.
     *
     * @param string $find regex to search on
     * @param array $in_array an array to search in
     * @param array $keys_found keys whose corresponding values match the regex
     * @return type an array of keys if $in_array is valid, or false if not.
     */
    public static function arraySearchPreg($find, $in_array, $keys_found = array()) {
        if (is_array($in_array)) {
            foreach ($in_array as $key => $val) {
                if (is_array($val)) {
                    $keys_found = self::arraySearchPreg($find, $val, $keys_found);
                } else {
                    if (preg_match('/' . $find . '/', $val))
                        $keys_found[] = $key;
                }
            }
            return $keys_found;
        }
        return false;
    }

    /**
     * Retrieve the first entry from an associative array 
     * @param array $array
     */
    public static function assocArrayShift (&$array) {
        $keys = array_keys ($array); 
        return array ($keys[0] => array_shift ($array));
    }

    /**
     * @param array $array the array to sort
     * @param bool $sideEffects If true, this function sorts the array reference. Otherwise, 
     *  the array is copied before sorting
     * @return the sorted array
     */
    public static function sort (array &$array, $sideEffects=false) {
        if ($sideEffects) {
            sort ($array);
            return $array;
        } else {
            $newArray = $array;
            sort ($newArray);
            return $newArray;
        }
    }

    /**
     * Side effect free version of array_pop
     */
    public static function pop (array $array) {
        $newArray = $array;
        return array_pop ($newArray);
    }

    public static function transpose ($array) {
        $newArray = array ();
        $arraySize = count ($array);
        for ($i = 0; $i < $arraySize; $i++) {
            $val = $array[$i];
            if (is_array ($val)) {
                $valSize = count ($val);
                $j = 0; 
                foreach ($val as $key => $cellVal) {
                    $newArray[$j][] = $cellVal;
                    $j++;
                }
            } else {
                $newArray[0][] = $val;
            }
        }
        return $newArray;
    }

    /**
     * Like array_search but returns numeric index instead of key 
     */
    public static function numericIndexOf ($needle, $haystack, $strict=false) {
        $i = 0;
        foreach ($haystack as $elem) {
            if (!$strict && $elem == $needle || $strict && $elem === $needle) return $i;
            $i++;
        }
        return false;
    }

    public static function setAndTrue ($array, $val) {
        return isset ($array[$val]) && $array[$val];
    }

}

?>
