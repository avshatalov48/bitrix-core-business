<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Filter\Helper;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;

trait IndexTableTrait
{
	use MultiplyInsertTrait;

	private static bool $isAlreadyPlanned = false;

	public static function indexInBackground(): void
	{
		if (self::$isAlreadyPlanned)
		{
			return;
		}

		self::$isAlreadyPlanned = true;

		Application::getInstance()->addBackgroundJob(fn () => self::runIndex());
	}

	public static function prepareSearchString(string $searchString): string
	{
		$searchString = Content::prepareStringToken($searchString);
		$searchString = Helper::matchAgainstWildcard($searchString);

		return $searchString;
	}

	public static function deleteByFilter(array $filter): void
	{
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$indexTable = $sqlHelper->quote(static::getTableName());
		$baseTable = $sqlHelper->quote(static::getBaseDataClass()::getTableName());
		$indexTablePrimary = $sqlHelper->quote(static::getEntity()->getPrimary());
		$baseTablePrimary = $sqlHelper->quote(static::getBaseDataClass()::getEntity()->getPrimary());
		$whereStatement = Query::buildFilterSql(static::getBaseDataClass()::getEntity(), $filter);

		if ($connection->getType() == 'mysql')
		{
			$sql = "
				DELETE {$indexTable} 
				FROM {$indexTable} 
					INNER JOIN {$baseTable} 
						ON {$indexTable}.{$indexTablePrimary} = {$baseTable}.{$baseTablePrimary}
				WHERE {$whereStatement};
			";
		}
		elseif ($connection->getType() == 'pgsql')
		{
			$sql = "
				DELETE FROM {$indexTable}
				USING {$baseTable}
				WHERE
					{$indexTable}.{$indexTablePrimary} = {$baseTable}.{$baseTablePrimary}
					AND {$whereStatement}
			";
		}

		$connection->queryExecute($sql);
	}

	public static function updateIndexStatus(array $ids, bool $status = true): void
	{
		if (empty($ids))
		{
			return;
		}

		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$baseTable = $sqlHelper->quote(static::getBaseDataClass()::getTableName());
		$baseTablePrimary = $sqlHelper->quote(static::getBaseDataClass()::getEntity()->getPrimary());
		$implodeIds = implode(',', $ids);
		$statusString = $status ? 'Y' : 'N';

		$sql = "
			UPDATE {$baseTable}
			SET IS_INDEXED = '{$statusString}'
			WHERE {$baseTablePrimary} IN ({$implodeIds});
		";

		$connection->queryExecute($sql);
	}

	private static function runIndex(): void
	{
		self::index();
		self::$isAlreadyPlanned = false;
	}

	/**
	 * @return string|DataManager
	 */
	abstract protected static function getBaseDataClass(): string;

	abstract public static function index(): void;
}