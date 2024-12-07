<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

/**
 * executable file example (project/bitrix/bitrix):
 * #!/usr/bin/php
 * <?php
 * $_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__.'/../');
 * require_once(__DIR__.'/modules/main/dev/cli/bitrix.php');
 */

// include bitrix
require_once 'bootstrap.php';

// default location of composer.json
$composerJsonFile = $_SERVER["DOCUMENT_ROOT"].'/bitrix/composer.json';

// custom location of composer.json from .settings.php
$composerSettings = \Bitrix\Main\Config\Configuration::getValue('composer');
if (!empty($composerSettings['config_path']))
{
	$jsonPath = $composerSettings['config_path'];
	$jsonPath = ($jsonPath[0] == '/')
		? $jsonPath // absolute
		: realpath($_SERVER["DOCUMENT_ROOT"].'/'.$jsonPath); // relative

	if (!empty($jsonPath))
	{
		$composerJsonFile = $jsonPath;
	}
}

// default vendor path has the same parent dir as composer.json has
$vendorPath = dirname($composerJsonFile).'/vendor';

if (file_exists($composerJsonFile) && is_readable($composerJsonFile))
{
	$jsonContent = json_decode(file_get_contents($composerJsonFile), true);

	if (isset($jsonContent['config']['vendor-dir']))
	{
		$vendorPath = realpath(dirname($composerJsonFile).DIRECTORY_SEPARATOR.$jsonContent['config']['vendor-dir']);

		if ($vendorPath === false)
		{
			throw new \Bitrix\Main\SystemException(sprintf(
				'Failed to load vendor libs from %s, path \'%s\' is not readable',
				$composerJsonFile, $jsonContent['config']['vendor-dir']
			));
		}
	}
}

// include composer autoload
require $vendorPath.'/autoload.php';

// initialize symfony
use Symfony\Component\Console\Application;

$application = new Application();

// register  commands
$modules = \Bitrix\Main\ModuleManager::getInstalledModules();
foreach ($modules as $moduleId => $_)
{
	$config = \Bitrix\Main\Config\Configuration::getInstance($moduleId)->get('console');
	if (isset($config['commands']) && is_array($config['commands']))
	{
		if (\Bitrix\Main\Loader::includeModule($moduleId))
		{
			foreach ($config['commands'] as $commandClass)
			{
				if (is_a($commandClass, \Symfony\Component\Console\Command\Command::class, true))
				{
					$application->add(new $commandClass());
				}
			}
		}
	}
}

// run console
$application->run();
