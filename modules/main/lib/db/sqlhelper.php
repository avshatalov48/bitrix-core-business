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
	 * @see \Bitrix\Main\DB\SqlHelper::getLeftQuote
	 * @see \Bitrix\Main\DB\SqlHelper::getRightQuote
	 */
	public function quote($identifier)
	{
		if (empty($this->idCache[$identifier]))
		{
			// security unshielding
			$quotedIdentifier = str_replace([$this->getLeftQuote(), $this->getRightQuote()], '', $identifier);

			// shield [[database.]tablename.]columnname
			if (mb_strpos($quotedIdentifier, '.') !== false)
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
	 * @see \Bitrix\Main\DB\MssqlSqlHelper::formatDate
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
	 * @see \Bitrix\Main\DB\MssqlSqlHelper::formatDate
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
	 * @throws Main\ArgumentTypeException
	 * @throws SqlQueryException
	 */
	public function prepareInsert($tableName, array $fields, $returnAsArray = false)
	{
		$columns = array();
		$values = array();

		$tableFields = $this->connection->getTableFields($tableName);

		// one registry
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
	 * @param array                    $fields      Data fields.
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
	 * @param mixed                $value Value to be converted.
	 * @param ORM\Fields\IReadable $field Type "source".
	 *
	 * @return string Value to write to column.
	 * @throws \Bitrix\Main\ArgumentTypeException
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
	 * Returns $value converted to an type according to $field type.
	 * <p>
	 * For example if $field is Entity\DatetimeField then returned value will be instance of Type\DateTime.
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
	 *
	 * @return string Value to write to column.
	 */
	public function convertToDbInteger($value)
	{
		return intval($value);
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
	 * @param array $parameters Additional information.
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
}
