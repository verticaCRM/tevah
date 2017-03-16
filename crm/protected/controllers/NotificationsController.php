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
 * User notifications & social feed controller
 *
 * @package application.controllers
 */
Yii::import('application.models.Relationships');
Yii::import('application.models.Tags');

class NotificationsController extends CController {

    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('get','delete','deleteAll','newMessage','getMessages','checkNotifications','saveGridviewSettings','saveFormSettings', 'fullScreen', 'widgetState','widgetOrder'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*')
            )
        );
    }

    /**
     * Obtain all current notifications for the current web user.
     */
    public function actionGet() {

        if(Yii::app()->user->isGuest) {
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'sessionError'=>Yii::t('app','Your session has expired. You may select "cancel" to ignore this message and recover unsaved data from the current page. Otherwise, you will be redirected to the login page.')
            ));
            Yii::app()->end();
        }

        if(!isset($_GET['lastNotifId']))    // if the client doesn't specify the last
            $_GET['lastNotifId'] = 0;        // message ID received, send everything

        $notifications = $this->getNotifications($_GET['lastNotifId']);
        $notifCount = 0;
        if(count($notifications))
            $notifCount = X2Model::model('Notification')->countByAttributes(array('user'=>Yii::app()->user->name),'createDate < '.time());

        $chatMessages = array();
        $lastEventId = 0;
        $lastTimestamp=0;
        if(isset($_GET['lastEventId']) && is_numeric($_GET['lastEventId'])){    // if the client specifies the last message ID received,
            $lastEventId = $_GET['lastEventId'];                                // only send newer messages
        }
        if(isset($_GET['lastTimestamp']) && is_numeric($_GET['lastTimestamp'])){
            $lastTimestamp=$_GET['lastTimestamp'];
        }
        Yii::import('application.models.Events');
        Yii::import('application.components.Formatter');
        Yii::import('application.controllers.x2base');
        Yii::import('application.controllers.X2Controller');
        if($lastEventId==0){
            $limit=20;
        }else{
            $limit=null;
        }
        $result=Events::getEvents($lastEventId,$lastTimestamp,null,null,$limit);
        $events=$result['events'];
        $i=count($events)-1;
        for($i; $i>-1; --$i) {
            if(isset($events[$i])){
                $userLink = '<span class="widget-event">'.$events[$i]->user.'</span>';
                $chatMessages[] = array(
                    (int)$events[$i]->id,
                    (int)$events[$i]->timestamp,
                    $userLink,
                    $events[$i]->getText(array ('truncated' =>true)),
                    Formatter::formatFeedTimestamp($events[$i]->timestamp)
                );
            }
        }

        if(!empty($notifications) || !empty($chatMessages)) {
            header('Content-type: application/json');
            echo CJSON::encode(array(
                'notifCount'=>$notifCount,
                'notifData'=>$notifications,
                'chatData'=>$chatMessages,
            ));
        }
    }

    /**
     * Looks up notifications using the specified offset and limit
     */
    public function getNotifications($lastId=0,$getNext=false) {

        // import all the models
        Yii::import('application.models.Social');
        Yii::import('application.models.Profile');
        Yii::import('application.models.Events');
        Yii::import('application.models.Notification');
        Yii::import('application.models.Fields');
        Yii::import('application.components.X2WebUser');
        foreach(scandir('protected/modules') as $module){
            if(file_exists('protected/modules/'.$module.'/register.php'))
                Yii::import('application.modules.'.$module.'.models.*');
        }

        $notifications = array();

        if($getNext) {
            $criteria = new CDbCriteria(array(
                'condition'=>'id<=:lastId AND user=:user AND createDate <= :time',                                // don't get anything more recent than lastId,
                'params'=>array(':user'=>Yii::app()->user->name,':lastId'=>$lastId,':time'=>time()),        // because these are going to get appended to the end,
                'order'=>'id DESC',                                                                         // not the beginning of the list
                'limit'=>1,        // only get the 10th row
                'offset'=>9
            ));
        } else {
            $criteria = new CDbCriteria(array(
                'condition'=>'id>:lastId AND user=:user AND createDate <= :time',                                // normal request; get everything since lastId
                'params'=>array(':user'=>Yii::app()->user->name,':lastId'=>$lastId,':time'=>time()),
                'order'=>'id DESC',
                'limit'=>10
            ));
        }


        $notifModels = X2Model::model('Notification')->findAll($criteria);

        foreach($notifModels as &$model) {
            $msg = $model->getMessage();

            if($msg !== null) {
                $notifications[] = array(
                    'id'=>$model->id,
                    'viewed'=>$model->viewed,
                    'date'=>Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short'),$model->createDate),
                    'text'=>$msg,
                    'timestamp'=>$model->createDate,
                    'modelId' => $model->modelId,
                    'type'=>$model->type,
                );
                if($model->type == 'voip_call') {
                    $model->viewed = 1;
                    $model->update('viewed');
                }
            }
        }
        return $notifications;
    }

    /**
     * Mark an action as viewed.
     */
    public function actionMarkViewed() {
        if(isset($_GET['id'])) {
            if(!is_array($_GET['id']))
                $_GET['id'] = array($_GET['id']);

            foreach($_GET['id'] as &$id) {
                $notif = X2Model::model('Notification')->findByPk($id);
                if(isset($notif) && $notif->user == Yii::app()->user->name) {
                    $notif->viewed = 1;
                    $notif->update();
                }
            }
        }
    }

    /**
     * Delete an action by its ID. Encode and return the next notification if requested
     * @param type $id
     */
    public function actionDelete($id) {

        if(!isset($_GET['lastNotifId']))
            $_GET['lastNotifId'] = 0;

        $model = X2Model::model('Notification')->findByPk($id);
        if(isset($model) && $model->user = Yii::app()->user->name)
            $model->delete();

        if(isset($_GET['getNext']))
            echo CJSON::encode(array('notifData'=>$this->getNotifications($_GET['lastNotifId'],true)));
    }

    /**
     * Clear all notifications.
     */
    public function actionDeleteAll() {
        X2Model::model('Notification')->deleteAllByAttributes(array('user'=>Yii::app()->user->name));
        $this->redirect(array('/site/viewNotifications'));
    }

    /**
     * Normalize linebreaks in output.
     *
     * @todo refactor this out of controllers
     * @param string $text
     * @param boolean $allowDouble
     * @param boolean $allowUnlimited
     * @return string
     */
    public static function convertLineBreaks($text,$allowDouble = true,$allowUnlimited = false) {
        $text = mb_ereg_replace("\r\n","\n",$text);        //convert microsoft's stupid CRLF to just LF

        if(!$allowUnlimited)
            $text = mb_ereg_replace("[\r\n]{3,}","\n\n",$text);    // replaces 2 or more CR/LF chars with just 2
        if($allowDouble)
            $text = mb_ereg_replace("[\r\n]",'<br />',$text);    // replaces all remaining CR/LF chars with <br />
        else
            $text = mb_ereg_replace("[\r\n]+",'<br />',$text);

        return $text;
    }
}
