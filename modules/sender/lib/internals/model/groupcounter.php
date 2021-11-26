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
 * Class GroupCounterTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupCounter_Query query()
 * @method static EO_GroupCounter_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_GroupCounter_Result getById($id)
 * @method static EO_GroupCounter_Result getList(array $parameters = array())
 * @method static EO_GroupCounter_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupCounter createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupCounter wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupCounter_Collection wakeUpCollection($rows)
 */
class GroupCounterTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_counter';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true,
			),
			'TYPE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true,
			),

			'CNT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 0,
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Delete counters by group ID.
	 *
	 * @param int $groupId Group ID.
	 * @return bool
	 */
	public static function deleteByGroupId($groupId)
	{
		$items = static::getList([
			'select' => ['GROUP_ID', 'TYPE_ID'],
			'filter' => ['=GROUP_ID' => $groupId]
		]);
		foreach ($items as $primary)
		{
			$result = static::delete($primary);
			if (!$result->isSuccess())
			{
				return false;
			}
		}

		return true;
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