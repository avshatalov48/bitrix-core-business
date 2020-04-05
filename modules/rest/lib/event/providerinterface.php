<?php
namespace Bitrix\Rest\Event;

interface ProviderInterface
{
	public function send(array $queryData);
}
