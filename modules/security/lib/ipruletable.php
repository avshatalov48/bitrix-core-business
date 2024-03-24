<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class IPRuleTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IPRule_Query query()
 * @method static EO_IPRule_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IPRule_Result getById($id)
 * @method static EO_IPRule_Result getList(array $parameters = [])
 * @method static EO_IPRule_Entity getEntity()
 * @method static \Bitrix\Security\IPRule createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\IPRules createCollection()
 * @method static \Bitrix\Security\IPRule wakeUpObject($row)
 * @method static \Bitrix\Security\IPRules wakeUpCollection($rows)
 */
class IPRuleTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_iprule';
	}

	public static function getMap()
	{
		return [
			(new \Bitrix\Main\Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new \Bitrix\Main\Entity\StringField('RULE_TYPE'))
				->configureSize(1)
				->configureDefaultValue('M'),
			(new \Bitrix\Main\Entity\EnumField('ACTIVE'))
				->configureValues(['Y', 'N'])
				->configureDefaultValue('Y'),
			(new \Bitrix\Main\Entity\EnumField('ADMIN_SECTION'))
				->configureValues(['Y', 'N'])
				->configureDefaultValue('Y'),
			(new \Bitrix\Main\Entity\StringField('SITE_ID'))
				->configureSize(2)
				->configureNullable(),
			(new \Bitrix\Main\Entity\IntegerField('SORT'))
				->configureDefaultValue(500),
			(new \Bitrix\Main\Entity\DatetimeField('ACTIVE_FROM'))
				->configureNullable(),
			(new \Bitrix\Main\Entity\IntegerField('ACTIVE_FROM_TIMESTAMP'))
				->configureNullable(),
			(new \Bitrix\Main\Entity\DatetimeField('ACTIVE_TO'))
				->configureNullable(),
			(new \Bitrix\Main\Entity\IntegerField('ACTIVE_TO_TIMESTAMP'))
				->configureNullable(),
			(new \Bitrix\Main\Entity\StringField('NAME'))
				->configureSize(250)
				->configureNullable()
		];
	}

	public static function getCollectionClass()
	{
		return IPRules::class;
	}

	public static function getObjectClass()
	{
		return IPRule::class;
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

class IPRules extends EO_IPRule_Collection
{
}

class IPRule extends EO_IPRule
{
}
