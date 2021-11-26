<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;

Loc::loadMessages(__FILE__);

/**
 * Class GroupThreadTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupThread_Query query()
 * @method static EO_GroupThread_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_GroupThread_Result getById($id)
 * @method static EO_GroupThread_Result getList(array $parameters = array())
 * @method static EO_GroupThread_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupThread createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupThread_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupThread wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupThread_Collection wakeUpCollection($rows)
 */
class GroupThreadTable extends Entity\DataManager
{
	const STATUS_NEW         = 'N';
	const STATUS_IN_PROGRESS = 'P';
	const STATUS_DONE        = 'D';

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_thread';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'THREAD_ID'   => [
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			],
			'GROUP_STATE_ID'  => [
				'data_type' => 'integer',
				'primary'   => true,
				'required'  => true,
			],
			'STEP'      => [
				'data_type' => 'integer',
				'required'  => true,
			],
			'STATUS'      => [
				'data_type' => 'string',
				'required'  => true,
			],
			'THREAD_TYPE' => [
				'data_type' => 'string',
				'required'  => true,
			],
			'EXPIRE_AT' => [
				'data_type' => 'datetime',
				'required'  => true,
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
	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}