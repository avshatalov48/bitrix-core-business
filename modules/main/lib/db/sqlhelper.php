<?php

namespace Bitrix\Main\DB;

use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\ORM;

abstract class SqlHelper
{
	/** @var Connection $connection */
	protected $connection;

	protected $idCache;

	/**
	 * @param Connection $connection Database connection.
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * Returns an identificator escaping left character.
	 *
	 * @return string
	 */
	public function getLeftQuote()
	{
		return '';
	}

	/**
	 * Returns an identificator escaping right character.
	 *
	 * @return string
	 */
	public function getRightQuote()
	{
		return '';
	}

	/**
	 * Returns maximum length of an alias in a select statement
	 *
	 * @return integer
	 */
	abstract public function getAliasLength();

	/**
	 * Returns quoted identifier.
	 * <p>
	 * For example Title become :
	 * - `Title` for MySQL
	 * - "TITLE" for Oracle
	 * - [Title] for Ms SQL
	 * <p>
	 * @param string $identifier Table or Column name.
	 *
	 * @return string
	 * @see SqlHelper::getLeftQuote
	 * @see SqlHelper::getRightQuote
	 */
	public function quote($identifier)
	{
		if (empty($this->idCache[$identifier]))
		{
			// security unshielding
			$quotedIdentifier = str_replace([$this->getLeftQuote(), $this->getRightQuote()], '', $identifier);

			// shield [[database.]tablename.]columnname
			if (str_contains($quotedIdentifier, '.'))
			{
				$quotedIdentifier = str_replace('.', $this->getRightQuote() . '.' . $this->getLeftQuote(), $quotedIdentifier);
			}

			// shield general borders
			$this->idCache[$identifier] = $this->getLeftQuote() . $quotedIdentifier . $this->getRightQuote();
		}

		return $this->idCache[$identifier];
	}

	/**
	 * Returns database specific query delimiter for batch processing.
	 *
	 * @return string
	 */
	abstract public function getQueryDelimiter();

	/**
	 * Escapes special characters in a string for use in an SQL statement.
	 *
	 * @param string $value Value to be escaped.
	 * @param integer $maxLength Limits string length if set.
	 *
	 * @return string
	 */
	abstract public function forSql($value, $maxLength = 0);

	/**
	 * Returns binary safe data representation.
	 *
	 * @param string $value Value to be encoded.
	 *
	 * @return string
	 */
	public function convertToDbBinary($value)
	{
		return "'" . $this->forSql($value) . "'";
	}

	/**
	 * Returns function for getting current time.
	 *
	 * @return string
	 */
	abstract public function getCurrentDateTimeFunction();

	/**
	 * Returns function for getting current date without time part.
	 *
	 * @return string
	 */
	abstract public function getCurrentDateFunction();

	/**
	 * Returns function for adding seconds time interval to $from.
	 * <p>
	 * If $from is null or omitted, then current time is used.
	 * <p>
	 * $seconds and $from parameters are SQL unsafe.
	 *
	 * @param integer $seconds How many seconds to add.
	 * @param integer $from Datetime database field of expression.
	 *
	 * @return string
	 */
	abstract public function addSecondsToDateTime($seconds, $from = null);

	/**
	 * Returns function for adding days time interval to $from.
	 * <p>
	 * If $from is null or omitted, then current time is used.
	 * <p>
	 * $days and $from parameters are SQL unsafe.
	 *
	 * @abstract
	 * @param integer $days How many days to add.
	 * @param integer $from Datetime database field of expression.
	 *
	 * @return string
	 */
	public function addDaysToDateTime($days, $from = null)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Returns function cast $value to datetime database type.
	 * <p>
	 * $value parameter is SQL unsafe.
	 *
	 * @param string $value Database field or expression to cast.
	 *
	 * @return string
	 */
	abstract public function getDatetimeToDateFunction($value);

