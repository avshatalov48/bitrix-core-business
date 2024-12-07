#!/usr/bin/php
<?php
namespace Bitrix\Main;

//utf8: 21844 * 3 = 65532
//utf8mb4: 21844 * 4 = 87376 # too much
//utf8mb4: 16383 * 4 = 65532
define('MAX_VARCHAR_LEN', 16383);
define('CHAR_WIDTH', 4);
define('CHARSET', 'utf8mb4');
define('COLLATION', 'utf8mb4_0900_ai_ci');

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING & ~E_USER_WARNING & ~E_COMPILE_WARNING);

class NotSupportedException extends \Exception {}

spl_autoload_register(
	function ($class_name)
	{
		if (strpos(strtolower($class_name), 'bitrix\\perfmon\\') === 0)
		{
			$file_name = substr($class_name, strlen('bitrix\\perfmon\\'));
			$file_name = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $file_name));
			$file_name = str_replace('\\', '/', $file_name);
			require_once __DIR__ . '/../../perfmon/lib/' . $file_name . '.php';
		}
	}
);

$files = [];
$mysqldump = '';
$host = '';
$user = '';
$password = '';
$databases = [];
$table = '';

for ($i = 1, $c = count($argv); $i < $c; $i++)
{
	if (preg_match("/^--file=(.+)\$/", $argv[$i], $match))
	{
		$files[] = $match[1];
	}
	elseif (preg_match("/^--mysqldump=(.+)\$/", $argv[$i], $match))
	{
		$mysqldump = $match[1];
	}
	elseif (preg_match("/^--host=(.+)\$/", $argv[$i], $match))
	{
		$host = $match[1];
	}
	elseif (preg_match("/^--user=(.+)\$/", $argv[$i], $match))
	{
		$user = $match[1];
	}
	elseif (preg_match("/^--password=(.+)\$/", $argv[$i], $match))
	{
		$password = $match[1];
	}
	elseif (preg_match("/^--database=(.+)\$/", $argv[$i], $match))
	{
		$databases[] = $match[1];
	}
	elseif (preg_match("/^--table=(.+)\$/", $argv[$i], $match))
	{
		$table = $match[1];
	}
	else
	{
		$files[] = $argv[$i];
	}
}

if ($mysqldump)
{
	$f = fopen($mysqldump, 'r');
	if ($f)
	{
		echo "SET client_min_messages TO WARNING;\n";

		$match = [];
		$tableName = '';
		$ddl = '';
		while ($line = fgets($f))
		{
			if (preg_match('/^CREATE TABLE `(.*?)` /', $line, $match))
			{
				$tableName = $match[1];
				$ddl = $line;
			}
			elseif ($tableName)
			{
				$ddl .= $line;
				if (preg_match('/^\)/', $line))
				{
					//echo $ddl;
					echo generate_schema_ddl($ddl, $table, true, $mysqldump);
					$tableName = '';
					$ddl = '';
				}
			}
			elseif (preg_match("/^(?:INSERT|REPLACE) INTO `(.*?)` VALUES (.+);\s*\$/", $line, $match))
			{
				$rows = parse_values($match[2]);
				foreach ($rows as $row)
				{
					echo 'INSERT INTO "' . $match[1] . '" VALUES ' . $row . ";\n";
				}
			}
		}
	}
}

if ($files)
{
	foreach ($files as $file_name)
	{
		$sql = file_get_contents($file_name, "r");
		echo generate_schema_ddl($sql, $table, false, $file_name);
	}
}

if ($host || $user || $password)
{
	echo "SET client_min_messages TO WARNING;\n";

	$dbh = new \PDO("mysql:host=".$host.";port=3306", $user, $password);
	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	if (!$databases)
	{
		$r = $dbh->query("show databases");
		while ($a = $r->fetch(\PDO::FETCH_ASSOC))
		{
			if (
				$a['Database'] !== 'information_schema'
				&& $a['Database'] !== 'mysql'
				&& $a['Database'] !== 'performance_schema'
				&& $a['Database'] !== 'sys'
			)
			{
				$databases[] = $a['Database'];
			}
		}
	}

	foreach ($databases as $db_name)
	{
		$dbh->exec("use ".$db_name);
		$sql = '';
		$r = $dbh->query("show tables");
		while ($a = $r->fetch(\PDO::FETCH_ASSOC))
		{
			$r2 = $dbh->query("show create table `".$a['Tables_in_' . $db_name]."`");
			$a2 = $r2->fetch(\PDO::FETCH_ASSOC);
			if (isset($a2['Table']))
			{
				$sql .= $a2['Create Table'] . ";\n";
			}
		}
		echo generate_schema_ddl($sql, $table, true, $db_name);
	}
}

function unquote($identifier)
{
	return trim($identifier, "`");
}

