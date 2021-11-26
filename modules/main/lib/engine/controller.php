<?php

namespace Bitrix\Main\Engine;

use Bitrix\Main\Application;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Errorable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Request;
use Bitrix\Main\Response;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\PostDecodeFilter;

class Controller implements Errorable, Controllerable
{
	const SCOPE_REST = 'rest';
	const SCOPE_AJAX = 'ajax';
	const SCOPE_CLI  = 'cli';

	const EVENT_ON_BEFORE_ACTION = 'onBeforeAction';
	const EVENT_ON_AFTER_ACTION  = 'onAfterAction';

	const ERROR_REQUIRED_PARAMETER = 'MAIN_CONTROLLER_22001';
	const ERROR_UNKNOWN_ACTION     = 'MAIN_CONTROLLER_22002';

	const EXCEPTION_UNKNOWN_ACTION = 22002;

	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var  \Bitrix\Main\HttpRequest */
	protected $request;
	/** @var Configurator */
	protected $configurator;
	/** @var null|array */
	private $configurationOfActions = null;
	/** @var string */
	private $scope;
	/** @var CurrentUser */
	private $currentUser;
	/** @var Converter */
	private $converter;
	/** @var string */
	private $filePath;
	/** @var array */
	private $sourceParametersList;
	private $unsignedParameters;

	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	final public static function className()
	{
		return get_called_class();
	}

	/**
	 * Constructor Controller.
	 * @param Request|null $request
	 */
	public function __construct(Request $request = null)
	{
		$this->scope = self::SCOPE_AJAX;
		$this->errorCollection = new ErrorCollection;
		$this->request = $request?: Context::getCurrent()->getRequest();
		$this->configurator = new Configurator();
		$this->converter = Converter::toJson();

		$this->init();
	}

	/**
	 * @param Controller $controller
	 * @param string     $actionName
	 * @param array|null      $parameters
	 *
	 * @return HttpResponse|mixed
	 * @throws SystemException
	 */
	public function forward($controller, string $actionName, array $parameters = null)
	{
		if (is_string($controller))
		{
			$controller = new $controller;
		}

		// override parameters
		$controller->request = $this->getRequest();
		$controller->setScope($this->getScope());
		$controller->setCurrentUser($this->getCurrentUser());

		// run action
		$result = $controller->run(
			$actionName,
			$parameters === null ? $this->getSourceParametersList() : [$parameters]
		);

		$this->addErrors($controller->getErrors());

		return $result;
	}

	/**
	 * Initializes controller.
	 * This method is invoked at the end of constructor.
	 * @return void
	 */
	protected function init()
	{
		$this->buildConfigurationOfActions();
	}

	/**
	 * @return array|null
	 */
	final public function getConfigurationOfActions()
	{
		return $this->configurationOfActions;
	}

	/**
	 * Returns module id.
	 * Tries to guess module id by file path and function @see getModuleId().
	 *
	 * @return string
	 */
	final public function getModuleId()
	{
		return getModuleId($this->getFilePath());
	}

	final public function isLocatedUnderPsr4(): bool
	{
		// do not lower if probably psr4
		$firstLetter = mb_substr(basename($this->getFilePath()), 0, 1);

		return $firstLetter !== mb_strtolower($firstLetter);
	}

	final protected function getFilePath()
	{
		if (!$this->filePath)
		{
			$reflector = new \ReflectionClass($this);
			$this->filePath = preg_replace('#[\\\/]+#', '/', $reflector->getFileName());
		}

		return $this->filePath;
	}

	/**
	 * Returns uri for ajax end point for the action name. It's a helper,
	 * which uses relative action name without controller name.
	 *
	 * @param string $actionName Action name. It's a relative action name without controller name.
	 * @param array $params Parameters for creating uri.
	 * @param bool $absolute
	 *
	 * @return \Bitrix\Main\Web\Uri
	 */
	final public function getActionUri($actionName, array $params = array(), $absolute = false)
	{
		if (mb_strpos($this->getFilePath(), '/components/') === false)
		{
			return UrlManager::getInstance()->createByController($this, $actionName, $params, $absolute);
		}

		return UrlManager::getInstance()->createByComponentController($this, $actionName, $params, $absolute);
	}

	/**
	 * @return mixed
	 */
	final public function getUnsignedParameters()
	{
		return $this->unsignedParameters;
	}

