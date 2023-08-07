<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Contract\RoutableAction;
use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Routing\CompileCache;
use Bitrix\Main\Routing\Controllers\PublicPageController;
use Bitrix\Main\Routing\Router;
use Bitrix\Main\Routing\RoutingConfigurator;
use Bitrix\Main\SystemException;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/start.php");

$application = Main\Application::getInstance();
$application->initializeExtendedKernel(array(
	"get" => $_GET,
	"post" => $_POST,
	"files" => $_FILES,
	"cookie" => $_COOKIE,
	"server" => $_SERVER,
	"env" => $_ENV
));

$router = $application->getRouter();

// match request
$request = Context::getCurrent()->getRequest();
$route = $router->match($request);

if ($route !== null)
{
	$application->setCurrentRoute($route);

	// copy route parameters to the request
	if ($route->getParametersValues())
	{
		foreach ($route->getParametersValues()->getValues() as $name => $value)
		{
			$_GET[$name] = $value;
			$_REQUEST[$name] = $value;
		}
	}

	$_SERVER["REAL_FILE_PATH"] = '/bitrix/routing_index.php';
	$controller = $route->getController();

	if ($controller instanceof PublicPageController)
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
		$io = CBXVirtualIo::GetInstance();

		$_SERVER["REAL_FILE_PATH"] = $controller->getPath();

		include_once($io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'].$controller->getPath()));
		die;
	}
	elseif ($controller instanceof \Closure)
	{
		$binder = Main\Engine\AutoWire\Binder::buildForFunction($controller);

		// pass current route
		$binder->appendAutoWiredParameter(new Parameter(
			Main\Routing\Route::class,
			fn () => $route
		));

		// pass request
		$binder->appendAutoWiredParameter(new Parameter(
			Main\HttpRequest::class,
			fn () => Context::getCurrent()->getRequest()
		));

		// pass named parameters
		$binder->setSourcesParametersToMap([
			$route->getParametersValues()->getValues()
		]);

		// init kernel
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

		// call
		$result = $binder->invoke();

		// send response
		if ($result !== null)
		{
			if ($result instanceof Main\HttpResponse)
			{
				// ready response
				$response = $result;
			}
			elseif (is_array($result))
			{
				// json
				$response = new Json($result);
			}
			else
			{
				// string
				$response = new Main\HttpResponse;
				$response->setContent($result);
			}

			$application->getContext()->setResponse($response);
			$response->send();
		}

		// terminate app
		$application->terminate(0);
	}
	elseif (is_array($controller))
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

		// classic controller
		[$controllerClass, $actionName] = $controller;
		Loader::requireClass($controllerClass);

		if (is_subclass_of($controllerClass, Main\Engine\Controller::class))
		{
			if (substr($actionName, -6) === 'Action')
			{
				$actionName = substr($actionName, 0, -6);
			}

			/** @var Main\HttpApplication $app */
			$app = Main\Application::getInstance();
			$app->runController($controllerClass, $actionName);
		}
	}
	elseif (is_string($controller))
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

		// actually action could be attached to a few controllers
		// but what if action was made for the one controller only
		// then it could be used in routing
		$actionClass = $controller;
		Loader::requireClass($actionClass);

		if (is_subclass_of($controller, Action::class))
		{
			if (is_subclass_of($actionClass, RoutableAction::class))
			{
				/** @var RoutableAction $actionClass */
				$controllerClass = $actionClass::getControllerClass();
				$actionName = $actionClass::getDefaultName();

				/** @var Main\HttpApplication $app */
				$app = Main\Application::getInstance();
				$app->runController($controllerClass, $actionName);
			}
			else
			{
				throw new SystemException(sprintf(
					'Action `%s` should implement %s interface for being called in routing',
					$actionClass, RoutableAction::class
				));
			}
		}
	}

	throw new SystemException(sprintf(
		'Unknown controller `%s`', $controller
	));
}

require_once __DIR__.'/urlrewrite.php';