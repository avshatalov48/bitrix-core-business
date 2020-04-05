<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Engine\Binder;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Engine\Router;
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

		$response = new HttpResponse($context);

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
		//register_shutdown_function(array($this, "finish"));
	}

	/**
	 * Finishes request execution.
	 * It is registered in start() and called automatically on script shutdown.
	 */
	public function finish()
	{
		//$this->managedCache->finalize();
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
	 * @throws SystemException
	 */
	public function run()
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
		$response = $this->buildResponse($result, $controller->getErrors());
		$this->context->setResponse($response);

		global $APPLICATION;
		$APPLICATION->restartBuffer();

		$response->send();

		//todo exit code in Response?
		$this->terminate(0);
	}

	private function registerAutoWirings()
	{
		/** @see \Bitrix\Main\UI\PageNavigation */
		Binder::registerParameter(
			'\\Bitrix\\Main\\UI\\PageNavigation',
			function() {
				$pageNavigation = new PageNavigation('nav');
				$pageNavigation->initFromUri();

				return $pageNavigation;
			}
		);
	}

	/**
	 * Builds a response by result's action.
	 * If an action returns non subclass of HttpResponse then the method tries to create Response\StandardJson.
	 *
	 * @param mixed $actionResult
	 * @param Error[] $errors
	 * @return HttpResponse
	 */
	private function buildResponse($actionResult, $errors)
	{
		if ($actionResult instanceof HttpResponse)
		{
			return $actionResult;
		}

		if ($errors)
		{
			$errorCollection = new ErrorCollection($errors);
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
