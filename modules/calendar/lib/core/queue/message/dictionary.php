<?php

namespace Bitrix\Calendar\Core\Queue\Message;

class Dictionary
{
	public const MESSAGE_PARTS = [
		'body' => 'body',
		'headers' => 'headers',
		'properties' => 'properties',
	];

	public const HEADER_KEYS = [
		'routingKey' => 'routingKey',
	];
}