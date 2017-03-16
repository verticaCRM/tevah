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

/* @edition:pro */

/*
Legacy web tracker. New version: webTracker.php
*/

$url = '';
if(!empty($_SERVER['HTTP_REFERER'])) {
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    
    // get referring URL's GET params
    $referer_GET = array();
    if(isset($referer['query'])) {
        parse_str($referer['query'],$referer_GET);
    }

    // get referring URL
    $url = isset($referer['host'])? $referer['host'] : '';
    $url .= (isset($referer['path'])? $referer['path'] : '');
}

$entryScript = (defined ('FUNCTIONAL_TEST') && constant ('FUNCTIONAL_TEST') ? 
            'index-test.php' : 'index.php'); 
$protocol = !empty ($_SERVER['HTTPS']) ? 'https' : 'http';

// use the link key first, then look at cookies (so marketing campaigns override generic tracking)
if(isset($referer_GET['get_key']) && ctype_alnum($referer_GET['get_key'])) {
	Header('Location: '.$protocol.'://'.$_SERVER['HTTP_HOST'].'/'.$entryScript.
        '/api/webListener?x2_key='.$referer_GET['get_key'].'&url='.urlencode($url));
} elseif(isset($_COOKIE['x2_key']) && ctype_alnum($_COOKIE['x2_key'])) {
	Header('Location: '.$protocol.'://'.$_SERVER['HTTP_HOST'].'/'.$entryScript.'/api/webListener?'.
        '&url='.urlencode($url));
} else {
	/* In this last effort to work properly, attempt to find the root parent window
	 * (i.e. in the case that the script is being called within two or more levels
	 * of nested iframes). It's still not guaranteed to work, if any of the iframes
	 * are on different domains.
	 */
?><html>
<head></head>
<body>
<script type="text/javascript" >
var thiswindow = window, i = 0;
while(thiswindow != top && i < 10) {
	thiswindow = thiswindow.parent;
	i++;
}
try {
    var getparam = /(https?:\/\/[\.\w\/\?]+)[\?&]x2_key=(\w+)/.exec(thiswindow.location.href);
} catch (e) {
    var getparam = null;
}
if(getparam != null) {
	var xmlhttp;
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open(
        "GET",'index.php/api/webListener?url='+
        encodeURIComponent(getparam[1])+'&x2_key='+encodeURIComponent(getparam[2]),true);
	xmlhttp.send();
}
</script>
</body>
</html>
<?php 
}
?>
