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
 * @package application.modules.charts.controllers 
 */
class ChartsController extends x2base {

    public $modelClass="";

    public function actionAdmin() {
        $this->redirect($this->createUrl('/charts/charts/index'));
    }

    public function actionIndex() {
        $this->redirect($this->createUrl('/charts/charts/leadVolume'));
    }

    /**
     * Create a menu for Charts
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        /**
         * To show all options:
         * $menuOptions = array(
         *     'leadVolume', 'marketing', 'pipeline', 'opportunities',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'leadVolume',
                'label' => Yii::t('charts', '{lead} Volume', array(
                    '{lead}' => Modules::displayName(false, "X2Leads"),
                )),
                'url'=>array('leadVolume'),
            ),
            array(
                'name'=>'marketing',
                'label' => Yii::t('charts', '{marketing}', array(
                    '{marketing}' => Modules::displayName(true, "Marketing"),
                )),
                'url' => array('marketing')
            ),
            array(
                'name'=>'pipeline',
                'label' => Yii::t('charts', 'Pipeline'),
                'url' => array('pipeline')
            ),
            array(
                'name'=>'opportunities',
                'label' => Yii::t('charts', '{opportunities}', array(
                    '{opportunities}' => Modules::displayName(true, "Opportunities"),
                )),
                'url' => array('sales')
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

    /***********************************************************************
    * Legacy Charts  
    ***********************************************************************/

    public function actionMarketing() {
        $model = new X2MarketingChartModel();
        if (isset($_POST['X2MarketingChartModel']))
            $model->attributes = $_POST['X2MarketingChartModel'];

        $this->render('marketing', array('model' => $model));
    }

    public function actionSales() {
        $model = new X2SalesChartModel();
        if (isset($_POST['X2SalesChartModel']))
            $model->attributes = $_POST['X2SalesChartModel'];

        $this->render('sales', array('model' => $model));
    }

    public function actionPipeline() {
        $model = new X2PipelineChartModel();
        if (isset($_POST['X2PipelineChartModel']))
            $model->attributes = $_POST['X2PipelineChartModel'];

        $this->render('pipeline', array('model' => $model));
    }
    
    public function formatLeadRatio($a,$b) {
        if($b==0)
            return '&ndash;';
        else
            return number_format(100*$a/$b,2).'%';
    }

    public function actionLeadVolume() {
        
        $dateRange = X2DateUtil::getDateRange();

        // if(isset($_GET['test'])) {
        if(isset($_GET['range'])) {
            $data = Yii::app()->db->createCommand()
                ->select('x2_users.id as userId, CONCAT(x2_users.firstName, " ",x2_users.lastName) as name, assignedTo as id, COUNT(assignedTo) as count')
                // ->select('assignedTo as id, COUNT(assignedTo) as count')
                ->from('x2_contacts')
                ->group('assignedTo')
                ->leftJoin('x2_users','x2_contacts.assignedTo=x2_users.username')
                ->where('createDate BETWEEN :start AND :end', 
                        array (':start' => $dateRange['start'], ':end' => $dateRange['end']))
                ->order('id ASC')
                ->queryAll();
                
            $total = 0;
            for($i=0;$i<$size=count($data);$i++) {
                $total += $data[$i]['count'];
                if(is_numeric($data[$i]['id'])) {
                    $group = X2Model::model('Groups')->findByPk($data[$i]['id']);
                    if(isset($group))
                        $data[$i]['name'] = $group->createLink();
                    else
                        $data[$i]['name'] = $data[$i]['id'];
                        
                } elseif(!empty($data[$i]['userId'])) {
                    $data[$i]['name'] = CHtml::link($data[$i]['name'],array('/users/'.$data[$i]['userId']));
                } else {
                    $data[$i]['name'] = $data[$i]['id'];
                }
                
            }
            $data[] = array('id'=>null,'name'=>'Total','count'=>$total);
            // $data[] = $totals;

            $dataProvider = new CArrayDataProvider($data,array(
                // 'totalItemCount'=>$count,
                'pagination'=>array(
                    'pageSize'=>100,//Yii::app()->params->profile->resultsPerPage,
                ),
            ));

        } else {
            $dataProvider = null;
        }

        $this->render('leadVolume', array(
            'dataProvider'=>$dataProvider,
            'dateRange'=>$dateRange
        ));
        
        // } else {
        
        // $this->render('leadVolume', array(
            // 'dateRange'=>$dateRange
        // ));
        // }
    }

    public function actionGetFieldData(){
        if(isset($_GET['field'])){
            $field=$_GET['field'];
            $options = Yii::app()->db->createCommand()
                    ->select($field)
                    ->from('x2_contacts')
                    ->group($field)
                    ->queryAll();
            $data=array();
            foreach($options as $row){
                if(!empty($row[$field]))
                    $data[$row[$field]]=$row[$field];
            }
            print_r($data);
        }else{
           
        }
    }

    /********************************
    * New Charts Functions
    *********************************/
//    public function actionView($id) {
//        $chart = X2Model::model('Charts')->findByPk($id);
//
//        $reportId = $chart->report->id;
//        $url = Yii::app()->createUrl('reports', array('id'=>$reportId));
//        $this->redirect($url);
//
//    }

}
