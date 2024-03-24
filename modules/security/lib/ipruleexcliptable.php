<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class IPRuleExclIPTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IPRuleExclIP_Query query()
 * @method static EO_IPRuleExclIP_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IPRuleExclIP_Result getById($id)
 * @method static EO_IPRuleExclIP_Result getList(array $parameters = [])
 * @method static EO_IPRuleExclIP_Entity getEntity()
 * @method static \Bitrix\Security\IPRuleExclIP createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\IPRuleExclIPs createCollection()
 * @method static \Bitrix\Security\IPRuleExclIP wakeUpObject($row)
 * @method static \Bitrix\Security\IPRuleExclIPs wakeUpCollection($rows)
 */
class IPRuleExclIPTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_iprule_excl_ip';
	}

	public static function getMap()
	{
		return [
			(new \Bitrix\Main\Entity\IntegerField('IPRULE_ID'))
				->configurePrimary(),
			(new \Bitrix\Main\Entity\StringField('RULE_IP'))
				->configurePrimary()
				->configureSize(50),
			(new \Bitrix\Main\Entity\IntegerField('SORT'))
				->configureDefaultValue(500),
			(new \Bitrix\Main\Entity\IntegerField('IP_START'))
				->configureSize(18)
				->configureNullable(),
			(new \Bitrix\Main\Entity\IntegerField('IP_END'))
				->configureSize(18)
				->configureNullable()
		];
	}

	public static function getCollectionClass()
	{
		return IPRuleExclIPs::class;
	}

	public static function getObjectClass()
	{
		return IPRuleExclIP::class;
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

class IPRuleExclIPs extends EO_IPRuleExclIP_Collection
{
}

class IPRuleExclIP extends EO_IPRuleExclIP
{
}
