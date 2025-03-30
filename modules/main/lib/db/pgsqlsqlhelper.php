<?php

namespace Bitrix\Main\DB;

use Bitrix\Main\Type;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\ScalarField;

class PgsqlSqlHelper extends SqlHelper
{
	/**
	 * @inheritdoc
	 */
	public function getLeftQuote()
	{
		return '"';
	}

	/**
	 * @inheritdoc
	 */
	public function getRightQuote()
	{
		return '"';
	}

	/**
	 * @inheritdoc
	 */
	public function getAliasLength()
	{
		return 63;
	}

	/**
	 * @inheritdoc
	 */
	public function quote($identifier)
	{
		return pg_escape_identifier($this->connection->getResource(), mb_strtolower($identifier));
	}

	/**
	 * @inheritdoc
	 */
	public function values($identifier)
	{
		return 'EXCLUDED.' . $this->quote($identifier);
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
	public function forSql($value, $maxLength = 0)
	{
		if ($maxLength > 0)
		{
			$value = mb_substr($value, 0, $maxLength);
		}

		return pg_escape_string($this->connection->getResource(), $value);
	}

	/**
	 * @inheritdoc
	 */
	public function convertToDbBinary($value)
	{
		return "'" . pg_escape_bytea($value) . "'";
		//return "E'\\\\x".bin2hex($value) . "'";
		//return "decode('".bin2hex($value)."', 'hex')";
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentDateTimeFunction()
	{
		return 'now()';
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrentDateFunction()
	{
		return 'current_date';
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
		else
		{
			$from = $from . "::timestamp";
		}

		return $from . " + cast(" . $seconds . "||' second' as interval)";
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
		else
		{
			$from = $from . "::timestamp";
		}

		return '(' . $from . " + cast(" . $days . "||' day' as interval))";
	}

	/**
	 * @inheritdoc
	 */
	public function getDatetimeToDateFunction($value)
	{
		return 'cast('.$value.' as date)';
	}

	/**
	 * @inheritdoc
	 */
	public function formatDate($format, $field = null)
	{
		static $translation  = [
			'YYYY' => 'YYYY',
			'MMMM' => 'FMMonth',
			'MI' => 'MI',
			'HH' => 'HH24',
			'GG' => 'HH12',
			'TT' => 'PM',
			'M' => 'Mon',
			'H' => 'HH12',
			'G' => 'FMHH12',
			'T' => 'PM',
			'W' => 'D',
		];

		$dbFormat = '';
		foreach (preg_split('/(YYYY|MMMM|MM|MI|DD|HH|GG|SS|TT|M|H|G|T|W)/', $format, -1, PREG_SPLIT_DELIM_CAPTURE) as $part)
		{
			$dbFormat .= $translation[$part] ?? $part;
		}

		if ($field === null)
		{
			return $dbFormat;
		}
		else
		{
			return "to_char(".$field.", '".$dbFormat."')";
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getRegexpOperator($field, $regexp)
	{
		return $field . ' ~ ' . $regexp;
	}

	/**
	 * @inheritdoc
	 */
	public function getIlikeOperator($field, $value)
	{
		return $field . ' ILIKE ' . $value;
	}

	/**
	 * @inheritdoc
	 */
	public function getConcatFunction()
	{
		return implode(" || ", func_get_args());
	}

	/**
	 * @inheritdoc
	 */
	public function getRandomFunction()
	{
		return 'random()';
	}

	/**
	 * @inheritdoc
	 */
	public function getSha1Function($field)
	{
		return "encode(digest(replace(" . $field . ", '\\', '\\\\')::bytea, 'sha1'), 'hex')";
	}

	/**
	 * @inheritdoc
	 */
	public function getIsNullFunction($expression, $result)
	{
		return "COALESCE(".$expression.", ".$result.")";
	}

	/**
	 * @inheritdoc
	 */
	public function getLengthFunction($field)
	{
		return "OCTET_LENGTH(".$field.")";
	}

	public function getMatchFunction($field, $value)
	{
		return "to_tsvector('english'::regconfig, " . $field . ") @@ to_tsquery('english'::regconfig, " . $value . ")";
	}

	public function getMatchAndExpression($values, $prefixSearch = false)
	{
		if ($prefixSearch)
		{
			foreach ($values as $i => $searchTerm)
			{
				$values[$i] = $searchTerm . ':*';
			}
		}
		return implode(' & ', $values);
	}

	public function getMatchOrExpression($values, $prefixSearch = false)
	{
		if ($prefixSearch)
		{
			foreach ($values as $i => $searchTerm)
			{
				$values[$i] = $searchTerm . ':*';
			}
		}
		return implode(' | ', $values);
	}

	/**
	 * @inheritdoc
	 */
	public function getCharToDateFunction($value)
	{
		return "timestamp '".$value."'";
	}

	/**
	 * @inheritdoc
	 */
	public function getDateToCharFunction($fieldName)
	{
		return "TO_CHAR(".$fieldName.", 'YYYY-MM-DD HH24:MI:SS')";
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
		return 'CAST('.$fieldName.' AS varchar)';
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
					return 'integer';
				case 8:
					return 'bigint';
			}
			return 'integer';
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
			return 'float8';
		}
		elseif ($field instanceof ORM\Fields\DatetimeField)
		{
			return 'timestamp';
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
				$falseLen = mb_strlen($values[0]);
				$trueLen = mb_strlen($values[1]);
				if ($falseLen === 1 && $trueLen === 1)
				{
					return 'char(1)';
				}
				return 'varchar(' . max($falseLen, $trueLen) . ')';
			}
		}
		elseif ($field instanceof ORM\Fields\EnumField)
		{
			return 'varchar('.max(array_map('mb_strlen', $field->getValues())).')';
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
		switch ($type)
		{
			case 'bigint':
			case 'int8':
			case 'bigserial':
			case 'serial8':
				$field = (new ORM\Fields\IntegerField($name))->configureSize(8);
				break;
			case 'integer':
			case 'int':
			case 'int4':
			case 'serial':
			case 'serial4':
				$field = (new ORM\Fields\IntegerField($name))->configureSize(4);
				break;
			case 'smallint':
			case 'int2':
			case 'smallserial':
			case 'serial2':
				$field = (new ORM\Fields\IntegerField($name))->configureSize(2);
				break;
			case 'double precision':
			case 'float4':
			case 'float8':
			case 'numeric':
			case 'decimal':
			case 'real':
				$field = new ORM\Fields\FloatField($name);
				break;
			case 'timestamp':
			case 'timestamp without time zone':
			case 'timestamptz':
			case 'timestamp with time zone':
				$field = new ORM\Fields\DatetimeField($name);
				break;
			case 'date':
				$field = new ORM\Fields\DateField($name);
				break;
			case 'bytea':
				$field = new ORM\Fields\StringField($name, ['binary' => true]);
				break;
			default:
				$field = new ORM\Fields\StringField($name);
		}

		$field->setConnection($this->connection);

		return $field;
	}

	/**
	 * @inheritdoc
	 */
	public function getTopSql($sql, $limit, $offset = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		if ($offset > 0 && $limit <= 0)
		{
			throw new \Bitrix\Main\ArgumentException("Limit must be set if offset is set");
		}

		if ($limit > 0)
		{
			$sql .= "\nLIMIT ".$limit;
		}

		if ($offset > 0)
		{
			$sql .= " OFFSET ".$offset;
		}

		$sql .= "\n";

		return $sql;
	}

	/**
	 * @inheritdoc
	 */
	public function getInsertIgnore($tableName, $fields, $sql)
	{
		return 'INSERT INTO ' . $tableName . $fields . $sql . ' ON CONFLICT DO NOTHING';
	}

	/**
	 * @inheritdoc
	 */
	public function getAscendingOrder()
	{
		return 'ASC NULLS FIRST';
	}

	/**
	 * @inheritdoc
	 */
	public function getDescendingOrder()
	{
		return 'DESC NULLS LAST';
	}

	/**
	 * @inheritdoc
	 */
	public function prepareMerge($tableName, array $primaryFields, array $insertFields, array $updateFields)
	{
		$insert = $this->prepareInsert($tableName, $insertFields);
		$update = $this->prepareUpdate($tableName, $updateFields);

		if (
			!empty($insert[0]) && !empty($insert[1])
			&& !empty($update[0])
			&& $primaryFields
		)
		{
			$sql = 'INSERT INTO ' . $this->quote($tableName) . ' ('.$insert[0].')
				VALUES (' . $insert[1] . ')
				ON CONFLICT (' . implode(',', array_map([$this, 'quote'], $primaryFields)) . ')
				DO UPDATE SET ' . $update[0];
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
	public function prepareMergeMultiple($tableName, array $primaryFields, array $insertRows)
	{
		$result = [];
		$head = '';
		$tail = '';
		$maxBodySize = 1024*1024; //1 Mb
		$body = [];
		$bodySize = 0;
		foreach ($insertRows as $insertFields)
		{
			$insert = $this->prepareInsert($tableName, $insertFields, true);
			if (!$head && $insert && $insert[0])
			{
				$head = 'INSERT INTO ' . $this->quote($tableName) . ' (' . implode(', ', $insert[0]) . ') VALUES ';
				$tail = ' ON CONFLICT (' . implode(',', array_map([$this, 'quote'], $primaryFields)) . ') DO UPDATE SET (' . implode(', ', $insert[0]) . ') = (' . implode(', ', array_map(function($f){return 'EXCLUDED.'.$f;}, $insert[0])) . ')';
			}
			if ($insert && $insert[1])
			{
				$values = '(' . implode(', ', $insert[1]) . ')';
				$bodySize += mb_strlen($values) + 4;
				$body[] = $values;
				if ($bodySize > $maxBodySize)
				{
					$result[] = $head.implode(', ', $body).$tail;
					$body = [];
					$bodySize = 0;
				}
			}
		}
		if ($body)
		{
			$result[] = $head.implode(', ', $body).$tail;
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function prepareMergeSelect($tableName, array $primaryFields, array $selectFields, $select, $updateFields)
	{
		$updateColumns = [];
		$updateValues = [];

		$tableFields = $this->connection->getTableFields($tableName);
		// one registry
		$tableFields = array_change_key_case($tableFields, CASE_UPPER);
		$updateFields = array_change_key_case($updateFields, CASE_UPPER);
		foreach ($updateFields as $columnName => $value)
		{
			if (isset($tableFields[$columnName]))
			{
				$updateColumns[] = $this->quote($columnName);
				$updateValues[] = $this->convertToDb($value, $tableFields[$columnName]);
			}
			else
			{
				trigger_error("Column `{$columnName}` is not found in the `{$tableName}` table", E_USER_WARNING);
			}
		}

		$sql = 'INSERT INTO ' . $this->quote($tableName) . ' (' . implode(',', array_map([$this, 'quote'], $selectFields)) . ') ';
		$sql .= $select;
		$sql .= ' ON CONFLICT (' . implode(',', array_map([$this, 'quote'], $primaryFields)) . ') DO UPDATE SET ';
		if (count($updateColumns) === 1)
		{
			$sql .=  $updateColumns[0] . ' = ' . $updateValues[0];
		}
		else
		{
			$sql .= ' (' . implode(', ', $updateColumns) . ') = (' . implode(', ', $updateValues) . ')';
		}

		return $sql;
	}

	/**
	 * @inheritdoc
	 */
	public function prepareDeleteLimit($tableName, array $primaryFields, $where, array $order, $limit)
	{
		$primaryColumns = [];
		foreach ($primaryFields as $columnName)
		{
			$primaryColumns[] = $this->quote($columnName);
		}
		$sqlPrimary = implode(', ', $primaryColumns);

		$orderColumns = [];
		foreach ($order as $columnName => $sort)
		{
			$orderColumns[] = $this->quote($columnName) . ' ' . $sort;
		}
		$sqlOrder = $orderColumns ? ' ORDER BY ' . implode(', ', $orderColumns) : '';
		return 'DELETE FROM ' . $this->quote($tableName) . ' WHERE (' . $sqlPrimary . ') IN (SELECT ' . $sqlPrimary . ' FROM ' . $this->quote($tableName) . ' WHERE ' . $where . $sqlOrder . ' LIMIT ' . intval($limit) . ')';
	}

	public function initRowNumber($variableName)
	{
		return '';
	}

	public function getRowNumber($variableName)
	{
		return 'row_number() over()';
	}

	/**
	 * @inheritdoc
	 */
	public function prepareCorrelatedUpdate($tableName, $tableAlias, $fields, $from, $where)
	{
		$dml = "UPDATE " . $tableName . ' AS ' . $tableAlias . " SET\n";

		$set = '';
		foreach ($fields as $fieldName => $fieldValue)
		{
			$set .= ($set ? ',' : '') . $fieldName . ' = ' .$fieldValue . "\n";
		}
		$dml .= $set;
		$dml .= 'FROM ' . $from . "\n";
		$dml .= 'WHERE ' . $where . "\n";

		return $dml;
	}

	protected function getOrderByField(string $field, array $values, callable $callback, bool $quote = true): string
	{
		$field = $quote ? $this->quote($field) : $field;
		$values = implode(',', array_map($callback, $values));

		return "array_position(ARRAY[{$values}], {$field})";
	}
}
