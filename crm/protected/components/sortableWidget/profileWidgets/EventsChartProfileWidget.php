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

 Yii::import ('application.components.sortableWidget.ChartWidget');

/**
 * @package application.components
 */
class EventsChartProfileWidget extends ChartWidget {

    public $canBeDeleted = true;

    public $defaultTitle = 'Events';

    public $relabelingEnabled = true;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{chartSubtypeSelector}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    public $chartType = 'eventsChart';

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Events',
                    'chartSettings' => array (
                        'startDate' => null,
                        'endDate' => null, 
                        'dateRange' => null,
                        'dateRangeType' => null,
                        'binSize' => null,
                        'firstMetric' => null, 
                        'showRelationships' => null,
                        'chartSetting' => null,
                        'usersFilter' => null,
                        'socialSubtypesFilter' => null,
                        'visibilityFilter' => null,
                    ),
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

	/**
	 * Retrieves all events between start and end timestamp. Query results are used to
	 * populate the activity feed chart.
	 */
	public static function getChartData ($startTimestamp, $endTimestamp){
        $command = Yii::app()->db->createCommand()
                ->select(
                        'type, subtype, visibility, user,'.
						'timestamp, COUNT(type) AS count,'.
                        'YEAR(FROM_UNIXTIME(timestamp)) AS year,'.
                        'MONTH(FROM_UNIXTIME(timestamp)) AS month,'.
                        'WEEK(FROM_UNIXTIME(timestamp)) AS week,'.
                        'DAY(FROM_UNIXTIME(timestamp)) AS day,'.
                        'HOUR(from_unixtime(timestamp)) as hour')
                ->from('x2_events');
        $command->where(
                'timestamp BETWEEN :startTimestamp AND :endTimestamp', 
				array('startTimestamp' => $startTimestamp, 'endTimestamp' => $endTimestamp));
        $events = $command->group(
                        'HOUR(FROM_UNIXTIME(timestamp)),'.
                        'DAY(FROM_UNIXTIME(timestamp)),'.
                        'WEEK(FROM_UNIXTIME(timestamp)),'.
                        'MONTH(FROM_UNIXTIME(timestamp)),'.
                        'YEAR(FROM_UNIXTIME(timestamp)),'.
                        'timestamp, type, subtype, visibility, user')
                ->order('year DESC, month DESC, week DESC, day DESC, hour desc')
                ->queryAll();
		return $events;
	}


    /**
     * Returns a data provider containing chart settings records with the specified type
     */
	public static function getChartSettingsProvider () {
        $chartSettingsDataProvider = new CActiveDataProvider('ChartSetting', array(
            'criteria' => array(
                'condition' => 
                    'userId='.Yii::app()->user->id.' AND '.
                    'chartType="eventsChart"',
                'order' => 'name ASC'
            )
        ));
		return $chartSettingsDataProvider;
	}

