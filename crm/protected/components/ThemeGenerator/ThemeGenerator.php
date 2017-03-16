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
 * Class to generate a CSS files from profile settings, works as a templating system.
 * Templates files are .php files that return a string of css. The array $colors will be sent to 
 * the files with generated keys based on $settingsList. 
 *
 * For example, because 'text' is a key in $settingsList, 
 *       $colors[text]
 *       $colors[dark_text]
 *       $colors[darker_text]
 *       $colors[bright_text]
 *       $colors[brighter_text]
 *       $colors[light_text]
 *       $colors[lighter_text]
 *       $colors[opaque_text]
 *
 *
 * Are all avaliable to the templates.
 * light and lighter keys are 'smart' meaning it 
 * will appear brighter on dark themes and darker on dark themes.
 *
 *  In addition, the keys 
 *
 *  $colors[smart_text]
 *  $colors[smart_text2]
 *  
 *  will adapt based on the difference in contrast between highlight1 and highlight2 with text 
 *  respectively 
 * 
 * 
 * An entry in the color array automatically adds the !important tag and a semicolon. 
 * Example Template entry: 
 * 
 *   #content a:hover {
 *      color: $colors[darker_link]  
 *   }
 *   
 *   #content span {
 *      background: $colors[highlight2]
 *      color: $colors[smart_text2]
 *   }
 * 
 * To use  key that doesnt have the !important tag added, append _hex to the key
 *   $colors[text_hex], $colors[darker_hex]
 *
 */
class ThemeGenerator {

    /**
    * @var name of default light theme
    */
    public static $defaultLight = 'Default';

    /**
    * @var name of default dark theme
    */
    public static $defaultDark = 'Terminal';

    /**
     * @var array list of the profile setting keys and their descriptions
     * This list is used in the Profile Model to set up the the theme behavior
     */
    public static $settingsList = array(
        'background',
        'content',
        'text',
        'link',
        'highlight1',
        'highlight2',
        );

    /**
     * @var string Path to the folder of templates.
     */
    const TEMPLATE_PATH = 'components/ThemeGenerator/templates';

    /**
     * Loads and processes the tempates with an array of keys
     * @return string $rendered css files
     */
    public static function loadTemplates($colors){
        $css = '';

        $dir = new DirectoryIterator( 
            Yii::app()->basePath.DIRECTORY_SEPARATOR.self::TEMPLATE_PATH );
        foreach ($dir as $fileinfo) {
            if (preg_match ('/\.php$/', $fileinfo)) {
                $css .= include $fileinfo->getPathname();
            }
        }
        return $css;
    }

    /**
     * Adds !important; to each set value. If a color is not set in the profile, 
     * simply adds a semicolon to prevent errors
     * @param $value string a hash code for a color (with the hash)
     * @return string returns the formatted color string
     */
    public static function formatColor($value){
        if (!preg_match('/#/', $value) && !preg_match('/rgb/', $value)) {
            return $value;
        }

        if(!isset($value) || !$value){
            $value = ';';
        } else {
            $value = "$value !important;";
        }

        return $value;
    }

    /**
     * Populates the array with different color option
     * @return array array filled with formatted css color strings
     */
    public static function generatePalette($preferences){
        $colors = $preferences;

        // Flag to indicate this array is generated
        $colors['generated'] = true;

        //Keys for smart text
        $colors['smart_text'] = '';
        $colors['smart_text2'] = '';

        if(isset($colors['backgroundImg']) && $colors['backgroundImg']) {
            $colors['background']='';
        }

        foreach(self::$settingsList as $key){

            $value = isset( $colors[$key]) ? 
                $colors[$key] : '';
            
            if (!preg_match("/#/", $value) && $value){
                $colors[$key] = '#'.$value;
            }

            $colors['darker_'.$key] = X2Color::brightness($value, -0.1, false);
            $colors['dark_'.$key] = X2Color::brightness($value, -0.05, false);
            
            $colors['brighter_'.$key] = X2Color::brightness($value, 0.1, false);
            $colors['bright_'.$key] = X2Color::brightness($value, 0.05, false);
    
            $colors['lighter_'.$key] = X2Color::brightness($value, 0.1, true);
            $colors['light_'.$key] = X2Color::brightness($value, 0.05, true);

            $colors['opaque_'.$key] = X2Color::opaque($value, 0.2);
        }

        # settings for most borders in the app
        $colors['border'] = $colors['lighter_content'];

        # Smart text for highlight 1 (Buttons and Windows)
        if( isset($colors['highlight1'], $colors['text']) &&
                !empty($colors['highlight1']) && !empty($colors['text']) ) {
            $colors['smart_text'] = X2Color::smartText($colors['highlight1'], $colors['text']);
        }

        # Smart text for highlight 2 (highlighted buttons)
        if( isset($colors['highlight2'], $colors['text']) &&
                !empty($colors['highlight2']) && !empty($colors['text']) ) {
            $colors['smart_text2'] = X2Color::smartText($colors['highlight2'], $colors['text']);
        }

        return $colors;
    }

    /**
     * Formats a color array to be CSS-ready by adding important tags and
     * adding a key appended with hex that does not have the important tags
     * @param array Array of color keys
     * @return array Array of formatted array
     */
    public static function formatColorArray($colors) {
        foreach($colors as $key => $value){
            # keep original value in special key
            $colors[$key.'_hex'] = $value;
            # Add important tags
            $colors[$key] = self::formatColor($value);
        }

        return $colors;
    }

