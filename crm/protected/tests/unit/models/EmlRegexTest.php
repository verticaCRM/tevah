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

Yii::import('application.models.*');
Yii::import('application.components.EmlParse');
Yii::import('application.components.util.FileUtil');

/**
 * Tests to run on the EmlRegex class. No fixtures are needed and the data is
 * pretty much static, so it needn't be a child class of CDbTestCase
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmlRegexTest extends X2TestCase {

	public $ignoreFilesMatching = '/(.*_.*|^\.[a-z]{3})$/';
	
	/**
	 * Test regex for each email
	 */
	public function testHeadRE() {
		// Obtain the test emails and parse them
		$patterns = X2Model::model('EmlRegex')->findAll();
		$ignoreFilesMatching = $this->ignoreFilesMatching;
		$emlFiles = array_filter(scandir(Yii::app()->basePath . FileUtil::rpath('/tests/data/email/')), function($e) use($ignoreFilesMatching) {
					return !in_array($e, array('..', '.'))&& !preg_match($ignoreFilesMatching,$e);
				});
		$emls = array();
		foreach ($emlFiles as $emlFile) {
			$rawEmail = file_get_contents(Yii::app()->basePath . FileUtil::rpath('/tests/data/email/') . $emlFile);
			$emls[$emlFile] = new EmlParse($rawEmail);
		}
		// Test each original email's raw content for the pattern. At least one of them should match.
		foreach ($emls as $emlFile => $eml) {
			if(VERBOSE_MODE) echo "Using content from raw email file $emlFile:\n";
			$matched = false;
			foreach ($patterns as $pattern) {
				if(VERBOSE_MODE) echo "Testing with pattern \"{$pattern->groupName}\"\n"; // : {$pattern->fwHeader}
				if ($matches = $pattern->matchHeader($eml->getBody())) {
					$matched = true;
                    if(VERBOSE_MODE) echo "Forwarded header pattern {$pattern->groupName} matched.\n";
				}
			}
			if (!$matched) {
				if(VERBOSE_MODE) echo "\n-----\nThe body that failed to match was:\n-----\n";
				print_r($eml->getBody())."\n";
			}
			// At least one of the email patterns should match. If not, the test
			// should fail.
			$this->assertTrue($matched);
		}
	}

	/**
	 * Test title stripping down to "Developer Person" full name
	 */
	public function testFullName() {
		$correctName = array('Developer', 'Person');
		// Put any silly combination of titles (prefix/suffix) in here:
		$namesWithTitles = array(
			'Mr. Developer Person',
			'Mrs. Developer Person',
			'Developer Person, PhD',
			'Ambassador Developer Person',
			'Developer Person DDS',
			'Coach Developer Person',
			'Person, Developer'
		);
		foreach ($namesWithTitles as $name) {
			$this->assertEquals($correctName,EmlRegex::fullName($name));
		}
	}
}

?>
