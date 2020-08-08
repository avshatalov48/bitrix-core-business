<?php

namespace Bitrix\Translate\Index\Internals;

use Bitrix\Main;


trait BulkOperation
{
	/** @var array */
	private static $tableFields;

	/**
	 * Multiple inserts rows.
	 *
	 * @param array $rows Data to add.
	 * @param string|string[] $primary Primary key field name.
	 *
	 * @return void
	 *
	 * @throws Main\ArgumentTypeException
	 * @throws Main\DB\SqlQueryException
	 */
	public static function bulkAdd(array $rows, $primary = null)
	{
		if (empty($rows))
		{
			return;
		}
		$tableName = static::getTableName();
		$connection = Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		if (empty(static::$tableFields))
		{
			static::$tableFields = $connection->getTableFields($tableName);
		}

		$columns0 = array_keys($rows[0]);
		$columns = [];
		foreach ($columns0 as $c)
		{
			$columns[$c] = $sqlHelper->quote(mb_strtoupper($c));
		}

		$sqlValues = array();
		foreach ($rows as $data)
		{
			foreach ($data as $columnName => $value)
			{
				$data[$columnName] = $sqlHelper->convertToDb($value, static::$tableFields[$columnName]);
			}
			$sqlValues[] = '('.implode(', ', $data).')';
		}
		unset($data);

		$sql = "INSERT INTO {$tableName} (".implode(', ', $columns).") VALUES ".implode(', ', $sqlValues);

		$checkPrimary = false;
		if (!empty($primary))
		{
			if (!is_array($primary))
			{
				$primary = array($primary);
			}
			if (count(array_intersect($primary, $columns0)) > 0)
			{
				$checkPrimary = true;
			}
		}
		if ($checkPrimary)
		{
			$sqlUpdate = array();
			foreach (array_diff($columns0, $primary) as $columnName)
			{
				$sqlUpdate[] = "{$columns[$columnName]} = VALUES({$columns[$columnName]})";
			}
			$sql .= " ON DUPLICATE KEY UPDATE ".implode(', ', $sqlUpdate);
		}

		$connection->queryExecute($sql);
	}

	/**
	 * Updates rows by filter.
	 *
	 * @param array $fields Values for update.
	 * @param array $filter Filter what to update.
	 *
	 * @return void
	 * @throws Main\DB\SqlQueryException
	 */
	public static function bulkUpdate(array $fields, array $filter = [])
	{
		if (empty($fields))
		{
			return;
		}
		$tableName = static::getTableName();
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$update = $helper->prepareUpdate($tableName, $fields);
		$valuesSql = $update[0];

		if (!empty($valuesSql))
		{
			if (!empty($filter))
			{
				$hasSubQuery = false;
				foreach ($filter as $field => $value)
				{
					if (mb_strpos($field, '.') !== false)
					{
						$hasSubQuery = true;
						break;
					}
				}
				if ($hasSubQuery)
				{
					$whereSql = (static::query())
						->setSelect(['ID' => 'ID'])
						->setFilter($filter)
						->getQuery();

					$querySql = "UPDATE {$tableName} target INNER JOIN ({$whereSql}) source ON target.ID = source.ID SET {$valuesSql}  ";
				}
				else
				{
					$whereSql = Main\ORM\Query\Query::buildFilterSql(static::getEntity(), $filter);
					$querySql = "UPDATE {$tableName} SET {$valuesSql} WHERE {$whereSql}";
				}
			}
			else
			{
				$querySql = "UPDATE {$tableName} SET {$valuesSql}";
			}

			$connection->queryExecute($querySql);
		}
	}

	/**
	 * Deletes rows by filter.
	 *
	 * @param array $filter Filter looks like filter in getList.
	 *
	 * @return void
	 * @throws Main\DB\SqlQueryException
	 */
	public static function bulkDelete(array $filter = [])
	{
		$tableName = static::getTableName();
		$connection = Main\Application::getConnection();

		if (!empty($filter))
		{
			$hasSubQuery = false;
			foreach ($filter as $field => $value)
			{
				if (mb_strpos($field, '.') !== false)
				{
					$hasSubQuery = true;
					break;
				}
			}
			if ($hasSubQuery)
			{
				$whereSql = (static::query())
					->setSelect(['ID' => 'ID'])
					->setFilter($filter)
					->getQuery();

				$querySql = "DELETE target FROM {$tableName} target INNER JOIN ({$whereSql}) source ON target.ID = source.ID";
			}
			else
			{
				$whereSql = Main\ORM\Query\Query::buildFilterSql(static::getEntity(), $filter);
				$querySql = "DELETE FROM {$tableName} WHERE {$whereSql}";
			}
		}
		else
		{
			$querySql = "TRUNCATE TABLE {$tableName}";
		}

		$connection->queryExecute($querySql);
	}


