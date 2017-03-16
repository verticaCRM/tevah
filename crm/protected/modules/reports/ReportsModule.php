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



class ReportsModule extends X2WebModule {

    public $packages = array();
    
    private $_assetsUrl;

	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'reports.models.*',
			'reports.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
        // Set module specific javascript packages
        $this->packages = array(
            'jquery' => array(),
            'jqplot' => array(
                'basePath' => $this->getBasePath(),
                'baseUrl' => $this->assetsUrl,
                'js' => array(
                    '/js/jqplot/jquery.js',
                    '/js/jqplot/jquery.jqplot.js',
                    /*'/js/jqplot/examples/syntaxhighlighter/scripts/shCore.js',
                    '/js/jqplot/examples/syntaxhighlighter/scripts/shBrushJScript.js',
                    '/js/jqplot/examples/syntaxhighlighter/scripts/shBrushXml.min.js',*/
                    '/js/jqplot/plugins/jqplot.pieRenderer.js',
                    '/js/jqplot/plugins/jqplot.donutRenderer.min.js',
                    '/js/jqplot/plugins/jqplot.barRenderer.min.js',
                    '/js/jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
                    '/js/x2charts.js',
                ),
                'css' => array(
                    '/js/jqplot/jquery.jqplot.css',
                    /*'/js/jqplot/examples/syntaxhighlighter/styles/shCoreDefault.css',
                    '/js/jqplot/examples/syntaxhighlighter/styles/shThemejqPlot.min.css',*/
                    '/css/x2charts.css',
                ),
            )
        );
        if (AuxLib::isIE8 ()) {
            $this->packages['jqplot']['js'][] = '/js/jqplot/excanvas.js';
        }
        
    }
}
