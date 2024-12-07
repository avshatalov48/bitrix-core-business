<?php
namespace Bitrix\Perfmon\Sql;

use Bitrix\Main\NotSupportedException;

class Index extends BaseObject
{
	public $unique = false;
	public $fulltext = false;
	public $using = '';
	public $columns = [];

	/**
	 * @param string $name Index name.
	 * @param bool $unique Uniqueness flag.
	 * @param bool $fulltext Fulltext flag.
	 */
	public function __construct($name = '', $unique, $fulltext=false)
	{
		parent::__construct($name);
		$this->unique = (bool)$unique;
		$this->fulltext = (bool)$fulltext;
	}

	/**
	 * Adds column to the index definition.
	 *
	 * @param string $name Column name.
	 *
	 * @return Index
	 */
	public function addColumn($name)
	{
		$this->columns[] = trim($name);
		$this->setBody(implode(', ', $this->columns));
		return $this;
	}

	/**
	 * Creates index object from tokens.
	 * <p>
	 * If parameter $indexName is not passed then current position should point to the name of the index.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 * @param bool $unique Uniqueness flag.
	 * @param bool $fulltext Fulltext flag.
	 * @param string $indexName Optional name of the index.
	 *
	 * @return Index
	 * @throws NotSupportedException
	 */
	public static function create(Tokenizer $tokenizer, $unique = false, $fulltext = false, $indexName = '')
	{
		if (!$indexName)
		{
			if ($tokenizer->testUpperText('IF'))
			{
				$tokenizer->skipWhiteSpace();

				if ($tokenizer->testUpperText('NOT'))
				{
					$tokenizer->skipWhiteSpace();
				}

				if ($tokenizer->testUpperText('EXISTS'))
				{
					$tokenizer->skipWhiteSpace();
				}
			}

			if ($tokenizer->getCurrentToken()->text !== '(')
			{
				$indexName = $tokenizer->getCurrentToken()->text;
				$tokenizer->nextToken();
				$tokenizer->skipWhiteSpace();
			}
		}

		if ($tokenizer->testUpperText('ON'))
		{
			$tokenizer->skipWhiteSpace();
			$tokenizer->nextToken();
			$tokenizer->skipWhiteSpace();
		}

		if ($tokenizer->testUpperText('USING'))
		{
			$tokenizer->skipWhiteSpace();
			$indexType = $tokenizer->getCurrentToken()->text;
			if (strtoupper($indexType) !== 'GIN')
			{
				throw new NotSupportedException("'GIN' expected. line:" . $tokenizer->getCurrentToken()->line);
			}
			$fulltext = true;
			$tokenizer->nextToken();
			$tokenizer->skipWhiteSpace();
		}

		$index = new self($indexName, $unique, $fulltext);

		if ($tokenizer->testText('('))
		{
			$tokenizer->skipWhiteSpace();
			$token = $tokenizer->getCurrentToken();
			$level = $token->level;
			$column = '';
			do
			{
				if ($token->text === ',')
				{
					$index->addColumn($column);
					$column = '';
				}
				else
				{
					$column .= $token->text;
				}
				$token = $tokenizer->nextToken();
			}
			while (!$tokenizer->endOfInput() && $token->level >= $level);

			if ($column)
			{
				$index->addColumn($column);
			}

			if (!$tokenizer->testText(')'))
			{
				throw new NotSupportedException("')' expected. line:" . $tokenizer->getCurrentToken()->line);
			}

			//USING BTREE
			$tokenizer->skipWhiteSpace();
			if ($tokenizer->testText('USING'))
			{
				$tokenizer->skipWhiteSpace();
				$token = $tokenizer->nextToken();
				$index->using = $token->text;
			}
		}
		else
		{
			throw new NotSupportedException("'(' expected. line:" . $tokenizer->getCurrentToken()->line);
		}

		return $index;
	}

	/**
	 * Searches token collection for 'ON' keyword.
	 * <p>
	 * Advances current position on to next token skipping whitespace.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 *
	 * @return void
	 * @throws NotSupportedException
	 */
	public static function searchTableName(Tokenizer $tokenizer)
	{
		$lineToken = $tokenizer->getCurrentToken();
		while (!$tokenizer->endOfInput())
		{
			if ($tokenizer->getCurrentToken()->upper === 'ON')
			{
				$tokenizer->nextToken();
				$tokenizer->skipWhiteSpace();
				return;
			}
			$tokenizer->nextToken();
		}
		throw new NotSupportedException('Index: table name not found. line: ' . $lineToken->line);
	}

	/**
	 * Return DDL for index creation.
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
			return 'CREATE ' . ($this->fulltext ? 'FULLTEXT ' : '') . ($this->unique ? 'UNIQUE ' : '') . 'INDEX ' . $this->name . ' ON ' . $this->parent->name . '(' . $this->body . ')';
		case 'PGSQL':
			return 'CREATE ' . ($this->unique ? 'UNIQUE ' : '') . 'INDEX ' . $this->name . ' ON ' . $this->parent->name . ($this->fulltext ? ' USING GIN (' . $this->body . ')' : '(' . $this->body . ')');
		default:
			return '// ' . get_class($this) . ':getDropDdl for database type [' . $dbType . '] not implemented';
		}
	}

	/**
	 * Return DDL for index destruction.
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
		case 'MSSQL':
			return 'DROP INDEX ' . $this->name . ' ON ' . $this->parent->name;
		case 'ORACLE':
		case 'PGSQL':
			return 'DROP INDEX ' . $this->name;
		default:
			return '// ' . get_class($this) . ':getDropDdl for database type [' . $dbType . '] not implemented';
		}
	}

	/**
	 * Return DDL for index modification.
	 *
	 * @param BaseObject $target Target object.
	 * @param string $dbType Database type (MYSQL, ORACLE or MSSQL).
	 *
	 * @return array|string
	 */
	public function getModifyDdl(BaseObject $target, $dbType = '')
	{
		return [
			$this->getDropDdl($dbType),
			$target->getCreateDdl($dbType),
		];
	}
}
