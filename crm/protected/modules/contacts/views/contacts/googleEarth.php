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
$menuItems = array(
    array('label'=>Yii::t('contacts','All Contacts'),'url'=>array('index')),
    array('label'=>Yii::t('contacts','Lists'),'url'=>array('lists')),
    array('label'=>Yii::t('contacts','Create Contact'),'url'=>array('create')),
    array('label'=>Yii::t('contacts','Create List'),'url'=>array('createList')),
    array('label'=>Yii::t('contacts','Import Contacts'),'url'=>array('/admin/importModels', 'model'=>'Contacts')),
    array('label'=>Yii::t('contacts','Export to CSV'),'url'=>array('/admin/exportModels', 'model'=>'Contacts')),
    array('label'=>Yii::t('contacts','Contact Map')),
    array('label'=>Yii::t('contacts','Saved Maps'),'url'=>array('savedMaps')),
);
$this->actionMenu = $this->formatMenu($menuItems);
Yii::app()->clientScript->registerScriptFile("https://maps.googleapis.com/maps/api/js?sensor=false&libraries=visualization");

if(isset($noHeatMap) && $noHeatMap){
    Yii::app()->clientScript->registerScript('maps-initialize',"
        var map, pointarray, ge, directionsDisplay;
        var center=$center;
        var markerFlag=$markerFlag;
        var mapFlag=$mapFlag;
        var zoom=0;
        var noHeatMap=true;
        var bounds=new google.maps.LatLngBounds();
        var directionsService=new google.maps.DirectionsService();
        function initialize() {
            directionsDisplay = new google.maps.DirectionsRenderer();
            var latLng = new google.maps.LatLng(center['lat'],center['lng']);
            bounds.extend(latLng);
            var mapOptions = {
                zoom: 3,
                mapTypeId: google.maps.MapTypeId.SATELLITE,
                center: latLng
            };
            map = new google.maps.Map(document.getElementById('map_canvas'),
                mapOptions);
            directionsDisplay.setMap(map);
            directionsDisplay.setPanel(document.getElementById('directions-panel'));
        }

    initialize();
    ");
}else{
    Yii::app()->clientScript->registerScript('maps-initialize',"
        var map, pointarray, heatmap, ge;
        locs=new Array();
        var bounds=new google.maps.LatLngBounds();
        var locations=$locations;
        var center=$center;
        var markerFlag=$markerFlag;
        var zoom=".(isset($zoom)?$zoom:"0").";
        var mapFlag=$mapFlag;
        var noHeatMap=false;
        $.each(locations,function(index,value){
            var tempLatLng=new google.maps.LatLng(value['lat'], value['lng'])
            locs.push(tempLatLng);
            bounds.extend(tempLatLng);
        });
        function initialize() {
            var latLng = new google.maps.LatLng(center['lat'],center['lng']);
            var mapOptions = {
                zoom: 3,
                mapTypeId: google.maps.MapTypeId.SATELLITE
            };
            map = new google.maps.Map(document.getElementById('map_canvas'),
                mapOptions);
            if(!mapFlag){
                if(locs.length>0){
                    map.fitBounds(bounds);
                }else{
                    map.setCenter(latLng);
                    map.setZoom(2);
                }
                google.maps.event.addListenerOnce(map,'zoom_changed',function(){
                    if(map.getZoom()>10 && zoom==0){
                        map.setZoom(3);
                    }
                });
            }else{
                map.setCenter(latLng);
                map.setZoom(zoom);
            }
            pointArray = new google.maps.MVCArray(locs);
            heatmap = new google.maps.visualization.HeatmapLayer({
                data: pointArray
            });
            heatmap.setMap(map);
            google.maps.event.addListenerOnce(map,'tilesloaded',function(){
                var lastValidCenter = map.getCenter();
                google.maps.event.addListener(map, 'center_changed', function() {
                    //console.debug(map.getCenter().lat());
                    if (map.getCenter().lat()<85 && map.getCenter().lat()>-85) {
                        // still within valid bounds, so save the last valid position
                        lastValidCenter = map.getCenter();
                        return;
                    }
                    // not valid anymore => return to last valid position
                    map.panTo(lastValidCenter);
                });
            });

        }
        initialize();

    ");
}

Yii::app()->clientScript->registerScript('maps-qtip', "
var contactId=".(empty($contactId)?"0":$contactId).";
var center=$markerLoc;
function refreshQtip() {
        var fields=new Array('link','directions');
		if(contactId!=0) {
                $.ajax({
                    url: yii.baseUrl+'/index.php/contacts/qtip',
                    data: { id: contactId,fields:fields },
                    method: 'get',
                    success: function(data){
                        if(typeof marker==='undefined'){
                            var latLng = new google.maps.LatLng(center['lat'],center['lng']);
                            var marker = new google.maps.Marker({
                                position: latLng,
                                map: map
                            });
                        }
                        if(typeof infowindow==='undefined'){
                            var infowindow = new google.maps.InfoWindow({
                                content: data
                            });
                            infowindow.open(map, marker);

                        }
                        google.maps.event.addListener(infowindow,'domready',function(){
                            $('#corporate-directions').click(function(e){
                                e.preventDefault();
                                getDirections('corporate');
                            });
                            $('#personal-directions').click(function(e){
                                e.preventDefault();
                                getDirections('personal');
                            });
                        });

                        google.maps.event.addListener(marker,'click',function(){
                            infowindow.open(map,marker);
                        });
                        $('#hide-marker-link').click(function(){
                            $(this).remove();
                            $('#contactId').val(null);
                            infowindow.close();
                            marker.setVisible(false);
                        });
                        bounds.extend(marker.getPosition());
                        google.maps.event.addListenerOnce(map,'zoom_changed',function(){
                            if(map.getZoom()>10 && zoom==0 && !noHeatMap){
                                map.setZoom(3);
                            }else if(zoom!=0){
                                map.setZoom(zoom);
                            }
                            if(noHeatMap){
                                map.setZoom(map.getZoom()-3);
                            }
                        });
                        if(zoom==0)
                            map.fitBounds(bounds);

                    }
                });
		}
}
function getDirections(type){
    var latLng = new google.maps.LatLng(center['lat'],center['lng']);
    var corporateAddress=\"".CJavaScript::encode(Yii::app()->settings->corporateAddress)."\";
    var personalAddress=\"".CJavaScript::encode(Yii::app()->params->profile->address)."\";
    if(type=='corporate' && corporateAddress!=''){
        var request = {
            origin:corporateAddress,
            destination:latLng,
            travelMode: google.maps.TravelMode.DRIVING
        };
    }else if(type=='corporate' && corporateAddress==''){
        alert('Invalid corporate address.');
    }
    if(type=='personal' && personalAddress!=''){
        var request = {
            origin:personalAddress,
            destination:latLng,
            travelMode: google.maps.TravelMode.DRIVING
        };
    }else if(type=='personal' && personalAddress==''){
        alert('Invalid personal address.');
    }
    if(typeof request!=='undefined'){
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
                $('#map_canvas').width('70%');
                $('#directions-box').show();
            }else if(status=='ZERO_RESULTS'){
                alert('No valid route found.');
            }
        });
    }
}
refreshQtip();
");

