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

		return match (true)
		{
			$type === Call::TYPE_PERMANENT => ConferenceCall::class,
			$provider === Call::PROVIDER_BITRIX => BitrixCall::class,
			$provider === Call::PROVIDER_PLAIN => PlainCall::class,
			default => Call::class
		};
	}

	/**
	 * @return Call|BitrixCall
	 */
	public static function createWithEntity(
		int $type,
		string $provider,
		string $entityType,
		string $entityId,
		int $initiatorId,
		?string $callUuid = null,
		?int $scheme = null
	): Call
	{
		$providerClass = self::getProviderClass($provider, $type);
		return $providerClass::createWithEntity($type, $provider, $entityType, $entityId, $initiatorId, $callUuid, $scheme);
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
	 * @return Call|BitrixCall
	 */
	public static function getCallInstance(string $provider, array $fields): Call
	{
		$type = (int)($fields['TYPE'] ?? Call::TYPE_INSTANT);
		$providerClass = self::getProviderClass($provider, $type);
		return $providerClass::createCallInstance($fields);
	}

	/**
	 * @return Call|BitrixCall|null
	 */
	public static function searchActive(int $type, string $provider, string $entityType, string $entityId): ?Call
	{
		$fields = self::search($type, $provider, $entityType, $entityId);
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
	public static function searchActiveCall(int $type, string $provider, string $entityType, string $entityId): ?Call
	{
		$fields = self::search($type, $provider, $entityType, $entityId);
		if ($fields)
		{
			$instance = self::getCallInstance($provider, $fields);

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
	 * Gets list active calls of a user on portal.
	 * @return array
	 */
	public static function getUserActiveCalls(int $userId, int $depthHours = 12): array
	{
		$date = (new DateTime())->add("-{$depthHours} hour");

		$query = CallTable::query()
			->addSelect('*')
			->whereIn('STATE', [\Bitrix\Im\Call\Call::STATE_NEW, \Bitrix\Im\Call\Call::STATE_INVITING])
			->where('START_DATE', '>=', $date)
			->where('CALL_USER.USER_ID', $userId)
		;
		$activeCalls = $query->exec()->fetchAll();

		return $activeCalls ?: [];
	}

	/**
	 * Checks if user has active call.
	 * @return bool
	 */
	public static function hasUserActiveCalls(int $userId, int $depthHours = 12): bool
	{
		$date = (new DateTime())->add("-{$depthHours} hour");

		$query = CallTable::query()
			->addSelect('ID')
			->whereIn('STATE', [\Bitrix\Im\Call\Call::STATE_NEW, \Bitrix\Im\Call\Call::STATE_INVITING])
			->where('START_DATE', '>=', $date)
			->where('CALL_USER.USER_ID', $userId)
			->whereIn('CALL_USER.STATE', [\Bitrix\Im\Call\CallUser::STATE_READY, \Bitrix\Im\Call\CallUser::STATE_CALLING])
			->setLimit(1)
		;
		$activeCall = $query->exec()->fetchAll();

		return (bool)$activeCall;
	}

	/**
	 * @param int $type
	 * @param string $provider
	 * @param string $entityType
	 * @param string $entityId
	 * @return array|null
	 */
	protected static function search(int $type, string $provider, string $entityType, string $entityId): ?array
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