<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Config\Configuration;
use Bitrix\Main\SystemException;

final class Resolver
{
	/**
	 * Returns list instance of controller and action in this controller.
	 *
	 * @param string $module
	 * @param string $action
	 * @param string $scope
	 *
	 * @return array|null
	 */
	public static function getControllerAndAction($module, $action, $scope = Controller::SCOPE_AJAX)
	{
		$controllersConfig = Configuration::getInstance($module);
		if (!$controllersConfig['controllers'] || !$controllersConfig['controllers']['namespaces'])
		{
			return null;
		}

		$parts = explode('.', $action);
		$actionName = array_pop($parts); //drop action name
		$aliases = array_change_key_case(array_flip($controllersConfig['controllers']['namespaces']), CASE_LOWER);
		$probablyPrefix = strtolower(reset($parts));

		$namespacePrefix = '';
		if (isset($aliases[$probablyPrefix]) && is_string($aliases[$probablyPrefix]))
		{
			$namespacePrefix = trim($aliases[$probablyPrefix], '\\');
			array_shift($parts); //drop prefix
		}

		$controllerClass = $namespacePrefix . '\\' . implode('\\', $parts);
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

	/**
	 * Returns name of controller for using in routing.
	 * The name is built by rules: fully qualified name contains delimiters by dot.
	 *
	 * @param Controller $controller Controller.
	 *
	 * @return string
	 */
	public static function getNameByController(Controller $controller)
	{
		return strtolower(str_replace('\\', '.', get_class($controller)));
	}
}