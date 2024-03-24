<?php

namespace Bitrix\Socialnetwork\Internals\EventService;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;

/**
 * Class EventTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Event_Query query()
 * @method static EO_Event_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Event_Result getById($id)
 * @method static EO_Event_Result getList(array $parameters = [])
 * @method static EO_Event_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\Internals\EventService\EO_Event createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\EventService\EO_Event_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\EventService\EO_Event wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\EventService\EO_Event_Collection wakeUpCollection($rows)
 */
class EventTable extends DataManager
{
	private const LOST_LIMIT = 50;
	private const LOST_TTL = 60;

	public static function getTableName(): string
	{
		return 'b_sonet_scorer_event';
	}

	public static function getClass(): string
	{
		return static::class;
	}

	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'HID' => [
				'data_type' => 'string',
				'required' => true,
			],
			'TYPE' => [
				'data_type' => 'string',
				'required' => true,
			],
			'DATA' => [
				'data_type' => 'text',
				'required' => true,
			],
			'LOG_DATA' => [
				'data_type' => 'text',
			],
			'CREATED' => [
				'data_type' => 'datetime'
			],
			'PROCESSED' => [
				'data_type' => 'datetime'
			],
		];
	}

	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function markProcessed(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$res = $connection->query(sprintf(
			'UPDATE %s SET PROCESSED = CURRENT_TIMESTAMP WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		));
		\CTimeZone::enable();

		return $res;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function hasLostEvents(): bool
	{
		$res = self::getList([
			'filter' => [
				'<=PROCESSED' => DateTime::createFromTimestamp(0),
			],
			'limit' => 1
		]);

		return $res->getSelectedRowsCount() > 0;
	}

	/**
	 * @param string $currentHid
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getLostEvents(): array
	{
		$limit = \COption::GetOptionString('socialnetwork', "sonetLostCountersLimit", 0);
		if (!$limit)
		{
			$limit = self::LOST_LIMIT;
		}

		\CTimeZone::disable();
		$res = self::getList([
			'filter' => [
				'<=PROCESSED' => DateTime::createFromTimestamp(0),
				'<CREATED' => DateTime::createFromTimestamp(time() - self::LOST_TTL)
			],
			'limit' => $limit
		]);
		\CTimeZone::enable();

		$events = [];
		while ($row = $res->fetch())
		{
			$events[] = $row;
		}

		return $events;
	}

	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		$enabled = \CTimeZone::Enabled();
		if ($enabled)
		{
			\CTimeZone::Disable();
		}

		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);

		if ($enabled)
		{
			\CTimeZone::Enable();
		}

		return $res;
	}
}