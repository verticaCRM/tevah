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

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/twitterFeedWidget.css'); 

?>
<div class='twitter-timeline-container-outer'>
    <div class='twitter-timeline-container'>
        <div class='twitter-timeline-container-inner'>
        <?php
        $this->getTimeline ();
        ?>
        </div>
        <button class='load-more-tweets-button x2-button'><?php  
            echo CHtml::encode (Yii::t('app', 'Load more'));
        ?></button>
    </div>
    <div class='tweet-box'>
        <a class='new-tweet-button' 
         href='https://twitter.com/intent/tweet?screen_name=<?php echo $username; ?>'>
        <?php
        echo CHtml::encode (Yii::t('app', 'Tweet to {username}', array (
            '{username}' => '@'.$username,
        )));
        ?>
        </a> 
    </div>
</div>

<script type="text/javascript" async src="//platform.twitter.com/widgets.js"></script>

<!--<a class="twitter-timeline" 
 href="https://twitter.com/x2engine" 
 data-screen-name="x2engine"
 data-widget-id="530872797577220096">
Tweets by @cruzio</a>

<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>-->