function convertColumnType($columnType, $length, $unsigned)
{
	switch ($columnType)
	{
		case 'TINYINT':
		case 'SMALLINT':
			return $unsigned ? 'int' : 'smallint';
		case 'BOOL':
		case 'BOOLEAN':
			return 'smallint';
		case 'MEDIUMINT':
		case 'INT':
		case 'INTEGER':
			return $unsigned ? 'int8' : 'int';
		case 'BIGINT':
			return 'int8';
		case 'DECIMAL':
		case 'NUMERIC':
			return strtolower($columnType);
		case 'FLOAT':
			return 'real';
		case 'DOUBLE':
			return 'double precision';
		case 'CHAR':
			return 'char(' . $length . ')';
		case 'VARCHAR':
			return 'varchar(' . $length . ')';
		case 'VARBINARY':
		case 'MEDIUMBLOB':
		case 'LONGBLOB':
		case 'BLOB':
			return 'bytea';
		case 'TEXT':
		case 'TINYTEXT':
		case 'MEDIUMTEXT':
		case 'LONGTEXT':
			return 'text';
		case 'DATE':
			return 'date';
		case 'DATETIME':
		case 'TIMESTAMP':
			return 'timestamp';
		case 'ENUM':
			return 'enum';
		default:
			return '//unknown type ' . $columnType;
	}
}

function generate_schema_ddl($sql, $tableFilter, $isDump, $source)
{
	$ddl = "";
	$match = [];

	//todo: Unsupported statement by Perfmon\Sql\Schema
	$sql = str_replace('USING BTREE', ' ', $sql);

	$s = new \Bitrix\Perfmon\Sql\Schema;
	$s->createFromString($sql, ';');
	/** @var \Bitrix\Perfmon\Sql\Table $table */
	foreach ($s->tables->getList() as $table)
	{
		if ($tableFilter && unquote($table->name) !== $tableFilter)
		{
			continue;
		}

		if ($isDump)
		{
			$ddl .= "DROP TABLE IF EXISTS " . unquote($table->name) . ";\n";
		}

		$autoIncrementValue = null;
		$autoIncrementColumn = '';
		$inset = [];
		/** @var \Bitrix\Perfmon\Sql\Column $column */
		foreach ($table->columns->getList() as $column)
		{
			$columnDefinition = unquote($column->name);
			if ($columnDefinition === 'OFFSET' || $columnDefinition === 'KEY')
			{
				$columnDefinition = '"' . strtolower($columnDefinition) . '"';
			}

			$hasAutoIncrement = preg_match('/AUTO_INCREMENT/i', $column->body) > 0;
			if ($hasAutoIncrement && preg_match('/AUTO_INCREMENT=(\d+)/', $table->body, $match))
			{
				$autoIncrementValue = $match[1];
				$autoIncrementColumn = unquote($column->name);
			}
			$type = convertColumnType($column->type, $column->length, $column->unsigned);

			if ($type === 'enum' && $column->enum)
			{
				$enumType = 't_' . preg_replace('/^b_/i', '', unquote($table->name)) . '_' . unquote($column->name);
				$ddl .= 'DROP TYPE IF EXISTS ' . $enumType . ";\n";
				$ddl .= 'CREATE TYPE ' . $enumType . " AS ENUM ('" . implode("', '", $column->enum) . "');\n";
				$columnDefinition .= ' ' . $enumType;
				fwrite(STDERR, "Warning: " . $source . ": " . $table->name . '.' . $column->name ." is enum. Convert to char.\n");
			}
			else
			{
				$columnDefinition .= ' ' . $type . ($hasAutoIncrement ? ' GENERATED BY DEFAULT AS IDENTITY' : '');
			}

			if (!$column->nullable || $hasAutoIncrement)
			{
				$columnDefinition .= ' NOT NULL';
			}

			if ($column->unsigned)
			{
				fwrite(STDERR, "Notice: " . $source . ": " . $table->name . '.' . $column->name ." is unsigned. Consider to convert to wider type and remove unsigned defunition.\n");
			}

			if ($column->type === 'TIMESTAMP')
			{
				$columnDefinition .= " DEFAULT CURRENT_TIMESTAMP";
				fwrite(STDERR, "Warning: " . $source . ": " . $table->name . '.' . $column->name ." is timestamp. Convert to datetime.\n");
			}
			elseif (!is_null($column->default) && strlen($column->default) > 0)
			{
				$default = str_replace('"', "'", $column->default);
				$default = str_replace("'0000-00-00 00:00:00'", "", $default);
				$default = str_replace('NOW', "CURRENT_TIMESTAMP", $default);
				$default = str_replace('now', "CURRENT_TIMESTAMP", $default);
				$default = str_replace('false', "0", $default);
				$default = trim($default, " \t\n\r");
				if ($default !== '')
				{
					$columnDefinition .= " DEFAULT " . $default;
				}
			}
			$inset[] = $columnDefinition;
		}

		/** @var \Bitrix\Perfmon\Sql\Constraint $constraint */
		foreach ($table->constraints->getList() as $constraint)
		{
			if (preg_match('/^PRIMARY/i', $constraint->body) > 0)
			{
				$inset[] = "PRIMARY KEY (" . implode(", ", array_map(
					function($x)
					{
						return trim(unquote(preg_replace('/\s+(desc|asc)/i', '', preg_replace('/\(\d+\)/', '', $x))), " \t\n\r");
					}, $constraint->columns)) . ")";
			}
			elseif (preg_match('/^UNIQUE/i', $constraint->body) > 0)
			{
				$inset[] = "UNIQUE (" . implode(", ", array_map(
					function($x)
					{
						return unquote($x);
					}, $constraint->columns)) . ")";
			}
		}

		if ($inset)
		{
			$ddl .= "\nCREATE TABLE " . unquote($table->name) . " (\n";

			$c = count($inset) - 1;
			foreach ($inset as $i => $line)
			{
				$ddl .= "  " . $line . ($i < $c ? "," : "") . "\n";
			}

			$ddl .= ");\n";
		}

		if ($autoIncrementValue && $isDump)
		{
			$ddl .= "ALTER TABLE " . unquote($table->name) . " ALTER COLUMN " . $autoIncrementColumn . " RESTART WITH " . $autoIncrementValue . ";\n";
		}

		$indexes = [];
		/** @var \Bitrix\Perfmon\Sql\Index $index */
		foreach ($table->indexes->getList() as $index)
		{
			$indexName = substr(
				($index->unique ? "ux_" : ($index->fulltext ? "tx_" : "ix_"))
				. unquote($table->name)
				. "_"
				. implode("_", array_map(
					function($x)
					{
						return strtolower(unquote(preg_replace('/\s*(\(\d+\)|asc|desc)(?![a-z0-9_])\s*/i', '', $x)));
					}, $index->columns))
				, 0, 63);
			if (array_key_exists($indexName, $indexes))
			{
				$i = ++$indexes[$indexName];
				$suffix = '_' . $i;
				$indexName = substr($indexName, 0, -strlen($suffix)) . $suffix;
			}
			else
			{
				$indexes[$indexName] = 0;
			}

			if ($isDump)
			{
				$ddl .= "DROP INDEX IF EXISTS " . $indexName . ";\n";
			}

			if ($index->fulltext)
			{
				$ddl .= "CREATE INDEX " . $indexName . " ON " . unquote($table->name) . " USING GIN (to_tsvector('english', " . implode(" || ", array_map(
					function($x)
					{
						return strtolower(unquote(preg_replace('/\s*(\(\d+\)|asc|desc)(?![a-z0-9_])\s*/i', '', $x)));
					}, $index->columns)) . "));\n";
			}
			else
			{
				$ddl .= "CREATE" . ($index->unique ? " UNIQUE " : " ") . "INDEX " . $indexName . " ON " . unquote($table->name) . " (" . implode(", ", array_map(
					function($x)
					{
						return strtolower(unquote(preg_replace('/\s*(\(\d+\)|asc|desc)(?![a-z0-9_])\s*/i', '', $x)));
					}, $index->columns)) . ");\n";
			}
		}
	}
	return $ddl;
}

