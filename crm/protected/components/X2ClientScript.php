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

Yii::import('application.extensions.NLSClientScript');

/**
 * Custom extension of CClientScript used by the app.
 *
 * @property bool $fullscreen Whether to render in full screen mode
 * @package application.components 
 */
class X2ClientScript extends NLSClientScript {

    private $_admin;
    private $_baseUrl;
    private $_fullscreen;
    private $_isGuest;
    private $_profile;
    private $_scriptUrl;
    private $_themeUrl;
    private $_cacheBuster;
    private $_defaultPackages;

    public function getDefaultPackages () {
        if (!isset ($this->_defaultPackages)) {
            $this->_defaultPackages = array_merge (
                $this->getIEDefaultPackages(), 
                array(
                    'auxlib' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/auxlib.js'
                        )
                    )
                )
            );
        }
        return $this->_defaultPackages;
    }

    public function getIEDefaultPackages() {
        if (AuxLib::getIEVer() >= 9)  {
            return array();
        }
        return array ();
//        return array(
//            'aight' => array(
//                'baseUrl' => Yii::app()->request->baseUrl,
//                'js' => array(
//                    'js/lib/aight/aight.js',
//                ),
//                'depends' => array('jquery'),
//            ),
//        );     
    }

    /**
     * @param string returns cache buster value. Append this value to names of files upon 
     *  registration to avoid retrieving the cached file.
     */
    private function getCacheBuster() {
        if (!isset ($this->_cacheBuster)) {
            if (YII_DEBUG) {
                /*
                Cache is refreshed once per session for debugging. It shouldn't be refreshed 
                every page load or it will cause issues with NLSClientScript.
                */
                if (!isset ($_SESSION['cacheBuster'])) {
                    $_SESSION['cacheBuster'] = ((string) time ());
                }
                // always bust caches in debug mode
                $this->_cacheBuster = $_SESSION['cacheBuster'];
            } else {
                // bust cache on update/upgrade
                $this->_cacheBuster = Yii::app()->params->buildDate;
            }
        }
        return $this->_cacheBuster;
    }
    
    /**
     * Inserts the scripts at the beginning of the body section.
     * @param boolean $includeScriptFiles whether to include external files, or just dynamic scripts
     * @return string the output to be inserted with scripts.
     */
    public function renderOnRequest($includeScriptFiles = false) {
        $html='';
        if($includeScriptFiles) {
            foreach($this->scriptFiles as $scriptFiles) {
                foreach($scriptFiles as $scriptFile)
                    $html.=CHtml::scriptFile($scriptFile)."\n";
            }
        }
        foreach($this->scripts as $script)    // the good stuff!
            $html.=CHtml::script(implode("\n",$script))."\n";

        if($html!=='')
            return $html;
    }

    /**
     * Echoes out registered scripts and the necessary JavaScript to load
     * all prerequisite script files.
     *
     * Useful for loading UI elements via AJAX that require registering scripts.
     */
    public function echoScripts(){
        $cs = $this;
        $scripts = '';
        $endScripts = '';
        foreach($cs->cssFiles as $url => $type){
            $scripts .= '
                if($("head link[href=\''.$url.'\']").length == 0) {
                    $.ajax({type:"GET",url:"'.$url.'"}).done(function(response) {
                        $(\'<link rel="stylesheet" type="text/css" href="'.$url.'">\').
                            appendTo("head");
                    });
                }';
        }
        foreach($cs->scriptFiles as $position => $scriptFiles){
            foreach($scriptFiles as $key => $url){
                $scripts .= '
                    $.ajax({
                        type:"GET",
                        dataType:"script",
                        url:"'.$url.'"
                    }).always(function(){';
                $endScripts .= '})';
            }
        }
        if(array_key_exists(CCLientScript::POS_READY, Yii::app()->clientScript->scripts)){
            foreach(Yii::app()->clientScript->scripts[CClientScript::POS_READY] as $id => $script){
                if(strpos($id, 'logo') === false)
                    $scripts .= "$script\n";
            }
        }

        echo $scripts.$endScripts.';';
    }

    /**
     * Registers a set of packages
     * @param Array $packages 
     * @param bool $useDefaultPackages 
     */
    public function registerPackages ($packages, $useDefaultPackages=false) {
        $oldPackages = Yii::app()->clientScript->packages;
        if ($useDefaultPackages) {
            Yii::app()->clientScript->packages = array_merge (
                $this->getDefaultPackages (), $packages);
        } else {
            Yii::app()->clientScript->packages = $packages;
        }
        
        foreach (array_keys ($packages) as $packageName) {
            Yii::app()->clientScript->registerPackage ($packageName);
        }
        Yii::app()->clientScript->packages = $oldPackages;
    }

    public function getCurrencyConfigScript () {
        // Declare currency format(s) from Yii for the jQuery maskMoney plugin
        $locale = Yii::app()->locale;

        $decSym = $locale->getNumberSymbol('decimal');
        $grpSym = $locale->getNumberSymbol('group');
        $curSym = Yii::app()->getLocale()->getCurrencySymbol(Yii::app()->params['currency']); 

        // Declare:
        $cldScript = 
            '(function($) {
                x2.currencyInfo = '.CJSON::encode(array(
                    'prefix' => isset($curSym)? $curSym : Yii::app()->params['currency'],
                    'decimal' => $decSym,
                    'thousands' => $grpSym,
                )).";
            })(jQuery);";

        return $cldScript;
    }

    /**
     * Returns a cache busting url suffix to be appended to JS/CSS files before registration
     * Checks for presence of query string to determine the appropriate separator between the 
     * url and the cache buster string.
     * @return string suffix
     */
    public function getCacheBusterSuffix ($url=null) {
        $cacheBuster = $this->getCacheBuster ();
        if ($url === null) {
            return '?'.$cacheBuster;
        } else if (preg_match ("/\?/", $url)) {
            return '&'.$cacheBuster;
        } else {
            return '?'.$cacheBuster;
        }
    }

    /**
     * Allows css containing media queries to be added conditionally 
     */
    public function registerResponsiveCssFile ($url, $media='') {
        if (RESPONSIVE_LAYOUT) {
            $this->registerCssFile (
                $url.$this->getCacheBusterSuffix ($url), $media);
        }
    }

    /**
     * Allows css containing media queries to be added conditionally 
     */
    public function registerResponsiveCss ($id, $css, $media='') {
        if (RESPONSIVE_LAYOUT) {
            $this->registerCss ($id, $css, $media);
        }
    }

    /**
     * Overrides parent method to add cache buster parameter 
     */
    public function registerScriptFile ($url, $position=null, array $htmlOptions=array()) {
        return parent::registerScriptFile (
            $url.$this->getCacheBusterSuffix ($url), $position,
            $htmlOptions);
    }

	/**
	 * Overrides parent method to add cache busting suffix to package files
	 */
	public function renderCoreScripts()
	{
		if($this->coreScripts===null)
			return;
		$cssFiles=array();
		$jsFiles=array();
		foreach($this->coreScripts as $name=>$package)
		{
			$baseUrl=$this->getPackageBaseUrl($name);
			if(!empty($package['js']))
			{
                /* x2modstart */ 
				foreach($package['js'] as $js)
					$jsFiles[$baseUrl.'/'.$js.$this->getCacheBusterSuffix ($js)]=$baseUrl.'/'.$js;
                /* x2modend */ 
			}
			if(!empty($package['css']))
			{
                /* x2modstart */ 
				foreach($package['css'] as $css)
					$cssFiles[$baseUrl.'/'.$css.$this->getCacheBusterSuffix ($css)]='';
                /* x2modend */ 
			}
		}
		// merge in place
		if($cssFiles!==array())
		{
			foreach($this->cssFiles as $cssFile=>$media)
				$cssFiles[$cssFile]=$media;
			$this->cssFiles=$cssFiles;
		}
		if($jsFiles!==array())
		{
			if(isset($this->scriptFiles[$this->coreScriptPosition]))
			{
				foreach($this->scriptFiles[$this->coreScriptPosition] as $url => $value)
					$jsFiles[$url]=$value;
			}
			$this->scriptFiles[$this->coreScriptPosition]=$jsFiles;
		}
	}

	/**
	 * Inserts the scripts in the head section.
	 * @param string $output the output to be inserted with scripts.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
	 */
	public function renderHead(&$output)
	{
        parent::renderHead ($output);
		$html='';
		foreach($this->metaTags as $meta)
			$html.=CHtml::metaTag($meta['content'],null,null,$meta)."\n";
		foreach($this->linkTags as $link)
			$html.=CHtml::linkTag(null,null,null,null,$link)."\n";
		foreach($this->cssFiles as $url=>$media)
			$html.=CHtml::cssFile($url,$media)."\n";
        /* x2modstart */ 
        if (Auxlib::getIEVer () < 10) { 
            // merge inline css
            $mergedCss = array ();
            $mediaType = null;
            foreach ($this->css as $css) {
                $text = $css[0];
                if (is_array ($text) && isset ($text['text'])) {
                    $text = $text['text'];
                }

                if (preg_match ('/@import/', $text)) {
                    $html .= CHtml::css($text,$css[1])."\n";
                    continue;
                }
                if ($mediaType === null) { 
                    $mediaType = $css[1];
                }
                if ($css[1] === $mediaType) {
                    if (!isset ($mergedCss[$mediaType])) {
                        $mergedCss[$mediaType] = '';
                    }
                    $mergedCss[$mediaType] .= "\n".$text;
                }
            }
            foreach ($mergedCss as $type => $css) {
                $html.=CHtml::css($css,$type)."\n";
            }
        } else {
            foreach($this->css as $css) {
                $text = $css[0];
                $media = $css[1];

                if (is_array ($text) && isset ($text['text']) && isset ($text['htmlOptions'])) {
                    // special case for css registered with html options
                    $html.=X2Html::css ($text['text'], $media, $text['htmlOptions']);
                    continue;
                }
                $html.=CHtml::css($text, $media)."\n";
            }
        }
        /* x2modend */ 
		if($this->enableJavaScript)
		{
			if(isset($this->scriptFiles[self::POS_HEAD]))
			{
				foreach($this->scriptFiles[self::POS_HEAD] as $scriptFileValueUrl=>$scriptFileValue)
				{
					if(is_array($scriptFileValue))
						$html.=CHtml::scriptFile($scriptFileValueUrl,$scriptFileValue)."\n";
					else
						$html.=CHtml::scriptFile($scriptFileValueUrl)."\n";
				}
			}
			if(isset($this->scripts[self::POS_HEAD]))
				$html.=$this->renderScriptBatch($this->scripts[self::POS_HEAD]);
		}
		if($html!=='')
		{
			$count=0;
			$output=preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is','<###head###>$1',$output,1,$count);
			if($count)
				$output=str_replace('<###head###>',$html,$output);
			else
				$output=$html.$output;
		}
	}

	public function registerScript($id,$script,$position=null,array $htmlOptions=array())
	{
		if($position===null)
			$position=$this->defaultScriptPosition;
		$this->hasScripts=true;
		if(empty($htmlOptions))
			$scriptValue=$script;
		else
		{
			if($position==self::POS_LOAD || $position==self::POS_READY)
				throw new CException(Yii::t('yii','Script HTML options are not allowed for "CClientScript::POS_LOAD" and "CClientScript::POS_READY".'));
			$scriptValue=$htmlOptions;
			$scriptValue['content']=$script;
		}
		$this->scripts[$position][$id]=$scriptValue;
		if($position===self::POS_READY || $position===self::POS_LOAD)
			$this->registerCoreScript('jquery');
		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerScript',$params);
		return $this;
	}

    /**
     * Modified to prevent duplicate rendering of scripts.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    private $renderedScripts = array ();
	protected function renderScriptBatch(array $scripts)
	{
		$html = '';
		$scriptBatches = array();
        /* x2modstart */ 
		foreach($scripts as $scriptName => $scriptValue)
		{
            // scripts with numeric names are assumed to have been added in renderBodyEnd
            if (!is_numeric ($scriptName) && isset ($this->renderedScripts[$scriptName])) continue;
            $this->renderedScripts[$scriptName] = true;
        /* x2modend */ 
			if(is_array($scriptValue))
			{
				$scriptContent = $scriptValue['content'];
				unset($scriptValue['content']);
				$scriptHtmlOptions = $scriptValue;
				ksort($scriptHtmlOptions);
			}
			else
			{
				$scriptContent = $scriptValue;
				$scriptHtmlOptions = array();
			}
			$key=serialize($scriptHtmlOptions);
			$scriptBatches[$key]['htmlOptions']=$scriptHtmlOptions;
			$scriptBatches[$key]['scripts'][]=$scriptContent;
		}
		foreach($scriptBatches as $scriptBatch)
			if(!empty($scriptBatch['scripts']))
				$html.=CHtml::script(implode("\n",$scriptBatch['scripts']),$scriptBatch['htmlOptions'])."\n";

		return $html;
	}

    /**
     * Overrides parent method to add cache buster parameter 
     */
    public function registerCssFile ($url, $media='') {
        return parent::registerCssFile (
            $url.$this->getCacheBusterSuffix ($url), $media);
    }

    /**
     * Registers a set of css files using cache busting.
     * For ie < 10, files are imported using css import statements within style tags. This is done
     * to get around the 31 stylesheet limit in ie 6-9.
     * @param string id CSS script unique id
     * @param array $filenames array of filename strings
     * @param bool if true, theme url + '/css/' will be prepended to each filename
     */
    public function registerCssFiles ($id, array $filenames, $prependThemeUrl=true, $media='') {
        $cssUrl = '';
        if ($prependThemeUrl) {
            $cssUrl = $this->getThemeUrl ().'/css/';
        }
        $ieVer = Auxlib::getIEVer ();
        if ($ieVer < 10) {
            $cacheBuster = $this->getCacheBuster ();
            $cssStr = '';
            foreach ($filenames as $file) {
                $cssStr .= '@import url("'.$cssUrl.$file.'?'.$cacheBuster.'");'."\n";
            }
            $this->registerCss ($id, $cssStr, $media);
        } else {
            foreach ($filenames as $file) {
                $this->registerCssFile ($cssUrl.$file, $media);
            }
        }
    }

    /**
     * Registers css for responsive title bar. Since title bar logo width can change, the
     * media query that determines the appearance of the title bar must be set in accordance
     * with the width of the currently uploaded logo.
     */
    private function registerResponsiveTitleBarCss () {
        $logo = Media::model()
            ->findByAttributes(array('associationId' => 1, 'associationType' => 'logo'));

        if (isset ($logo)) {
            $dimensions = CJSON::decode ($logo->resolveDimensions ());
            if (is_array ($dimensions)) {
                $imgWidth = floor ($dimensions['width'] * (30 / $dimensions['height']));
                Yii::app()->clientScript->registerScript('logoWidthScript',"
                if (typeof x2 === 'undefined') x2 = {};
                x2.logoWidth = ".$imgWidth.";
                ", CClientScript::POS_HEAD);
            }
        }

        if (isset ($imgWidth)) {
            $threshold = 915 + $imgWidth;
        } else {
            $threshold = 915;
        }

        Yii::app()->clientScript->registerResponsiveCss('responsiveTitleBar',"
        /*
        Step between full title bar and mobile title bar. Search bar minimizes and expands to make
        room for user menu links
        */
        @media (max-width: ".$threshold."px) {
            #search-bar-box {
                display: none;
                width: 180px;
            }
            #search-bar button.x2-button {
                border-radius: 3px 3px 3px 3px;
                -moz-border-radius: 3px 3px 3px 3px;
                -webkit-border-radius: 3px 3px 3px 3px;
                -o-border-radius: 3px 3px 3px 3px;
            }
        }

        @media (min-width: ".$threshold."px) {
            #user-menu > li {
                display: block !important;
            }
            #search-bar-box {
                display: block !important;
            }
        }
        ");

    }

    /**
     * Registers a set of css files which are used for all pages with the main layout. 
     */
    private function registerCombinedCss () {
        $ieVer = Auxlib::getIEVer ();
        $cssUrl = $this->getThemeUrl ().'/css';

        $cssFiles = array (
            'screen.css',
            'auxlib.css',
            'jquery-ui.css',
            'dragtable.css',
            'main.css',
            'ui-elements.css',
            'layout.css',
            'details.css',
            'x2forms.css',
            'form.css',
            'publisher.css',
            'sortableWidgets.css',
            '../../../js/bgrins-spectrum-2c2010c/spectrum.css',
            '../../../js/qtip/jquery.qtip.min.css',
            '../../../js/checklistDropdown/jquery.multiselect.css',
            'rating/jquery.rating.css',
            'fontAwesome/css/font-awesome.css',
            'bootstrap/bootstrap.css',
            'css-loaders/load8.css',
        );
        /* x2prostart */ 
        if (get_class (Yii::app()->controller) !== 'EmailInboxesController')
        /* x2proend */ 
            $cssFiles[] = 'recordView.css';

        $responsiveCssFiles = array (
            'responsiveLayout.css',
            'responsiveUIElements.css',
            'responsiveX2Forms.css',
        );

        $this->registerResponsiveTitleBarCss ();

        $this->registerCssFiles ('combinedCss', $cssFiles, 'screen, projection');

        if (RESPONSIVE_LAYOUT) {
            $this->registerCssFiles ('responsiveCombinedCss', 
                $responsiveCssFiles, 'screen, projection');
        }
    }

    /**
     * Instantiates the Flashes utility class 
     */
    public function registerX2Flashes () {
        $this->registerScriptFile($this->baseUrl.'/js/TopFlashes.js', CClientScript::POS_END);
        $this->registerScriptFile($this->baseUrl.'/js/X2Flashes.js', CClientScript::POS_END);
        $this->registerScript ('registerX2Flashes', "
        (function () {
            x2.flashes = new x2.Flashes ({
                containerSelector: 'x2-flashes-container',
                expandWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Expand_Widget.png'."',
                collapseWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Collapse_Widget.png'."',
                closeWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Close_Widget.png'."',
                translations: ".CJSON::encode (array (
                    'noticeFlashList' => Yii::t('app', '{Action} exectuted with', array(
                        '{Action}'=>Modules::displayName(false, 'Actions')
                    )),
                    'errorFlashList' => Yii::t('app', '{Action} exectuted with', array(
                        '{Action}'=>Modules::displayName(false, 'Actions')
                    )),
                    'noticeItemName' => Yii::t('app', 'warnings'),
                    'errorItemName' => Yii::t('app', 'errors'),
                    'successItemName' => Yii::t('app', 'Close'),
                    'close' => Yii::t('app', 'Close'),
                ))."
            });
        }) ();
        ", CClientScript::POS_READY);
    }

    private function registerX2ModelMappingsScript () {
        $this->registerScript('x2ModelMappingsScript',"
            x2.associationModels = ".CJSON::encode (X2Model::$associationModels).";
            x2.modelNameToModuleName = ".CJSON::encode (X2Model::$modelNameToModuleName).";
        ", CClientScript::POS_READY);
    }


    /**
     * Instantiates the x2.Forms utitility class
     */
    private function registerX2Forms () {
        $this->registerScriptFile($this->baseUrl.'/js/X2Forms.js');
        $this->registerScript('registerX2Forms',"
            x2.forms = new x2.Forms ({
                translations: ".CJSON::encode (array (
                    'Check All' => Yii::t('app', 'Check All'),
                    'Uncheck All' => Yii::t('app', 'Uncheck All'),
                    'selected' => Yii::t('app', 'selected'),
                ))."
            });
        ", CClientScript::POS_END);
    }

    private function registerX2QuickCreate () {
        $this->registerScriptFile($this->baseUrl.'/js/X2QuickCreate.js');
        $modelsWhichSupportQuickCreate = 
            QuickCreateRelationshipBehavior::getModelsWhichSupportQuickCreate (true);
        $createUrls = QuickCreateRelationshipBehavior::getCreateUrlsForModels (
            $modelsWhichSupportQuickCreate);
        $dialogTitles = QuickCreateRelationshipBehavior::getDialogTitlesForModels (
            $modelsWhichSupportQuickCreate);
        $this->registerScript('registerQuickCreate',"
            x2.QuickCreate.createRecordUrls = ".CJSON::encode ($createUrls).";
            x2.QuickCreate.dialogTitles = ".CJSON::encode ($dialogTitles).";
        ", CClientScript::POS_END);
    }

    private function registerAttachments () {
        $this->registerScriptFile($this->baseUrl.'/js/Attachments.js');
        $this->registerScript('X2ClientScript.registerAttachments',"
            x2.attachments = new x2.Attachments ({
                translations: ".CJSON::encode (array (
                    'filetypeError' => Yii::t('app', '"{X}" is not an allowed filetype.'),
                ))."
            });
        ", CClientScript::POS_END);
    }

    /**
     * Passes locale-specific date format strings to JS. 
     */
    private function registerDateFormats () {
        $this->registerScript('registerDateFormats',"
            x2.dateFormats = {
                dateFormat: '".Formatter::formatDatePicker()."',
                timeFormat: '".Formatter::formatTimePicker()."',
                ampm: '".Formatter::formatAMPM()."'
            };
        ", CClientScript::POS_END);
    }

    /**
     * Performs all the necessary JavaScript/CSS initializations for most parts of the app.
     */
    public function registerMain(){
        foreach(array('IS_IPAD','RESPONSIVE_LAYOUT') as $layoutConst) {
            defined($layoutConst) or define($layoutConst,false);
        }

        $fullscreen = $this->fullscreen;
        $profile = $this->profile;
        $baseUrl = $this->baseUrl;
        $themeUrl = $this->themeUrl;
        $scriptUrl = $this->scriptUrl;
        $admin = $this->admin;
        $isGuest = $this->isGuest;


        // jQuery and jQuery UI libraries
        $this->registerCoreScript('jquery')
           ->registerCoreScript('jquery.ui')
           ->registerCoreScript('jquery.migrate');

       $this->registerPackages($this->getDefaultPackages());

        $cldScript = $this->getCurrencyConfigScript ();

        AuxLib::registerPassVarsToClientScriptScript('auxlib', array(
            'saveMiscLayoutSettingUrl' =>
            "'".addslashes(Yii::app()->createUrl('/profile/saveMiscLayoutSetting'))."'"
                ), 'passAuxLibVars'
        );
        
        $this->registerX2ModelMappingsScript ();
        $this->registerX2Forms ();
        $this->registerX2QuickCreate ();
        $this->registerX2Flashes ();

        $this->registerAttachments ();
        $this->registerDateFormats ();
        if (YII_DEBUG) $this->registerScriptFile($baseUrl.'/js/Timer.js');

        // custom scripts
        $this->registerScriptFile($baseUrl.'/js/json2.js')
            ->registerScriptFile($baseUrl.'/js/webtoolkit.sha256.js')
            ->registerScriptFile($baseUrl.'/js/main.js', CCLientScript::POS_HEAD)
            ->registerScriptFile($baseUrl.'/js/auxlib.js', CClientScript::POS_HEAD)
            ->registerScriptFile($baseUrl.'/js/IframeFixOverlay.js', CClientScript::POS_HEAD)
            ->registerScriptFile($baseUrl.'/js/LayoutManager.js')
            //->registerScriptFile($baseUrl.'/js/X2Select.js')
            ->registerScriptFile($baseUrl.'/js/media.js')
            ->registerScript('formatCurrency-locales', $cldScript, CCLientScript::POS_HEAD)
            ->registerScriptFile($baseUrl.'/js/modernizr.custom.66175.js')
            ->registerScriptFile($baseUrl.'/js/widgets.js')
            ->registerScriptFile($baseUrl.'/js/qtip/jquery.qtip.min.js')
            ->registerScriptFile($baseUrl.'/js/ActionFrames.js')
            ->registerScriptFile($baseUrl.'/js/bgrins-spectrum-2c2010c/spectrum.js')
            ->registerScriptFile($baseUrl.'/js/ColorPicker.js', CCLientScript::POS_END)
            ->registerScriptFile($baseUrl.'/js/PopupDropdownMenu.js', CCLientScript::POS_END)
            ->registerScriptFile($baseUrl.'/js/checklistDropdown/jquery.multiselect.js');

        if (YII_DEBUG && YII_UNIT_TESTING) {
            $this->registerScriptFile (
                $baseUrl.'/js/qunit/qunit-1.15.0.js', CClientScript::POS_HEAD);
            $this->registerCssFile ($baseUrl.'/js/qunit/qunit-1.15.0.css');
        }

        if(IS_IPAD){
            $this->registerScriptFile($baseUrl.'/js/jquery.mobile.custom.js');
        }
        $this->registerInitScript ();

        if(Yii::app()->session['translate'])
            $this->registerScriptFile($baseUrl.'/js/translator.js');

        $this->registerScriptFile($baseUrl.'/js/backgroundFade.js');
        $this->registerScript('datepickerLanguage', "
            $.datepicker.setDefaults($.datepicker.regional['']);
        ");
        $mmPath = Yii::getPathOfAlias('application.extensions.moneymask.assets');
        $aMmPath = Yii::app()->getAssetManager()->publish($mmPath);
        $this->registerScriptFile("$aMmPath/jquery.maskMoney.js");
        $this->registerCssFile($baseUrl.'/css/normalize.css', 'all')
            ->registerCssFile($themeUrl.'/css/print.css', 'print')
            ->registerCoreScript('cookie');
        $this->registerCombinedCss ();
        if(!RESPONSIVE_LAYOUT && IS_ANDROID) {
            $this->registerCssFile(
                $themeUrl.'/css/androidLayout.css', 'screen, projection');
        } elseif (IS_IPAD) {
            $this->registerCssFile($themeUrl.'/css/ipadLayout.css', 'screen, projection');
        }

        $this->registerScript('fullscreenToggle', '
            window.enableFullWidth = '.(!Yii::app()->user->isGuest ? 
                ($profile->enableFullWidth ? 'true' : 'false') : 'true').';
            window.fullscreen = '.($fullscreen ? 'true' : 'false').';
        ', CClientScript::POS_HEAD);

        if(is_object(Yii::app()->controller->module)){
            $this->registerScript('saveCurrModule', "
                x2.currModule = '".Yii::app()->controller->module->name."';
            ", CClientScript::POS_HEAD);
        }

        if(!$isGuest){
            $this->registerScript('notificationsParams', "
                x2.notifications = new x2.Notifs ({
                    disablePopup: ".($profile->disableNotifPopup ? 'true' : 'false').",
                    translations: {
                        clearAll:
                            '".addslashes(Yii::t('app', 'Permanently delete all notifications?'))."'
                    }
                });
            ", CClientScript::POS_READY);
            $this->registerScriptFile($baseUrl.'/js/jstorage.min.js')
               ->registerScriptFile(
                $baseUrl.'/js/notifications.js', CClientScript::POS_BEGIN);
        }

        if(!$isGuest && ($profile->language == 'he' || $profile->language == 'fa'))
            $this->registerCss('rtl-language', 'body{text-align:right;}');

        $this->registerCoreScript('rating');
    }

    public function getAdmin() {
        if(!isset($this->_admin)) {
            $this->_admin = Yii::app()->settings;
        }
        return $this->_admin;
    }

    public function setAdmin(Admin $value) {
        $this->_admin = $value;
    }

    public function getBaseUrl(){
        if(!isset($this->_baseUrl)){
            $this->_baseUrl = Yii::app()->baseUrl;
        }
        return $this->_baseUrl;
    }

    public function setBaseUrl($value){
        $this->_baseUrl = $value;
    }

    public function getFullscreen() {
        if(!isset($this->_fullscreen)) {
            $this->_fullscreen = Yii::app()->user->isGuest || $this->profile->fullscreen;
        }
        return $this->_fullscreen;
    }

    public function setFullscreen($value) {
        $this->_fullscreen = $value;
    }

    public function getIsGuest() {
        if(!isset($this->_isGuest)) {
            $this->_isGuest = Yii::app()->user->isGuest;
        }
        return $this->_isGuest;
    }
    public function setIsGuest($value) {
        $this->_isGuest = $value;
    }

    public function getProfile() {
        if(!isset($this->_profile)) {
            $this->_profile = Yii::app()->params->profile;
        }
        return $this->_profile;

    }

    public function setProfile(Profile $value) {
        $this->_profile = $value;
    }

    public function getScriptUrl() {
        if(!isset($this->_scriptUrl)) {
            $this->_scriptUrl = Yii::app()->request->scriptUrl;
        }
        return $this->_scriptUrl;
    }

    public function setScriptUrl( $value) {
        $this->_scriptUrl = $value;
    }

    public function getThemeUrl() {
        if(!isset($this->_themeUrl)) {
            $this->_themeUrl = Yii::app()->theme->baseUrl;
        }
        return $this->_themeUrl;
    }
    public function setThemeUrl($value) {
        $this->_themeUrl = $value;
    }

    private function registerInitScript () {
        Yii::app()->clientScript->registerScript ('X2ClientScriptInitScript',"
            (function () {
                var actionFramesName = 'actionFrames';
                x2[actionFramesName] = new x2.ActionFrames ({ 
                    instanceName: actionFramesName,
                    deleteActionUrl: '".
                        Yii::app()->controller->createUrl ('/actions/actions/delete')."'
                });
            }) ();
        ", CClientScript::POS_HEAD);
    }

}
