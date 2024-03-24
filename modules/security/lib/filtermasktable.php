<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class FilterMaskTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FilterMask_Query query()
 * @method static EO_FilterMask_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FilterMask_Result getById($id)
 * @method static EO_FilterMask_Result getList(array $parameters = [])
 * @method static EO_FilterMask_Entity getEntity()
 * @method static \Bitrix\Security\FilterMask createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\FilterMasks createCollection()
 * @method static \Bitrix\Security\FilterMask wakeUpObject($row)
 * @method static \Bitrix\Security\FilterMasks wakeUpCollection($rows)
 */
class FilterMaskTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_filter_mask';
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
			(new \Bitrix\Main\Entity\StringField('FILTER_MASK'))
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
		return FilterMasks::class;
	}

	public static function getObjectClass()
	{
		return FilterMask::class;
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

class FilterMasks extends EO_FilterMask_Collection
{
}

class FilterMask extends EO_FilterMask
{
}
