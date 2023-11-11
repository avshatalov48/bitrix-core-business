<?php

namespace Bitrix\Vote\Base;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Error;
use \Bitrix\Main\Application;
use \Bitrix\Main\Context;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;
use Bitrix\Tasks\AccessDeniedException;

Loc::loadMessages(__FILE__);

abstract class Controller
{
	const EVENT_ON_BEFORE_ACTION = 'onBeforeAction';

	const ERROR_REQUIRED_PARAMETER = 'VOTE_CONTROLLER_22001';
	const ERROR_UNKNOWN_ACTION     = 'VOTE_CONTROLLER_22002';

	const STATUS_SUCCESS      = 'success';
	const STATUS_DENIED       = 'denied';
	const STATUS_ERROR        = 'error';
	const STATUS_NEED_AUTH    = 'need_auth';

	/** @var string */
	protected $action;
	/** @var  array */
	protected $actionDescription;
	/** @var  string */
	protected $realActionName;
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var  \Bitrix\Main\HttpRequest */
	protected $request;

	private $collectDebugInfo = 1;

	/**
	 * Constructor Controller.
	 */
	public function __construct()
	{
		try
		{
			$this->errorCollection = new ErrorCollection;
			$this->request = Context::getCurrent()->getRequest();

			$this->init();
		}
		catch(\Exception $e)
		{
			$this->errorCollection->add(array(new Error($e->getMessage())));
		}

		if (!$this->errorCollection->isEmpty())
		{
			$this->sendJsonErrorResponse();
		}
	}

	/**
	 * Initializes controller.
	 * This method is invoked at the end of constructor.
	 * @return void
	 */
	protected function init()
	{}

	/**
	 * Terminates controller and application.
	 * This method replaces "die()" or "exit()" and ensures life cycle of application.
	 * @return void
	 */
	protected function end()
	{
		$this->logDebugInfo();

		/** @noinspection PhpUndefinedClassInspection */
		\CMain::finalActions();
		die;
	}

	/**
	 * Executes controller by specific action.
	 * This method contains all steps of life cycle controller.
	 * @return void
	 */
	public function exec()
	{
		try
		{
			$this->collectDebugInfo();
			$this->resolveAction();
			$this->checkAction();
			if ($this->prepareParams() && $this->errorCollection->isEmpty() && $this->processBeforeAction($this->getAction()) === true)
			{
				$this->runAction();
			}
			$this->logDebugInfo();
		}
		catch(\Exception $e)
		{
			$this->errorCollection->add(array(new Error($e->getMessage())));
		}

		if (!$this->errorCollection->isEmpty())
		{
			$this->sendJsonErrorResponse();
		}
	}

	/**
	 * Collects debug info by Diag.
	 * @return void
	 */
	protected function collectDebugInfo()
	{
		if($this->collectDebugInfo)
		{
			Diag::getInstance()->collectDebugInfo(get_called_class());
		}
	}

	/**
	 * Logs debug info by Diag.
	 * @throws \Bitrix\Main\SystemException
	 * @return void
	 */
	protected function logDebugInfo()
	{
		if($this->collectDebugInfo)
		{
			Diag::getInstance()->logDebugInfo(get_called_class(), get_called_class() . ':' . $this->getAction());
		}
	}

	/**
	 * Gets current user.
	 * @return array|bool|\CUser
	 */
	protected function getUser()
	{
		global $USER;

		return $USER;
	}

	/**
	 * Sends JSON response and terminates controller.
	 * @param mixed $response
	 * @param null|array $params
	 * @return void
	 */
	protected function sendJsonResponse($response, $params = null)
	{
		if(!defined('PUBLIC_AJAX_MODE'))
		{
			define('PUBLIC_AJAX_MODE', true);
		}

		global $APPLICATION;
		$APPLICATION->restartBuffer();

		if(!empty($params['http_status']) && $params['http_status'] == 403)
		{
			header('HTTP/1.0 403 Forbidden', true, 403);
		}
		if(!empty($params['http_status']) && $params['http_status'] == 500)
		{
			header('HTTP/1.0 500 Internal Server Error', true, 500);
		}
		if(!empty($params['http_status']) && $params['http_status'] == 510)
		{
			header('HTTP/1.0 510 Not Extended', true, 510);
		}

		header('Content-Type:application/json; charset=UTF-8');
		echo Json::encode($response);

		$this->end();
	}

