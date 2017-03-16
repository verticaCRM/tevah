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

Yii::import('zii.widgets.CPortlet');


/**
 * Gives a utility function to derived classes which sets up this left widgets title bar.
 * @package application.components 
 */
class LeftWidget extends CPortlet {

	/**
     * The name of the widget. This should match the name used in the layout stored in
     * the user's profile.
	 * @var string
	 */
    public $widgetName;

	/**
     * The label used in this widgets title bar
	 * @var string
	 */
    public $widgetLabel;

    protected $isCollapsed = false;

    private $_openTag;

    /**
     * @var string The prefix used on the id of the container
     */
    public static $idPrefix = 'x2widget_';


    /**
     * @var string The class of the container
     */
    public static $class = 'sidebar-left';

    /**
     * Class added to the porlet decoration to indicate that the widget is collapsed
     * @var string 
     */
    public static $leftWidgetCollapsedClass = 'left-widget-collapsed';

    public static function registerScript () {
        // collapse or expand left widget and save setting to user profile
        Yii::app()->clientScript->registerScript('leftWidgets','
            $(".left-widget-min-max").click(function(e){
                e.preventDefault();
                var link=this;
                var action = $(this).attr ("value");
                $.ajax({
                    url:"'.Yii::app()->request->getScriptUrl ().'/site/minMaxLeftWidget'.'",
                    data:{
                        action: action,
                        widgetName: $(link).attr ("name")
                    },
                    success:function(data){
                        if (data === "failure") return;
                        if(action === "expand"){
                            $(link).removeClass("fa-caret-left");
                            $(link).addClass("fa-caret-down");
                            $("ggads").html("<img src=\'"+yii.themeBaseUrl+"/images/icons/'.
                                'Collapse_Widget.png\' />");
                            $(link).parents(".portlet-decoration").next().slideDown();
                            $(link).attr ("value", "collapse");
                            $(link).parents (".portlet-decoration").parent ().
                                removeClass ("'.self::$leftWidgetCollapsedClass.'")
                        }else if(action === "collapse"){
                            $(link).removeClass("fa-caret-down");
                            $(link).addClass("fa-caret-left");
                            $("ggads").html("<img src=\'"+yii.themeBaseUrl+"/images/icons/'.
                                'Expand_Widget.png\' />");
                            $(link).parents(".portlet-decoration").next().slideUp();
                            $(link).attr ("value", "expand");
                            $(link).parents (".portlet-decoration").parent ().
                                addClass ("'.self::$leftWidgetCollapsedClass.'")
                        }
                    }
                });
            });
        ');
    }

	/**
	 * Sets the label in the widget title and determines whether this left widget should 
     * be hidden or shown on page load.
	 */
    protected function initTitleBar () {
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $layout = $profile->getLayout ();
            if (in_array ($this->widgetName, array_keys ($layout['left']))) {
                $this->isCollapsed = $layout['left'][$this->widgetName]['minimize'];
            }
        }
        $themeURL = Yii::app()->theme->getBaseUrl();
		$this->title =
            Yii::t('app', $this->widgetLabel).
            CHtml::tag( 'i',
                array(
                    'title'=>Yii::t('app', $this->widgetLabel), 
                    'name'=>$this->widgetName, 
                    'class'=>'fa fa-lg right left-widget-min-max '.($this->isCollapsed ? 'fa-caret-left' : 'fa-caret-down'),
                    'value'=>($this->isCollapsed ? 'expand' : 'collapse'),
                    ), ' '
            );
        $this->htmlOptions = array(
            'class' => (!$this->isCollapsed ? "" : "hidden-filter")
        );

    }

	/**
     * overrides parent method so that content gets hidden/shown depending on value
     * of isCollapsed
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function init()
	{
        if (!$this->widgetName) {
            $this->widgetName = get_called_class();
        }
        /* x2modstart */ 
        $this->initTitleBar ();
        /* x2modend */ 

		ob_start();
		ob_implicit_flush(false);

		if(isset($this->htmlOptions['id']))
			$this->id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$this->id;

        /* x2modstart */ 
        if ($this->isCollapsed)
            $this->htmlOptions['class'] = self::$leftWidgetCollapsedClass;
        /* x2modend */ 

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
		$this->renderDecoration();
        /* x2modstart */ 
		echo "<div class=\"{$this->contentCssClass}\" ".
            ($this->isCollapsed ? "style='display: none;'" : '').">\n";
        /* x2modend */ 

		$this->_openTag=ob_get_contents();
		ob_clean();
	}

	/**
	 * Overrides parent method since private property _openTag gets set in init ().
     * This is identical to the parent method.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function run()
	{
		$this->renderContent();
		$content=ob_get_clean();
		if($this->hideOnEmpty && trim($content)==='')
			return;
		echo $this->_openTag;
		echo $content;
		echo "</div>\n";
		echo CHtml::closeTag($this->tagName);
	}


    /**
     * Instantiates a left Widget with the specified settings
     * @param array $settings the array of settings to be passed to the widget
     */
    public static function instantiateWidget ($settings=array()) {
        $class = get_called_class();
        echo CHtml::openTag('div', array(
            'id' => self::$idPrefix.$class,
            'class' => self::$class ));

        Yii::app()->controller->widget($class, $settings);

        echo "</div>";
    }
}
?>
