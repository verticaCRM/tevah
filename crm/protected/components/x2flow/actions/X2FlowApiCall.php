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
 * X2FlowAction that calls a remote API
 *
 * @package application.components.x2flow.actions
 */
class X2FlowApiCall extends X2FlowAction {

    /**
     * This will only ever by false during unit tests 
     */
    private static $_makeRequest;

    /**
     * Allows request behavior of this class to be toggled during unit tests
     */
    public function getMakeRequest () {
        if (!isset (self::$_makeRequest)) {
            self::$_makeRequest = true;
        }
        return self::$_makeRequest;
    }
    
    public $title = 'Remote API Call';
    //public $info = 'Call a remote API by requesting the specified URL. You can specify the request type and any variables to be passed with the request. To improve performance, the request will be put into a job queue unless you need it to execute immediately.';
    public $info = 'Call a remote API by requesting the specified URL. You can specify the request type, HTTP headers, and any variables to be passed with the request.';

    public function paramRules(){
        $httpVerbs = array(
            'GET' => Yii::t('studio', 'GET'),
            'POST' => Yii::t('studio', 'POST'),
            'PUT' => Yii::t('studio', 'PUT'),
            'DELETE' => Yii::t('studio', 'DELETE')
        );

        return array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'modelClass' => 'API_params',
            'options' => array(
                array('name' => 'url', 'label' => Yii::t('studio', 'URL')),
                array('name' => 'method', 'label' => Yii::t('studio', 'Method'), 'type' => 'dropdown', 'options' => $httpVerbs),
                array('name' => 'attributes', 'optional' => 1),
                array('name' => 'headers', 'type' => 'attributes', 'optional' => 1),
            // array('name'=>'immediate','label'=>'Call immediately?','type'=>'boolean','defaultVal'=>true),
                ));
    }

    /**
     * @param array $headerRows 
     * @return array
     */
    public function getHeaders ($headerRows, $params) {
        $headers = array();
        foreach ($headerRows as $row) {
            $name = X2Flow::parseValue ($row['name'], '', $params, false);
            $value = X2Flow::parseValue ($row['value'], '', $params, false);
            $headers[$name] = $value;
        }
        return $headers;
    }

    /**
     * @param array $headers 
     * @return string
     */
    public function formatHeaders ($headers) {
        $formattedHeaders = array ();
        foreach ($headers as $name => $value) {
            $formattedHeaders[] = $name.': '.$value;
        }
        return $formattedHeaders;
    }

    /**
     * Override parent method to add url validation. Present warning to user on flow save if
     * specified url points to same server as the one X2Engine is hosted on.
     */
    public function validateOptions(&$paramRules,$params=null,$showWarnings=false) {
        list ($success, $message) = parent::validateOptions ($paramRules, $params, $showWarnings);
        if (!$success) return array ($success, $message);
        $url = $this->config['options']['url']['value'];

        if (YII_UNIT_TESTING) {
            $hostInfo = 'localhost';
        } else {
            $hostInfo = preg_replace ('/^https?:\/\//', '', Yii::app()->getAbsoluteBaseUrl ());
        }

        $url = preg_replace ('/^https?:\/\//', '', $url);
        if ($showWarnings && 
            gethostbyname ($url) === gethostbyname ($hostInfo)) {

            return array (
                self::VALIDATION_WARNING, 
                Yii::t(
                    'studio',
                    'Warning: The url specified in your Remote API Call flow action points to the '.
                    'same server that X2Engine is hosted on. This could mean that this flow makes '.
                    'a request to X2Engine\'s API. Calling X2Engine\'s API from X2Flow is not '.
                    'advised since it could potentially trigger this flow, resulting in an '.
                    'infinite loop.'));
        } else {
            return array (true, $message);
        }
    }

    /**
     * Try to prevent api requests to X2Engine's api. This is a looser check than
     * validateOptions (). ValidateOptions () can produce false positives which we wouldn't want
     * to have effect flow execution.
     */
    private function validateUrl ($url) {
        if (YII_DEBUG && YII_UNIT_TESTING) {
            $absoluteBaseUrl = 'http://localhost';
        } else {
            $absoluteBaseUrl = Yii::app()->getAbsoluteBaseUrl ();
        }
        $absoluteBaseUrl = preg_replace ('/^https?:\/\//', '', $absoluteBaseUrl);
        $url = preg_replace ('/^https?:\/\//', '', $url);
        if (preg_match ("/^".preg_quote ($absoluteBaseUrl, '/')."/", $url)) {
            return false;
        }
        return true;
    }

    public function execute(&$params){
        $url = $this->parseOption('url', $params);
        if(strpos($url,'http')===false){
            $url = 'http://'.$url;
        }
        $method = $this->parseOption('method', $params);

        if($this->parseOption('immediate', $params) || true){
            $headers = array ();
            $httpOptions = array(
                'timeout' => 5, // 5 second timeout
                'method' => $method,
            );
            if(isset($this->config['attributes']) && !empty($this->config['attributes'])){

                if (isset ($this->config['headerRows'])) {
                    $headers = $this->getHeaders ($this->config['headerRows'], $params);
                } 

                $data=array();
                foreach($this->config['attributes'] as $param){
                    if(isset($param['name'],$param['value'])){
                        $data[$param['name']]=X2Flow::parseValue(
                            $param['value'],'',$params, false);
                    }
                }
                if($method === 'GET'){
                    $data = http_build_query($data);
                    // make sure the URL is ready for GET params
                    $url .= strpos($url, '?') === false ? '?' : '&'; 
                    $url .= $data;
                }else{
                    // set up default header for POST style data
                    if (!isset ($headers['Content-Type']))
                        $headers['Content-Type'] = 'application/x-www-form-urlencoded'; 

                    if (preg_match ("/application\/json/", $headers['Content-Type'])) {
                        $data = CJSON::encode ($data);
                        $httpOptions['content'] = $data;
                    } else {
                        $data = http_build_query($data);
                        $httpOptions['content'] = $data;
                    }

                    // set up default header for POST style data
                    if (!isset ($headers['Content-Length']))
                        $headers['Content-Length'] = strlen($data);
                }
            }
            if (count ($headers)) {
                $formattedHeaders = $this->formatHeaders ($headers);
                $httpOptions['header'] = implode("\r\n", $formattedHeaders);
            }

            $context = stream_context_create(array('http' => $httpOptions));
            if (!$this->getMakeRequest ()) {
                return array (true, array_merge (array ('url' => $url), $httpOptions));
            } else {
                if (!$this->validateUrl ($url)) {
                    return array(
                        false, 
                        Yii::t('studio', 'Requests cannot be made to X2Engine\'s API from X2Flow.')
                    );
                }
                $response = @file_get_contents($url, false, $context);
                if ($response !== false) {
                    if (YII_DEBUG && YII_UNIT_TESTING) {
                        return array(true, $response);
                    } else {
                        return array(true, Yii::t('studio', "Remote API call succeeded"));
                    }
                }else{
                    if (YII_DEBUG && YII_UNIT_TESTING) {
                        return array(false, var_dump ($http_response_header));
                    } else {
                        return array(false, Yii::t('studio', "Remote API call failed!"));
                    }
                }
            }
        }
    }

}