	/**
	 * Sends JSON response with status "error" and with errors and terminates controller.
	 * @return void
	 */
	protected function sendJsonErrorResponse()
	{
		$errors = array();
		foreach($this->getErrors() as $error)
		{
			/** @var Error $error */
			$errors[] = array(
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
			);
		}
		unset($error);
		$this->sendJsonResponse(array(
			'status' => self::STATUS_ERROR,
			'errors' => $errors,
		));
	}

	/**
	 * Sends JSON response with status "denied" and terminates controller.
	 * @param string $message Message.
	 * @return void
	 */
	protected function sendJsonAccessDeniedResponse($message = '')
	{
		$this->sendJsonResponse(array(
			'status' => self::STATUS_DENIED,
			'message' => $message,
		));
	}

	/**
	 * Sends JSON response with status "success" and mixed data, and terminates controller.
	 * @param array $response Data to response.
	 * @return void
	 */
	protected function sendJsonSuccessResponse(array $response = array())
	{
		$response['status'] = self::STATUS_SUCCESS;
		$this->sendJsonResponse($response);
	}

	/**
	 * Sends response and terminates controller.
	 * Automatically restart buffer.
	 * @param mixed $response Mixed data to response.
	 * @return void
	 */
	protected function sendResponse($response)
	{
		$this->logDebugInfo();

		global $APPLICATION;
		$APPLICATION->restartBuffer();

		echo $response;

		$this->end();
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}


	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * Resolves action and description of action, which need to run.
	 * @see listActions().
	 * @return $this
	 */
	protected function resolveAction()
	{
		$listOfActions = array_change_key_case($this->listActions(), CASE_LOWER);
		$action = mb_strtolower($this->action);

		if(!isset($listOfActions[$action]))
			throw new NotSupportedException(Loc::getMessage(
				'VOTE_CONTROLLER_ERROR_UNKNOWN_ACTION',
				array('#ACTION#' => $this->sanitizeActionName($action))
			));

		$this->realActionName = $action;
		$description = $this->normalizeActionDescription($action, $listOfActions[$this->realActionName]);

		$this->setAction($description['name'], $description);

		return $this;
	}

	private function sanitizeActionName($actionName)
	{
		if(!preg_match('/^[a-zA-Z0-9]+$/i', $actionName))
		{
			return 'unknown';
		}

		return $actionName;
	}

	/**
	 * Normalizes action description.
	 *
	 * Default description:
	 * array(
	 *			'method' => array('GET'), //allowed methods to run action.
	 *			'name' => $action, //action which will run
	 *			'need_auth' => true,
	 *			'redirect_on_auth' => true,
	 *	)
	 *
	 * @param string $action Action name.
	 * @param array|string $description Action description.
	 * @return array
	 */
	protected function normalizeActionDescription($action, $description)
	{
		$description = array_merge(
			array(
				'method' => array('GET'),
				'name' => (is_string($description) && $description <> '' ? $description : $action),
				'need_auth' => true,
				'check_sessid' => true,
				'redirect_on_auth' => true
			),
			(is_array($description) ? $description : array())
		);
		$description["method"] = array_intersect(is_array($description["method"]) ? $description["method"] : array($description["method"]), array("GET", "POST"));
		return $description;
	}

	/**
	 * Checks action by settings in description.
	 * This method may terminate controller and application.
	 * @return boolean
	 */
	protected function checkAction()
	{
		$description = $this->getActionDescription();

		if ($description["need_auth"] && (!$this->getUser() || !$this->getUser()->getId()))
		{
			if ($description["redirect_on_auth"])
			{
				LocalRedirect(SITE_DIR . 'auth/?backurl=' . urlencode(Application::getInstance()->getContext()->getRequest()->getRequestUri()));
			}
			else
			{
				throw new AccessDeniedException();
			}
		}

		if (!in_array($this->request->getRequestMethod(), $description['method']))
			throw new ArgumentException("Request method is not supported by ".$this->getAction()." operation.");

		if ($description['check_sessid'] && !check_bitrix_sessid())
			throw new ArgumentException("Bad sessid.");

		return true;
	}

