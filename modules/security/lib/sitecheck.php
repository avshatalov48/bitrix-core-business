<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class SiteCheckTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SiteCheck_Query query()
 * @method static EO_SiteCheck_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SiteCheck_Result getById($id)
 * @method static EO_SiteCheck_Result getList(array $parameters = [])
 * @method static EO_SiteCheck_Entity getEntity()
 * @method static \Bitrix\Security\SiteCheck createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\SiteChecks createCollection()
 * @method static \Bitrix\Security\SiteCheck wakeUpObject($row)
 * @method static \Bitrix\Security\SiteChecks wakeUpCollection($rows)
 */
class SiteCheckTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_security_sitecheck';
	}

	public static function getMap()
	{
		return [
			(new \Bitrix\Main\Entity\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new \Bitrix\Main\Entity\DatetimeField('TEST_DATE'))
				->configureNullable(),
			(new \Bitrix\Main\Entity\TextField('RESULTS'))
				->configureNullable()
				->configureLong(),
		];
	}

	public static function getCollectionClass()
	{
		return SiteChecks::class;
	}

	public static function getObjectClass()
	{
		return SiteCheck::class;
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

class SiteChecks extends EO_SiteCheck_Collection
{
}

class SiteCheck extends EO_SiteCheck
{
}
