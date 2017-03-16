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
 * ****************************************************************************** */

/**
 * @file 1415137524-migrate-user-themes.php
 * 
 * With the introduction of preset themes in 5.0, the user's existing theme
 * will need to be migrated over, and these preset themes will need to be
 * inserted into the database via a migration.
 */
$migrateUserThemes = function() {
    // Field mappings from pre-5.0 to 5.0
    $newMappings = array(
        'backgroundColor' => 'background',
        'menuBgColor' => 'highlight1',
        'pageHeaderBgColor' => 'highlight2',
        'pageHeaderTextColor' => 'link',
    );

    // Create new indices
    $createIndices = array(
        'content',
        'text',
    );

    // First add the new preset themes to the database
    $newThemesSql = <<<HERE
INSERT INTO `x2_media` (`id`, `associationType`, `uploadedBy`, `fileName`, `description`, `private`) VALUES
('-1', "theme",'admin','Default','{"themeName":"Default","background":"","content":"","text":"","link":"","highlight1":"","highlight2":"","backgroundTiling":"stretch","backgroundImg":"","owner":"admin","private":"0"}',0),
('-2', "theme",'admin','Terminal','{"themeName":"Terminal","background":"221E1E","content":"2E2E2E","text":"F7F7F7","link":"F2921D","highlight1":"1B1B1B","highlight2":"074E8C","backgroundTiling":"stretch","backgroundImg":"","owner":"admin"}',0),
('-3', "theme",'admin','Twilight','{"themeName":"Twilight","background":"0C1021","content":"0C1021","text":"F7F7F7","link":"FBDE2D","highlight1":"303E49","highlight2":"FF6400","backgroundTiling":"stretch","backgroundImg":"","owner":"admin"}',0),
('-4', "theme",'admin','Guava','{"themeName":"Guava","background":"F0AA81","content":"D6CCAD","text":"42282F","link":"2D4035","highlight1":"74A588","highlight2":"D6655A","backgroundTiling":"stretch","backgroundImg":"","owner":"admin"}',0);
HERE;
    Yii::app()->db->createCommand($newThemesSql)->execute();

    // Migrate color settings from each theme in x2_media
    $usersThemes = Yii::app()->db->createCommand()
        ->select('*')
        ->from('x2_media')
        ->where('associationType = "theme" AND id > -1')
        ->queryAll();
    foreach ($usersThemes as $theme) {
        $themeId = $theme['id'];
        $theme = CJSON::decode ($theme['description']);
        if (!$theme)
            continue;

        // Migrate user's color settings
        foreach ($newMappings as $oldField => $newField)
            $theme[$newField] = $theme[$oldField];

        // Ensure new indices are present
        foreach ($createIndices as $index) {
            if (!array_key_exists($index, $theme))
                $theme[$index] = "";
        }

        $theme = CJSON::encode($theme);
        if (!$theme)
            continue;

        $params = array(
            ':id' => $themeId,
            ':theme' => $theme,
        );
        Yii::app()->db->createCommand('UPDATE x2_media SET description = :theme WHERE id = :id')
            ->execute($params);
    }

    // Now migrate the users current theme
    $users = Yii::app()->db->createCommand()
        ->select('username')
        ->from('x2_users')
        ->queryColumn();
    foreach ($users as $user) {
        $currentTheme = Yii::app()->db->createCommand()
            ->select('theme')
            ->from('x2_profile')
            ->where('username = :user', array(':user' => $user))
            ->queryScalar();
        $theme = CJSON::decode ($currentTheme);
        if (!$theme)
            continue;

        // Migrate user's color settings
        foreach ($newMappings as $oldField => $newField)
            $theme[$newField] = $theme[$oldField];

        // Ensure new indices are present
        foreach ($createIndices as $index) {
            if (!array_key_exists($index, $theme))
                $theme[$index] = "";
        }

        // Now update the themeName or create a theme with a default name if one doesn't exist
        if (empty($theme['themeName'])) {
            $defaultName = $user."'s Theme";
            foreach (range(0, 10) as $i) {
                // Ensure this name isn't taken.
                $searchName = $defaultName.($i !== 0 ? " ($i)" : "");
                $tempName = Yii::app()->db->createCommand()
                    ->select('fileName')
                    ->from('x2_media')
                    ->where('fileName = :name', array(
                        ':name' => $searchName
                    ))->queryScalar();
                if ($tempName !== $searchName) {
                    $defaultName = $searchName;
                    break;
                }
            }
            $theme['themeName'] = $defaultName;
            $sql = 'INSERT INTO x2_media (`associationType`, `uploadedBy`, `fileName`, '.
                                         '`description`, `private`) '.
                   'VALUES ("theme", :user, :themeName, :theme, 1)';
            $encodedTheme = CJSON::encode ($theme);
            if (!$encodedTheme)
                continue;
            $params = array(
                ':user' => $user,
                ':themeName' => $defaultName,
                ':theme' => $encodedTheme,
            );

            if ($theme['backgroundColor'] || $theme['menuBgColor'] ||
                    $theme['pageHeaderBgColor'] || $theme['pageHeaderTextColor']) {
                Yii::app()->db->createCommand ($sql)
                    ->execute($params);
            }
        } else {
            // Lookup the theme's name and set it: before, this field held id
            $themeName = Yii::app()->db->createCommand()
                ->select('fileName')
                ->from('x2_media')
                ->where('id = :id', array(':id' => $theme['themeName']))
                ->queryScalar();
            $theme['themeName'] = $themeName;
        }

        $theme = CJSON::encode($theme);
        if (!$theme)
            continue;
        $params = array(
            ':theme' => $theme,
            ':user' => $user,
        );
        Yii::app()->db->createCommand('UPDATE x2_profile SET theme = :theme WHERE username = :user')
            ->execute($params);
    }
};

$migrateUserThemes();
?>
