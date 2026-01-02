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
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Response\BFile;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Engine\Access\LoadLimiter;
use Bitrix\Rest\Engine\RestManager;
use Bitrix\Rest\Event\Session;
use Bitrix\Rest\LicenseException;
use Bitrix\Rest\Notify;
use Bitrix\Rest\OAuthService;
use Bitrix\Rest\RestException;
use Bitrix\Rest\AccessException;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\RestExceptionInterface;
use Bitrix\Rest\Tools\Diagnostics\RestServerProcessLogger;
use Bitrix\Rest\UsageStatTable;
use Bitrix\Socialservices\Bitrix24Signer;
use Bitrix\Rest\NonLoggedExceptionDecorator;

class CRestServer
{
	public const STATUS_OK = "200 OK";
	public const STATUS_CREATED = "201 Created";
	public const STATUS_WRONG_REQUEST = "400 Bad Request";
	public const STATUS_UNAUTHORIZED = "401 Unauthorized";
	public const STATUS_PAYMENT_REQUIRED = "402 Payment Required"; // reserved for future use
	public const STATUS_FORBIDDEN = "403 Forbidden";
	public const STATUS_NOT_FOUND = "404 Not Found";
	public const STATUS_TO_MANY_REQUESTS = "429 Too Many Requests";
	public const STATUS_INTERNAL = "500 Internal Server Error";

	public const TRANSPORT_JSON = 'json';
	public const TRANSPORT_XML = 'xml';
	protected const DEFAULT_REST_PROVIDER = 'CRestProvider';

	protected static ?CRestServer $instance = null;

	protected ?string $class = null;
	protected ?string $method = null;
	protected ?string $transport = null;
	protected ?string $scope = null;
	protected ?array $query = null;

	protected int $timeStart = 0;
	protected int $timeProcessStart = 0;
	protected int $timeProcessFinish = 0;
	protected mixed $usage = null;

	protected array $auth = [];
	protected array $authData = [];
	protected ?array $authScope = null;
	protected ?string $clientId = null;
	protected ?string $passwordId = null;

	protected RestExceptionInterface|null $error = null;
	protected ?string $resultStatus = null;

	protected ?array $securityMethodState = null;
	protected ?string $securityClientState = null;

	protected array $arServiceDesc = [];

	protected bool $tokenCheck = false;
	protected ?string $authType = null;

	public function __construct($params, $toLowerMethod = true)
	{
		$this->class = $params['CLASS'] ?: static::DEFAULT_REST_PROVIDER;
		$this->method = $toLowerMethod ? mb_strtolower($params['METHOD']) : $params['METHOD'];

		$this->query = $params['QUERY'] ?? null;

		$this->transport = $params['TRANSPORT'] ?? null;

		$this->securityClientState = $params['STATE'] ?? null;

		if ($this->transport === null)
		{
			$this->transport = self::TRANSPORT_JSON;
		}

		if (static::$instance === null)
		{
			static::$instance = $this;
		}
	}

	public static function instance(): ?self
	{
		return self::$instance;
	}

	public static function transportSupported(string $transport): bool
	{
		return $transport === self::TRANSPORT_XML || $transport === self::TRANSPORT_JSON;
	}

	public function process()
	{
		$this->timeStart = microtime(true);

		if (!defined('BX24_REST_SKIP_SEND_HEADERS'))
		{
			CRestUtil::sendHeaders();
		}

		try
		{
			return $this->processExecution();
		}
		catch (Exception $e)
		{
			return $this->processException($e);
		}
	}

	/**
	 * @throws RestException
	 */
	protected function isTokenCheck(): bool
	{
		$methodDescription = $this->getMethodDescriptions();
		if ($methodDescription === null)
		{
			throw new RestException('Method not found!', RestException::ERROR_METHOD_NOT_FOUND, self::STATUS_NOT_FOUND);
		}

		return in_array($this->method, [
			CRestUtil::METHOD_DOWNLOAD,
			CRestUtil::METHOD_UPLOAD,
		]) || isset($this->query['token']);
	}

