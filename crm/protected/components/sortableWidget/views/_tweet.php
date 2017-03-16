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

$twitterFeedWidget->replaceTextEntities ($data);
?>
<div class='tweet-container'>
<div class='tweet-container-inner'>
    <div class='x2-col'>
        <a href='https://twitter.com/<?php echo urlencode ($data['user']['screen_name']); ?>'> 
            <img src="<?php echo $data['user']['profile_image_url_https']; ?>" />
        </a>
    </div>
    <div class='x2-col'>
        <div class='x2-row'>
            <a href='https://twitter.com/<?php echo urlencode ($data['user']['screen_name']); ?>' 
             class='author-name'>
            <?php
                echo CHtml::encode ($data['user']['name']);
            ?>
            </a>
            <a href='https://twitter.com/<?php echo urlencode ($data['user']['screen_name']); ?>' 
             class='author-username'>
            <?php
                echo '@'.CHtml::encode ($data['user']['screen_name']);
            ?>
            </a>
            <span class='tweet-timestamp'>
            <?php
                echo $twitterFeedWidget->renderTimestamp ($data);
            ?>
            </span>
        </div>
        <div class='x2-row'>
        <?php
            echo $data['text'];
        ?>
        </div>
        <div class='x2-row button-row'>
            <div class='buttons-container'>
                <a title="<?php echo CHtml::encode (Yii::t('app', 'Reply')); ?>" 
                 href='https://twitter.com/intent/tweet?in_reply_to=<?php echo $data['id_str']; ?>'
                 class='reply-button pseudo-link'></a>
                <a title="<?php echo CHtml::encode (Yii::t('app', 'Retweet')); ?>" 
                 href='https://twitter.com/intent/retweet?tweet_id=<?php echo $data['id_str']; ?>'
                 class='retweet-button pseudo-link'></a>
                <a title="<?php echo CHtml::encode (Yii::t('app', 'Favorite')); ?>" 
                 href='https://twitter.com/intent/favorite?tweet_id=<?php echo $data['id_str']; ?>'
                 class='favorite-button pseudo-link'></a>
            </div>
        </div>
    </div>
    <div class='clearfix'></div>
</div>
</div>
