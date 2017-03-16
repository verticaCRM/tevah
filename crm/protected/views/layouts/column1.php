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

$this->beginContent('//layouts/main');
$themeURL = Yii::app()->theme->getBaseUrl();

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

?>
<div id="content" class="single-column-layout-content">
	<!-- content -->
	<?php echo $content; ?>
</div>
<?php $this->endContent();
