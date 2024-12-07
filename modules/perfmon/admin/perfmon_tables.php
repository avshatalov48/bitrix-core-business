<?php

/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CMain $APPLICATION */

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\StringHelper;

const ADMIN_MODULE_NAME = 'perfmon';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

Loader::includeModule('perfmon');
IncludeModuleLangFile(__FILE__);

/** @var \Bitrix\Main\HTTPRequest $request */
$request = Main\Context::getCurrent()->getRequest();

$connectionName = $request['connection'] ?: 'default';
$connection = \Bitrix\Main\Application::getConnection($connectionName);
$sqlHelper = $connection->getSqlHelper();

$RIGHT = CMain::GetGroupRight('perfmon');
if ($RIGHT == 'D')
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$engines = [
	'MYISAM' => ['NAME' => 'MyISAM'],
	'INNODB' => ['NAME' => 'InnoDB'],
];

$tableID = 't_perfmon_all_tables';
$sort = new CAdminSorting($tableID, 'TABLE_NAME', 'asc');
$lAdmin = new CAdminList($tableID, $sort);
$by = mb_strtoupper($sort->getField());
$order = mb_strtolower($sort->getOrder());

if (
	$request->get('orm') === 'y'
	&& Main\Config\Option::get('perfmon', 'enable_tablet_generator') !== 'Y'
)
{
	Main\Config\Option::set('perfmon', 'enable_tablet_generator', 'Y', '');
}

