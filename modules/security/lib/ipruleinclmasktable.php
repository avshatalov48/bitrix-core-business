<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class IPRuleInclMaskTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IPRuleInclMask_Query query()
 * @method static EO_IPRuleInclMask_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IPRuleInclMask_Result getById($id)
 * @method static EO_IPRuleInclMask_Result getList(array $parameters = [])
 * @method static EO_IPRuleInclMask_Entity getEntity()
 * @method static \Bitrix\Security\IPRuleInclMask createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\IPRuleInclMasks createCollection()
 * @method static \Bitrix\Security\IPRuleInclMask wakeUpObject($row)
 * @method static \Bitrix\Security\IPRuleInclMasks wakeUpCollection($rows)
 */
class IPRuleInclMaskTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_iprule_incl_mask';
	}

	public static function getMap()
	{
		return [
			(new \Bitrix\Main\Entity\IntegerField('IPRULE_ID'))
				->configurePrimary(),
			(new \Bitrix\Main\Entity\StringField('RULE_MASK'))
				->configurePrimary()
				->configureSize(250),
			(new \Bitrix\Main\Entity\IntegerField('SORT'))
				->configureDefaultValue(500),
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
		return IPRuleInclMasks::class;
	}

	public static function getObjectClass()
	{
		return IPRuleInclMask::class;
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

class IPRuleInclMasks extends EO_IPRuleInclMask_Collection
{
}

class IPRuleInclMask extends EO_IPRuleInclMask
{
}
