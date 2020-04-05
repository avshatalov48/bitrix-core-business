<?php

namespace Bitrix\MobileApp\Janative;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\MobileApp\Janative\Entity\Component;
use Bitrix\MobileApp\Janative\Entity\Extension;

class Manager
{
	private static $instance;
	public $workspaces;
	public $availableComponents;
	private $extensions;

	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new Manager();
		}

		return self::$instance;
	}


	private function __construct()
	{
		$this->workspaces = [];
		$this->availableComponents = [];
		$this->extensions = [];
		$this->workspaces = [];
		$events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("mobileapp", "onJNComponentWorkspaceGet");
		foreach ($events as $event)
		{
			$path = ExecuteModuleEventEx($event);
			if (!in_array($path, $this->workspaces))
			{
				$this->workspaces[] = $path;
			}
		}

		$this->fetchComponents();
		self::$instance = $this;
	}

	private function fetchComponents()
	{
		$componentList = [];
		$workspaces = $this->workspaces;
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
							$componentList[$name] = $component->getInfo();
						}
						else
						{
							$componentList[$namespace . ':' . $name] = $component->getInfo();
						}
					}
					catch (\Exception $e)
					{

					}

				}
			}
		}

		$this->availableComponents = $componentList;
	}

	/**
	 * @param $ext
	 * @return string|string[]|null
	 */
	public function getExtensionPath($ext)
	{
		$desc = Utils::extractEntityDescription($ext);
		foreach ($this->workspaces as $path)
		{
			$extensionDir = new Directory(Application::getDocumentRoot() . $path . "/extensions/" . $desc["relativePath"]);
			if($extensionDir->isExists())
			{
				return $extensionDir->getPath();
			}
		}

		return null;
	}

	public static function getExtensionResourceList($ext)
	{
		$extList = is_array($ext)? $ext: [$ext];

		$extensions = [];
		$alreadyResolved = [];

		foreach ($extList as $ext)
		{
			if (!Manager::getInstance()->getExtensionPath($ext))
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

			$result['js'][] = $extension->getRelativePath();
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

	public function extractNamespace($entityPath)
	{

	}

}