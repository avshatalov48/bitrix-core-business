<?php

namespace Bitrix\Main\DB;

use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\ScalarField;

class MssqlSqlHelper extends SqlHelper
{
	/**
	 * @inheritdoc
	 */
	public function getLeftQuote()
	{
		return '[';
	}

	/**
	 * @inheritdoc
	 */
	public function getRightQuote()
	{
		return ']';
	}

	/**
	 * @inheritdoc
	 */
	public function getAliasLength()
	{
		return 28;
	}

	/**
	 * @inheritdoc
	 */
	public function getQueryDelimiter()
	{
		return "\nGO";
	}

	/**
	 * @inheritdoc
	 */
	public function forSql($value, $maxLength = 0)
	{
		if ($maxLength > 0)
		{
			$value = mb_substr($value, 0, $maxLength);
		}
		$value = str_replace("'", "''", $value);
		$value = str_replace("\x00", "", $value);
		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentDateTimeFunction()
	{
		return "GETDATE()";
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentDateFunction()
	{
		return "convert(datetime, cast(year(getdate()) as varchar(4)) + '-' + cast(month(getdate()) as varchar(2)) + '-' + cast(day(getdate()) as varchar(2)), 120)";
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

		return 'DATEADD(second, '.$seconds.', '.$from.')';
	}

	/**
	 * @inheritdoc
	 */
	public function getDatetimeToDateFunction($value)
	{
		return 'DATEADD(dd, DATEDIFF(dd, 0, '.$value.'), 0)';
	}

	/**
	 * @inheritdoc
	 */
	public function formatDate($format, $field = null)
	{
		if ($field === null)
		{
			return '';
		}

		$result = array();

		foreach (preg_split("#(YYYY|MMMM|MM|MI|M|DD|HH|H|GG|G|SS|TT|T)#", $format, -1, PREG_SPLIT_DELIM_CAPTURE) as $part)
		{
			switch ($part)
			{
				case "YYYY":
					$result[] = "\n\tCONVERT(varchar(4),DATEPART(yyyy, $field))";
					break;
				case "MMMM":
					$result[] = "\n\tdatename(mm, $field)";
					break;
				case "MM":
					$result[] = "\n\tREPLICATE('0',2-LEN(DATEPART(mm, $field)))+CONVERT(varchar(2),DATEPART(mm, $field))";
					break;
				case "MI":
					$result[] = "\n\tREPLICATE('0',2-LEN(DATEPART(mi, $field)))+CONVERT(varchar(2),DATEPART(mi, $field))";
					break;
				case "M":
					$result[] = "\n\tCONVERT(varchar(3), $field,7)";
					break;
				case "DD":
					$result[] = "\n\tREPLICATE('0',2-LEN(DATEPART(dd, $field)))+CONVERT(varchar(2),DATEPART(dd, $field))";
					break;
				case "HH":
				case "GG":
					$result[] = "\n\tREPLICATE('0',2-LEN(DATEPART(hh, $field)))+CONVERT(varchar(2),DATEPART(hh, $field))";
					break;
				case "H":
				case "G":
					$result[] = "\n\tCASE WHEN DATEPART(HH, $field) < 13 THEN RIGHT(REPLICATE('0',2) + CAST(datepart(HH, $field) AS VARCHAR(2)),2) ELSE RIGHT(REPLICATE('0',2) + CAST(datepart(HH, dateadd(HH, -12, $field)) AS VARCHAR(2)), 2) END";
					break;
				case "SS":
					$result[] = "\n\tREPLICATE('0',2-LEN(DATEPART(ss, $field)))+CONVERT(varchar(2),DATEPART(ss, $field))";
					break;
				case "TT":
				case "T":
					$result[] = "\n\tCASE WHEN DATEPART(HH, $field) < 12 THEN 'AM' ELSE 'PM' END";
					break;
				default:
					$result[] = "'".$part."'";
					break;
			}
		}

		return implode("+", $result);
	}

	/**
	 * @inheritdoc
	 */
	public function getSubstrFunction($str, $from, $length = null)
	{
		$sql = 'SUBSTRING('.$str.', '.$from;

		if (!is_null($length))
			$sql .= ', '.$length;
		else
			$sql .= ', LEN('.$str.') + 1 - '.$from;

		return $sql.')';
	}

	/**
	 * @inheritdoc
	 */
	public function getConcatFunction()
	{
		return implode(" + ", func_get_args());
	}

	/**
	 * @inheritdoc
	 */
	public function getIsNullFunction($expression, $result)
	{
		return "ISNULL(".$expression.", ".$result.")";
	}

	/**
	 * @inheritdoc
	 */
	public function getLengthFunction($field)
	{
		return "LEN(".$field.")";
	}

	/**
	 * @inheritdoc
	 */
	public function getCharToDateFunction($value)
	{
		return "CONVERT(datetime, '".$value."', 120)";
	}

	/**
	 * @inheritdoc
	 */
	public function getDateToCharFunction($fieldName)
	{
		return "CONVERT(varchar(19), ".$fieldName.", 120)";
	}

	/**
	 * @inheritdoc
	 */
	public function castToChar($fieldName)
	{
		return 'CAST('.$fieldName.' AS varchar)';
	}

	/**
	 * @inheritdoc
	 */
	public function softCastTextToChar($fieldName)
	{
		return 'CONVERT(VARCHAR(8000), '.$fieldName.')';
	}

	/**
	 * @inheritdoc
	 */
	public function getConverter(ScalarField $field)
	{
		if ($field instanceof ORM\Fields\DatetimeField)
		{
			return array($this, "convertFromDbDateTime");
		}
		elseif ($field instanceof ORM\Fields\DateField)
		{
			return array($this, "convertFromDbDate");
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
	 * @inheritdoc
	 */
	public function convertFromDbDateTime($value)
	{
		if ($value !== null)
		{
			$value = new Type\DateTime(mb_substr($value, 0, 19), "Y-m-d H:i:s");
		}

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function convertFromDbDate($value)
	{
		if($value !== null)
		{
			$value = new Type\Date($value, "Y-m-d");
		}

		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function convertFromDbString($value, $length = null)
	{
		if ($value !== null)
		{
			if(preg_match("#^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}\$#", $value))
			{
				return new Type\DateTime($value, "Y-m-d H:i:s");
			}
		}

		return parent::convertFromDbString($value, $length);
	}

	/**
	 * @inheritdoc
	 */
	public function getColumnTypeByField(ScalarField $field)
	{
		if ($field instanceof ORM\Fields\IntegerField)
		{
			return 'int';
		}
		elseif ($field instanceof ORM\Fields\FloatField)
		{
			return 'float';
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
			return 'text';
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
				$falseLen = strlen($values[0]);
				$trueLen = strlen($values[1]);
				if ($falseLen === 1 && $trueLen === 1)
				{
					return 'char(1)';
				}
				return 'varchar(' . max($falseLen, $trueLen) . ')';
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
	public function getFieldByColumnType($name, $type, array $parameters = null)
	{
		switch($type)
		{
			case 4:
			case 5:
			case -6:
				//int SQL_INTEGER (4)
				//smallint SQL_SMALLINT (5)
				//tinyint SQL_TINYINT (-6)
				return new ORM\Fields\IntegerField($name);

			case 2:
			case 3:
			case 6:
			case 7:
				//numeric SQL_NUMERIC (2)
				//decimal SQL_DECIMAL (3)
				//smallmoney SQL_DECIMAL (3)
				//money SQL_DECIMAL (3)
				//float SQL_FLOAT (6)
				//real SQL_REAL (7)
				return new ORM\Fields\FloatField($name, array("scale" => $parameters["scale"]));

			case 93:
				//datetime - SQL_TYPE_TIMESTAMP (93)
				//datetime2 - SQL_TYPE_TIMESTAMP (93)
				//smalldatetime - SQL_TYPE_TIMESTAMP (93)
				return new ORM\Fields\DatetimeField($name);

			case 91:
				//date - SQL_TYPE_DATE (91)
				return new ORM\Fields\DateField($name);
		}
		//bigint SQL_BIGINT (-5)
		//binary SQL_BINARY (-2)
		//bit SQL_BIT (-7)
		//char SQL_CHAR (1)
		//datetimeoffset SQL_SS_TIMESTAMPOFFSET (-155)
		//image SQL_LONGVARBINARY (-4)
		//nchar SQL_WCHAR (-8)
		//ntext SQL_WLONGVARCHAR (-10)
		//nvarchar SQL_WVARCHAR (-9)
		//text SQL_LONGVARCHAR (-1)
		//time SQL_SS_TIME2 (-154)
		//timestamp SQL_BINARY (-2)
		//udt SQL_SS_UDT (-151)
		//uniqueidentifier SQL_GUID (-11)
		//varbinary SQL_VARBINARY (-3)
		//varchar SQL_VARCHAR (12)
		//xml SQL_SS_XML (-152)
		return new ORM\Fields\StringField($name, array("size" => $parameters["size"]));
	}

	/**
	 * @inheritdoc
	 */
	public function getTopSql($sql, $limit, $offset = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		if ($offset > 0 && $limit <= 0)
			throw new Main\ArgumentException("Limit must be set if offset is set");

		if ($limit > 0)
		{
			if ($offset <= 0)
			{
				$sql = preg_replace("/^\\s*SELECT/i", "SELECT TOP ".$limit, $sql);
			}
			else
			{
				$orderBy = '';
				$sqlTmp = $sql;

				preg_match_all("#\\sorder\\s+by\\s#i", $sql, $matches, PREG_OFFSET_CAPTURE);
				if (isset($matches[0]) && is_array($matches[0]) && !empty($matches[0]))
				{
					$idx = $matches[0][count($matches[0]) - 1][1];
					$s = mb_substr($sql, $idx);
					if (substr_count($s, '(') === substr_count($s, ')'))
					{
						$orderBy = $s;
						$sqlTmp = mb_substr($sql, 0, $idx);
					}
				}

				if ($orderBy === '')
				{
					$orderBy = "ORDER BY (SELECT 1)";
					$sqlTmp = $sql;
				}

				// ROW_NUMBER() Returns the sequential number of a row within a partition of a result set, starting at 1 for the first row in each partition.
				$sqlTmp = preg_replace(
					"/^\\s*SELECT/i",
					"SELECT ROW_NUMBER() OVER (".$orderBy.") AS ROW_NUMBER_ALIAS,",
					$sqlTmp
				);

				$sql =
					"WITH ROW_NUMBER_QUERY_ALIAS AS (".$sqlTmp.") ".
					"SELECT * ".
					"FROM ROW_NUMBER_QUERY_ALIAS ".
					"WHERE ROW_NUMBER_ALIAS BETWEEN ".($offset + 1)." AND ".($offset + $limit);
			}
		}
		return $sql;
	}

	/**
	 * @inheritdoc
	 */
	public function prepareMerge($tableName, array $primaryFields, array $insertFields, array $updateFields)
	{
		$insert = $this->prepareInsert($tableName, $insertFields);

		$updateColumns = array();
		$sourceSelectValues = array();
		$sourceSelectColumns = array();
		$targetConnectColumns = array();
		$tableFields = $this->connection->getTableFields($tableName);
		foreach($tableFields as $columnName => $tableField)
		{
			$quotedName = $this->quote($columnName);
			if (in_array($columnName, $primaryFields))
			{
				$sourceSelectValues[] = $this->convertToDb($insertFields[$columnName], $tableField);
				$sourceSelectColumns[] = $quotedName;
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
			&& $sourceSelectValues && $sourceSelectColumns && $targetConnectColumns
		)
		{
			$sql = "
				MERGE INTO ".$this->quote($tableName)." AS target USING (
					SELECT ".implode(", ", $sourceSelectValues)."
				) AS source (
					".implode(", ", $sourceSelectColumns)."
				)
				ON
				(
					".implode(" AND ", $targetConnectColumns)."
				)
				WHEN MATCHED THEN
					UPDATE SET ".implode(", ", $updateColumns)."
				WHEN NOT MATCHED THEN
					INSERT (".$insert[0].")
					VALUES (".$insert[1].")
				;
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