$tables = $lAdmin->GroupAction();
if (isset($_REQUEST['action']) && $tables && $RIGHT >= 'W')
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$data = CPerfomanceTableList::GetList(false, $connection);
		while ($ar = $data->Fetch())
		{
			$tables[] = $ar['TABLE_NAME'];
		}
	}

	foreach ($engines as $id => $ar)
	{
		if ($_REQUEST['action'] === 'convert_to_' . $id)
		{
			$_REQUEST['action'] = 'convert';
			$request['to'] = $id;
			break;
		}
	}

	$to = mb_strtoupper($request['to']);

	$action = $lAdmin->GetAction();
	foreach ($tables as $table_name)
	{
		$table_name = (string) $table_name;
		if ($table_name === '')
		{
			continue;
		}

		$status = [];
		if ($connection->getType() === 'mysql')
		{
			$res = $connection->query("show table status like '" . $sqlHelper->forSql($table_name) . "'");
			$status = $res->fetch();
			if (!$status || $status['Comment'] === 'VIEW')
			{
				continue;
			}
		}

		switch ($action)
		{
		case 'convert':
			try
			{
				if ($to !== mb_strtoupper($status['Engine']))
				{
					if ($to === 'MYISAM')
					{
						$connection->query('alter table ' . $sqlHelper->quote($table_name) . ' ENGINE = MyISAM');
					}
					elseif ($to === 'INNODB')
					{
						$connection->query('alter table ' . $sqlHelper->quote($table_name) . ' ENGINE = InnoDB');
					}
				}
			}
			catch (\Bitrix\Main\DB\SqlQueryException $e)
			{
				$lAdmin->AddGroupError($e, $table_name);
			}
			break;
		case 'optimize':
			try
			{
				if ($connection->getType() === 'mysql')
				{
					$connection->query('optimize table ' . $sqlHelper->quote($table_name));
				}
				elseif ($connection->getType() === 'pgsql')
				{
					$connection->query('vacuum ' . $sqlHelper->quote($table_name));
				}
			}
			catch (\Bitrix\Main\DB\SqlQueryException $e)
			{
				$lAdmin->AddGroupError($e, $table_name);
			}
			break;
		case 'orm':
			$tableParts = explode('_', $table_name);
			array_shift($tableParts);
			$moduleNamespace = ucfirst($tableParts[0]);
			$moduleName = mb_strtolower($tableParts[0]);
			if (count($tableParts) > 1)
			{
				array_shift($tableParts);
			}
			$className = StringHelper::snake2camel(implode('_', $tableParts));

			$obTable = new CPerfomanceTable;
			$obTable->Init($table_name, $connection);
			$arFields = $obTable->GetTableFields(false, true);

			$arUniqueIndexes = $obTable->GetUniqueIndexes();
			$hasID = false;
			foreach ($arUniqueIndexes as $indexName => $indexColumns)
			{
				if (array_values($indexColumns) === ['ID'])
				{
					$hasID = $indexName;
				}
			}

			if ($hasID)
			{
				$arUniqueIndexes = [$hasID => $arUniqueIndexes[$hasID]];
			}

			$obSchema = new CPerfomanceSchema;
			$arParents = $obSchema->GetParents($table_name);
			$arValidators = [];
			$arMessages = [];

			$shortAliases = Main\Config\Option::get('perfmon', 'tablet_short_aliases') === 'Y';
			$objectSettings = Main\Config\Option::get('perfmon', 'tablet_object_settings') === 'Y';
			$useMapIndex = Main\Config\Option::get('perfmon', 'tablet_use_map_index') === 'Y';
			$useValidationClosure = Main\Config\Option::get('perfmon', 'tablet_validation_closure') === 'Y';

			$dateFunctions = [
				'curdate' => true,
				'current_date' => true,
				'current_time' => true,
				'current_timestamp' => true,
				'curtime' => true,
				'localtime' => true,
				'localtimestamp' => true,
				'now' => true
			];

			$descriptions = [];
			$fields = [];
			$fieldClassPrefix = '';
			$validatorPrefix = '';
			$referencePrefix = '';
			$datetimePrefix = '';
			$aliases = [
				'Bitrix\Main\Localization\Loc',
				'Bitrix\Main\ORM\Data\DataManager'
			];

			if (!$shortAliases)
			{
				$fieldClassPrefix = 'Fields\\';
				$validatorPrefix = $fieldClassPrefix . 'Validators\\';
				$referencePrefix = $fieldClassPrefix . 'Relations\\';
				$datetimePrefix = 'Type\\';
				$aliases[] = 'Bitrix\Main\ORM\Fields';
			}

			$fieldClasses = [
				'integer' => 'IntegerField',
				'float' => 'FloatField',
				'boolean' => 'BooleanField',
				'string' => 'StringField',
				'text' => 'TextField',
				'enum' => 'EnumField',
				'date' => 'DateField',
				'datetime' => 'DatetimeField'
			];

			foreach ($arFields as $columnName => $columnInfo)
			{
				$type = $columnInfo['orm_type'];
				if ($shortAliases)
				{
					$aliases[] = 'Bitrix\Main\ORM\Fields\\' . $fieldClasses[$type];
				}

				$match = [];
				if (
					preg_match('/^(.+)_TYPE$/', $columnName, $match)
					&& $columnInfo['length'] == 4
					&& isset($arFields[$match[1]])
				)
				{
					$columnInfo['nullable'] = true;
					$columnInfo['orm_type'] = 'enum';
					$columnInfo['enum_values'] = ["'text'", "'html'"];
					$columnInfo['length'] = '';
				}

				$columnInfo['default'] = (string)$columnInfo['default'];
				if ($columnInfo['default'] !== '')
				{
					$columnInfo['nullable'] = true;
				}

				switch ($type)
				{
					case 'integer':
					case 'float':
						break;
					case 'boolean':
						if ($columnInfo['default'] !== '')
						{
							$columnInfo['default'] = "'" . $columnInfo['default'] . "'";
						}
						$columnInfo['type'] = 'bool';
						$columnInfo['length'] = '';
						$columnInfo['enum_values'] = ["'N'", "'Y'"];
						break;
					case 'string':
					case 'text':
						$columnInfo['type'] = $columnInfo['orm_type'];
						if ($columnInfo['default'] !== '')
						{
							$columnInfo['default'] = "'" . $columnInfo['default'] . "'";
						}
						break;
					case 'enum':
						if ($columnInfo['default'] !== '' && !is_numeric($columnInfo['default']))
						{
							$columnInfo['default'] = "'" . $columnInfo['default'] . "'";
						}
						break;
					case 'date':
					case 'datetime':
						if ($columnInfo['default'] !== '' && !is_numeric($columnInfo['default']))
						{
							$defaultValue = mb_strtolower($columnInfo['default']);
							if (mb_strlen($defaultValue) > 2)
							{
								if (substr_compare($defaultValue, '()', -2, 2, true) === 0)
								{
									$defaultValue = mb_substr($defaultValue, 0, -2);
								}
							}
							if (isset($dateFunctions[$defaultValue]))
							{
								if ($type == 'date')
								{
									if ($shortAliases)
									{
										$aliases[] = 'Bitrix\Main\Type\Date';
									}
									else
									{
										$aliases[] = 'Bitrix\Main\Type';
									}
									$columnInfo['default_text'] = 'current date';
									$columnInfo['default'] = "function()\n"
										. "\t\t\t\t\t{\n"
										. "\t\t\t\t\t\treturn new " . $datetimePrefix . "Date();\n"
										. "\t\t\t\t\t}";
								}
								else
								{
									if ($shortAliases)
									{
										$aliases[] = 'Bitrix\Main\Type\DateTime';
									}
									else
									{
										$aliases[] = 'Bitrix\Main\Type';
									}
									$columnInfo['default_text'] = 'current datetime';
									$columnInfo['default'] = "function()\n"
										. "\t\t\t\t\t{\n"
										. "\t\t\t\t\t\treturn new " . $datetimePrefix . "DateTime();\n"
										. "\t\t\t\t\t}";
								}
							}
							else
							{
								$columnInfo['default'] = "'" . $columnInfo['default'] . "'";
							}
						}
						break;
				}

				$primary = false;
				foreach ($arUniqueIndexes as $arColumns)
				{
					if (in_array($columnName, $arColumns))
					{
						$primary = true;
						break;
					}
				}

				$messageId = mb_strtoupper(implode('_', $tableParts) . '_ENTITY_' . $columnName . '_FIELD');
				$arMessages[$messageId] = '';

				$descriptions[$columnName] = ' * &lt;li&gt; ' . $columnName
					. ' ' . $columnInfo['type'] . ($columnInfo['length'] != '' ? '(' . $columnInfo['length'] . ')' : '')
					. ($columnInfo['orm_type'] === 'enum' || $columnInfo['orm_type'] === 'boolean' ?
						' (' . implode(', ', $columnInfo['enum_values']) . ')'
						: ''
					)
					. ' ' . ($columnInfo['nullable'] ? 'optional' : 'mandatory')
					. ($columnInfo['default'] !== ''
						? ' default ' . ($columnInfo['default_text'] ?? $columnInfo['default'])
						: ''
					)
					. "\n";

				$useValidator = false;
				$validateFunctionName = '';
				if (
					$columnInfo['orm_type'] === 'string'
					&& $columnInfo['length'] > 0
				)
				{
					$useValidator = true;
					if ($shortAliases)
					{
						$aliases[] = 'Bitrix\Main\ORM\Fields\Validators\LengthValidator';
					}
					if (!$useValidationClosure)
					{
						$validateFunctionName = 'validate' . StringHelper::snake2camel($columnName);
						$arValidators[$validateFunctionName] = [
							'length' => $columnInfo['length'],
							'field' => $columnName,
						];
					}
				}

				$size = 0;
				if ($columnInfo['orm_type'] === 'integer')
				{
					if (str_starts_with($columnInfo['type~'], 'tinyint'))
					{
						$size = 1;
					}
					elseif (str_starts_with($columnInfo['type~'], 'smallint'))
					{
						$size = 2;
					}
					elseif (str_starts_with($columnInfo['type~'], 'mediumint'))
					{
						$size = 3;
					}
					elseif (str_starts_with($columnInfo['type~'], 'bigint'))
					{
						$size = 8;
					}
				}

				if ($objectSettings)
				{
					$offset = ($useMapIndex ? "\t\t\t\t" : "\t\t\t");
					$initParams = $offset . "\t[]\n";
					if ($useValidator)
					{
						if ($useValidationClosure)
						{
							$initParams =
								$offset . "\t[\n"
								. $offset . "\t\t'validation' => function()\n"
								. $offset . "\t\t{\n"
								. $offset . "\t\t\treturn[\n"
								. $offset . "\t\t\t\tnew " . $validatorPrefix . 'LengthValidator(null, ' . $columnInfo['length'] . "),\n"
								. $offset . "\t\t\t];\n"
								. $offset . "\t\t},\n"
								. $offset . "\t]\n"
							;
						}
						else
						{
							$initParams =
								$offset . "\t[\n"
								. $offset . "\t\t'validation' => [_" . '_CLASS_' . "_, '" . $validateFunctionName . "']\n"
								. $offset . "\t]\n"
							;
						}
					}

					$fields[$columnName] =
						"\t\t\t"
						. ($useMapIndex ? "'" . $columnName . "' => " : '')
						. '(new ' . $fieldClassPrefix . $fieldClasses[$type] . "('" . $columnName . "',\n"
						. $initParams
						. $offset . "))->configureTitle(Loc::getMessage('" . $messageId . "'))\n"
						. ($primary ? $offset . "\t\t->configurePrimary(true)\n" : '')
						. ($columnInfo['increment'] ? $offset . "\t\t->configureAutocomplete(true)\n" : '')
						. (!$primary && $columnInfo['nullable'] === false ? $offset . "\t\t->configureRequired(true)\n" : '')
						. ($columnInfo['orm_type'] === 'boolean'
								? $offset . "\t\t->configureValues(" . implode(', ', $columnInfo['enum_values']) . ")\n"
								: ''
						)
						. ($columnInfo['orm_type'] === 'enum'
								? $offset . "\t\t->configureValues([" . implode(', ', $columnInfo['enum_values']) . "])\n"
								: ''
						)
						. ($columnInfo['default'] !== ''
								? $offset . "\t\t->configureDefaultValue(" . $columnInfo['default'] . ")\n"
								: ''
						)
						. ($size
								? $offset . "\t\t->configureSize(" . $size . ")\n"
								: ''
						)
					;

					$fields[$columnName] =
						mb_substr($fields[$columnName], 0, -1)
						. "\n"
						. "\t\t\t,\n"
					;
				}
				else
				{
					$validator = '';
					if ($useValidator)
					{
						if ($useValidationClosure)
						{
							$offset = "\t\t\t\t\t";
							$validator =
								$offset . "'validation' => function()\n"
								. $offset . "{\n"
								. $offset . "\treturn[\n"
								. $offset . "\t\tnew " . $validatorPrefix . 'LengthValidator(null, ' . $columnInfo['length'] . "),\n"
								. $offset . "\t];\n"
								. $offset . "},\n"
							;
						}
						else
						{
							$validator = "\t\t\t\t\t'validation' => [_" . '_CLASS_' . "_, '" . $validateFunctionName . "'],\n";
						}
					}

					$fields[$columnName] =
						"\t\t\t"
						. ($useMapIndex ? "'" . $columnName . "' => " : '')
						. 'new ' . $fieldClassPrefix . $fieldClasses[$type] . "(\n"
						. "\t\t\t\t'" . $columnName . "',\n"
						. "\t\t\t\t[\n"
						. ($primary ? "\t\t\t\t\t'primary' => true,\n" : '')
						. ($columnInfo['increment'] ? "\t\t\t\t\t'autocomplete' => true,\n" : '')
						. (!$primary && $columnInfo['nullable'] === false ? "\t\t\t\t\t'required' => true,\n" : '')
						. ($columnInfo['orm_type'] === 'boolean' || $columnInfo['orm_type'] === 'enum'
							? "\t\t\t\t\t'values' => [" . implode(', ', $columnInfo['enum_values']) . "],\n"
							: ''
						)
						. ($columnInfo['default'] !== '' ? "\t\t\t\t\t'default' => " . $columnInfo['default'] . ",\n" : '')
						. $validator
						. "\t\t\t\t\t'title' => Loc::getMessage('" . $messageId . "'),\n"
						. ($size ? "\t\t\t\t\t'size' => " . $size . ",\n" : '')
						. "\t\t\t\t]\n"
						. "\t\t\t),\n"
					;
				}
			}
			foreach ($arParents as $columnName => $parentInfo)
			{
				if ($shortAliases)
				{
					$aliases[] = 'Bitrix\Main\ORM\Fields\Relations\Reference';
				}

				$parentTableParts = explode('_', $parentInfo['PARENT_TABLE']);
				array_shift($parentTableParts);
				$parentModuleNamespace = ucfirst($parentTableParts[0]);
				$parentClassName = StringHelper::snake2camel(implode('_', $parentTableParts));

				$columnNameEx = preg_replace('/_ID$/', '', $columnName);
				if (isset($descriptions[$columnNameEx]))
				{
					$columnNameEx = mb_strtoupper($parentClassName);
				}
				$descriptions[$columnNameEx] = ' * &lt;li&gt; ' . $columnName
					. ' reference to {@link \\Bitrix\\' . $parentModuleNamespace
					. '\\' . $parentClassName . 'Table}'
					. "\n";

				$fields[$columnNameEx] = "\t\t\t"
					. ($useMapIndex ? "'" . $columnNameEx . "' => " : '')
					. 'new ' . $referencePrefix . "Reference(\n"
					. "\t\t\t\t'" . $columnNameEx . "',\n"
					. "\t\t\t\t'\Bitrix\\" . $parentModuleNamespace . '\\' . $parentClassName . "',\n"
					. "\t\t\t\t['=this." . $columnName . "' => 'ref." . $parentInfo['PARENT_COLUMN'] . "'],\n"
					. "\t\t\t\t['join_type' => 'LEFT']\n"
					. "\t\t\t),\n";
			}

			$aliases = array_unique($aliases);
			sort($aliases);

			echo "\n\nFile: /bitrix/modules/" . $moduleName . '/lib/' . mb_strtolower($className) . 'table.php';
			echo '<hr>';
			echo '<pre>';
			echo '&lt;', '?', "php\n";
			echo 'namespace Bitrix\\' . $moduleNamespace . ";\n";
			echo "\n";
			foreach ($aliases as $row)
			{
				echo 'use ' . $row . ";\n";
			}
			echo "\n";
			echo '/' . "**\n";
			echo ' * Class ' . $className . "Table\n";
			echo " * \n";
			echo " * Fields:\n";
			echo " * &lt;ul&gt;\n";
			echo implode('', $descriptions);
			echo " * &lt;/ul&gt;\n";
			echo " *\n";
			echo ' * @package Bitrix\\' . $moduleNamespace . "\n";
			echo ' *' . "*/\n";
			echo "\n";
			echo 'class ' . $className . "Table extends DataManager\n";
			echo "{\n";
			echo "\t/**\n";
			echo "\t * Returns DB table name for entity.\n";
			echo "\t *\n";
			echo "\t * @return string\n";
			echo "\t */\n";
			echo "\tpublic static function getTableName()\n";
			echo "\t{\n";
			echo "\t\treturn '" . $table_name . "';\n";
			echo "\t}\n";
			echo "\n";
			echo "\t/**\n";
			echo "\t * Returns entity map definition.\n";
			echo "\t *\n";
			echo "\t * @return array\n";
			echo "\t */\n";
			echo "\tpublic static function getMap()\n";
			echo "\t{\n";
			echo "\t\treturn [\n";
			echo implode('', $fields);
			echo "\t\t];\n";
			echo "\t}\n";
			foreach ($arValidators as $validateFunctionName => $validator)
			{
				echo "\n\t/**\n";
				echo "\t * Returns validators for " . $validator['field'] . " field.\n";
				echo "\t *\n";
				echo "\t * @return array\n";
				echo "\t */\n";
				echo "\tpublic static function " . $validateFunctionName . "(): array\n";
				echo "\t{\n";
				echo "\t\treturn [\n";
				echo "\t\t\tnew " . $validatorPrefix . 'LengthValidator(null, ' . $validator['length'] . "),\n";
				echo "\t\t];\n";
				echo "\t}\n";
			}
			echo "}\n";
			echo '</pre>';
			echo 'File: /bitrix/modules/' . $moduleName . '/lang/ru/lib/' . mb_strtolower($className) . 'table.php';
			echo '<hr>';
			echo '<pre>';
			echo '&lt;', '?', "php\n";
			foreach ($arMessages as $messageId => $messageText)
			{
				echo "\$MESS['" . $messageId . "'] = \"" . EscapePHPString($messageText) . "\";\n";
			}
			echo '</pre>';
			break;
		}
	}
}

