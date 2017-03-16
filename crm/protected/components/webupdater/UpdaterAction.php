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
 * Action for the updates/upgrades control page.
 * @package application.components.webupdater
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UpdaterAction extends WebUpdaterAction {

	/**
	 * Runs user-interface-related updater actions.
     *
     * @param string $scenario A keyword specifying what exactly is being done
     * @param integer $redirect If true, and the scenario is "delete", redirect
     *  to the previous page (referrer); if true, and the scenario is anything
     *  but "delete", redirect to the package download URL. If false, it does
     *  nothing unless the scenario is "delete".
	 */
	public function run($scenario = 'update',$redirect=0){
        // Get configuration variables:
        $configVars = $this->configVars;
        extract($configVars);

        if(!in_array($scenario,array('update','upgrade','delete')))
            throw new CHttpException(400);
        if($scenario == 'delete'){
            // Delete the package that's on the server
            $this->cleanUp();
            if($redirect && !empty($_SERVER['HTTP_REFERER'])){
                $this->controller->redirect($_SERVER['HTTP_REFERER']);
            }else{
                $this->respond('');
            }
        }

        $unique_id = $this->uniqueId;
        $edition = $this->edition;
        $this->scenario = $scenario;
        $ready = $this->checkIf('packageExists',false);
        $latestVersion = $version;
        $viewParams = compact('scenario','unique_id','edition','version','ready','latestVersion');

        // Check for an existing package that has been extracted, in which case
        // it isn't necessary to make any requests to the update server, but
        // it is necessary to perform additional checks (which will be done
        // via ajax in the updater view)
        if($ready) {
            $viewParams['latestVersion'] = $this->manifest['targetVersion'];
            $this->controller->render('updater',$viewParams);
            return;
        }

        // Check to see if there's anything new available. FileUtil should be
        // available by this point in time (since the old safeguard methods
        // in AdminController would take care of that for much older
        // versions) so it's safe to auto-download at this point.
        $updaterCheck = $this->getLatestUpdaterVersion();
        if($updaterCheck){
            $refreshCriteria = version_compare($updaterVersion,$updaterCheck) < 0
                    || $this->backCompatHooks($updaterCheck);

            if($refreshCriteria){
                $this->runUpdateUpdater($updaterCheck, array('updater', 'scenario' => $scenario));
            }

            $this->output(Yii::t('admin','The updater is up-to-date and safe to use.'));
        }else{
            // Is it the fault of the webserver?
            $this->controller->checkRemoteMethods();
            // Redirect to updater with the appropriate error message.
            $msg = Yii::t('admin', 'Could not connect to the updates server, or an error occurred on the updates server.');
            $this->output($msg,1);
            $this->controller->render('updater', array(
                'scenario' => 'error',
                'message' => Yii::t('admin', 'Could not connect to the updates server, or an error occurred on the updates server.'),
            ));
        }

        $latestVersion = $this->checkUpdates(true);
        $viewParams['latestVersion'] = $latestVersion;
        if(version_compare($version, $latestVersion) < 0){ // If the effective version is not the most recent version
            if($scenario == 'update'){
                // Update.
                $this->controller->render('updater',$viewParams);
            }else{ 
                // Upgrade.
                //
                // Theoretically, legacy upgrade packages could be possible to
                // use, but at this stage the updater utility itself has been
                // updated, and so it won't be safe to use them because they
                // might only be compatible with an earlier version of the
                // updater.
                $this->controller->render('updater', array(
                    'scenario' => 'error',
                    'message' => 'Update required',
                    'longMessage' => Yii::t('admin',"Before upgrading, you must update to the latest version ({latestver}).",array('{latestver}'=>$latestVersion)).' '.CHtml::link(Yii::t('app', 'Update'), array('/admin/updater','scenario'=>'update'), array('class' => 'x2-button'))
                ));
            }
        }else{ // If at latest version already.
            if($scenario=='update'){
                // Display a success message
                Yii::app()->session['versionCheck'] = true;
                $this->controller->render('updater', array(
                    'scenario' => 'message',
                    'version' => $version,
                    'message' => Yii::t('admin', 'X2Engine is at the latest version!'),
                    'longMessage' => $redirect ? Yii::t('admin','Download cancelled (no update package necessary)') : ''
                ));
            }else{
                // Upgrade.
                //
                // First, remove database backup; if it exists, the user most
                // likely came here immediately after updating to the latest
                // version, in which case the backup is outdated (applies to the
                // old version and not the current state of the database).
                $this->removeDatabaseBackup();
                $this->controller->render('updater', $viewParams);
            }
        }
    }

}

?>