    /**
     * Loads a formatted color array into the templates and returns the generated CSS
     * @param array $colors Array of formatted colors
     * @return string string of total generated CSS 
     */
    public static function getCss($colors) {
        if (!$colors['themeName'] || $colors['themeName'] == self::$defaultLight){
            return "";
        }

        $colors = self::formatColorArray($colors);
        $css = self::loadTemplates($colors);
        return $css;
    }

    /**
     * Computes the theme and registers it with Yii
     * @param array $colors If set, will render CSS with these colors
     * Otherwise, it uses colors from the users profile
     */
    public static function render($colors = null) {
        if (!$colors) {
            $profile = Yii::app()->params->profile;

            // If no profile render the default theme
            if (!$profile) {
                self::renderTheme(self::$defaultLight);
                return;
            }

            $colors = $profile->getTheme();

            // If the theme isnt generated, Generate it and save
            if (!array_key_exists('generated', $colors) || !$colors['generated']) {
                $colors = self::generatePalette($colors);
                $profile->theme = $colors;
                $profile->save();
            }

        }

        $css = self::getCss($colors);
        Yii::app()->clientScript->registerCSS('ProfileGeneratedCSS', $css, 'screen', CClientScript::POS_HEAD);
    }

    /**
     * Loads a theme for the login page
     * @param string $themeName string of the theme to render
     */
    public static function loadDefault($themeName) {
        //In case default light was deleted
        if ($themeName == self::$defaultLight) {
            return array('themeName'=>self::$defaultLight);
        }

        $media = X2Model::model('Media')->findByAttributes(
            array(
                'associationType' => 'theme',
                'fileName' => $themeName,
                'private' => 0,
            )
        );

        if( !$media ) {
            $media = X2Model::model('Media')->findByAttributes(
                array(
                    'associationType' => 'theme',
                    'fileName' => self::$defaultDark
                )
            );

            if (!$media) {
                return self::loadDefault(self::$defaultLight);
            }
        }

        $json = CJSON::decode( $media->description );
        $colors = ThemeGenerator::generatePalette($json);
        return $colors;
    }

   
    /**
     * Wrapper function for render and default render
     */
    public static function renderTheme($themeName=null) {
        if ($themeName) {
            $colors = self::loadDefault($themeName);
            self::render($colors);
        } else {
            self::render();
        }
    }

    /**
     * Renders the settings for the profile page based on the $settingsList array 
     * TODO: Move to a class for rendering the theme settings. 
     */
    public static function renderSettings(){
        $colors = Yii::app()->params->profile->getTheme();

        $translations = self::getTranslations();

        $i = 0;
        foreach(self::$settingsList as $key){
            $setting = $translations[$key];
            $value = isset($colors[$key]) ? $colors[$key] : '' ;
            echo "<div class='row' style='display:inline-block; margin-right:15px;'>
                    <label for='pageHeaderBgColor'>
                        $setting
                    </label>
                    <input  type='text'
                           name='preferences[$key]'
                           id='preferences_$key'
                           value='$value'
                           class='color-picker-input theme-attr'> 
                    </input>
                  </div>";

            if (++$i % 3 == 0)
                echo '</br>';
        }

        echo "<div style='clear:both;'></div>";
    }

    /**
     * Retrieves translations of field Names
     */
    public static function getTranslations() {
        return array(
            'background' => Yii::t('profile', 'Background'),
            'content' => Yii::t('profile', 'Content'),
            'text' => Yii::t('profile', 'Text'),
            'link' => Yii::t('profile', 'Links'),
            'highlight1' => Yii::t('profile', 'Windows and Buttons'),
            'highlight2' => Yii::t('profile', 'Highlight')
        );
    }

    /**
     * Function to remove the color from the #content element of a page. 
     */
    public static function removeBackdrop() {
        Yii::app()->clientScript->registerScript ('RemoveBackropJS', '
            $(function() {
                $("#content").addClass("no-backdrop");
            });
        ', CClientScript::POS_END);
    }

    public static function isThemed() {
        $theme = Yii::app()->params->profile->theme['themeName'];
        return ($theme && $theme != self::$defaultLight);
    }


    /**
     * List of keys for the profile JSON fields behaviors
     */
    public static function getProfileKeys() {
        return array(
            'themeName',
            'background',
            'content',
            'text',
            'link',
            'highlight1',
            'highlight2',
            'smart_text',
            'smart_text2',
            'darker_background',
            'dark_background',
            'brighter_background',
            'bright_background',
            'lighter_background',
            'light_background',
            'opaque_background',
            'darker_content',
            'dark_content',
            'brighter_content',
            'bright_content',
            'lighter_content',
            'light_content',
            'opaque_content',
            'darker_text',
            'dark_text',
            'brighter_text',
            'bright_text',
            'lighter_text',
            'light_text',
            'opaque_text',
            'darker_link',
            'dark_link',
            'brighter_link',
            'bright_link',
            'lighter_link',
            'light_link',
            'opaque_link',
            'darker_highlight1',
            'dark_highlight1',
            'brighter_highlight1',
            'bright_highlight1',
            'lighter_highlight1',
            'light_highlight1',
            'opaque_highlight1',
            'darker_highlight2',
            'dark_highlight2',
            'brighter_highlight2',
            'bright_highlight2',
            'lighter_highlight2',
            'light_highlight2',
            'opaque_highlight2',
            'border',
            'generated'
        );
    }


}

?>
