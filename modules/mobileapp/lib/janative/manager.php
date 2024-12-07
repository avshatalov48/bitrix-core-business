<?php

namespace Bitrix\MobileApp\Janative;

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\MobileApp\Janative\Entity\Component;
use Bitrix\MobileApp\Janative\Entity\Extension;
use Exception;

class Manager
{
	private static ?array $workspaces = null;
	private static ?array $availableComponents = null;
	private static array $extensionPaths = [];

	private static function getWorkspaces(): array
	{
		if (self::$workspaces == null)
		{
			self::$workspaces = [];
			$events = EventManager::getInstance()->findEventHandlers('mobileapp', 'onJNComponentWorkspaceGet');
			foreach ($events as $event)
			{
				$path = ExecuteModuleEventEx($event);
				if (!in_array($path, self::$workspaces))
				{
					self::$workspaces[] = $path;
				}
			}
		}

		return self::$workspaces;
	}

	/**
	 * @return mixed
	 * @throws FileNotFoundException
	 */
	private static function fetchComponents(): ?array
	{
		if (self::$availableComponents == null)
		{
			self::$availableComponents = [];
			$rawComponentList = [];
			$workspaces = self::getWorkspaces();
			foreach ($workspaces as $path)
			{
				$componentDir = new Directory(Application::getDocumentRoot() . $path . '/components/');
				if (!$componentDir->isExists())
				{
					continue;
				}

				$namespaces = $componentDir->getChildren();
				foreach ($namespaces as $NSDir)
				{
					if (!$NSDir->isDirectory())
					{
						continue;
					}

					$namespaceItems = $NSDir->getChildren();
					$namespace = $NSDir->getName();
					foreach ($namespaceItems as $item)
					{
						try
						{
							$component = new Component($item->getPath(), $namespace);
							$name = $item->getName();
							$name = ($namespace == 'bitrix' ? $name : $namespace . ':' . $name);
							$rawComponentList[$name] = $component;
						}
						catch (Exception $e)
						{

						}
					}
				}
			}

			self::$availableComponents = $rawComponentList;

		}

		return self::$availableComponents;
	}

	/**
	 * @param $ext
	 * @return string|string[]|null
	 */
	public static function getExtensionPath($ext): array|string|null
	{
		if (!isset(self::$extensionPaths[$ext]))
		{
			$desc = Utils::extractEntityDescription($ext);
			$workspaces = self::getWorkspaces();
			foreach ($workspaces as $path)
			{
				$extensionDir = new Directory(Application::getDocumentRoot() . $path . '/extensions/' . $desc['relativePath']);
				if ($extensionDir->isExists())
				{
					self::$extensionPaths[$ext] = $extensionDir->getPath();
					break;
				}
			}
		}

		return self::$extensionPaths[$ext] ?? null;
	}

	public static function getExtensionResourceList($ext): array
	{
		$extList = is_array($ext) ? $ext : [$ext];

		$extensions = [];
		$alreadyResolved = [];

		foreach ($extList as $extension)
		{
			if (!Manager::getExtensionPath($extension))
			{
				continue;
			}

			Extension::getResolvedDependencyList($extension, $extensions, $alreadyResolved);
		}

		$result = [
			'js' => [],
			'messages' => [],
		];

		foreach ($extensions as $extName)
		{
			$extension = Extension::getInstance($extName);
			$result['messages'] = array_merge($result['messages'], $extension->getLangMessages());
			$result['js'][] = $extension->getRelativePathToFile();
		}

		return $result;
	}

	/**
	 * @param $componentName
	 * @return float|int|string
	 * @throws Exception
	 */
	public static function getComponentVersion($componentName): string
	{
		$component = Component::createInstanceByName($componentName);
		if ($component)
		{
			return $component->getVersion();
		}

		return '1.0';
	}

	/**
	 * @param $componentName
	 * @return string
	 * @throws Exception
	 */
	public static function getComponentPath($componentName): string
	{
		$component = Component::createInstanceByName($componentName);
		if ($component)
		{
			return $component->getPublicPath();
		}

		return '';
	}

	/**
	 * @return array<Component>
	 * @throws FileNotFoundException
	 */
	public static function getAvailableComponents(): ?array
	{
		return self::fetchComponents();
	}

	/**
	 * @param $name
	 * @return Component|null
	 * @throws FileNotFoundException
	 */
	public static function getComponentByName($name): ?Component
	{
		$name = str_replace("bitrix:", "", $name);
		$components = self::fetchComponents();

		return $components[$name] ?? null;
	}

}
