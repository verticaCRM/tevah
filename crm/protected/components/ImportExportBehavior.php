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
 * Behavior for dealing with data files directly on the server while avoiding
 * directory traversal and publicly visible files.
 * 
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ImportExportBehavior extends CBehavior {

    /**
     * Sends the file to the web client upon request
     * @param type $file
     * @return false if send file failed (if successful, script is terminated)
     */
    public function sendFile($file, $deleteAfterSend=false){
        if(!preg_match('/(\.\.|\/)/', $file)){
            $file = Yii::app()->file->set($this->safePath($file));
            return $file->send(false, false, $deleteAfterSend);
        }
        return false;
    }

    /**
     * Returns a file path that is within the protected folder, to protect data
     * @param type $filename
     * @return type
     */
    public function safePath($filename = 'data.csv'){
        return implode(DIRECTORY_SEPARATOR, array(
                    Yii::app()->basePath,
                    'data',
                    $filename
                ));
    }

}

?>
