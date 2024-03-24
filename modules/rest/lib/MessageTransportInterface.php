<?php

namespace Bitrix\Rest;

interface MessageTransportInterface
{
	public function send(string $method, array $parameters): bool;
}