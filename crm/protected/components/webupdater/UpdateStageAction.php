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

Yii::import('application.components.webupdater.*');

/**
 * Generic action class for performing a stage of an update/upgrade via AJAX.
 *
 * @package application.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdateStageAction extends WebUpdaterAction {

    public function behaviors() {
        return array(
			'UpdaterBehavior' => array(
				'class' => 'application.components.UpdaterBehavior',
                'errorCode' => 200, // Simple UI-based error reporting
                'handleErrors' => true,
                'handleExceptions' => true
			)
		);
    }

	public function run($stage,$scenario,$version,$uniqueId,$autoRestore=0){
        $this->scenario = $scenario;
		if(Yii::app()->request->isAjaxRequest){
            switch($stage) {
                case 'download':
                    // Download the update/upgrade package
                    $this->downloadPackage($version,$uniqueId,$this->edition);
                    $this->respond(Yii::t('admin','Successfully downloaded package.'));
                    break;
                case 'enact':
                    // The final and most critical part of the update, where
                    // files are copied and database commands are run.
                    $autoRestore = ($autoRestore === 'true') ? true : false;
                    $this->uniqueId = $uniqueId;
                    $this->enactChanges($autoRestore);
                    $this->respond(Yii::t('admin', 'All done.'));
                    break;
                case 'check':
                    // Retrive the manifest of changes pre-emptively, or load
                    // the existing manifest, for review. Check the installation
                    // and server environment for compatibility.
                    if(!$this->checkIf('manifestAvail',false)) {
                        // No package has yet been downloaded; if the package
                        // were present, but the manifest not ready for use,
                        // UpdaterBehavior would have thrown an exception by now
                        // (the 'verify' stage, which performs the necessary
                        // checks, should have been run first).
                        $data = $this->getUpdateData($version,$uniqueId,$this->edition);
                        if(array_key_exists('errors',$data)) {
                            // The update server doesn't like us.
                            $this->respond($data['errors'],1);
                            break;
                        }
                        $this->manifest = $data;
                    }

                    $cStatus = $this->getCompatibilityStatus();
                    $this->response['allClear'] = $cStatus['allClear'];
                    $this->response['manifest'] = $this->manifest;
                    $this->response['compatibilityStatus'] = $cStatus;
                    $this->respond($this->renderCompatibilityMessages('strong',array('style'=>'text-decoration:underline;')));
                    break;
                case 'unpack':
                    // Unpack the zip file
                    $this->unpack();
                    $this->respond(Yii::t('admin','Successfully extracted package.'));
                    break;
                case 'verify':
                    try{
                        // Check package contents.
                        $this->checkIf('packageApplies');
                        // The JSON returned by this action should include all the
                        // necessary data to render warning messages concerning files.
                        $this->response['statusCodes'] = array(
                            'present' => UpdaterBehavior::FILE_PRESENT,
                            'corrupt' => UpdaterBehavior::FILE_CORRUPT,
                            'missing' => UpdaterBehavior::FILE_MISSING
                        );
                        $this->response['filesStatus'] = $this->filesStatus;
                        $this->response['filesByStatus'] = $this->filesByStatus;
                        $this->respond(Yii::t('admin', 'Files checked.'), !((bool) $this->files) || $this->filesStatus[UpdaterBehavior::FILE_CORRUPT] > 0 || $this->filesStatus[UpdaterBehavior::FILE_MISSING] > 0);
                    }catch(Exception $e){
                        $this->respond($e->getMessage(), true);
                    }
                    break;
            }
		}else{
			$this->respond('Update requests must be made via AJAX.',true);
		}
	}
}

?>
