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

Yii::app()->clientScript->registerScript('deleteActionJs', "
function deleteAction(actionId, type) {

	if(confirm('".Yii::t('app', 'Are you sure you want to delete this item?')."')) {
		$.ajax({
			url: '".CHtml::normalizeUrl(array('/actions/actions/delete'))."/'+actionId+'?ajax=1',
			type: 'POST',
			success: function(response) {
				if(response === 'success')
					$('#history-'+actionId).fadeOut(200,function() { 
                        $('#history-'+actionId).remove(); 
                    });

					// event detected by x2chart.js
					$(document).trigger ('deletedAction');
                    x2.TransactionalViewWidget.refreshByActionType (type);
				}
		});
	}
}
", CClientScript::POS_HEAD);
$themeUrl = Yii::app()->theme->getBaseUrl();
if(empty($data->type)){
    if($data->complete == 'Yes')
        $type = 'complete';
    else if($data->dueDate < time())
        $type = 'overdue';
    else
        $type = 'action';
} else
    $type = $data->type;

if($type == 'workflow'){

    $workflowRecord = X2Model::model('Workflow')->findByPk($data->workflowId);
    $stageRecords = X2Model::model('WorkflowStage')->findAllByAttributes(
            array('workflowId' => $data->workflowId), new CDbCriteria(array('order' => 'id ASC'))
    );

    // see if this stage even exists; if not, delete this junk
    if($workflowRecord === null || $data->stageNumber < 1 || $data->stageNumber > count($stageRecords)){
        $data->delete();
        return;
    }
}

// if($type == 'call') {
// $type = 'note';
// $data->type = 'note';
// }
?>



<div class="view" id="history-<?php echo $data->id; ?>">
    <!--<div class="deleteButton">
<?php //echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button')  ?>
    </div>-->
    <div class="icon <?php echo $type; ?>">
    <div class="stacked-icon"></div>
    </div>
    <div class="header">
<?php

if(empty($data->type) || $data->type == 'weblead'){
    echo "<span style='color:grey;cursor:pointer' class='action-frame-link' data-action-id='{$data->id}'>";
    if($data->complete == 'Yes'){
        echo Yii::t('actions', 'Completed:')." </span>".Formatter::formatCompleteDate($data->completeDate);
    }else{
        if(!empty($data->dueDate)){
            echo Yii::t('actions', 'Due:')." </span>".Actions::parseStatus($data->dueDate).'</b>';
        }elseif(!empty($data->createDate)){
            echo Yii::t('actions', 'Created:')." </span>".Formatter::formatLongDateTime($data->createDate).'</b>';
        }else{
            echo "&nbsp;";
        }
    }
} elseif($data->type == 'workflow'){
    // $actionData = explode(':',$data->actionDescription);
    echo Yii::t('workflow', 'Process:').'<b> '.$workflowRecord->name.'/'.$stageRecords[$data->stageNumber - 1]->name.'</b> ';
}elseif($data->type == 'event'){
    echo '<b>'.CHtml::link(Yii::t('calendar', 'Event').': ', '#', array('class' => 'action-frame-link', 'data-action-id' => $data->id));
    if($data->allDay){
        echo Formatter::formatLongDate($data->dueDate);
        if($data->completeDate)
            echo ' - '.Formatter::formatLongDate($data->completeDate);
    } else{
        echo Formatter::formatLongDateTime($data->dueDate);
        if($data->completeDate)
            echo ' - '.Formatter::formatLongDateTime($data->completeDate);
    }
    echo '</b>';
}elseif($data->type == 'call'){
    echo Yii::t('actions', 'Call:').' '.($data->completeDate == $data->dueDate
            ? Formatter::formatCompleteDate($data->completeDate)
            : Formatter::formatTimeInterval(
                $data->dueDate,$data->completeDate,'{start}; {decHours} '.Yii::t('app','hours')));
}elseif($data->type == 'webactivity'){
    echo Yii::t('actions', 'This contact visited your website');
}elseif($data->type == 'time'){
    echo Formatter::formatTimeInterval($data->dueDate,$data->dueDate+$data->timeSpent);
} else{
    $timeFormat = Formatter::formatCompleteDate($data->getRelevantTimestamp());
    if($data->type == 'attachment') {
        if ($data->completedBy === 'Email') {
            $label = 'Email Message:';
        } else {
            $label = 'Attachment:';
        }
    } elseif($data->type == 'quotes') {
        $label = 'Quote:';
    } elseif(in_array($data->type, array('email', 'emailFrom', 'email_quote', 'email_invoice'))) {
        $label = 'Email Message:';
    } elseif(
        in_array($data->type, array('emailOpened', 'emailOpened_quote', 'email_opened_invoice'))) {

        $label = 'Email Opened:';
    }

    if(isset($label)) echo Yii::t('actions', $label).' ';
    echo $timeFormat;
}
?>
        <div class="buttons">
        <?php
        if(!Yii::app()->user->isGuest){
            if(empty($data->type) || $data->type == 'weblead'){
                if($data->complete == 'Yes' && 
                   Yii::app()->user->checkAccess('ActionsUncomplete',
                    array('assignedTo'=>$data->assignedTo))) {

                    echo CHtml::link(
                        X2Html::fa('fa-undo'), 
                        '#', array(   
                            'class' => 'uncomplete-button',
                            'title' => Yii::t('app', 'uncomplete'),
                            'data-action-id' => $data->id));
                } elseif(Yii::app()->user->checkAccess(
                    'ActionsComplete',array('assignedTo'=>$data->assignedTo))){

                    echo CHtml::link(
                        X2Html::fa('fa-check-circle'), 
                        '#', array(
                            'class' => 'complete-button', 
                            'title' => Yii::t('app', 'complete'),
                            'data-action-id' => $data->id));
                }
            }
            if($data->type != 'workflow'){
                if(Yii::app()->user->checkAccess(
                    'ActionsUpdate',array('assignedTo'=>$data->assignedTo))){

                    echo ($data->type != 'attachment' && $data->type != 'email') ?
                        ' '.CHtml::link(
                            X2Html::fa('fa-edit'), 
                            '#', array(
                                'class' => 'update-button', 'title' => Yii::t('app', 'edit'),
                                'data-action-id' => $data->id)) : '';
                }
                if(Yii::app()->user->checkAccess(
                    'ActionsDelete',array('assignedTo'=>$data->assignedTo))){

                    echo ' '.CHtml::link(
                        X2Html::fa('fa-times'), 
                        '#', array(
                            'onclick' => 'deleteAction('.
                                $data->id.', "'.$data->type.'"); return false',
                            'title' => Yii::t('app', 'delete')
                        ));
                }
            }
        }
        ?>
        </div>
    </div>
    <div class="description">
<?php
if($type == 'attachment' && $data->completedBy != 'Email') {
    echo Media::attachmentActionText(Yii::app()->controller->convertUrls($data->actionDescription), true, true);
} else if($type == 'workflow'){

    if(!empty($data->stageNumber) && !empty($data->workflowId) && $data->stageNumber <= count($stageRecords)){
        if($data->complete == 'Yes')
            echo ' <b>'.Yii::t('workflow', 'Completed').'</b> '.Formatter::formatLongDateTime($data->completeDate);
        else
            echo ' <b>'.Yii::t('workflow', 'Started').'</b> '.Formatter::formatLongDateTime($data->createDate);
    }
    if(isset($data->actionDescription))
        echo '<br>'.CHtml::encode($data->actionDescription);
} elseif($type == 'webactivity'){
    if(!empty($data->actionDescription))
        echo CHtml::encode($data->actionDescription), '<br>';
    echo date('Y-m-d H:i:s', $data->completeDate);
} elseif(in_array($data->type, 
    array(
        'email',
        'emailFrom',
        'email_quote',
        'email_invoice',
        'emailOpened',
        'emailOpened_quote', 
        'emailOpened_invoice'
    ))) {

    $legacy = false;
    if(!preg_match(
        InlineEmail::insertedPattern('ah', '(.*)', 1, 'mis'), $data->actionDescription, $matches)){
        // Legacy pattern:
        preg_match('/<b>(.*?)<\/b>(.*)/mis', $data->actionDescription, $matches);
        $legacy = true;
    }
    if(!empty($matches)){
        $header = $matches[1];
        $body = '';
    }else{
        if(empty($data->subject)){
            $header = "No subject found";
            $body = "(Error displaying email)";
        }else{
            $header = $data->subject."<br>";
            $body = $data->actionDescription;
        }
    }
    if($type == 'emailOpened'){
        echo "Contact has opened the following email:<br />";
    }
    if(!Yii::app()->user->isGuest){
        echo $legacy ? '<strong>'.$header.'</strong> '.$body : $header.$body;
    }else{
        echo $body;
    }
    echo ($legacy ? '<br />' : '').
        CHtml::link(
            '[View email]', '#', 
            array('onclick' => 'return false;', 'id' => $data->id, 'class' => 'email-frame'));
}elseif($data->type == 'quotesDeleted'){
    echo $data->actionDescription;
}elseif($data->type == 'quotes'){
    $data->renderInlineViewLink ();
} else
    echo Yii::app()->controller->convertUrls(CHtml::encode($data->actionDescription)); // convert LF and CRLF to <br />
?>
    </div>
    <div class="footer">
        <?php
        if(isset($relationshipFlag) && $relationshipFlag && $data->associationId !== 0 && X2Model::getModelName($data->associationType) !== false){
            $relString=" | ".X2Model::getModelLink($data->associationId, X2Model::getModelName($data->associationType));
        }else{
            $relString="";
        }
        if(empty($data->type) || $data->type == 'weblead' || $data->type == 'workflow'){
            if($data->complete == 'Yes'){
                echo Yii::t('actions', 'Completed by {name}', array('{name}' => User::getUserLinks($data->completedBy))).$relString;
            }else{
                $userLink = User::getUserLinks($data->assignedTo);
                $userLink = empty($userLink) ? Yii::t('actions', 'Anyone') : $userLink;
                echo Yii::t('actions', 'Assigned to {name}', array('{name}' => $userLink)).$relString;
            }
        }else if(in_array($data->type,array('note','call','emailOpened','time'))){
            echo $data->completedBy == 'Guest' ? "Guest" : User::getUserLinks($data->completedBy).$relString;
            // echo ' '.Formatter::formatDate($data->completeDate);
        }else if($data->type == 'attachment' && $data->completedBy != 'Email'){
            echo Yii::t('media', 'Uploaded by {name}', array('{name}' => User::getUserLinks($data->completedBy))).$relString;
        }else if(in_array($data->type, array('email', 'emailFrom')) && $data->completedBy != 'Email'){
            echo Yii::t('media', ($data->type == 'email' ? 'Sent by {name}' : 'Sent to {name}'), array('{name}' => User::getUserLinks($data->completedBy))).$relString;
        }
        ?>
    </div>

</div>
