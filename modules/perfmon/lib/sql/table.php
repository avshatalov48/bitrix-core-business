<?php
namespace Bitrix\Perfmon\Sql;

use Bitrix\Main\NotSupportedException;

class Table extends BaseObject
{
	public $checkExists = false;
	/** @var Collection */
	public $columns = null;
	/** @var Collection */
	public $indexes = null;
	/** @var Collection */
	public $constraints = null;
	/** @var Collection */
	public $triggers = null;

	/**
	 * @param string $name Index name.
	 */
	public function __construct($name = '')
	{
		parent::__construct($name);
		$this->columns = new Collection;
		$this->indexes = new Collection;
		$this->constraints = new Collection;
		$this->triggers = new Collection;
	}

	/**
	 * Creates trigger object from tokens.
	 * <p>
	 * And registers trigger in the table trigger registry.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 *
	 * @return Table
	 * @see Constraint::create
	 */
	public function createTrigger(Tokenizer $tokenizer)
	{
		$trigger = Trigger::create($tokenizer);
		$trigger->setParent($this);
		$this->triggers->add($trigger);
		return $this;
	}

	/**
	 * Creates constraint object from tokens.
	 * <p>
	 * And registers constraint in the table constraint registry.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 * @param string $constraintName Optional name of the constraint.
	 *
	 * @return Table
	 * @see Constraint::create
	 */
	public function createConstraint(Tokenizer $tokenizer, $constraintName = '')
	{
		$constraint = Constraint::create($tokenizer, $constraintName);
		$constraint->setParent($this);
		$this->constraints->add($constraint);
		return $this;
	}

	/**
	 * Creates index object from tokens.
	 * <p>
	 * And registers index in the table index registry.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 * @param bool $unique Uniqueness flag.
	 * @param bool $fulltext Fulltext flag.
	 * @param string $indexName Optional name of the index.
	 *
	 * @return Table
	 * @see Index::create
	 */
	public function createIndex(Tokenizer $tokenizer, $unique = false, $fulltext = false, $indexName = '')
	{
		$index = Index::create($tokenizer, $unique, $fulltext, $indexName);
		$index->setParent($this);
		$this->indexes->add($index);
		return $this;
	}

	/**
	 * Creates column object from tokens.
	 * <p>
	 * And registers column in the table column registry.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 *
	 * @return Table
	 * @see Column::create
	 */
	public function createColumn(Tokenizer $tokenizer)
	{
		$column = Column::create($tokenizer, $this);
		$this->columns->add($column);
		return $this;
	}

	/**
	 * Alters column object from tokens.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 *
	 * @return Table
	 * @see Column::create
	 */
	public function modifyColumn(Tokenizer $tokenizer)
	{
		$column = Column::create($tokenizer);
		if ($column !== null)
		{
			$column->setParent($this);
			$columnIndex = $this->columns->searchIndex($column->name);
			if ($columnIndex === null)
			{
				throw new NotSupportedException('Column ' . $this->name . '.' . $column->name . ' not found line:' . $tokenizer->getCurrentToken()->line);
			}
			$this->columns->set($columnIndex, $column);
		}
		return $this;
	}