	/**
	 * @return array
	 * @throws AccessException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws RestException
	 * @throws SystemException
	 */
	protected function processTokenCheckCall(): array
	{
		$token = $this->query["token"];

		[$this->scope, $queryString, $querySignature] = explode(CRestUtil::TOKEN_DELIMITER, $token);

		$signature = $this->getTokenCheckSignature($this->method, $queryString);

		if ($signature === $querySignature)
		{
			$queryString = base64_decode($queryString);

			$query = [];
			parse_str($queryString, $query);

			unset($query["_"]);

			$callback = $this->getMethodCallback();

			if (!$callback)
			{
				throw new RestException('Method not found!', RestException::ERROR_METHOD_NOT_FOUND, self::STATUS_NOT_FOUND);
			}

			$result = call_user_func_array($callback, [$query, $this->scope, $this]);

			return ["result" => $result];
		}
		else
		{
			throw new AccessException("Link check failed");
		}
	}

	/**
	 * @return mixed
	 * @throws ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws RestException
	 * @throws SystemException
	 */
	protected function processCall(): mixed
	{
		if (
			LoadLimiter::is(
				$this->getAuthType(),
				!empty($this->getClientId()) ? $this->getClientId() : $this->getPasswordId(),
				$this->method,
			)
		)
		{
			throw new RestException('Method is blocked due to operation time limit.', RestException::ERROR_OPERATION_TIME_LIMIT, self::STATUS_TO_MANY_REQUESTS);
		}

		$start = 0;
		if (isset($this->query['start']))
		{
			$start = intval($this->query['start']);
			unset($this->query['start']);
		}

		$callback = $this->getMethodCallback();

		if (!$callback)
		{
			throw new RestException('Method not found!', RestException::ERROR_METHOD_NOT_FOUND, self::STATUS_NOT_FOUND);
		}

		$this->timeProcessStart = microtime(true);

		if (ModuleManager::isModuleInstalled('bitrix24') && function_exists('getrusage'))
		{
			$this->usage = getrusage();
		}

		$entity = !empty($this->getClientId()) ? $this->getClientId() : $this->getPasswordId();
		LoadLimiter::registerStarting(
			$this->getAuthType(),
			$entity,
			$this->method,
		);

		$response = call_user_func_array($callback, [$this->query, $start, $this]);
		LoadLimiter::registerEnding(
			$this->getAuthType(),
			$entity,
			$this->method,
		);
		$this->timeProcessFinish = microtime(true);

		return $this->processResponse($response);
	}

	public function getTransport()
	{
		return $this->transport;
	}

	public function getAuth(): array
	{
		return $this->auth;
	}

	public function getAuthData(): array
	{
		return $this->authData;
	}

