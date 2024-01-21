<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Psr\Http\Message\RequestInterface;

/**
 * @method RequestEventResult[] getResults()
 */
class RequestEvent extends Main\Event
{
	protected HttpClient $client;
	protected RequestInterface $request;

	public function __construct(HttpClient $client, RequestInterface $request, string $type)
	{
		parent::__construct('main', $type);

		$this->client = $client;
		$this->request = $request;
	}

	public function getClient(): HttpClient
	{
		return $this->client;
	}

	public function getRequest(): RequestInterface
	{
		return $this->request;
	}
}
