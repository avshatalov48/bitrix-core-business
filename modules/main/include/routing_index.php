<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2020 Bitrix
 */

use Bitrix\Main\Context;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Contract\RoutableAction;
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

// files with routes
$files = [];

// user files
$routingConfig = \Bitrix\Main\Config\Configuration::getInstance()->get('routing');
if (!empty($routingConfig['config']))
{
	$fileNames = $routingConfig['config'];

	foreach ($fileNames as $fileName)
	{
		$files[] = $_SERVER["DOCUMENT_ROOT"].'/bitrix/routes/'.basename($fileName);
	}
}

// system files
$files[] = $_SERVER["DOCUMENT_ROOT"].'/bitrix/routes/web_bitrix.php';

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
		$controller($request);
		die;
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

//admin section 404
if(strpos($request->getRequestUri(), "/bitrix/admin/") === 0)
{
	$_SERVER["REAL_FILE_PATH"] = "/bitrix/admin/404.php";
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/404.php");
	die();
}

define("BX_CHECK_SHORT_URI", true);