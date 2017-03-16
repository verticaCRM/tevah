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

Yii::import('application.components.ApplicationConfigBehavior');
Yii::import('application.tests.functional.pageCrawlers.VisitAllPagesTest');

/**
 * Visit all pages and assert there are no injection vulnerabilities present
 * @package application.tests.functional
 */
class VisitAllPagesWithInjections extends VisitAllPagesTest {

    /**
     * Crawl as admin to check all pages without restrictions
     * @var array
     */
    public $login = array(
        'username' => 'admin',
        'password' => 'admin',
    );

    /**
     * Manually inject the database
     */
    public function setup() {
        parent::setup();

        // Prepare DB with injection statements
        // See: https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet for more
        // example injection strings. This will be more useful as we test bypassing filters
        $injectionStrings = array(
            '<h1 class="TESTX2INJECTION">{XSS}</h1>',
        );

        // TODO most of these tables are ignored due to constraints, it'd be nice to find a
        // workaround so that these can be tested for injection vulns
        $ignoreTables = array(
            'x2_action_meta_data', 'x2_auth_assignment', 'x2_auth_item_child', 'x2_auth_cache',
            'x2_auth_item', 'x2_cron_events', 'x2_chart_settings', 'x2_email_reports',
            'x2_credentials_default', 'x2_gallery_photo', 'x2_fields', 'x2_forwarded_email_patterns',
            'x2_gallery_to_model', 'x2_list_criteria', 'x2_list_items', 'x2_trigger_logs',
            'x2_password_reset', 'x2_timezones', 'x2_workflow_stages', 'x2_sessions', 'x2_profile',
        );
        $ignoreFields = array(
            'nameId', 'actionDescription', 'actionId', 'existingProducts', 'products',
            'masterId', 'username', 'parameters',
        );

        // Prepare a mapping of table names to an array of string-type attributes
        $tables = array();
        $tableSchema = Yii::app()->db->schema->getTables();
        foreach ($tableSchema as $table => $schema) {
            if (in_array($table, $ignoreTables))
                continue;
            $columns = array();
            foreach ($schema->columns as $column => $attrs) {
                if (!in_array($attrs->type, array('string'))
                        || in_array($column, $ignoreFields))
                    continue;
                $columns[$column] = $attrs->type;
            }
            $tables[$table] = $columns;
        }

        // Cycle over the possible injection strings when inserting
        $currentInjectionString = 0;
        foreach ($tables as $table => $fields) {
            $columns = array();
            if (array_key_exists('id', $fields))
                $columns['id'] = 666;

            foreach ($fields as $field => $type) {
                $value = $injectionStrings[ $currentInjectionString ];
                $identifier = "XSS from $field in $table";
                $columns[$field] = str_replace('{XSS}', $identifier, $value);
                $currentInjectionString = ($currentInjectionString + 1) % count($injectionStrings);
            }

            if (!empty($table) && !empty($columns))
                Yii::app()->db->createCommand()->insert($table, $columns);
        }
    }

    /**
     * Visit all pages in the app to assert no JavaScript alerts are raised
     */
    public function testInjectionOnAllPages() {
        $injectionPages = array_merge(
            $this->allPages,
            $this->adminPages
        );

        // Process pages and set id to the 'injected' record
        foreach ($injectionPages as &$page) {
            if (preg_match('/\d+$/', $page))
                $page = preg_replace('/\d+$/', 666, $page);
        }

        $this->visitPages ($injectionPages, true);
    }

    /**
     * Override testPages() to skip
     */
    public function testPages () {
        $this->markTestSkipped('Skipping testPages(). This can be executed from the ordinary '.
            'VisitAllPagesTest class');
    }
}