	public function getAuthScope(): array
	{
		if ($this->authScope === null)
		{
			$this->authScope = [];

			$authData = $this->getAuthData();

			$this->authScope = explode(',', $authData['scope']);
		}

		return $this->authScope;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function getAuthType(): ?string
	{
		return $this->authType;
	}

	/**
	 * @deprecated
	 *
	 * use \CRestServer::getClientId()
	 **/
	public function getAppId(): ?string
	{
		return $this->getClientId();
	}

	public function getClientId(): ?string
	{
		return $this->clientId;
	}

	public function getPasswordId(): ?string
	{
		return $this->passwordId;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function setStatus($status): void
	{
		$this->resultStatus = $status;
	}

	public function setSecurityState($state = null): void
	{
		$this->securityMethodState = $state;
	}

	public function getScope(): ?string
	{
		return $this->scope;
	}

	public function getScopeList(): array
	{
		return array_keys($this->arServiceDesc);
	}

	public function getServiceDescription(): array
	{
		return $this->arServiceDesc;
	}

	/**
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws SystemException
	 */
	public function getTokenCheckSignature($method, $queryString): string
	{
		if (!OAuthService::getEngine()->isRegistered())
		{
			try
			{
				OAuthService::register();
				OAuthService::getEngine()->getClient()->getApplicationList();
			}
			catch (SystemException)
			{
			}
		}

		$key = OAuthService::getEngine()->getClientSecret();

		$signatureState = mb_strtolower($method)
			. CRestUtil::TOKEN_DELIMITER . ($this->scope === CRestUtil::GLOBAL_SCOPE ? '' : $this->scope)
			. CRestUtil::TOKEN_DELIMITER . $queryString
			. CRestUtil::TOKEN_DELIMITER . implode(CRestUtil::TOKEN_DELIMITER, $this->auth);

		return $this->makeSignature($key, $signatureState);
	}

	/**
	 * @throws Main\LoaderException
	 * @throws Main\ArgumentTypeException
	 * @throws ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws SystemException
	 */
	public function getApplicationSignature(): string
	{
		$signature = '';

		$arRes = AppTable::getByClientId($this->clientId);
		if (is_array($arRes) && $arRes['SHARED_KEY'] <> '')
		{
			$methodState = is_array($this->securityMethodState)
				? $this->securityMethodState
				: ['data' => $this->securityMethodState];

			$methodState['state'] = $this->securityClientState;

			$signature = $this->makeSignature($arRes['SHARED_KEY'], $methodState);
		}

		return $signature;
	}

	/**
	 * @throws ArgumentNullException
	 * @throws RestException
	 * @throws AccessException
	 */
	public function requestConfirmation($userList, $message, $notifyEvent = 'admin_notification'): bool
	{
		if ($message == '')
		{
			throw new ArgumentNullException('message');
		}

		if (!is_array($userList) && intval($userList) > 0)
		{
			$userList = [$userList];
		}

		if (count($userList) <= 0)
		{
			throw new ArgumentNullException('userList');
		}

		if (!$this->getClientId())
		{
			throw new AccessException('Application context required');
		}

		if (
			!isset($this->authData['parameters'])
			|| !isset($this->authData['parameters']['notify_allow'])
			|| !array_key_exists($this->method, $this->authData['parameters']['notify_allow'])
		)
		{
			$notify = new Notify(Notify::NOTIFY_BOT, $userList);
			$notify->send($this->getClientId(), $this->authData['access_token'], $this->method, $message, $notifyEvent);

			$this->authData['parameters']['notify_allow'][$this->method] = 0;

			if ($this->authData['parameters_callback'] && is_callable($this->authData['parameters_callback']))
			{
				call_user_func_array($this->authData['parameters_callback'], [$this->authData]);
			}
		}

		if ($this->authData['parameters']['notify_allow'][$this->method] === 0)
		{
			throw new RestException('Waiting for confirmation', 'METHOD_CONFIRM_WAITING', static::STATUS_UNAUTHORIZED);
		}
		if ($this->authData['parameters']['notify_allow'][$this->method] < 0)
		{
			throw new RestException('Method call denied', 'METHOD_CONFIRM_DENIED', static::STATUS_FORBIDDEN);
		}

		return true;
	}

	/**
	 * @throws RestException
	 */
	protected function init(): bool
	{
		if (!in_array($this->transport, [self::TRANSPORT_JSON, self::TRANSPORT_XML]))
		{
			throw new RestException('Wrong transport!', RestException::ERROR_INTERNAL_WRONG_TRANSPORT, self::STATUS_INTERNAL);
		}
		if (!$this->checkSite())
		{
			throw new RestException('Portal was deleted', RestException::ERROR_INTERNAL_PORTAL_DELETED, self::STATUS_FORBIDDEN);
		}
		if (!class_exists($this->class) || !method_exists($this->class, 'getDescription'))
		{
			throw new RestException('Wrong handler class!', RestException::ERROR_INTERNAL_WRONG_HANDLER_CLASS, self::STATUS_INTERNAL);
		}
		else
		{
			if (array_key_exists("state", $this->query))
			{
				$this->securityClientState = $this->query["state"];
				unset($this->query["state"]);
			}
		}

		return true;
	}

	protected function checkSite(): bool
	{
		return Option::get("main", "site_stopped", "N") !== 'Y';
	}

	protected function getMethodDescriptions()
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
					if (!isset($this->arServiceDesc[$result['scope']]) || !is_array($this->arServiceDesc[$result['scope']]))
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


	protected function getMethodCallback(): callable|bool|array
	{
		$methodDescription = $this->getMethodDescriptions();
		if($methodDescription)
		{
			$callback = $methodDescription['callback'] ?? $methodDescription;

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

	protected function checkScope(): bool
	{
		if ($this->tokenCheck)
		{
			if (isset($this->query["token"]) && $this->query["token"] <> '')
			{
				[$scope] = explode(CRestUtil::TOKEN_DELIMITER, $this->query["token"], 2);
				$this->scope = $scope == "" ? CRestUtil::GLOBAL_SCOPE : $scope;
			}
		}

		$callback = $this->getMethodCallback();

		if ($callback)
		{
			return true;
		}

		return false;
	}

	/**
	 * @throws \Bitrix\Rest\OAuthException
	 * @throws Main\LoaderException
	 * @throws SystemException
	 * @throws LicenseException
	 */
	protected function checkAuth(): bool
	{
		$res = [];
		if (CRestUtil::checkAuth($this->query, $this->scope, $res))
		{
			$this->authType = $res['auth_type'];

			$this->clientId = $res['client_id'] ?? null;
			$this->passwordId = $res['password_id'] ?? null;

			$this->authData = $res;

			if (
				(isset($this->authData['auth_connector']))
				&& !$this->canUseConnectors()
			) {
				throw new LicenseException('auth_connector');
			}

			if (isset($res['parameters_clear']) && is_array($res['parameters_clear']))
			{
				foreach ($res['parameters_clear'] as $param)
				{
					if (array_key_exists($param, $this->query))
					{
						$this->auth[$param] = $this->query[$param];
						unset($this->query[$param]);
					}
				}
			}

			$arAdditionalParams = $res['parameters'] ?? null;
			if (isset($arAdditionalParams[Session::PARAM_SESSION]))
			{
				Session::set($arAdditionalParams[Session::PARAM_SESSION]);
			}

			return true;
		}
		else
		{
			throw new \Bitrix\Rest\OAuthException($res);
		}
	}

	/**
	 * @throws Main\LoaderException
	 * @throws SystemException
	 */
	protected function canUseConnectors(): bool
	{
		return !Loader::includeModule('bitrix24')
			|| Feature::isFeatureEnabled('rest_auth_connector');
	}

	/**
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws SystemException
	 */
	private function makeSignature($key, $state): string
	{
		$signature = '';

		if (Loader::includeModule('socialservices'))
		{
			$signer = new Bitrix24Signer();
			$signer->setKey($key);
			$signature = $signer->sign($state);
		}

		return $signature;
	}

	/*************************************************************/

	protected function outputError(): array
	{
		return $this->error->output();
	}

	public function sendHeaders():void
	{
		if ($this->error)
		{
			CHTTP::setStatus($this->error->getStatus());
		}
		elseif ($this->resultStatus)
		{
			CHTTP::setStatus($this->resultStatus);
		}
		else
		{
			CHTTP::setStatus(self::STATUS_OK);
		}

		switch ($this->transport)
		{
			case self::TRANSPORT_JSON:
				header('Content-Type: application/json; charset=utf-8');

				break;
			case self::TRANSPORT_XML:
				header('Content-Type: text/xml; charset=utf-8');

				break;
		}

		$this->sendHeadersAdditional();
	}

	public function sendHeadersAdditional(): void
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			if (!empty($this->clientId))
			{
				header('X-Bitrix-Rest-Application: ' . $this->clientId);
			}

			header('X-Bitrix-Rest-Time: ' . number_format($this->timeProcessFinish - $this->timeProcessStart, 10, '.', ''));

			if ($this->usage && function_exists('getrusage'))
			{
				$usage = getrusage();

				header('X-Bitrix-Rest-User-Time: ' . number_format($usage['ru_utime.tv_sec'] - $this->usage['ru_utime.tv_sec'] + ($usage['ru_utime.tv_usec'] - $this->usage['ru_utime.tv_usec']) / 1000000, 10, '.', ''));
				header('X-Bitrix-Rest-System-Time: ' . number_format($usage['ru_stime.tv_sec'] - $this->usage['ru_stime.tv_sec'] + ($usage['ru_stime.tv_usec'] - $this->usage['ru_stime.tv_usec']) / 1000000, 10, '.', ''));
			}
		}
	}

	public function output($data): BFile|string|null
	{
		UsageStatTable::finalize();

		if (isset($data['result']) && $data['result'] instanceof BFile)
		{
			return $data['result'];
		}

		return match ($this->transport)
		{
			self::TRANSPORT_JSON => $this->outputJson($data),
			self::TRANSPORT_XML => $this->outputXml(['response' => $data]),
			default => null,
		};
	}

	/**
	 * @throws Main\LoaderException
	 */
	protected function appendDebugInfo(array $data): array
	{
		$data['time'] = [
			'start' => $this->timeStart,
			'finish' => microtime(true),
		];

		$data['time']['duration'] = $data['time']['finish'] - $data['time']['start'];
		$data['time']['processing'] = $this->timeProcessFinish - $this->timeProcessStart;

		$data['time']['date_start'] = date('c', $data['time']['start']);
		$data['time']['date_finish'] = date('c', $data['time']['finish']);

		if (LoadLimiter::isActive())
		{
			$reset = LoadLimiter::getResetTime(
				$this->getAuthType(),
				!empty($this->getClientId()) ? $this->getClientId() : $this->getPasswordId(),
				$this->method,
			);
			if ($reset)
			{
				$data['time']['operating_reset_at'] = $reset;
			}

			$data['time']['operating'] = LoadLimiter::getRestTime(
				$this->getAuthType(),
				!empty($this->getClientId()) ? $this->getClientId() : $this->getPasswordId(),
				$this->method,
			);
		}

		return $data;
	}

	protected function outputJson($data): string
	{
		try
		{
			$res = Json::encode($data);
		}
		catch (ArgumentException)
		{
			$res = '{"error":"WRONG_ENCODING","error_description":"Wrong request encoding"}';
		}

		return $res;
	}

	protected function outputXml($data): string
	{
		$res = '';
		foreach ($data as $key => $value)
		{
			if ($key === intval($key))
				$key = 'item';

			$res .= '<' . $key . '>';

			if (is_array($value)) {
				$res .= $this->outputXml($value);
			}
			else
			{
				$res .= CDataXML::xmlspecialchars($value);
			}

			$res .= '</' . $key . '>';
		}

		return $res;
	}

	/**
	 * @throws RestException
	 * @throws LicenseException
	 * @throws \Bitrix\Rest\OAuthException
	 * @throws Main\LoaderException
	 * @throws AccessException
	 * @throws Exception
	 * @throws SystemException
	 */
	protected function processExecution()
	{
		global $APPLICATION;

		if ($this->init())
		{
			$handler = new $this->class();

			/* @var IRestService $handler */
			$this->arServiceDesc = $handler->getDescription();

			$this->tokenCheck = $this->isTokenCheck();

			if ($this->checkScope())
			{
				$APPLICATION->RestartBuffer();
				if ($this->checkAuth())
				{
					UsageStatTable::log($this);
					$logger = new RestServerProcessLogger($this);
					$logger->logRequest();

					if ($this->tokenCheck)
					{
						$result = $this->processTokenCheckCall();
					}
					else
					{
						$result = $this->processCall();
					}

					$logger->logResponse($result);

					return $result;
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

	protected function processException(RestExceptionInterface|Exception $e): array
	{
		global $APPLICATION;

		if ($e instanceof NonLoggedExceptionDecorator)
		{
			$e = RestException::initFromException($e->getOriginalException());
		}
		elseif (!($e instanceof RestException))
		{
			Main\Application::getInstance()->getExceptionHandler()->writeToLog($e);

			$e = RestException::initFromException($e);
		}

		$this->error = $e;

		$ex = $APPLICATION->GetException();
		if ($ex)
		{
			$this->error->setApplicationException($ex);
		}

		return $this->outputError();
	}

	/**
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws SystemException
	 */
	protected function processResponse(mixed $response): array
	{
		if (!empty($response['error']) && !empty($response['error_description']))
		{
			return $response;
		}

		$response = ['result' => $response];
		if (is_array($response['result']))
		{
			if (isset($response['result']['next']))
			{
				$response['next'] = intval($response['result']['next']);
				unset($response['result']['next']);
			}

			//Using array_key_exists instead isset for process NULL values
			if (array_key_exists('total', $response['result']))
			{
				$response['total'] = intval($response['result']['total']);
				unset($response['result']['total']);
			}
		}

		if ($this->securityClientState != null && $this->securityMethodState != null)
		{
			$response['signature'] = $this->getApplicationSignature();
		}

		return $this->appendDebugInfo($response);
	}

	public function finalize(): void
	{
		self::$instance = null;
	}
}

class CRestServerBatchItem extends CRestServer
{
	protected array $authKeys = [];

	public function setApplicationId($appId): void
	{
		$this->clientId = $appId;
	}

	public function setAuthKeys($keys): void
	{
		$this->authKeys = $keys;
	}

	public function setAuthData($authData): void
	{
		$this->authData = $authData;
	}

	public function setAuthType($authType): void
	{
		$this->authType = $authType;
	}

	/**
	 * @throws \Bitrix\Rest\OAuthException
	 */
	protected function checkAuth(): bool
	{
		foreach ($this->authKeys as $param)
		{
			if (array_key_exists($param, $this->query))
			{
				$this->auth[$param] = $this->query[$param];
				unset($this->query[$param]);
			}
		}

		if ($this->scope !== CRestUtil::GLOBAL_SCOPE)
		{
			$allowedScope = explode(',', $this->authData['scope']);
			$allowedScope = RestManager::fillAlternativeScope($this->scope, $allowedScope);
			if (!in_array($this->scope, $allowedScope))
			{
				throw new \Bitrix\Rest\OAuthException(['error' => 'insufficient_scope']);
			}
		}

		return true;
	}
}

class IRestService
{
	public const LIST_LIMIT = 50;

	protected static function getNavData($start, $bORM = false)
	{
		if ($start >= 0)
		{
			return $bORM
				? [
					'limit' => static::LIST_LIMIT,
					'offset' => intval($start),
				]
				: [
					'nPageSize' => static::LIST_LIMIT,
					'iNumPage' => intval($start / static::LIST_LIMIT) + 1,
				];
		}
		else
		{
			return $bORM
				? [
					'limit' => static::LIST_LIMIT,
				]
				: [
					'nTopCount' => static::LIST_LIMIT,
				];
		}
	}

	protected static function setNavData($result, $dbRes)
	{
		if (is_array($dbRes))
		{
			// backward compatibility moment...
			if ($result instanceof Countable || is_array($result))
			{
				$count = count($result);
			}
			elseif (is_null($result))
			{
				$count = 0;
			}
			else
			{
				$count = 1;
			}

			if ($dbRes["offset"] + $count < $dbRes["count"])
			{
				$result['next'] = $dbRes["offset"] + $count;
			}
			if (!is_scalar($result))
			{
				$result['total'] = $dbRes["count"];
			}
		}
		else
		{
			$result['total'] = $dbRes->NavRecordCount;
			if ($dbRes->NavPageNomer < $dbRes->NavPageCount)
			{
				$result['next'] = $dbRes->NavPageNomer * $dbRes->NavPageSize;
			}
		}

		return $result;
	}

	public function getDescription()
	{
		$arMethods = get_class_methods($this);

		$arResult = [];

		foreach ($arMethods as $name)
		{
			if ($name != 'getDescription')
			{
				$arResult[$name] = [$this, $name];
			}
		}

		return $arResult;
	}

	/**
	 * @throws RestException
	 */
	protected static function sanitizeFilter($filter, array $availableFields = null, $valueCallback = null, array $availableOperations = null)
	{
		static $defaultOperations = ['', '=', '>', '<', '>=', '<=', '@', '%'];

		if ($availableOperations === null)
		{
			$availableOperations = $defaultOperations;
		}

		if (!is_array($filter))
		{
			throw new RestException('The filter is not an array.', RestException::ERROR_ARGUMENT, CRestServer::STATUS_WRONG_REQUEST);
		}

		$filter = array_change_key_case($filter, CASE_UPPER);

		$resultFilter = [];
		foreach ($filter as $key => $value)
		{
			if (preg_match('/^([^a-zA-Z]*)(.*)/', $key, $matches))
			{
				$operation = $matches[1];
				$field = $matches[2];

				if (!in_array($operation, $availableOperations))
				{
					throw new RestException('Filter operation not allowed: ' . $operation, RestException::ERROR_ARGUMENT, CRestServer::STATUS_WRONG_REQUEST);
				}

				if ($availableFields !== null && !in_array($field, $availableFields))
				{
					throw new RestException('Filter field not allowed: ' . $field, RestException::ERROR_ARGUMENT, CRestServer::STATUS_WRONG_REQUEST);
				}

				if (is_callable($valueCallback))
				{
					$value = call_user_func_array($valueCallback, [$field, $value, $operation]);
				}

				$resultFilter[$operation . $field] = $value;
			}
		}

		return $resultFilter;
	}

	/**
	 * @throws RestException
	 */
	protected static function sanitizeOrder($order, array $availableFields = null)
	{
		if (!is_array($order))
		{
			throw new RestException('The order is not an array.', RestException::ERROR_ARGUMENT, CRestServer::STATUS_WRONG_REQUEST);
		}

		$order = array_change_key_case($order, CASE_UPPER);

		foreach ($order as $key => $value)
		{
			if (!is_numeric($key))
			{
				if ($availableFields !== null && !in_array($key, $availableFields))
				{
					throw new RestException('Order field not allowed: ' . $key, RestException::ERROR_ARGUMENT, CRestServer::STATUS_WRONG_REQUEST);
				}

				if (!in_array(mb_strtoupper($value), ['ASC', 'DESC']))
				{
					throw new RestException('Order direction should be one of {ASC|DESC}', RestException::ERROR_ARGUMENT, CRestServer::STATUS_WRONG_REQUEST);
				}
			}
			elseif ($availableFields !== null && !in_array($value, $availableFields))
			{
				throw new RestException('Order field not allowed: ' . $value, RestException::ERROR_ARGUMENT, CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $order;
	}
}
