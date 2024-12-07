<?php

namespace Bitrix\Rest\Engine;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\AutoWire;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Resolver;
use Bitrix\Main\Errorable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Type\Contract;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\Engine\ScopeManager;
use Bitrix\Rest\RestException;

class RestManager extends \IRestService
{
	public const DONT_CALCULATE_COUNT = -1;

	/** @var \CRestServer */
	protected $restServer;
	/** @var PageNavigation */
	private $pageNavigation;
	/** @var bool */
	private $calculateTotalCount = true;

	public static function onFindMethodDescription($potentialAction)
	{
		$restManager = new static();
		$potentialActionData = ScopeManager::getInstance()->getMethodInfo($potentialAction);

		$request = new \Bitrix\Main\HttpRequest(
			Context::getCurrent()->getServer(),
			['action' => $potentialActionData['method']],
			[], [], []
		);

		$router = new Engine\Router($request);
		$controllersConfig = Configuration::getInstance($router->getModule());
		if (empty($controllersConfig['controllers']['restIntegration']['enabled']))
		{
			return false;
		}

		/** @var Controller $controller */
		list($controller) = $router->getControllerAndAction();
		if (!$controller || $controller instanceof Engine\DefaultController)
		{
			return false;
		}

		return [
			'scope' => static::getModuleScopeAlias($potentialActionData['scope']),
			'callback' => [
				$restManager, 'processMethodRequest'
			]
		];
	}

	public static function getModuleScopeAlias($moduleId)
	{
		if($moduleId === 'tasks')
		{
			return 'task';
		}

		return $moduleId;
	}

	private static function getAlternativeScope($scope): ?array
	{
		if ($scope === \Bitrix\Rest\Api\User::SCOPE_USER)
		{
			return [
				\Bitrix\Rest\Api\User::SCOPE_USER_BRIEF,
				\Bitrix\Rest\Api\User::SCOPE_USER_BASIC,
			];
		}

		return null;
	}

	public static function fillAlternativeScope($scope, $scopeList)
	{
		if (!in_array($scope, $scopeList, true))
		{
			$altScopeList = static::getAlternativeScope($scope);
			if (is_array($altScopeList))
			{
				$hasScope = array_intersect($scopeList, $altScopeList);
				if (count($hasScope) > 0)
				{
					$scopeList[] = $scope;
				}
			}
		}

		return $scopeList;
	}

	/**
	 * Processes method to services.
	 *
	 * @param array $params Input parameters ($_GET, $_POST).
	 * @param     string $start Start position.
	 * @param \CRestServer $restServer REST server.
	 *
	 * @return array
	 * @throws RestException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function processMethodRequest(array $params, $start, \CRestServer $restServer)
	{
		$start = intval($start);
		$this->initialize($restServer, $start);

		$errorCollection = new ErrorCollection();
		$method = $restServer->getMethod();
		$methodData = ScopeManager::getInstance()->getMethodInfo($method);

		$request = new \Bitrix\Main\HttpRequest(
			Context::getCurrent()->getServer(),
			['action' => $methodData['method']],
			[], [], []
		);
		$router = new Engine\Router($request);

		/** @var Controller $controller */
		[$controller, $action] = Resolver::getControllerAndAction(
			$router->getVendor(),
			$router->getModule(),
			$router->getAction(),
			Controller::SCOPE_REST
		);
		if (!$controller)
		{
			throw new RestException("Unknown {$method}. There is not controller in module {$router->getModule()}");
		}

		$this->calculateTotalCount = true;
		if ((int)$start === self::DONT_CALCULATE_COUNT)
		{
			$this->calculateTotalCount = false;
		}

		$autoWirings = $this->getAutoWirings();

		$this->registerAutoWirings($autoWirings);
		$result = $controller->run($action, [$params, ['__restServer' => $restServer, '__calculateTotalCount' => $this->calculateTotalCount]]);
		$this->unRegisterAutoWirings($autoWirings);

		if ($result instanceof Engine\Response\File)
		{
			$result->send();
			return;
		}

		if ($result instanceof HttpResponse)
		{
			if ($result instanceof Errorable)
			{
				$errorCollection->add($result->getErrors());
			}

			$result = $result->getContent();
		}

		if ($result instanceof RestException)
		{
			throw $result;
		}

		if ($result === null)
		{
			$errorCollection->add($controller->getErrors());
			if (!$errorCollection->isEmpty())
			{
				throw $this->createExceptionFromErrors($errorCollection->toArray());
			}
		}

