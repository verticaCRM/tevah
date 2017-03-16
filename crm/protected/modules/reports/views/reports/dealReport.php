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

Yii::app()->clientScript->registerCss('dealReportCss',"

#deal-report-grid-contacts {
    border-bottom: 1px solid #D5D5D5;
}

");
$this->actionMenu = $this->formatMenu($this->getActionMenuItems());
Yii::app()->clientScript->registerScript('leadPerformance','
    $("#startDate,#endDate").change(function() {
        $("#dateRange").val("custom");
    });
',CClientScript::POS_READY);
?>
<div class="page-title"><h2><?php echo Yii::t('charts', 'Deal Report'); ?></h2></div>
<div class="form">
<?php $form = $this->beginWidget('CActiveForm', array(
    'action'=>'dealReport',
    'id'=>'dateRangeForm',
    'enableAjaxValidation'=>false,
    'method'=>'get'
)); ?>

<div class="row">
    <div class="cell">
        <?php echo CHtml::label(Yii::t('charts', 'Record Type'),'modelType'); ?>
        <?php echo CHtml::dropDownList('model',$modelName,array('contacts'=>Yii::t('app','Contacts'),'opportunity'=>Yii::t('app','Opportunities'),'accounts'=>Yii::t('app','Accounts')),
                array(
                    'id'=>'modelType',
                ));
        ?>
    </div>
    <div class="cell">
        <?php echo CHtml::label(Yii::t('charts', 'Start Date'),'startDate'); ?>
        <?php
        Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');

        $this->widget('CJuiDateTimePicker',array(
            'name'=>'start',
            // 'value'=>$startDate,
            'value'=>Formatter::formatDate($dateRange['start']),
            // 'title'=>Yii::t('app','Start Date'),
            // 'model'=>$model, //Model object
            // 'attribute'=>$field->fieldName, //attribute name
            'mode'=>'date', //use "time","date" or "datetime" (default)
            'options'=>array(
                'dateFormat'=>Formatter::formatDatePicker(),
                'changeMonth'=>true,
                'changeYear'=>true,

            ), // jquery plugin options
            'htmlOptions'=>array('id'=>'startDate','width'=>20),
            'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
        ));
        ?>
    </div>
    <div class="cell">
        <?php echo CHtml::label(Yii::t('charts', 'End Date'),'startDate'); ?>
        <?php
        $this->widget('CJuiDateTimePicker',array(
            'name'=>'end',
            'value'=>Formatter::formatDate($dateRange['end']),
            // 'value'=>$endDate,
            'mode'=>'date', //use "time","date" or "datetime" (default)
            'options'=>array(
                'dateFormat'=>Formatter::formatDatePicker(),
                'changeMonth'=>true,
                'changeYear'=>true,
            ),
            'htmlOptions'=>array('id'=>'endDate','width'=>20),
            'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
        ));
        ?>
    </div>
    <div class="cell">
        <?php echo CHtml::label(Yii::t('charts', 'Date Range'),'range'); ?>
        <?php
        echo CHtml::dropDownList('range',$dateRange['range'],array(
            'custom'=>Yii::t('charts','Custom'),
            'thisWeek'=>Yii::t('charts','This Week'),
            'thisMonth'=>Yii::t('charts','This Month'),
            'lastWeek'=>Yii::t('charts','Last Week'),
            'lastMonth'=>Yii::t('charts','Last Month'),
            // 'lastQuarter'=>Yii::t('charts','Last Quarter'),
            'thisYear'=>Yii::t('charts','This Year'),
            'lastYear'=>Yii::t('charts','Last Year'),
                            'all'=>Yii::t('charts','All Time'),

        ),array('id'=>'dateRange'));
        ?>
    </div>
    <div class="cell">
        <?php echo CHtml::label(Yii::t('charts', 'Strict Mode'),'strict'); ?>
        <?php
        echo CHtml::checkbox('strict',$dateRange['strict'],array('id'=>'strict'));
        ?>
    </div>

    <!--<div class="cell">
        <?php echo CHtml::submitButton(Yii::t('charts','Go'),array('name'=>'','class'=>'x2-button','style'=>'margin-top:13px;')); ?>
    </div>-->
</div>
<div class="row">
    <div class="cell">
        <?php
            echo $form->label($model,'assignedTo');
            echo $form->dropDownList($model,'assignedTo', array(''=>'---')+User::getNames());
        ?>
    </div>
    <div class="cell">
        <?php
        if($modelName!='accounts'){
            echo $form->label($model,'leadSource');
            $dropdown = Dropdowns::model()->findByPk(103);    // lead source
            $dropdowns = json_decode($dropdown->options,true);

            $dropdowns = array(''=>'---') + $dropdowns;

            echo $form->dropDownList($model,'leadSource',$dropdowns, array());
        }
        ?>
    </div>
    <div class="cell">
        <?php
        if($modelName!='accounts'){
            if($model->hasAttribute('company'))
                $companyField='company';
            elseif($model->hasAttribute('accountName'))
                $companyField='accountName';

            echo $form->label($model,$companyField);

            $linkId = '';
            // if the field is an ID, look up the actual name
            if(isset($model->$companyField) && ctype_digit((string)$model->$companyField)) {
                $linkModel = X2Model::model('Accounts')->findByPk($model->$companyField);
                if(isset($linkModel)) {
                    $model->$companyField = $linkModel->name;
                    $linkId = $linkModel->id;
                } else {
                    $model->$companyField = '';
                }
                // Otherwise parse the nameId ref
            } else {
                $linkModel = $model->getLinkedModel($companyField);
                if ($linkModel !== null) $model->$companyField = $linkModel->name;
            }
            // $linkSource = $this->createUrl(X2Model::model('Accounts')->getAutoCompleteSource());
            $linkSource = $this->createUrl('/accounts/accounts/getItems');

            echo CHtml::hiddenField(
                'Contacts[company_id]',$linkId,array('id'=>'Contacts_company_id'));
            $form->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'model'=>$model,
                'attribute'=>$companyField,
                // 'name'=>'autoselect_'.$fieldName,
                'source' => $linkSource,
                'value'=>$model->$companyField,
                'options'=>array(
                    'minLength'=>'1',
                    'select'=>'js:function( event, ui ) {
                        $("#Contacts_company_id").val(ui.item.id);
                        $(this).val(ui.item.value);
                        return false;
                    }',
                ),
            ));
        }
            // echo $form->textField($model,'company',array());
        ?>
    </div>
    <div class="cell">
        <?php echo CHtml::submitButton(Yii::t('charts','Go'),array('class'=>'x2-button','style'=>'margin-top:13px;')); ?>
    </div>

