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

/**
 * @package application.tests.unit.components
 */
class ThemeGeneratorTest extends X2TestCase {

	public $fixtures = array(
	);

	public $emptyColors = array (
				'background' => '',
				'text' => '',
				'link' => '',
				'content' => '',
				'highlight1' => '',
				'highlight2' => '000',
			);			

	public $normalColors= array(
				'background' => 'FF0000',
				'text' => '#000000',
				'link' => '#0000FF',
				'content' => '00AA00',
				'highlight1' => '000000',
				'highlight2' => '000000',
			);	

	public function testGeneratePalette($colors=null){

		// Test for malformed array input
		if (!$colors) {
			$colors = $this->normalColors;	
		}

		$generated = ThemeGenerator::generatePalette($colors);
		print_r($generated);

		$keys = ThemeGenerator::getProfileKeys();
		$this->assertTrue(in_array('themeName', $keys));
		print_r($keys);

		foreach($keys as $key) {
			if ($key == 'themeName') {
				continue;
			}

			$this->assertArrayHasKey($key, $generated);
		}

		$this->assertCount(count($keys)-1, $generated);

	}

	public function testEmptyColors() {
		$this->testGeneratePalette($this->emptyColors);
	}




}

?>
