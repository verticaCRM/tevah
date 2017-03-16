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

class X2DbCriteria extends CDbCriteria {

    /**
     * Modified to allow comparison with empty string.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/  
     */
    public function compare(
        $column, $value, $partialMatch=false, $operator='AND', $escape=true, 
        /* x2modstart */$ignoreEmpty=true/* x2modend */)
    {
        if(is_array($value))
        {
            if($value===array())
                return $this;
            return $this->addInCondition($column,$value,$operator);
        }
        else
            $value="$value";

        if(preg_match('/^(?:\s*(<>|<=|>=|<|>|=))?(.*)$/',$value,$matches))
        {
            $value=$matches[2];
            $op=$matches[1];
        }
        else
            $op='';

        /* x2modstart */ 
        if($ignoreEmpty && $value==='')
            return $this;
        /* x2modend */ 

        if($partialMatch)
        {
            if($op==='')
                return $this->addSearchCondition($column,$value,$escape,$operator);
            if($op==='<>')
                return $this->addSearchCondition($column,$value,$escape,$operator,'NOT LIKE');
        }
        elseif($op==='')
            $op='=';

        $this->addCondition($column.$op.self::PARAM_PREFIX.self::$paramCount,$operator);
        $this->params[self::PARAM_PREFIX.self::$paramCount++]=$value;

        return $this;
    }
}

?>
