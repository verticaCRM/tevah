<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * 
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ApplicationConfigBehaviorTest extends X2TestCase {

    public function testContEd(){
        $oldEd = Yii::app()->edition;
        switch(Yii::app()->edition){
            case 'pla':
                foreach(array('pla', 'pro', 'opensource') as $ed){
                    $this->assertTrue(Yii::app()->contEd($ed));
                }
                break;
            case 'pro':
                $this->assertFalse(Yii::app()->contEd('pla'));
                foreach(array('pro', 'opensource') as $ed){
                    $this->assertTrue(Yii::app()->contEd($ed));
                }
                break;
            case 'opensource':
                $this->assertTrue(Yii::app()->contEd('opensource'));
                foreach(array('pro', 'pla') as $ed){
                    $this->assertFalse(Yii::app()->contEd($ed));
                }
        }
    }

    /* x2plastart */
    public function testGetEdition() {
        if(YII_DEBUG) {
            switch(PRO_VERSION) {
                case 1:
                    $this->assertEquals('pro',Yii::app()->edition,'Forced edition (debug), should be "pro"');
                    break;
                case 2:
                    $this->assertEquals('pla',Yii::app()->edition,'Forced edition (debug), should be "pla"');
                    break;
                default:
                    $this->assertEquals('opensource',Yii::app()->edition,'Forced edition (debug), should be "opensource"');
            }
        } else {
            $this->assertEquals('pla',Yii::app()->edition,'Automatically-determined; should be "pla" for the superset');
        }
    }
    /* x2plaend */
}

?>