</div>

</div>

<?php

if(isset($dataProvider) && $modelName=='contacts') {
    $this->widget('X2GridView', array(
        'id'=>'deal-report-grid-contacts',
        'title'=>Yii::t('contacts','Contacts'),
        'template'=> '<div class="page-title icon contacts rounded-top">{title}{summary}</div>{items}{pager}',
        'dataProvider'=>$dataProvider,
        // 'enableSorting'=>false,
        // 'model'=>$model,
        // 'filter'=>$model,
        // 'columns'=>$columns,
        'modelName'=>'Contacts',
        'viewName'=>'reports-deal-report-contacts',
        // 'columnSelectorId'=>'contacts-column-selector',
        'defaultGvSettings'=>array(
            "name"=>110,
            "assignedTo"=>45,
            "company"=>55,
            "leadSource"=>65,
            "closedate"=>50,
            "dealvalue"=>75,
            "dealstatus"=>70,
            "rating"=>60,
            "lastUpdated"=>55
            // 'gvControls'=>66,
        ),
        'specialColumns'=>array(
            'name'=>array(
                'name'=>'name',
                'header'=>Yii::t('contacts','Name'),
                'value'=>'CHtml::link($data->name,array("/contacts/".$data->id), array("class" => "contact-name"))',
                // 'value'=>'$data->getLink()',
                'type'=>'raw',
            ),
            'rating'=>array(
                'name'=>'rating',
                'header'=>Yii::t('contacts','Confidence'),
                'value'=>'($data->rating*20)."%"',
                // 'value'=>'$data->getLink()',
                'type'=>'raw',
            ),
        ),
        'enableControls'=>true,
        'enableTags'=>true,
        'fullscreen'=>true,
    ));
}elseif(isset($dataProvider) && $modelName=='opportunity'){
    $this->widget('X2GridView', array(
        'id'=>'opportunities-grid',
        'title'=>Yii::t('opportunities','Opportunities'),
        'template'=> '<div class="page-title icon opportunities rounded-top">{title}{summary}</div>{items}{pager}',
        'dataProvider'=>$dataProvider,
        // 'enableSorting'=>false,
        // 'model'=>$model,
        // 'filter'=>$model,
        // 'columns'=>$columns,
        'modelName'=>'Opportunity',
        'viewName'=>'reports-deal-report-opportunities',
        // 'columnSelectorId'=>'contacts-column-selector',
        'defaultGvSettings'=>array(
            "name"=>120,
            "assignedTo"=>55,
            "accountName"=>50,
            "quoteAmount"=>60,
            "expectedCloseDate"=>50,
            "probability"=>55,
            "salesStage"=>70,
            "leadSource"=>65,
            "lastUpdated"=>60
            // 'gvControls'=>66,
        ),
        'specialColumns'=>array(
            'name'=>array(
                'name'=>'name',
                'header'=>Yii::t('opportunities','Name'),
                'value'=>'CHtml::link($data->name,array("/opportunities/".$data->id), array("class" => "opportunity-name"))',
                // 'value'=>'$data->getLink()',
                'type'=>'raw',
            ),
        ),
        'enableControls'=>true,
        'enableTags'=>true,
        'fullscreen'=>true,
    ));
}elseif(isset($dataProvider) && $modelName='accounts'){
    $this->widget('X2GridView', array(
        'id'=>'accounts-grid',
        'title'=>Yii::t('accounts','Accounts'),
        'template'=> '<div class="page-title icon accounts rounded-top">{title}{summary}</div>{items}{pager}',
        'dataProvider'=>$dataProvider,
        // 'enableSorting'=>false,
        // 'model'=>$model,
        // 'filter'=>$model,
        // 'columns'=>$columns,
        'modelName'=>'Accounts',
        'viewName'=>'reports-deal-report-accounts',
        // 'columnSelectorId'=>'contacts-column-selector',
        'defaultGvSettings'=>array(
            "name"=>140,
            "createDate"=>70,
            "assignedTo"=>80,
            "type"=>70,
            "employees"=>50,
            "annualRevenue"=>55,
            "website"=>60,
            "lastUpdated"=>65
            // 'gvControls'=>66,
        ),
        'specialColumns'=>array(
            'name'=>array(
                'name'=>'name',
                'header'=>Yii::t('accounts','Name'),
                'value'=>'CHtml::link($data->name,array("/accounts/".$data->id), array("class" => "account-name"))',
                // 'value'=>'$data->getLink()',
                'type'=>'raw',
            ),
        ),
        'enableControls'=>true,
        'enableTags'=>true,
        'fullscreen'=>true,
    ));
}

?><br>
<?php $form = $this->endWidget(); ?>
<div class="form">
    <h3>Data Summary</h3>
    <b>Total Records: </b><?php echo $total;?><br />
    <?php if($modelName!='accounts'){ ?><b>Max Deal Value: </b><?php echo Yii::app()->locale->numberFormatter->formatCurrency($totalValue, Yii::app()->params['currency']);?><br /><?php } ?>
    <?php if($modelName!='accounts'){ ?><b>Projected Value: </b><?php echo Yii::app()->locale->numberFormatter->formatCurrency($projectedValue, Yii::app()->params['currency']);?><br /><?php } ?>
    <?php if($modelName!='accounts'){ ?><b>Current Value: </b><?php echo Yii::app()->locale->numberFormatter->formatCurrency($currentValue, Yii::app()->params['currency']);?><br /><?php } ?>

<br><br>

<?php
echo CHtml::link(Yii::t('charts','Save Report'),array(
        'saveReport',
        'type'=>'deal',
        'parameters'=>$parameters,
        'start'=>$dateRange['start'],
        'end'=>$dateRange['end'],
        'range'=>$dateRange['range'],
), array('class' => 'x2-button'));
?>
</div>




