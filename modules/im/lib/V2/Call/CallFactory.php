<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Im\Call\Call;
use Bitrix\Im\Model\CallTable;
use Bitrix\Call\Call\PlainCall;
use Bitrix\Call\Call\BitrixCall;
use Bitrix\Call\Call\ConferenceCall;
use Bitrix\Main\Type\DateTime;

class CallFactory
{
	/**
	 * @return string|Call|BitrixCall
	 */
	protected static function getProviderClass(string $provider, int $type)
	{
		\Bitrix\Main\Loader::includeModule('call');
		if (false && $type == Call::TYPE_LANGE)
		{
			return \Bitrix\Call\Call\LargeCall::class;
		}

		if ($type == Call::TYPE_PERMANENT)
		{
			return ConferenceCall::class;
		}

		return match ($provider)
		{
			Call::PROVIDER_BITRIX => BitrixCall::class,
			Call::PROVIDER_PLAIN => PlainCall::class,
			default => Call::class
		};
	}

	/**
	 * @return Call|BitrixCall
	 */
	public static function createWithEntity(int $type, string $provider, string $entityType, string $entityId, int $initiatorId): Call
	{
		$providerClass = self::getProviderClass($provider, $type);
		return $providerClass::createWithEntity($type, $provider, $entityType, $entityId, $initiatorId);
	}

	/**
	 * @return Call|BitrixCall
	 */
	public static function createWithArray(string $provider, array $fields): Call
	{
		$type = (int)($fields['TYPE'] ?? Call::TYPE_INSTANT);
		$providerClass = self::getProviderClass($provider, $type);
		return $providerClass::createWithArray($fields);
	}

	/**
	 * @return Call|BitrixCall|null
	 */
	public static function searchActive(int $type, string $provider, string $entityType, string $entityId, int $currentUserId = 0): ?Call
	{
		$fields = self::search($type, $provider, $entityType, $entityId, $currentUserId);
		if ($fields)
		{
			$instance = self::createWithArray($provider, $fields);

			if ($instance->hasActiveUsers(false))
			{
				return $instance;
			}
		}

		return null;
	}

	/**
	 * @return Call|BitrixCall|null
	 */
	public static function searchActiveByUuid(string $provider, string $uuid): ?Call
	{
		$fields = self::searchByUuid($uuid);
		if ($fields)
		{
			return self::createWithArray($provider, $fields);
		}

		return null;
	}


	/**
	 * @param int $type
	 * @param string $provider
	 * @param string $entityType
	 * @param string $entityId
	 * @param int $currentUserId
	 * @return Call|null
	 */
	protected static function search(int $type, string $provider, string $entityType, string $entityId, int $currentUserId = 0): ?array
	{
		$query = CallTable::query()
			->addSelect('*')
			->where('TYPE', $type)
			->where('PROVIDER', $provider)
			->where('ENTITY_TYPE', $entityType)
			->where('ENTITY_ID', $entityId)
			->whereNot('STATE', Call::STATE_FINISHED)
			->whereNull('END_DATE')
			->addFilter('>START_DATE', (new DateTime)->add('-12 hours'))
			->setOrder(['ID' => 'DESC'])
			->setLimit(1)
		;

		/*
		if ($entityType === EntityType::CHAT && strpos($entityId, "chat") !== 0)
		{
			if (!$currentUserId)
			{
				$currentUserId = \Bitrix\Im\User::getInstance()->getId();
			}
			$query->where('INITIATOR_ID', $currentUserId);
			$query->where('ENTITY_ID', $entityId);
		}
		else
		{
			$query->where("ENTITY_ID", $entityId);
		}
		*/

		$callFields = $query->exec()->fetch();

		return $callFields ?: null;
	}

	protected static function searchByUuid(string $uuid): ?array
	{
		$callFields = CallTable::query()
			->addSelect("*")
			->where("UUID", $uuid)
			->setLimit(1)
			->exec()
			->fetch()
		;

		return $callFields ?: null;
	}
}