$lAdmin->BeginPrologContent();

?><h4><?=Loc::getMessage('PERFMON_TABLES_ALL') ?></h4>
<script>
	hrefs = "";
	rows = [];
	prev = '';
</script><?php

$headers = [
	0 => [
		'id' => 'TABLE_NAME',
		'content' => Loc::getMessage('PERFMON_TABLES_NAME'),
		'default' => true,
		'sort' => 'TABLE_NAME',
	],
	1 => [
		'id' => 'ENGINE_TYPE',
		'content' => Loc::getMessage('PERFMON_TABLES_ENGINE_TYPE'),
		'default' => true,
		'sort' => 'ENGINE_TYPE',
	],
	2 => [
		'id' => 'NUM_ROWS',
		'content' => Loc::getMessage('PERFMON_TABLES_NUM_ROWS'),
		'default' => true,
		'align' => 'right',
		'sort' => 'NUM_ROWS',
	],
	3 => [
		'id' => 'BYTES',
		'content' => Loc::getMessage('PERFMON_TABLES_BYTES'),
		'default' => true,
		'align' => 'right',
		'sort' => 'BYTES',
	],
	4 => [
		'id' => 'BYTES_INDEX',
		'content' => Loc::getMessage('PERFMON_TABLES_BYTES_INDEX'),
		'default' => true,
		'align' => 'right',
		'sort' => 'BYTES_INDEX',
	]
];

