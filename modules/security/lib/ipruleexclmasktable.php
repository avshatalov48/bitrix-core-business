<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class IPRuleExclMaskTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IPRuleExclMask_Query query()
 * @method static EO_IPRuleExclMask_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IPRuleExclMask_Result getById($id)
 * @method static EO_IPRuleExclMask_Result getList(array $parameters = [])
 * @method static EO_IPRuleExclMask_Entity getEntity()
 * @method static \Bitrix\Security\IPRuleExclMask createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\IPRuleExclMasks createCollection()
 * @method static \Bitrix\Security\IPRuleExclMask wakeUpObject($row)
 * @method static \Bitrix\Security\IPRuleExclMasks wakeUpCollection($rows)
 */
class IPRuleExclMaskTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_iprule_excl_mask';
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
		return IPRuleExclMasks::class;
	}

	public static function getObjectClass()
	{
		return IPRuleExclMask::class;
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

class IPRuleExclMasks extends EO_IPRuleExclMask_Collection
{
}

class IPRuleExclMask extends EO_IPRuleExclMask
{
}
