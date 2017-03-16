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

/* @edition:pro */

// Each comment line goes with the shortcode that comes immediately after it.

return array(
    /* Action Description is a weird case where it won't be matched by 'hasAttribute'
       and must be provided manually so that we can access the property */
    'actionDescription'=>'
        if($model instanceof Actions){
            return $model->actionDescription;
        }else{
            return null;
        }',

    'actionAssociatedRecord' => '
        if ($model instanceof Actions) {
            return X2Model::getModelOfTypeWithId (
                $model->associationType, $model->associationId, true);
        } else {
            return null;
        }
    ',

    /* Generate a link to the record */
    'link'=>'
        if($model->asa("X2LinkableBehavior")){
            if($model instanceof Actions){
                return $model->getLink(30, false);
            }else{
                return $model->getLink();
            }
        }else{
            return null;
        }',

    /* Return the current date, properly formatted */
    'date'=>'return Formatter::formatDate(time(), "long", false);',

    /* Return the current time, properly formatted */
    'time'=>'return Formatter::formatTime(time());',

    'timestamp' => 'return time ();',

    /* Return a combination date/time string, properly formatted. */
    'dateTime'=>'return Formatter::formatLongDateTime(time());',

    /* Return the Profile record of the current user */
    'user'=>'return X2Model::model("Profile")->findByAttributes(array("username"=>Yii::app()->user->getName()));',

    /* Creates an unsubscribe link, used by Marketing emails */
    'unsub'=>'return \'<a href="\'.Yii::app()->createAbsoluteUrl(\'/marketing/marketing/click\',array(\'uid\' => "", \'type\' => \'unsub\', \'email\' => $model->email)).\'">\'.Yii::t(\'marketing\', \'unsubscribe\').\'</a>\';',

    /* Validate that a phone number only contains digits. */
    'validphone'=>'
        if($model->hasAttribute("phone")){
            if(preg_match(\'/^[0-9\-\(\)]+$/\',$model->phone)){
                return $model->phone;
            }
        }
        return null;',
);
?>
