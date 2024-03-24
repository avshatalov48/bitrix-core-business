<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Query\Query;

/**
 * Class VirusTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Virus_Query query()
 * @method static EO_Virus_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Virus_Result getById($id)
 * @method static EO_Virus_Result getList(array $parameters = [])
 * @method static EO_Virus_Entity getEntity()
 * @method static \Bitrix\Security\Virus createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\Viruss createCollection()
 * @method static \Bitrix\Security\Virus wakeUpObject($row)
 * @method static \Bitrix\Security\Viruss wakeUpCollection($rows)
 */
class VirusTable extends \Bitrix\Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sec_virus';
	}

	public static function getConnectionName()
	{
		$connectionName = \Bitrix\Main\Session\Handlers\Table\UserSessionTable::CONNECTION_NAME;

		$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
		$isConnectionExists = $pool->getConnection($connectionName) !== null;
		if (!$isConnectionExists)
		{
			$pool->cloneConnection(
				$pool::DEFAULT_CONNECTION_NAME,
				$connectionName
			);
		}

		return $connectionName;
	}

	public static function getMap()
	{
		return [
			(new \Bitrix\Main\Entity\StringField('ID'))
				->configurePrimary()
				->configureSize(32),
			(new \Bitrix\Main\Entity\DatetimeField('TIMESTAMP_X'))
				->configureNullable(),
			(new \Bitrix\Main\Entity\StringField('SITE_ID'))
				->configureSize(2)
				->configureNullable(),
			(new \Bitrix\Main\Entity\EnumField('SENT'))
				->configureValues(['Y', 'N'])
				->configureDefaultValue('N'),
			(new \Bitrix\Main\Entity\TextField('INFO'))
				->configureLong(),
		];
	}

	public static function getCollectionClass()
	{
		return Viruss::class;
	}

	public static function getObjectClass()
	{
		return Virus::class;
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

class Viruss extends EO_Virus_Collection
{
}

class Virus extends EO_Virus
{
}