	final protected function processUnsignedParameters()
	{
		foreach ($this->getSourceParametersList() as $source)
		{
			if (isset($source['signedParameters']) && is_string($source['signedParameters']))
			{
				try
				{
					$this->unsignedParameters = ParameterSigner::unsignParameters(
						$this->getSaltToUnsign(),
						$source['signedParameters']
					);
				}
				catch (BadSignatureException $exception)
				{}


				return;
			}
		}
	}

	/**
	 * Tries to find salt from request. It's "c" (component name) in general.
	 *
	 * @return string|null
	 */
	protected function getSaltToUnsign()
	{
		foreach ($this->getSourceParametersList() as $source)
		{
			if (isset($source['c']) && is_string($source['c']))
			{
				return $source['c'];
			}
		}

		return null;
	}

	/**
	 * @return CurrentUser
	 */
	final public function getCurrentUser()
	{
		return $this->currentUser;
	}

	/**
	 * @param CurrentUser $currentUser
	 */
	final public function setCurrentUser(CurrentUser $currentUser)
	{
		$this->currentUser = $currentUser;
	}

	/**
	 * Converts keys of array to camel case notation.
	 * @see \Bitrix\Main\Engine\Response\Converter::OUTPUT_JSON_FORMAT
	 * @param mixed $data Data.
	 *
	 * @return array|mixed|string
	 */
	public function convertKeysToCamelCase($data)
	{
		return $this->converter->process($data);
	}

