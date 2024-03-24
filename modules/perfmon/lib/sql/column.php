<?php
namespace Bitrix\Perfmon\Sql;

use Bitrix\Main\NotSupportedException;

class Column extends BaseObject
{
	/** @var Table|null */
	public $parent = null;
	public $type = '';
	public $typeAddition = '';
	public $unsigned = false;
	public $length = '';
	public $precision = 0;
	public $nullable = true;
	public $default = null;
	public $enum = [];

	protected static $types = [
		'BIGINT' => true,
		'BINARY' => true,
		'BLOB' => true,
		'BOOLEAN' => true,
		'BYTEA' => true,
		'CHAR' => true,
		'DATE' => true,
		'DATETIME' => true,
		'DECIMAL' => true,
		'DOUBLE' => true,
		'ENUM' => true,
		'FLOAT' => true,
		'INT' => true,
		'INT8' => true,
		'INTEGER' => true,
		'LONGBLOB' => true,
		'LONGTEXT' => true,
		'MEDIUMBLOB' => true,
		'MEDIUMINT' => true,
		'MEDIUMTEXT' => true,
		'NUMBER' => true,
		'NUMERIC' => true,
		'REAL' => true,
		'SET' => true,
		'SMALLINT' => true,
		'TEXT' => true,
		'TIME' => true,
		'TIMESTAMP' => true,
		'TINYBLOB' => true,
		'TINYINT' => true,
		'TINYTEXT' => true,
		'VARBINARY' => true,
		'VARCHAR' => true,
	];

	/**
	 * Checks the $type against type list:
	 * - INT
	 * - INTEGER
	 * - TINYINT
	 * - NUMERIC
	 * - NUMBER
	 * - FLOAT
	 * - DOUBLE
	 * - DECIMAL
	 * - BIGINT
	 * - SMALLINT
	 * - MEDIUMINT
	 * - VARCHAR
	 * - CHAR
	 * - TIMESTAMP
	 * - DATETIME
	 * - DATE
	 * - TIME
	 * - TEXT
	 * - LONGTEXT
	 * - MEDIUMTEXT
	 * - TINYTEXT
	 * - BLOB
	 * - MEDIUMBLOB
	 * - LONGBLOB
	 * - TINYBLOB
	 * - BINARY
	 * - VARBINARY
	 * - ENUM
	 * - SET
	 * - BOOLEAN
	 *
	 * @param string $type Type of a column.
	 *
	 * @return bool
	 */
	public static function checkType($type)
	{
		return isset(self::$types[$type]);
	}

	/**
	 * Returns storage size for the column.
	 *
	 * @param int $charWidth Collation defined maximum character size in bytes.
	 * @param int $maxLength Overwrite column definition length.
	 *
	 * @return int
	 * @throws NotSupportedException
	 */
	public function getLength($charWidth, $maxLength = null)
	{
		$length = $maxLength ?? intval($this->length);
		static $fixed = [
			'INT' => 4,
			'INTEGER' => 4,
			'TINYINT' => 1,
			'FLOAT' => 4,
			'DOUBLE' => 8,
			'BIGINT' => 8,
			'SMALLINT' => 2,
			'MEDIUMINT' => 3,
			'TIMESTAMP' => 4,
			'DATETIME' => 8,
			'YEAR' => 1,
			'DATE' => 3,
			'TIME' => 3,
			'NUMERIC' => 4, //up to
			'NUMBER' => 4, //up to
			'DECIMAL' => 4, //up to
			'ENUM' => 2, //up to
			'SET' => 8, //up to
			'BOOLEAN' => 1,
		];
		if (isset($fixed[$this->type]))
		{
			return $fixed[$this->type];
		}
		if ($this->type === 'BINARY')
		{
			return $length;
		}
		if ($this->type === 'VARBINARY')
		{
			return $length + ($length > 255 ? 2 : 1);
		}
		if ($this->type === 'TINYBLOB' || $this->type === 'TINYTEXT')
		{
			return ($length ?: pow(2, 8)) + 1;
		}
		if ($this->type === 'BLOB' || $this->type === 'TEXT')
		{
			return ($length ?: pow(2, 16)) + 2;
		}
		if ($this->type === 'MEDIUMBLOB' || $this->type === 'MEDIUMTEXT')
		{
			return ($length ?: pow(2, 24)) + 3;
		}
		if ($this->type === 'LONGBLOB' || $this->type === 'LONGTEXT')
		{
			return ($length ?: pow(2, 32)) + 3;
		}
		if ($this->type === 'CHAR')
		{
			return $length * $charWidth;
		}
		if ($this->type === 'VARCHAR')
		{
			return ($length * $charWidth) + ($length > 255 ? 2 : 1);
		}
		throw new NotSupportedException('column type [' . $this->type . '].');
	}