Yii::app()->clientScript->registerScript('map-controls',"
$('#mapControlForm').submit(function(){
    var tags=new Array();
    $.each($(this).find ('.x2-tag-list a'),function(){
        tags.push($(this).text());
    });
    $('#params_tags').val(tags);
});
$(window).resize(function(){
   google.maps.event.trigger(map,'resize');
});
$('#save-button').click(function(e){
    e.preventDefault();
    var mapName = prompt('".addslashes(Yii::t('app','What should the map be named?'))."','');
    if(mapName){
        var center=map.getCenter();
        var tags=new Array();
        $.each($('#mapControlForm').find ('.x2-tag-list a'),function(){
            tags.push($(this).text());
        })
        var parameters={'assignedTo':'".(empty($assignment)?"":$assignment)."','tags':tags};
        var contactIdPost=$('#contactId').val();
        centerLat=center.lat();
        centerLng=center.lng();
        zoom=map.getZoom();
        $.ajax({
            url:'saveMap',
            type:'POST',
            data:{'mapName':mapName,'contactId':contactIdPost,'parameters':parameters,'centerLat':centerLat,'centerLng':centerLng,'zoom':zoom},
            success:function(){
                alert('Map parameters saved!');
            }
        });
    }
});
");
?>

<div class='page-title icon contacts'>
    <h2><?php echo Yii::t('contacts','Contact Map');?></h2>
    <?php 
    echo CHtml::link(
        Yii::t('contacts','Save Map'),'#',
        array(
            'class'=>'x2-button right',
            'id'=>'save-button',
        ));
    ?>
</div>
<div id="controls" class="form">

<?php
   $form = $this->beginWidget('CActiveForm', array(
        'action' => 'googleMaps',
        'id' => 'mapControlForm',
        'enableAjaxValidation' => false,
        'method' => 'POST',
    ));
    echo CHtml::hiddenField('contactId',isset($contactId)?$contactId:'');
    // $range = 30; //$model->dateRange;
    // echo $startDate .' '.$endDate;

    ?>
    <div class="row">
        <h2 style='margin-top: 5px'><?php echo Yii::t('contacts','Filters');?></h2>
        <div class="cell">
            <?php if(!empty($contactId)) { ?>
                <a href="#" id="hide-marker-link" style="text-decoration:none;"><?php echo Yii::t('contacts','Clear Marker');?></a>
            <?php } ?>
        </div>

        <div class="cell">
            <label><?php echo Yii::t('contacts','Assigned To'); ?></label>
            <?php echo CHtml::dropDownList('params[assignedTo]',$assignment,array_merge(array(''=>'All'),User::getNames())); ?>
        </div>

        <div class="cell" style="width:350px;">
            <?php $this->widget('InlineTags', array('filter'=>true,'tags'=>$tags)); ?>
            <?php echo CHtml::hiddenField('params[tags]'); ?>
        </div>

        <div class="cell">
            <?php echo CHtml::submitButton(Yii::t('charts', 'Go'), array('class' => 'x2-button', 'style' => 'margin-top:13px;')); ?>
        </div>
    </div>
    <div class="row">



    </div>
    <?php $this->endWidget();?>
</div>
<div style="width:30%;float:left;display:none;" id="directions-box">
    <div style="width:auto;height:788px;margin-bottom:0px;overflow-y:scroll;" class="form" id="directions-panel"></div>
</div>
<div id="map_canvas" style="height: 800px; width:100%;float:right;"></div>

