<?php
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 */

use Bitrix\Bitrix24\Feature;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Rest\RestException;
use Bitrix\Rest\AccessException;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Text\Encoding;
use Bitrix\Socialservices\Bitrix24Signer;

class CRestServer
{
	const STATUS_OK = "200 OK";
	const STATUS_CREATED = "201 Created";
	const STATUS_WRONG_REQUEST = "400 Bad Request";
	const STATUS_UNAUTHORIZED = "401 Unauthorized";
	const STATUS_PAYMENT_REQUIRED = "402 Payment Required"; // reserved for future use
	const STATUS_FORBIDDEN = "403 Forbidden";
	const STATUS_NOT_FOUND = "404 Not Found";
	const STATUS_INTERNAL = "500  Internal Server Error";

	/* @var \CRestServer */
	protected static $instance = null;

	protected $class = '';
	protected $method = '';
	protected $transport = '';
	protected $scope = '';
	protected $query = array();

	protected $timeStart = 0;
	protected $timeProcessStart = 0;
	protected $timeProcessFinish = 0;
	protected $usage = null;

	protected $auth = array();
	protected $authData = array();
	protected $authScope = null;
	protected $clientId = '';
	protected $passwordId = '';

	/* @var RestException */
	protected $error = '';
	protected $resultStatus = null;

	protected $securityMethodState = null;
	protected $securityClientState = null;

	protected $arServiceDesc = array();

	protected $tokenCheck = false;
	protected $authType = null;

	public function __construct($params)
	{
		$this->class = $params['CLASS'];
		$this->method = ToLower($params['METHOD']);
		$this->query = $params['QUERY'];

		$this->transport = $params['TRANSPORT'];

		$this->securityClientState = $params['STATE'];

		if(!$this->transport)
		{
			$this->transport = 'json';
		}

		if(self::$instance === null)
		{
			self::$instance = $this;
		}
	}

	/**
	 * @return \CRestServer|null
	 */
	public static function instance()
	{
		return self::$instance;
	}

	public static function transportSupported($transport)
	{
		return $transport == 'xml' || $transport == 'json';
	}

	public function process()
	{
		global $APPLICATION;

		$this->timeStart = microtime(true);

		if(!defined('BX24_REST_SKIP_SEND_HEADERS'))
		{
			\CRestUtil::sendHeaders();
		}

		try
		{
			if($this->init())
			{
				$handler = new $this->class();
				/* @var IRestService $handler */
				$this->arServiceDesc = $handler->getDescription();

				$this->tokenCheck = $this->isTokenCheck();

				if($this->checkScope())
				{
					$APPLICATION->RestartBuffer();

					if($this->checkAuth())
					{
						\Bitrix\Rest\StatTable::log($this);


						if($this->tokenCheck)
						{
							return $this->processTokenCheckCall();
						}
						else
						{
							return $this->processCall();
						}
					}
					else
					{
						throw new AccessException();
					}
				}
				else
				{
					throw new RestException('Method not found!', RestException::ERROR_METHOD_NOT_FOUND, self::STATUS_NOT_FOUND);
				}
			}
		}
		catch(Exception $e)
		{
			$this->error = $e;

			if(!is_a($this->error, "\\Bitrix\\Rest\\RestException"))
			{
				$this->error = RestException::initFromException($this->error);
			}

			$ex = $APPLICATION->GetException();
			if($ex)
			{
				$this->error->setApplicationException($ex);
			}
		}

		if($this->error)
		{
			return $this->outputError();
		}

		return null;
	}

	protected function isTokenCheck()
	{
		$methodDescription = $this->getMethodDescription();
		if(!$methodDescription)
		{
			throw new RestException('Method not found!', RestException::ERROR_METHOD_NOT_FOUND, self::STATUS_NOT_FOUND);
		}

		return in_array($this->method, array(
			\CRestUtil::METHOD_DOWNLOAD,
			\CRestUtil::METHOD_UPLOAD,
		)) || isset($this->query['token']);
	}

	protected function processTokenCheckCall()
	{
		$token = $this->query["token"];

		list($this->scope, $queryString, $querySignature) = explode(\CRestUtil::TOKEN_DELIMITER, $token);

		$signature = $this->getTokenCheckSignature($this->method, $queryString);

		if($signature === $querySignature)
		{
			$queryString = base64_decode($queryString);

			$query = array();
			parse_str($queryString, $query);

			unset($query["_"]);

			$callback = $this->getMethodCallback();

			if(!$callback)
			{
				throw new RestException('Method not found!', RestException::ERROR_METHOD_NOT_FOUND, self::STATUS_NOT_FOUND);
			}

			$result = call_user_func_array($callback, array($query, $this->scope, $this));

			return array("result" => $result);
		}
		else
		{
			throw new AccessException("Link check failed");
		}
	}

