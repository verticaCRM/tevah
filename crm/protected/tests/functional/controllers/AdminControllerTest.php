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

Yii::import('application.tests.WebTestCase');
Yii::import('application.controllers.AdminController');
Yii::import('application.models.Modules');
Yii::import('application.components.util.FileUtil');

/**
 * Test for the Admin controller methods
 *
 * @package application.tests.functional.controllers
 */
class AdminControllerTest extends X2WebTestCase {

    public $fixtures = array(
        'modules' => 'Modules',
        'actions' => array('Actions', '.ImportTest'),
        'contacts' => array('Contacts', '.ImportTest'),
        'accounts' => array('Accounts', '.ImportTest'),
        'opportunities' => array('Opportunity', '.ImportTest'),
        'services' => array('Services', '.ImportTest'),
        'product' => array('Product', '.ImportTest'),
        'x2Leads' => array('X2Leads', '.ImportTest'),
        'quotes' => array('Quote', '.ImportTest'),
        'docs' => array('Docs', '.ImportTest'),
        'bugReports' => array('BugReports', '.ImportTest'),
    );

    /**
     * Array of CSVs to test. These are loaded from protected/tests/data/csvs/
     */
    public $csvs = array(
        'actions',
        'contacts',
        'accounts',
        'opportunity',
        'services',
        'product',
        'x2Leads',
        'quote',
        'docs',
        'bugReports',
    );

    /**
     * Array of attributes that are to be ignored when verifying imports. These are
     * usually generated or updated, and will be different in most cases
     */
    public $ignoreImportFields = array(
        'createDate',
        'lastUpdated',
        'lastActivity',
        'trackingKey',
    );

