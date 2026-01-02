<?php

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\ControllerBuilder;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Rest\Engine\Access\LoadLimiter;
use Bitrix\Rest\Engine\RestManager;
use Bitrix\Rest\Event\Session;
use Bitrix\Rest\RestExceptionInterface;
use Bitrix\Rest\Tools\Diagnostics\RestServerProcessLogger;
use Bitrix\Rest\UsageStatTable;
use Bitrix\Rest\V3\Controllers\RestController;
use Bitrix\Rest\V3\Exceptions\AccessDeniedException;
use Bitrix\Rest\V3\Exceptions\Internal\InternalException;
use Bitrix\Rest\V3\Exceptions\InvalidSelectException;
use Bitrix\Rest\V3\Exceptions\LicenseException;
use Bitrix\Rest\V3\Exceptions\MethodNotFoundException;
use Bitrix\Rest\V3\Exceptions\RateLimitException;
use Bitrix\Rest\V3\Exceptions\RestException;
use Bitrix\Rest\V3\Interaction\Request\BatchRequest;
use Bitrix\Rest\V3\Interaction\Request\ServerRequest;
use Bitrix\Rest\V3\Interaction\Response\BatchResponse;
use Bitrix\Rest\V3\Interaction\Response\ErrorResponse;
use Bitrix\Rest\V3\Interaction\Response\Response;
use Bitrix\Rest\V3\Interaction\Response\ResponseWithRelations;
use Bitrix\Rest\V3\Schema\MethodDescription;
use Bitrix\Rest\V3\Schema\SchemaManager;

class CRestApiServer extends CRestServer
{
	protected ?string $localErrorLanguage = null;
	/**
	 * @var MethodDescription[]
	 */
	protected ?array $methodDescriptions = null;

	/**
	 * @var string[]
	 */
	private array $availableScopes = [CRestUtil::GLOBAL_SCOPE];
	private ?array $requestAccess = null;

	protected SchemaManager $schemaManager;

	/**
	 * @param $params
	 * @throws AccessDeniedException
	 */
	public function __construct($params)
	{
		$this->transport = self::TRANSPORT_JSON;
		$this->localErrorLanguage = $params['LOCAL_ERROR_LANGUAGE'] ?? null;
		$this->schemaManager = ServiceLocator::getInstance()->get(SchemaManager::class);

		if (!$this->checkSite())
		{
			throw new AccessDeniedException(status: self::STATUS_WRONG_REQUEST);
		}

		parent::__construct($params);

		$routes = $this->schemaManager->getRouteAliases();
		$this->method = $routes[$this->method] ?? $this->method;
	}

	/**
	 * @return MethodDescription[]
	 */
	protected function getMethodDescriptions(): array
	{
		if ($this->methodDescriptions === null)
		{
			$this->methodDescriptions = $this->schemaManager->getMethodDescriptions();
		}
		return $this->methodDescriptions;
	}

	protected function getMethodDescription(string $method): ?MethodDescription
	{
		return $this->schemaManager->getMethodDescription($method);
	}

	public function processServerRequest(ServerRequest $request)
	{
		$this->timeStart = microtime(true);

		if (!defined('BX24_REST_SKIP_SEND_HEADERS'))
		{
			CRestUtil::sendHeaders();
		}

		try
		{
			return $this->processServerExecution($request);
		}
		catch (Throwable $e)
		{
			return $this->processException($e);
		}
	}

	private function processServerExecution(ServerRequest $request)
	{
		$this->initServerExecution($request);

		$methodDescription = $this->getMethodDescription($request->getMethod());
		if ($methodDescription === null || !Loader::includeModule($methodDescription->getModule()))
		{
			throw new MethodNotFoundException($request->getMethod());
		}

		$this->initRequestScope($request);

		$this->checkServerAuth($request);

		UsageStatTable::log($this);
		$logger = new RestServerProcessLogger($this);
		$logger->logRequest();

		$result = $this->processServerRequestCall($request);

		$logger->logResponse($result);

		return $result;
	}

