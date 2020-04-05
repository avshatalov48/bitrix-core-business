<?php
namespace Bitrix\Rest\Event;


interface ProviderOfflineInterface
{
	public function send(array $eventList);
}