<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals;

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
				$stringSet .= "\nWHEN ID = $id THEN \"$value\"";
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
	 * @return void
	 */
	public static function insert($tableName, array $fields, array $onDuplicateUpdateFields = array())
	{
		$columnNames = self::getFieldNames($fields);
		if (count($columnNames) == 0)
		{
			return;
		}

		$columnNamesString = implode(", ", $columnNames);
		$dataListString =  implode('),(', self::getFieldValues($fields));

		$sql = "INSERT IGNORE $tableName($columnNamesString) VALUES($dataListString)";
		if (!empty($onDuplicateUpdateFields))
		{
			$sqlUpdateFields = array();
			foreach ($onDuplicateUpdateFields as $field)
			{
				if (is_array($field))
				{
					$fieldName = $field['NAME'];
					$fieldValue = $field['VALUE'];
				}
				else
				{
					$fieldName = $field;
					$fieldValue = "IFNULL(VALUES($fieldName), $fieldName)";
				}

				if (!in_array($fieldName, $columnNames))
				{
					continue;
				}

				$sqlUpdateFields[] = "$fieldName = $fieldValue";
			}

			if (count($sqlUpdateFields) > 0)
			{
				$sql .= " ON DUPLICATE KEY UPDATE " . implode(", ", $sqlUpdateFields);
			}
		}

		Application::getConnection()->query($sql);
	}

	private static function getFieldNames(array &$fields)
	{
		foreach ($fields as $items)
		{
			return array_keys($items);
		}

		return array();
	}

	private static function getFieldValues(array &$fields)
	{
		$dataList = array();
		$conHelper = Application::getConnection()->getSqlHelper();

		foreach ($fields as $items)
		{
			$values = array();
			foreach ($items as $key => $value)
			{
				switch (gettype($value))
				{
					case 'array':
						$value = $value['VALUE'];
						break;

					case 'integer':
						break;

					case 'object':
						if ($value instanceof DateTime)
						{
							$value = $conHelper->convertToDbDateTime($value);
						}
						break;

					case 'NULL':
						$value = 'NULL';
						break;

					case 'string':
					default:
						$value = (string) $value;
						$value = $conHelper->forSql($value);
						$value = '"' . $value . '"';
						break;
				}
				$values[] = $value;
			}

			$dataList[] = implode(", ", $values);
		}

		return $dataList;
	}
}