	protected function checkServerAuth(ServerRequest $request): bool
	{
		$res = $this->getRequestAccess($request->getQuery());

		$this->authType = $res['auth_type'];
		$this->clientId = $res['client_id'] ?? null;
		$this->passwordId = $res['password_id'] ?? null;
		$this->authData = $res;

		if (isset($this->authData['auth_connector']) && !$this->canUseConnectors())
		{
			throw new LicenseException(status: self::STATUS_FORBIDDEN);
		}

		if (isset($res['parameters_clear']))
		{
			$query = $request->getQuery();
			foreach ((array)$res['parameters_clear'] as $param)
			{
				if (array_key_exists($param, $query))
				{
					$this->auth[$param] = $query[$param];
					unset($query[$param]);
				}
			}
			$request->setQuery($query);
		}

		if (isset($res['parameters'][Session::PARAM_SESSION]))
		{
			Session::set($res['parameters'][Session::PARAM_SESSION]);
		}

		return true;
	}

	protected function initRequestScope(ServerRequest $request): void
	{
		if ($request->getToken() !== null)
		{
			[$scope] = explode(CRestUtil::TOKEN_DELIMITER, $request->getToken(), 2);
			$request->setScope($scope ?: CRestUtil::GLOBAL_SCOPE);
		}
		else
		{
			$methodDescription = $this->getMethodDescription($request->getMethod());
			$request->setScope($methodDescription?->getScope());
		}
	}

	protected function initServerExecution(ServerRequest $request): void
	{
		if (array_key_exists('state', $request->getQuery()))
		{
			$this->securityClientState = $request->getQuery()['state'];
			$query = $request->getQuery();
			unset($query['state']);
			$request->setQuery($query);
		}
	}

	/**
	 * @param ServerRequest $request
	 * @return mixed
	 * @throws AccessDeniedException
	 * @throws InternalException
	 * @throws LoaderException
	 * @throws MethodNotFoundException
	 * @throws ObjectException
	 * @throws RateLimitException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected function processServerRequestCall(ServerRequest $request): mixed
	{
		$entityId = $this->getClientId() ?: $this->getPasswordId();

		if (LoadLimiter::is($this->getAuthType(), $entityId, $request->getMethod()))
		{
			throw new RateLimitException();
		}

		$this->timeProcessStart = microtime(true);

		if (ModuleManager::isModuleInstalled('bitrix24') && function_exists('getrusage'))
		{
			$this->usage = getrusage();
		}

		LoadLimiter::registerStarting(
			$this->getAuthType(),
			$entityId,
			$request->getMethod(),
		);

		$currentUser = CurrentUser::get();

		$response = $this->processRequest($request, $currentUser);

		LoadLimiter::registerEnding(
			$this->getAuthType(),
			$entityId,
			$request->getMethod(),
		);

		$this->timeProcessFinish = microtime(true);

		return $this->processResponse($response);
	}

	/**
	 * @param ServerRequest $request
	 * @param CurrentUser $currentUser
	 * @return Response
	 * @throws AccessDeniedException
	 * @throws InternalException
	 * @throws MethodNotFoundException
	 * @throws ObjectException
	 * @throws SystemException
	 */
	protected function processRequest(ServerRequest $request, CurrentUser $currentUser): Response
	{
		if ($request->getMethod() === 'batch')
		{
			return $this->processBatchServerExecution($request, $currentUser);
		}
		else
		{
			return $this->processServerRequestExecution($request, $currentUser);
		}
	}

