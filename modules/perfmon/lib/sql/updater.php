<?php
namespace Bitrix\Perfmon\Sql;

use Bitrix\Main\NotSupportedException;
use Bitrix\Perfmon\Php;

class Updater
{
	protected $dbType = '';
	protected $delimiter = '';
	/** @var \Bitrix\Perfmon\Sql\Table  */
	protected $tableCheck = null;
	protected $columns = [];

	/** @var \Bitrix\Perfmon\Php\Statement[]*/
	protected  $statements = [];

	/**
	 * Sets database type. Currently supported:
	 * - MYSQL
	 * - ORACLE
	 * - MSSQL
	 *
	 * @param string $dbType Database type.
	 *
	 * @return Updater
	 */
	public function setDbType($dbType = '')
	{
		$this->dbType = (string)$dbType;
		return $this;
	}

	/**
	 * Sets DDL delimiter for parsing.
	 *
	 * @param string $delimiter DDL statements delimiter.
	 *
	 * @return Updater
	 */
	public function setDelimiter($delimiter = '')
	{
		$this->delimiter = (string)$delimiter;
		return $this;
	}

	/**
	 * Returns array of generated statements.
	 *
	 * @return \Bitrix\Perfmon\Php\Statement[]
	 */
	public function getStatements()
	{
		return $this->statements;
	}

	/**
	 * Produces updater code.
	 *
	 * @param string $sourceSql Source DDL statements.
	 * @param string $targetSql Target DDL statements.
	 *
	 * @return string
	 * @throws NotSupportedException
	 */
	public function generate($sourceSql, $targetSql)
	{
		$source = new Schema;
		$source->createFromString($sourceSql, $this->delimiter);

		$target = new Schema;
		$target->createFromString($targetSql, $this->delimiter);

		$diff = Compare::diff($source ,$target);
		if ($diff)
		{
			$sourceTables = $source->tables->getList();
			if ($sourceTables)
			{
				$this->tableCheck = array_shift($sourceTables);
			}
			else
			{
				$targetTables = $target->tables->getList();
				if ($targetTables)
				{
					$this->tableCheck = array_shift($targetTables);
				}
				else
				{
					$this->tableCheck = null;
				}
			}

			if (!$this->tableCheck)
			{
				throw new NotSupportedException('no CHECK TABLE found.');
			}

			return $this->handle($diff);
		}
		else
		{
			return '';
		}
	}

	/**
	 * @param array $diff Difference pairs.
	 *
	 * @return string
	 */
	protected function handle(array $diff)
	{
		$this->columns = [];
		foreach ($diff as $pair)
		{
			if (!isset($pair[0]))
			{
				$this->handleCreate($pair[1]);
			}
			elseif (!isset($pair[1]))
			{
				$this->handleDrop($pair[0]);
			}
			else
			{
				$this->handleChange($pair[0], $pair[1]);
			}
		}

		foreach ($this->columns as $columns)
		{
			$ddl = ''; $predicate2 = [];
			foreach ($columns as $column)
			{
				$predicate2[] = $column[0];
				if ($ddl)
				{
					$ddl .= ', ' . preg_replace('/^ALTER TABLE [^ ]+ /', '', $column[1]);
				}
				else
				{
					$ddl = $column[1];
				}
			}

			$stmt = $this->createStatement('$DB->Query("', $ddl, '");');
			$stmt->dependOn = $columns[0][2];
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($columns[0][3]);
			$stmt->addCondition($columns[0][0]);

			$this->statements[] = $stmt;
		}

		$updaterSteps = $this->getStatements();
		$codeTree = new \Bitrix\Perfmon\Php\CodeTree($updaterSteps);
		$result = $codeTree->getCode(0);

		return $result;
	}