	/**
	 * Creates table object from tokens.
	 * <p>
	 * Current position should point to the name of the sequence or 'if not exists' clause.
	 *
	 * @param Tokenizer $tokenizer Tokens collection.
	 *
	 * @return Table
	 * @throws NotSupportedException
	 */
	public static function create(Tokenizer $tokenizer)
	{
		$checkExists = false;
		$tokenizer->skipWhiteSpace();

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
				$checkExists = true;
			}
		}

		$table = new Table($tokenizer->getCurrentToken()->text);
		$table->checkExists = $checkExists;

		$tokenizer->nextToken();
		$tokenizer->skipWhiteSpace();

		if ($tokenizer->testText('('))
		{
			$tokenizer->skipWhiteSpace();

			$token = $tokenizer->getCurrentToken();
			$level = $token->level;
			do
			{
				if (
					$tokenizer->testUpperText('INDEX')
					|| $tokenizer->testUpperText('KEY')
				)
				{
					$tokenizer->skipWhiteSpace();
					$table->createIndex($tokenizer, false);
				}
				elseif ($tokenizer->testUpperText('UNIQUE'))
				{
					$tokenizer->skipWhiteSpace();
					if ($tokenizer->testUpperText('KEY'))
					{
						$tokenizer->skipWhiteSpace();
					}
					elseif ($tokenizer->testUpperText('INDEX'))
					{
						$tokenizer->skipWhiteSpace();
					}
					$table->createIndex($tokenizer, true);
				}
				elseif ($tokenizer->testUpperText('FULLTEXT'))
				{
					$tokenizer->skipWhiteSpace();
					if ($tokenizer->testUpperText('KEY'))
					{
						$tokenizer->skipWhiteSpace();
					}
					elseif ($tokenizer->testUpperText('INDEX'))
					{
						$tokenizer->skipWhiteSpace();
					}
					$table->createIndex($tokenizer, false, true);
				}
				elseif ($tokenizer->testUpperText('PRIMARY'))
				{
					$tokenizer->skipWhiteSpace();
					if (!$tokenizer->testUpperText('KEY'))
					{
						throw new NotSupportedException("'KEY' expected. line:" . $tokenizer->getCurrentToken()->line);
					}

					$tokenizer->putBack(); //KEY
					$tokenizer->putBack(); //WS
					$tokenizer->putBack(); //PRIMARY
					$table->createConstraint($tokenizer, false);
				}
				elseif ($tokenizer->testUpperText('CONSTRAINT'))
				{
					$tokenizer->skipWhiteSpace();
					$constraintName = $tokenizer->getCurrentToken()->text;

					$tokenizer->nextToken();
					$tokenizer->skipWhiteSpace();

					if ($tokenizer->testUpperText('PRIMARY') || $tokenizer->testUpperText('UNIQUE'))
					{
						$tokenizer->putBack();
						$table->createConstraint($tokenizer, $constraintName);
					}
					elseif ($tokenizer->testUpperText('FOREIGN'))
					{
						$tokenizer->putBack();
						$table->createConstraint($tokenizer, $constraintName);
					}
					else
					{
						throw new NotSupportedException("'PRIMARY KEY' expected. line:" . $tokenizer->getCurrentToken()->line);
					}
				}
				elseif ($tokenizer->testUpperText(')'))
				{
					break;
				}
				else
				{
					$table->createColumn($tokenizer);
				}

				$tokenizer->skipWhiteSpace();

				$token = $tokenizer->getCurrentToken();

				if ($token->level == $level && $token->text === ',')
				{
					$token = $tokenizer->nextToken();
				}
				elseif ($token->level < $level && $token->text === ')')
				{
					$tokenizer->nextToken();
					break;
				}
				else
				{
					throw new NotSupportedException("',' or ')' expected got (" . $token->text . '). line:' . $token->line);
				}

				$tokenizer->skipWhiteSpace();
			}
			while (!$tokenizer->endOfInput() && $token->level >= $level);

			$suffix = '';
			while (!$tokenizer->endOfInput())
			{
				$suffix .= $tokenizer->getCurrentToken()->text;
				$tokenizer->nextToken();
			}
			if ($suffix)
			{
				$table->setBody($suffix);
			}
		}
		else
		{
			throw new NotSupportedException("'(' expected. line:" . $tokenizer->getCurrentToken()->line);
		}

		return $table;
	}

	/**
	 * Return DDL for table creation.
	 *
	 * @param string $dbType Database type (MYSQL, ORACLE or MSSQL).
	 *
	 * @return array|string
	 */
	public function getCreateDdl($dbType = '')
	{
		$result = [];

		$items = [];
		/** @var Column $column */
		foreach ($this->columns->getList() as $column)
		{
			$items[] = $column->name . ' ' . $column->body;
		}

		if ($dbType === 'MYSQL')
		{
			/** @var Index $index */
			foreach ($this->indexes->getList() as $index)
			{
				$items[] = ($index->fulltext ? 'FULLTEXT ' : '') . ($index->unique ? 'UNIQUE ' : '') . 'KEY ' . $index->name . ' (' . $index->body . ')';
			}
		}

		/** @var Constraint $constraint */
		foreach ($this->constraints->getList() as $constraint)
		{
			if ($constraint->name === '')
			{
				$items[] = $constraint->body;
			}
			else
			{
				$items[] = 'CONSTRAINT ' . $constraint->name . ' ' . $constraint->body;
			}
		}

		$result[] = 'CREATE TABLE ' . ($this->checkExists ? 'IF NOT EXISTS ' : '') . $this->name . "(\n\t" . implode(",\n\t", $items) . "\n)" . $this->body;

		if ($dbType !== 'MYSQL')
		{
			foreach ($this->indexes->getList() as $index)
			{
				$result[] = $index->getCreateDdl($dbType);
			}
		}

		return $result;
	}

	/**
	 * Return DDL for table destruction.
	 *
	 * @param string $dbType Database type (MYSQL, ORACLE or MSSQL).
	 *
	 * @return array|string
	 */
	public function getDropDdl($dbType = '')
	{
		return 'DROP TABLE ' . $this->name;
	}
}