	protected function processCall()
	{
		$start = 0;
		if(isset($this->query['start']))
		{
			$start = intval($this->query['start']);
			unset($this->query['start']);
		}

		$callback = $this->getMethodCallback();

		if(!$callback)
		{
			throw new RestException('Method not found!', RestException::ERROR_METHOD_NOT_FOUND, self::STATUS_NOT_FOUND);
		}

		$this->timeProcessStart = microtime(true);

		if(\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') && function_exists('getrusage'))
		{
			$this->usage = getrusage();
		}

		$result = call_user_func_array($callback, array($this->query, $start, $this));

		$this->timeProcessFinish = microtime(true);

		$result = array("result" => $result);
		if(is_array($result['result']))
		{
			if(isset($result['result']['next']))
			{
				$result["next"] = intval($result['result']['next']);
				unset($result['result']['next']);
			}

			//Using array_key_exists instead isset for process NULL values
			if(array_key_exists('total', $result['result']))
			{
				$result['total'] = intval($result['result']['total']);
				unset($result['result']['total']);
			}
		}

		if($this->securityClientState != null && $this->securityMethodState != null)
		{
			$result['signature'] = $this->getApplicationSignature();
		}

		$result = $this->appendDebugInfo($result);

		return $result;
	}

	public function getTransport()
	{
		return $this->transport;
	}

	public function getAuth()
	{
		return $this->auth;
	}

	public function getAuthData()
	{
		return $this->authData;
	}

	public function getAuthScope()
	{
		if($this->authScope == null)
		{
			$this->authScope = array();

			$authData = $this->getAuthData();
			$scopeList = explode(',', $authData['scope']);
			$serviceDescription = $this->getServiceDescription();

			$this->authScope = array();
			foreach($scopeList as $scope)
			{
				if(array_key_exists($scope, $serviceDescription))
				{
					$this->authScope[] = $scope;
				}
			}
		}

		return $this->authScope;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function getAuthType()
	{
		return $this->authType;
	}

	/**
	 * @deprecated
	 *
	 * use \CRestServer::getClientId()
	 **/
	public function getAppId()
	{
		return $this->getClientId();
	}

	public function getClientId()
	{
		return $this->clientId;
	}

	public function getPasswordId()
	{
		return $this->passwordId;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function setStatus($status)
	{
		$this->resultStatus = $status;
	}

	public function setSecurityState($state = null)
	{
		$this->securityMethodState = $state;
	}

	public function getScope()
	{
		return $this->scope;
	}

	public function getScopeList()
	{
		return array_keys($this->arServiceDesc);
	}

	public function getServiceDescription()
	{
		return $this->arServiceDesc;
	}

	public function getTokenCheckSignature($method, $queryString)
	{
		if(!\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
		{
			try
			{
				\Bitrix\Rest\OAuthService::register();
			}
			catch(\Bitrix\Main\SystemException $e)
			{
			}
		}

		$key = \Bitrix\Rest\OAuthService::getEngine()->getClientSecret();

		$signatureState = ToLower($method)
			.\CRestUtil::TOKEN_DELIMITER.($this->scope === \CRestUtil::GLOBAL_SCOPE ? '' : $this->scope)
			.\CRestUtil::TOKEN_DELIMITER.$queryString
			.\CRestUtil::TOKEN_DELIMITER.implode(\CRestUtil::TOKEN_DELIMITER, $this->auth);

		return $this->makeSignature($key, $signatureState);
	}

	public function getApplicationSignature()
	{
		$signature = '';

		$arRes = \Bitrix\Rest\AppTable::getByClientId($this->clientId);
		if(is_array($arRes) && strlen($arRes['SHARED_KEY']) > 0)
		{
			$methodState = is_array($this->securityMethodState)
				? $this->securityMethodState
				: array('data' => $this->securityMethodState);

			$methodState['state'] = $this->securityClientState;

			$signature = $this->makeSignature($arRes['SHARED_KEY'], $methodState);
		}

		return $signature;
	}


	public function requestConfirmation($userList, $message)
	{
		if(strlen($message) <= 0)
		{
			throw new ArgumentNullException('message');
		}

		if(!is_array($userList) && intval($userList) > 0)
		{
			$userList = array($userList);
		}

		if(count($userList) <= 0)
		{
			throw new ArgumentNullException('userList');
		}

		if(!$this->getClientId())
		{
			throw new AccessException('Application context required');
		}

		if(
			!isset($this->authData['parameters'])
			|| !isset($this->authData['parameters']['notify_allow'])
			|| !array_key_exists($this->method, $this->authData['parameters']['notify_allow'])
		)
		{
			$notify = new \Bitrix\Rest\Notify(\Bitrix\Rest\Notify::NOTIFY_BOT, $userList);
			$notify->send($this->getClientId(), $this->authData['access_token'], $this->method, $message);

			$this->authData['parameters']['notify_allow'][$this->method] = 0;

			if($this->authData['parameters_callback'] && is_callable($this->authData['parameters_callback']))
			{
				call_user_func_array($this->authData['parameters_callback'], array($this->authData));
			}
		}

		if($this->authData['parameters']['notify_allow'][$this->method] === 0)
		{
			throw new RestException('Waiting for confirmation', 'METHOD_CONFIRM_WAITING', static::STATUS_UNAUTHORIZED);
		}
		elseif($this->authData['parameters']['notify_allow'][$this->method] < 0)
		{
			throw new RestException('Method call denied', 'METHOD_CONFIRM_DENIED', static::STATUS_FORBIDDEN);
		}

		return true;
	}

	private function init()
	{
		if(!in_array($this->transport, array('json', 'xml')))
		{
			throw new RestException('Wrong transport!', RestException::ERROR_INTERNAL_WRONG_TRANSPORT, self::STATUS_INTERNAL);
		}
		elseif(!$this->checkSite())
		{
			throw new RestException('Portal was deleted', RestException::ERROR_INTERNAL_PORTAL_DELETED, self::STATUS_FORBIDDEN);
		}
		elseif(!class_exists($this->class) || !method_exists($this->class, 'getDescription'))
		{
			throw new RestException('Wrong handler class!', RestException::ERROR_INTERNAL_WRONG_HANDLER_CLASS, self::STATUS_INTERNAL);
		}
		else
		{
			if(array_key_exists("state", $this->query))
			{
				$this->securityClientState = $this->query["state"];
				unset($this->query["state"]);
			}
		}

		return true;
	}

	private function checkSite()
	{
		return \Bitrix\Main\Config\Option::get("main", "site_stopped", "N") !== 'Y';
	}

	private function getMethodDescription()
	{
		if(!$this->scope)
		{
			foreach($this->arServiceDesc as $scope => $arMethods)
			{
				if(array_key_exists($this->method, $arMethods))
				{
					$this->scope = $scope;
					break;
				}
			}
		}

		if(!isset($this->arServiceDesc[$this->scope]) || !isset($this->arServiceDesc[$this->scope][$this->method]))
		{
			foreach(GetModuleEvents('rest', 'onFindMethodDescription', true) as $event)
			{
				$result = ExecuteModuleEventEx($event, array($this->method, $this->scope));
				if(is_array($result))
				{
					if(!is_array($this->arServiceDesc[$result['scope']]))
					{
						$this->arServiceDesc[$result['scope']] = array();
					}

					$this->scope = $result['scope'];

					$this->arServiceDesc[$this->scope][$this->method] = $result;

					return $result;
				}
			}
		}

		return $this->arServiceDesc[$this->scope][$this->method];
	}

	private function getMethodCallback()
	{
		$methodDescription = $this->getMethodDescription();

		if($methodDescription)
		{
			$callback = isset($methodDescription['callback'])
				? $methodDescription['callback']
				: $methodDescription;

			// hack to prevent callback structure doubling in case of strange doubling of event handlers
			if(!is_callable($callback) && is_array($callback) && count($callback) > 2)
			{
				$callback = array($callback[0], $callback[1]);
			}

			if(is_callable($callback))
			{
				return $callback;
			}
		}

		return false;
	}

	private function checkScope()
	{
		if($this->tokenCheck)
		{
			if(isset($this->query["token"]) && strlen($this->query["token"]) > 0)
			{
				list($scope) = explode(\CRestUtil::TOKEN_DELIMITER, $this->query["token"], 2);
				$this->scope = $scope == "" ? \CRestUtil::GLOBAL_SCOPE : $scope;
			}
		}

		$callback = $this->getMethodCallback();

		if($callback)
		{
			return true;
		}

		return false;
	}

	protected function checkAuth()
	{
		$res = array();
		if(\CRestUtil::checkAuth($this->query, $this->scope, $res))
		{
			$this->authType = $res['auth_type'];

			$this->clientId = isset($res['client_id']) ? $res['client_id'] : null;
			$this->passwordId = isset($res['password_id']) ? $res['password_id'] : null;

			$this->authData  = $res;

			if(
				$this->authData['auth_connector']
				&& !$this->canUseConnectors()
			)
			{
				throw new \Bitrix\Rest\LicenseException('auth_connector');
			}

			if(isset($res['parameters_clear']) && is_array($res['parameters_clear']))
			{
				foreach($res['parameters_clear'] as $param)
				{
					if(array_key_exists($param, $this->query))
					{
						$this->auth[$param] = $this->query[$param];
						unset($this->query[$param]);
					}
				}
			}

			$arAdditionalParams = $res['parameters'];
			if(isset($arAdditionalParams[\Bitrix\Rest\Event\Session::PARAM_SESSION]))
			{
				\Bitrix\Rest\Event\Session::set($arAdditionalParams[\Bitrix\Rest\Event\Session::PARAM_SESSION]);
			}

			return true;
		}
		else
		{
			throw new \Bitrix\Rest\OAuthException($res);
		}
	}

	protected function canUseConnectors()
	{
		return !Loader::includeModule('bitrix24')
			|| Feature::isFeatureEnabled('rest_auth_connector');
	}

	protected function getMethodOptions()
	{
		$methodDescription = $this->getMethodDescription();
		return is_array($methodDescription['options'])
			? $methodDescription['options']
			: array();
	}

	private function makeSignature($key, $state)
	{
		$signature = '';

		if(Loader::includeModule('socialservices'))
		{
			$signer = new Bitrix24Signer();
			$signer->setKey($key);
			$signature = $signer->sign($state);
		}

		return $signature;
	}

	/*************************************************************/

	private function outputError()
	{
		$res = array_merge(array(
			'error' => $this->error->getErrorCode(),
			'error_description' => $this->error->getMessage(),
		), $this->error->getAdditional());

		return $res;
	}

	public function sendHeaders()
	{
		if($this->error)
		{
			\CHTTP::setStatus($this->error->getStatus());
		}
		elseif($this->resultStatus)
		{
			\CHTTP::setStatus($this->resultStatus);
		}

		switch($this->transport)
		{
			case 'json':
				Header('Content-Type: application/json; charset=utf-8');
			break;
			case 'xml':
				Header('Content-Type: text/xml; charset=utf-8');
			break;
		}

		if(\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			if($this->clientId)
			{
				Header('X-Bitrix-Rest-Application: '.$this->clientId);
			}

			Header('X-Bitrix-Rest-Time: '.number_format($this->timeProcessFinish - $this->timeProcessStart, 10, '.', ''));

			if(function_exists('getrusage'))
			{
				$usage = getrusage();

				Header('X-Bitrix-Rest-User-Time: '.number_format($usage['ru_utime.tv_sec'] - $this->usage['ru_utime.tv_sec'] + ($usage['ru_utime.tv_usec'] - $this->usage['ru_utime.tv_usec']) / 1000000, 10, '.', ''));
				Header('X-Bitrix-Rest-System-Time: '.number_format($usage['ru_stime.tv_sec'] - $this->usage['ru_stime.tv_sec'] + ($usage['ru_stime.tv_usec'] - $this->usage['ru_stime.tv_usec']) / 1000000, 10, '.', ''));
			}
		}
	}

	public function output($data)
	{
		\Bitrix\Rest\LogTable::log($this, $data);
		\Bitrix\Rest\StatTable::finalize();

		switch($this->transport)
		{
			case 'json':
				return $this->outputJson($data);
			break;
			case 'xml':
				$data = Encoding::convertEncoding($data, LANG_CHARSET, 'utf-8');
				return $this->outputXml(array('response' => $data));
			break;
		}
		return null;
	}

	protected function appendDebugInfo(array $data)
	{
		$data['time'] = array(
			'start' => $this->timeStart,
			'finish' => microtime(true),
		);

		$data['time']['duration'] = $data['time']['finish'] - $data['time']['start'];
		$data['time']['processing'] = $this->timeProcessFinish - $this->timeProcessStart;

		$data['time']['date_start'] = date('c', $data['time']['start']);
		$data['time']['date_finish'] = date('c', $data['time']['finish']);

		return $data;
	}

	private function outputJson($data)
	{
		try
		{
			$res = Json::encode($data);
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			$res = '{"error":"WRONG_ENCODING","error_description":"Wrong request encoding"}';
		}

		return $res;
	}

	private function outputXml($data)
	{
		$res = "";
		foreach($data as $key => $value)
		{
			if($key === intval($key))
				$key = 'item';

			$res .= '<'.$key.'>';

			if(is_array($value))
				$res .= $this->outputXml($value);
			else
				$res .= \CDataXML::xmlspecialchars($value);

			$res .= '</'.$key.'>';
		}
		return $res;
	}
}

class CRestServerBatchItem extends \CRestServer
{
	protected $authKeys = array();

	public function setApplicationId($appId)
	{
		$this->clientId = $appId;
	}

	public function setAuthKeys($keys)
	{
		$this->authKeys = $keys;
	}

	public function setAuthData($authData)
	{
		$this->authData = $authData;
	}

	public function setAuthType($authType)
	{
		$this->authType = $authType;
	}

	protected function checkAuth()
	{
		foreach($this->authKeys as $param)
		{
			if(array_key_exists($param, $this->query))
			{
				$this->auth[$param] = $this->query[$param];
				unset($this->query[$param]);
			}
		}

		if($this->scope !== \CRestUtil::GLOBAL_SCOPE)
		{
			$allowedScope = explode(',', $this->authData['scope']);
			if(!in_array($this->scope, $allowedScope))
			{
				throw new \Bitrix\Rest\OAuthException(array('error' => 'insufficient_scope'));
			}
		}

		return true;
	}
}

class IRestService
{
	const LIST_LIMIT = 50;

	protected static function getNavData($start, $bORM = false)
	{
		if($start >= 0)
		{
			return ($bORM
				? array(
					'limit' => static::LIST_LIMIT,
					'offset' => intval($start)
				)
				: array(
					'nPageSize' => static::LIST_LIMIT,
					'iNumPage' => intval($start / static::LIST_LIMIT) + 1
				)
			);
		}
		else
		{
			return ($bORM
				? array(
					'limit' => static::LIST_LIMIT,
				)
				: array(
					'nTopCount' => static::LIST_LIMIT,
				)
			);
		}
	}

	protected static function setNavData($result, $dbRes)
	{
		if (is_array($dbRes))
		{
			if($dbRes["offset"] + count($result) < $dbRes["count"])
			{
				$result['next'] = $dbRes["offset"] + count($result);
			}
			$result['total'] = $dbRes["count"];
		}
		else
		{
			$result['total'] = $dbRes->NavRecordCount;
			if($dbRes->NavPageNomer < $dbRes->NavPageCount)
			{
				$result['next'] = $dbRes->NavPageNomer * $dbRes->NavPageSize;
			}
		}

		return $result;
	}

	public function getDescription()
	{
		$arMethods = get_class_methods($this);

		$arResult = array();

		foreach ($arMethods as $name)
		{
			if($name != 'getDescription')
			{
				$arResult[$name] = array($this, $name);
			}
		}

		return $arResult;
	}

	protected static function sanitizeFilter($filter, array $availableFields = null, $valueCallback = null, array $availableOperations = null)
	{
		static $defaultOperations = array('', '=', '>', '<', '>=', '<=', '@', '%');

		if($availableOperations === null)
		{
			$availableOperations = $defaultOperations;
		}

		if(!is_array($filter))
		{
			throw new RestException('The filter is not an array.', RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$filter = array_change_key_case($filter, CASE_UPPER);

		$resultFilter = array();
		foreach($filter as $key => $value)
		{
			if(preg_match('/^([^a-zA-Z]*)(.*)/', $key, $matches))
			{
				$operation = $matches[1];
				$field = $matches[2];

				if(!in_array($operation, $availableOperations))
				{
					throw new RestException('Filter operation not allowed: '.$operation, RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
				}

				if($availableFields !== null && !in_array($field, $availableFields))
				{
					throw new RestException('Filter field not allowed: '.$field, RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
				}

				if(is_callable($valueCallback))
				{
					$value = call_user_func_array($valueCallback, array($field, $value, $operation));
				}

				$resultFilter[$operation.$field] = $value;
			}
		}

		return $resultFilter;
	}

	protected static function sanitizeOrder($order, array $availableFields = null)
	{
		if(!is_array($order))
		{
			throw new RestException('The order is not an array.', RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$order = array_change_key_case($order, CASE_UPPER);

		foreach($order as $key => $value)
		{
			if(!is_numeric($key))
			{
				if($availableFields !== null && !in_array($key, $availableFields))
				{
					throw new RestException('Order field not allowed: '.$key, RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
				}

				if(!in_array(ToUpper($value), array('ASC', 'DESC')))
				{
					throw new RestException('Order direction should be one of {ASC|DESC}', RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
				}
			}
			elseif($availableFields !== null && !in_array($value, $availableFields))
			{
				throw new RestException('Order field not allowed: '.$value, RestException::ERROR_ARGUMENT, \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $order;
	}

}
