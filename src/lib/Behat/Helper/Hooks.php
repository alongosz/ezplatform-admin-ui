<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformAdminUi\Behat\Helper;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Behat\Testwork\Tester\Result\TestResult;
use EzSystems\EzPlatformAdminUi\Behat\PageElement\ElementFactory;
use EzSystems\EzPlatformAdminUi\Behat\PageObject\PageObjectFactory;
use WebDriver\LogType;

class Hooks extends RawMinkContext
{
    private const CONSOLE_LOGS_LIMIT = 10;
    private const APPLICATION_LOGS_LIMIT = 10;
    private const LOG_FILE_NAME = 'travis_test.log';

    use KernelDictionary;

    /** @BeforeScenario
     */
    public function setInstallTypeBeforeScenario()
    {
        $env = new Environment($this->getContainer());
        $installType = $env->getInstallType();

        PageObjectFactory::setInstallType($installType);
        ElementFactory::setInstallType($installType);
        EzEnvironmentConstants::setInstallType($installType);
    }

    /** @BeforeScenario @restoreEnvironmentBefore
     * Restores the database and clears cache for tests marked with @restoreEnvironmentBefore tag
     */
    public function restoreEnvironmentBeforeScenario()
    {
        $env = new Environment($this->getContainer());

        $env->restoreDatabase();
        $env->clearCache();
    }

    /** @AfterScenario @restoreEnvironmentAfter
     * Restores the database and clears cache for tests marked with @restoreEnvironmentAfter tag
     */
    public function restoreEnvironmentAfterScenario()
    {
        $env = new Environment($this->getContainer());

        $env->restoreDatabase();
        $env->clearCache();
    }

    /** @AfterStep */
    public function getLogsAfterFailedStep(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() !== TestResult::FAILED) {
            return;
        }

        $driver = $this->getSession()->getDriver();
        if ($driver instanceof Selenium2Driver) {
            $browserLogEntries = $this->parseBrowserLogs($driver->getWebDriverSession()->log(LogType::BROWSER));
            $this->displayLogEntries('JS console errors:', $browserLogEntries);
        }

        $applicationLogEntries = $this->parseApplicationlogs();
        $this->displayLogEntries('Application errors:', $applicationLogEntries);
    }

    private function parseApplicationlogs(): array
    {
        $logEntries = [];
        $counter = 0;

        $file = @fopen(sprintf('%s/%s', $this->getKernel()->getLogDir(), self::LOG_FILE_NAME), 'r');

        if ($file === false) {
            return $logEntries;
        }

        while (!feof($file)) {
            if ($counter >= self::APPLICATION_LOGS_LIMIT) {
                break;
            }

            $logEntries[] = fgets($file);
            ++$counter;
        }

        fclose($file);

        return $logEntries;
    }

    private function parseBrowserLogs($logEntries): array
    {
        $filter = new BrowserLogFilter();

        if (empty($logEntries)) {
            return [];
        }

        $errorMessages = array_column($logEntries, 'message');
        $errorMessages = $filter->filter($errorMessages);

        return \array_slice($errorMessages, 0, self::CONSOLE_LOGS_LIMIT);
    }

    private function displayLogEntries($sectionName, $logEntries)
    {
        if (empty($logEntries)) {
            return;
        }

        echo sprintf('%s' . PHP_EOL, $sectionName);

        foreach ($logEntries as $logEntry) {
            echo sprintf('%s' . PHP_EOL, $logEntry);
        }
    }
}