    /**
     * Copy a module directory structure from the tests/data directory
     */
    protected function setupModule($moduleName) {
        $baseDir = Yii::app()->basePath;
        $src = implode(DIRECTORY_SEPARATOR, array(
            $baseDir,
            'tests',
            'data',
            'testModules',
            $moduleName
        ));
        $dest = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $moduleName
        ));
        $this->assertTrue(FileUtil::ccopy ($src, $dest));
    }

    /**
     * Copy a module directory structure from the tests/data directory
     */
    protected function cleanupModule($moduleName) {
        $dest = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $moduleName
        ));
        FileUtil::rrmdir ($dest);
        $this->assertTrue(!is_dir($dest));
    }

    /**
     * Remove fields that are to be ignored when verifying imports
     * @param array $attributes
     * @return filtered attributes
     */
    protected function removeIgnoredFields(&$attributes) {
        $attributes = array_diff_key ($attributes, array_flip ($this->ignoreImportFields));
    }

    /**
     * Return the translated validation failure text
     */
    protected function getFailedValidationText() {
        return Yii::t('admin', 'have failed validation and were not imported');
    }

    /**
     * Navigate to the import page, upload a CSV, and ensure it was
     * properly uploaded
     * @param string $model Name of the model to import
     */
    protected function prepareImport($model, $csvName) {
        $this->openX2 ('/admin/importModels?model='.ucfirst($model));
        $csv = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'csvs',
            $csvName
        ));
        $this->type ('data', $csv);
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        $this->assertCsvUploaded ($csv);
    }

    /**
     * Click the 'process' link to begin the import, and assert ui functions
     * as expected
     */
    protected function beginImport() {
        $this->click ("css=#process-link");
        $this->waitForTextPresent ("Import Complete");
        $this->assertAlert ("Import Complete!");
    }

    /**
     * Save specified records in memory and remove so that the fields being
     * imported can be verified
     * @param string $model Name of the model
     * @return array of model attribute arrays, indexed by the original model ID
     */
    protected function stashModels ($modelName) {
        $models = X2Model::model ($modelName)->findAll();
        $attributes = array();
        foreach ($models as $model) {
            $attributes[$model->id] = $model->attributes;
            $model->delete();
        }
        return $attributes;
    }

    /********************************************************************
     * Tests
     ********************************************************************/
    public function testConvertCustomModules() {
        // Launch conversion without any new modules. Should have no errors
        $this->openX2 ('/admin/convertCustomModules');
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        $this->assertElementNotPresent ("css=#x2-php-error");

        // Copy over a legacy module, convert, and verify
        $legacyModule = $this->modules('legacyModule');
        $this->setupModule ($legacyModule->name);
        $this->openX2 ('/admin/convertCustomModules');
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        $this->assertElementNotPresent ("css=#x2-php-error");
        $this->assertConvertedLegacyModule ($legacyModule->name);
        $this->cleanupModule ($legacyModule->name);

        // Verify behavior when conversion fails and backups are handled properly
        $controllerFile= implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $legacyModule->name,
            'controllers',
            'DefaultController.php'
        ));
        $expected = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'testModules',
            $legacyModule->name
        ));
        $actual = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $legacyModule->name
        ));

        $this->setupModule ($legacyModule->name);
        chmod($controllerFile, 0440); // unwritable file will cause conversion to fail
        $this->openX2 ('/admin/convertCustomModules');
        $this->clickAndWait ("dom=document.querySelector ('input[type=\"submit\"]')");
        $this->assertElementNotPresent ("css=#x2-php-error");
        $this->assertTextPresent ("Fatal error - Unable to change class declaration. ".
            "Aborting module conversion.");
        $this->assertTextPresent ("Module backup was successfully restored.");

        $this->assertModulesEqual ($expected, $actual);
        $this->cleanupModule ($legacyModule->name);
    }

    /**
     * Iterate over possible module imports, testing to ensure that attributes
     * are correctly imported and set according to importer conventions when
     * the CSV contains valid records
     */
    public function testValidRecordImport() {
        foreach ($this->csvs as $modelName) {
            $this->prepareImport ($modelName, $modelName.'.csv');
            $expected = $this->stashModels ($modelName);
            $this->assertGreaterThan (0, count($expected),
                'Failed to load fixture! Models were expected');

            $this->beginImport();
            $this->assertNoValidationErrorsPresent();
            $this->assertModelsWereImported ($expected, $modelName);
        }
    }

    /**
     * Import an Account, Contact, and Action to ensure relationships and links are
     * correctly established
     */
    public function testImportRelations() {
        // Import the account, contact, and action
        $relationCsvs = array(
            'Accounts' => 'relations-accounts.csv',
            'Contacts' => 'relations-contacts.csv',
            'Actions' => 'relations-actions.csv',
        );
        foreach ($relationCsvs as $modelType => $csvFile) {
            $this->prepareImport ($modelType, $csvFile);
            $this->beginImport();
            $this->assertNoValidationErrorsPresent();
        }

        // Assert that Relationship records were created
        $this->assertImportRelation(array(
            array('type' => 'Contacts', 'id' => 8888),
            array('type' => 'Accounts', 'id' => 9999),
        ));
        $this->assertImportRelation(array(
            array('type' => 'Contacts', 'id' => 8889),
            array('type' => 'Accounts', 'id' => 9999),
        ));
        $this->assertImportRelation(array(
            array('type' => 'Contacts', 'id' => 8889),
            array('type' => 'Actions', 'id' => 7778),
        ));

        // Ids as defined in tests/data/csvs/relations-*.csv
        $action = X2Model::model('Actions')->findByPk (7777);
        $association = X2Model::model('Accounts')->findByPk (9999);
        $this->assertActionAssociation ($action, $association);

        $action = X2Model::model('Actions')->findByPk (7778);
        $association = X2Model::model('Contacts')->findByPk (8888);
        $this->assertActionAssociation ($action, $association);
    }

    /**
     * Ensure the importer raises validation errors when data in the supplied
     * CSV is malformed
     */
    public function testImportValidationFailures() {
        $failureCsvs = array(
            'Contacts' => 'failure-contacts.csv',
        );
        foreach ($failureCsvs as $modelType => $csvFile) {
            $this->prepareImport ($modelType, $csvFile);
            $this->beginImport();
            $this->assertValidationErrorsPresent();
            $this->assertCorrectFailedRecords ($csvFile);
        }
    }

    /********************************************************************
     * Assert methods
     ********************************************************************/
    protected function assertConvertedLegacyModule($moduleName) {
        $path = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'modules',
            $moduleName
        ));
        $defaultController = implode(DIRECTORY_SEPARATOR, array(
            $path,
            'controllers',
            'DefaultController.php'
        ));
        $controllerClass = ucfirst($moduleName)."Controller";
        $controllerFile = implode(DIRECTORY_SEPARATOR, array(
            $path,
            'controllers',
            $controllerClass.".php"
        ));
        $this->assertTrue(is_dir($path));

        // Ensure the controller and views directory were converted from pre-3.5.1 format
        $newViewDir = implode(DIRECTORY_SEPARATOR, array($path, 'views', $moduleName));
        $this->assertTrue( is_dir($newViewDir) );
        $this->assertFileNotExists( $defaultController );
        $this->assertFileExists( $controllerFile );
        $file = Yii::app()->file->set($controllerFile);
        $contents = $file->getContents();
        $classnameRegex = "/class ".$controllerClass."/";
        $this->assertTrue(preg_match ($classnameRegex, $contents) === 1);

        // Assert that the views have been updated to retrieve item name from the db
        $viewPath = implode(DIRECTORY_SEPARATOR, array($path, "views", $moduleName));
        $viewFiles = scandir ($viewPath);
        foreach ($viewFiles as $viewFile) {
            // Skip partials and anything non-php
            if (!preg_match('/^[^_].*\.php$/', $viewFile))
                continue;
            $file = Yii::app()->file->set($viewPath.DIRECTORY_SEPARATOR.$viewFile);
            $contents = $file->getContents();
            if ($viewFile === 'index.php') {
                $searchPattern = "/'title'=>Modules::displayName\(true, \\\$moduleConfig\['moduleName'\]\)/m";
                $this->assertTrue(preg_match ($searchPattern, $contents) === 1);
            }
            $this->assertTrue(preg_match ("/Modules::itemDisplayName()/", $contents) === 1);
            $this->assertTrue(preg_match ("/\\\$moduleConfig\['recordName'\]/", $contents) === 0);
        }
    }

    /**
     * Compare two module structures recursively
     * @param string $expected Path to expected module layout
     * @param string $actual Path to actual module layout
     */
    protected function assertModulesEqual($expected, $actual) {
        $moduleName = explode(DIRECTORY_SEPARATOR, $expected);
        $moduleName = end($moduleName);
        $viewsDir = 'views'.DIRECTORY_SEPARATOR.$moduleName;

        foreach (array('controllers', 'models', $viewsDir) as $dir) {
            $expectedFiles = scandir ($expected.DIRECTORY_SEPARATOR.$dir);
            foreach ($expectedFiles as $file) {
                if (in_array($file, array('.', '..')))
                    continue;
                $expectedFile = implode(DIRECTORY_SEPARATOR, array($expected, $dir, $file));
                $actualFile = implode(DIRECTORY_SEPARATOR, array($actual, $dir, $file));
                $this->assertFileEquals ($expectedFile, $actualFile);
            }
        }
    }

    /**
     * Assert that the CSV exists and the file contents are equal
     * @param string $csv Path to the uploaded csv
     */
    protected function assertCsvUploaded($csv) {
        $uploadedPath = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'data.csv'
        ));
        $this->assertFileExists ($uploadedPath);
        $this->assertFileEquals ($csv, $uploadedPath);
    }

    /**
     * Assert that all models in the CSV were properly imported
     * @param array $expected Array of expected model attributes, indexed by model ID.
     *  This attribute expects a parameter like that returned by {@link stashModels}
     * @param string $model Name of the model that was imported
     */
    protected function assertModelsWereImported($expected, $modelName) {
        $models = X2Model::model ($modelName)->findAll();
        foreach ($models as $model) {
            $attributes = $model->attributes;
            $this->assertArrayHasKey ($model->id, $expected);
            $expectedAttributes = $expected[$model->id];
            $this->removeIgnoredFields ($expectedAttributes);
            $this->removeIgnoredFields ($attributes);
            $this->assertEquals ($expectedAttributes, $attributes);
        }
    }

    /**
     * Assert that relationships were created properly, and that nameIds used for
     * link type fields are correctly set
     * @param array $models Array of associative arrays, each with a model id and type
     */
    protected function assertImportRelation($models) {
        $this->assertTrue (is_array($models));
        $this->assertTrue (count($models) === 2);
        $whereClause = 'firstType = :firstType AND firstId = :firstId AND '.
                       'secondType = :secondType AND secondId = :secondId';
        $relationModelId = Yii::app()->db->createCommand()
            ->select ('id')
            ->from ('x2_relationships')
            ->where ($whereClause, array(
                ':firstType' => $models[0]['type'],
                ':firstId' => $models[0]['id'],
                ':secondType' => $models[1]['type'],
                ':secondId' => $models[1]['id'],
            ))->orWhere ($whereClause, array(
                ':firstType' => $models[1]['type'],
                ':firstId' => $models[1]['id'],
                ':secondType' => $models[0]['type'],
                ':secondId' => $models[0]['id'],
            ))->queryScalar();
        $this->assertTrue (!is_null($relationModelId),
            'Failed to locate a relationship between '.$models[0]['type'].' '.$models[0]['id'].
            ' and '.$models[1]['type'].' '.$models[1]['id']);
    }

    /**
     * Assert that an Action's association* fields are properly set
     * @param Actions $action
     * @param X2Model $association
     */
    protected function assertActionAssociation(Actions $action, X2Model $association) {
        $associationNameId = Fields::nameId ($association->name, $association->id);
        $this->assertEquals (lcfirst(get_class($association)), $action->associationType);
        $this->assertEquals ($association->id, $action->associationId);
        $this->assertEquals ($associationNameId, $action->associationName);
    }

    /**
     * Assert that the failedRecords.csv file generated on validation failures
     * is as expected
     */
    protected function assertCorrectFailedRecords($csv) {
        $generatedCsv = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'data',
            'failedRecords.csv'
        ));
        $expectedCsv = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'csvs',
            str_replace('.csv', '-expected.csv', $csv)
        ));
        $this->assertFileExists ($generatedCsv);
        $this->assertFileEquals ($expectedCsv, $generatedCsv);
    }

    /**
     * Assert that no validation errors were present after import
     */
    protected function assertNoValidationErrorsPresent() {
        $this->assertTextNotPresent ($this->getFailedValidationText());
    }

    /**
     * Assert that validation errors were present after import
     */
    protected function assertValidationErrorsPresent() {
        $this->assertTextPresent ($this->getFailedValidationText());
    }
}
