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
 * Handles rendering of X2Model fields 
 */

class FieldFormatter extends CComponent {

    /**
     * @var X2Model $owner
     */
    public $owner; 

    public function renderAttribute(
        $fieldName, $makeLinks = true, $textOnly = true, $encode = true) {

        $field = $this->owner->getField($fieldName);
        if (!isset($field))
            return null;

        if (!YII_UNIT_TESTING && Yii::app()->params->noSession) {
            $webRequestAttributes = array(
                'rating', // Uses a Yii widget, which requires access to the controller
                'assignment', // Depends on getUserLinks, which depends on the user session
                'optionalAssignment', // Same as above
                'url', // Renders an actual link
                'text', // Uses convertUrls, which is in x2base
            );
            if (in_array($field->type, $webRequestAttributes))
                return $this->render($this->owner->$fieldName, $encode);
        }

        $renderFn = 'render'.ucfirst ($field->type);
        if (method_exists ($this, $renderFn)) {
            return $this->$renderFn ($field, $makeLinks, $textOnly, $encode);
        } else {
            return $this->render ($this->owner->$fieldName, $encode);
        }
    }

    protected function render ($val, $encode) {
        return $encode ? CHtml::encode($val) : $val;
    }

    protected function renderDate ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if (empty($this->owner->$fieldName))
            return ' ';
        elseif (is_numeric($this->owner->$fieldName))
            return Formatter::formatLongDate($this->owner->$fieldName);
        else
            return $this->render ($this->owner->$fieldName, $encode);
    }

    protected function renderDateTime ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if (empty($this->owner->$fieldName))
            return ' ';
        elseif (is_numeric($this->owner->$fieldName))
            return Formatter::formatCompleteDate($this->owner->$fieldName);
        else
            return $this->render ($this->owner->$fieldName, $encode);
    }

    protected function renderRating ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if ($textOnly) {
            return $this->render ($this->owner->$fieldName, $encode);
        } else {
            return Yii::app()->controller->widget('CStarRating', array(
                'model' => $this->owner,
                'name' => str_replace(
                    ' ', '-', get_class($this->owner) . '-' . $this->owner->id . '-rating-' . 
                    $field->fieldName),
                'attribute' => $field->fieldName,
                'readOnly' => true,
                // If not required, render the "cancel" button to clear the rating
                'allowEmpty' => !$field->required,
                'minRating' => 1, //minimal valuez
                'maxRating' => 5, //max value
                'starCount' => 5, //number of stars
                'cssFile' => Yii::app()->theme->getBaseUrl() .
                '/css/rating/jquery.rating.css',
            ), true);
        }
    }

    protected function renderAssignment ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        return User::getUserLinks($this->owner->$fieldName, $makeLinks);
    }

    protected function renderOptionalAssignment ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if ($this->owner->$fieldName == '')
            return '';
        else
            return User::getUserLinks($this->owner->$fieldName);
    }

    protected function renderVisibility ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        switch ($this->owner->$fieldName) {
            case '1':
                return Yii::t('app', 'Public');
            case '0':
                return Yii::t('app', 'Private');
            case '2':
                return Yii::t('app', 'User\'s Groups');
            default:
                return '';
        }
    }

    protected function renderEmail ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if (empty($this->owner->$fieldName)) {
            return '';
        } else {
            $mailtoLabel = (isset($this->owner->name) && !is_numeric($this->owner->name)) ? 
                '"' . $this->owner->name . '" <' . $this->owner->$fieldName . '>' : 
                $this->owner->$fieldName;
            return $makeLinks ? 
                CHtml::mailto(CHtml::encode($this->owner->$fieldName), $mailtoLabel) : 
                $this->render ($this->owner->$fieldName, $encode);
        }
    }

    protected function renderPhone ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        $value = X2Model::getPhoneNumber(
            $fieldName, get_class($this->owner), $this->owner->id, $encode, $makeLinks,
            $this->owner->$fieldName);
        return $value;
    }

    protected function renderUrl ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if (!$makeLinks)
            return CHtml::encode($this->owner->$fieldName);

        if (empty($this->owner->$fieldName)) {
            $text = '';
        } elseif (!empty($field->linkType)) {
            switch ($field->linkType) {
                case 'skype':
                    $text = '<a href="callto:' . $this->render ($this->owner->$fieldName, $encode) . '">' . 
                        $this->render ($this->owner->$fieldName, $encode) . '</a>';
                    break;
                case 'googleplus':
                    $text = '<a href="http://plus.google.com/' . 
                        $this->render ($this->owner->$fieldName, $encode) . '">' . $this->render($this->owner->$fieldName, $encode) . 
                        '</a>';
                    break;
                case 'twitter':
                    $text = '<a href="http://www.twitter.com/#!/' . 
                        $this->render ($this->owner->$fieldName, $encode) . '">' . $this->render($this->owner->$fieldName, $encode) . 
                        '</a>';
                    break;
                case 'linkedin':
                    $text = '<a href="http://www.linkedin.com/in/' . 
                        $this->render ($this->owner->$fieldName, $encode) . '">' . $this->render($this->owner->$fieldName, $encode) . 
                        '</a>';
                    break;
                default:
                    $text = '<a href="http://www.' . $field->linkType . '.com/' . 
                        $this->render ($this->owner->$fieldName, $encode) . '">' . 
                            $this->render ($this->owner->$fieldName, $encode) . 
                        '</a>';
            }
        } else {
            $text = trim(preg_replace(
                array(
                    '/<a([^>]*)target="?[^"\']+"?/i',
                    '/<a([^>]+)>/i',
                ), array(
                    '<a\\1 target="_blank"',
                    '<a\\1 target="_blank">',
                ), $this->render ($this->owner->$fieldName, $encode)
            ));
            $oldText = $text;
            if (!function_exists('linkReplaceCallback')) {

                function linkReplaceCallback($matches) {
                    return stripslashes(
                        (strlen($matches[2]) > 0 ? 
                            '<a href=\"' . $matches[2] . '\" target=\"_blank\">' . 
                                $matches[0] . '</a>' : 
                            $matches[0]));
                }

            }

            $text = trim(preg_replace_callback(
                array(
                    '/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+))/ix',
                ), 'linkReplaceCallback', $this->render ($this->owner->$fieldName, $encode)
            ));
            if ($text == trim($oldText)) {
                if (!function_exists('linkReplaceCallback2')) {

                    function linkReplaceCallback2($matches) {
                        return stripslashes(
                            (strlen($matches[2]) > 0 ? 
                                '<a href=\"http://' . $matches[2] . 
                                    '\" target=\"_blank\">' . $matches[0] . '</a>' : 
                            $matches[0]));
                    }

                }

                $text = trim(preg_replace_callback(
                    array(
                        '/(^|\s|>)(www.[^<> \n\r]+)/ix',
                    ), 'linkReplaceCallback2', $this->render ($this->owner->$fieldName, $encode)
                ));
            }
        }
        return $text;
    }

    protected function renderLink ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        $linkedModel = $this->owner->getLinkedModel($fieldName, false);
        if ($linkedModel === null) {
            return $this->render ($this->owner->$fieldName, $encode);
        } else {
            return $makeLinks ? $linkedModel->getLink() : $linkedModel->name;
        }
    }

    protected function renderBoolean ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        return $textOnly ? 
            $this->render (
                $this->owner->$fieldName ? 
                    Yii::t('app', 'Yes') : Yii::t('app', 'No'), $encode) : 
            CHtml::checkbox(
                '', $this->owner->$fieldName, 
                array('onclick' => 'return false;', 'onkeydown' => 'return false;'));
    }

    protected function renderCurrency ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if ($this->owner instanceof Product) { // products have their own currency
            return Yii::app()->locale->numberFormatter->formatCurrency(
                $this->owner->$fieldName, $this->owner->currency);
        } else {
            return empty($this->owner->$fieldName) ? 
                ($encode ? "&nbsp;" : '') : 
                Yii::app()->locale->numberFormatter->formatCurrency(
                    $this->owner->$fieldName, Yii::app()->params['currency']);
        }
    }

    protected function renderPercentage ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        return $this->owner->$fieldName !== null && $this->owner->$fieldName !== '' ? 
            (string) ($this->render ($this->owner->$fieldName, $encode)) . "%" : null;
    }

    protected function renderDropdown ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        return $this->render(
            X2Model::model('Dropdowns')->getDropdownValue(
                $field->linkType, $this->owner->$fieldName), $encode);
    }

    protected function renderParentCase ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        return $this->render (
            Yii::t(strtolower(Yii::app()->controller->id), $this->owner->$fieldName), $encode);
    }

    protected function renderText ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        return Yii::app()->controller->convertUrls(
            $this->render ($this->owner->$fieldName, $encode));
    }

    protected function renderCredentials ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        $sysleg = Yii::t('app', 'System default (legacy)');
        if ($this->owner->$fieldName == -1) {
            return $sysleg;
        } else {
            $creds = Credentials::model()->findByPk($this->owner->$fieldName);
            if (!empty($creds))
                return $this->render ($creds->name, $encode);
            else
                return $sysleg;
        }
    }

    protected function renderTimerSum ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        $t_seconds = $this->owner->$fieldName;
        $t[] = floor($t_seconds / 3600); // Hours
        $t[] = floor($t_seconds / 60) % 60; // Minutes
        $t[] = $t_seconds % 60; // Seconds
        $pad = function($t) {
            return str_pad((string) $t, 2, '0', STR_PAD_LEFT);
        };
        return implode(':', array_map($pad, $t));
    }

    protected function renderInt ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if ($fieldName != 'id')
            return Yii::app()->locale->numberFormatter->formatDecimal($this->owner->$fieldName);
    }

    protected function renderFloat ($field, $makeLinks, $textOnly, $encode) {
        return $this->renderInt ($field, $makeLinks, $textOnly, $encode);
    }

    protected function renderCustom ($field, $makeLinks, $textOnly, $encode) {
        $fieldName = $field->fieldName;
        if ($field->linkType == 'display') {
            // Interpret as HTML. Restore curly braces in href
            // attributes that HTMLPurifier has replaced:
            $fieldText = preg_replace(
                    '/%7B([\w\.]+)%7D/', '{$1}', $field->data);
            return Formatter::replaceVariables($fieldText, $this->owner, '', false);
        } elseif ($field->linkType == 'formula') {
            $evald = Formatter::parseFormula($field->data, array(
                'model' => $this->owner,
            ));
            if ($evald[0]) {
                return $this->render ($evald[1], $encode);
            } else {
                return Yii::t('app', 'Error parsing formula.') . ' ' . $evald[1];
            }
        } else {
            return $this->render ($this->owner->$fieldName, $encode);
        }
    }
}

?>