	/**
	 * Returns database expression for converting $field value according the $format.
	 * <p>
	 * Following format parts converted:
	 * - YYYY   A full numeric representation of a year, 4 digits
	 * - MMMM   A full textual representation of a month, such as January or March
	 * - MM     Numeric representation of a month, with leading zeros
	 * - MI     Minutes with leading zeros
	 * - M      A short textual representation of a month, three letters
	 * - DD     Day of the month, 2 digits with leading zeros
	 * - HH     24-hour format of an hour with leading zeros
	 * - H      24-hour format of an hour without leading zeros
	 * - GG     12-hour format of an hour with leading zeros
	 * - G      12-hour format of an hour without leading zeros
	 * - SS     Seconds with leading zeros
	 * - TT     AM or PM
	 * - T      AM or PM
	 * - W      Day of the week (0=Sunday ... 6=Saturday)
	 * <p>
	 * $field parameter is SQL unsafe.
	 *
	 * @param string $format Format string.
	 * @param string $field Database field or expression.
	 *
	 * @return string
	 */
	abstract public function formatDate($format, $field = null);

	/**
	 * Returns function for getting part of string.
	 * <p>
	 * If length is null or omitted, the substring starting
	 * from start until the end of the string will be returned.
	 * <p>
	 * $str and $from parameters are SQL unsafe.
	 *
	 * @param string $str Database field or expression.
	 * @param integer $from Start position.
	 * @param integer $length Maximum length.
	 *
	 * @return string
	 */
	public function getSubstrFunction($str, $from, $length = null)
	{
		$sql = 'SUBSTR('.$str.', '.$from;

		if (!is_null($length))
			$sql .= ', '.$length;

		return $sql.')';
	}

	/**
	 * Returns function for concatenating database fields or expressions.
	 * <p>
	 * All parameters are SQL unsafe.
	 *
	 * @param string $field,... Database fields or expressions.
	 *
	 * @return string
	 */
	abstract public function getConcatFunction();

	/**
	 * Returns function for testing database field or expressions
	 * against NULL value. When it is NULL then $result will be returned.
	 * <p>
	 * All parameters are SQL unsafe.
	 *
	 * @param string $expression Database field or expression for NULL test.
	 * @param string $result Database field or expression to return when $expression is NULL.
	 *
	 * @return string
	 */
	abstract public function getIsNullFunction($expression, $result);

	/**
	 * Returns function for getting length of database field or expression.
	 * <p>
	 * $field parameter is SQL unsafe.
	 *
	 * @param string $field Database field or expression.
	 *
	 * @return string
	 */
	abstract public function getLengthFunction($field);

	/**
	 * Returns function for converting string value into datetime.
	 * $value must be in YYYY-MM-DD HH:MI:SS format.
	 * <p>
	 * $value parameter is SQL unsafe.
	 *
	 * @param string $value String in YYYY-MM-DD HH:MI:SS format.
	 *
	 * @return string
	 * @see SqlHelper::formatDate
	 */
	abstract public function getCharToDateFunction($value);

	/**
	 * Returns function for converting database field or expression into string.
	 * <p>
	 * Result string will be in YYYY-MM-DD HH:MI:SS format.
	 * <p>
	 * $fieldName parameter is SQL unsafe.
	 *
	 * @param string $fieldName Database field or expression.
	 *
	 * @return string
	 * @see SqlHelper::formatDate
	 */
	abstract public function getDateToCharFunction($fieldName);

	/**
	 * Returns CAST expression for converting field or expression into string
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	abstract public function castToChar($fieldName);

	/**
	 * Returns expression for text field being used in group or order
	 * @see \Bitrix\Main\ORM\Query\Query::buildGroup
	 * @see \Bitrix\Main\ORM\Query\Query::buildOrder
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	abstract public function softCastTextToChar($fieldName);

	/**
	 * Transforms Sql according to $limit and $offset limitations.
	 * <p>
	 * You must specify $limit when $offset is set.
	 *
	 * @param string $sql Sql text.
	 * @param integer $limit Maximum number of rows to return.
	 * @param integer $offset Offset of the first row to return, starting from 0.
	 *
	 * @return string
	 * @throws Main\ArgumentException
	 */
	abstract public function getTopSql($sql, $limit, $offset = 0);

