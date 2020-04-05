<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\Binder;
use Bitrix\Main\Engine\Controller;
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
	 */
	public function run()
	{
		try
		{
			$e = null;
			$result = null;
			$errorCollection = new ErrorCollection();

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
		catch (\Exception $e)
		{
			$errorCollection[] = new Error($e->getMessage(), $e->getCode());
		}
		catch (\Error $e)
		{
			//todo add work with debug mode to show extend errors and exceptions
			$errorCollection[] = new Error($e->getMessage(), $e->getCode());
		}
		finally
		{
			$exceptionHandling = Configuration::getValue('exception_handling');
			if ($e && !empty($exceptionHandling['debug']))
			{
				$errorCollection[] = new Error(Diag\ExceptionHandlerFormatter::format($e));
				if ($e->getPrevious())
				{
					$errorCollection[] = new Error(Diag\ExceptionHandlerFormatter::format($e->getPrevious()));
				}
			}

			if ($e instanceof \Exception || $e instanceof \Error)
			{
				$exceptionHandler = $this->getExceptionHandler();
				$exceptionHandler->writeToLog($e);
			}

			$response = $this->buildResponse($result, $errorCollection);
			$this->clonePreviousHeadersAndCookies($this->context->getResponse(), $response);
			if (isset($controller))
			{
				$controller->finalizeResponse($response);
			}

			$this->context->setResponse($response);

			global $APPLICATION;
			$APPLICATION->restartBuffer();

			$response->send();

			//todo exit code in Response?
			$this->terminate(0);
		}
	}

	private function registerAutoWirings()
	{
		/** @see \Bitrix\Main\UI\PageNavigation */
		Binder::registerParameter(
			'\\Bitrix\\Main\\UI\\PageNavigation',
			function() {
				$pageNavigation = new PageNavigation('nav');
				$pageNavigation
					->setPageSizes(range(1, 50))
					->initFromUri()
				;

				return $pageNavigation;
			}
		);
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

	private function clonePreviousHeadersAndCookies(HttpResponse $previousResponse, HttpResponse $response)
	{
		$httpHeaders = $response->getHeaders();

		$status = $response->getStatus();
		$previousStatus = $previousResponse->getStatus();
		foreach ($previousResponse->getHeaders() as $headerName => $values)
		{
			if ($this->shouldIgnoreHeaderToClone($headerName))
			{
				continue;
			}

			if ($status && $headerName === $previousStatus)
			{
				continue;
			}

			if ($httpHeaders->get($headerName))
			{
				continue;
			}

			$httpHeaders->add($headerName, $values);
		}

		foreach ($previousResponse->getCookies() as $cookie)
		{
			$response->addCookie($cookie, false);
		}

		return $response;
	}

	private function shouldIgnoreHeaderToClone($headerName)
	{
		return in_array(strtolower($headerName), [
			'content-encoding',
			'content-length',
			'content-type',
		]);
	}
}
