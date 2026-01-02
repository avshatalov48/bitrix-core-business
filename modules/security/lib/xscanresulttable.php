<?php

namespace Bitrix\Security;

use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Query\Query;

/**
 * Class XScanResultTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_XScanResult_Query query()
 * @method static EO_XScanResult_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_XScanResult_Result getById($id)
 * @method static EO_XScanResult_Result getList(array $parameters = [])
 * @method static EO_XScanResult_Entity getEntity()
 * @method static \Bitrix\Security\XScanResult createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\XScanResults createCollection()
 * @method static \Bitrix\Security\XScanResult wakeUpObject($row)
 * @method static \Bitrix\Security\XScanResults wakeUpCollection($rows)
 */
class XScanResultTable extends \Bitrix\Main\Entity\DataManager
{
	private const trace = [
		'/bitrix/modules/security/lib/controller/xscan.php',
		'',
		'/bitrix/modules/main/lib/engine/autowire/binder.php',
		'/bitrix/modules/main/lib/engine/action.php',
		'/bitrix/modules/main/lib/engine/controller.php',
		'/bitrix/modules/main/lib/httpapplication.php',
		'/bitrix/modules/main/lib/httpapplication.php',
		'/bitrix/modules/main/services/ajax.php',
		'/bitrix/services/main/ajax.php',
	];

	public static function getTableName()
	{
		return 'b_sec_xscan_results';
	}

	public static function getMap()
	{
		return [
			new \Bitrix\Main\Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new \Bitrix\Main\Entity\EnumField('TYPE', [
				'values' => ['file', 'agent', 'event', 'tmpl', 'trigg'],
				'default_value' => 'file',
			]),
			new \Bitrix\Main\Entity\StringField('SRC'),
			new \Bitrix\Main\Entity\StringField('MESSAGE'),
			new \Bitrix\Main\Entity\FloatField('SCORE'),
			new \Bitrix\Main\Entity\DatetimeField('CTIME'),
			new \Bitrix\Main\Entity\DatetimeField('MTIME'),
			new \Bitrix\Main\Entity\StringField('TAGS'),
		];
	}

	public static function getCollectionClass()
	{
		return XScanResults::class;
	}

	public static function getObjectClass()
	{
		return XScanResult::class;
	}

	public static function delete($primary)
	{
		$e = new \Exception();
		$t = $e->getTrace();

		if (count($t) !== count(XScanResultTable::trace))
		{
			return new DeleteResult();
		}

		foreach(XScanResultTable::trace as $key => $val)
		{
			if ($val && (!isset($t[$key]['file']) || !str_ends_with($t[$key]['file'], $val)))
			{
				return new DeleteResult();
			}
		}

		return parent::delete($primary);
	}

	public static function deleteList(array $filter)
	{
		$e = new \Exception();
		$t = $e->getTrace();

		if (count($t) !== count(XScanResultTable::trace))
		{
			return new DeleteResult();
		}

		foreach(XScanResultTable::trace as $key => $val)
		{
			if ($val && (!isset($t[$key]['file']) || !str_ends_with($t[$key]['file'], $val)))
			{
				return new DeleteResult();
			}
		}

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

class XScanResults extends EO_XScanResult_Collection
{
}

class XScanResult extends EO_XScanResult
{
}