	/**
	 * Get filter parameters as SQL code.
	 *
	 * @param array $filterFields Gets filter parameters.
	 * @param array $filterAlias Aliases for the filter fields.
	 *
	 * @return string
	 */
	private static function prepareWhere(array $filterFields, array $filterAlias = array())
	{
		$sqlHelper = Main\Application::getConnection()->getSqlHelper();

		$where = array();
		$logic = 'AND';
		foreach ($filterFields as $key => $val)
		{
			if ($key === 'LOGIC')
			{
				$logic = $val;
				continue;
			}
			$operator = '=';
			if (!is_numeric($key))
			{
				if (preg_match("/^([=<>!@%]+)([^=<>!@%]+)$/", $key, $parts))
				{
					list(, $operator, $key) = $parts;
				}
				if (is_array($val) && !isset($val['LOGIC']))
				{
					if ($operator === '=')
					{
						$operator = '@';
					}
					elseif ($operator === '!')
					{
						$operator = '!@';
					}
				}
				if (isset($filterAlias[$key]))
				{
					$key = $filterAlias[$key];
				}
			}
			switch ($operator)
			{
				case '!':
					$where[] = "$key != '". $sqlHelper->forSql($val). "'";
					break;

				case '%':
					$where[] = "$key LIKE '%". $sqlHelper->forSql($val). "%'";
					break;

				case '!%':
					$where[] = "$key NOT LIKE '%". $sqlHelper->forSql($val). "%'";
					break;

				case '@':
				{
					if (is_array($val) && count($val) > 0)
					{
						$val = array_map(array($sqlHelper, 'forSql'), $val);
						$where[] = "$key IN('".implode("', '", $val)."')";
					}
					elseif (is_string($val) && $val <> '')
					{
						$where[] = "$key IN(".$val.')';
					}
					break;
				}

				case '!@':
				{
					if (is_array($val) && count($val) > 0)
					{
						$val = array_map(array($sqlHelper, 'forSql'), $val);
						$where[] = "$key NOT IN('".implode("', '", $val)."')";
					}
					elseif (is_string($val) && $val <> '')
					{
						$where[] = "$key NOT IN(".$val.')';
					}
					break;
				}

				default:
				{
					if (is_array($val))// && isset($val['LOGIC']))// && $val['LOGIC'] === 'OR')// OR condition
					{
						$subLogic = 'AND';
						if (isset($val['LOGIC']) && $val['LOGIC'] === 'OR')
						{
							$subLogic = 'OR';
							unset($val['LOGIC']);
						}

						//$val = array_pop($val);

						$condition = array();
						foreach ($val as $k => $v)
						{
							$subOperator = '=';
							if (preg_match("/^([=<>!@%]+)([^=<>!@%]+)$/", $k, $parts))
							{
								list(, $subOperator, $k) = $parts;
							}
							if (isset($filterAlias[$k]))
							{
								$k = $filterAlias[$k];
							}
							switch ($subOperator)
							{
								case '!':
									$condition[] = "$k != '".$sqlHelper->forSql($v)."'";
									break;
								case '%':
									$condition[] = "$k LIKE '%".$sqlHelper->forSql($v)."%'";
									break;
								case '!%':
									$condition[] = "$k NOT LIKE '%".$sqlHelper->forSql($v)."%'";
									break;
								default:
									$condition[] = "$k $subOperator '".$sqlHelper->forSql($v)."'";
							}
						}
						$where[] = '('.implode(" $subLogic ", $condition).')';
					}
					else
					{
						$where[] = "$key $operator '".$sqlHelper->forSql($val)."'";
					}
					break;
				}
			}
		}

		$whereSql = '';
		if (count($where))
		{
			$whereSql = ' AND '. implode(" $logic ", $where);
		}

		return $whereSql;
	}
}