$lAdmin->EndPrologContent();
$lAdmin->AddHeaders($headers);

$bShowFullInfo = (
		($request->get('full_info') === 'Y')
		|| (COption::GetOptionInt('perfmon', 'tables_show_time', 0) <= 5)
	);

if ($bShowFullInfo)
{
	session_write_close();
}

$stime = time();
$arAllTables = [];
$data = CPerfomanceTableList::GetList($bShowFullInfo, $connection);
while ($ar = $data->Fetch())
{
	$arAllTables[] = $ar;
}

sortByColumn($arAllTables, [$by => $order === 'desc' ? SORT_DESC : SORT_ASC]);
$etime = time();

if ($bShowFullInfo)
{
	COption::SetOptionInt('perfmon', 'tables_show_time', $etime - $stime);
}

$data = new CDBResult;
$data->InitFromArray($arAllTables);
$data = new CAdminResult($data, $tableID);

$generateOrm = Main\Config\Option::get('perfmon', 'enable_tablet_generator') === 'Y';

while ($result = $data->GetNext())
{
	$row =& $lAdmin->AddRow($result['TABLE_NAME'], $result);
	$row->AddViewField('TABLE_NAME', '<a class="table_name" data-table-name="' . $result['TABLE_NAME'] . '" href="perfmon_table.php?lang=' . LANGUAGE_ID . (isset($request->getQueryList()['connection']) ? '&amp;connection=' . urlencode($connectionName) : '') . '&amp;table_name=' . urlencode($result['TABLE_NAME']) . '">' . $result['TABLE_NAME'] . '</a>');
	$row->AddViewField('BYTES', CFile::FormatSize($result['BYTES']));
	$row->AddViewField('BYTES_INDEX', CFile::FormatSize($result['BYTES_INDEX']));

	$actions = [];
	if ($connection->getType() === 'mysql' && $result['ENGINE_TYPE'] !== 'VIEW')
	{
		if ($bShowFullInfo)
		{
			foreach ($engines as $id => $ar)
			{
				if (mb_strtoupper($result['ENGINE_TYPE']) != $id)
				{
					$actions[] = [
						'ICON' => 'edit',
						'DEFAULT' => false,
						'TEXT' => Loc::getMessage('PERFMON_TABLES_ACTION_CONVERT', ['#ENGINE_TYPE#' => $ar['NAME']]),
						'ACTION' => $lAdmin->ActionDoGroup($result['TABLE_NAME'], 'convert', 'to=' . $id . (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : '')),
					];
				}
			}
		}

		$actions[] = [
			'DEFAULT' => false,
			'TEXT' => Loc::getMessage('PERFMON_TABLES_ACTION_OPTIMIZE'),
			'ACTION' => $lAdmin->ActionDoGroup($result['TABLE_NAME'], 'optimize', (isset($request->getQueryList()['connection']) ? 'connection=' . urlencode($connectionName) : '')),
		];
	}
	elseif ($connection->getType() === 'pgsql')
	{
		$actions[] = [
			'DEFAULT' => false,
			'TEXT' => Loc::getMessage('PERFMON_TABLES_ACTION_OPTIMIZE'),
			'ACTION' => $lAdmin->ActionDoGroup($result['TABLE_NAME'], 'optimize', (isset($request->getQueryList()['connection']) ? 'connection=' . urlencode($connectionName) : '')),
		];
	}

	if ($generateOrm)
	{
		$actions[] = [
			'DEFAULT' => false,
			'TEXT' => 'ORM',
			'ACTION' => $lAdmin->ActionDoGroup($result['TABLE_NAME'], 'orm', (isset($request->getQueryList()['connection']) ? 'connection=' . urlencode($connectionName) : '')),
		];
	}
	if (!empty($actions))
	{
		$row->AddActions($actions);
	}
}

