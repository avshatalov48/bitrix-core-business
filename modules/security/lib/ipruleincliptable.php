<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class IPRuleInclIPTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_IPRuleInclIP_Query query()
 * @method static EO_IPRuleInclIP_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_IPRuleInclIP_Result getById($id)
 * @method static EO_IPRuleInclIP_Result getList(array $parameters = [])
 * @method static EO_IPRuleInclIP_Entity getEntity()
 * @method static \Bitrix\Security\IPRuleInclIP createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\IPRuleInclIPs createCollection()
 * @method static \Bitrix\Security\IPRuleInclIP wakeUpObject($row)
 * @method static \Bitrix\Security\IPRuleInclIPs wakeUpCollection($rows)
 */
class IPRuleInclIPTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_iprule_incl_ip';
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
		return IPRuleInclIPs::class;
	}

	public static function getObjectClass()
	{
		return IPRuleInclIP::class;
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

class IPRuleInclIPs extends EO_IPRuleInclIP_Collection
{
}

class IPRuleInclIP extends EO_IPRuleInclIP
{
}
