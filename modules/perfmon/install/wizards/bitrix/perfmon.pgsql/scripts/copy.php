<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
/** @global CUser $USER */
global $USER;

if (!$USER->isAdmin() || !check_bitrix_sessid() || !CModule::IncludeModule('perfmon'))
{
	echo GetMessage('PGWIZ_ERROR_ACCESS_DENIED');
	require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
	die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/wizard.php';

$lang = $_REQUEST['lang'];
if (!preg_match('/^[a-z0-9_]{2}$/i', $lang))
{
	$lang = 'en';
}

$wizard = new CWizard('bitrix:perfmon.pgsql');
$wizard->IncludeWizardLang('scripts/convert.php', $lang);
require_once $_SERVER['DOCUMENT_ROOT'] . $wizard->path . '/wizard.php';

$myConnection = \Bitrix\Main\Application::getConnection();
$pgConnection = \Bitrix\Main\Application::getConnection($_REQUEST['connection']);

$error = '';
$ddl = '';
$displayLinesCount = 15;
$lines = 0;
$etime = microtime(1) + 5;
$pageSize = 1000;

if ($_REQUEST['next'] === 'init')
{
	$next = CBasePgWizardStep::MakeTables('', $etime);
	if ($next === '')
	{
		$next = 'continue';
	}
}
elseif ($_REQUEST['next'] === 'continue')
{
	CBasePgWizardStep::installMainAddons($pgConnection);
	$next = '';
}
elseif ($_REQUEST['next'])
{
	$next = CBasePgWizardStep::MakeTables($_REQUEST['next'], $etime);
	if ($next === '')
	{
		$next = 'continue';
	}
}
else
{
	$next = '';
	while ($tableInfo = CBasePgWizardStep::getNextTable())
	{
		try
		{
			if ($tableInfo['LAST_ID'] == '')
			{
				CreateTable($myConnection, $pgConnection, $tableInfo['TABLE_NAME']);
				if ($tableInfo['TABLE_NAME'] === 'b_perf_table')
				{
					$myConnection->query('DELETE FROM b_perf_table WHERE ID = ' . $tableInfo['ID']);
					continue;
				}
			}

			$tableColumns = GetTableColumns($myConnection, $tableInfo['TABLE_NAME']);
			$fullTextColumns = GetFullTextColumns($pgConnection, $tableInfo['TABLE_NAME']);

			$i = intval($tableInfo['REC_COUNT']);
			$di = 0;
			$last_id = '';
			$strInsert = '';
			if ($tableInfo['KEY_COLUMN'] <> '')
			{
				$strSelect = '
					SELECT *
					FROM ' . $tableInfo['TABLE_NAME'] . '
					' . ($tableInfo['LAST_ID'] <> '' ? 'WHERE ' . $tableInfo['KEY_COLUMN'] . " > '" . $tableInfo['LAST_ID'] . "'" : '') . '
					ORDER BY ' . $tableInfo['KEY_COLUMN'] . '
					LIMIT ' . $pageSize . '
				';
			}
			else
			{
				$strSelect = '
					SELECT *
					FROM ' . $tableInfo['TABLE_NAME'] . '
					LIMIT ' . ($tableInfo['LAST_ID'] <> '' ? $tableInfo['LAST_ID'] . ', ' : '') . $pageSize . '
				';
			}
			$rsSource = $myConnection->query($strSelect);
			$insertValues = [];
			while ($arSource = $rsSource->fetchRaw())
			{
				$i++;
				$di++;

				foreach ($arSource as $key => $value)
				{
					if (!isset($value) || is_null($value))
					{
						$arSource[$key] = 'NULL';
					}
					elseif ($tableColumns[$key] == 0)
					{
						$arSource[$key] = $value;
					}
					elseif ($tableColumns[$key] == 1)
					{
						if (empty($value) && $value != '0')
						{
							$arSource[$key] = '\'\'';
						}
						else
						{
							$arSource[$key] = "decode('" . bin2hex($value) . "', 'hex')";
						}
					}
					elseif ($tableColumns[$key] == 2)
					{
						$value = str_replace('0000-00-00', '0001-01-01', $value);
						$arSource[$key] = "'" . $pgConnection->getSqlHelper()->forSql($value) . "'";
					}
					elseif ($tableColumns[$key] == 3)
					{
						if (array_key_exists($key, $fullTextColumns))
						{
							$arSource[$key] = "'" . $pgConnection->getSqlHelper()->forSql($value, 900000) . "'";
						}
						else
						{
							$arSource[$key] = "'" . $pgConnection->getSqlHelper()->forSql($value) . "'";
						}
					}
				}
				$insertValues[] = '(' . implode(',', $arSource) . ')';

				if ($tableInfo['KEY_COLUMN'])
				{
					$last_id = $arSource[$tableInfo['KEY_COLUMN']];
				}
				else
				{
					$last_id = $i;
				}
			}

			if ($insertValues)
			{
				$pgConnection->query('insert into ' . $tableInfo['TABLE_NAME'] . ' values ' . implode(',', $insertValues));
				$myConnection->query('
						UPDATE b_perf_table
						SET LAST_ID = ' . $last_id . '
						,REC_COUNT = ' . $i . "
						WHERE ID = '" . $tableInfo['ID'] . "'
				");
			}

			if ($di < $pageSize)
			{
				$myConnection->query('DELETE FROM b_perf_table WHERE ID = ' . $tableInfo['ID']);

				if ($lines < $displayLinesCount)
				{
					echo $tableInfo['TABLE_NAME'] . ' (' . ($tableInfo['REC_COUNT'] + $di) . ')<br />';
				}

				$lines++;
			}
		}
		catch (\Bitrix\Main\DB\SqlQueryException $e)
		{
			$error = $e->getMessage() . ' ' . $e->getQuery();
			break;
		}

		if (microtime(1) > $etime)
		{
			if ($lines < $displayLinesCount && $di === $pageSize)
			{
				echo $tableInfo['TABLE_NAME'] . ' (' . ($tableInfo['REC_COUNT'] + $di) . ')<br />';
			}

			$lines++;
			break;
		}
	}
}

if ($lines > $displayLinesCount)
{
	echo GetMessage('PGWIZ_MORE', ['#count#' => $lines - $displayLinesCount]) . '<br />';
}

if ($error)
{
	echo '<br />' . GetMessage('PGWIZ_TABLE_COPY_ERROR') . '<br />';
	echo '<p class="pgwiz_err">' . htmlspecialcharsEx($ddl) . '</p><p class="pgwiz_err">' . htmlspecialcharsEx($error) . '</p>';
	echo GetMessage('PGWIZ_TABLE_COPY_ERROR_ADVICE') . '<br />';
}
else
{
	$tablesToCopy = CBasePgWizardStep::getTables();
	if ($tablesToCopy)
	{
		echo '<br />' . GetMessage('PGWIZ_TABLE_PROGRESS', ['#tables#' => count($tablesToCopy)]) . '<br />';
		echo '<script>BX.Wizard.PgSql.action(\'copy\', \'' . $next . '\')</script>';
	}
	else
	{
		//Uninstall not supported modules
		foreach (\Bitrix\Main\ModuleManager::getInstalledModules() as $moduleId => $_)
		{
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/mysql'))
			{
				if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/pgsql'))
				{
					CBasePgWizardStep::deleteModule($pgConnection, $moduleId);
				}
			}
			elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/db/mysql'))
			{
				if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/db/pgsql'))
				{
					CBasePgWizardStep::deleteModule($pgConnection, $moduleId);
				}
			}
		}

		echo '<br />' . GetMessage('PGWIZ_ALL_DONE');
		echo '<script>BX.Wizard.PgSql.EnableButton();</script>';
	}
}

function GetTableColumns($myConnection, $tableName)
{
	$columns = [];

	$sql = 'SHOW COLUMNS FROM ' . $myConnection->getSqlHelper()->quote($tableName);
	$res = $myConnection->query($sql);
	while ($row = $res->fetch())
	{
		if (preg_match('/^(\w*int|year|float|double|decimal)/i', $row['Type']))
		{
			$columns[$row['Field']] = 0;
		}
		elseif (preg_match('/^(\w*(binary|blob))/i', $row['Type']))
		{
			$columns[$row['Field']] = 1;
		}
		elseif (preg_match('/^(\w*(date|time))/i', $row['Type']))
		{
			$columns[$row['Field']] = 2;
		}
		else
		{
			$columns[$row['Field']] = 3;
		}
	}

	return $columns;
}

function GetFullTextColumns($pgConnection, $tableName)
{
	$fullTextColumns = [];

	$sql = "
		SELECT relname, indkey, pg_get_expr(pg_index.indexprs, pg_index.indrelid) full_text
		FROM pg_class, pg_index
		WHERE pg_class.oid = pg_index.indexrelid
		AND pg_class.oid IN (
			SELECT indexrelid
			FROM pg_index, pg_class
			WHERE pg_class.relname = '" . $pgConnection->getSqlHelper()->forSql($tableName) . "'
			AND pg_class.oid = pg_index.indrelid
		)
	";
	$res = $pgConnection->query($sql);
	while ($row = $res->fetch())
	{
		if ($row['FULL_TEXT'])
		{
			$match = [];
			if (preg_match_all('/,\s*([a-z0-9_]+)/i', $row['FULL_TEXT'], $match))
			{
				foreach ($match[1] as $i => $colName)
				{
					$fullTextColumns[mb_strtoupper($colName)] = true;
				}
			}
		}
	}

	return $fullTextColumns;
}

function unquote($identifier)
{
	return trim($identifier, '`');
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

function CreateTable($myConnection, $pgConnection, $tableName)
{
	$myHelper = $myConnection->getSqlHelper();
	$pgHelper = $pgConnection->getSqlHelper();

	$ar = $myConnection->query('show create table ' . $myHelper->quote($tableName))->fetch();
	if ($ar && $ar['Create Table'] != '')
	{
		//todo: Unsupported statement by Perfmon\Sql\Schema
		$sql = str_replace('USING BTREE', ' ', $ar['Create Table']);

		$s = new \Bitrix\Perfmon\Sql\Schema;
		$s->createFromString($sql, ';');
		/** @var \Bitrix\Perfmon\Sql\Table $table */
		foreach ($s->tables->getList() as $table)
		{
			$pgConnection->query('DROP TABLE IF EXISTS ' . $pgHelper->quote(unquote($table->name)));

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
					$pgConnection->query('DROP TYPE IF EXISTS ' . $enumType);
					$pgConnection->query('CREATE TYPE ' . $enumType . " AS ENUM ('" . implode("', '", $column->enum) . "')");
					$columnDefinition .= ' ' . $enumType;
				}
				else
				{
					$columnDefinition .= ' ' . $type . ($hasAutoIncrement ? ' GENERATED BY DEFAULT AS IDENTITY' : '');
				}

				if (!$column->nullable || $hasAutoIncrement)
				{
					$columnDefinition .= ' NOT NULL';
				}

				if ($column->type === 'TIMESTAMP')
				{
					$columnDefinition .= ' DEFAULT CURRENT_TIMESTAMP';
				}
				elseif (!is_null($column->default) && strlen($column->default) > 0)
				{
					$default = str_replace('"', "'", $column->default);
					$default = str_replace("'0000-00-00 00:00:00'", '', $default);
					$default = str_replace('NOW', 'CURRENT_TIMESTAMP', $default);
					$default = str_replace('now', 'CURRENT_TIMESTAMP', $default);
					$default = str_replace('false', '0', $default);
					$default = trim($default, " \t\n\r");
					if ($default !== '')
					{
						$columnDefinition .= ' DEFAULT ' . $default;
					}
				}
				$inset[] = $columnDefinition;
			}
			/** @var \Bitrix\Perfmon\Sql\Constraint $constraint */
			foreach ($table->constraints->getList() as $constraint)
			{
				if (preg_match('/^PRIMARY/i', $constraint->body) > 0)
				{
					$inset[] = 'PRIMARY KEY ('
						. implode(', ', array_map(
							function($x)
							{
								return trim(unquote(preg_replace('/\s+(desc|asc)/i', '', preg_replace('/\(\d+\)/', '', $x))), " \t\n\r");
							}, $constraint->columns))
						. ')'
					;
				}
				elseif (preg_match('/^UNIQUE/i', $constraint->body) > 0)
				{
					$inset[] = 'UNIQUE ('
						. implode(', ', array_map(
							function($x)
							{
								return unquote($x);
							}, $constraint->columns))
						. ')'
					;
				}
			}

			if ($inset)
			{
				$ddl = 'CREATE TABLE ' . unquote($table->name) . " (\n";

				$c = count($inset) - 1;
				foreach ($inset as $i => $line)
				{
					$ddl .= '  ' . $line . ($i < $c ? ',' : '') . "\n";
				}

				$ddl .= ')';
				$pgConnection->query($ddl);
			}

			if ($autoIncrementValue)
			{
				$pgConnection->query('ALTER TABLE ' . unquote($table->name) . ' ALTER COLUMN ' . $autoIncrementColumn . ' RESTART WITH ' . $autoIncrementValue);
			}

			$indexes = [];
			/** @var \Bitrix\Perfmon\Sql\Index $index */
			foreach ($table->indexes->getList() as $index)
			{
				$indexName = substr(
					($index->unique ? 'ux_' : ($index->fulltext ? 'tx_' : 'ix_'))
					. unquote($table->name)
					. '_'
					. implode('_', array_map(
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

				if ($index->fulltext)
				{
					$pgConnection->query('CREATE INDEX ' . $indexName
						. ' ON ' . unquote($table->name)
						. " USING GIN (to_tsvector('english', " . implode(' || ', array_map(
							function($x)
							{
								return strtolower(unquote(preg_replace('/\s*(\(\d+\)|asc|desc)(?![a-z0-9_])\s*/i', '', $x)));
							}, $index->columns))
						. '))'
					);
				}
				else
				{
					$pgConnection->query('CREATE' . ($index->unique ? ' UNIQUE ' : ' ') . 'INDEX ' . $indexName
						. ' ON ' . unquote($table->name)
						. ' (' . implode(', ', array_map(
							function($x)
							{
								return strtolower(unquote(preg_replace('/\s*(\(\d+\)|asc|desc)(?![a-z0-9_])\s*/i', '', $x)));
							}, $index->columns))
						. ')'
					);
				}
			}
		}
	}
}

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
