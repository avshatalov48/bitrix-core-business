<?php

namespace Bitrix\Rest\Engine;

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

	/**
	 * Builds list of REST methods which provides modules.
	 *
	 * @return array
	 */
	public static function onRestServiceBuildDescription()
	{
		$restManager = new static();

		$methods = array();
		foreach ($restManager->getModules() as $module)
		{
			$actions = Crawler::getInstance()->listActionsByModule($module);
			if (!$actions)
			{
				continue;
			}

			$methods[$module] = array();
			foreach ($actions as $action)
			{
				$methods[$module][$module . '.' . $action] = array(
					$restManager,
					'processMethodRequest'
				);
			}
		}

		return $methods;
	}

	protected function getModules()
	{
		$event = new Event('rest', 'onRestGetModule');
		$event->send();

		$modules = array();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == \Bitrix\Main\EventResult::ERROR)
			{
				continue;
			}

			$eventParams = $eventResult->getParameters();
			if (!empty($eventParams['MODULE_ID']))
			{
				$modules[] = $eventParams['MODULE_ID'];
			}
		}

		return $modules;
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
		$parts = explode('.', $method);
		$module = array_shift($parts);

		$action = implode('.', $parts);
		list ($controller, $action) = Resolver::getControllerAndAction($module, $action, Controller::SCOPE_REST);
		if (!$controller)
		{
			throw new RestException("Unknown {$method}. There is not controller in module {$module}");
		}

		$this->registerAutoWirings($restServer, $start);

		/** @var Controller $controller */
		$result = $controller->run($action, array($params));
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
			return self::setNavData($this->processData($result->getIterator()), array(
				"count" => $result->getTotalCount(),
				"offset" => $this->pageNavigation->getOffset(),
			));
		}

		if ($result instanceof Contract\Arrayable)
		{
			$result = $result->toArray();
		}

		if (is_array($result) || $result instanceof \Traversable)
		{
			foreach ($result as $key => $item)
			{
				$result[$key] = $this->processData($item);
			}
		}

		return $result;
	}

	private function convertAjaxUriToRest(Uri $uri)
	{
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
		if (empty($params['action']) || empty($params['m']))
		{
			return $uri->getUri();
		}

		//todo @see \CRestUtil::getSpecialUrl
		$uri->addParams(array('_' => randString(32)));
		$query = $uri->getQuery();

		$scope = $this->restServer->getScope();
		if($scope === \CRestUtil::GLOBAL_SCOPE)
		{
			$scope = '';
		}

		$method = "{$params['m']}.{$params['action']}";
		$signature = $this->restServer->getTokenCheckSignature($method, $query);

		$token = $scope
				 .\CRestUtil::TOKEN_DELIMITER.$query
				 .\CRestUtil::TOKEN_DELIMITER.$signature;


		$authData = $this->restServer->getAuthData();
		if($authData['password_id'])
		{
			$auth = $this->restServer->getAuth();

			return \CRestUtil::getWebhookEndpoint(
					$auth['ap'],
					$auth['aplogin'],
					$method
				)."?".http_build_query(array(
				   'token' => $token,
				));
		}
		else
		{
			$urlParam = array_merge(
				$this->restServer->getAuth(),
				array(
					'token' => $token,
				)
			);

			return \CHTTP::URN2URI(
				$this->getRestEndPoint()."/".$method.".".$this->restServer->getTransport()
				."?".http_build_query($urlParam)
			);
		}
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

		$description = array();
		/** @var Error $lastError */
		$lastError = array_pop($errors);
		$description[] = $lastError->getMessage() . " ({$lastError->getCode()}).";

		foreach ($errors as $error)
		{
			$description[] = $error->getMessage() . " ({$error->getCode()}).";
		}

		return new RestException(implode(' ', $description), $lastError->getCode());
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