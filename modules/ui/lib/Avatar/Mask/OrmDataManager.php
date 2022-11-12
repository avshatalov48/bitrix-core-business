<?php
namespace Bitrix\UI\Avatar\Mask;

use Bitrix\Main;

abstract class OrmDataManager extends Main\ORM\Data\DataManager
{
	public static function deleteByFilter(array $filter): Main\Orm\Data\DeleteResult
	{
		$entity = static::getEntity();
		$sqlTableName = static::getTableName();
		$sqlHelper = $entity->getConnection()->getSqlHelper();

		$where = Main\ORM\Query\Query::buildFilterSql($entity, $filter);
		$result = new Main\Orm\Data\DeleteResult();
		if ($where !== '')
		{
			$sql = "DELETE FROM {$sqlHelper->quote($sqlTableName)} WHERE " . $where;
			$entity->getConnection()->queryExecute($sql);
			$result->setData(['rowsCount' => $entity->getConnection()->getAffectedRowsCount()]);
		}
		return $result;
	}
}