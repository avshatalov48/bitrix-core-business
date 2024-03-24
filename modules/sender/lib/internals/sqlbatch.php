<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class SqlBatch
 * @package Bitrix\Sender\Internals
 */
class SqlBatch
{
	private const KEY_FIELDS_SEPARATOR = '#kvA3[56U?OWz16l#';

	/**
	 * Return true if batch fulled.
	 *
	 * @param array $list List.
	 * @param integer $limit Batch limit.
	 * @return array
	 */
	public static function divide(array $list, $limit = 300)
	{
		$length = count($list);
		if ($length < $limit)
		{
			return array($list);
		}

		$result = array();
		$partsCount = ceil($length / $limit);
		for ($index = 0; $index < $partsCount; $index++)
		{
			$result[$index] = array_slice($list, $limit * $index, $limit);
		}

		return $result;
	}

	/**
	 * Get string for sql-IN.
	 *
	 * @param array $values Values.
	 * @return string
	 */
	public static function getInString(array $values)
	{
		$conHelper = Application::getConnection()->getSqlHelper();
		foreach($values as $index => $value)
		{
			$values[$index] = $conHelper->forSql($value);
		}

		return "'" . implode("', '", $values) . "'";
	}

	/**
	 * Update.
	 *
	 * @param string $tableName Table name.
	 * @param array $fields Fields.
	 * @return void
	 */
	public static function update($tableName, array $fields)
	{
		$ids = []; $sets = [];
		foreach ($fields as $item)
		{
			if (!isset($item['ID']) || !$item['ID'])
			{
				continue;
			}

			$id = (int) $item['ID'];
			if ($id <= 0)
			{
				continue;
			}
			$ids[] = $id;
			unset($item['ID']);

			foreach ($item as $key => $value)
			{
				if (!isset($sets[$key]))
				{
					$sets[$key] = [];
				}

				$sets[$key][$id] = $value;
			}
		}

		if (count($ids) <= 0 || count($sets) <= 0)
		{
			return;
		}

		$conHelper = Application::getConnection()->getSqlHelper();
		$ids = implode(',', $ids);
		$stringSets = [];
		foreach ($sets as $key => $values)
		{
			$stringSet = "";
			foreach ($values as $id => $value)
			{
				$value = $conHelper->forSql($value);
				$stringSet .= "\nWHEN ID = $id THEN '$value'";
			}
			$stringSet = "\n$key = CASE $stringSet ELSE $key END";
			$stringSets[] = $stringSet;
		}
		$stringSets = implode(', ', $stringSets) . "\n";


		$sql = "UPDATE $tableName SET $stringSets WHERE ID in ($ids)";
		Application::getConnection()->query($sql);
	}

	/**
	 * Insert.
	 *
	 * @param string $tableName Table name.
	 * @param array $fields Fields.
	 * @param array $onDuplicateUpdateFields Duplicate update fields.
	 * @param array $primaryFields Flat array of column with unique index
	 *
	 * @return void
	 */
	public static function insert(
		string $tableName,
		array $fields,
		array $onDuplicateUpdateFields = [],
		array $primaryFields = []
	): void
	{
		$columnNames = self::getFieldNames($fields);
		if (count($columnNames) == 0)
		{
			return;
		}

		$sqlHelper = Application::getConnection()->getSqlHelper();

		if (!empty($onDuplicateUpdateFields))
		{
			$sqlUpdateFields = [];
			foreach ($onDuplicateUpdateFields as $field)
			{
				if (is_array($field))
				{
					$sqlUpdateFields[$field['NAME']] = $field['VALUE'];
				}
				else
				{
					$sqlUpdateFields[$field] = new SqlExpression(
						'case when ?v is null then ?#.?# else ?v end',
						$field,
						$tableName,
						$field,
						$field,
					);
				}
			}
			$fields = self::getUniqueRowsByPrimaryFields($fields, $primaryFields);

			$sql = self::prepareMergeValues($tableName, $primaryFields, $fields, $sqlUpdateFields);
		}
		else
		{
			$columnNamesString = implode(", ", $columnNames);
			$valuesStrings = [];
			foreach ($fields as $row)
			{
				[$columnNamesString, $valuesString] = $sqlHelper->prepareInsert($tableName, $row);
				$valuesStrings[] = $valuesString;
			}
			$dataListString = implode('),(', $valuesStrings);
			$sql = $sqlHelper->getInsertIgnore($tableName, "($columnNamesString)", " VALUES($dataListString)");
		}

		Application::getConnection()->query($sql);
	}

	private static function getUniqueRowsByPrimaryFields(array $rows, array $primaryFields): array
	{
		$unique = [];

		foreach($rows as $row)
		{
			$primaryValues = array_intersect_key($row, array_flip($primaryFields));
			$key = implode(self::KEY_FIELDS_SEPARATOR, $primaryValues);
			$unique[$key] = $row;
		}

		return array_values($unique);
	}

	private static function getFieldNames(array &$fields)
	{
		foreach ($fields as $items)
		{
			return array_keys($items);
		}

		return array();
	}

	/**
	 * Returns prepared sql string for upsert multiple rows
	 * (temporal replacement for main module method)
	 *
	 * @param string $tableName Table name
	 * @param array $primaryFields Fields that can be conflicting keys (primary, unique keys)
	 * @param array $insertRows Rows to insert [['FIELD_NAME' =>'value',...],...], Attention! use same columns in each row
	 * @param array $updateFields Fields to update, if empty - update all fields, can be only field names, or fieldname => expression or fieldname => value
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function prepareMergeValues(string $tableName, array $primaryFields, array $insertRows, array $updateFields = []): string
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$insertColumns = array_keys($insertRows[array_key_first($insertRows)] ?? []);
		$insertValuesStrings = [];
		foreach ($insertRows as $row)
		{
			[, $rowValues] = $sqlHelper->prepareInsert($tableName, $row);
			$insertValuesStrings[] = $rowValues;
		}

		if (empty($updateFields))
		{
			$notPrimaryFields = array_diff($insertColumns, $primaryFields);
			if (empty($notPrimaryFields))
			{
				trigger_error("Only primary fields to update, use getInsertIgnore() or specify fields", E_USER_WARNING);
			}
			$updateFields = $notPrimaryFields;
		}

		$compatibleUpdateFields = [];

		foreach ($updateFields as $key => $value)
		{
			if (is_numeric($key) && is_string($value))
			{
				$compatibleUpdateFields[$value] = new SqlExpression('?v', $value);
			}
			else
			{
				$compatibleUpdateFields[$key] = $value;
			}
		}

		$insertValueString = 'values (' . implode('),(', $insertValuesStrings) . ')';

		return $sqlHelper->prepareMergeSelect($tableName, $primaryFields, $insertColumns, $insertValueString, $compatibleUpdateFields);
	}

}
