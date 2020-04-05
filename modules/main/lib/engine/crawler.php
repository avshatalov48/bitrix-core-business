<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

final class Crawler
{
	/** @var  Crawler */
	private static $instance;

	private function __construct()
	{
	}

	private function __clone()
	{
	}

	/**
	 * Returns Singleton of Crawler
	 * @return Crawler
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new Crawler;
		}

		return self::$instance;
	}

	public function listActionsByModule($module)
	{
		$actions = array();
		foreach ($this->getNamespaces($module) as $namespace)
		{
			$actions = array_merge(
				$actions,
				$this->getActionsFromControllers($namespace)
			);
		}

		return array_unique($actions);
	}

	private function getActionsFromControllers($namespace)
	{
		$actions = array();
		foreach ($this->getFilesUnderNamespace($namespace) as $className)
		{
			try
			{
				$reflectionClass = new \ReflectionClass($className);
				if ($reflectionClass->isAbstract())
				{
					continue;
				}

				$classNamespace = strtolower(trim($reflectionClass->getNamespaceName(), '\\'));
				$namespace = strtolower(trim($namespace, '\\'));

				if (strpos($classNamespace, $namespace) === false)
				{
					continue;
				}

				if (!$reflectionClass->isSubclassOf(Controller::className()))
				{
					continue;
				}

				$controllerName = strtr($reflectionClass->getName(), '\\', '.');
				$controllerName = strtolower($controllerName);

				$controller = $this->constructController($reflectionClass);
				foreach ($controller->listNameActions() as $actionName)
				{
					$actions[] = $controllerName . '.' . strtolower($actionName);
				}
			}
			catch (\ReflectionException $exception)
			{}
		}

		return $actions;
	}

	private function getFilesUnderNamespace($namespace)
	{
		$path = ltrim($namespace, "\\");    // fix web env
		$path = strtr($path, Loader::ALPHA_UPPER, Loader::ALPHA_LOWER);

		$documentRoot = Context::getCurrent()->getServer()->getDocumentRoot();

		if (preg_match("#[^\\\\/a-zA-Z0-9_]#", $path))
		{
			return array();
		}

		$path = str_replace('\\', '/', $path);
		$pathParts = explode("/", $path);

		if ($pathParts[0] === "bitrix")
		{
			array_shift($pathParts);

			if (empty($pathParts))
			{
				return array();
			}

			$module = array_shift($pathParts);
			if ($module == null || empty($pathParts))
			{
				return array();
			}
		}
		else
		{
			$module1 = array_shift($pathParts);
			$module2 = array_shift($pathParts);
			if ($module1 == null || $module2 == null || empty($pathParts))
			{
				return array();
			}

			$module = $module1 . "." . $module2;
		}

		$rootFolder = $documentRoot . "/bitrix/modules/" . $module . "/lib/" . implode("/", $pathParts);

		$classes = array();
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootFolder)) as $file)
		{
			/** @var $file \SplFileInfo */
			if (!$file->isFile())
			{
				continue;
			}

			if ($file->getPath() === $rootFolder)
			{
				$classes[] = $namespace . '\\' . $file->getBasename('.php');
				continue;
			}

			$relativeFolder = trim(substr($file->getPath(), strlen($rootFolder)), '\\/');
			$classes[] = $namespace . '\\' . strtr($relativeFolder, array('/' => '\\')) . '\\' . $file->getBasename('.php');
		}

		return $classes;
	}

	private function getNamespaces($module)
	{
		$controllersConfig = Configuration::getInstance($module);
		if (!$controllersConfig['controllers'] || !$controllersConfig['controllers']['namespaces'])
		{
			return array();
		}

		$namespaces = array();
		foreach ($controllersConfig['controllers']['namespaces'] as $key => $namespace)
		{
			if (is_string($key))
			{
				$namespaces[] = $key;
			}
			else
			{
				$namespaces[] = $namespace;
			}
		}

		return $namespaces;
	}

	/**
	 * @param \ReflectionClass $reflectionClass
	 *
	 * @return Controller
	 */
	private function constructController(\ReflectionClass $reflectionClass)
	{
		/** @see \Bitrix\Main\Engine\Controller::__construct */
		/** @var $controller Controller */
		$controller =  $reflectionClass->newInstanceArgs();

		return $controller;
	}
}