function parse_values($values_str)
{
	static $search = ['\\\'', '\\"'];
	static $replace = ['\'\'', '"'];
	$result = [];
	$tokens = token_get_all('<?php '.$values_str);
	$row = [];
	$c = count($tokens);
	for ($i = 1; $i < $c; $i++)
	{
		$token = $tokens[$i];
		if (
			($token == ',' || $token == ';')
			&& ($tokens[$i-1] == ')')
		)
		{
			if ($row)
			{
				$result[] = '('.implode('', $row).')';
			}
			$row = [];
		}
		elseif ($token == '(' || $token == ')')
		{
			//skip
		}
		elseif (is_array($token))
		{
			if ($token[0] === T_CONSTANT_ENCAPSED_STRING)
			{
				$escaped = str_replace($search, $replace, $token[1]);
				if ($escaped === "'0000-00-00 00:00:00'")
				{
					$escaped = "NULL";
				}
				elseif (
					preg_match('/\\\\[bfnrt\']/', $escaped)
					|| strpos($escaped, '\\\\') !== false
				)
				{
					$escaped = 'E' . $escaped;
				}
				$row[] = $escaped;
			}
			elseif (preg_match('/^0x[0-9A-F]+$/', $token[1]))
			{
				$row[] = 'decode(\'' . substr($token[1], 2) . '\', \'hex\')';
			}
			else
			{
				$row[] = $token[1];
			}
		}
		else
		{
			$row[] = $token;
		}
	}
	if ($row)
	{
		$result[] = '('.implode('', $row).')';
	}
	return $result;
}
