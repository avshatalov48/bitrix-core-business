<?php
namespace Bitrix\Landing;

use \Bitrix\Main\Entity;
use \Bitrix\Main\ORM\Query\Result as QueryResult;
use \Bitrix\Landing\Internals\LockTable;

class Lock
{
	/**
	 * Site entity type.
	 */
	const ENTITY_TYPE_SITE = 'S';

	/**
	 * Landing entity type.
	 */
	const ENTITY_TYPE_LANDING = 'L';

	/**
	 * Lock type for 'delete'.
	 */
	const LOCK_TYPE_DELETE = 'D';

	/**
	 * Returns true if entity is under lock.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @param string $lockType Lock type.
	 * @return bool
	 */
	protected static function isEntityLocked(int $entityId, string $entityType, string $lockType): bool
	{
		return LockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType,
				'=LOCK_TYPE' => $lockType
			]
		])->fetch() ? true : false;
	}

	/**
	 * Locks / unlocks entity.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @param string $lockType Lock type.
	 * @param bool $lock Lock or unlock.
	 * @return bool
	 */
	protected static function lockEntity(int $entityId, string $entityType, string $lockType, bool $lock = true): bool
	{
		$current = LockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType,
				'=LOCK_TYPE' => $lockType
			]
		])->fetch();
		if (!$lock && isset($current['ID']))
		{
			return LockTable::delete($current['ID'])->isSuccess();
		}
		if ($lock && !isset($current['ID']))
		{
			return LockTable::add([
				'ENTITY_ID' => $entityId,
				'ENTITY_TYPE' => $entityType,
				'LOCK_TYPE' => $lockType
			])->isSuccess();
		}
		return true;
	}

	/**
	 * Returns true if site is under 'delete' lock.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function isSiteDeleteLocked(int $siteId): bool
	{
		if (self::isEntityLocked($siteId, self::ENTITY_TYPE_SITE, self::LOCK_TYPE_DELETE))
		{
			return true;
		}
		// check status for site's landings
		return LockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'LANDING.SITE_ID' => $siteId,
				'=ENTITY_TYPE' => self::ENTITY_TYPE_LANDING,
				'=LOCK_TYPE' => self::LOCK_TYPE_DELETE
			],
			'runtime' => [
				new Entity\ReferenceField(
					'LANDING',
					'Bitrix\Landing\Internals\LandingTable',
					[
						'=this.ENTITY_ID' => 'ref.ID',
						'=this.ENTITY_TYPE' => [
							'?', self::ENTITY_TYPE_LANDING
						]
					]
				)
			]
		])->fetch() ? true : false;
	}

	/**
	 * Returns true if landing is under 'delete' lock.
	 * @param int $landingId Landing id.
	 * @return bool
	 */
	public static function isLandingDeleteLocked(int $landingId): bool
	{
		return self::isEntityLocked($landingId, self::ENTITY_TYPE_LANDING, self::LOCK_TYPE_DELETE);
	}

	/**
	 * Locks site for delete.
	 * @param int $siteId Site id.
	 * @param bool $lock Lock or unlock.
	 * @return bool
	 */
	public static function lockDeleteSite(int $siteId, bool $lock = true): bool
	{
		if (Site::ping($siteId, !$lock))
		{
			return self::lockEntity($siteId, self::ENTITY_TYPE_SITE, self::LOCK_TYPE_DELETE, $lock);
		}
		return false;
	}

	/**
	 * Locks landing for delete.
	 * @param int $landingId Landing id.
	 * @param bool $lock Lock or unlock.
	 * @return bool
	 */
	public static function lockDeleteLanding(int $landingId, bool $lock = true): bool
	{
		if (Landing::ping($landingId, !$lock))
		{
			return self::lockEntity($landingId, self::ENTITY_TYPE_LANDING, self::LOCK_TYPE_DELETE, $lock);
		}
		return false;
	}

	/**
	 * Provides ORM queries to LockTable.
	 * @param array $params ORM params.
	 * @return QueryResult
	 */
	public static function getList(array $params = []): QueryResult
	{
		return LockTable::getList($params);
	}
}