	/**
	 * @param ServerRequest $request
	 * @param CurrentUser $currentUser
	 * @return Response
	 * @throws AccessDeniedException
	 * @throws InternalException
	 * @throws InvalidSelectException
	 * @throws MethodNotFoundException
	 * @throws ObjectException
	 * @throws SystemException
	 */
	protected function processBatchServerExecution(ServerRequest $request, CurrentUser $currentUser): Response
	{
		$jsonData = $request->getHttpRequest()->getJsonList()->toArray();
		$batchRequest = new BatchRequest($jsonData);
		$batchResponse = new BatchResponse();
		foreach ($batchRequest->getItems() as $index => $item)
		{
			$context = $batchResponse->getContext();
			$httpJsonData = $this->prepareJsonData($context, $item->getQuery());
			$itemHttpRequest = new \Bitrix\Main\HttpRequest(\Bitrix\Main\Context::getCurrent()->getServer(), [], [], [], [], $httpJsonData);
			$itemServerRequest = new ServerRequest($item->getMethod(), $request->getQuery(), $itemHttpRequest);

			$response = $this->processServerRequestExecution($itemServerRequest, $currentUser);
			if ($response instanceof ErrorResponse)
			{
				return $response;
			}
			$item->setResponse($response);
			$batchResponse->addItem($item->getAlias() ?? $index, $response);
		}
		return $batchResponse;
	}

	protected function prepareJsonData(array $context, array $queryParams): array
	{
		$getValueByPath = function ($path, $context)
		{
			$current = $context;
			$pathParts = explode('.', $path);

			foreach ($pathParts as $key)
			{
				if (!is_array($current) && !($current instanceof ArrayAccess))
				{
					throw new InvalidSelectException("Invalid context path '{$path}' - expected array at '{$key}'");
				}
				if (!isset($current[$key]))
				{
					throw new InvalidSelectException("Path '{$path}' not found in context");
				}
				$current = $current[$key];
			}

			return $current;
		};

		$replaceRef = function ($value) use ($context, $getValueByPath, &$replaceRef)
		{
			if (is_array($value))
			{
				if (isset($value['$ref']))
				{
					return $getValueByPath($value['$ref'], $context);
				}

				if (isset($value['$refArray']))
				{
					$refValue = $value['$refArray'];

					if (is_string($refValue))
					{
						$lastDotPos = strrpos($refValue, '.');
						if ($lastDotPos === false)
						{
							throw new InvalidSelectException("Invalid \$refArray format - expected 'path.to.array.field'");
						}

						$arrayPath = substr($refValue, 0, $lastDotPos);
						$field = substr($refValue, $lastDotPos + 1);

						$items = $getValueByPath($arrayPath, $context);
						if (!is_array($items) && !($items instanceof Traversable))
						{
							throw new InvalidSelectException("Path '{$arrayPath}' must point to an array or iterable");
						}

						$result = [];
						foreach ($items as $item)
						{
							if (!is_array($item) && !($item instanceof ArrayAccess))
							{
								throw new InvalidSelectException("Items in '{$arrayPath}' must be arrays or objects");
							}
							if (!isset($item[$field]))
							{
								throw new InvalidSelectException("Field '{$field}' not found in items");
							}
							$result[] = $item[$field];
						}

						return $result;
					}

					throw new InvalidSelectException("Invalid \$refArray value - expected string");
				}

				// Рекурсивная обработка вложенных массивов
				return array_map($replaceRef, $value);
			}

			return $value;
		};

		return $replaceRef($queryParams);
	}

