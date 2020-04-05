<?php

namespace Bitrix\Main\Engine;


use Bitrix\Main\Engine\Contract\Controllerable;
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
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\PostDecodeFilter;

Loc::loadMessages(__FILE__);

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
	/** @var string */
	private $filePath;
	/** @var array */
	private $sourceParametersList;

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
	 * @param Request $request
	 */
	public function __construct(Request $request = null)
	{
		$this->scope = self::SCOPE_AJAX;
		$this->errorCollection = new ErrorCollection;
		$this->request = $request?: Context::getCurrent()->getRequest();
		$this->configurator = new Configurator();

		$this->init();
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

	final protected function getFilePath()
	{
		if (!$this->filePath)
		{
			$reflector = new \ReflectionClass($this);
			$this->filePath = $reflector->getFileName();
		}

		return $this->filePath;
	}

	/**
	 * Returns uri for ajax end point for the action name. It's a helper,
	 * which uses relative action name without controller name.
	 *
	 * @param string $actionName Action name. It's a relative action name without controller name.
	 * @param array $params Parameters for creating uri.
	 *
	 * @return \Bitrix\Main\Web\Uri
	 */
	final public function getActionUri($actionName, array $params = array())
	{
		if (strpos($this->getFilePath(), '/components/') === false)
		{
			return UrlManager::getInstance()->createByController($this, $actionName, $params);
		}

		return UrlManager::getInstance()->createByComponentController($this, $actionName, $params);
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
	 * Returns list of all
	 * @return array
	 */
	final public function listNameActions()
	{
		$actions = array_keys($this->getConfigurationOfActions());
		$lengthSuffix = strlen(self::METHOD_ACTION_SUFFIX);

		$class = new \ReflectionClass($this);
		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
		{
			$probablySuffix = substr($method->getName(), -$lengthSuffix);
			if ($probablySuffix === self::METHOD_ACTION_SUFFIX)
			{
				$actions[] = strtolower(substr($method->getName(), 0, -$lengthSuffix));
			}
		}

		return array_unique($actions);
	}

	public function configureActions()
	{
		return array();
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
			$this->sourceParametersList = $sourceParametersList;

			$action = $this->create($actionName);
			if (!$action)
			{
				throw new SystemException("Could not create action by name {$actionName}");
			}

			$this->attachFilters($action);

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
		catch (\Exception $e)
		{
			$this->runProcessingException($e);
		}
		catch (\Error $e)
		{
			$this->runProcessingError($e);
		}

		$this->logDebugInfo();

		return $result;
	}

	final public function getFullEventName($eventName)
	{
		return $this::className() . '::' . $eventName;
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
		if ($this->request->isPost())
		{
			\CUtil::jSPostUnescape();
			$this->request->addFilter(new PostDecodeFilter);
		}

		return true;
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
			$this->getFullEventName(static::EVENT_ON_BEFORE_ACTION),
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

	final protected function triggerOnAfterAction(Action $action, $result)
	{
		$event = new Event(
			'main',
			$this->getFullEventName(static::EVENT_ON_AFTER_ACTION),
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
			if ($method->isPublic() && strtolower($method->getName()) === strtolower($methodName))
			{
				return new InlineAction($actionName, $this, $config);
			}
		}
		else
		{
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
			new ActionFilter\Authentication,
			new ActionFilter\HttpMethod(
				array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
			),
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

		return $config;
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
				$this->getFullEventName(static::EVENT_ON_BEFORE_ACTION),
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
				$this->getFullEventName(static::EVENT_ON_AFTER_ACTION),
				array($filter, 'onAfterAction')
			);
		}
	}

	final protected function getActionConfig($actionName)
	{
		$listOfActions = array_change_key_case($this->configurationOfActions, CASE_LOWER);
		$actionName = strtolower($actionName);

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

	protected function buildErrorFromException(\Exception $e)
	{
		if ($e instanceof ArgumentNullException)
		{
			return new Error($e->getMessage(), self::ERROR_REQUIRED_PARAMETER);
		}

		return new Error($e->getMessage());
	}

	protected function buildErrorFromPhpError(\Error $error)
	{
		return new Error($error->getMessage());
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