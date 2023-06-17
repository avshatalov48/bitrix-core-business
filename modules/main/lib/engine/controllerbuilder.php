<?php
namespace Bitrix\Main\Engine;


use Bitrix\Main\ObjectException;

final class ControllerBuilder
{
	public static function build(string $controllerClass, $options): Controller
	{
		try
		{
			$scope = $options['scope'] ?? Controller::SCOPE_AJAX;
			$currentUser = $options['currentUser'] ?? CurrentUser::get();

			$reflectionClass = new \ReflectionClass($controllerClass);
			if ($reflectionClass->isAbstract())
			{
				throw new ObjectException("Controller class should be non abstract.");
			}

			if (!$reflectionClass->isSubclassOf(Controller::class))
			{
				throw new ObjectException("Controller class should be subclass of \Bitrix\Main\Engine\Controller.");
			}

			/** @var Controller $controller */
			/** @see \Bitrix\Main\Engine\Controller::__construct */
			/** @see \Bitrix\Main\Engine\Controller::forward */
			$controller = $reflectionClass->newInstance();
			$controller->setScope($scope);
			$controller->setCurrentUser($currentUser);

			return $controller;
		}
		catch (\ReflectionException $exception)
		{
			throw new ObjectException("Unable to construct controller {{$controllerClass}}.", $exception);
		}
	}
}