	/**
	 * @param BaseObject $object Database schema object.
	 *
	 * @return void
	 */
	protected function handleCreate(BaseObject $object)
	{
		$stmt = null;

		if ($object instanceof Sequence || $object instanceof Procedure)
		{
			$ddl = $object->getCreateDdl($this->dbType);

			$stmt = $this->createStatement('$DB->Query("', $ddl, '", true);');
			$stmt->dependOn = $this->tableCheck->getLowercasedName();
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition('$updater->TableExists("' . EscapePHPString($this->tableCheck->getLowercasedName()) . '")');
		}
		elseif ($object instanceof Table)
		{
			$ddl = $object->getCreateDdl($this->dbType);
			$predicate = '!$updater->TableExists("' . EscapePHPString($object->name) . '")';
			$cond = "\tif (${predicate})\n";

			$stmt = $this->createStatement('$DB->Query("', $ddl, '", true);');
			$stmt->tableName = $object->getLowercasedName();
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			if ($this->tableCheck->getLowercasedName() !== $object->getLowercasedName())
			{
				$stmt->addCondition('$updater->TableExists("' . EscapePHPString($this->tableCheck->getLowercasedName()) . '")');
			}
			$stmt->addCondition('!$updater->TableExists("' . EscapePHPString($object->getLowercasedName()) . '")');
		}
		elseif ($object instanceof Column)
		{
			$ddl = $object->getCreateDdl($this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($object->parent->getLowercasedName()) . '")';
			$cond = "\t\tif (${predicate})\n";
			$predicate2 = '!$DB->Query("SELECT ' . EscapePHPString($object->name) . ' FROM ' . EscapePHPString($object->parent->getLowercasedName()) . ' WHERE 1=0", true)';

			$this->columns[$cond][] = [$predicate2, $ddl, $object->parent->getLowercasedName(), $predicate, $predicate2];
		}
		elseif ($object instanceof Index)
		{
			$ddl = $object->getCreateDdl($this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($object->parent->getLowercasedName()) . '")';
			$predicate2 = '!$DB->IndexExists("' . EscapePHPString($object->parent->getUnquotedName()) . '", array(' . $this->multiLinePhp('"', $object->getUnquotedName($object->columns), '", ') . '), true)';

			$stmt = $this->createStatement('$DB->Query("', $ddl, '");');
			$stmt->dependOn = $object->parent->getLowercasedName();
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($predicate);
			$stmt->addCondition($predicate2);
		}
		elseif ($object instanceof Trigger || $object instanceof Constraint)
		{
			$ddl = $object->getCreateDdl($this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($object->parent->getLowercasedName()) . '")';

			$stmt = $this->createStatement('$DB->Query("', $ddl, '", true);');
			$stmt->dependOn = $object->parent->getLowercasedName();
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($predicate);
		}
		else
		{
			$stmt = $this->createStatement('', '//create for ' . get_class($object) . ' not supported yet', '');
		}

		if ($stmt)
		{
			$this->statements[] = $stmt;
		}
	}

	/**
	 * @param BaseObject $object Database schema object.
	 *
	 * @return void
	 */
	protected function handleDrop(BaseObject $object)
	{
		$stmt = false;

		if ($object instanceof Sequence || $object instanceof Procedure)
		{
			$ddl = $object->getDropDdl($this->dbType);

			$stmt = $this->createStatement('$DB->Query("', $ddl, '", true);');
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition('$updater->TableExists("' . EscapePHPString($this->tableCheck->getLowercasedName()) . '")');
		}
		elseif ($object instanceof Table)
		{
			$ddl = $object->getDropDdl($this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($object->getLowercasedName()) . '")';

			$stmt = $this->createStatement('$DB->Query("', $ddl, '");');
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($predicate);
		}
		elseif ($object instanceof Column)
		{
			$ddl = $object->getDropDdl($this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($object->parent->name) . '")';
			$cond = "\t\tif (${predicate})\n";
			$predicate2 = '$DB->Query("SELECT ' . EscapePHPString($object->name) . ' FROM ' . EscapePHPString($object->parent->getLowercasedName()) . ' WHERE 1=0", true)';

			$this->columns[$cond][] = [$predicate2, $ddl, $object->parent->getLowercasedName(), $predicate, $predicate2];
		}
		elseif ($object instanceof Index)
		{
			$ddl = $object->getDropDdl($this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($object->parent->getLowercasedName()) . '")';
			$predicate2 = '$DB->IndexExists("' . EscapePHPString($object->parent->getUnquotedName()) . '", array(' . $this->multiLinePhp('"', $object->getUnquotedName($object->columns), '", ') . '), true)';

			$stmt = $this->createStatement('$DB->Query("', $ddl, '");');
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($predicate);
			$stmt->addCondition($predicate2);
		}
		elseif ($object instanceof Trigger || $object instanceof Constraint)
		{
			$ddl = $object->getDropDdl($this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($object->parent->getLowercasedName()) . '")';

			$stmt = $this->createStatement('$DB->Query("', $ddl, '", true);');
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($predicate);
		}
		else
		{
			$stmt = $this->createStatement('', '//drop for ' . get_class($object) . ' not supported yet', '');
		}

		if ($stmt)
		{
			$this->statements[] = $stmt;
		}
	}

	/**
	 * @param BaseObject $source Source object.
	 * @param BaseObject $target Target object.
	 *
	 * @return void
	 */
	protected function handleChange(BaseObject $source, BaseObject $target)
	{
		$stmt = null;

		if ($source instanceof Sequence || $source instanceof Procedure)
		{
			$dropStmt = $this->createStatement('$DB->Query("', $source->getDropDdl($this->dbType), '", true);');
			$createStmt = $this->createStatement('$DB->Query("', $target->getCreateDdl($this->dbType), '", true);');
			$stmt = new Php\Statement;
			$stmt->dependOn = $this->tableCheck->getLowercasedName();
			$stmt->merge($dropStmt);
			$stmt->merge($createStmt);
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition('$updater->TableExists("' . EscapePHPString($this->tableCheck->getLowercasedName()) . '")');
		}
		elseif ($target instanceof Column)
		{
			$ddl = $source->getModifyDdl($target, $this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($source->parent->getLowercasedName()) . '")';
			$cond = "\t\tif (${predicate})\n";
			$predicate2 = '$DB->Query("SELECT ' . EscapePHPString($source->name) . ' FROM ' . EscapePHPString($source->parent->getLowercasedName()) . ' WHERE 1=0", true)';

			$this->columns[$cond][] = [$predicate2, $ddl, $source->parent->getLowercasedName(), $predicate, $predicate2];
		}
		elseif ($source instanceof Index)
		{
			$predicate = '$updater->TableExists("' . EscapePHPString($source->parent->getLowercasedName()) . '")';
			$predicate2 = '$DB->IndexExists("' . EscapePHPString($source->parent->getUnquotedName()) . '", array(' . $this->multiLinePhp('"', $source->getUnquotedName($source->columns), '", ') . '), true)';

			$dropStmt = $this->createStatement('$DB->Query("', $source->getDropDdl($this->dbType), '", true);');
			$createStmt = $this->createStatement('$DB->Query("', $target->getCreateDdl($this->dbType), '", true);');
			$stmt = new Php\Statement;
			$stmt->dependOn = $source->parent->getLowercasedName();
			$stmt->merge($dropStmt);
			$stmt->merge($createStmt);
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($predicate);
			$stmt->addCondition($predicate2);
			$stmt->addCondition('!$DB->IndexExists("' . EscapePHPString($target->parent->getUnquotedName()) . '", array(' . $this->multiLinePhp('"', $source->getUnquotedName($source->columns), '", ') . '), true)');
		}
		elseif ($source instanceof Trigger || $source instanceof Constraint)
		{
			$ddl = $source->getModifyDdl($target, $this->dbType);
			$predicate = '$updater->TableExists("' . EscapePHPString($source->parent->getLowercasedName()) . '")';

			$stmt = $this->createStatement('$DB->Query("', $ddl, '", true);');
			$stmt->dependOn = $source->parent->getLowercasedName();
			$stmt->addCondition('$updater->CanUpdateDatabase()');
			$stmt->addCondition('$DB->type == "' . EscapePHPString($this->dbType) . '"');
			$stmt->addCondition($predicate);
		}
		else
		{
			$stmt = $this->createStatement('', '//change for ' . get_class($source) . ' not supported yet', '');
		}

		if ($stmt)
		{
			$this->statements[] = $stmt;
		}
	}

	/**
	 * Returns escaped php code repeated for body? prefixed with $prefix and suffixed with $suffix.
	 *
	 * @param string $prefix Prefix string for each from body.
	 * @param array|string $body Strings to be escaped.
	 * @param string $suffix Suffix string for each from body.
	 *
	 * @return string
	 */
	protected function multiLinePhp($prefix, $body, $suffix)
	{
		$result  = [];
		if (is_array($body))
		{
			foreach ($body as $line)
			{
				$result[] = $prefix . EscapePHPString($line) . $suffix;
			}
		}
		else
		{
			$result[] = $prefix . EscapePHPString($body) . $suffix;
		}
		return implode('', $result);
	}

	/**
	 * Returns Php\Statement object with escaped php code repeated for body? prefixed with $prefix and suffixed with $suffix.
	 *
	 * @param string $prefix Prefix string for each from body.
	 * @param array|string $body Strings to be escaped.
	 * @param string $suffix Suffix string for each from body.
	 *
	 * @return \Bitrix\Perfmon\Php\Statement
	 */
	protected function createStatement($prefix, $body, $suffix)
	{
		$result  = new Php\Statement;
		if (is_array($body))
		{
			foreach ($body as $line)
			{
				$result->addLine($prefix . EscapePHPString($line) . $suffix);
			}
		}
		else
		{
			$result->addLine($prefix . EscapePHPString($body) . $suffix);
		}
		return $result;
	}
}
