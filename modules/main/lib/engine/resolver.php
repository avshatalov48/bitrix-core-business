<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Config\Configuration;

final class Resolver
{
	const DEFAULT_VENDOR = 'bitrix';

	/**
	 * Returns list instance of controller and action in this controller.
	 *
	 * @param string $vendor
	 * @param string $module
	 * @param string $action
	 * @param string $scope
	 *
	 * @return array|null
	 */
	public static function getControllerAndAction($vendor, $module, $action, $scope = Controller::SCOPE_AJAX)
	{
		$parts = explode('.', $action);
		$actionName = array_pop($parts);

		$controllerClass = self::buildControllerClassName($vendor, $module, $parts);
		try
		{
			$reflectionClass = new \ReflectionClass($controllerClass);
			if ($reflectionClass->isAbstract())
			{
				return null;
			}

			if (!$reflectionClass->isSubclassOf(Controller::className()))
			{
				return null;
			}

			/** @var Controller $controller */
			/** @see \Bitrix\Main\Engine\Controller::__construct */
			$controller = $reflectionClass->newInstance();
			$controller->setScope($scope);
			$controller->setCurrentUser(CurrentUser::get());

			return array($controller, $actionName);
		}
		catch (\ReflectionException $exception)
		{}

		return null;
	}

	private static function buildControllerClassName($vendor, $module, array $actionParts)
	{
		$controllerName = array_pop($actionParts);

		if (self::isOldFullQualifiedName($vendor, $module, $actionParts))
		{
			$actionParts[] = $controllerName;
			$className = implode('\\', $actionParts);
			if (!self::checkClassUnderAllowedNamespaces($module, $className))
			{
				return null;
			}

			return $className;
		}

		$namespaces = self::listAllowedNamespaces($module);

		$aliases = array_change_key_case($namespaces, CASE_LOWER);
		$probablyPrefix = mb_strtolower(reset($actionParts));
		if (isset($aliases[$probablyPrefix]))
		{
			$alias = $aliases[$probablyPrefix];
			array_shift($actionParts); //drop prefix
			array_push($actionParts, $controllerName);

			return $alias . '\\' . implode('\\', $actionParts);
		}

		$furtherNamespace = mb_strtolower(self::buildClassNameByAction($vendor, $module, $actionParts));
		if (self::checkClassUnderAllowedNamespaces($module, $furtherNamespace))
		{
			return $furtherNamespace . '\\' . $controllerName;
		}

		$defaultNamespaceByModule = self::getDefaultNamespaceByModule($module);
		if (!$defaultNamespaceByModule)
		{
			return null;
		}

		$defaultPath = mb_strtolower(strtr($defaultNamespaceByModule, ['\\' => '.']));
		array_unshift($actionParts, ...explode('.', $defaultPath));
		array_push($actionParts, $controllerName);

		return implode('\\', $actionParts);
	}

	/**
	 * Returns default namespace by module.
	 * @param string $module Module id.
	 *
	 * @return null|string
	 */
	public static function getDefaultNamespaceByModule($module)
	{
		$controllersConfig = Configuration::getInstance($module);
		if (!$controllersConfig['controllers'] || !isset($controllersConfig['controllers']['defaultNamespace']))
		{
			return null;
		}

		return $controllersConfig['controllers']['defaultNamespace'];
	}

	/**
	 * Checks if the name of action is old full qualified name.
	 * For example: disk.bitrix.disk.controller.file.get.
	 * @param $vendor
	 * @param $module
	 * @param array $actionParts
	 *
	 * @return bool
	 */
	private static function isOldFullQualifiedName($vendor, $module, array $actionParts)
	{
		if ($vendor !== self::DEFAULT_VENDOR)
		{
			return false;
		}

		if (!isset($actionParts[0]) || !isset($actionParts[1]))
		{
			return false;
		}

		return $actionParts[0] === $vendor && $actionParts[1] === $module;
	}

	private static function listAllowedNamespaces($module)
	{
		$controllersConfig = Configuration::getInstance($module);
		if (!$controllersConfig['controllers'])
		{
			return [];
		}

		$namespaces = [];
		if (isset($controllersConfig['controllers']['namespaces']))
		{
			foreach ($controllersConfig['controllers']['namespaces'] as $key => $namespace)
			{
				if (is_int($key))
				{
					$namespaces[] = $namespace;
				}
				else
				{
					$namespaces[$namespace] = $key;
				}
			}
		}

		if (isset($controllersConfig['controllers']['defaultNamespace']))
		{
			$namespaces[] = $controllersConfig['controllers']['defaultNamespace'];
		}

		return $namespaces;
	}

	private static function checkClassUnderAllowedNamespaces($module, $class)
	{
		$namespaces = self::listAllowedNamespaces($module);
		foreach ($namespaces as $namespace)
		{
			if (mb_stripos(ltrim($class, '\\'), ltrim($namespace, '\\')) === 0)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Builds class name by vendor and module.
	 * @param $vendor
	 * @param $module
	 * @param array $actionParts
	 *
	 * @return string
	 */
	private static function buildClassNameByAction($vendor, $module, array $actionParts)
	{
		if($vendor === self::DEFAULT_VENDOR)
		{
			$namespace = "\\Bitrix\\$module";
		}
		else
		{
			$namespace = "\\" . strtr($module, ['.' => '\\']);
		}

		if (!$actionParts)
		{
			return $namespace;
		}

		return "{$namespace}\\" . trim(implode('\\', $actionParts), '\\');
	}
	
	/**
	 * Returns name of controller for using in routing.
	 * The name is built by rules: fully qualified name contains delimiters by dot.
	 * Example: vendor:module.controller.action.
	 *
	 * @param Controller $controller Controller.
	 *
	 * @return string
	 */
	public static function getNameByController(Controller $controller)
	{
		$parts = explode('\\', get_class($controller));
		$vendor = mb_strtolower(array_shift($parts));

		return $vendor . ':' . implode('.', $parts);
	}
}