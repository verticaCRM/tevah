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
/**
 * @package application.modules.calendar.models
 *
 * @deprecated
 * @todo Find out if this class is still actually used for anything. Delete it
 *  and anything else associated with it (i.e. the database table) if not.
 */
class X2Calendar extends CActiveRecord
{

	public $googleCalendarName;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @return Contacts the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_calendars';
	}
	
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'googleCalendar'=>Yii::t('calendar', 'Google Calendar'),
			'googleFeed'=>Yii::t('calendar', 'Google Feed'),
			'googleCalendarName' => Yii::t('calendar','Google Calendar Name'),
		);
	}
	
	public static function getNames() {
		$calendars = X2Calendar::model()->findAllByAttributes(array('googleCalendar'=>false));
		
		$names = array();
		foreach($calendars as $calendar)
			$names["{$calendar->id}"] = $calendar->name;
		
		return $names;
	}
	
	public static function getViewableUserCalendarNames() {
		$order = 'desc';
		$userArray = X2Model::model('User')->findAll();
		$names = array('Anyone' => 'Anyone');
		foreach ($userArray as $user) {
			if(in_array(Yii::app()->user->name, explode(',', $user->calendarViewPermission)) || 
				!$user->setCalendarPermissions || // user hasn't set up calendar permissions?
				Yii::app()->params->isAdmin || 
				Yii::app()->user->name == $user->username) {
				$first = $user->firstName;
				$last = $user->lastName;
				$userName = $user->username;
				$name = $first . ' ' . $last;
				$names[$userName] = $name;
			}
		}
		return $names;
	}
	
	public static function getEditableUserCalendarNames() {
		$order = 'desc';
		$userArray = X2Model::model('User')->findAll();
		$names = array('Anyone' => 'Anyone');
		foreach ($userArray as $user) {
			if(in_array(Yii::app()->user->name, explode(',', $user->calendarEditPermission)) || 
				Yii::app()->params->isAdmin || 
				Yii::app()->user->name == $user->username) {
				$first = $user->firstName;
				$last = $user->lastName;
				$userName = $user->username;
				$name = $first . ' ' . $last;
				$names[$userName] = $name;
			}
		}
		return $names;
	}
	
	public static function getViewableGroupCalendarNames() {
		
		$names = array();

		if(Yii::app()->params->isAdmin) { // admin sees all
			$groups = Yii::app()->db->createCommand()->select()->from('x2_groups')->queryAll();			
		} else {
			$groups = Yii::app()->db->createCommand()
                ->select('x2_groups.id, x2_groups.name')
                ->from('x2_group_to_user')
                ->join('x2_groups', 'groupId = x2_groups.id')
                ->where('userId='.Yii::app()->user->id)->queryAll();
		}
		
		foreach($groups as $group) {
			$names[$group['id']] = $group['name'];
		}
		
		return $names;
	}
	
	public static function getCalendarFilters() {
		$user = User::model()->findByPk(Yii::app()->user->id);
		$calendarFilters = explode(',', $user->calendarFilter);
		$filters = X2Calendar::getCalendarFilterNames();
		
		$filterList = array();
		foreach($filters as $filter)
			if(in_array($filter, $calendarFilters))
				$filterList[$filter] = true;
			else
				$filterList[$filter] = false;
		
		return $filterList;
	}
	
	// get a list of the names of all filters
	public static function getCalendarFilterNames() {
		return array('contacts', 'accounts', 'opportunities', 'quotes', 'products', 'media', 'completed', 'email', 'attachment');
	}

	/**
     * Getter for the possible actions used by the calendar
     * @return array Array of constructed URLS
     */ 
    public static function getCalendarUrls(){
        $urls = array(
            'jsonFeed' => Yii::app()->createUrl('/calendar/jsonFeed'), // feed to get actions from users
            'jsonFeedGroup' => Yii::app()->createUrl('/calendar/jsonFeedGroup'), // feed to get actions from group Calendar
            'jsonFeedShared' => Yii::app()->createUrl('/calendar/jsonFeedShared'), // feed to get actions from shared calendars
            'jsonFeedGoogle' => Yii::app()->createUrl('/calendar/jsonFeedGoogle'), // feed to get events from a google calendar
            //'jsonFeedAll' => Yii::app()->createUrl('/calendar/jsonFeedAll'),
            'currentUserFeed' => Yii::app()->createUrl('/calendar/jsonFeed', array('user' => Yii::app()->user->name)), // add current user actions to calendar
            'anyoneUserFeed' => Yii::app()->createUrl('/calendar/jsonFeed', array('user' => 'Anyone')), // add Anyone actions to calendar
            'moveAction' => Yii::app()->createUrl('/calendar/moveAction'),
            'moveGoogleEvent' => Yii::app()->createUrl('/calendar/moveGoogleEvent'),
            'resizeAction' => Yii::app()->createUrl('/calendar/resizeAction'),
            'resizeGoogleEvent' => Yii::app()->createUrl('/calendar/resizeGoogleEvent'),
            'viewAction' => Yii::app()->createUrl('/calendar/viewAction'),
            'saveAction' => Yii::app()->createUrl('/actions/actions/quickUpdate'),
            'editAction' => Yii::app()->createUrl('/calendar/editAction'),
            'viewGoogleEvent' => Yii::app()->createUrl('/calendar/viewGoogleEvent'),
            'editGoogleEvent' => Yii::app()->createUrl('/calendar/editGoogleEvent'),
            'saveGoogleEvent' => Yii::app()->createUrl('/calendar/saveGoogleEvent'),
            'deleteGoogleEvent' => Yii::app()->createUrl('/calendar/deleteGoogleEvent'),
            'completeAction' => Yii::app()->createUrl('/calendar/completeAction'),
            'uncompleteAction' => Yii::app()->createUrl('/calendar/uncompleteAction'),
            'deleteAction' => Yii::app()->createUrl('/calendar/deleteAction'),
            'saveCheckedCalendar' => Yii::app()->createUrl('/calendar/saveCheckedCalendar'),
            'saveCheckedCalendarFilter' => Yii::app()->createUrl('/calendar/saveCheckedCalendarFilter'),
            'index' => Yii::app()->createUrl('/calendar/index')
        );
        
        return $urls;
    
    }

    public static function translationArray($key, $encode = true){
    	if ($key == 'buttonText'){
	    	$array = array( // translate buttons
	    	    'today' => Yii::t('calendar', 'Today'),
	    	    'month' => Yii::t('calendar', 'Month'),
	    	    'agendaWeek' => Yii::t('calendar', 'Week'),
	    	    'day' => Yii::t('calendar', 'Day'),
	    	);
    	}
    	else if ($key == 'monthNames'){
	    	$array =  array( // translate month names
	    	    Yii::t('calendar', 'January'),
	    	    Yii::t('calendar', 'February'),
	    	    Yii::t('calendar', 'March'),
	    	    Yii::t('calendar', 'April'),
	    	    Yii::t('calendar', 'May'),
	    	    Yii::t('calendar', 'June'),
	    	    Yii::t('calendar', 'July'),
	    	    Yii::t('calendar', 'August'),
	    	    Yii::t('calendar', 'September'),
	    	    Yii::t('calendar', 'October'),
	    	    Yii::t('calendar', 'November'),
	    	    Yii::t('calendar', 'December'),
	    	);
	    }
    	else if ($key == 'monthNamesShort'){
	    	$array =  array( // translate short month names
	    	    Yii::t('calendar', 'Jan'),
	    	    Yii::t('calendar', 'Feb'),
	    	    Yii::t('calendar', 'Mar'),
	    	    Yii::t('calendar', 'Apr'),
	    	    Yii::t('calendar', 'May'),
	    	    Yii::t('calendar', 'Jun'),
	    	    Yii::t('calendar', 'Jul'),
	    	    Yii::t('calendar', 'Aug'),
	    	    Yii::t('calendar', 'Sep'),
	    	    Yii::t('calendar', 'Oct'),
	    	    Yii::t('calendar', 'Nov'),
	    	    Yii::t('calendar', 'Dec'),
	    	);
	    }
    	else if ($key == 'dayNames'){
	    	$array =  array( // translate day names
	    	    Yii::t('calendar', 'Sunday'),
	    	    Yii::t('calendar', 'Monday'),
	    	    Yii::t('calendar', 'Tuesday'),
	    	    Yii::t('calendar', 'Wednesday'),
	    	    Yii::t('calendar', 'Thursday'),
	    	    Yii::t('calendar', 'Friday'),
	    	    Yii::t('calendar', 'Saturday'),
	    	);
	    }
    	if ($key == 'dayNamesShort'){
	    	$array =  array( // translate short day names
	    	    Yii::t('calendar', 'Sun'),
	    	    Yii::t('calendar', 'Mon'),
	    	    Yii::t('calendar', 'Tue'),
	    	    Yii::t('calendar', 'Wed'),
	    	    Yii::t('calendar', 'Thu'),
	    	    Yii::t('calendar', 'Fri'),
	    	    Yii::t('calendar', 'Sat'),	
	    	);
	    }

	    if( $encode )
	    	return CJSON::encode($array);
	    else
	    	return $array;

    }
	
	// get a google calendar service instance using an access token and,
	// if necesary, refresh the access token
	public function getGoogleCalendar() {
		// Google Calendar Libraries
		$timezone = date_default_timezone_get();
		require_once "protected/extensions/google-api-php-client/src/Google_Client.php";
		require_once "protected/extensions/google-api-php-client/src/contrib/Google_CalendarService.php";
		date_default_timezone_set($timezone);

		$admin = Yii::app()->settings;
		if($admin->googleIntegration) {
			$client = new Google_Client();
			$client->setClientId($admin->googleClientId);
			$client->setClientSecret($admin->googleClientSecret);
			//$client->setDeveloperKey($admin->googleAPIKey);
			$client->setAccessToken($this->googleAccessToken);
			$service = new Google_CalendarService($client);

			// check if the access token needs to be refreshed
			// note that the google library automatically refreshes the access token if we need a new one, 
			// we just need to check if this happend by calling a google api function that requires authorization, 
			// and, if the access token has changed, save this new access token
			$googleCalendar = $service->calendars->get($this->googleCalendarId);			
			if($this->googleAccessToken != $client->getAccessToken()) {
				$this->googleAccessToken = $client->getAccessToken();
				$this->update();
			}

			return $service;
		}
		
		return null;
	}
	
	public function createGoogleEvent($action) {
	
		// Google Calendar Libraries
		$timezone = date_default_timezone_get();
		require_once "protected/extensions/google-api-php-client/src/Google_Client.php";
		require_once "protected/extensions/google-api-php-client/src/contrib/Google_CalendarService.php";
		date_default_timezone_set($timezone);
		
		$googleCalendar = $this->getGoogleCalendar();
	
		$event = new Event();
		$event->setSummary($action->actionDescription);
		
		if($action->allDay) {
			$start = new EventDateTime();
			$start->setDate(date('Y-m-d', $action->dueDate));
			$event->setStart($start);
			
			if(!$action->completeDate)
				$action->completeDate = $action->dueDate + 86400;
			$end = new EventDateTime();
			$end->setDate(date('Y-m-d', $action->completeDate));
			$event->setEnd($end);
		} else {
			$start = new EventDateTime();
			$start->setDateTime(date('c', $action->dueDate));
			$event->setStart($start);
			
			if(!$action->completeDate)
				$action->completeDate = $action->dueDate + 3600; // if no end time specified, make event 1 hour long
			$end = new EventDateTime();
			$end->setDateTime(date('c', $action->completeDate));
			$event->setEnd($end);
		}
		
		if($action->color && $action->color != '#3366CC') {
		    $colorTable = array(
		    	10=>'Green',
		    	11=>'Red',
		    	6=>'Orange',
		    	8=>'Black',
		    );
		    if(($key = array_search($action->color, $colorTable)) != false)
		    	$event->setColorId($key);
		}
		
		$googleCalendar->events->insert($this->googleCalendarId, $event);
	}

	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
		if(!Yii::app()->user->checkAccess('CalendarAdminAccess')) // if not admin
			$criteria->condition = "createdBy='". Yii::app()->user->name . "'"; // user can only edit shared calendar they have created
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}
	
	private function searchBase($criteria, $pageSize=null) {
		$criteria->compare('name',$this->name,true);
		
		return new SmartActiveDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'pagination'=>array(
				'pageSize'=>Profile::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

    public function getDisplayName ($plural=true) {
        return Yii::t('calendar', 'Calendar');
    }
	
}
