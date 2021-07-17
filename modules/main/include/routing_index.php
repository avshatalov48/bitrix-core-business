<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

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

$application = \Bitrix\Main\Application::getInstance();
$application->initializeExtendedKernel(array(
	"get" => $_GET,
	"post" => $_POST,
	"files" => $_FILES,
	"cookie" => $_COOKIE,
	"server" => $_SERVER,
	"env" => $_ENV
));

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$routes = new RoutingConfigurator;
$router = new Router;
$routes->setRouter($router);
$application->setRouter($router);

// files with routes
$files = [];

// user files
$routingConfig = \Bitrix\Main\Config\Configuration::getInstance()->get('routing');
if (!empty($routingConfig['config']))
{
	$fileNames = $routingConfig['config'];

	foreach ($fileNames as $fileName)
	{
		foreach (['local', 'bitrix'] as $vendor)
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"].'/'.$vendor.'/routes/'.basename($fileName)))
			{
				$files[] = $_SERVER["DOCUMENT_ROOT"].'/'.$vendor.'/routes/'.basename($fileName);
			}
		}
	}
}

// system files
if (file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/routes/web_bitrix.php'))
{
	$files[] = $_SERVER["DOCUMENT_ROOT"].'/bitrix/routes/web_bitrix.php';
}

foreach ($files as $file)
{
	$callback = include $file;
	$callback($routes);
}

$router->releaseRoutes();

// cache for route compiled data
CompileCache::handle($files, $router);

// match request
$request = Context::getCurrent()->getRequest();
$route = $router->match($request);

if (!empty($route))
{
	$application->setCurrentRoute($route);

	// copy route parameters to the request
	if ($route->getParametersValues())
	{
		foreach ($route->getParametersValues()->getValues() as $name => $value)
		{
			$_GET[$name] = $value;
			$_REQUEST[$name] = $value;
			$request->set($name, $value);
			$request->getQueryList()->set($name, $value);
		}
	}

	$controller = $route->getController();

	if ($controller instanceof PublicPageController)
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
		$io = CBXVirtualIo::GetInstance();

		$_SERVER["REAL_FILE_PATH"] = $controller->getPath();
		Context::getCurrent()->getServer()->set('REAL_FILE_PATH', $controller->getPath());

		include_once($io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'].$controller->getPath()));
		die;
	}
	elseif ($controller instanceof \Closure)
	{
		$b = \Bitrix\Main\Engine\AutoWire\Binder::buildForFunction($controller);

		// pass current route
		$b->appendAutoWiredParameter(new Parameter(
			\Bitrix\Main\Routing\Route::class, function () use ($route) {
				return $route;
			}
		));

		// pass request
		$b->appendAutoWiredParameter(new Parameter(
			\Bitrix\Main\HttpRequest::class, function () use ($request) {
			return $request;
		}
		));

		// pass named parameters
		$b->setSourcesParametersToMap([
			$route->getParametersValues()->getValues()
		]);

		// init kernel
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

		// call
		$result = $b->invoke();

		// send response
		if ($result !== null)
		{
			if ($result instanceof \Bitrix\Main\HttpResponse)
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
				$response = new \Bitrix\Main\HttpResponse;
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
		list($controllerClass, $actionName) = $controller;
		Loader::requireClass($controllerClass);

		if (is_subclass_of($controllerClass, \Bitrix\Main\Engine\Controller::class))
		{
			if (substr($actionName, -6) == 'Action')
			{
				$actionName = substr($actionName, 0, -6);
			}

			/** @var \Bitrix\Main\HttpApplication $app */
			$app = \Bitrix\Main\Application::getInstance();
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

				/** @var \Bitrix\Main\HttpApplication $app */
				$app = \Bitrix\Main\Application::getInstance();
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
else
{
	require_once __DIR__.'/urlrewrite.php';
}