		return $this->processData($result);
	}

	/**
	 * @param \CRestServer $restServer
	 * @param $start
	 */
	private function initialize(\CRestServer $restServer, int $start): void
	{
		$pageNavigation = new PageNavigation('nav');
		$pageNavigation->setPageSize(static::LIST_LIMIT);
		if ($start > 0)
		{
			$pageNavigation->setCurrentPage((int)($start / static::LIST_LIMIT) + 1);
		}

		$this->pageNavigation = $pageNavigation;
		$this->restServer = $restServer;
	}

	/**
	 * @param Engine\Response\DataType\Page $page
	 * @see \IRestService::setNavData
	 *
	 * @return array
	 */
	private function getNavigationData(Engine\Response\DataType\Page $page): array
	{
		if (!$this->calculateTotalCount)
		{
			return [];
		}

		$result = [];
		$offset = $this->pageNavigation->getOffset();
		$total = $page->getTotalCount();

		$currentPageSize = count($page->getItems());

		if ($offset + $currentPageSize < $total)
		{
			$result['next'] = $offset + $currentPageSize;
		}

		$result['total'] = $total;

		return $result;
	}


	private function processData($result)
	{
		if ($result instanceof DateTime)
		{
			return \CRestUtil::convertDateTime($result);
		}

		if ($result instanceof Date)
		{
			return \CRestUtil::convertDate($result);
		}

		if ($result instanceof Uri)
		{
			return $this->convertAjaxUriToRest($result);
		}

		if ($result instanceof Engine\Response\DataType\Page)
		{
			if (method_exists($result, 'getId'))
			{
				$data = [$result->getId() => $this->processData($result->getIterator())];
			}
			else
			{
				$data = $this->processData($result->getIterator());
			}

			return array_merge($data, $this->getNavigationData($result));
		}

		if ($result instanceof Contract\Arrayable)
		{
			$result = $result->toArray();
		}
		if ($result instanceof \JsonSerializable)
		{
			$result = $result->jsonSerialize();
		}

		if (is_array($result))
		{
			foreach ($result as $key => $item)
			{
				if ($item instanceof Engine\Response\DataType\ContentUri)
				{
					$result[$key . "Machine"] = $this->processData($item);
					$result[$key] = $this->processData(new Uri($item));
				}
				else
				{
					$result[$key] = $this->processData($item);
				}

			}
		}
		elseif ($result instanceof \Traversable)
		{
			$newResult = [];
			foreach ($result as $key => $item)
			{
				$newResult[$key] = $this->processData($item);
			}

			$result = $newResult;
		}

		return $result;
	}

	private function convertAjaxUriToRest(Uri $uri)
	{
		if (!($uri instanceof Engine\Response\DataType\ContentUri))
		{
			return $uri->getUri();
		}

		$endPoint = Engine\UrlManager::getInstance()->getEndPoint(Engine\UrlManager::ABSOLUTE_URL);
		if ($uri->getPath() !== $endPoint->getPath())
		{
			return $uri->getUri();
		}

		if ($uri->getHost() && $uri->getHost() !== $endPoint->getHost())
		{
			return $uri->getUri();
		}

		parse_str($uri->getQuery(), $params);
		if (empty($params['action']))
		{
			return $uri->getUri();
		}

		return \CRestUtil::getSpecialUrl($params['action'], $params, $this->restServer);
	}

	private function getRestEndPoint()
	{
		return \Bitrix\Main\Config\Option::get('rest', 'rest_server_path', '/rest');
	}

	/**
	 * @param Error[] $errors
	 * @return RestException
	 */
	private function createExceptionFromErrors(array $errors)
	{
		if(!$errors)
		{
			return null;
		}

		$firstError = reset($errors);

		return new RestException($firstError->getMessage(), $firstError->getCode());
	}

	/**
	 * @param array $autoWirings
	 */
	private function registerAutoWirings(array $autoWirings): void
	{
		foreach ($autoWirings as $parameter)
		{
			AutoWire\Binder::registerGlobalAutoWiredParameter($parameter);
		}
	}

	/**
	 * @param array $autoWirings
	 */
	private function unRegisterAutoWirings(array $autoWirings): void
	{
		foreach ($autoWirings as $parameter)
		{
			AutoWire\Binder::unRegisterGlobalAutoWiredParameter($parameter);
		}
	}

	/**
	 * @return array
	 */
	private function getAutoWirings(): array
	{
		$buildRules = [
			'restServer' => [
				'class' => \CRestServer::class,
				'constructor' => function() {
					return $this->restServer;
				},
			],
			'pageNavigation' => [
				'class' => PageNavigation::class,
				'constructor' => function() {
					return $this->pageNavigation;
				},
			],
		];

		$autoWirings = [];
		foreach ($buildRules as $rule)
		{
			$autoWirings[] = new AutoWire\Parameter($rule['class'], $rule['constructor']);
		}

		return $autoWirings;
	}
}
