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
 * Checks uploaded files in the web request for invalid extensions.
 *
 * Intended as a catch-all for attempted arbitrary file type uploads.
 * 
 * @package application.components.filters
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class FileUploadsFilter extends CFilter {

    /**
     * Regular expression for blacklisted files.
     *
     * Does not match end of string to prevent circumvention via the methods
     * described in OWASP's Unrestricted File Upload article:
     * 
     * https://www.owasp.org/index.php/Unrestricted_File_Upload#Using_Black-List_for_Files.E2.80.99_Extensions
     */
    const EXT_BLACKLIST = '/\.\s*(?P<ext>html|htm|js|jsb|mhtml|mht|xhtml|xht|php|phtml|php3|php4|php5|phps|shtml|jhtml|pl|py|cgi|exe|scr|dll|msi|vbs|bat|com|pif|cmd|vxd|cpl|ini|conf|cnf|key|iv|htaccess)\b/i';

    /**
     * List of mime-types that uploaded files should never have
     * @var type
     */
    private $_mimeBlacklist = array(
        'text/html', 'text/javascript', 'text/x-javascript',
        'application/x-shellscript', 'application/x-php', 'text/x-php',
        'text/x-python', 'text/x-perl', 'text/x-bash', 'text/x-sh',
        'text/x-csh', 'text/scriptlet', 'application/x-msdownload',
        'application/x-msmetafile'
    );

    /**
     * Returns true if the file is safe to upload.
     *
     * Will use fileinfo if available for determining mime type of the uploaded file.
     * @param array $file
     */
    public function checkFilename($filename){
        if(preg_match(self::EXT_BLACKLIST, $filename,$match)){
            AuxLib::debugLog('Throwing exception for array: '.var_export($_FILES,1));
            throw new CHttpException(403,Yii::t('app','Forbidden file type: {ext}',array('{ext}'=>$match['ext'])));
        }
    }

    public function checkFiles(array $inputs){
   
        foreach($inputs as $fieldName => $input){
            // Structure:
            // [field name] =>
            //      'name' => [name(s)]
            //      'type' => [type(s)]
            //      'tmp_name' => [name(s)]
            //      'error' => [error(s)]
            //      'size' => [size(s)]
            if(!isset($input['name'])){
                throw new CHttpException(400, Yii::t('app', 'Uploaded files must have names.'));
            }elseif(is_array($input['name'])){
                // Multiple files in this input field
                foreach($input['name'] as $name){
                    $this->checkFileName($name);
                }
                if((bool) ($finfo = FileUtil::finfo())) {
                    $types = array();
                    foreach ($input['tmp_name'] as $path) {
                        if(file_exists($path)) {
                            $types[] = finfo_file($finfo, $path, FILEINFO_MIME);
                        }
                    }
                } else {
                    $types = $input['type'];
                }
                if($forbidden = count(array_intersect($types, $this->_mimeBlacklist)) > 0){
                    throw new CHttpException(403, Yii::t('app', 'List of uploaded files includes forbidden MIME types: {types}', array('{types}' => implode(',', $forbidden))));
                }
            }else{
                // One file in this input field
                $this->checkFileName($input['name']);
                if(file_exists($input['tmp_name']) && (bool) ($finfo = FileUtil::finfo())) {
                    $type = finfo_file($finfo, $input['tmp_name'], FILEINFO_MIME);
                } else  {
                    $type = $input['type'];
                }
                if(in_array($type,$this->_mimeBlacklist)) {
                    throw new CHttpException(403, Yii::t('app','Forbidden MIME type for file: {file}',array('{file}'=>$input['name'])));
                }
            }
        }
    }

    protected function preFilter($filterChain){
        if(empty($_FILES)){ // No files to be uploaded
            return true;
        }
        $this->checkFiles($_FILES);
        return true;
    }

}

?>
