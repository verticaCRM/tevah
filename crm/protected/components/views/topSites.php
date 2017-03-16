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

Yii::app()->clientScript->registerCss ('topSitesCss', "
#sites-box{
    min-height: 25px;
    width: auto;
    margin: 5px;
    padding: 0 4px;
    overflow-y: auto;
    word-wrap: break-word;
    line-height: 1.1em;
    font-size: 9pt;
    color: #555;
    background: #fcfcfc;
    border: 1px solid #ddd;
}
#widget_TopSites .portlet-content{
    padding: 0;
}
#site-url-container input {
    width: 120px;
    padding: 5px;
}
#site-url-container #top-site-submit-button {
    width: 60px;
}
#top-sites-form {
    padding: 2px;
}
#sites-box {
    padding: 5px;
}
#sites-box table td:first-child {
    width: 80%;
}
#sites-box table th {
    font-size: 13px;
    font-family: Arial, Helvetica, sans-serif;
}
#top-sites-container .delete-top-site-link-container {
    text-align: center;
}
#top-sites-container .site-delete-button-row-header {
}

.top-sites-input {
    margin-left: 5px;
    display:inline-block;
}
.top-sites-input label{
    display:block;
    font-weight:bold;
}
.top-sites-input.submit {
    vertical-align: top;
}

#top-sites-table a.delete-top-site-link {
    display: none;
    text-decoration: none;
}
#top-sites-table tr:hover {
    background-color: #F5F4DE;
}

#top-sites-table tr:hover a.delete-top-site-link {
    display: block;
}
");

Yii::app()->clientScript->registerScript('updateURLs', "
x2.topSites = {};
/*$(document).ready(updateURLs());
x2.topSites.updateURLs = function (){
    $.ajax({
        type: 'POST',
        url: '".$this->controller->createUrl('/site/getURLs',array('url'=>Yii::app()->request->requestUri))."',
        success:
        function(data){
            $('#sites-box').html(data);
        }
    });
}*/

$('#sites-container').resizable({
    handles: 's',
    minHeight: 75,
    alsoResize: '#sites-container, #sites-box',
    stop: function(event, ui){
        $.post(
            '".Yii::app()->createUrl("/site/saveWidgetHeight")."',
            {
                Widget: 'TopSites',
                Height: {topsitesHeight: parseInt($('topsites-box').css('height'))}
            }
        );
    }
});

$(document).on ('click', '#top-sites-container .delete-top-site-link', function (evt) {
    evt.preventDefault ();
    var that = $(this);
    $.ajax ({
        url: $(this).attr ('href'),
        success: function () {
            $(that).closest ('tr').remove ();
        }
    });
    return false;
});

x2.topSites.addSite = function (links) {
    var newRow = $('<tr>').append (
        $('<td>').append ($(links[0])),
        $('<td>', {'class': 'delete-top-site-link-container'}).append ($(links[1]))
    );
    $('#top-sites-table').append ($(newRow));
};

",CClientScript::POS_HEAD);

?>
<div id="sites-container-fix">
<div id="sites-container">
<div id="top-sites-container">
<div id="sites-box">

<table id='top-sites-table'>
<?php
foreach($data as $entry){
?>
    <tr>
        <td>
        <?php
            echo CHtml::link(
               $entry['title'], URL::prependProto($entry['url']), array('target'=>'_blank'));
        ?>
        </td>
        <td class='delete-top-site-link-container'>
        <?php
        if(isset($entry['id']))
            echo CHtml::link(
                '[x]', array ('site/deleteURL', 'id' => isset($entry['id'])?$entry['id']:'#'),
                array (
                    'title' => Yii::t('app', 'Delete Link'),
                    'class' => 'delete-top-site-link',
                    'target' => '_blank'
                ));
        ?>
        </td>
    </tr>
<?php
}
?>
</table>
</div><!-- #sites-box -->
<form id='top-sites-form'>
    <div id='site-url-container'>
        <div class="top-sites-input">
        <?php
            echo CHtml::label(Yii::t('app', 'Title:'), 'url-title');
            echo CHtml::textField('url-title', '', array('class'=>'x2-textfield'));
        ?>
        </div><!-- .top-sites-input -->
        <div class="top-sites-input">
        <?php 
            echo CHtml::label(Yii::t('app','Link:'),'url-url');
            echo CHtml::textField('url-url', '',array('class'=>'x2-textfield'));
        ?>
        </div><!-- .top-sites-input -->
        <div class="top-sites-input submit">
        <?php
        echo CHtml::ajaxSubmitButton(
            Yii::t('app','Add Site'),
            array('/site/addSite'),
            array(
                'update'=>'sites-box',
                'success'=>"function(response){
                    x2.topSites.addSite (JSON.parse (response));
                    $('#url-title').val('');
                    $('#url-url').val('');
                }",
            ),
            array('class'=>'x2-button','id'=>'top-site-submit-button')
        );?>
        </div><!-- .top-sites-input -->
    </div>

</form>
</div>
</div>
</div>
