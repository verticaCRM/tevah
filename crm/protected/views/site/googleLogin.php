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

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl().'/css/login.css');

$this->pageTitle = Yii::app()->settings->appName.' - Login';
$admin = Admin::model()->findByPk(1);


$loginBoxHeight = 230;

/* x2plastart */
if (X2_PARTNER_DISPLAY_BRANDING) {
    //$loginBoxHeight -= 36;
} 
/* x2plaend */

Yii::app()->clientScript->registerCss('googleLogin', "

#login-box-outer {
    top: ".$loginBoxHeight."px;
}

// fix menu shadow
#page .container {
	position:relative;
	z-index:2;
}

#google-login-logo {
    margin: 8px 10px 0 -5px;
}
", 'screen', CClientScript::POS_HEAD);
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js">
</script>
<script type="text/javascript">
    (function () {
      var po = document.createElement('script');
      po.type = 'text/javascript';
      po.async = true;
      po.src = 'https://plus.google.com/js/client:plusone.js?onload=start';
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(po, s);
    })();
</script>
<div id="login-box-outer">
<div class="container<?php echo (isset ($profileId) ? ' welcome-back-page' : ''); ?>" id="login-page">
<div id="login-box">
<div class="form" id="login-form">
    <?php if(isset($admin->googleIntegration) && $admin->googleIntegration == '1'){ ?>
        <div id="login-box">
            <div id="error-message">
                <?php
                if(isset($failure) && $failure == 'email'){
                    echo "A user with email address: <b>$email</b> was not found.  Please contact an administrator.";
                    echo "<div><br /><a class='x2-button' href='".$this->createUrl('login')."'>Return To Login Screen</a></div>";
                }else{
                    echo "Click the button below to log into X2Engine CRM with your Google ID.";

                ?>
            </div>
            <br />
            <div id="signinButton">
                <span class="g-signin"
                      data-scope="https://www.googleapis.com/auth/plus.login
                      https://www.googleapis.com/auth/drive
                      https://www.googleapis.com/auth/userinfo.email
                      https://www.googleapis.com/auth/userinfo.profile
                      https://www.googleapis.com/auth/calendar
                      https://www.googleapis.com/auth/calendar.readonly"
                      data-clientid="<?php echo trim(Yii::app()->settings->googleClientId) ?>"
                      data-redirecturi="postmessage"
                      data-accesstype="offline"
                      data-cookiepolicy="single_host_origin"
                      data-callback="signInCallback">
                </span>
            </div>
            <div id="result"></div>
        </div>
        <?php } ?>
    <?php }else{ ?>
        <div id="login-box">
            <div id="error-message">
                Google Integration is not enabled for this instance of X2Engine.  Please contact an administrator.
            </div>
            <br />
            <a class='x2-button' href='<?php echo $this->createUrl('login'); ?>'>Return to Login Screen</a>
        </div>
    <?php }
    ?>

    <div class="row" style="margin-top:10px;text-align:center;">
        <?php
//        echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/google_icon.png" id="google-icon" /> '.Yii::t('app', 'Sign in with Google'), (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://').
//                ((substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.') ? substr($_SERVER['HTTP_HOST'], 4) : $_SERVER['HTTP_HOST']).
//                $this->createUrl('/site/googleLogin'), array('class' => 'x2touch-link'));
        ?>
    </div>
</div>
</div>
</div>
<?php
$this->renderPartial ('loginCompanyInfo');
?>
</div>
<script type="text/javascript">
function signInCallback(authResult) {
  if (authResult['code']) {

    // Hide the sign-in button now that the user is authorized, for example:
    $('#signinButton').attr('style', 'display: none');
    $('#result').html('<div><div class="loading-icon" style="vertical-align:middle;"></div> <span><b>Logging you in...</b></span></div>');
    // Send the code to the server
    $.ajax({
      type: 'POST',
      url: 'storeToken',
      contentType: 'application/octet-stream; charset=utf-8',
      success: function(result) {
        window.location=window.location;
      },
      processData: false,
      data: authResult['code']
    });
  } else if (authResult['error']) {
    // There was an error.
    // Possible error codes:
    //   "access_denied" - User denied access to your app
    //   "immediate_failed" - Could not automatially log in the user
    // console.log('There was an error: ' + authResult['error']);
  }
}
</script>
