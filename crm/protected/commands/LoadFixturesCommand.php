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

Yii::import('application.components.util.*');
Yii::import('system.test.CTestCase');
Yii::import('system.test.CDbTestCase');
Yii::import('system.test.CWebTestCase');
Yii::import('application.tests.*');

/**
 * Allows you to quickly update database with changes to individual fixture files
 */
class LoadFixturesCommand extends CConsoleCommand {

    /**
     * Each alias should be of the form <modelClass>[.<fixture suffix>]?
     * @param array $fixtureAlias 
     */
    public function actionLoad (array $fixtureAlias=array (), array $testClassName=array ()) {
        /**/print ("Loading fixtures\n");
        
        if (!YII_UNIT_TESTING) {
            throw new CException ('YII_UNIT_TESTING must be set to true');
        }
        define('LOAD_FIXTURES', false);

        // build a dummy test class with the specified fixtures, then instantiate the class and
        // load the fixtures into the DB.
        eval ("class DummyTest extends X2DbTestCase {}");
        $fixtures = array ();
        foreach ($fixtureAlias as $alias) {
            $pieces = explode ('.', $alias);
            if (count ($pieces) === 2) { 
                $modelClass = $pieces[0];
                $alias = $pieces[1];
                $fixtures[$alias] = array ($modelClass, '.'.$alias);
            } else {
                $modelClass = $pieces[0];
                $fixtures[$alias] = $modelClass;
            }
        }
        foreach ($testClassName as $classPath) {
            Yii::import ($classPath);
            $className = preg_replace ('/^.*\./', '', $classPath);
            $testClass = new $className;
            $fixtures = array_merge ($fixtures, $testClass->fixtures);
        }
        print_r ($fixtures);

        $dummyTest = new DummyTest;
        $dummyTest->getFixtureManager ()->loadFixtures = true;
        $dummyTest->getFixtureManager ()->load ($fixtures);
    }

}

?>