$lAdmin->AddFooter(
	[
		[
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $data->SelectedRowsCount(),
		],
		[
			'counter' => true,
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_CHECKED'),
			'value' => '0',
		],
	]
);

$arGroupActions = ['optimize' => Loc::getMessage('PERFMON_TABLES_ACTION_OPTIMIZE')];
if ($connection->getType() === 'mysql')
{
	foreach ($engines as $id => $ar)
	{
		$arGroupActions['convert_to_' . $id] = Loc::getMessage('PERFMON_TABLES_ACTION_CONVERT', ['#ENGINE_TYPE#' => $ar['NAME']]);
	}
}

$lAdmin->AddGroupActionTable($arGroupActions);

if (!$bShowFullInfo)
{
	$lAdmin->BeginEpilogContent();
	?>
	<script>
		BX.ready(function ()
		{
			<?=$tableID?>.
			GetAdminList('<?= $APPLICATION->GetCurPage();?>?lang=<?= LANGUAGE_ID?>&full_info=Y<?php echo (isset($request->getQueryList()['connection']) ? '&connection=' . urlencode($connectionName) : '')?>');
		});
	</script><?php
	$lAdmin->EndEpilogContent();
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('PERFMON_TABLES_TITLE'));

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$strLastTables = trim(CUserOptions::GetOption('perfmon', 'last_tables' . (isset($request->getQueryList()['connection']) ? '_' . $connectionName : ''), ''));
if ($strLastTables !== '')
{
	$arLastTables = explode(',', $strLastTables);
	if (count($arLastTables) > 0)
	{
		sort($arLastTables);

		foreach ($arLastTables as $i => $table_name)
		{
			if ($connection->isTableExists($table_name))
			{
				$arLastTables[$i] = ['NAME' => '<a href="perfmon_table.php?lang=' . LANGUAGE_ID . (isset($request->getQueryList()['connection']) ? '&amp;connection=' . urlencode($connectionName) : '') . '&amp;table_name=' . urlencode($table_name) . '">' . $table_name . '</a>'];
			}
			else
			{
				unset($arLastTables[$i]);
			}
		}

		$sTableID2 = 't_perfmon_recent_tables';

		$lAdmin2 = new CAdminList($sTableID2);

		$lAdmin2->BeginPrologContent();
		echo '<h4>' . Loc::getMessage('PERFMON_TABLES_RECENTLY_BROWSED') . "</h4>\n";

		$lAdmin2->EndPrologContent();
		$lAdmin2->AddHeaders([
			[
				'id' => 'NAME',
				'content' => Loc::getMessage('PERFMON_TABLES_NAME'),
				'default' => true,
			],
		]);

		$data = new CDBResult;
		$data->InitFromArray($arLastTables);
		$data = new CAdminResult($data, $sTableID2);

		$j = 0;
		while ($result = $data->Fetch())
		{
			$row =& $lAdmin2->AddRow($j++, $result);
			foreach ($result as $key => $value)
			{
				$row->AddViewField($key, $value);
			}
		}

		$lAdmin2->CheckListMode();
		$lAdmin2->DisplayList();
	}
}
?>
<h4><?= Loc::getMessage('PERFMON_TABLES_QUICK_SEARCH') ?></h4>
<input type="text" id="instant-search">
<script>
	BX.ready(function ()
	{
		if (location.hash != '#empty' && location.hash != '#authorize')
			BX('instant-search').value = location.hash.replace(/^#/, '');
		BX('instant-search').focus();
		setTimeout(filter_rows, 250);
	});
	var hrefs;
	var rows = [];
	var prev = '';
	function filter_rows()
	{
		var i;
		var input = BX('instant-search').value.replace(/[^a-z_0-9]+/, '');
		if (input != prev)
		{
			prev = input;
			if (prev.length)
				location.hash = prev;
			else
				location.hash = 'empty';

			var tbody = BX('<?= $tableID?>').getElementsByTagName("tbody")[0];
			if (!hrefs)
			{
				hrefs = BX.findChildren(tbody, {tag: 'a', className: 'table_name'}, true);
				for (i = 0; i < hrefs.length; i++)
					rows[i] = BX.findParent(hrefs[i], {'tag': 'tr'});
			}


			for (i = 0; i < hrefs.length; i++)
				if (rows[i].parentNode)
					rows[i].parentNode.removeChild(rows[i]);

			var j = 0;
			for (i = 0; i < hrefs.length; i++)
			{
				// change getAttribute to dataset when Bitrix reached IE11
				if (
					input.length == 0
					|| match(hrefs[i].getAttribute('data-table-name'), input)
				)
				{
					tbody.appendChild(rows[i]);
					j++;
				}
			}
		}
		setTimeout(filter_rows, 250);
	}
	function match(haystack, needle)
	{
		if (haystack.indexOf(needle) >= 0)
			return true;

		var ciNeedle = new RegExp(needle, 'i');
		if (haystack.match(ciNeedle))
			return true;

		var needleParts = needle.split('');
		for (var i = 0; i < needleParts.length; i++)
		{
			needleParts[i] = needleParts[i].replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
		}
		var pattern = '^.+_' + needleParts.join('.+_') + '.+$';
		var expr = new RegExp(pattern, 'i');
		return !!haystack.match(expr);
	}
</script><?php

$lAdmin->DisplayList();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