    /**
     * Instantiates a subclass of X2Chart, passing it a function which allows it to save widget
     * settings.
     */
    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $chartData = $this->getInitialChartData ();
            $userNames = User::getNames ();
            $socialSubtypes = json_decode (
                Dropdowns::model()->findByPk(113)->options,true);
            $visibilityFilters = array (
                '1'=>'Public',
                '0'=>'Private',
            );
            $this->migrateDateRange();
            $chartSettingsData = self::getChartSettingsProvider ($this->chartType)->data;
            $this->_setupScript = parent::getSetupScript ()."
                $(function () {
                    var chartUID = '$this->chartType$this->widgetUID';
                    x2[chartUID] = {};
                    x2[chartUID].chart = X2Chart.instantiateTemporarySubtype (
                        X2EventsChart, {
                        ".(isset ($chartData) ?
                            "chartData :".CJSON::encode ($chartData)."," : '')."
                        actionParams: ".CJSON::encode (array (
                            'widgetType' => get_called_class (),
                        )).",
                        socialSubtypes:".CJSON::encode (array_keys ($socialSubtypes)).",
                        visibilityTypes:".CJSON::encode (array_keys ($visibilityFilters)).",
                        eventTypes:".CJSON::encode (array_keys ($userNames)).", 
                        translations: ".CJSON::encode ($this->getTranslations ()).",
                        getChartDataActionName: 'getEventsBetween',
                        saveChartSetting: function (key, value, callback) {
                            this.lastChartSettings[key] = value;
                            x2.$widgetClass$this->widgetUID.setProperty (
                                'chartSettings', this.lastChartSettings, callback);
                        },
                        suppressDateRangeSelector: false,
                        suppressChartSettings: false,
                        lastChartSettings: ".CJSON::encode ($this->getChartSettings ()).",
                        widgetUID: '$this->widgetUID',
                        chartType: '$this->chartType',
                        chartSubtype: '".self::getJSONProperty (
                            $this->profile, 'chartSubtype', $this->widgetType, $this->widgetUID)."',
                        chartSettings: ".CJSON::encode (
                            count ($chartSettingsData) ? array_combine (
                                array_map (function ($setting) {
                                    return $setting->name;
                                }, $chartSettingsData),
                                $chartSettingsData) : array ())."
                    });
                    $(document).trigger ('$this->chartType' + 'Ready');
                });
            ";
        }
        return $this->_setupScript;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'userNames' => User::getNames (),
                    'socialSubtypes' => json_decode (
			            Dropdowns::model()->findByPk(113)->options,true),
                    'visibilityFilters' => array (
                        '1'=>'Public',
                        '0'=>'Private',
                    ),
			        'chartSettingsDataProvider' => self::getChartSettingsProvider (
                        $this->chartType),
                    'suppressChartSettings' => false,
                    'metricTypes' => array (
                        'any'=>Yii::t('app', 'All Events'),
                        'notif'=>Yii::t('app', 'Notifications'),
                        'feed'=>Yii::t('app', 'Feed Events'),
                        'comment'=>Yii::t('app', 'Comments'),
                        'record_create'=>Yii::t('app', 'Records Created'),
                        'record_deleted'=>Yii::t('app', 'Records Deleted'),
                        'weblead_create'=>Yii::t('app', 'Webleads Created'),
                        'workflow_start'=>Yii::t('app', '{Process} Started', array(
                            '{Process}' => Modules::displayName(false, 'Workflow')
                        )),
                        'workflow_complete'=>Yii::t('app', '{Process} Complete', array(
                            '{Process}' => Modules::displayName(false, 'Workflow')
                        )),
                        'workflow_revert'=>Yii::t('app', '{Process} Reverted', array(
                            '{Process}' => Modules::displayName(false, 'Workflow')
                        )),
                        'email_sent'=>Yii::t('app', 'Emails Sent'),
                        'email_opened'=>Yii::t('app', 'Emails Opened'),
                        'web_activity'=>Yii::t('app', 'Web Activity'),
                        'case_escalated'=>Yii::t('app', 'Cases Escalated'),
                        'calendar_event'=>Yii::t('app', '{Calendar} Events', array(
                            '{Calendar}' => Modules::displayName(false, 'Calendar')
                        )),
                        'action_reminder'=>Yii::t('app', '{Action} Reminders', array(
                            '{Action}' => Modules::displayName(false, 'Actions')
                        )),
                        'action_complete'=>Yii::t('app', '{Actions} Completed', array(
                            '{Actions}' => Modules::displayName(true, 'Actions')
                        )),
                        'doc_update'=>Yii::t('app', 'Doc Updates'),
                        'email_from'=>Yii::t('app', 'Email Received'),
                        'voip_calls'=>Yii::t('app', 'VOIP Calls'),
                        'media'=>Yii::t('app', '{Media}', array(
                            '{Media}' => Modules::displayName(true, 'Media')
                        ))
                    ),
                    'chartType' => 'eventsChart',
                    'widgetUID' => $this->widgetUID,
                )
            );
        }
        return $this->_viewFileParams;
    } 

    /**
     * overrides parent method. Adds JS file necessary to run the setup script.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'EventsChartProfileWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/X2Chart/X2EventsChart.js',
                        ),
                        'depends' => array ('ChartWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (
                parent::getTranslations (),
                array (
                    'metric1Label' => Yii::t('app', 'metric(s) selected'),
                    'user(s) selected' => Yii::t('app', 'user(s) selected'),
                    'event subtype(s) selected' => Yii::t('app', 'event subtype(s) selected'),
                    'visibility setting(s) selected' => Yii::t(
                        'app', 'visibility setting(s) selected'),
                )
            );
        }
        return $this->_translations;
    }

	/**
	 * Collect initial chart data so the client doesn't have to request it via ajax .
	 * Decreases time before chart render after page is loaded.
	 */
	protected function getInitialChartData () {
		/* 
		Chart data only needs to be sent with initial response if chart was
		left open.
		*/
		if (self::getJSONProperty (
            $this->profile, 'minimized', $this->widgetType, $this->widgetUID)) {

			$tsDict = $this->getStartEndTimestamp (time () - self::SECPERWEEK, time ());
			$startDate = $tsDict[0];
			$endDate = $tsDict[1];
			$events = self::getChartData ($startDate, $endDate);
			return $events;
		}
	}

    /**
     * Temporary Migration from the old chart settings to the new. 
     */
    public function migrateDateRange () {
        $settings = $this->getWidgetProperty('chartSettings');

        if (!isset($settings['dateRange'])) return;
        $dateRange = $settings['dateRange'];

        $mapping = array (
            "Custom" => array('custom', 'day'),
            "Today" => array('this', 'day'),
            "Yesterday" => array('last', 'day'),
            "This Week" => array('this', 'week'),
            "Last Week" => array("last", "week"),
            "This Month" => array("this", "month"),
            "Last Month" => array("last", "month"),
            "Last Three Months" => array("last", "quarter"),
            "Last Six Months" => array("last", "year"),
            "This Year" => array("this", "year"),
            "Last Year" => array("last", "year")
        );

        if (array_key_exists($dateRange, $mapping)) {
            $settings['dateRangeType'] = $mapping[$dateRange][0];
            $settings['dateRange'] = $mapping[$dateRange][1];
            $this->setWidgetProperty ('chartSettings', $settings);
        }

    }

}
?>
