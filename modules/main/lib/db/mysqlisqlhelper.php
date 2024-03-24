<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\DB;

use Bitrix\Main\Type;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\ScalarField;

/**
 * @property MysqliConnection $connection
 */
class MysqliSqlHelper extends SqlHelper
{
	/**
	 * @inheritdoc
	 */
	public function getLeftQuote()
	{
		return '`';
	}

	/**
	 * @inheritdoc
	 */
	public function getRightQuote()
	{
		return '`';
	}

	/**
	 * @inheritdoc
	 */
	public function getAliasLength()
	{
		return 256;
	}

	/**
	 * @inheritdoc
	 */
	public function getQueryDelimiter()
	{
		return ';';
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentDateTimeFunction()
	{
		return "NOW()";
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentDateFunction()
	{
		return "CURDATE()";
	}

	/**
	 * @inheritdoc
	 */
	public function addSecondsToDateTime($seconds, $from = null)
	{
		if ($from === null)
		{
			$from = static::getCurrentDateTimeFunction();
		}

		return 'DATE_ADD('.$from.', INTERVAL '.$seconds.' SECOND)';
	}

	/**
	 * @inheritdoc
	 */
	public function addDaysToDateTime($days, $from = null)
	{
		if ($from === null)
		{
			$from = static::getCurrentDateTimeFunction();
		}

		return 'DATE_ADD('.$from.', INTERVAL '.$days.' DAY)';
	}

	/**
	 * @inheritdoc
	 */
	public function getDatetimeToDateFunction($value)
	{
		return 'DATE('.$value.')';
	}

	/**
	 * @inheritdoc
	 */
	public function formatDate($format, $field = null)
	{
		static $search  = array(
			"YYYY",
			"MMMM",
			"MM",
			"MI",
			"DD",
			"HH",
			"GG",
			"G",
			"SS",
			"TT",
			"T",
			"W",
		);
		static $replace = array(
			"%Y",
			"%M",
			"%m",
			"%i",
			"%d",
			"%H",
			"%h",
			"%l",
			"%s",
			"%p",
			"%p",
			"%w",
		);

		$format = str_replace($search, $replace, $format);

		if (!str_contains($format, '%H'))
		{
			$format = str_replace("H", "%h", $format);
		}

		if (!str_contains($format, '%M'))
		{
			$format = str_replace("M", "%b", $format);
		}

		if($field === null)
		{
			return $format;
		}
		else
		{
			return "DATE_FORMAT(".$field.", '".$format."')";
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getConcatFunction()
	{
		$str = implode(", ", func_get_args());
		if ($str != '')
		{
			$str = "CONCAT(".$str.")";
		}
		return $str;
	}

	/**
	 * @inheritdoc
	 */
	public function getIsNullFunction($expression, $result)
	{
		return "IFNULL(".$expression.", ".$result.")";
	}

	/**
	 * @inheritdoc
	 */
	public function getLengthFunction($field)
	{
		return "LENGTH(".$field.")";
	}

	/**
	 * @inheritdoc
	 */
	public function getCharToDateFunction($value)
	{
		return "'".$value."'";
	}

	/**
	 * @inheritdoc
	 */
	public function getDateToCharFunction($fieldName)
	{
		return $fieldName;
	}

	/**
	 * @inheritdoc
	 */
	public function getConverter(ScalarField $field)
	{
		if($field instanceof ORM\Fields\DatetimeField)
		{
			return array($this, "convertFromDbDateTime");
		}
		elseif($field instanceof ORM\Fields\DateField)
		{
			return array($this, "convertFromDbDate");
		}
		else
		{
			return parent::getConverter($field);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function convertFromDbDateTime($value)
	{
		if($value !== null && $value != '0000-00-00 00:00:00')
		{
			return new Type\DateTime($value, "Y-m-d H:i:s");
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function convertFromDbDate($value)
	{
		if($value !== null && $value != '0000-00-00')
		{
			return new Type\Date($value, "Y-m-d");
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function castToChar($fieldName)
	{
		return 'CAST('.$fieldName.' AS char)';
	}

	/**
	 * @inheritdoc
	 */
	public function softCastTextToChar($fieldName)
	{
		return $fieldName;
	}

	/**
	 * @inheritdoc
	 */
	public function getColumnTypeByField(ScalarField $field)
	{
		if ($field instanceof ORM\Fields\IntegerField)
		{
			switch ($field->getSize())
			{
				case 2:
					return 'smallint';
				case 4:
					return 'int';
				case 8:
					return 'bigint';
			}
			return 'int';
		}
		elseif ($field instanceof ORM\Fields\DecimalField)
		{
			$defaultPrecision = 18;
			$defaultScale = 2;

			$precision = $field->getPrecision() > 0 ? $field->getPrecision() : $defaultPrecision;
			$scale = $field->getScale() > 0 ? $field->getScale() : $defaultScale;

			if ($scale >= $precision)
			{
				$precision = $defaultPrecision;
				$scale = $defaultScale;
			}

			return "decimal($precision, $scale)";
		}
		elseif ($field instanceof ORM\Fields\FloatField)
		{
			return 'double';
		}
		elseif ($field instanceof ORM\Fields\DatetimeField)
		{
			return 'datetime';
		}
		elseif ($field instanceof ORM\Fields\DateField)
		{
			return 'date';
		}
		elseif ($field instanceof ORM\Fields\TextField)
		{
			return $field->isLong() ? 'longtext' : 'text';
		}
		elseif ($field instanceof ORM\Fields\BooleanField)
		{
			$values = $field->getValues();

			if (preg_match('/^[0-9]+$/', $values[0]) && preg_match('/^[0-9]+$/', $values[1]))
			{
				return 'int';
			}
			else
			{
				return 'varchar('.max(mb_strlen($values[0]), mb_strlen($values[1])).')';
			}
		}
		elseif ($field instanceof ORM\Fields\EnumField)
		{
			return 'varchar('.max(array_map('strlen', $field->getValues())).')';
		}
		else
		{
			// string by default
			$defaultLength = false;
			foreach ($field->getValidators() as $validator)
			{
				if ($validator instanceof ORM\Fields\Validators\LengthValidator)
				{
					if ($defaultLength === false || $defaultLength > $validator->getMax())
					{
						$defaultLength = $validator->getMax();
					}
				}
			}
			return 'varchar('.($defaultLength > 0? $defaultLength: 255).')';
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getTopSql($sql, $limit, $offset = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		if ($offset > 0 && $limit <= 0)
			throw new \Bitrix\Main\ArgumentException("Limit must be set if offset is set");

		if ($limit > 0)
		{
			$sql .= "\nLIMIT ".$offset.", ".$limit."\n";
		}

		return $sql;
	}

	/**
	 * @inheritdoc
	 */
	public function prepareMerge($tableName, array $primaryFields, array $insertFields, array $updateFields)
	{
		$insert = $this->prepareInsert($tableName, $insertFields);
		$update = $this->prepareUpdate($tableName, $updateFields);

		if (
			$insert && $insert[0] != "" && $insert[1] != ""
			&& $update && $update[1] != ""
		)
		{
			$sql = "
				INSERT INTO ".$this->quote($tableName)." (".$insert[0].")
				VALUES (".$insert[1].")
				ON DUPLICATE KEY UPDATE ".$update[0]."
			";
		}
		else
		{
			$sql = "";
		}

		return array(
			$sql
		);
	}

	/**
	 * @inheritdoc
	 */
	public function forSql($value, $maxLength = 0)
	{
		if ($maxLength > 0)
			$value = mb_substr($value, 0, $maxLength);

		$con = $this->connection->getResource();

		return $con->real_escape_string($value);
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldByColumnType($name, $type, array $parameters = null)
	{
		switch($type)
		{
			case MYSQLI_TYPE_TINY:
			case MYSQLI_TYPE_SHORT:
				$field = (new ORM\Fields\IntegerField($name))->configureSize(2);
				break;
			case MYSQLI_TYPE_LONG:
			case MYSQLI_TYPE_INT24:
			case MYSQLI_TYPE_CHAR:
				$field = (new ORM\Fields\IntegerField($name))->configureSize(4);
				break;
			case MYSQLI_TYPE_LONGLONG:
				$field = ((new ORM\Fields\IntegerField($name)))->configureSize(8);
				break;
			case MYSQLI_TYPE_DECIMAL:
			case MYSQLI_TYPE_NEWDECIMAL:
			case MYSQLI_TYPE_FLOAT:
			case MYSQLI_TYPE_DOUBLE:
				$field = new ORM\Fields\FloatField($name);
				break;
			case MYSQLI_TYPE_DATETIME:
			case MYSQLI_TYPE_TIMESTAMP:
				$field = new ORM\Fields\DatetimeField($name);
				break;
			case MYSQLI_TYPE_DATE:
			case MYSQLI_TYPE_NEWDATE:
				$field = new ORM\Fields\DateField($name);
				break;
			default:
				$field = new ORM\Fields\StringField($name);
		}
		//MYSQLI_TYPE_BIT
		//MYSQLI_TYPE_TIME
		//MYSQLI_TYPE_YEAR
		//MYSQLI_TYPE_INTERVAL
		//MYSQLI_TYPE_ENUM
		//MYSQLI_TYPE_SET
		//MYSQLI_TYPE_TINY_BLOB
		//MYSQLI_TYPE_MEDIUM_BLOB
		//MYSQLI_TYPE_LONG_BLOB
		//MYSQLI_TYPE_BLOB
		//MYSQLI_TYPE_VAR_STRING
		//MYSQLI_TYPE_STRING
		//MYSQLI_TYPE_GEOMETRY

		$field->setConnection($this->connection);

		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function getInsertIgnore($tableName, $fields, $sql)
	{
		return 'INSERT IGNORE INTO ' . $tableName . $fields . $sql;
	}

	/**
	 * @inheritdoc
	 */
	public function getRegexpOperator($field, $regexp)
	{
		return $field . ' regexp ' . $regexp;
	}

	/**
	 * @inheritdoc
	 */
	public function values($identifier)
	{
		return 'VALUES(' . $this->quote($identifier) . ')';
	}

	public function getMatchFunction($field, $value)
	{
		return "MATCH (" . $field . ") AGAINST (" . $value . " IN BOOLEAN MODE)";
	}

	public function getMatchAndExpression($values, $prefixSearch = false)
	{
		if ($prefixSearch)
		{
			foreach ($values as $i => $searchTerm)
			{
				$values[$i] = $searchTerm . '*';
			}
		}
		return '+' . implode(' +', $values);
	}

	public function getMatchOrExpression($values, $prefixSearch = false)
	{
		if ($prefixSearch)
		{
			foreach ($values as $i => $searchTerm)
			{
				$values[$i] = $searchTerm . '*';
			}
		}
		return implode(' ', $values);
	}

	/**
	 * @inheritdoc
	 */
	public function prepareMergeMultiple($tableName, array $primaryFields, array $insertRows)
	{
		$result = [];
		$head = '';
		$maxBodySize = 1024*1024; //1 Mb
		$body = [];
		$bodySize = 0;
		foreach ($insertRows as $insertFields)
		{
			$insert = $this->prepareInsert($tableName, $insertFields);
			if (!$head && $insert && $insert[0])
			{
				$head = 'REPLACE INTO ' . $this->quote($tableName) . ' (' . $insert[0] . ') VALUES ';
			}
			if ($insert && $insert[1])
			{
				$values = '(' . $insert[1] . ')';
				$bodySize += mb_strlen($values) + 4;
				$body[] = $values;
				if ($bodySize > $maxBodySize)
				{
					$result[] = $head.implode(', ', $body);
					$body = [];
					$bodySize = 0;
				}
			}
		}
		if ($body)
		{
			$result[] = $head.implode(', ', $body);
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function prepareMergeSelect($tableName, array $primaryFields, array $selectFields, $select, $updateFields)
	{
		$update = [];

		$tableFields = $this->connection->getTableFields($tableName);
		// one registry
		$tableFields = array_change_key_case($tableFields, CASE_UPPER);
		$updateFields = array_change_key_case($updateFields, CASE_UPPER);
		foreach ($updateFields as $columnName => $value)
		{
			if (isset($tableFields[$columnName]))
			{
				$update[] = $this->quote($columnName) . ' = '. $this->convertToDb($value, $tableFields[$columnName]);
			}
			else
			{
				trigger_error("Column `{$columnName}` is not found in the `{$tableName}` table", E_USER_WARNING);
			}
		}

		$sql = 'INSERT INTO ' . $this->quote($tableName) . ' (' . implode(',', array_map([$this, 'quote'], $selectFields)) . ') ';
		$sql .= $select;
		$sql .= ' ON DUPLICATE KEY UPDATE ' . implode(',', $update);

		return $sql;
	}

	/**
	 * @inheritdoc
	 */
	public function prepareDeleteLimit($tableName, array $primaryFields, $where, array $order, $limit)
	{
		$orderColumns = [];
		foreach ($order as $columnName => $sort)
		{
			$orderColumns[] = $this->quote($columnName) . ' ' . $sort;
		}
		$sqlOrder = $orderColumns ? ' ORDER BY ' . implode(', ', $orderColumns) : '';
		return 'DELETE FROM ' . $this->quote($tableName) . ' WHERE ' . $where . $sqlOrder . ' LIMIT ' . intval($limit);
	}

	public function initRowNumber($variableName)
	{
		return 'set @' . $variableName . ' = 0';
	}

	public function getRowNumber($variableName)
	{
		return '@' . $variableName . ':=' . '@' . $variableName . ' + 1';
	}

	/**
	 * @inheritdoc
	 */
	public function prepareCorrelatedUpdate($tableName, $tableAlias, $fields, $from, $where)
	{
		$dml = "UPDATE " . $tableName . ' AS ' . $tableAlias . ",\n";
		$dml .= $from . "\n";

		$set = '';
		foreach ($fields as $fieldName => $fieldValue)
		{
			$set .= ($set ? ',' : '') . $tableAlias . '.' . $fieldName . ' = ' .$fieldValue . "\n";
		}
		$dml .= 'SET ' . $set;
		$dml .= 'WHERE ' . $where . "\n";

		return $dml;
	}

	protected function getOrderByField(string $field, array $values, callable $callback, bool $quote = true): string
	{
		$field = $quote ? $this->quote($field) : $field;
		$values = implode(',', array_map($callback, $values));

		return "FIELD({$field}, {$values})";
	}
}