	/**
	 * Lists all actions by controller. This listing may contains description in short-style.
	 *
	 * If you set
	 * array(
	 *  'showFoo' => array(
	 *		'method' => array('GET', 'POST'), //allowed GET and POST methods to run action.
	 *		'name' => 'showFoo', //execute method processActionShowFoo
	 *		'need_auth' => true
	 *  )
	 * )
	 *
	 * @return array
	 */
	protected function listActions()
	{
		return array();
	}

	/**
	 * Gets current action.
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Gets description of action.
	 * @return array
	 */
	public function getActionDescription()
	{
		return $this->actionDescription;
	}

	/**
	 * Sets action and description.
	 * @param string $action Action name.
	 * @param array  $description Action description.
	 * @return $this
	 */
	public function setAction($action, array $description)
	{
		$this->action = $action;
		$this->actionDescription = $description;
		return $this;
	}

	/**
	 * Sets action name.
	 * @param string $action Action name.
	 * @return $this
	 */
	public function setActionName($action)
	{
		$this->action = $action;
		return $this;
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
	 * @param string $actionName Action name which will be run.
	 * @return bool If method will return false, then action will not execute.
	 */
	protected function processBeforeAction($actionName)
	{
		return true;
	}

	/**
	 * Runs action.
	 * Will be run method with name processAction{$actionName}
	 * @return mixed
	 */
	protected function runAction()
	{
		if (! method_exists($this, 'processAction' . $this->getAction()))
			throw new InvalidOperationException('processAction' . $this->getAction());
		return call_user_func(array($this, 'processAction' . $this->getAction()));
	}


	/**
	 * Get application instance.
	 * @return Application|\Bitrix\Main\HttpApplication|\CMain
	 */
	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	/**
	 * Checks required parameters.
	 * Fills error collection if required parameter is missed.
	 * @param array $inputParams Input data.
	 * @param array $required Required parameters.
	 * @return bool
	 */
	protected function checkRequiredInputParams(array $inputParams, array $required)
	{
		foreach ($required as $item)
		{
			if(!isset($inputParams[$item]) || (!$inputParams[$item] && !(is_string($inputParams[$item]) && mb_strlen($inputParams[$item]))))
			{
				$this->errorCollection->add(array(new Error(Loc::getMessage('VOTE_CONTROLLER_ERROR_REQUIRED_PARAMETER', array('#PARAM#' => $item)), self::ERROR_REQUIRED_PARAMETER)));
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks required parameters in $_POST.
	 * Fills error collection if required parameter is missed.
	 * @param array $required Required parameters.
	 * @return bool
	 */
	protected function checkRequiredPostParams(array $required)
	{
		$params = array();
		foreach($required as $item)
		{
			$params[$item] = $this->request->getPost($item);
		}
		unset($item);

		return $this->checkRequiredInputParams($params, $required);
	}

	/**
	 * Checks required parameters in $_GET.
	 * Fills error collection if required parameter is missed.
	 * @param array $required Required parameters.
	 * @return bool
	 */
	protected function checkRequiredGetParams(array $required)
	{
		$params = array();
		foreach($required as $item)
		{
			$params[$item] = $this->request->getQuery($item);
		}
		unset($item);

		return $this->checkRequiredInputParams($params, $required);
	}

	/**
	 * Checks required parameters in $_FILES.
	 * Fills error collection if required parameter is missed.
	 * @param array $required Required parameters.
	 * @return bool
	 */
	protected function checkRequiredFilesParams(array $required)
	{
		$params = array();
		foreach($required as $item)
		{
			$params[$item] = $this->request->getFile($item);
		}
		unset($item);

		return $this->checkRequiredInputParams($params, $required);
	}

	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request.
	 * @return boolean
	 */
	protected function isAjaxRequest()
	{
		return $this->request->isAjaxRequest();
	}
}