<?php

namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\MessageService\Sender\Result\HttpRequestResult;
use Bitrix\MessageService\Providers\ExternalSender as IExternalSender;

abstract class ExternalSender implements IExternalSender
{
	protected const USER_AGENT = 'Bitrix24';
	protected const CONTENT_TYPE = 'application/json';
	protected const CHARSET = 'UTF-8';

	protected const WAIT_RESPONSE = true;

	protected string $apiKey;
	protected string $apiEndpoint;

	protected int $socketTimeout;
	protected int $streamTimeout;

	public function setApiKey(string $apiKey) : self
	{
		$this->apiKey = $apiKey;

		return $this;
	}
}