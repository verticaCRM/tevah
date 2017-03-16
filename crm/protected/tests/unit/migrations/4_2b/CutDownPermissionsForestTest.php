<?php
/***********************************************************************************
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
 **********************************************************************************/

class CutDownPermissionsForestTest extends X2DbTestCase {

    /**
     * Contains dump of database at 4.1.7 Platinum after adding custom roles called TestRole and
     * SuperTestRole. SuperTestRole is flagged as Admin
     */
    public $fixtures = array (
        'authItem' => array (':x2_auth_item', '.CutDownPermissionsForestTest'), 
        'fields' => array ('Fields', '.CutDownPermissionsForestTest'), 
        'dropdowns' => array ('Dropdowns', '.CutDownPermissionsForestTest'), 
        'authItemChild' => array (':x2_auth_item_child', '.CutDownPermissionsForestTest'), 
    );

    /**
     * Runs 4.2b migration scripts on auth tables with custom roles set up in version 4.1.7. 
     * Asserts that roles are restructured to match expectations of 4.2b permissions system.
     */
    public function testRestructuringOfCustomRole () {

         // Get all children of test role before migration scripts run 
        $testRoleAuthItemChildren = array_map (function ($row) {
            return $row['child'];
        }, Yii::app()->db->createCommand ("
            select * 
            from x2_auth_item_child
            where parent='TestRole'
        ")->queryAll ());
        print_r ($testRoleAuthItemChildren);

        // run first migration script
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
            'migrations/4.2b/1407436318-update-sql.php';
        $return_var;
        $output = array ();
        print_r (exec ($command, $return_var, $output));
        print_r ($return_var);
        print_r ($output);

        // run second migration script
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
            'migrations/4.2b/1406225725-cut-down-permissions-forest.php';
        $return_var;
        $output = array ();
        print_r (exec ($command, $return_var, $output));
        print_r ($return_var);
        print_r ($output);

         // Get all children of test role after migration scripts run
        $newTestRoleAuthItemChildren = array_map (function ($row) {
            return $row['child'];
        }, Yii::app()->db->createCommand ("
            select * 
            from x2_auth_item_child
            where parent='TestRole'
        ")->queryAll ());
        print_r ($newTestRoleAuthItemChildren);

        // ensure that permisions were added properly by migration scripts
        foreach ($testRoleAuthItemChildren as $child) {
            println ('asserting correctness of restructured permissions for '.$child);
            $module = preg_replace (
                '/(PrivateReadOnly|PrivateUpdate|PrivateFull|PrivateBasic|ReadOnly|Basic|Update|'.
                'Full|Admin)Access$/', '', $child);
            if (preg_match ('/Charts|Reports/', $child)) 
                continue;
            if (!preg_match ('/.*(((Private)?(Basic|Full|Update))|Admin)Access$/', $child)) 
                continue;

            $this->assertContains ($module.'ReadOnlyAccess', $newTestRoleAuthItemChildren);
            if (preg_match ('/.*(Private)?BasicAccess$/', $child)) {
                continue;
            }
            $this->assertContains ($module.'BasicAccess', $newTestRoleAuthItemChildren);
            if (preg_match ('/.*(Private)?UpdateAccess$/', $child)) {
                continue;
            }
            $this->assertContains ($module.'UpdateAccess', $newTestRoleAuthItemChildren);
            if (preg_match ('/.*(Private)?FullAccess$/', $child)) {
                continue;
            }
            $this->assertContains ($module.'FullAccess', $newTestRoleAuthItemChildren);
            $this->assertEquals (1, preg_match ('/.*AdminAccess$/', $child));
        }
    }

}


?>
