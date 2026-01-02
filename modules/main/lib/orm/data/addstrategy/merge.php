<?php

namespace Bitrix\Main\ORM\Data\AddStrategy;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Data\AddStrategy\Internal\Helper;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ScalarField;

final class Merge implements AddStrategy
{
	private readonly array $uniqueFieldNames;
	private readonly array $uniqueDbColumns;

	public function __construct(
		private readonly Entity $entity,
		?array $uniqueFieldNames = null,
	)
	{
		$uniqueFieldNames ??= $this->entity->getPrimaryArray();

		if (!Helper::isEntitySupportedByUniqueValidatingStrategies($this->entity, $uniqueFieldNames))
		{
			throw new NotSupportedException('Entity is not supported by the ' . self::class);
		}

		sort($uniqueFieldNames, SORT_STRING);
		$this->uniqueFieldNames = $uniqueFieldNames;

		$uniqueDbColumns = $this->replaceFieldNames($this->uniqueFieldNames);
		sort($uniqueDbColumns, SORT_STRING);
		$this->uniqueDbColumns = $uniqueDbColumns;
	}

	public function add(array $dbFields): AddedData
	{
		$updateDbFields = $this->getUpdateDbFields($dbFields, $this->uniqueDbColumns);

		$insertedId = $this->merge(
			$dbFields,
			$updateDbFields,
			$this->uniqueDbColumns,
		);

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
		foreach ($multiDbFields as &$dbFields)
		{
			// prepareMergeValues looks on key *order* in fields, not key *name*
			ksort($dbFields, SORT_STRING);
		}

		$firstDbFields = reset($multiDbFields);
		$updateDbColumns = array_keys(
			$this->getUpdateDbFields($firstDbFields, $this->uniqueDbColumns),
		);

		$connection = $this->entity->getConnection();

		// never force separate queries, since events are always ignored
		$sql = $connection->getSqlHelper()->prepareMergeValues(
			$this->entity->getDBTableName(),
			$this->uniqueDbColumns,
			$multiDbFields,
			$updateDbColumns,
		);

		$connection->queryExecute($sql);

		return new AddedMultiData($this->isDBChanged());
	}

	private function merge(array $insertFields, array $updateFields, array $uniqueFieldNames): int
	{
		$sqlHelper = $this->entity->getConnection()->getSqlHelper();

		$sql = $sqlHelper->prepareMerge(
			$this->entity->getDBTableName(),
			$uniqueFieldNames,
			$insertFields,
			$updateFields
		);

		return Helper::executeAndGetInsertedId($this->entity, current($sql));
	}

	private function replaceFieldNames(array $fieldNames): array
	{
		$result = [];
		foreach ($fieldNames as $fieldName)
		{
			$field = $this->entity->getField($fieldName);

			$result[] = $field instanceof ScalarField ? $field->getColumnName() : $fieldName;
		}

		return $result;
	}

	/**
	 * @param Array<string, mixed> $dbFields
	 *
	 * @return Array<string, mixed>
	 */
	private function getUpdateDbFields(array $dbFields, array $uniqueColumns): array
	{
		$update = [];
		foreach ($dbFields as $column => $value)
		{
			if (!in_array($column, $uniqueColumns, true))
			{
				$update[$column] = $value;
			}
		}

		return $update;
	}

	private function isDBChanged(): bool
	{
		// on mysql count will be 0 if no changes, 1 if inserted, 2 if a row was updated
		// pgsql will always return 1 on merge
		return $this->entity->getConnection()->getAffectedRowsCount() > 0;
	}
}
