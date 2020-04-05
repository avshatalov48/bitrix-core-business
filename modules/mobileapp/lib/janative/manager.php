<?php

namespace Bitrix\MobileApp\Janative;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\MobileApp\Janative\Entity\Component;
use Bitrix\MobileApp\Janative\Entity\Extension;

class Manager
{
	/**
	 * @var array
	 */
	private static $workspaces;
	/**
	 * @var array|null
	 */
	private static $availableComponents;
	private $extensions;
	private $initiated;


	private function __construct()
	{
		$this->initiated = false;
		$this->extensions = [];
	}


	/**
	 * @return array
	 */
	private static function getWorkspaces()
	{
		if(self::$workspaces == null)
		{
			self::$workspaces = [];
			$events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("mobileapp", "onJNComponentWorkspaceGet");
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
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	private static function fetchComponents()
	{
		if(self::$availableComponents == null)
		{
			self::$availableComponents = [];
			$workspaces = self::getWorkspaces();
			foreach ($workspaces as $path)
			{
				$componentDir = new Directory(Application::getDocumentRoot() . $path . "/components/");
				if(!$componentDir->isExists())
					continue;

				$namespaces = $componentDir->getChildren();
				foreach ($namespaces as $NSDir)
				{
					if (!$NSDir->isDirectory())
						continue;

					$namespaceItems = $NSDir->getChildren();
					$namespace = $NSDir->getName();
					foreach ($namespaceItems as $item)
					{
						try
						{
							$component = new Component($item->getPath());
							$name = $item->getName();
							if($namespace == "bitrix")
							{
								self::$availableComponents[$name] =$component->getInfo();
							}
							else
							{
								self::$availableComponents[$namespace . ':' . $name] = $component->getInfo();
							}
						}
						catch (\Exception $e)
						{

						}

					}
				}
			}

		}

		return self::$availableComponents;
	}

	/**
	 * @param $ext
	 * @return string|string[]|null
	 */
	public static function getExtensionPath($ext)
	{
		$desc = Utils::extractEntityDescription($ext);
		$extensionPath = null;
		$workspaces = self::getWorkspaces();
		foreach ($workspaces as $path)
		{
			$extensionDir = new Directory(Application::getDocumentRoot() . $path . "/extensions/" . $desc["relativePath"]);
			if($extensionDir->isExists())
			{
				$extensionPath = $extensionDir->getPath();
			}
		}

		return $extensionPath;
	}

	public static function getExtensionResourceList($ext)
	{
		$extList = is_array($ext)? $ext: [$ext];

		$extensions = [];
		$alreadyResolved = [];

		foreach ($extList as $ext)
		{
			if (!Manager::getExtensionPath($ext))
			{
				continue;
			}

			Extension::getResolvedDependencyList($ext, $extensions, $alreadyResolved);
		}

		$result = [
			'js' => [],
			'messages' => [],
		];

		foreach ($extensions as $extName)
		{
			$extension = new Extension($extName);
			$result['messages'] = array_merge($result['messages'], $extension->getLangMessages());
			$result['js'][] = $extension->getRelativePathToFile();
		}

		return $result;
	}

	/**
	 * @param $componentName
	 * @return float|int|string
	 * @throws \Exception
	 */
	public static function getComponentVersion($componentName)
	{
		$component = Component::createInstanceByName($componentName);
		if($component)
			return $component->getVersion();

		return 1.0;
	}

	/**
	 * @param $componentName
	 * @return string
	 * @throws \Exception
	 */
	public static function getComponentPath($componentName)
	{
		$component = Component::createInstanceByName($componentName);
		if($component)
			return $component->getPublicPath();

		return "";
	}

	/**
	 * @return mixed
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	public static function getAvailableComponents()
	{
		return self::fetchComponents();
	}

	/**
	 * @param $name
	 * @return Component|null
	 * @throws \Bitrix\Main\IO\FileNotFoundException
	 */
	public static function getComponentByName($name)
	{
		$workspaces = self::getWorkspaces();
		foreach ($workspaces as $path)
		{
			$componentDir = new Directory(Application::getDocumentRoot() . $path . "/components/");
			if(!$componentDir->isExists())
				continue;

			$namespaces = $componentDir->getChildren();
			foreach ($namespaces as $NSDir)
			{
				if (!$NSDir->isDirectory())
					continue;

				$namespaceItems = $NSDir->getChildren();
				$namespace = $NSDir->getName();
				foreach ($namespaceItems as $item)
				{
					try
					{
						if($name === $item->getName() || $namespace . ':' . $name === $item->getName())
						{
							return new Component($item->getPath());
						}
					}
					catch (\Exception $e)
					{
						$a = $e;
					}
				}
			}
		}

		return null;
	}


}