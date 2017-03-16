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


Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiSortable class.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package application.components
 */
class SortableWidgets extends CJuiWidget {

    /**
     * @var array list of sortable items (id=>item content).
     * Note that the item contents will not be HTML-encoded.
     */
    public $portlets = array();
    public $jQueryOptions = array();

    /**
     * @var string the name of the container element that contains all items. Defaults to 'div'.
     */
    public $tagName = 'div';

    /**
     * Run this widget.
     * This method registers necessary javascript and renders the needed HTML code.
     */
    public function run(){
        $themeURL = Yii::app()->theme->getBaseUrl();

        Yii::app()->clientScript->registerScript('toggleWidgetState', "
            function toggleWidgetState(widget,state) {
                if($('#widget_' + widget).hasClass('ui-sortable-helper') == false) {
                    $.ajax({
                        url: '".CHtml::normalizeUrl(array('/site/widgetState'))."',
                        type: 'GET',
                        data: 'widget='+widget+'&state='+state,
                        success: function(response) {
                            if(response === 'success') {
                                var link = $('#widget_'+widget+
                                    ' .portlet-minimize a.portlet-minimize-button');
                                var newLink = ($(link).find('span').hasClass('expand-widget')) ?
                                    '<span '+ 
                                      'class=\"fa fa-caret-down collapse-widget\" ></span>' : 
                                    // toggle link between [+] and [-]
                                    '<span '+
                                      'class=\"fa fa-caret-left expand-widget\"></span>';            
                                link.html(newLink);

                                // slide widget open or closed
                                $('#widget_'+widget+' .portlet-content').toggle({
                                    effect: 'blind',
                                    duration: 200,
                                    complete: function() {
                                        blindComplete = true;
                                    }
                                });
                            }
                        }
                    });
                }

            }
        ", CClientScript::POS_HEAD);

        $id = $this->getId(); //get generated id
        if(isset($this->htmlOptions['id'])) {
            $id = $this->htmlOptions['id'];
        } else {
            $this->htmlOptions['id'] = $id;
        }

        $options = empty($this->jQueryOptions) ? '' : CJavaScript::encode($this->jQueryOptions);
        Yii::app()->getClientScript()->registerScript(
            'SortableWidgets'.'#'.$id, "jQuery('#{$id}').sortable({$options});");

        echo CHtml::openTag($this->tagName, $this->htmlOptions)."\n";

        $widgetHideList = array();
        if(!Yii::app()->user->isGuest){
            $layout = Yii::app()->params->profile->getLayout();
        }else{
            $layout = array();
        }
        $profile = yii::app()->params->profile;
        foreach($this->portlets as $class => $properties){
            
            // show widget if it isn't hidden
            if(!in_array($class, array_keys($layout['hiddenRight']))){ 
                $visible = ($properties['visibility'] == '1');

                if(!$visible)
                    $widgetHideList[] = '#widget_'.$class;

                $minimizeLink = CHtml::link(
                    $visible ? 
                        CHtml::tag('span',
                            array('class' => 'fa fa-caret-down collapse-widget'), ' ') : 

                        CHtml::tag('span',
                            array('class' => 'fa fa-caret-left expand-widget'), ' ')

                    , '#', array('class' => 'portlet-minimize-button')
                    ).' '.CHtml::link(
                        '<i class="fa fa-times"></i>', '#',
                        array(
                            'onclick' => "$('#widget_$class').hideWidgetRight(); return false;",
                            'class' => 'portlet-close-button'
                        )
                    );

                $widget = $this->widget($class, $properties['params'], true);

                if($profile->activityFeedOrder){
                    ?>
                    <script>
                        $("#topDown").addClass('selected');
                    </script>
                    <?php
                    $activityFeedOrderSelect = 'top';
                }else{
                    ?>
                    <script>
                        $("#bottomUp").addClass('selected');
                    </script>
                    <?php
                    $activityFeedOrderSelect = 'bottom';
                }
                if($profile->mediaWidgetDrive){
                    ?>
                    <script>
                        $("#drive-selector").addClass('selected');
                    </script>
                    <?php
                }else{
                    ?>
                    <script>
                        $("#media-selector").addClass('selected');
                    </script>
                    <?php
                }
                $preferences;
                $activityFeedWidgetBgColor = '';
                if($profile != null){
                    $preferences = $profile->theme;
                    $activityFeedWidgetBgColor = $preferences['activityFeedWidgetBgColor']; 
                }
                if(!empty($widget)){
                    if($class == "ChatBox"){
                        $header = '<div style="text-decoration: none; margin-right:30px; display:inline-block;">'.
                            Yii::t('app', 'Activity Feed').
                            '</div>
                            <script>
                                $(\'#widget-dropdown a\').css("text-align", "none");
                                $(\'#widget-dropdown a\').css("text-align", "center !important");
                             </script>
                            <span id="gear-img-container" class="gear-img-container fa fa-cog fa-lg" style="width: 18px; height: 18px">
                                <span
                                 style="opacity:0.3" onmouseout="this.style.opacity=0.3;"
                                 onmouseover="this.style.opacity=1" ></span>
                            </span>
                            <ul class="closed" id="feed-widget-gear-menu">
                                <div style="text-align: left">'.
                                    Yii::t('app','Activity Feed Order').
                                '</div>
                                <hr>
                                <div id="topDown" style="font-weight:normal; 
                                 float: left; margin-right: 3px;">'.
                                    Yii::t('app','Top Down').
                                '</div>
                                <div id="bottomUp" style="font-weight:normal; float: left">'.
                                    Yii::t('app','Bottom Up').
                                '</div>
                                <!--hr>
                                <div style="text-align: left">'.
                                    Yii::t('app','Background Color').
                                '</div>
                                <colorPicker style="padding: 0px !important;">'.
                                    CHtml::textField( 
                                        'widgets-activity-feed-widget-bg-color',
                                        $activityFeedWidgetBgColor).
                                '</colorPicker-->
                            </ul>';
                    }elseif($class == "MediaBox" && Yii::app()->settings->googleIntegration){
                        $auth = new GoogleAuthenticator();
                        if($auth->getAccessToken()){
                            $header = 
                                '<div style="margin-right:15%;display:inline-block;">'.
                                    Yii::t('app', 'Media').
                                '</div>
                                <span style="float:left">
                                    <img src="'.Yii::app()->theme->baseUrl.'/images/widgets.png" 
                                     style="opacity:0.3" onmouseout="this.style.opacity=0.3;"
                                    onmouseover="this.style.opacity=1" />
                                </span>
                                <ul class="closed" id="media-widget-gear-menu">
                                    <div style="text-align: left">'.
                                        Yii::t('app','{media} Widget Settings', array(
                                            '{media}' => Modules::displayName(true, 'Media'),
                                        )).
                                    '</div>
                                    <hr>
                                    <div id="media-selector" style="font-weight:normal; 
                                     float: left; margin-right: 3px;">'.
                                         Yii::t('app','X2 {media}', array(
                                            '{media}' => Modules::displayName(true, 'Media'),
                                         )).
                                    '</div>
                                    <div id="drive-selector" style="font-weight:normal; 
                                     float: left">'.
                                        Yii::t('app','Google Drive').
                                    '</div>
                                    <hr>
                                    <div style="text-align: left">'.
                                        Yii::t('app','Refresh Google Drive Cache').
                                    '</div>
                                    <hr>
                                    <a href="#" class="x2-button" id="drive-refresh" 
                                     style="font-weight:normal; float: left">'.
                                        Yii::t('app','Refresh Files').
                                    '</a>
                                    <hr>
                                </ul> ';
                        }else{
                            $header = Yii::t('app', Yii::app()->params->registeredWidgets[$class]);
                        }
                    }else{
                        $header = Yii::t('app', Yii::app()->params->registeredWidgets[$class]);
                    }
                    $this->beginWidget('zii.widgets.CPortlet', array(
                        'title' => 
                            '<div id="widget-dropdown" class="dropdown">'
                                .$header.
                                '<div class="portlet-minimize" 
                                  onclick="toggleWidgetState(\''.
                                    $class.'\','.($visible ? 0 : 1).'); return false;">'.

                                    $minimizeLink.
                                '</div>
                            </div>',
                        'id' => $properties['id']
                    ));
                    echo $widget;
                    $this->endWidget();
                }else{
                    echo '<div ', CHtml::renderAttributes(
                        array('style' => 'display;none;', 'id' => $properties['id'])), '></div>';
                }
            }
        }
        Yii::app()->clientScript->registerScript('setWidgetState', '
            $(document).ready(function() {
                $("'.implode(',', $widgetHideList).'").find(".portlet-content").hide();
            });', CClientScript::POS_HEAD);

        echo CHtml::closeTag($this->tagName);
        
Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
    'dmFyIF8weDFhNzk9WyJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDZDXHg2R'
    .'lx4NjFceDY0IiwiXHgyM1x4NzBceDZGXHg3N1x4NjVceDcyXHg2NVx4NjRceDJEXHg2Mlx4NzlceDJEX'
    .'Hg3OFx4MzJceDY1XHg2RVx4NjdceDY5XHg2RVx4NjUiLCJceDZDXHg2NVx4NkVceDY3XHg3NFx4NjgiL'
    .'CJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzMVx4NjRceDMxXHg2Mlx4NjRceDYzXHgzMFx4N'
    .'jJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzOFx4NjNceDY1XHgzN1x4MzRceDMzXHg2Nlx4M'
    .'zZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzMVx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M1x4N'
    .'jVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzMlx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4N'
    .'jRceDY1XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzIiwiXHg2MVx4NzRceDc0XHg3MiIsIlx4M0Fce'
    .'Dc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1IiwiXHg2OVx4NzMiLCJceDY4XHg2OVx4NjRceDY0XHg2N'
    .'Vx4NkUiLCJceDc2XHg2OVx4NzNceDY5XHg2Mlx4NjlceDZDXHg2OVx4NzRceDc5IiwiXHg2M1x4NzNce'
    .'DczIiwiXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiXHg3N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGX'
    .'Hg3MFx4NjFceDYzXHg2OVx4NzRceDc5IiwiXHg3M1x4NzRceDYxXHg3NFx4NjlceDYzIiwiXHg3MFx4N'
    .'kZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDUwXHg2Q1x4NjVceDYxXHg3M1x4NjVceDIwXHg3M'
    .'Fx4NzVceDc0XHgyMFx4NzRceDY4XHg2NVx4MjBceDZDXHg2Rlx4NjdceDZGXHgyMFx4NjJceDYxXHg2M'
    .'1x4NkJceDJFIiwiXHg2OFx4NzJceDY1XHg2NiIsIlx4NzJceDY1XHg2RFx4NkZceDc2XHg2NVx4NDFce'
    .'Dc0XHg3NFx4NzIiLCJceDYxIiwiXHg2Rlx4NkUiXTtpZihfMHgxYTc5WzBdIT09IHR5cGVvZiBqUXVlc'
    .'nkmJl8weDFhNzlbMF0hPT0gdHlwZW9mIFNIQTI1Nil7JCh3aW5kb3cpW18weDFhNzlbMjFdXShfMHgxY'
    .'Tc5WzFdLGZ1bmN0aW9uICgpe3ZhciBfMHg5OTNleDE9JChfMHgxYTc5WzJdKTtfMHg5OTNleDFbXzB4M'
    .'WE3OVszXV0mJl8weDFhNzlbNF09PVNIQTI1NihfMHg5OTNleDFbXzB4MWE3OVs2XV0oXzB4MWE3OVs1X'
    .'SkpJiZfMHg5OTNleDFbXzB4MWE3OVs4XV0oXzB4MWE3OVs3XSkmJl8weDFhNzlbOV0hPV8weDk5M2V4M'
    .'VtfMHgxYTc5WzExXV0oXzB4MWE3OVsxMF0pJiYwIT1fMHg5OTNleDFbXzB4MWE3OVsxMl1dKCkmJjAhP'
    .'V8weDk5M2V4MVtfMHgxYTc5WzEzXV0oKSYmMT09XzB4OTkzZXgxW18weDFhNzlbMTFdXShfMHgxYTc5W'
    .'zE0XSkmJl8weDFhNzlbMTVdPT1fMHg5OTNleDFbXzB4MWE3OVsxMV1dKF8weDFhNzlbMTZdKXx8KCQoX'
    .'zB4MWE3OVsyMF0pW18weDFhNzlbMTldXShfMHgxYTc5WzE4XSksYWxlcnQoXzB4MWE3OVsxN10pKTt9I'
    .'Ck7fQo='));


    Yii::app()->clientScript->registerScript('sortableWidgetsJS',"
    $(document).ready(function() {
        $('#topDown').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#bottomUp').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#media-selector').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#drive-selector').hover(function(){
            if(!$(this).hasClass('selected')){
                $(this).toggleClass('hover');
            }
        });
        $('#topDown').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/activityFeedOrder'});
                yii.profile['activityFeedOrder']=1;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                var feedbox = $('#feed-box');
                feedbox.children().each(function(i,child){feedbox.prepend(child)});
                feedbox.prop('scrollTop',0);
                $('#bottomUp').removeClass('selected');
            }
        });
        $('#bottomUp').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/activityFeedOrder'});
                yii.profile['activityFeedOrder']=0;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                var feedbox = $('#feed-box');
                var scroll=feedbox.prop('scrollHeight');
                feedbox.children().each(function(i,child){feedbox.prepend(child)});
                feedbox.prop('scrollTop',scroll);
                $('#topDown').removeClass('selected');
            }
        });
        $('#media-selector').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/mediaWidgetToggle'});
                yii.profile['mediaWidgetDrive']=0;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                $('#media-widget-gear-menu').removeClass('open');
                $('#drive-selector').removeClass('selected');
                $('#drive-table').hide();
                $('#x2-media-list').show();
            }
        });
        $('#drive-selector').click(function(){
            if($(this).hasClass('selected')) return;
            else {
                $.ajax({url:yii.baseUrl+'/index.php/site/mediaWidgetToggle'});
                yii.profile['mediaWidgetDrive']=1;
                $(this).addClass('selected');
                $(this).removeClass('hover');
                $('#media-widget-gear-menu').removeClass('open');
                $('#media-selector').removeClass('selected');
                $('#drive-table').show();
                $('#x2-media-list').hide();
            }
        });
        $('#drive-refresh').click(function(e){
            e.preventDefault();
            $.ajax({
                'url':'".
                    Yii::app()->controller->createUrl('/media/media/refreshDriveCache') 
                ."',
                'success':function(data){
                    $('#drive-table').html(data);
                }
            });
            $('#media-widget-gear-menu').removeClass('open');
        });

        function saveWidgetBgColor () {
            if ($(this).data ('ignoreChange')) {
                return;
            }
            var color = $(this).val();
            $.ajax({
                url: yii.baseUrl + '/index.php/site/activityFeedWidgetBgColor',
                data: 'color='+ color,
                success:function(){
                    if(color == '') {
                        $('#feed-box').css('background-color', '#fff');
                    } else {
                        $('#feed-box').css('background-color', '#' + color);
                    }
                    //$('#feed-box').css('color', convertTextColor(color, 'standardText'));
                    // Check for a dark color
                    /*if(convertTextColor(color, 'linkText') == '#fff000'){
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass('dark_background');
                }
                // Light color
                else {
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass('light_background');
                }
                // Set color correctly if transparent is selected
                if(color == ''){
                    $('#feed-box').css('color', 'rgb(51, 51, 51)');
                    $('#feed-box a').removeClass();
                    $('#feed-box a').addClass('light_background');
                }*/
                }
            });
        }

        x2.colorPicker.setUp ($('#widgets-activity-feed-widget-bg-color'), true);

        $('#widgets-activity-feed-widget-bg-color').change(saveWidgetBgColor);


    });

    // @param \$colorString a string representing a hex number
    // @param \$testType standardText or linkText
    function convertTextColor( colorString, textType){
        // Split the string to red, green and blue components
        // Convert hex strings into ints
        var red   = parseInt(colorString.substring(1,3), 16);
        var green = parseInt(colorString.substring(3,5), 16);
        var blue  = parseInt(colorString.substring(5,7), 16);

        if(textType == 'standardText') {
            if((((red*299)+(green*587)+(blue*114))/1000) >= 128) {
                return 'black';
            }
            else {
                return 'white';
            }
        }
        else if (textType == 'linkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff000';  // Yellow links
            }
            else return '#0645AD'; // Blue link color
        }
        else if (textType == 'visitedLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#ede100';  // Yellow links
            }
            else return '#0B0080'; // Blue link color
        }
        else if (textType == 'activeLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff000';  // Yellow links
            }
            else return '#0645AD'; // Blue link color
        }
        else if (textType == 'hoverLinkText') {
            if((((red < 100) || (green < 100)) && blue > 80) || 
               ((red < 80) && (green < 80) && (blue < 80))) {
                return '#fff761';  // Yellow links
            }
            else return '#3366BB'; // Blue link color
        }
    }

    ");

    }
}
?>
<script>
</script>
