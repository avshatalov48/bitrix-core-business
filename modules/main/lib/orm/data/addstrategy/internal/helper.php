<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Internal;

use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Query\Query;

/**
 * @internal
 */
final class Helper
{
	private function __construct()
	{
	}

	public static function isEntitySupportedByUniqueValidatingStrategies(Entity $entity, array $uniqueFieldNames): bool
	{
		return (
			self::isUniqueIdenticalToPrimary($entity, $uniqueFieldNames)
			|| (
				self::isEntityHasAutoIncrementID($entity) && !self::isUniqueContainsPrimary($entity, $uniqueFieldNames)
			)
		);
	}

	private static function isUniqueIdenticalToPrimary(Entity $entity, array $uniqueFieldNames): bool
	{
		$primary = $entity->getPrimaryArray();
		sort($primary, SORT_STRING);
		sort($uniqueFieldNames, SORT_STRING);

		return $primary === $uniqueFieldNames;
	}

	private static function isEntityHasAutoIncrementID(Entity $entity): bool
	{
		if (empty($entity->getAutoIncrement()))
		{
			return false;
		}

		return $entity->getPrimaryArray() === [$entity->getAutoIncrement()];
	}

	private static function isUniqueContainsPrimary(Entity $entity, array $uniqueFieldNames): bool
	{
		$intersect = array_intersect($uniqueFieldNames, $entity->getPrimaryArray());

		return !empty($intersect);
	}

	public static function executeAndGetInsertedId(Entity $entity, string $sql): int
	{
		$connection = $entity->getConnection();

		if ($connection->getType() === 'pgsql' && !empty($entity->getAutoIncrement()))
		{
			$autoIncrementColumn = $entity->getField($entity->getAutoIncrement())->getColumnName();

			$sql .= " RETURNING {$autoIncrementColumn}";

			$row = $connection->query($sql)->fetch();
			if (!is_array($row))
			{
				return 0;
			}

			return (int)reset($row);
		}

		$connection->queryExecute($sql);

		if ($connection->getType() === 'pgsql')
		{
			// If table hasn't autoincrement key, PostgreSQL returns wrong last inserted id - from previous insert query in a table with autoincrement
			return 0;
		}

		return $connection->getInsertedId();
	}

	public static function guessInsertedId(
		Entity $entity,
		array $dbFields,
		array $uniqueFieldNames,
	): int
	{
		// yes, there is a case of $isGuessedPrimary in \Bitrix\Main\ORM\Data\DataManager::sysAddInternal.
		// but we would rather not make a useless db query than guess in this case.
		// if you use a unique-validating strategy, have an autoincrement column, and didn't specify it,
		// you are doing something really wrong.
		if (empty($entity->getAutoIncrement()))
		{
			return 0;
		}

		// here we have data after \Bitrix\Main\ORM\Fields\Field::modifyValueBeforeSave,
		// but with field names instead of columns
		$modifiedData = self::replaceColumnNamesWithFieldNames($entity, $dbFields);

		$row = self::fetchProbablyOriginalRow($entity, $modifiedData, $uniqueFieldNames);
		if (!$row)
		{
			return 0;
		}

		return self::extractInsertedId($row, $entity->getAutoIncrement());
	}

	private static function replaceColumnNamesWithFieldNames(Entity $entity, array $dbFields): array
	{
		$columnToFieldMap = [];
		foreach ($entity->getScalarFields() as $field)
		{
			$columnToFieldMap[$field->getColumnName()] = $field->getName();
		}

		$result = [];
		foreach ($dbFields as $column => $value)
		{
			$fieldName = $columnToFieldMap[$column] ?? $column;

			$result[$fieldName] = $value;
		}

		return $result;
	}

	private static function fetchProbablyOriginalRow(
		Entity $entity,
		array $modifiedData,
		array $uniqueFieldNames,
	): array|false
	{
		$query = (new Query($entity))
			->setSelect([$entity->getAutoIncrement()])
			->setLimit(1)
		;

		foreach ($modifiedData as $field => $value)
		{
			if (in_array($field, $uniqueFieldNames, true))
			{
				$query->where($field, $value);
			}
		}

		return $query->fetch();
	}

	private static function extractInsertedId(array $row, string $primaryFieldName): int
	{
		$value = $row[$primaryFieldName] ?? null;
		if (is_numeric($value))
		{
			return (int)$value;
		}

		return 0;
	}
}
