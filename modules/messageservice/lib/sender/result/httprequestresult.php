<?php
namespace Bitrix\MessageService\Sender\Result;

use Bitrix\Main\Result;
use Bitrix\MessageService\DTO;

class HttpRequestResult extends Result
{
	protected $httpRequest;
	protected $httpResponse;

	/**
	 * @return ?DTO\Request
	 */
	public function getHttpRequest(): ?DTO\Request
	{
		return $this->httpRequest;
	}

	/**
	 * @param DTO\Request $httpRequest
	 */
	public function setHttpRequest(DTO\Request $httpRequest): HttpRequestResult
	{
		$this->httpRequest = $httpRequest;
		return $this;
	}

	/**
	 * @return ?DTO\Response
	 */
	public function getHttpResponse(): ?DTO\Response
	{
		return $this->httpResponse;
	}

	/**
	 * @param DTO\Response $httpResponse
	 */
	public function setHttpResponse(DTO\Response $httpResponse): HttpRequestResult
	{
		$this->httpResponse = $httpResponse;
		return $this;
	}
}