	/**
	 * Creates column object from tokens.
	 * <p>
	 * Current position should point to the name of the column.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 *
	 * @return Column
	 * @throws NotSupportedException
	 */
	public static function create(Tokenizer $tokenizer)
	{
		$columnName = $tokenizer->getCurrentToken()->text;

		$tokenizer->nextToken();
		$tokenizer->skipWhiteSpace();
		$token = $tokenizer->getCurrentToken();

		$columnType = $token->upper;
		if (!self::checkType($columnType))
		{
			throw new NotSupportedException('column type expected but [' . $tokenizer->getCurrentToken()->text . '] found. line: ' . $tokenizer->getCurrentToken()->line);
		}

		$column = new self($columnName);
		$column->type = $columnType;

		$level = $token->level;
		$lengthLevel = -1;
		$columnDefinition = '';
		do
		{
			if ($token->level == $level && $token->text === ',')
			{
				break;
			}
			if ($token->level < $level && $token->text === ')')
			{
				break;
			}

			$columnDefinition .= $token->text;

			if ($token->upper === 'NOT')
			{
				$column->nullable = false;
			}
			elseif ($token->upper === 'DEFAULT')
			{
				$column->default = false;
			}
			elseif ($token->upper === 'UNSIGNED')
			{
				$column->unsigned = true;
			}
			elseif ($token->upper === 'PRECISION')
			{
				$column->typeAddition = $token->upper;
			}
			elseif ($token->upper === 'VARYING')
			{
				$column->typeAddition = $token->upper;
			}
			elseif ($column->default === false)
			{
				if ($token->type !== Token::T_WHITESPACE && $token->type !== Token::T_COMMENT)
				{
					$column->default = $token->text;
				}
			}

			$token = $tokenizer->nextToken();

			//parentheses after type
			if ($lengthLevel == -1)
			{
				if ($token->text === '(')
				{
					if ($column->type === 'ENUM')
					{
						$lengthLevel = $token->level;
						while (!$tokenizer->endOfInput())
						{
							$columnDefinition .= $token->text;

							$token = $tokenizer->nextToken();

							if ($token->level === $lengthLevel && $token->text === ')')
							{
								break;
							}

							if ($token->type == Token::T_SINGLE_QUOTE)
							{
								$column->enum[] = trim($token->text, "'");
							}
							elseif ($token->type == Token::T_DOUBLE_QUOTE)
							{
								$column->enum[] = trim($token->text, '"');
							}
						}
					}
					else
					{
						$lengthLevel = $token->level;
						while (!$tokenizer->endOfInput())
						{
							$columnDefinition .= $token->text;

							$token = $tokenizer->nextToken();

							if ($token->level === $lengthLevel && $token->text === ')')
							{
								break;
							}

							if ($token->type == Token::T_STRING)
							{
								if (!$column->length)
								{
									$column->length = (int)$token->text;
								}
								else
								{
									$column->precision = (int)$token->text;
								}
							}
						}
					}
				}
				elseif ($token->type !== Token::T_WHITESPACE && $token->type !== Token::T_COMMENT)
				{
					$lengthLevel = 0;
				}
			}
		}
		while (!$tokenizer->endOfInput());

		$column->setBody($columnDefinition);

		return $column;
	}

	/**
	 * Returns DDL presentation of column data type.
	 *
	 * @return string
	 */
	public function getDdlType()
	{
		return $this->type
			. ($this->typeAddition ? ' ' . $this->typeAddition : '')
			. ($this->length !== '' ? '(' . $this->length . ($this->precision !== 0 ? ',' . $this->precision : '') . ')' : '');
	}

	/**
	 * Return DDL for table column creation.
	 *
	 * @param string $dbType Database type (MYSQL, ORACLE or MSSQL).
	 *
	 * @return array|string
	 */
	public function getCreateDdl($dbType = '')
	{
		switch ($dbType)
		{
		case 'MYSQL':
		case 'MSSQL':
			return 'ALTER TABLE ' . $this->parent->name . ' ADD ' . $this->name . ' ' . $this->body;
		case 'PGSQL':
			return 'ALTER TABLE ' . $this->parent->name . ' ADD COLUMN ' . $this->name . ' ' . $this->body;
		case 'ORACLE':
			return 'ALTER TABLE ' . $this->parent->name . ' ADD (' . $this->name . ' ' . $this->body . ')';
		default:
			return '// ' . get_class($this) . ':getCreateDdl for database type [' . $dbType . '] not implemented';
		}
	}

	/**
	 * Return DDL for column destruction.
	 *
	 * @param string $dbType Database type (MYSQL, ORACLE or MSSQL).
	 *
	 * @return array|string
	 */
	public function getDropDdl($dbType = '')
	{
		switch ($dbType)
		{
		case 'MYSQL':
			return 'ALTER TABLE ' . $this->parent->name . ' DROP ' . $this->name;
		case 'MSSQL':
		case 'PGSQL':
			return 'ALTER TABLE ' . $this->parent->name . ' DROP COLUMN ' . $this->name;
		case 'ORACLE':
			return 'ALTER TABLE ' . $this->parent->name . ' DROP (' . $this->name . ')';
		default:
			return '// ' . get_class($this) . ':getDropDdl for database type [' . $dbType . '] not implemented';
		}
	}

