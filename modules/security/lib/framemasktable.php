<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class FrameMaskTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FrameMask_Query query()
 * @method static EO_FrameMask_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FrameMask_Result getById($id)
 * @method static EO_FrameMask_Result getList(array $parameters = [])
 * @method static EO_FrameMask_Entity getEntity()
 * @method static \Bitrix\Security\FrameMask createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\FrameMasks createCollection()
 * @method static \Bitrix\Security\FrameMask wakeUpObject($row)
 * @method static \Bitrix\Security\FrameMasks wakeUpCollection($rows)
 */
class FrameMaskTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_frame_mask';
	}

	public static function getMap()
	{
		return [
			(new \Bitrix\Main\Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new \Bitrix\Main\Entity\IntegerField('SORT'))
				->configureDefaultValue(10),
			(new \Bitrix\Main\Entity\StringField('SITE_ID'))
				->configureSize(2)
				->configureNullable(),
			(new \Bitrix\Main\Entity\StringField('FRAME_MASK'))
				->configureSize(250)
				->configureNullable(),
			(new \Bitrix\Main\Entity\StringField('LIKE_MASK'))
				->configureSize(250)
				->configureNullable(),
			(new \Bitrix\Main\Entity\StringField('PREG_MASK'))
				->configureSize(250)
				->configureNullable(),

		];
	}

	public static function getCollectionClass()
	{
		return FrameMasks::class;
	}

	public static function getObjectClass()
	{
		return FrameMask::class;
	}

	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		$where = Query::buildFilterSql($entity, $filter);
		$where = $where ? 'WHERE ' . $where : '';

		$sql = sprintf(
			'DELETE FROM %s %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			$where
		);

		$res = $connection->query($sql);

		return $res;
	}

}

class FrameMasks extends EO_FrameMask_Collection
{
}

class FrameMask extends EO_FrameMask
{
}
