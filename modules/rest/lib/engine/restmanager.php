<?php

namespace Bitrix\Rest\Engine;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Crawler;
use Bitrix\Main\Engine\Resolver;
use Bitrix\Main\Errorable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Event;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Type\Contract;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\RestException;

class RestManager extends \IRestService
{
	/** @var \CRestServer */
	protected $restServer;
	/** @var PageNavigation */
	private $pageNavigation;

	public static function onFindMethodDescription($potentialAction)
	{
		$restManager = new static();

		$request = new \Bitrix\Main\HttpRequest(
			Context::getCurrent()->getServer(),
			['action' => $potentialAction],
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
			'scope' => static::getModuleScopeAlias($router->getModule()),
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
		$this->restServer = $restServer;

		$errorCollection = new ErrorCollection();
		$method = $restServer->getMethod();

		$request = new \Bitrix\Main\HttpRequest(
			Context::getCurrent()->getServer(),
			['action' => $method],
			[], [], []
		);
		$router = new Engine\Router($request);

		list ($controller, $action) = Resolver::getControllerAndAction(
			$router->getVendor(),
			$router->getModule(),
			$router->getAction(),
			Controller::SCOPE_REST
		);
		if (!$controller)
		{
			throw new RestException("Unknown {$method}. There is not controller in module {$router->getModule()}");
		}

		$this->registerAutoWirings($restServer, $start);

		/** @var Controller $controller */
		$result = $controller->run($action, array($params));
		if ($result instanceof Engine\Response\File)
		{
			/** @noinspection PhpVoidFunctionResultUsedInspection */
			return $result->send();
		}

		if ($result instanceof HttpResponse)
		{
			if ($result instanceof Errorable)
			{
				$errorCollection->add($result->getErrors());
			}

			$result = $result->getContent();
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
	 * @param Engine\Response\DataType\Page $page
	 * @see \IRestService::setNavData
	 *
	 * @return array
	 */
	private function getNavigationData(Engine\Response\DataType\Page $page)
	{
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

	private function registerAutoWirings(\CRestServer $restServer, $start)
	{
		Engine\Binder::registerParameter(
			get_class($restServer),
			function() use ($restServer) {
				return $restServer;
			}
		);

		$pageNavigation = new PageNavigation('nav');
		$pageNavigation->setPageSize(RestManager::LIST_LIMIT);
		if($start)
		{
			$pageNavigation->setCurrentPage(intval($start / RestManager::LIST_LIMIT) + 1);
		}

		//php 5.3 we can't use this in \Closure.
		$this->pageNavigation = $pageNavigation;

		/** @see \Bitrix\Main\UI\PageNavigation */
		Engine\Binder::registerParameter(
			'\\Bitrix\\Main\\UI\\PageNavigation',
			function() use ($pageNavigation) {
				return $pageNavigation;
			}
		);
	}
}
