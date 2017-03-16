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

Yii::app()->clientScript->registerCss('_x2ProgressBarCSS',"

.x2-progress-bar {
    width: 100%;
    height: 20px;
    border-radius: 3px;
    border: 1px solid rgb(200, 200, 200);
}

.x2-progress-bar .composite-label {
    text-align: center;
    width: 100%;
    display: inline-block;
    margin-top: 1px;
    font-weight: bold;
    height: 0;
    float: left;
}

.x2-progress-bar .progress-value {
    height: 20px;
    width: 0%;
    display: block;
    background: rgb(185, 242, 255);
	background:-moz-linear-gradient(top,	rgb(205,262,275) 0%, rgb(162,222,235) 100%);
	background:-webkit-linear-gradient(top,	rgb(205,262,275) 0%, rgb(162,222,235) 100%);
	background:-o-linear-gradient(top,		rgb(205,262,275) 0%, rgb(162,222,235) 100%);
	background:-ms-linear-gradient(top,		rgb(205,262,275) 0%, rgb(162,222,235) 100%);
	background:linear-gradient(to bottom,	rgb(205,262,275) 0%, rgb(162,222,235) 100%);
    border-radius: 3px 0 0 3px;
}

");

Yii::app()->clientScript->registerScript('_x2ProgressBarJS'.$this->uid,"

;(function () {

function ProgressBar (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        max: null,
        containerSelector: null,
        count: 0
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._container$ = $(this.containerSelector);
    this._bar$ = this._container$.find ('.x2-progress-bar');
    this._count$ = this._container$.find ('.progress-count');
    this._label$ = this._container$.find ('.progress-label');
    this._value$ = this._container$.find ('.progress-value');
    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Pause progress. Can be resumed with resume ()  
 */
ProgressBar.prototype.pause = function () {

};

/**
 * Resumes progress after progress has been paused 
 */
ProgressBar.prototype.resume = function () {

};

/**
 * Resets progress to 0
 */
ProgressBar.prototype.reset = function () {

};

/**
 * Update count, moving progress bar to the right
 * @param int count 
 */
ProgressBar.prototype.updateCount = function (count) {
    this.count = count;
    this._count$.text (count + '/' + this.max);
    this._value$.css ({ width: count / this.max * 100 + '%' });
};

ProgressBar.prototype.getCount = function () {
    return this.count;
};

ProgressBar.prototype.getMax = function () {
    return this.max;
};

/**
 * Increment count, moving progress bar to the right
 * @param int count 
 */
ProgressBar.prototype.incrementCount = function (increment) {
    this.count += increment;
    this.updateCount (this.count);
};

/**
 * @param string label
 */
ProgressBar.prototype.updateLabel = function (label) {
    this._label$.text (label);    
};

/*
Private instance methods
*/

ProgressBar.prototype._init = function () {
    this.updateCount (0);
};

$('#x2-progress-bar-container-$this->uid').data (
    'progressBar', 
    new ProgressBar ({
        max: $this->max,
        containerSelector: '#x2-progress-bar-container-$this->uid'
    }));

}) ();

", CClientScript::POS_READY);

?>
<div id='x2-progress-bar-container-<?php echo $this->uid; ?>' class='x2-progress-bar-container'>
    <div class='x2-progress-bar'>
        <span class='composite-label'>
            <span class='progress-count'></span>
            <span class='progress-label'></span>
        </span>
        <span class='progress-value'></span>
    </div>
</div>
