<?php

namespace Bitrix\Seo\Catalog;

use Bitrix\Seo\Engine;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Request;
use Bitrix\Main\Result;
use Bitrix\Main\Web\JWT;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\Response\Json;


class CatalogWebhookHandler
{
	/**@var Request $request*/
	private $request;

	/**@var Engine $engine*/
	private $engine;

	/**@var EventManager $eventManager*/
	private $eventManager;

	/**@var Application $eventManager*/
	private $application;

	public function __construct(
		Request $request,
		Engine $engine,
		EventManager $eventManager,
		Application $application
	)
	{
		$this->request = $request;
		$this->engine = $engine;
		$this->eventManager = $eventManager;
		$this->application = $application;
	}

	private function verifyRequest() : Result
	{
		$result = new Result();

		$authToken = $this->request->getHeader('Authorization');
		if (!$authToken)
		{
			$authToken = \Bitrix\Main\Context::getCurrent()->getServer()->get('REMOTE_USER');
		}
		if (!$authToken)
		{
			$result->addError(new Error('wrong request'));
			return $result;
		}

		$authToken = substr($authToken, strlen('Bearer '));
		$engineClientSecret = $this->engine->getClientSecret();
		$authTokenSalt = mb_substr($authToken, 0, 8);
		$isAllowedToken = $authToken === $authTokenSalt . md5($authTokenSalt . $engineClientSecret);
		if (!$isAllowedToken)
		{
			$result->addError(new Error('Invalid client credentials'));
		}

		return $result;

	}

	private function run() : Json
	{
		$result = $this->verifyRequest();

		if (!$result->isSuccess())
		{
			return $this->buildErrorResponse(
				$result->getErrorCollection()->current()
			);
		}

		$this->eventManager->send(
			new Event('seo', 'OnCatalogWebhook', ['payload' => $this->request->toArray(),])
		);

		return $this->buildResponse();
	}


	public function handle(): void
	{
		$response = $this->run();
		$this->application->end(200, $response);
	}

	private function buildErrorResponse(Error $error): Json
	{
		return new Json([
			'error' => [
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
			],
			'data' => [],
		]);
	}

	private function buildResponse(): Json
	{
		return new Json([
			'error' => false,
			'data' => [
				'status' => 'ok'
			],
		]);
	}
}