	/**
	 * @throws MethodNotFoundException
	 * @throws AccessDeniedException
	 * @throws ObjectException
	 * @throws InternalException
	 * @throws SystemException
	 */
	protected function processServerRequestExecution(ServerRequest $request, CurrentUser $currentUser): Response
	{
		$methodDescription = $this->getMethodDescription($request->getMethod());
		if ($methodDescription === null)
		{
			throw new MethodNotFoundException($request->getMethod());
		}

		if (!$request->getScope())
		{
			$request->setScope($methodDescription->getScope() ?? null);
		}

		if (!$this->isRequestScopeAvailable($request->getScope()))
		{
			throw new AccessDeniedException(status: self::STATUS_FORBIDDEN);
		}

		$controller = ControllerBuilder::build($methodDescription->getController(), ['scope' => \Bitrix\Main\Engine\Controller::SCOPE_REST, 'currentUser' => $currentUser, 'request' => $request->getHttpRequest()]);

		if (!$controller instanceof RestController)
		{
			$exception = new SystemException('Use should use only RestController');
			throw new InternalException($exception);
		}

		$controller->setLocalErrorLanguage($this->localErrorLanguage);

		$manager = new RestManager();
		$autoWirings = $manager->getAutoWirings();

		$manager->registerAutoWirings($autoWirings);
		$response = $controller->run($methodDescription->getMethod(), [$request->getQuery(), ['__restServer' => $this]]);
		$manager->unRegisterAutoWirings($autoWirings);
		if ($controller->hasErrors())
		{
			return new ErrorResponse($controller->getErrors());
		}

		if (!$response instanceof Response)
		{
			$exception = new SystemException('Use should use only Response');
			throw new InternalException($exception);
		}

		if ($response instanceof ResponseWithRelations && !empty($response->getRelations()))
		{
			foreach ($response->getRelations() as $relation)
			{
				if (!$relation->getRequest()->filter)
				{
					continue;
				}

				$httpRequestBody = [
					'select' => array_merge($relation->getRequest()->select->getList(), $relation->getRequest()->select->getRelationFields()),
					'filter' => $relation->getRequest()->filter->getList(),
				];
				if ($relation->getRequest()->order)
				{
					$httpRequestBody['order'] = $relation->getRequest()->order->getList();
				}

				$httpRequest = new \Bitrix\Main\HttpRequest(\Bitrix\Main\Context::getCurrent()->getServer(), [], [], [], [], $httpRequestBody);

				$subRequest = new ServerRequest($relation->getMethod(), $request->getQuery(), $httpRequest);
				$subResponse = $this->processRequest($subRequest, $currentUser);
				$relation->setResponse($subResponse);
			}
		}

		return $response;
	}

	protected function processException(RestExceptionInterface|Exception $e): array
	{
		global $APPLICATION;

		$this->error = $e;

		$ex = $APPLICATION->GetException();
		if ($ex instanceof CApplicationException)
		{
			$this->error = new InternalException(new Exception($ex->msg));
		}

		return $this->outputError();
	}

	/**
	 * @param Response $response
	 */
	protected function processResponse(mixed $response): array
	{
		$result = $response->toArray();

		if ($this->securityClientState && $this->securityMethodState)
		{
			$result['signature'] = $this->getApplicationSignature();
		}

		if (!$response->isShowRawData())
		{
			$result = ['result' => $result];
		}

		if ($response->isShowDebugInfo())
		{
			$result = $this->appendDebugInfo($result);
		}

		if ($response instanceof ErrorResponse)
		{
			$this->error = $response;
		}

		return $result;
	}

	protected function getRequestAccess(array $query): array
	{
		if ($this->requestAccess === null)
		{
			$res = [];
			if (!CRestUtil::checkAuth($query, '_global', $res))
			{
				throw new AccessDeniedException(status: $res['error'] === 'insufficient_scope' ? self::STATUS_FORBIDDEN : self::STATUS_UNAUTHORIZED);
			}
			$this->requestAccess = $res;
			if ($res['scope'])
			{
				$this->availableScopes = array_merge($this->availableScopes, explode(',', $res['scope']));
			}
		}

		return $this->requestAccess;
	}

	private function isRequestScopeAvailable(?string $scope): bool
	{
		return in_array($scope, $this->availableScopes, true);
	}

	protected function outputError(): array
	{
		if (!is_subclass_of($this->error, RestException::class))
		{
			$this->error = new InternalException($this->error);
		}
		return ['error' => $this->error->output($this->localErrorLanguage)];
	}
}
