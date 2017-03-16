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

Yii::import('application.components.util.FileUtil');
Yii::import('application.modules.media.models.Media');

/**
 * Test case for the {@link Media} model class.
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package application.tests.unit.modules.media.models
 */
class MediaTest extends X2DbTestCase {
	
	public static function referenceFixtures(){
		return array(
			'media' => 'Media'
		);
	}
	
	public function getRootPath() {
		return realpath(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..');
	}
	
	public function testFilesystem() {
		$image = $this->media('bg');
		$this->assertNotEquals(null,$image->path,'Failed asserting valid path for media item "bg"');
		$this->assertFileExists($image->path);
	}
        
        public function testGetFilePath() {
            $image = $this->media("bg");
            $expected = implode(DIRECTORY_SEPARATOR, array('uploads', $image->fileName));
            $this->assertEquals($expected, Media::getFilePath(null, $image->fileName));
        }
        
        public function testFileExists() {
            $image = $this->media("bg");
            $this->assertTrue($image->fileExists());
            $image = $this->media("testfile");
            $this->assertFalse($image->fileExists());
        }
        
        public function testGetImage() {
            $image = $this->media('bg');
            $this->assertTrue($image->fileExists());
            $this->assertTrue($image->isImage());
            $expected = '<img class="attachment-img" src="'.Yii::app()->baseUrl.'/uploads/'.$image->fileName.'" alt="" />';
            $imageTag = $image->getImage();
            $this->assertEquals($expected, $imageTag);
        }
        
        public function testGetPath() {
            $source = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'tests', 'data', 'media', 'testfile.txt'));
            $dest = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '..', 'uploads', 'media', 'admin', 'testfile.txt'));
            FileUtil::ccopy($source, $dest);
            $dest = realpath($dest);
            $testfile = $this->media("testfile");

            $this->assertEquals($dest, $testfile->getPath());
            
            unlink($dest);
        }
	
        public function testDeleteUpload() {
            $source = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, 'tests', 'data', 'media', 'testfile.txt'));
            $dest = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '..', 'uploads', 'media', 'admin', 'testfile.txt'));
            FileUtil::ccopy($source, $dest);
            $dest = realpath($dest);
            $testfile = $this->media("testfile");
            
            $this->assertFileExists($dest);
            $testfile->delete();
            $this->assertFileNotExists($dest);
        }
        
	public function testResolveMimetype() {
		$image = $this->media('bg');
		$mt = $image->resolveType();
		$this->assertStringStartsWith('image/', $mt);
		$this->assertStringStartsWith('image/',Yii::app()->db->createCommand()->select('mimetype')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar());
	}
	
	public function testResolveSize() {
		$image = $this->media('bg');
		$this->assertEquals(97724,$image->resolveSize());
		$this->assertEquals(97724,Yii::app()->db->createCommand()->select('filesize')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar());
	}
	
	public function testResolveDimensions() {
		$this->assertEquals(1,extension_loaded('gd'));
		$image = $this->media('bg');
		$this->assertEquals(array('height'=>682,'width'=>1024),CJSON::decode($image->resolveDimensions()));
		$this->assertEquals(array('height'=>682,'width'=>1024),CJSON::decode(Yii::app()->db->createCommand()->select('dimensions')->from('x2_media')->where("id=:id",array(':id'=>$image->id))->queryScalar()));
	}
        
        public function testGetFmtDimensions() {
            $image = $this->media('bg');
            $this->assertEquals('1024 x 682', $image->getFmtDimensions());
        }

        public function testToBytes() {
            $fn = TestingAuxLib::setPublic('Media', 'toBytes');
            $testSizes = array(
                '3PB' => 3 * pow(1024, 5),
                '1g' => 1024 * 1024 * 1024,
                '2m' => 2 * 1024 * 1024,
                '1MB' => 1024 * 1024,
                '1k' => 1024,
                666 => 666,
            );
            foreach ($testSizes as $readable => $bytes) {
                $this->assertEquals($bytes, $fn (array($readable)));
            }
        }
}

?>
