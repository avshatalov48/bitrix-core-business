<?php
namespace Bitrix\Main\DB;

use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\ScalarField;

class OracleSqlHelper extends SqlHelper
{
	/**
	 * Returns an identificator escaping left character.
	 *
	 * @return string
	 */
	public function getLeftQuote()
	{
		return '"';
	}

	/**
	 * Returns an identificator escaping right character.
	 *
	 * @return string
	 */
	public function getRightQuote()
	{
		return '"';
	}

	/**
	 * Returns maximum length of an alias in a select statement
	 *
	 * @return integer
	 */
	public function getAliasLength()
	{
		return 30;
	}

	/**
	 * Returns quoted identifier.
	 *
	 * @param string $identifier Table or Column name.
	 *
	 * @return string
	 * @see \Bitrix\Main\DB\SqlHelper::quote
	 */
	public function quote($identifier)
	{
		return parent::quote(mb_strtoupper($identifier));
	}

	/**
	 * Returns database specific query delimiter for batch processing.
	 *
	 * @return string
	 */
	public function getQueryDelimiter()
	{
		return "(?<!\\*)/(?!\\*)";
	}

	/**
	 * Escapes special characters in a string for use in an SQL statement.
	 *
	 * @param string $value Value to be escaped.
	 * @param integer $maxLength Limits string length if set.
	 *
	 * @return string
	 */
	function forSql($value, $maxLength = 0)
	{
		if ($maxLength <= 0 || $maxLength > 2000)
			$maxLength = 2000;

		$value = mb_substr($value, 0, $maxLength);

		if (\Bitrix\Main\Application::isUtfMode())
		{
			// From http://w3.org/International/questions/qa-forms-utf-8.html
			// This one can crash php with segmentation fault on large input data (over 20K)
			// https://bugs.php.net/bug.php?id=60423
			if (preg_match_all('%(
				[\x00-\x7E]                        # ASCII
				|[\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
				|\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
				|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
				|\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
				|\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
				|[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
				|\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
			)+%x', $value, $match))
				$value = implode(' ', $match[0]);
			else
				return ''; //There is no valid utf at all
		}

		return str_replace("'", "''", $value);
	}

	/**
	 * Returns function for getting current time.
	 *
	 * @return string
	 */
	public function getCurrentDateTimeFunction()
	{
		return "SYSDATE";
	}

	/**
	 * Returns function for getting current date without time part.
	 *
	 * @return string
	 */
	public function getCurrentDateFunction()
	{
		return "TRUNC(SYSDATE)";
	}

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
	public function addSecondsToDateTime($seconds, $from = null)
	{
		if ($from === null)
		{
			$from = static::getCurrentDateTimeFunction();
		}

		return '('.$from.'+'.$seconds.'/86400)';
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
	public function getDatetimeToDateFunction($value)
	{
		return 'TRUNC('.$value.')';
	}

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
	public function formatDate($format, $field = null)
	{
		$format = str_replace("HH", "HH24", $format);
		$format = str_replace("GG", "HH24", $format);

		if (mb_strpos($format, 'HH24') === false)
		{
			$format = str_replace("H", "HH", $format);
		}

		$format = str_replace("G", "HH", $format);

		$format = str_replace("MI", "II", $format);

		if (mb_strpos($format, 'MMMM') !== false)
		{
			$format = str_replace("MMMM", "MONTH", $format);
		}
		elseif (mb_strpos($format, 'MM') === false)
		{
			$format = str_replace("M", "MON", $format);
		}

		$format = str_replace("II", "MI", $format);

		$format = str_replace("TT", "AM", $format);
		$format = str_replace("T", "AM", $format);

		if ($field === null)
		{
			return $format;
		}
		else
		{
			return "TO_CHAR(".$field.", '".$format."')";
		}
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
	public function getConcatFunction()
	{
		$str = "";
		$ar = func_get_args();
		if (is_array($ar))
			$str .= implode(" || ", $ar);
		return $str;
	}

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
	public function getIsNullFunction($expression, $result)
	{
		return "NVL(".$expression.", ".$result.")";
	}

	/**
	 * Returns function for getting length of database field or expression.
	 * <p>
	 * $field parameter is SQL unsafe.
	 *
	 * @param string $field Database field or expression.
	 *
	 * @return string
	 */
	public function getLengthFunction($field)
	{
		return "LENGTH(".$field.")";
	}

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
	public function getCharToDateFunction($value)
	{
		return "TO_DATE('".$value."', 'YYYY-MM-DD HH24:MI:SS')";
	}

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
	public function getDateToCharFunction($fieldName)
	{
		return "TO_CHAR(".$fieldName.", 'YYYY-MM-DD HH24:MI:SS')";
	}

	/**
	 * Performs additional processing of CLOB fields.
	 *
	 * @param ScalarField[] $tableFields Table fields.
	 * @param array         $fields      Data fields.
	 *
	 * @return array
	 */
	protected function prepareBinds(array $tableFields, array $fields)
	{
		$binds = array();

		foreach ($tableFields as $columnName => $tableField)
		{
			if (isset($fields[$columnName]) && !($fields[$columnName] instanceof SqlExpression))
			{
				if ($tableField instanceof ORM\Fields\TextField && $fields[$columnName] <> '')
				{
					$binds[$columnName] = $fields[$columnName];
				}
			}
		}

		return $binds;
	}

	/**
	 * Returns callback to be called for a field value on fetch.
	 * Used for soft conversion. For strict results @see ORM\Query\Result::setStrictValueConverters()
	 *
	 * @param ScalarField $field Type "source".
	 *
	 * @return false|callback
	 */
	public function getConverter(ScalarField $field)
	{
		if ($field instanceof ORM\Fields\DatetimeField)
		{
			return array($this, "convertFromDbDateTime");
		}
		elseif ($field instanceof ORM\Fields\TextField)
		{
			return array($this, "convertFromDbText");
		}
		elseif ($field instanceof ORM\Fields\StringField)
		{
			return array($this, "convertFromDbString");
		}
		else
		{
			return parent::getConverter($field);
		}
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
	 * @see \Bitrix\Main\Db\OracleSqlHelper::getConverter
	 */
	public function convertDatetimeField($value)
	{
		return $this->convertFromDbDateTime($value);
	}

	/**
	 * @param $value
	 *
	 * @return Type\DateTime
	 * @throws Main\ObjectException
	 */
	public function convertFromDbDateTime($value)
	{
		if ($value !== null)
		{
			if (mb_strlen($value) == 19)
			{
				//preferable format: NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'
				$value = new Type\DateTime($value, "Y-m-d H:i:s");
			}
			else
			{
				//default Oracle date format: 03-MAR-14
				$value = new Type\DateTime($value." 00:00:00", "d-M-y H:i:s");
			}
		}

		return $value;
	}

	/**
	 * @deprecated
	 * Converts lob object into string.
	 * <p>
	 * Helper function.
	 *
	 * @param string $value Value fetched.
	 *
	 * @return null|string
	 * @see \Bitrix\Main\Db\OracleSqlHelper::getConverter
	 */
	public function convertTextField($value)
	{
		return $this->convertFromDbText($value);
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function convertFromDbText($value)
	{
		if ($value !== null)
		{
			if (is_object($value))
			{
				/** @var \OCI_Lob $value */
				$value = $value->load();
			}
		}

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function convertToDbText($value)
	{
		return empty($value) ? "NULL" : "EMPTY_CLOB()";
	}

	/**
	 * @deprecated
	 * Converts string into \Bitrix\Main\Type\Date object if string has datetime specific format..
	 * <p>
	 * Helper function.
	 *
	 * @param string $value Value fetched.
	 *
	 * @return null|\Bitrix\Main\Type\DateTime
	 * @see \Bitrix\Main\Db\OracleSqlHelper::getConverter
	 */
	public function convertStringField($value)
	{
		return $this->convertFromDbString($value);
	}

	/**
	 * @param string $value
	 * @param null   $length
	 *
	 * @return Type\DateTime|string
	 * @throws Main\ObjectException
	 */
	public function convertFromDbString($value, $length = null)
	{
		if ($value !== null)
		{
			if ((mb_strlen($value) == 19) && preg_match("#^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$#", $value))
			{
				return new Type\DateTime($value, "Y-m-d H:i:s");
			}
		}

		return parent::convertFromDbString($value, $length);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string $fieldName
	 *
	 * return string
	 */
	public function castToChar($fieldName)
	{
		return 'TO_CHAR('.$fieldName.')';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string $fieldName
	 *
	 * return string
	 */
	public function softCastTextToChar($fieldName)
	{
		return 'dbms_lob.substr('.$fieldName.', 4000, 1)';
	}

	/**
	 * Returns a column type according to ScalarField object.
	 *
	 * @param ScalarField $field Type "source".
	 *
	 * @return string
	 */
	public function getColumnTypeByField(ScalarField $field)
	{
		if ($field instanceof ORM\Fields\IntegerField)
		{
			return 'number(18)';
		}
		elseif ($field instanceof ORM\Fields\FloatField)
		{
			$scale = $field->getScale();
			return 'number'.($scale !== null? "(*,".$scale.")": "");
		}
		elseif ($field instanceof ORM\Fields\DatetimeField)
		{
			return 'date';
		}
		elseif ($field instanceof ORM\Fields\DateField)
		{
			return 'date';
		}
		elseif ($field instanceof ORM\Fields\TextField)
		{
			return 'clob';
		}
		elseif ($field instanceof ORM\Fields\BooleanField)
		{
			$values = $field->getValues();

			if (preg_match('/^[0-9]+$/', $values[0]) && preg_match('/^[0-9]+$/', $values[1]))
			{
				return 'number(1)';
			}
			else
			{
				return 'varchar2('.max(mb_strlen($values[0]), mb_strlen($values[1])).' char)';
			}
		}
		elseif ($field instanceof ORM\Fields\EnumField)
		{
			return 'varchar2('.max(array_map('strlen', $field->getValues())).' char)';
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
			return 'varchar2('.($defaultLength > 0? $defaultLength: 255).' char)';
		}
	}

	/**
	 * Returns instance of a descendant from Entity\ScalarField
	 * that matches database type.
	 *
	 * @param string $name Database column name.
	 * @param mixed $type Database specific type.
	 * @param array $parameters Additional information.
	 *
	 * @return ScalarField
	 */
	public function getFieldByColumnType($name, $type, array $parameters = null)
	{
		switch ($type)
		{
		case "DATE":
			return new ORM\Fields\DatetimeField($name);

		case "NCLOB":
		case "CLOB":
		case "BLOB":
			return new ORM\Fields\TextField($name);

		case "FLOAT":
		case "BINARY_FLOAT":
		case "BINARY_DOUBLE":
			return new ORM\Fields\FloatField($name);

		case "NUMBER":
			if ($parameters["precision"] == 0 && $parameters["scale"] == -127)
			{
				//NUMBER
				return new ORM\Fields\FloatField($name);
			}
			if (intval($parameters["scale"]) <= 0)
			{
				//NUMBER(18)
				//NUMBER(18,-2)
				return new ORM\Fields\IntegerField($name);
			}
			//NUMBER(*,2)
			return new ORM\Fields\FloatField($name, array("scale" => $parameters["scale"]));
		}
		//LONG
		//VARCHAR2(size [BYTE | CHAR])
		//NVARCHAR2(size)
		//TIMESTAMP [(fractional_seconds_precision)]
		//TIMESTAMP [(fractional_seconds)] WITH TIME ZONE
		//TIMESTAMP [(fractional_seconds)] WITH LOCAL TIME ZONE
		//INTERVAL YEAR [(year_precision)] TO MONTH
		//INTERVAL DAY [(day_precision)] TO SECOND [(fractional_seconds)]
		//RAW(size)
		//LONG RAW
		//ROWID
		//UROWID [(size)]
		//CHAR [(size [BYTE | CHAR])]
		//NCHAR[(size)]
		//BFILE
		return new ORM\Fields\StringField($name, array("size" => $parameters["size"]));
	}

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
	public function getTopSql($sql, $limit, $offset = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		if ($offset > 0 && $limit <= 0)
			throw new \Bitrix\Main\ArgumentException("Limit must be set if offset is set");

		if ($limit > 0)
		{
			//The first row selected has a ROWNUM of 1, the second has 2, and so on
			if ($offset <= 0)
			{
				$sql =
					"SELECT * ".
					"FROM (".$sql.") ".
					"WHERE ROWNUM <= ".$limit;
			}
			else
			{
				$sql =
					"SELECT * ".
					"FROM (".
					"   SELECT rownum_query_alias.*, ROWNUM rownum_alias ".
					"   FROM (".$sql.") rownum_query_alias ".
					"   WHERE ROWNUM <= ".($offset + $limit)." ".
					") ".
					"WHERE rownum_alias >= ".($offset + 1);
			}
		}
		return $sql;
	}

	/**
	 * Returns ascending order specifier for ORDER BY clause.
	 *
	 * @return string
	 */
	public function getAscendingOrder()
	{
		return 'ASC NULLS FIRST';
	}

	/**
	 * Returns descending order specifier for ORDER BY clause.
	 *
	 * @return string
	 */
	public function getDescendingOrder()
	{
		return 'DESC NULLS LAST';
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
	public function prepareMerge($tableName, array $primaryFields, array $insertFields, array $updateFields)
	{
		$insert = $this->prepareInsert($tableName, $insertFields);

		$updateColumns = array();
		$sourceSelectColumns = array();
		$targetConnectColumns = array();
		$tableFields = $this->connection->getTableFields($tableName);
		foreach($tableFields as $columnName => $tableField)
		{
			$quotedName = $this->quote($columnName);
			if (in_array($columnName, $primaryFields))
			{
				$sourceSelectColumns[] = $this->convertToDb($insertFields[$columnName], $tableField)." AS ".$quotedName;
				if($insertFields[$columnName] === null)
				{
					//can't just compare NULLs
					$targetConnectColumns[] = "(source.".$quotedName." IS NULL AND target.".$quotedName." IS NULL)";
				}
				else
				{
					$targetConnectColumns[] = "(source.".$quotedName." = target.".$quotedName.")";
				}
			}

			if (isset($updateFields[$columnName]) || array_key_exists($columnName, $updateFields))
			{
				$updateColumns[] = "target.".$quotedName.' = '.$this->convertToDb($updateFields[$columnName], $tableField);
			}
		}

		if (
			$insert && $insert[0] != "" && $insert[1] != ""
			&& $updateColumns
			&& $sourceSelectColumns && $targetConnectColumns
		)
		{
			$sql = "
				MERGE INTO ".$this->quote($tableName)." target USING (
					SELECT ".implode(", ", $sourceSelectColumns)." FROM dual
				)
				source ON
				(
					".implode(" AND ", $targetConnectColumns)."
				)
				WHEN MATCHED THEN
					UPDATE SET ".implode(", ", $updateColumns)."
				WHEN NOT MATCHED THEN
					INSERT (".$insert[0].")
					VALUES (".$insert[1].")
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
}