	/**
	 * Returns list of all
	 * @return array
	 */
	final public function listNameActions()
	{
		$actions = array_keys($this->getConfigurationOfActions());
		$lengthSuffix = mb_strlen(self::METHOD_ACTION_SUFFIX);

		$class = new \ReflectionClass($this);
		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
		{
			$probablySuffix = mb_substr($method->getName(), -$lengthSuffix);
			if ($probablySuffix === self::METHOD_ACTION_SUFFIX)
			{
				$actions[] = mb_strtolower(mb_substr($method->getName(), 0, -$lengthSuffix));
			}
		}

		return array_unique($actions);
	}

	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [];
	}

	/**
	 * @return Parameter[]
	 */
	public function getAutoWiredParameters()
	{
		return [];
	}

	/**
	 * @return Parameter|null
	 */
	public function getPrimaryAutoWiredParameter()
	{
		return null;
	}

	/**
	 * @return Parameter[]
	 */
	final public function getDefaultAutoWiredParameters()
	{
		return [];
	}

	private function buildConfigurationOfActions()
	{
		$this->configurationOfActions = $this->configurator->getConfigurationByController($this);
	}

	/**
	 * @return \Bitrix\Main\HttpRequest
	 */
	final public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @return string
	 */
	final public function getScope()
	{
		return $this->scope;
	}

	/**
	 * @param string $scope
	 *
	 * @return Controller
	 */
	final public function setScope($scope)
	{
		$this->scope = $scope;

		return $this;
	}

	/**
	 * @return array
	 */
	final public function getSourceParametersList()
	{
		return $this->sourceParametersList;
	}

	/**
	 * @param array $sourceParametersList
	 *
	 * @return Controller
	 */
	final public function setSourceParametersList($sourceParametersList)
	{
		$this->sourceParametersList = $sourceParametersList;

		return $this;
	}

	/**
	 * @param       $actionName
	 * @param array $sourceParametersList
	 *
	 * @return HttpResponse|mixed
	 * @throws SystemException
	 */
	final public function run($actionName, array $sourceParametersList)
	{
		$this->collectDebugInfo();

		$result = null;

		try
		{
			$this->setSourceParametersList($sourceParametersList);
			$this->processUnsignedParameters();

			$action = $this->create($actionName);
			if (!$action)
			{
				throw new SystemException("Could not create action by name {$actionName}");
			}

			$this->attachFilters($action);
			if ($this->shouldDecodePostData($action))
			{
				$this->decodePostData();
			}

			if ($this->prepareParams() &&
				$this->processBeforeAction($action) === true &&
				$this->triggerOnBeforeAction($action) === true)
			{
				$result = $action->runWithSourceParametersList();

				if ($action instanceof Errorable)
				{
					$this->errorCollection->add($action->getErrors());
				}
			}

			$result = $this->triggerOnAfterAction($action, $result);
			$probablyResult = $this->processAfterAction($action, $result);
			if ($probablyResult !== null)
			{
				$result = $probablyResult;
			}
		}
		catch (\Throwable $e)
		{
			$this->runProcessingThrowable($e);
			$this->processExceptionInDebug($e);
		}

		$this->logDebugInfo();

		return $result;
	}

	protected function writeToLogException(\Throwable $e)
	{
		$exceptionHandler = Application::getInstance()->getExceptionHandler();
		$exceptionHandler->writeToLog($e);
	}

	private function processExceptionInDebug(\Throwable $e)
	{
		if (!($e instanceof BinderArgumentException))
		{
			$this->writeToLogException($e);
		}

		$exceptionHandling = Configuration::getValue('exception_handling');
		if (!empty($exceptionHandling['debug']))
		{
			$this->addError(new Error(ExceptionHandlerFormatter::format($e)));
			if ($e->getPrevious())
			{
				$this->addError(new Error(ExceptionHandlerFormatter::format($e->getPrevious())));
			}
		}
	}

	final public static function getFullEventName($eventName)
	{
		return get_called_class() . '::' . $eventName;
	}

	/**
	 * Collects debug info by Diag.
	 * @return void
	 */
	final protected function collectDebugInfo()
	{
		//Bitrix\Disk\Internals\Diag::getInstance()->collectDebugInfo(get_called_class());
	}

	/**
	 * Logs debug info by Diag.
	 * @throws \Bitrix\Main\SystemException
	 * @return void
	 */
	final protected function logDebugInfo()
	{
		//Bitrix\Disk\Internals\Diag::getInstance()->logDebugInfo(get_called_class());
	}

	/**
	 * Prepare params before process action.
	 * @return bool
	 */
	protected function prepareParams()
	{
		return true;
	}

	/**
	 * Common operations before process action.
	 * @param Action $action
	 * @return bool If method will return false, then action will not execute.
	 */
	protected function processBeforeAction(Action $action)
	{
		return true;
	}

	protected function shouldDecodePostData(Action $action): bool
	{
		return $this->request->isPost();
	}

	final protected function decodePostData(): void
	{
		\CUtil::jSPostUnescape();
		$this->request->addFilter(new PostDecodeFilter);
	}

	/**
	 * Triggers the event {{static::EVENT_ON_BEFORE_ACTION}}
	 * @see \Bitrix\Main\Engine\Controller::getFullEventName.
	 * This method is invoked right before an action is executed.
	 * In case the action should not run, event handler have to return EvenResult with type EventResult::ERROR.
	 *
	 * @param Action $action Action name.
	 * @return bool
	 */
	final protected function triggerOnBeforeAction(Action $action)
	{
		$event = new Event(
			'main',
			static::getFullEventName(static::EVENT_ON_BEFORE_ACTION),
			array(
				'action' => $action,
				'controller' => $this,
			)
		);
		$event->send($this);

		$allow = true;
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() != EventResult::SUCCESS)
			{
				$handler = $eventResult->getHandler();
				if ($handler && $handler instanceof Errorable)
				{
					$this->errorCollection->add($handler->getErrors());
				}

				$allow = false;
			}
		}

		return $allow;
	}

	/**
	 * Common operations after process action.
	 * If the method returns void or null it means that we don't want to modify $result.
	 *
	 * @param Action $action
	 * @param $result
	 *
	 * @return HttpResponse|mixed|void
	 */
	protected function processAfterAction(Action $action, $result)
	{}

	/**
	 * Finalizes response.
	 * The method will be invoked when HttpApplication will be ready to send response to client.
	 * It's a final place where Controller can interact with response.
	 *
	 * @param Response $response
	 * @return void
	 */
	public function finalizeResponse(Response $response)
	{}

	final protected function triggerOnAfterAction(Action $action, $result)
	{
		$event = new Event(
			'main',
			static::getFullEventName(static::EVENT_ON_AFTER_ACTION),
			array(
				'result' => $result,
				'action' => $action,
				'controller' => $this,
			)
		);
		$event->send($this);

		return $event->getParameter('result');
	}

	final public function generateActionMethodName($action)
	{
		return $action . self::METHOD_ACTION_SUFFIX;
	}

	protected function create($actionName)
	{
		$config = $this->getActionConfig($actionName);
		$methodName = $this->generateActionMethodName($actionName);

		if (method_exists($this, $methodName))
		{
			$method = new \ReflectionMethod($this, $methodName);
			if ($method->isPublic() && mb_strtolower($method->getName()) === mb_strtolower($methodName))
			{
				return new InlineAction($actionName, $this, $config);
			}
		}
		else
		{
			if (!$config && ($this instanceof Contract\FallbackActionInterface))
			{
				return new FallbackAction($actionName, $this, []);
			}
			if (!$config)
			{
				throw new SystemException(
					"Could not find description of {$actionName} in {$this::className()}",
					self::EXCEPTION_UNKNOWN_ACTION
				);
			}

			return $this->buildActionInstance($actionName, $config);
		}

		return null;
	}

	final protected function buildActionInstance($actionName, array $config)
	{
		if (isset($config['callable']))
		{
			$callable = $config['callable'];
			if (!is_callable($callable))
			{
				throw new ArgumentTypeException('callable', 'callable');
			}

			return new ClosureAction($actionName, $this, $callable);
		}
		elseif (empty($config['class']))
		{
			throw new SystemException(
				"Could not find class in description of {$actionName} in {$this::className()} to create instance",
				self::EXCEPTION_UNKNOWN_ACTION
			);
		}

		/** @see Action::__construct */
		$action = new $config['class']($actionName, $this, $config);

		return $action;
	}

	final protected function existsAction($actionName)
	{
		try
		{
			$action = $this->create($actionName);
		}
		catch (SystemException $e)
		{
			if ($e->getCode() !== Controller::EXCEPTION_UNKNOWN_ACTION)
			{
				throw $e;
			}
		}

		return isset($action);
	}

	/**
	 * Returns default pre-filters for action.
	 * @return array
	 */
	protected function getDefaultPreFilters()
	{
		return array(
			new ActionFilter\Authentication(),
			new ActionFilter\HttpMethod(
				array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
			),
			new ActionFilter\Csrf(),
		);
	}

	/**
	 * Returns default post-filters for action.
	 * @return array
	 */
	protected function getDefaultPostFilters()
	{
		return array();
	}

	/**
	 * Builds filter by config. If there is no config,
	 * then we use default filters @see \Bitrix\Main\Engine\Controller::getDefaultPreFilters() and
	 * @see \Bitrix\Main\Engine\Controller::getDefaultPostFilters().
	 * If now is POST query and there is no csrf check in config, then we add it.
	 *
	 * @param array|null $config
	 *
	 * @return array|null
	 */
	final protected function buildFilters(array $config = null)
	{
		if ($config === null)
		{
			$config = array();
		}

		if (!isset($config['prefilters']))
		{
			$config['prefilters'] = $this->configurator->wrapFiltersClosure(
				$this->getDefaultPreFilters()
			);
		}
		if (!isset($config['postfilters']))
		{
			$config['postfilters'] = $this->configurator->wrapFiltersClosure(
				$this->getDefaultPostFilters()
			);
		}

		$hasPostMethod = $hasCsrfCheck = false;
		foreach ($config['prefilters'] as $filter)
		{
			if ($filter instanceof ActionFilter\HttpMethod && $filter->containsPostMethod())
			{
				$hasPostMethod = true;
			}
			if ($filter instanceof ActionFilter\Csrf)
			{
				$hasCsrfCheck = true;
			}
		}

		if ($hasPostMethod && !$hasCsrfCheck && $this->request->isPost())
		{
			$config['prefilters'][] = new ActionFilter\Csrf;
		}

		if (!empty($config['-prefilters']))
		{
			$config['prefilters'] = $this->removeFilters($config['prefilters'], $config['-prefilters']);
		}

		if (!empty($config['-postfilters']))
		{
			$config['postfilters'] = $this->removeFilters($config['postfilters'], $config['-postfilters']);
		}

		if (!empty($config['+prefilters']))
		{
			$config['prefilters'] = $this->appendFilters($config['prefilters'], $config['+prefilters']);
		}

		if (!empty($config['+postfilters']))
		{
			$config['postfilters'] = $this->appendFilters($config['postfilters'], $config['+postfilters']);
		}

		return $config;
	}

	final protected function appendFilters(array $filters, array $filtersToAppend)
	{
		return array_merge($filters, $filtersToAppend);
	}

	final protected function removeFilters(array $filters, array $filtersToRemove)
	{
		$cleanedFilters = [];
		foreach ($filters as $filter)
		{
			$found = false;
			foreach ($filtersToRemove as $filterToRemove)
			{
				if (is_a($filter, $filterToRemove))
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				$cleanedFilters[] = $filter;
			}
		}

		return $cleanedFilters;
	}

	final protected function attachFilters(Action $action)
	{
		$modifiedConfig = $this->buildFilters(
			$this->getActionConfig($action->getName())
		);

		$eventManager = EventManager::getInstance();
		foreach ($modifiedConfig['prefilters'] as $filter)
		{
			/** @var $filter ActionFilter\Base */
			if (!in_array($this->getScope(), $filter->listAllowedScopes(), true))
			{
				continue;
			}

			$filter->bindAction($action);

			$eventManager->addEventHandler(
				'main',
				static::getFullEventName(static::EVENT_ON_BEFORE_ACTION),
				array($filter, 'onBeforeAction')
			);

		}

		foreach ($modifiedConfig['postfilters'] as $filter)
		{
			/** @var $filter ActionFilter\Base */
			if (!in_array($this->getScope(), $filter->listAllowedScopes(), true))
			{
				continue;
			}

			/** @var $filter ActionFilter\Base */
			$filter->bindAction($action);

			$eventManager->addEventHandler(
				'main',
				static::getFullEventName(static::EVENT_ON_AFTER_ACTION),
				array($filter, 'onAfterAction')
			);
		}
	}

	final protected function getActionConfig($actionName)
	{
		$listOfActions = array_change_key_case($this->configurationOfActions, CASE_LOWER);
		$actionName = mb_strtolower($actionName);

		if (!isset($listOfActions[$actionName]))
		{
			return null;
		}

		return $listOfActions[$actionName];
	}

	final protected function setActionConfig($actionName, array $config = null)
	{
		$this->configurationOfActions[$actionName] = $config;

		return $this;
	}

	protected function runProcessingThrowable(\Throwable $throwable)
	{
		if ($throwable instanceof BinderArgumentException)
		{
			$this->runProcessingBinderThrowable($throwable);
		}
		elseif ($throwable instanceof \Exception)
		{
			$this->runProcessingException($throwable);
		}
		elseif ($throwable instanceof \Error)
		{
			$this->runProcessingError($throwable);
		}
	}

	/**
	 * Runs processing exception.
	 * @param \Exception $e Exception.
	 * @return void
	 */
	protected function runProcessingException(\Exception $e)
	{
		//		throw $e;
		$this->errorCollection[] = $this->buildErrorFromException($e);
	}

	protected function runProcessingError(\Error $error)
	{
		//		throw $error;
		$this->errorCollection[] = $this->buildErrorFromPhpError($error);
	}

	protected function runProcessingBinderThrowable(BinderArgumentException $e): void
	{
		$currentControllerErrors = $this->getErrors();
		$errors = $e->getErrors();
		if ($errors)
		{
			foreach ($errors as $error)
			{
				if (in_array($error, $currentControllerErrors, true))
				{
					continue;
				}

				$this->addError($error);
			}
		}
		else
		{
			$this->runProcessingException($e);
		}
	}

	protected function buildErrorFromException(\Exception $e)
	{
		if ($e instanceof ArgumentNullException)
		{
			return new Error($e->getMessage(), self::ERROR_REQUIRED_PARAMETER);
		}

		return new Error($e->getMessage(), $e->getCode());
	}

	protected function buildErrorFromPhpError(\Error $error)
	{
		return new Error($error->getMessage(), $error->getCode());
	}

	/**
	 * Runs processing if user is not authorized.
	 * @return void
	 */
	protected function runProcessingIfUserNotAuthorized()
	{
		$this->errorCollection[] = new Error('User is not authorized');

		throw new SystemException('User is not authorized');
	}

	/**
	 * Runs processing if csrf token is invalid.
	 * @return void
	 */
	protected function runProcessingIfInvalidCsrfToken()
	{
		$this->errorCollection[] = new Error('Invalid csrf token');

		throw new SystemException('Invalid csrf token');
	}

	public function redirectTo($url): HttpResponse
	{
		return Context::getCurrent()->getResponse()->redirectTo($url);
	}

	/**
	 * Adds error to error collection.
	 * @param Error $error Error.
	 *
	 * @return $this
	 */
	protected function addError(Error $error)
	{
		$this->errorCollection[] = $error;

		return $this;
	}

	/**
	 * Adds list of errors to error collection.
	 * @param Error[] $errors Errors.
	 *
	 * @return $this
	 */
	protected function addErrors(array $errors)
	{
		$this->errorCollection->add($errors);

		return $this;
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	final public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	final public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}
