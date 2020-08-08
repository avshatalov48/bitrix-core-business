<?php

namespace Bitrix\Catalog\v2\IoC;

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\NotSupportedException;

/**
 * Class ContainerBuilder
 *
 * @package Bitrix\Catalog\v2\IoC
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class ContainerBuilder
{
	private static function getLocalPaths($path): array
	{
		$paths = [];
		$root = Application::getDocumentRoot();

		if (File::isFileExists($root.'/bitrix/'.$path))
		{
			$paths[] = $root.'/bitrix/'.$path;
		}

		if (File::isFileExists($root.'/local/'.$path))
		{
			$paths[] = $root.'local/'.$path;
		}

		return $paths;
	}

	private static function getConfigPaths(): array
	{
		if (!defined('CATALOG_CONTAINER_PATH'))
		{
			throw new NotSupportedException('Default container path not found.');
		}

		$configPaths = static::getLocalPaths(CATALOG_CONTAINER_PATH);

		if (empty($configPaths))
		{
			throw new NotSupportedException('Default container config does not exist.');
		}

		return $configPaths;
	}

	private static function loadDependencies(string $customPath = null): array
	{
		$dependencies = [];

		$configPaths = static::getConfigPaths();

		if ($customPath)
		{
			$configPaths[] = $customPath;
		}

		foreach ($configPaths as $configPath)
		{
			$current = include($configPath);

			if (is_array($current))
			{
				$dependencies[] = $current;
			}
			else
			{
				throw new NotSupportedException(sprintf(
					'Config {%s} must return an array.', $current
				));
			}
		}

		return array_merge(...$dependencies);
	}

	private static function buildContainer(array $dependencies): ContainerContract
	{
		$containerClass = $dependencies[Dependency::CONTAINER] ?? null;

		if ($containerClass === null)
		{
			throw new NotSupportedException(sprintf(
				'Container dependency {%s} must be configured in the config file.',
				Dependency::CONTAINER
			));
		}

		/** @var \Bitrix\Catalog\v2\IoC\ContainerContract $container */
		return new $containerClass();
	}

	public static function buildFromConfig(string $customPath = null): ContainerContract
	{
		$dependencies = static::loadDependencies($customPath);
		$container = static::buildContainer($dependencies);

		foreach ($dependencies as $dependency => $entity)
		{
			$container->inject($dependency, $entity);
		}

		return $container;
	}
}