	/**
	 * Builds the strings for the SQL INSERT command for the given table.
	 *
	 * @param string $tableName A table name.
	 * @param array  $fields    Array("column" => $value)[].
	 *
	 * @param bool   $returnAsArray
	 *
	 * @return array (columnList, valueList, binds)
	 */
	public function prepareInsert($tableName, array $fields, $returnAsArray = false)
	{
		$columns = array();
		$values = array();

		$tableFields = $this->connection->getTableFields($tableName);

		$tableFields = array_change_key_case($tableFields, CASE_UPPER);
		$fields = array_change_key_case($fields, CASE_UPPER);

		foreach ($fields as $columnName => $value)
		{
			if (isset($tableFields[$columnName]))
			{
				$columns[] = $this->quote($columnName);
				$values[] = $this->convertToDb($value, $tableFields[$columnName]);
			}
			else
			{
				trigger_error("Column `{$columnName}` is not found in the `{$tableName}` table", E_USER_WARNING);
			}
		}

		$binds = $this->prepareBinds($tableFields, $fields);

		return array(
			$returnAsArray ? $columns : implode(", ", $columns),
			$returnAsArray ? $values : implode(", ", $values),
			$binds
		);
	}

	/**
	 * Builds the strings for the SQL UPDATE command for the given table.
	 *
	 * @param string $tableName A table name.
	 * @param array $fields Array("column" => $value)[].
	 *
	 * @return array (update, binds)
	 */
	public function prepareUpdate($tableName, array $fields)
	{
		$update = array();

		$tableFields = $this->connection->getTableFields($tableName);

		// one registry
		$tableFields = array_change_key_case($tableFields, CASE_UPPER);
		$fields = array_change_key_case($fields, CASE_UPPER);

		foreach ($fields as $columnName => $value)
		{
			if (isset($tableFields[$columnName]))
			{
				$update[] = $this->quote($columnName).' = '.$this->convertToDb($value, $tableFields[$columnName]);
			}
			else
			{
				trigger_error("Column `{$columnName}` is not found in the `{$tableName}` table", E_USER_WARNING);
			}
		}

		$binds = $this->prepareBinds($tableFields, $fields);

		return array(
			implode(", ", $update),
			$binds
		);
	}

	/**
	 * Builds the strings for the SQL MERGE command for the given table.
	 *
	 * @param string $tableName A table name.
	 * @param array $primaryFields Array("column")[] Primary key columns list.
	 * @param array $insertFields Array("column" => $value)[] What to insert.
	 * @param array $updateFields Array("column" => $value)[] How to update.
	 *
	 * @return array (merge)
	 */
	abstract public function prepareMerge($tableName, array $primaryFields, array $insertFields, array $updateFields);

	/**
	 * Performs additional processing of CLOB fields.
	 *
	 * @param ORM\Fields\ScalarField[] $tableFields Table fields.
	 * @param array $fields Data fields.
	 *
	 * @return array
	 */
	protected function prepareBinds(array $tableFields, array $fields)
	{
		return array();
	}

	/**
	 * Builds the string for the SQL assignment operation of the given column.
	 *
	 * @param string $tableName A table name.
	 * @param string $columnName A column name.
	 * @param string $value A value to assign.
	 *
	 * @return string
	 */
	public function prepareAssignment($tableName, $columnName, $value)
	{
		$tableField = $this->connection->getTableField($tableName, $columnName);

		return $this->quote($columnName).' = '.$this->convertToDb($value, $tableField);
	}

	/**
	 * Converts values to the string according to the column type to use it in a SQL query.
	 *
	 * @param mixed $value Value to be converted.
	 * @param ORM\Fields\IReadable | null $field Type "source".
	 *
	 * @return string Value to write to column.
	 */
	public function convertToDb($value, ORM\Fields\IReadable $field = null)
	{
		if ($value === null)
		{
			return "NULL";
		}

		if ($value instanceof SqlExpression)
		{
			return $value->compile();
		}

		if (is_a($field, '\Bitrix\Main\ORM\Fields\StringField'))
		{
			$size = $field->getSize();
			if ($size)
			{
				$value = mb_substr($value, 0, $size);
			}
		}

		if($field instanceof ORM\Fields\IReadable)
		{
			$result = $field->convertValueToDb($value);
		}
		else
		{
			$result = $this->convertToDbString($value);
		}

		return $result;
	}

