<?php

namespace Bitrix\MessageService\Providers;

use Bitrix\MessageService\Sender\Result\HttpRequestResult;

interface ExternalSender
{
	public function callExternalMethod(string $method, ?array $requestParams = null, string $httpMethod = ''): HttpRequestResult;
	public function setApiKey(string $apiKey): self;
}