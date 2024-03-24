<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Im\Call\Call;

class CallFactory
{
	public static function createWithEntity($type, $provider, $entityType, $entityId, $initiatorId)
	{
		switch ($provider)
		{
			case 'Bitrix':
				return BitrixCall::createWithEntity($type, $provider, $entityType, $entityId, $initiatorId);
			default:
				return Call::createWithEntity($type, $provider, $entityType, $entityId, $initiatorId);
		}
	}

	public static function createWithArray($provider, array $fields): Call
	{
		switch ($provider)
		{
			case 'Bitrix':
				return BitrixCall::createWithArray($fields);
			default:
				return Call::createWithArray($fields);
		}
	}

	public static function searchActive($type, $provider, $entityType, $entityId, $currentUserId = 0)
	{
		switch ($provider)
		{
			case 'Bitrix':
				return BitrixCall::searchActive($type, $provider, $entityType, $entityId, $currentUserId);
			default:
				return Call::searchActive($type, $provider, $entityType, $entityId, $currentUserId);
		}
	}

	public static function searchActiveByUuid(string $provider, string $uuid)
	{
		switch ($provider)
		{
			case 'Bitrix':
				return BitrixCall::searchActiveByUuid($uuid);
			default:
				return Call::searchActiveByUuid($uuid);
		}
	}
}