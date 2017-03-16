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

Yii::app()->clientScript->registerCss("AvatarCss", "

#profile-image-container {
    width: 100px;
    margin-top: 4px;
    margin: 15px;
    position: relative;
}

.file-wrapper {
    height: 119px;
    display: block;
}


#avatar-image {
    display:block;
}

#photo-upload-overlay {
    text-align: center;
    position: absolute;
    width: 91px;
    height: 35px;
    
    font-weight: bold;
    font-size: 12px;

    border-radius: 0 0 4px 4px;
    border-top: none;
    border: 2px solid rgb(204, 200, 200);
    
    color: rgb(95, 94, 94);
    background: rgb(213, 243, 255);

    opacity:0.7;
    top: 60px;
}

#photo-upload-overlay:hover {
    cursor: pointer;
}

#photo-upload-overlay span {
    display: table-cell;
    vertical-align: middle;
    width: 97px;
    height: 35px;
}

.avatar-upload {
    -webkit-border-radius:8px;
    -moz-border-radius:8px;
    -o-border-radius:8px;
    border-radius:8px;
}

#reset-profile-avatar {
    display:inline-block;
    text-decoration: none;
    margin-bottom: 5px;
    margin-top: 5px;

}

");

Yii::app()->clientScript->registerScript('AvatarJs',"

/**
 * Validate file extension of avatar image. Called during file field onchange event and upon dialog
 * submit.
 * @param object elem a jQuery object corresponding to the file field element
 * @param object submitButton a jQuery object corresponding to the dialog submit button
 * @return bool false if invalid, true otherwise
 */
function validateAvatarFile (elem, submitButton) {
    var isLegalExtension = false;
    auxlib.destroyErrorFeedbackBox (elem);

    // get the file name and split it to separate the extension
    var name = elem.val ();

    // name is valid
    if (name.match (/.+\..+/)) {
        var extension = name.split('.').pop ().toLowerCase ();

        var legalExtensions = ['png','gif','jpg','jpe','jpeg'];        
        if ($.inArray (extension, legalExtensions) !== -1)
            isLegalExtension = true;
    } else if (name !== '') {
        var extension = '';
    }

    if(isLegalExtension) { // enable submit
        submitButton.addClass ('highlight');
    } else { // delete the file name, disable Submit, Alert message
        elem.val ('');
        if (typeof extension !== 'undefined') {
            auxlib.createErrorFeedbackBox ({
                prevElem: elem,
                message: '".Yii::t('app', 'Invalid file type.')."'
            });
        } else {
            auxlib.createErrorFeedbackBox ({
                prevElem: elem,
                message: '".Yii::t('app', 'Please upload a file.')."'
            });
        }
        submitButton.removeClass ('highlight');
    }
        
    return isLegalExtension;
}

/**
 * Setup avatar upload UI element behavior 
 */
function setUpAvatarUpload () {

    // hide/show overlay
    $('#profile-image-container').mouseover (function () {
        $('#photo-upload-overlay').show ();
    }).mouseleave (function (evt) {
        if ($(evt.relatedTarget).closest ('#avatar-image').length === 0 &&
            $(evt.relatedTarget).closest ('#photo-upload-overlay span').length === 0)
            $('#photo-upload-overlay').hide ();
    });

    // instantiate image upload dialog
    $('#photo-upload-overlay').click (function () {
        $('#photo-upload-dialog').dialog ({
            title: '".Yii::t('app', 'Upload an Avatar Photo')."',
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: '".Yii::t('app', 'Submit')."',
                    'class': 'photo-upload-dialog-submit-button',
                    click: function () {
                        if (validateAvatarFile (
                            $('#avatar-photo-file-field'), 
                            $('.photo-upload-dialog-submit-button'))) {

                            $('#photo-form').submit ();
                        }
                    }
                },
                {
                    text: '".Yii::t('app', 'Cancel')."',
                    click: function () {
                        $(this).dialog ('close');
                    }
                }
            ],
            close: function () {
                $('#photo-upload-dialog').hide ();
                $(this).dialog ('destroy');
                auxlib.destroyErrorFeedbackBox ($('#avatar-photo-file-field'));
            }
        });
    });
}

$(function() {
    setUpAvatarUpload ();
});

",CClientScript::POS_HEAD);
?>
<div id='profile-image-container'>
<?php Profile::renderFullSizeAvatar ($id); 
	if($editable) { ?>
    <div id='photo-upload-overlay' style='display:none;'>
        <span><?php echo Yii::t('app', 'Change Avatar'); ?></span>
    </div>
	<?php 
        }
        $url = Yii::app()->createUrl ("profile/uploadPhoto", array ( 
            'id'    => $id,
            'clear' => true 
        ));
    ?>
    <?php if (Profile::model()->findByPk($id)->avatar) { ?>
    <a id='reset-profile-avatar' href='<?php echo $url ?>'>
       <?php echo Yii::t('app', 'Reset avatar') ?>
    </a>
    <?php } ?>
    </div>

<?php if($editable) { ?>
	<div id='photo-upload-dialog' style='display:none;'>
	<?php
	    echo CHtml::form (
            Yii::app()->createUrl ("profile/uploadPhoto", array ('id' => $id)),
                'post',
    	        array ('enctype'=>'multipart/form-data', 'id'=>'photo-form'));
	    echo CHtml::fileField(
	        'photo','', array (
	            'id' => 'avatar-photo-file-field',
	        'onchange' => 
	            'validateAvatarFile ($(this), $(".photo-upload-dialog-submit-button"));'
	    )).'<br />';
	echo CHtml::endForm();
	?>
	</div>

<?php } ?>