	/**
	 * Return DDL for object modification.
	 * <p>
	 * Implemented only for MySQL database. For Oracle or MS SQL returns commentary.
	 *
	 * @param BaseObject $target Column object.
	 * @param string $dbType Database type (MYSQL, ORACLE or MSSQL).
	 *
	 * @return array|string
	 */
	public function getModifyDdl(BaseObject $target, $dbType = '')
	{
		/** @var $target Column */
		switch ($dbType)
		{
		case 'MYSQL':
			return 'ALTER TABLE ' . $this->parent->name . ' CHANGE ' . $this->name . ' ' . $target->name . ' ' . $target->body;
		case 'PGSQL':
			$alter = [];
			$sourceType = $this->getDdlType();
			$targetType = $target->getDdlType();
			if ($sourceType !== $targetType)
			{
				$alter[] = 'ALTER COLUMN ' . $this->name . ' TYPE ' . $targetType;
			}

			if ($this->default === null)
			{
				if ($target->default !== null)
				{
					$alter[] = 'ALTER COLUMN ' . $this->name . ' SET DEFAULT ' . $target->default;
				}
			}
			else
			{
				if ($target->default === null)
				{
					$alter[] = 'ALTER COLUMN ' . $this->name . ' DROP DEFAULT';
				}
				elseif ($this->default != $target->default)
				{
					$alter[] = 'ALTER COLUMN ' . $this->name . ' SET DEFAULT ' . $target->default;
				}
			}

			if ($this->nullable != $target->nullable)
			{
				$alter[] = 'ALTER COLUMN ' . $this->name . ' ' . ($target->nullable ? 'DROP' : 'SET') . ' NOT NULL ';
			}

			if ($alter)
			{
				return 'ALTER TABLE ' . $this->parent->name . ' ' . implode(', ', $alter);
			}
			else
			{
				return '// ' . get_class($this) . ':getModifyDdl for database type [' . $dbType . "] not implemented. Change requested from [${this}->body] to [${target}->body].";
			}
		case 'MSSQL':
			if ($this->nullable !== $target->nullable)
			{
				$nullDdl = ($target->nullable ? ' NULL' : ' NOT NULL');
			}
			else
			{
				$nullDdl = '';
			}

			if (
				$this->type === $target->type
				&& $this->default === $target->default
				&& (
					intval($this->length) < intval($target->length)
					|| (
						intval($target->length) < intval($this->length)
						&& mb_strtoupper($this->type) === 'CHAR'
					)
				)
			)
			{
				$sql = [];
				/** @var $index Index */
				foreach ($this->parent->indexes->getList() as $index)
				{
					if (in_array($this->name, $index->columns, true))
					{
						$sql[] = $index->getDropDdl($dbType);
					}
				}
				$sql[] = 'ALTER TABLE ' . $this->parent->name . ' ALTER COLUMN ' . $this->name . ' ' . $target->body . $nullDdl;
				foreach ($this->parent->indexes->getList() as $index)
				{
					if (in_array($this->name, $index->columns, true))
					{
						$sql[] = $index->getCreateDdl($dbType);
					}
				}
				return $sql;
			}
			elseif (
				$this->type === $target->type
				&& $this->default === $target->default
				&& intval($this->length) === intval($target->length)
				&& $this->nullable !== $target->nullable
			)
			{
				return 'ALTER TABLE ' . $this->parent->name . ' ALTER COLUMN ' . $this->name . ' ' . $target->body;
			}
			else
			{
				return '// ' . get_class($this) . ':getModifyDdl for database type [' . $dbType . "] not implemented. Change requested from [${this}->body] to [${target}->body].";
			}
		case 'ORACLE':
			if (
				$this->type === $target->type
				&& $this->default === $target->default
				&& (
					intval($this->length) < intval($target->length)
					|| (
						intval($target->length) < intval($this->length)
						&& mb_strtoupper($this->type) === 'CHAR'
					)
				)
			)
			{
				return 'ALTER TABLE ' . $this->parent->name . ' MODIFY (' . $this->name . ' ' . $target->type . '(' . $target->length . ')' . ')';
			}
			elseif (
				$this->type === $target->type
				&& $this->default === $target->default
				&& intval($this->length) === intval($target->length)
				&& $this->nullable !== $target->nullable
			)
			{
				return "
					declare
						l_nullable varchar2(1);
					begin
						select nullable into l_nullable
						from user_tab_columns
						where table_name = '" . $this->parent->name . "'
						and   column_name = '" . $this->name . "';
						if l_nullable = '" . ($target->nullable ? 'N' : 'Y') . "' then
							execute immediate 'alter table " . $this->parent->name . ' modify (' . $this->name . ' ' . ($target->nullable ? 'NULL' : 'NOT NULL') . ")';
						end if;
					end;
				";
			}
			else
			{
				return '// ' . get_class($this) . ':getModifyDdl for database type [' . $dbType . "] not implemented. Change requested from [${this}->body] to [${target}->body].";
			}
		default:
			return '// ' . get_class($this) . ':getModifyDdl for database type [' . $dbType . "] not implemented. Change requested from [${this}->body] to [${target}->body].";
		}
	}
}
