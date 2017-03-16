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

/* @edition:pro */

/**
 * @package application.controllers
 */
class RelationshipsController extends x2base {

    public $modelClass = 'Relationships';

	public $layout = '//layouts/column1';


    public function filters() {
        return array_merge(parent::filters(), array(
            'accessControl',
        ));
    }

    public function accessRules() {
        return array (
            array ('allow',
                'actions' => array (
                    'graph', 'getRecordData', 'addNode', 'connectNodes', 
                    'deleteEdges', 'viewInlineGraph', 'ajaxGetModelAutocomplete',
                ),
                'users' => array ('@'),
            ),
            array ('deny',
                'users' => array ('*')
            )
        );
    }

    public function actionAjaxGetModelAutocomplete ($modelType, $name=null) {
        if (!Yii::app()->params->isAdmin) $this->denied ();
        return parent::actionAjaxGetModelAutocomplete ($modelType, $name);
    }

    /**
     * Display the relationships graph with initial focus given to the specified record
     */
    public function actionGraph ($recordId, $recordType) {
        if (!Yii::app()->params->isAdmin) $this->denied ();
        $model = X2Model::getModelOfTypeWithId ($recordType, $recordId);
        if (!$model) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record type or record id')); 
        }
        if (!Yii::app()->controller->checkPermissions ($model, 'view')) {
            $this->denied ();
        }
        $this->render ('graphFullScreen', array (
            'model' => $model,
        ));
    }

    /**
     * Get information about the record and its neighbors 
     */
    public function actionGetRecordData ($recordId, $recordType) {
        $model = X2Model::getModelOfTypeWithId ($recordType, $recordId);
        if (!Yii::app()->controller->checkPermissions ($model, 'view')) {
            $this->denied ();
        }

        if (!$model) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record type or record id')); 
        }
        $retArr = array ();
        $neighborData = RelationshipsGraph::getNeighborData ($model);
        $retArr['detailView'] = $this->renderPartial (
            'application.components.views._relationshipsGraphRecordDetails', array (
                'model' => $model,
                'neighborData' => $neighborData,
            ), true);
        $retArr['neighborData'] = $neighborData;
        echo CJSON::encode ($retArr);
    }

    /**
     * Creates an edge between source node and all target nodes and echoes info about the new edges
     */
    public function actionAddNode ($recordId, $recordType, array $otherRecordInfo) {
        $model = X2Model::getModelOfTypeWithId ($recordType, $recordId);
        if (!$model) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record type or record id')); 
        }
        if (!Yii::app()->controller->checkPermissions ($model, 'edit')) {
            $this->denied ();
        }
        $models = $this->getModelsFromTypeAndId ($otherRecordInfo);
        $modelCount = count ($models);

        // create relationships between new node and each target node
        $edges = array (); // new edges with source and target specified by node uid
        $modelA = $model;
        for ($i = 0; $i < $modelCount; $i++) {
            $modelB = $models[$i];
            $rel = new Relationships;
            $typeA = get_class ($modelA);
            $typeB = get_class ($modelB);
            $rel->firstType = $typeA;
            $rel->firstId = $modelA->id;
            $rel->secondType = $typeB;
            $rel->secondId = $modelB->id;
            if ($rel->save ()) {
                $edges[] = array (
                    'source' => $typeA.$modelA->id,
                    'target' => $typeB.$modelB->id,
                );
            }
        }
        echo CJSON::encode ($edges);
    }

    /**
     * Creates an edge between all pairs of specified nodes and echoes info about the new edges
     * @param array $recordInfo model type, model id pairs (max 4)
     */
    public function actionConnectNodes (array $recordInfo) {
        $models = array ();
        if (count ($recordInfo) > 4) {
            throw new CHttpException (400, Yii::t('app', 'Too many records to connect')); 
        }
        if (!Yii::app()->controller->checkPermissions ($model, 'edit')) {
            $this->denied ();
        }
        $models = $this->getModelsFromTypeAndId ($recordInfo);
        $modelCount = count ($models);

        // create relationships between each pair of models
        $edges = array (); // new edges with source and target specified by node uid
        for ($i = 0; $i < $modelCount; $i++) {
            $modelA = $models[$i];
            for ($j = $i + 1; $j < $modelCount; $j++) {
                $modelB = $models[$j];
                $rel = new Relationships;
                $typeA = get_class ($modelA);
                $typeB = get_class ($modelB);
                $rel->firstType = $typeA;
                $rel->firstId = $modelA->id;
                $rel->secondType = $typeB;
                $rel->secondId = $modelB->id;
                if ($rel->save ()) {
                    $edges[] = array (
                        'source' => $typeA.$modelA->id,
                        'target' => $typeB.$modelB->id,
                    );
                }
            }
        }
        echo CJSON::encode ($edges);
    }

    public function actionDeleteEdges (array $edgeData) {
        $criteria = new CDbCriteria;
        $qpg = new QueryParamGenerator (':actionDeleteEdges');
        foreach ($edgeData as $edge) {
            $firstType = $edge[0][0];
            $firstId = $edge[0][1];
            $secondType = $edge[1][0];
            $secondId = $edge[1][1];
            // deny access if user doesn't have edit permission for either node
            if (!Yii::app()->params->isAdmin) {
                $modelA = X2Model::getModelOfTypeWithId ($firstType, $firstId);
                $modelB = X2Model::getModelOfTypeWithId ($secondType, $secondId);
                if ((!$modelA || !Yii::app()->controller->checkPermissions ($modelA, 'edit')) &&
                    (!$modelB || !Yii::app()->controller->checkPermissions ($modelB, 'edit'))) {

                    $this->denied ();
                }
            }
            $criteria->addCondition (
                "firstType={$qpg->nextParam ($firstType)} AND
                 firstId={$qpg->nextParam ($firstId)} AND
                 secondType={$qpg->nextParam ($secondType)} AND
                 secondId={$qpg->nextParam ($secondId)}", 'OR');
            $criteria->addCondition (
                "secondType={$qpg->nextParam ($secondId)} AND
                 secondId={$qpg->nextParam ($secondType)} AND
                 firstType={$qpg->nextParam ($firstType)} AND
                 firstId={$qpg->nextParam ($firstId)}", 'OR');
        }
        $criteria->params = $qpg->getParams ();
        if (Relationships::model ()->deleteAll ($criteria)) {
            echo 'success';
        }
    }

    public function actionViewInlineGraph ($recordId, $recordType, $height=null) {
        $model = X2Model::getModelOfTypeWithId ($recordType, $recordId);
        if (!$model) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record type or record id')); 
        }
        if (!Yii::app()->controller->checkPermissions ($model, 'view')) {
            $this->denied ();
        }

        $this->renderPartial ('graphInline', array (
            'model' => $model,
            'height' => $height,
        ), false, true);
    }

    private function getModelsFromTypeAndId (array $recordInfo) {
        // validate record info and look up models
        foreach ($recordInfo as $info) {
            $model = X2Model::getModelOfTypeWithId ($info[0], $info[1]);
            if (!$model) {
                throw new CHttpException (400, Yii::t('app', 'Invalid record type or record id')); 
            }
            $models[] = $model;
        }
        return $models;
    }

}
