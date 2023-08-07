<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;

class ControllerResolver
{
	const DEFAULT_VENDOR = 'bitrix';

	public static function createController(string $controllerName, array $options = []): ?UploaderController
	{
		[$moduleId, $className] = self::resolveName($controllerName);

		if (!is_string($className))
		{
			return null;
		}

		if (is_string($moduleId) && self::canIncludeModule($moduleId))
		{
			Loader::includeModule($moduleId);
		}

		try
		{
			$controllerClass = new \ReflectionClass($className);
			if ($controllerClass->isAbstract())
			{
				return null;
			}

			if (!$controllerClass->isSubclassOf(UploaderController::class))
			{
				return null;
			}

			/** @var UploaderController $controller */
			$controller = $controllerClass->newInstance($options);

			// $baseClass = new \ReflectionClass(UploaderController::class);
			// $moduleIdProperty = $baseClass->getProperty('moduleId');
			// $moduleIdProperty->setAccessible(true);
			// $moduleIdProperty->setValue($controller, $moduleId);
			//
			// $nameProperty = $baseClass->getProperty('name');
			// $nameProperty->setAccessible(true);
			// $nameProperty->setValue($controller, $controllerName);

			if (!$controller->isAvailable())
			{
				return null;
			}

			return $controller;
		}
		catch (\ReflectionException $exception)
		{
			$application = HttpApplication::getInstance();
			$exceptionHandler = $application->getExceptionHandler();
			$exceptionHandler->writeToLog($exception);
		}

		return null;
	}

	public static function resolveName(string $controllerName): array
	{
		$controllerName = trim($controllerName);
		if (mb_strlen($controllerName) < 1)
		{
			return [null, null];
		}

		[$vendor, $controllerName] = self::resolveVendor($controllerName);
		[$moduleId, $className] = self::resolveModuleAndClass($controllerName);
		$moduleId = self::refineModuleName($vendor, $moduleId);

		$className = self::buildClassName($vendor, $moduleId, $className);

		return [$moduleId, $className];
	}

	public static function getNameByController(UploaderController $controller): string
	{
		$parts = explode('\\', get_class($controller));
		$vendor = mb_strtolower(array_shift($parts));
		$moduleId = mb_strtolower(array_shift($parts));

		$parts = array_map(
			function ($part) {
				return lcfirst($part);
			},
			$parts
		);

		if ($vendor === self::DEFAULT_VENDOR)
		{
			return $moduleId . '.' . implode('.', $parts);
		}
		else
		{
			return $vendor . ':' . $moduleId . '.' . implode('.', $parts);
		}
	}

	private static function buildClassName(string $vendor, string $moduleId, string $className): string
	{
		if ($vendor === self::DEFAULT_VENDOR)
		{
			$moduleId = ucfirst($moduleId);
			$namespace = "\\Bitrix\\{$moduleId}";
		}
		else
		{
			$moduleParts = explode('.', $moduleId);
			$moduleParts = array_map(
				function ($part) {
					return ucfirst(trim(trim($part), '\\'));
				},
				$moduleParts
			);

			$namespace = "\\" . join('\\', $moduleParts);
		}

		$classNameParts = explode('.', $className);
		$classNameParts = array_map(
			function ($part) {
				return ucfirst(trim(trim($part), '\\'));
			},
			$classNameParts
		);

		if (!$classNameParts)
		{
			return $namespace;
		}

		return "{$namespace}\\" . join('\\', $classNameParts);
	}

	private static function resolveModuleAndClass(string $controllerName): array
	{
		$parts = explode('.', $controllerName);
		$moduleId = array_shift($parts);
		$className = implode('.', $parts);

		return [$moduleId, $className];
	}

	private static function resolveVendor(string $controllerName): array
	{
		[$vendor, $controllerName] = explode(':', $controllerName) + [null, null];

		if (!$controllerName)
		{
			$controllerName = $vendor;
			$vendor = self::DEFAULT_VENDOR;
		}

		return [$vendor, $controllerName];
	}

	private static function refineModuleName($vendor, $moduleId): string
	{
		if ($vendor === self::DEFAULT_VENDOR)
		{
			return mb_strtolower($moduleId);
		}

		return mb_strtolower($vendor . '.' . $moduleId);
	}

	private static function canIncludeModule(string $moduleId): bool
	{
		$settings = \Bitrix\Main\Config\Configuration::getInstance($moduleId)->get('ui.uploader');
		if (empty($settings) || !is_array($settings))
		{
			return false;
		}

		return isset($settings['allowUseControllers']) && $settings['allowUseControllers'] === true;
	}
}