	/**
	 * Returns $value converted to a type according to $field type.
	 * <p>
	 * For example if $field is Entity\DatetimeField then returned value will be the instance of Type\DateTime.
	 *
	 * @param mixed                $value Value to be converted.
	 * @param ORM\Fields\IReadable $field Type "source".
	 *
	 * @return mixed
	 */
	public function convertFromDb($value, ORM\Fields\IReadable $field)
	{
		return $field->convertValueFromDb($value);
	}

	/**
	 * Converts value to the string according to the data type to use it in a SQL query.
	 *
	 * @param mixed $value Value to be converted.
	 * @param int $size Size in bytes.
	 *
	 * @return int Value to write to column.
	 */
	public function convertToDbInteger($value, $size = 8)
	{
		$value = intval($value);
		if ($size == 2)
		{
			$value = max(-32768, min(+32767, $value));
		}
		elseif ($size == 4)
		{
			$value = max(-2147483648, min(+2147483647, $value));
		}
		return $value;
	}

	/**
	 * @param $value
	 *
	 * @return int
	 */
	public function convertFromDbInteger($value)
	{
		return intval($value);
	}

	/**
	 * Converts value to the string according to the data type to use it in a SQL query.
	 *
	 * @param mixed $value Value to be converted.
	 * @param int|null $scale Precise to round float value.
	 *
	 * @return string Value to write to column.
	 */
	public function convertToDbFloat($value, $scale = null)
	{
		$value = doubleval($value);
		if(!is_finite($value))
		{
			$value = 0;
		}

		return $scale !== null ? "'".round($value, $scale)."'" : "'".$value."'";
	}

	/**
	 * @param      $value
	 * @param int $scale
	 *
	 * @return float
	 */
	public function convertFromDbFloat($value, $scale = null)
	{
		$value = doubleval($value);

		return $scale !== null ? round($value, $scale) : $value;
	}

	/**
	 * Converts value to the string according to the data type to use it in a SQL query.
	 *
	 * @param mixed $value Value to be converted.
	 * @param int|null $length Maximum acceptable length of the value
	 *
	 * @return string Value to write to column.
	 */
	public function convertToDbString($value, $length = null)
	{
		return "'".$this->forSql($value, $length)."'";
	}

	/**
	 * @param string $value
	 * @param int $length
	 *
	 * @return string
	 */
	public function convertFromDbString($value, $length = null)
	{
		if ($length > 0)
		{
			$value = mb_substr($value, 0, $length);
		}

		return strval($value);
	}

