<?php

namespace Bitrix\Main\ORM\Data\AddStrategy;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Data\AddStrategy\Internal\Helper;
use Bitrix\Main\ORM\Entity;

final class InsertIgnore implements AddStrategy
{
	private readonly array $uniqueFieldNames;

	public function __construct(
		private readonly Entity $entity,
		?array $uniqueFieldNames = null,
	)
	{
		$this->uniqueFieldNames = $uniqueFieldNames ?? $this->entity->getPrimaryArray();

		if (!Helper::isEntitySupportedByUniqueValidatingStrategies($this->entity, $this->uniqueFieldNames))
		{
			throw new NotSupportedException('Entity is not supported by the ' . self::class);
		}
	}

	public function add(array $dbFields): AddedData
	{
		$insertedId = $this->insertIgnore($dbFields);

		$isDBChanged = $this->isDBChanged();

		if ($insertedId !== 0)
		{
			return new AddedData($insertedId, $isDBChanged);
		}
		else
		{
			return new AddedData(
				Helper::guessInsertedId($this->entity, $dbFields, $this->uniqueFieldNames),
				$isDBChanged,
			);
		}
	}

	public function addMulti(array $multiDbFields): AddedMultiData
	{
		$this->insertIgnoreMulti($multiDbFields);

		return new AddedMultiData($this->isDBChanged());
	}

	private function insertIgnore(array $insertFields): int
	{
		$tableName = $this->entity->getDBTableName();
		$sqlHelper = $this->entity->getConnection()->getSqlHelper();

		[$sqlFieldNames, $sqlValues] = $sqlHelper->prepareInsert(
			$tableName,
			$insertFields,
		);

		$sql = $sqlHelper->getInsertIgnore($tableName, " ({$sqlFieldNames})", " VALUES ({$sqlValues})");

		return Helper::executeAndGetInsertedId($this->entity, $sql);
	}

	private function insertIgnoreMulti(array $multiInsertFields): void
	{
		$connection = $this->entity->getConnection();
		$tableName = $this->entity->getDBTableName();
		$sqlHelper = $connection->getSqlHelper();

		$uniqueColumns = [];
		$inserts = [];

		// prepare data
		foreach ($multiInsertFields as $insertFields)
		{
			$insert = $sqlHelper->prepareInsert($tableName, $insertFields, true);
			$inserts[] = $insert;

			// and get unique column names
			foreach ($insert[0] as $column)
			{
				$uniqueColumns[$column] = true;
			}
		}

		// prepare sql
		$sqlValues = [];

		foreach ($inserts as $insert)
		{
			$columns = array_flip($insert[0]);
			$values = $insert[1];

			$finalValues = [];

			foreach (array_keys($uniqueColumns) as $column)
			{
				if (array_key_exists($column, $columns))
				{
					// set real value
					$finalValues[] = $values[$columns[$column]];
				}
				else
				{
					// set default
					$finalValues[] = 'DEFAULT';
				}
			}

			$sqlValues[] = '(' . join(', ', $finalValues) . ')';
		}

		$sql = $sqlHelper->getInsertIgnore(
			$tableName,
			'(' . join(', ', array_keys($uniqueColumns)) . ')',
			'VALUES ' . join(', ', $sqlValues),
		);

		$connection->queryExecute($sql);
	}

	private function isDBChanged(): bool
	{
		// on mysql count will be 0 if no changes, 1 if inserted
		// pgsql will always return 1 on insert-ignore
		return $this->entity->getConnection()->getAffectedRowsCount() > 0;
	}
}
