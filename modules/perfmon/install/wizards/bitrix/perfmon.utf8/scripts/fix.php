<?php
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
/** @global CUser $USER */
global $USER;

if (!$USER->isAdmin() || !check_bitrix_sessid())
{
	echo GetMessage('UTFWIZ_ERROR_ACCESS_DENIED');
	require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
	die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/wizard.php';

$lang = $_REQUEST['lang'];
if (!preg_match('/^[a-z0-9_]{2}$/i', $lang))
{
	$lang = 'en';
}

$wizard = new CWizard('bitrix:perfmon.utf8');
$wizard->IncludeWizardLang('scripts/fix.php', $lang);
require_once $_SERVER['DOCUMENT_ROOT'] . $wizard->path . '/wizard.php';

list($tableName, $tableColumn, $lastKey) = explode(':', $_REQUEST['next'] ?? '', 3);
$sourceEncoding = $_REQUEST['sourceEncoding'] ?? 'cp1251';

$connection = \Bitrix\Main\Application::getConnection();
$helper = $connection->getSqlHelper();
$columnList = $connection->query("
	select
		TABLE_NAME
		,COLUMN_NAME
		,DATA_TYPE
	from
		information_schema.COLUMNS
	where
		TABLE_SCHEMA = '" . $helper->forSql($connection->getDatabase()) . "'
		AND DATA_TYPE IN ('text', 'tinytext', 'mediumtext', 'longtext', 'blob', 'tinyblob', 'mediumblob', 'longblob')
		" . ($tableName ? "AND TABLE_NAME >= '" . $helper->forSql($tableName) . "'" : '') . '
	order by
		1, 2
');

$next = '';
$displayLinesCount = 15;
$lines = 0;
$etime = microtime(1) + 1;
while ($column = $columnList->fetch())
{
	if (
		$tableName && $tableName === $column['TABLE_NAME']
		&& $tableColumn && $column['COLUMN_NAME'] < $tableColumn)
	{
		continue;
	}

	$next = $column['TABLE_NAME'] . ':' . $column['COLUMN_NAME'] . ':';

	if (microtime(1) > $etime && $lines > 0)
	{
		break;
	}

	do
	{
		$fixedCount = fix($column['TABLE_NAME'], $column['COLUMN_NAME'], $lastKey, 1000, $column['DATA_TYPE']);
		if (microtime(1) > $etime)
		{
			break;
		}
	}
	while ($lastKey);

	if ($lines < $displayLinesCount)
	{
		echo $column['TABLE_NAME'] . '.' . $column['COLUMN_NAME'] . ': ' . htmlspecialcharsEx($lastKey) . ' (' . $sourceEncoding . ': ' . $fixedCount . ')<br/>';
	}
	$lines++;

	if ($lastKey !== false)
	{
		$next = $column['TABLE_NAME'] . ':' . $column['COLUMN_NAME'] . ':' . $lastKey;
		break;
	}
}


if ($lines > $displayLinesCount)
{
	echo GetMessage('UTFWIZ_MORE', ['#count#' => $lines - $displayLinesCount]) . '<br />';
}

if ($column)
{
	echo '<script>BX.Wizard.Utf8.action(\'fix\', ' . \Bitrix\Main\Web\Json::encode($next) . ')</script>';
}
else
{
	echo '<br />' . GetMessage('UTFWIZ_ALL_DONE');
	echo '<script>BX.Wizard.Utf8.EnableButton();</script>';
}

function getPrimary($tableName)
{
	$connection = \Bitrix\Main\Application::getConnection();
	$helper = $connection->getSqlHelper();

	$result = [];
	$indexes = $connection->query('SHOW INDEXES FROM ' . $helper->quote($tableName));
	while ($index = $indexes->fetch())
	{
		if (!$index['Non_unique'])
		{
			$result[$index['Key_name']][$index['Seq_in_index']] = $index['Column_name'];
		}
	}

	return $result ? array_shift($result) : false;
}

function isUtf($data)
{
	if (is_array($data))
	{
		foreach ($data as $key => $value)
		{
			if (!\Bitrix\Main\Text\Encoding::detectUtf8($key, false))
			{
				return false;
			}

			if (!isUtf($value))
			{
				return false;
			}
		}
	}
	elseif (is_string($data))
	{
		if (!\Bitrix\Main\Text\Encoding::detectUtf8($data, false))
		{
			return false;
		}
	}
	return true;
}

function fix($tableName, $fieldName, &$lastKey, $pageSize, $dataType)
{
	global $sourceEncoding;
	$connection = \Bitrix\Main\Application::getConnection();
	$helper = $connection->getSqlHelper();
	$counter = 0;
	$rowsProcessed = 0;

	$pkName = getPrimary($tableName);
	if ($pkName)
	{
		$isBinary = $dataType === 'blob' || $dataType === 'tinyblob' || $dataType === 'mediumblob' || $dataType === 'longblob';

		$pk = [];
		foreach ($pkName as $pkField)
		{
			$pk[] = $helper->quote($pkField) . " > '" . $helper->forSql(trim($lastKey, '')) . "'";
		}

		$q = $connection->query('
			SELECT '
				. implode(',', array_map([$helper, 'quote'], $pkName))
				. ',' . $helper->quote($fieldName)
			. ' FROM ' . $helper->quote($tableName)
			. ' WHERE 1=1'
			. ($isBinary ? '' : ' AND (' . $helper->quote($fieldName) . " LIKE 'a:%' OR " . $helper->quote($fieldName) . " LIKE 's:%')")
			. (count($pk) === 1
				? ' AND ' . $pk[0] . ' LIMIT ' . intval($pageSize)
				: ' LIMIT ' . intval($pageSize) . ' OFFSET ' . intval($lastKey)
			)
		);

		while ($row = $q->fetch())
		{
			$value = $row[$fieldName];
			if ($isBinary)
			{
				$uncompressed = @gzuncompress($value);
				if ($uncompressed !== false)
				{
					$value = $uncompressed;
				}
			}
			else
			{
				$uncompressed = false;
			}

			$data = @unserialize($value, ['allowed_classes' => CBaseUtf8WizardStep::$allowedUnserializeClassesList]);
			if ($data === false)
			{
				$decoded = \Bitrix\Main\Text\Encoding::convertEncoding($value, 'utf8', $sourceEncoding);
				$data = @unserialize($decoded, ['allowed_classes' => CBaseUtf8WizardStep::$allowedUnserializeClassesList]);
				if ($data !== false)
				{
					$fixed = serialize(\Bitrix\Main\Text\Encoding::convertEncoding($data, $sourceEncoding, 'utf8'));
					$pk = [];
					foreach ($pkName as $pkField)
					{
						$pk[] = $helper->quote($pkField) . " = '" . $helper->forSql($row[$pkField]) . "'";
					}

					if ($uncompressed)
					{
						$fixed = gzcompress($fixed);
					}

					$dml = 'UPDATE ' . $helper->quote($tableName) . ' SET ' . $helper->quote($fieldName) . "='" . $helper->forSql($fixed) . "' WHERE " . implode(' AND ', $pk);
					$connection->query($dml);
					$counter++;
				}
			}
			elseif ($uncompressed && is_array($data) && !isUtf($data))
			{
				$fixed = gzcompress(serialize(\Bitrix\Main\Text\Encoding::convertEncoding($data, $sourceEncoding, 'utf8')));
				$pk = [];
				foreach ($pkName as $pkField)
				{
					$pk[] = $helper->quote($pkField) . " = '" . $helper->forSql($row[$pkField]) . "'";
				}

				$dml = 'UPDATE ' . $helper->quote($tableName) . ' SET ' . $helper->quote($fieldName) . "='" . $helper->forSql($fixed) . "' WHERE " . implode(' AND ', $pk);
				$connection->query($dml);
				$counter++;
			}

			if (count($pkName) === 1)
			{
				reset($pkName);
				$lastKey = $row[current($pkName)];
			}
			else
			{
				$lastKey++;
			}

			$rowsProcessed++;
		}
	}

	if ($rowsProcessed < $pageSize)
	{
		$lastKey = false;
	}

	return $counter;
}

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_after.php';
