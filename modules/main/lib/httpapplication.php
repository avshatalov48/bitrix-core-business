<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\AutoWire;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Engine\Router;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\UI\PageNavigation;

/**
 * Http application extends application. Contains http specific methods.
 */
class HttpApplication extends Application
{
	/**
	 * Creates new instance of http application.
	 */
	protected function __construct()
	{
		parent::__construct();
	}

	/**
	 * Initializes context of the current request.
	 *
	 * @param array $params Request parameters
	 */
	protected function initializeContext(array $params)
	{
		$context = new HttpContext($this);

		$server = new Server($params["server"]);

		$request = new HttpRequest(
			$server,
			$params["get"],
			$params["post"],
			$params["files"],
			$params["cookie"]
		);

		$response = new HttpResponse();

		$context->initialize($request, $response, $server, array('env' => $params["env"]));

		$this->setContext($context);
	}

	public function createExceptionHandlerOutput()
	{
		return new Diag\HttpExceptionHandlerOutput();
	}

	/**
	 * Starts request execution. Should be called after initialize.
	 */
	public function start()
	{
	}

	/**
	 * Finishes request execution.
	 * It is registered in start() and called automatically on script shutdown.
	 */
	public function finish()
	{
	}

	private function getSourceParametersList()
	{
		if (!$this->context->getServer()->get('HTTP_BX_AJAX_QB'))
		{
			return array(
				$this->context->getRequest()->getPostList(),
				$this->context->getRequest()->getQueryList(),
			);
		}

		return array(
			Web\Json::decode($this->context->getRequest()->getPost('bx_data'))
		);
	}

	/**
	 * Runs controller and its action and sends response to the output.
	 *
	 * @return void
	 */
	public function run()
	{
		$e = null;
		$result = null;
		$errorCollection = new ErrorCollection();

		try
		{
			$router = new Router($this->context->getRequest());

			/** @var Controller $controller */
			/** @var string $actionName */
			list($controller, $actionName) = $router->getControllerAndAction();
			if (!$controller)
			{
				throw new SystemException('Could not find controller for the request');
			}

			$this->registerAutoWirings();

			$result = $controller->run($actionName, $this->getSourceParametersList());
			$errorCollection->add($controller->getErrors());
		}
		catch (\Throwable $e)
		{
			$this->handleRunError($e, $errorCollection);
		}
		finally
		{
			$controller = isset($controller) ? $controller : null;
			$this->runFinally($controller, $result, $errorCollection);
		}
	}

	public function runController($controller, $action)
	{
		$controllerClass = $controller;
		$errorCollection = new ErrorCollection();

		try
		{
			/** @var \Bitrix\Main\Engine\Controller $controllerObject */
			$controllerObject = new $controllerClass;
			$controllerObject->setScope(\Bitrix\Main\Engine\Controller::SCOPE_AJAX);
			$controllerObject->setCurrentUser(CurrentUser::get());

			$this->registerAutoWirings();

			$result = $controllerObject->run($action, [[], []]);
			$errorCollection->add($controllerObject->getErrors());
		}
		catch (\Throwable $e)
		{
			$this->handleRunError($e, $errorCollection);
		}
		finally
		{
			$this->runFinally($controllerObject, $result, $errorCollection);
		}
	}

	/**
	 * @param Controller        $controller
	 * @param HttpResponse|null $result
	 * @param ErrorCollection   $errorCollection
	 */
	private function runFinally($controller, $result, ErrorCollection $errorCollection)
	{
		$response = $this->buildResponse($result, $errorCollection);
		$response = $this->context->getResponse()->copyHeadersTo($response);

		if (!empty($controller))
		{
			$controller->finalizeResponse($response);
		}

		$this->context->setResponse($response);

		$response->send();

		//todo exit code in Response?
		$this->terminate(0);
	}

	/**
	 * @param \Throwable $e
	 * @param ErrorCollection $errorCollection
	 *
	 * @return array
	 */
	private function handleRunError($e, $errorCollection)
	{
		$exceptionHandler = $this->getExceptionHandler();
		$exceptionHandler->writeToLog($e);

		$errorCollection[] = new Error($e->getMessage(), $e->getCode());
		$exceptionHandling = Configuration::getValue('exception_handling');
		if (!empty($exceptionHandling['debug']))
		{
			$errorCollection[] = new Error(Diag\ExceptionHandlerFormatter::format($e));
			if ($e->getPrevious())
			{
				$errorCollection[] = new Error(Diag\ExceptionHandlerFormatter::format($e->getPrevious()));
			}
		}
	}

	private function registerAutoWirings()
	{
		AutoWire\Binder::registerGlobalAutoWiredParameter(new AutoWire\Parameter(
			PageNavigation::class,
			static function() {
				$pageNavigation = new PageNavigation('nav');
				$pageNavigation
					->setPageSizes(range(1, 50))
					->initFromUri()
				;

				return $pageNavigation;
			}
		));

		AutoWire\Binder::registerGlobalAutoWiredParameter(new AutoWire\Parameter(
			JsonPayload::class,
			static function() {
				return new JsonPayload();
			}
		));

		AutoWire\Binder::registerGlobalAutoWiredParameter(new AutoWire\Parameter(
			CurrentUser::class,
			static function() {
				return CurrentUser::get();
			}
		));
	}

	/**
	 * Builds a response by result's action.
	 * If an action returns non subclass of HttpResponse then the method tries to create Response\StandardJson.
	 *
	 * @param mixed $actionResult
	 * @param ErrorCollection $errorCollection
	 *
	 * @return HttpResponse
	 */
	private function buildResponse($actionResult, ErrorCollection $errorCollection)
	{
		if ($actionResult instanceof HttpResponse)
		{
			return $actionResult;
		}

		if (!$errorCollection->isEmpty())
		{
			//todo There is opportunity to create DenyError() and recognize AjaxJson::STATUS_DENIED by this error.

			return new AjaxJson(
				$actionResult,
				AjaxJson::STATUS_ERROR,
				$errorCollection
			);
		}

		return new AjaxJson($actionResult);
	}
}
