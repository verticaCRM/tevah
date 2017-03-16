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

class RowsAndColumnsReportSort extends CSort {

    /**
     * Names of attributes which are sorted and their sort direction
     * @var array $sortOrders
     */
    public $sortOrders; 

    public $multiSort = true; 

    /**
     * Allows sort links to be generated based on public sortOrders property, instead 
     * of GET parameters. {@link X2RowsAndColumnsReport} handles sorting, so sort order GET 
     * parameter is not set.
     */
    public function getPresetDirections () {
        $presetDirections = array ();
        foreach ($this->sortOrders as $attr => $direction) {
            $presetDirections[$attr] = $direction === 'desc' ? self::SORT_DESC : self::SORT_ASC;
        }
        return $presetDirections;
    }

	/**
	 * Generates a hyperlink that can be clicked to cause sorting.
	 * @param string $attribute the attribute name. This must be the actual attribute name, not 
     * alias.
	 * If it is an attribute of a related AR object, the name should be prefixed with
	 * the relation name (e.g. 'author.name', where 'author' is the relation name).
	 * @param string $label the link label. If null, the label will be determined according
	 * to the attribute (see {@link resolveLabel}).
	 * @param array $htmlOptions additional HTML attributes for the hyperlink tag
	 * @return string the generated hyperlink
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function link($attribute,$label=null,$htmlOptions=array())
	{
		if($label===null)
			$label=$this->resolveLabel($attribute);
		if(($definition=$this->resolveAttribute($attribute))===false)
			return $label;
        /* x2modstart */ 
		$directions=$this->getPresetDirections();
        /* x2modend */ 
		if(isset($directions[$attribute]))
		{
			$class=$directions[$attribute] ? 'desc' : 'asc';
			if(isset($htmlOptions['class']))
				$htmlOptions['class'].=' '.$class;
			else
				$htmlOptions['class']=$class;
			$descending=!$directions[$attribute];
			unset($directions[$attribute]);
		}
		elseif(is_array($definition) && isset($definition['default']))
			$descending=$definition['default']==='desc';
		else
			$descending=false;

		if($this->multiSort) {
            /* x2modstart */ 
            // switched order of arguments so that new sort order comes last
			$directions=array_merge($directions, array($attribute=>$descending));
            /* x2modend */ 
		} else {
			$directions=array($attribute=>$descending);
        }

		$url=$this->createUrl(Yii::app()->getController(),$directions);

		return $this->createLink($attribute,$label,$url,$htmlOptions);
	}

    /**
     * Parses sort order formatted with separators property
     * @return array attributes to sort on and direction
     */
    public static function parseSortOrders ($sortOrder, $separators) {
        $sortOrders = explode ($separators[0], $sortOrder);
        $parsed = array ();
        foreach ($sortOrders as $order) {
            $pieces = explode ($separators[1], $order);
            if (count ($pieces) === 1) $pieces[] = 'asc';
            $parsed[$pieces[0]] = $pieces[1];
        }
        return $parsed;
    }

}

?>
