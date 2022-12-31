<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\AutoWire;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ControllerBuilder;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Engine\Router;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web;

/**
 * Http application extends application. Contains http specific methods.
 */
class HttpApplication extends Application
{
	public const EXCEPTION_UNKNOWN_CONTROLLER = 221001;

	/**
	 * Initializes context of the current request.
	 *
	 * @param array $params Request parameters
	 */
	protected function initializeContext(array $params)
	{
		$context = new HttpContext($this);

		$server = new Server($params['server']);

		$request = new HttpRequest(
			$server,
			$params['get'],
			$params['post'],
			$params['files'],
			$params['cookie']
		);

		$response = new HttpResponse();

		$context->initialize($request, $response, $server, ['env' => $params['env']]);

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
		$this->context->getRequest()->decodeJson();
	}

	/**
	 * Finishes request execution.
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
		try
		{
			$router = new Router($this->context->getRequest());

			/** @var Controller $controller */
			/** @var string $actionName */
			[$controller, $actionName] = $router->getControllerAndAction();
			if (!$controller)
			{
				throw new SystemException('Could not find controller for the request', self::EXCEPTION_UNKNOWN_CONTROLLER);
			}

			$this->runController($controller, $actionName);
		}
		catch (\Throwable $e)
		{
			$errorCollection = new ErrorCollection();

			$this->processRunError($e, $errorCollection);
			$this->finalizeControllerResult($controller ?? null, null, $errorCollection);
		}
	}

	/**
	 * @param Controller|string $controller
	 * @param string $action
	 * @return void
	 */
	final public function runController($controller, $action): void
	{
		$result = null;
		$errorCollection = new ErrorCollection();

		try
		{
			if (is_string($controller))
			{
				$controller = ControllerBuilder::build($controller, [
					'scope' => Controller::SCOPE_AJAX,
					'currentUser' => CurrentUser::get(),
				]);
			}

			$this->registerAutoWirings();

			$result = $controller->run($action, $this->getSourceParametersList());
			$errorCollection->add($controller->getErrors());
		}
		catch (\Throwable $e)
		{
			$this->processRunError($e, $errorCollection);
		}
		finally
		{
			$this->finalizeControllerResult($controller, $result, $errorCollection);
		}
	}

	/**
	 * @param Controller|null   $controller
	 * @param HttpResponse|null $result
	 * @param ErrorCollection   $errorCollection
	 */
	private function finalizeControllerResult($controller, $result, ErrorCollection $errorCollection): void
	{
		$response = $this->buildResponse($result, $errorCollection);
		$response = $this->context->getResponse()->copyHeadersTo($response);

		if ($controller)
		{
			$controller->finalizeResponse($response);
		}

		$this->context->setResponse($response);

		//todo exit code in Response?
		$this->end(0, $response);
	}

	private function shouldWriteToLogException(\Throwable $e): bool
	{
		if ($e instanceof AutoWire\BinderArgumentException)
		{
			return false;
		}

		$unnecessaryCodes = [
			self::EXCEPTION_UNKNOWN_CONTROLLER,
			Router::EXCEPTION_INVALID_COMPONENT_INTERFACE,
			Router::EXCEPTION_INVALID_COMPONENT,
			Router::EXCEPTION_INVALID_AJAX_MODE,
			Router::EXCEPTION_NO_MODULE,
			Router::EXCEPTION_INVALID_MODULE_NAME,
			Router::EXCEPTION_INVALID_COMPONENT_NAME,
			Router::EXCEPTION_NO_COMPONENT,
			Router::EXCEPTION_NO_COMPONENT_AJAX_CLASS,
		];

		if ($e instanceof SystemException && in_array($e->getCode(), $unnecessaryCodes, true))
		{
			return false;
		}

		return true;
	}

	private function processRunError(\Throwable $e, ErrorCollection $errorCollection): void
	{
		$errorCollection[] = new Error($e->getMessage(), $e->getCode());
		$exceptionHandling = Configuration::getValue('exception_handling');
		$debugMode = !empty($exceptionHandling['debug']);
		if ($debugMode)
		{
			$errorCollection[] = new Error(Diag\ExceptionHandlerFormatter::format($e));
			if ($e->getPrevious())
			{
				$errorCollection[] = new Error(Diag\ExceptionHandlerFormatter::format($e->getPrevious()));
			}
		}

		if ($debugMode || $this->shouldWriteToLogException($e))
		{
			$this->getExceptionHandler()->writeToLog($e);
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