	/**
	 * Converts value to the string according to the data type to use it in a SQL query.
	 *
	 * @param mixed $value Value to be converted.
	 *
	 * @return string Value to write to column.
	 */
	public function convertToDbText($value)
	{
		return $this->convertToDbString($value);
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function convertFromDbText($value)
	{
		return $this->convertFromDbString($value);
	}

	/**
	 * Converts value to the string according to the data type to use it in a SQL query.
	 *
	 * @param mixed $value Value to be converted.
	 *
	 * @return string Value to write to column.
	 * @throws Main\ArgumentTypeException
	 */
	public function convertToDbDate($value)
	{
		if (empty($value))
		{
			return "NULL";
		}
		elseif($value instanceof Type\Date)
		{
			return $this->getCharToDateFunction($value->format("Y-m-d"));
		}
		else
		{
			throw new Main\ArgumentTypeException('value', '\Bitrix\Main\Type\Date');
		}
	}

	/**
	 * @param $value
	 *
	 * @return Type\Date
	 * @throws Main\ObjectException
	 */
	public function convertFromDbDate($value)
	{
		return new Type\Date($value);
	}

	/**
	 * Converts value to the string according to the data type to use it in a SQL query.
	 *
	 * @param mixed $value Value to be converted.
	 *
	 * @return string Value to write to column.
	 * @throws Main\ArgumentTypeException
	 */
	public function convertToDbDateTime($value)
	{
		if (empty($value))
		{
			return "NULL";
		}
		elseif($value instanceof Type\Date)
		{
			if($value instanceof Type\DateTime)
			{
				$value = clone($value);
				$value->setDefaultTimeZone();
			}
			return $this->getCharToDateFunction($value->format("Y-m-d H:i:s"));
		}
		else
		{
			throw new Main\ArgumentTypeException('value', '\Bitrix\Main\Type\Date');
		}
	}

	/**
	 * @param $value
	 *
	 * @return Type\DateTime
	 * @throws Main\ObjectException
	 */
	public function convertFromDbDateTime($value)
	{
		return new Type\DateTime($value);
	}

	/**
	 * @deprecated
	 * Converts string into \Bitrix\Main\Type\DateTime object.
	 * <p>
	 * Helper function.
	 *
	 * @param string $value Value fetched.
	 *
	 * @return null|\Bitrix\Main\Type\DateTime
	 * @see SqlHelper::getConverter
	 */
	public function convertDatetimeField($value)
	{
		return $this->convertFromDbDateTime($value);
	}

	/**
	 * @deprecated
	 * Converts string into \Bitrix\Main\Type\Date object.
	 * <p>
	 * Helper function.
	 *
	 * @param string $value Value fetched.
	 *
	 * @return null|\Bitrix\Main\Type\Date
	 * @see SqlHelper::getConverter
	 */
	public function convertDateField($value)
	{
		return $this->convertFromDbDate($value);
	}

	/**
	 * Returns callback to be called for a field value on fetch.
	 * Used for soft conversion. For strict results @see ORM\Query\Result::setStrictValueConverters()
	 *
	 * @param ORM\Fields\ScalarField $field Type "source".
	 *
	 * @return false|callback
	 */
	public function getConverter(ORM\Fields\ScalarField $field)
	{
		return false;
	}

	/**
	 * Returns a column type according to ScalarField object.
	 *
	 * @param \Bitrix\Main\ORM\Fields\ScalarField $field Type "source".
	 *
	 * @return string
	 */
	abstract public function getColumnTypeByField(ORM\Fields\ScalarField $field);

	/**
	 * Returns instance of a descendant from Entity\ScalarField
	 * that matches database type.
	 *
	 * @param string $name Database column name.
	 * @param mixed $type Database specific type.
	 * @param array | null $parameters Additional information.
	 *
	 * @return \Bitrix\Main\ORM\Fields\ScalarField
	 */
	abstract public function getFieldByColumnType($name, $type, array $parameters = null);

	/**
	 * Returns ascending order specifier for ORDER BY clause.
	 *
	 * @return string
	 */
	public function getAscendingOrder()
	{
		return 'ASC';
	}

	/**
	 * Returns descending order specifier for ORDER BY clause.
	 *
	 * @return string
	 */
	public function getDescendingOrder()
	{
		return 'DESC';
	}

	/**
	 * @param string|SqlExpression $field
	 * @param string $value
	 * @return string
	 */
	public function getConditionalAssignment($field, string $value): string
	{
		$field = $field instanceof SqlExpression ? $field->compile() : $this->quote($field);
		$hash = $this->convertToDbString(sha1($value));
		$value = $this->convertToDbString($value);

		return 'case when ' . $this->getSha1Function($field) . ' = ' . $hash . ' then ' . $field . ' else ' . $value . ' end';
	}

	/**
	 * Makes an insert statement which will ignore duplicate keys errors.
	 *
	 * @abstract
	 * @param string $tableName Table to insert.
	 * @param integer $fields Fields list in braces.
	 * @param integer $sql Select or values sql.
	 *
	 * @return string
	 */
	public function getInsertIgnore($tableName, $fields, $sql)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Returns function for getting random number.
	 *
	 * @return string
	 */
	public function getRandomFunction()
	{
		return 'rand()';
	}

	/**
	 * Returns function to generate sha1 hash.
	 * <p>
	 * $field parameter is SQL unsafe.
	 *
	 * @param string $field Database field or expression.
	 *
	 * @return string
	 */
	public function getSha1Function($field)
	{
		return 'sha1(' . $field . ')';
	}

	/**
	 * Returns regexp expression.
	 * <p>
	 * All parameters are SQL unsafe.
	 *
	 * @abstract
	 * @param string $field Database field or expression.
	 * @param string $regexp Regexp to match.
	 *
	 * @return string
	 */
	public function getRegexpOperator($field, $regexp)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Returns case insensitive like expression.
	 * <p>
	 * All parameters are SQL unsafe.
	 *
	 * @abstract
	 * @param string $field Database field or expression.
	 * @param string $value String to match.
	 *
	 * @return string
	 */
	public function getIlikeOperator($field, $value)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Returns identifier for usage in VALUES.
	 *
	 * @abstract
	 * @param string $identifier Column name.
	 *
	 * @return string
	 * @see SqlHelper::quote
	 */
	public function values($identifier)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * @abstract
	 */
	public function getMatchFunction($field, $value)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * @abstract
	 */
	public function getMatchAndExpression($values, $prefixSearch = false)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * @abstract
	 */
	public function getMatchOrExpression($values, $prefixSearch = false)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Builds the DML strings for the SQL REPLACE INTO command for the given table.
	 *
	 * @abstract
	 * @param string $tableName A table name.
	 * @param array $primaryFields Array("column")[] Primary key columns list.
	 * @param array $insertRows Array(Array("column" => $value)[])[] Rows to insert.
	 *
	 * @return array (replace)
	 */
	public function prepareMergeMultiple($tableName, array $primaryFields, array $insertRows)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Builds the DML strings for the SQL INSERT INTO ON CONFLICT UPDATE command for the given table.
	 *
	 * @abstract
	 * @param string $tableName A table name.
	 * @param array $primaryFields Array("column")[] Primary key columns list.
	 * @param array $selectFields
	 * @param $select
	 * @param $updateFields
	 * @return string (replace)
	 */
	public function prepareMergeSelect($tableName, array $primaryFields, array $selectFields, $select, $updateFields)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Builds the DML string for the SQL DELETE command for the given table with limited rows number.
	 *
	 * @abstract
	 * @param string $tableName A table name.
	 * @param array $primaryFields Array("column")[] Primary key columns list.
	 * @param string $where Sql where clause.
	 * @param array $order Array("column" => asc|desc)[] Sort order.
	 * @param integer $limit Rows to delete count.
	 *
	 * @return string (replace)
	 */
	public function prepareDeleteLimit($tableName, array $primaryFields, $where, array $order, $limit)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * @abstract
	 */
	public function initRowNumber($variableName)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * @abstract
	 */
	public function getRowNumber($variableName)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Builds correlated update DML.
	 *
	 * @abstract
	 * @param string $tableName A table name.
	 * @param string $tableAlias A table alias.
	 * @param array $fields Array("column" => "expression")[] Update columns list.
	 * @param string $from Correlated tables.
	 * @param string $where Where clause.
	 *
	 * @return string
	 */
	public function prepareCorrelatedUpdate($tableName, $tableAlias, $fields, $from, $where)
	{
		throw new Main\NotImplementedException('Method should be implemented in a child class.');
	}

	/**
	 * Returns prepared sql string for upsert multiple rows
	 *
	 * @param string $tableName Table name
	 * @param array $primaryFields Fields that can be conflicting keys (primary, unique keys)
	 * @param array $insertRows Rows to insert [['FIELD_NAME' =>'value',...],...], Attention! use same columns in each row
	 * @param array $updateFields Fields to update, if empty - update all fields, can be only field names, or fieldname => expression or fieldname => value
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function prepareMergeValues(string $tableName, array $primaryFields, array $insertRows, array $updateFields = []): string
	{
		$insertColumns = array_keys($insertRows[array_key_first($insertRows)] ?? []);
		$insertValuesStrings = [];
		foreach ($insertRows as $row)
		{
			[, $rowValues] = $this->prepareInsert($tableName, $row);
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

		return $this->prepareMergeSelect($tableName, $primaryFields, $insertColumns, $insertValueString, $compatibleUpdateFields);
	}

	/**
	 * @param string $field
	 * @param array $values
	 * @param bool $quote
	 *
	 * @return string
	 */
	public function getOrderByStringField(string $field, array $values, bool $quote = true): string
	{
		return $this->getOrderByField($field, $values, [$this, 'convertToDbString'], $quote);
	}

	/**
	 * @param string $field
	 * @param array $values
	 * @param bool $quote
	 *
	 * @return string
	 */
	public function getOrderByIntField(string $field, array $values, bool $quote = true): string
	{
		return $this->getOrderByField($field, $values, [$this, 'convertFromDbInteger'], $quote);
	}

	/**
	 * @param string $field
	 * @param array $values
	 * @param callable $callback
	 * @param bool $quote
	 *
	 * @return string
	 */
	protected function getOrderByField(string $field, array $values, callable $callback, bool $quote = true): string
	{
		return $quote ? $this->quote($field) : $field;
	}

	/**
	 * @param string $sql
	 * @param int $maxLevel
	 * @return array
	 */
	public function getQueryTables(string $sql, int $maxLevel = -1) : array
	{
		$level = 0;
		$tables = [];

		$escaped = false;
		$singleQuotes = false;
		$doubleQuotes = false;

		$isFrom = [0 => false];
		$isTable = [0 => false];
		$isIf = [0 => false];

		$sql = preg_replace('/\s\s+/m', ' ', $sql);
		$sql = preg_replace('/(HOUR|MINUTE|SECOND|YEAR|QUARTER|WEEK|MICROSECOND)(\s+)FROM/is', 'XXX_FROM', $sql);

		foreach (preg_split('/([,()"\'\\\\]|\s+)/s', $sql, -1, PREG_SPLIT_DELIM_CAPTURE) as $token)
		{
			if ($maxLevel > -1 && $level > $maxLevel)
			{
				break;
			}

			$token = trim($token, "` ;\t\n\r");
			if ($token === '\\')
			{
				$escaped = !$escaped;
				continue;
			}

			if ($token === '"' && !$escaped)
			{
				$doubleQuotes = !$doubleQuotes;
				continue;
			}

			if ($token === '\'' && !$escaped)
			{
				$singleQuotes = !$singleQuotes;
				continue;
			}

			if ($token && !$doubleQuotes && !$singleQuotes)
			{
				if ($token === '(')
				{
					$isTable[$level] = false;
					$level++;
					$isFrom[$level] = false;
					$isTable[$level] = false;
					$isIf[$level] = false;
				}
				elseif ($token === ')')
				{
					$isTable[$level] = false;
					if ($level > 0)
					{
						$level--;
					}
				}
				else
				{
					switch (strtoupper($token))
					{
						case 'INTO':
							$isTable[$level] = true;
							break;
						case 'FROM':
						case 'UPDATE':
						case 'TABLE':
						case 'TRUNCATE':
							$isFrom[$level] = true;
							$isTable[$level] = true;
							break;
						case 'EXISTS':
							if ($isIf[$level])
							{
								$isFrom[$level] = true;
								$isTable[$level] = true;
							}
							break;

						case 'WHERE':
						case 'GROUP':
						case 'HAVING':
						case 'ORDER':
						case 'LIMIT':
						case 'SET':
							$isFrom[$level] = false;
							break;
						case ',':
						case 'JOIN':
						case 'STRAIGHT_JOIN':
							if ($isFrom[$level])
							{
								$isTable[$level] = true;
							}
							break;
						case 'IF':
							$isIf[$level] = true;
							$isTable[$level] = false;
							break;
						case 'TEMPORARY':
							$isTable[$level] = false;
							break;
						default:
							if ($isTable[$level])
							{
								$tables[$token] = $token;
								$isTable[$level] = false;
							}
					}
				}
			}
		}

		return $tables;
	}

	/**
	 * Checks is the field type is BIG
	 *
	 * @param $type
	 * @return bool
	 */
	public function isBigType($type): bool
	{
		return false;
	}
}
