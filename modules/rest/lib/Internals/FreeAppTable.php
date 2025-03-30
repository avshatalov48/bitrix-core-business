<?php

namespace Bitrix\Rest\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Rest\AppTable;

/**
 * Class FreeAppTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FreeApp_Query query()
 * @method static EO_FreeApp_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FreeApp_Result getById($id)
 * @method static EO_FreeApp_Result getList(array $parameters = [])
 * @method static EO_FreeApp_Entity getEntity()
 * @method static \Bitrix\Rest\Internals\EO_FreeApp createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\Internals\EO_FreeApp_Collection createCollection()
 * @method static \Bitrix\Rest\Internals\EO_FreeApp wakeUpObject($row)
 * @method static \Bitrix\Rest\Internals\EO_FreeApp_Collection wakeUpCollection($rows)
 */
class FreeAppTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_rest_free_app';
	}

	public static function getMap()
	{
		return [
			new StringField('APP_CODE', [
				'primary' => true,
			]),
			(new Reference(
				'APP',
				AppTable::class,
				Join::on('this.APP_CODE', 'ref.CODE')
			)),
		];
	}

	final public static function updateFreeAppTable(array $freeAppList): void
	{
		$connection = Application::getConnection();
		$connection->startTransaction();

		try
		{
			$connection->truncateTable(FreeAppTable::getTableName());
			$helper = $connection->getSqlHelper();

			if (!empty($freeAppList))
			{
				$values = implode("'),('", $freeAppList);
				$query = $helper->getInsertIgnore(FreeAppTable::getTableName(),  '(APP_CODE)', "VALUES ('$values')");
				$connection->query($query);
			}
		}
		catch (SqlQueryException $e)
		{
			$connection->rollbackTransaction();
			throw $e;
		}

		$connection->commitTransaction();
		self::cleanCache();
	}
}
