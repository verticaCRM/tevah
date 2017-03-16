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

/**
 * Convert pre-5.0 reports in x2_reports to post-5.0 reports in x2_reports_2
 */

$migrateReports = function () {

    // range parsing function copied from 4.3
    $parseDateRange = function ($range) {
        $dateRange = array ();

        switch ($range) {
            case 'thisWeek':
                $dateRange['start'] = strtotime('mon this week'); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'thisMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n'), 1); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'lastWeek':
                $dateRange['start'] = strtotime('mon last week'); // first of last month
                $dateRange['end'] = strtotime('mon this week') - 1;  // first of this month
                break;
            case 'lastMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n') - 1, 1); // first of last month
                $dateRange['end'] = mktime(0, 0, 0, date('n'), 1) - 1;  // first of this month
                break;
            case 'thisYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1);  // first of the year
                $dateRange['end'] = time(); // now
                break;
            case 'lastYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1, date('Y') - 1);  // first of last year
                $dateRange['end'] = mktime(0, 0, 0, 1, 1, date('Y')) - 1;   // first of this year
                break;
            case 'all':
                $dateRange['start'] = 0;        // every record
                $dateRange['end'] = time();
                break;
            default: 
                return false;
        }
        return $dateRange;
    };

    $reports = Yii::app()->db->createCommand ("
        select *
        from x2_reports
    ")->queryAll ();

    foreach ($reports as $rep) {
        print_r ($rep);
        if (!in_array ($rep['type'], array ('grid', 'deal'), 1)) {
            print_r ('invalid type');
            continue;
        }
        if ($rep['type'] === 'grid') {
            $settings = array ();
            $parameters = json_decode ($rep['parameters'], true);

            if (!empty ($parameters['module'])) {
                $settings['primaryModelType'] = ucfirst ($parameters['module']);
            } else {
                $settings['primaryModelType'] = 'Contacts';
            }

            if (!empty ($rep['field1'])) {
                $settings['rowField'] = $rep['field1'];
            } else {
                print_r ('invalid field1'."\n");
                continue; // invalid saved report
            }

            if (!empty ($rep['field2'])) {
                $settings['columnField'] = $rep['field2'];
            } else {
                print_r ('invalid field2'."\n");
                continue; // invalid saved report
            }

            if (!empty ($rep['cellType'])) {
                $settings['cellDataType'] = $rep['cellType'];
            } else {
                $settings['cellDataType'] = 'count';
            }

            if ($settings['cellDataType'] !== 'count') {
                if (!empty ($rep['cellData'])) {
                    $settings['cellDataField'] = $rep['cellData'];
                } else {
                    print_r ('invalid cellData'."\n");
                    continue; // invalid saved report
                }
            }

            if (empty ($rep['dateRange'])) {
                print_r ('invalid dateRange'."\n");
                continue; // invalid saved report
            } else if ($rep['dateRange'] === 'custom') {
                if (empty ($rep['start']) || empty ($rep['end'])) {
                    print_r ('invalid start or end'."\n");
                    continue; // invalid saved report
                }
                $start = $rep['start'];
                $end = $rep['end'];
            } else { // $rep['dateRange'] !== 'custom'
                $dateRange = $parseDateRange ($rep['dateRange']);
                if (!$dateRange) {
                    print_r ('invalid date range'."\n");
                    continue; // invalid saved report
                }
                $start = $dateRange['start'];
                $end = $dateRange['end'];
            }

            $settings['allFilters'] = array (
                array (
                    'name' => 'createDate',
                    'operator' => '>=',
                    'value' => $start,
                ),
                array (
                    'name' => 'createDate',
                    'operator' => '<=',
                    'value' => $end,
                ),
            );

            Yii::app()->db->createCommand ("
                INSERT INTO x2_reports_2 
                    (`createDate`, `createdBy`, `name`, `settings`, `version`, `type`)
                    VALUES (
                    :createDate, :createdBy, :name, :settings, :version, :type)
            ")->execute (array (
                ':createDate' => $rep['createDate'],
                ':createdBy' => $rep['createdBy'],
                ':name' => preg_replace ('/s$/', '', $settings['primaryModelType']).' Grid',
                ':settings' => json_encode ($settings),
                ':version' => '5.0',
                ':type' => 'grid',
            ));
        } else { // $rep['type'] === 'deal'
            $settings = array ();
            $parameters = json_decode ($rep['parameters'], true);
            if (!empty ($parameters['model'])) {
                if (!preg_match ('/opportunity|contacts|accounts/i', $parameters['model'])) {
                    print_r ('invalid model'."\n");
                    continue;
                }
                $settings['primaryModelType'] = ucfirst ($parameters['model']);
            } else {
                $settings['primaryModelType'] = 'Contacts';
            }

            if (empty ($rep['dateRange'])) {
                print_r ('invalid date range'."\n");
                continue; // invalid saved report
            } else if ($rep['dateRange'] === 'custom') {
                if (empty ($rep['start']) || empty ($rep['end'])) {
                    print_r ('invalid start or end'."\n");
                    continue; // invalid saved report
                }
                $start = $rep['start'];
                $end = $rep['end'];
            } else { // $rep['dateRange'] !== 'custom'
                $dateRange = $parseDateRange ($rep['dateRange']);
                if (!$dateRange) {
                    print_r ('invalid dateRange'."\n");
                    continue; // invalid saved report
                }
                $start = $dateRange['start'];
                $end = $dateRange['end'];
            }

            if (in_array ($settings['primaryModelType'], array ('Contacts', 'Accounts'), 1)) {
                $settings['allFilters'] = array (
                    array (
                        'name' => 'closedate',
                        'operator' => '>=',
                        'value' => $start,
                    ),
                    array (
                        'name' => 'closedate',
                        'operator' => '<=',
                        'value' => $end,
                    ),
                );
            } else { // $settings['primaryModelType'] === 'Opportunity'
                $settings['allFilters'] = array (
                    array (
                        'name' => 'expectedCloseDate',
                        'operator' => '>=',
                        'value' => $start,
                    ),
                    array (
                        'name' => 'expectedCloseDate',
                        'operator' => '<=',
                        'value' => $end,
                    ),
                );
            }
            if (!empty ($parameters['strict']) && $parameters['strict']) {
                $settings['allFilters'] = array_merge ($settings['allFilters'], array (
                    array (
                        'name' => 'createDate',
                        'operator' => '>=',
                        'value' => $start,
                    ),
                    array (
                        'name' => 'createDate',
                        'operator' => '<=',
                        'value' => $end,
                    ),
                ));
            }
            
            if ($settings['primaryModelType'] === 'Contacts') {
                $columns = array (
                    "name","assignedTo","company","leadscore","closedate","dealvalue","dealstatus",
                    "rating","lastUpdated");
            } elseif ($settings['primaryModelType'] === 'Accounts') {
                $columns = array (
                    "name", "createDate", "assignedTo", "type", "employees", "annualRevenue", 
                    "website", "lastUpdated"
                );
            } else { // $settings['primaryModelType'] === 'Opportunitiy'
                $columns = array (
                    "name", "assignedTo", "accountName", "quoteAmount", "expectedCloseDate", 
                    "probability", "salesStage", "leadSource", "lastUpdated"
                );
            }
            $settings['columns'] = $columns;

            if (isset ($parameters[$settings['primaryModelType']])) {

                $attributes = $parameters[$settings['primaryModelType']];
                if (is_array ($attributes)) {
                    foreach ($attributes as $name => $val) {
                        if (!empty ($val)) {
                            $settings['allFilters'][] = array (
                                'name' => $name,
                                'operator' => '=',
                                'value' => $val,
                            );
                        }
                    }
                }
            }

            Yii::app()->db->createCommand ("
                INSERT INTO x2_reports_2 (
                    `createDate`, `createdBy`, `name`, `settings`, `version`, `type`)
                    VALUES (
                    :createDate, :createdBy, :name, :settings, :version, :type)
            ")->execute (array (
                ':createDate' => $rep['createDate'],
                ':createdBy' => $rep['createdBy'],
                ':name' => preg_replace ('/s$/', '', $settings['primaryModelType']).' Deals',
                ':settings' => json_encode ($settings),
                ':version' => '5.0',
                ':type' => 'rowsAndColumns',
            ));
        }

        print ('insert'); 
    }

    // insert default reports
    Yii::app()->db->createCommand ("
    INSERT INTO `x2_reports_2` (`id`, `createdBy`, `lastUpdated`, `name`, `settings`, `version`, `type`) VALUES (1, 'admin',1414093271,'Services Report','{\"columns\":[\"name\",\"impact\",\"status\",\"assignedTo\",\"lastUpdated\",\"updatedBy\"],\"orderBy\":[],\"primaryModelType\":\"Services\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false}','4.3','rowsAndColumns'),(2, 'admin',1414093762,'Deal Report','{\"columns\":[\"name\",\"assignedTo\",\"company\",\"leadscore\",\"closedate\",\"dealvalue\",\"dealstatus\",\"rating\",\"lastUpdated\"],\"orderBy\":[],\"primaryModelType\":\"Contacts\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false}','5.0','rowsAndColumns');")->execute ();

};
$migrateReports ();

?>
