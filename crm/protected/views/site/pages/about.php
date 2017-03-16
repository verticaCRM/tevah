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


Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/about-page.css');

$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->settings->appName . ' - ' . Yii::t('app','About');
?>
<?php
$logo = Yii::app()->baseUrl.'/images/x2engine.png';

?>
<div id='icon-container'>
<?php
if (ThemeGenerator::isThemed()) {
	echo X2Html::x2icon('x2-logo-square', array('id' => 'x2-about-logo'));
} else {
	echo CHtml::image($logo,'');
}
?>
</div>
<?php
Yii::app()->clientScript->registerScript('loadJqueryVersion',"$('#jqueryVersion').html($().jquery);",CClientScript::POS_READY);
?>

<div class='center-column-container form left' >
    <b><?php echo Yii::t('app','Version')." ".Yii::app()->params->version;?></b><br>
	<b><?php echo Yii::app()->getEditionLabel(true); ?></b><br>
    <?php if(Yii::app()->params->isAdmin && Yii::app()->contEd('pro')){ echo '<strong>'.Yii::t('app','License').'</strong>&nbsp;<tt>'.Yii::app()->settings->unique_id.'</tt><br />'; } ?>
	<?php echo Yii::app()->dateFormatter->formatDateTime(Yii::app()->params->buildDate,'long',null); ?>.<br><br>
	<?php
	if(Yii::app()->edition==='opensource')
		echo Yii::t('app','X2Engine is an open source Customer Relationship Management application <br>designed by John Roberts and licensed under the {link}.',array(
			'{link}'=>CHtml::link(Yii::t('app','GNU Affero GPL v3'),Yii::app()->getBaseUrl().'/LICENSE.txt',array('title'=>Yii::t('app','GNU Affero General Public License version 3')))
		));
	else
		//echo Yii::t('app','X2Engine is a Customer Relationship Management application <br>designed by John Roberts.');
		echo Yii::t('app','X2Engine Inc. is a Santa Cruz California software corporation founded<br>in 2011 by John Roberts.');
	?>
	<?php //echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/x2footer.png','',array('style'=>'display:block;margin:5px 0;')); ?><br><br>
	<div style="clear:both"><?php echo Yii::t('app','For customer and community support:');?> <a href="http://www.x2engine.com/">www.x2engine.com</a></div>
	<div id="about-intro">
		<div class="span-4">
		<h4><?php echo CHtml::encode (Yii::t('app','Address')); ?></h4>
		<ul>
			<li>X2Engine Inc.</li>
			<li>PO Box 66752</li>
			<li>Scotts Valley, California 95067</li>
			<li>USA</li>
			<li><a href="http://www.x2engine.com/">www.x2engine.com</a></li>
		</ul>
		</div>
		<div class="span-4">
		<h4><?php echo CHtml::encode (Yii::t('app','Core Team')); ?></h4>
		<ul>
			<li>John Roberts</li>
			<li>Derek Mueller</li>
			<li>Tony Zavala</li>
			<li>Raymond Colebaugh</li>
			<li>Alex Rowe</li>
			<li>Jake Houser</li>
		</ul>
		</div><br>
		<h4 class="clear"><?php echo CHtml::encode (Yii::t('app','Contributors'));?></h4>
		<ul class="inline">
			<li>Demitri Morgan</li>
			<li>Sandra Tam</li>
            <li>Matthew Pearson</li>
			<li>James Thomas</li>
			<li>Andrew Hoffman</li>
			<li>Zach Louden</li>
			<li>Steve Lance</li>
			<li>DJ Visbal</li>
			<li>John Mendonca</li>
			<li>Xinyi Lin</li>
			<li>Haley Sedam</li>
			<li>Ben Hoehn</li>
			<li>Bill Posner</li>
			<li>Chris Hodges</li>
			<li>Bastian Pfaff</li>
		</ul>
	</div>
	<hr>
	<div id="about-credits">
		<!--<div class="about-list" style="height:450px;width:auto;overflow-y:scroll;border:1px solid #ddd;padding:10px;"></div>
		<hr>-->
		<h4><?php echo Yii::t('app','Version Info'); ?></h4>
		<ul>
			<li>X2Engine: <?php echo Yii::app()->params->version;?></li>
			<!--<?php echo Yii::t('app','Build'); ?>: 1234<br>-->
			<li>Yii: <?php echo Yii::getVersion(); ?></li>
			<li>jQuery: <span id="jqueryVersion"></span></li>
			<li>PHP: <?php echo phpversion(); ?></li>
			<!--jQuery Mobile: 1.0b2<br>-->
		</ul>
		<h4><?php echo Yii::t('app','Code Base'); ?></h4>
		<ul>
			<li>GitHub: <a href="https://github.com/X2Engine/X2Engine" target="_blank">https://github.com/X2Engine/X2Engine</a></li>
			<!--BitBucket: <a href="https://bitbucket.org/X2Engine/X2Engine" target="_blank">https://bitbucket.org/X2Engine/X2Engine</a></li>-->
		</ul>

		<h4><?php echo Yii::t('app','Plugins/Extensions'); ?></h4>
		<ul>
			<li>Google API PHP Client:
				<a href="http://code.google.com/p/google-api-php-client/" target="_blank"><?php echo Yii::t('app','Project'); ?></a>
				<a href="http://www.apache.org/licenses/" target="_blank" class="no-underline" title="Apache License 2.0">[Apache]</a></li>
			<li>Modernizr:
				<a href="http://modernizr.com" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://modernizr.com/license/" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
			<li>Spectrum: <a href="http://bgrins.github.io/spectrum/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="https://github.com/bgrins/spectrum/blob/master/LICENSE" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>colResizable: <a href="http://quocity.com/colresizable/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>formatCurrency: <a href="http://code.google.com/p/jquery-formatcurrency/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
			<li>phpMailer: <a href="http://phpmailer.worxware.com/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
			<li>FullCalendar: <a href="http://arshaw.com/fullcalendar/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<!--<li>JS SHA-256: <a href="http://www.webtoolkit.info/javascript-sha256.html" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.webtoolkit.info/license" target="_blank" class="no-underline" title="License">[License]</a></li>-->
			<li>CKEditor: <a href="http://www.ckeditor.com/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" class="no-underline" title="Lesser GPL License">[LGPL]</a></li>
			<li>CFile Class:
				<a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php echo Yii::t('app','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>EZip Class:
				<a href="http://www.yiiframework.com/extension/cfile" target="_blank"><?php echo Yii::t('app','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>CSaveRelationsBehavior Class:
				<a href="http://www.yiiframework.com/extension/save-relations-ar-behavior/" target="_blank"><?php echo Yii::t('app','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/BSD-3-Clause" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
			<li>ERememberFiltersBehavior Class:
				<a href="http://www.yiiframework.com/extension/remember-filters-gridview/" target="_blank"><?php echo Yii::t('app','Yii Extension'); ?></a>
				<a href="http://www.opensource.org/licenses/BSD-3-Clause" target="_blank" class="no-underline" title="New BSD License">[New BSD]</a></li>
			<li>qTip2: <a href="http://craigsworks.com/projects/qtip2/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>jStorage: <a href="http://www.jstorage.info/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.jstorage.info/static/license.txt" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
			<li>FineDiff: <a href="http://www.raymondhill.net/finediff/" target="_blank"><?php echo Yii::t('app','Developer'); ?></a>
				<a href="http://www.opensource.org/licenses/mit-license.php" target="_blank" class="no-underline" title="MIT License">[MIT]</a></li>
		</ul>
	</div>
	<hr>
	<div id="about-legal">
<?php
	// Yii::app()->params->edition = 'opensource';
?>
		<a href="http://www.x2engine.com/"><?php echo Yii::t('app','Powered by X2Engine');?></a>. Copyright &copy; 2011-<?php echo date('Y'); ?> X2Engine Inc.<br>
		<div style="margin-top:5px;"><?php echo CHtml::link(CHtml::image(Yii::app()->getBaseUrl().'/images/powered_by_x2engine.png',''),'http://www.x2engine.com/'); ?></div>
		<?php if(Yii::app()->params->edition==='opensource'): ?>
			<?php echo Yii::t('app','Released as free software without warranties under the');?> <a href="<?php echo Yii::app()->getBaseUrl(); ?>/LICENSE.txt" title="GNU Affero General Public License version 3">GNU Affero GPL v3</a>.<br><br>

			<b>The interactive user interfaces in modified source and object code versions
			of this program must display Appropriate Legal Notices, as required under
			Section 5 of the GNU Affero General Public License version 3. In accordance
			with Section 7(b) of the GNU General Public License version 3, these
			Appropriate Legal Notices must retain the display of the "Powered by X2Engine"
			logo. If the display of the logo is not reasonably feasible for technical reasons,
			the Appropriate Legal Notices must display the words "Powered by X2Engine".
			X2Engine and X2Engine are trademarks of X2Engine Inc.<br><br>

		<?php else: ?>
			<b>X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
			to install and use this Software for your internal business purposes.
			You shall not modify, distribute, license or sublicense the Software.
			Title, ownership, and all intellectual property rights in the Software belong
			exclusively to X2Engine.<br><br>
		<?php endif; ?>
		THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
		EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
		MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
		</b>
	</div><br>
</div>
<div id="about-map">
<!--<a title="<?php echo Yii::t('app','Our office in downtown Santa Cruz'); ?>" target="_blank" href="https://maps.google.com/maps?q=501+Mission+Street+Suite+%235+Santa+Cruz,+California+95060+USA&hl=en&sll=37.269174,-119.306607&sspn=14.636891,27.114258&t=h&hnear=501+Mission+St+%235,+Santa+Cruz,+California+95060&z=8">-->
<?php //echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/office.png',''); ?>
</a>
<?php //echo Yii::t('app','X2Engine Inc. is headquartered in beautiful Santa Cruz, California. We really enjoy meeting customers and partners whenever possible and encourage you to visit our offices when you find yourself in the San Francisco bay area.');
?>
</div>
<div class='clear'></div>












