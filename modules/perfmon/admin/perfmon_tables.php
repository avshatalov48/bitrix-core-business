<?
use Bitrix\Main,
	Bitrix\Main\Loader;
define("ADMIN_MODULE_NAME", "perfmon");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
Loader::includeModule('perfmon');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");
IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$arEngines = array(
	"MYISAM" => array("NAME" => "MyISAM"),
	"INNODB" => array("NAME" => "InnoDB"),
);

$sTableID = "t_perfmon_all_tables";
$oSort = new CAdminSorting($sTableID, "TABLE_NAME", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

if (
	isset($_GET['orm']) && $_GET['orm'] === 'y'
	&& Main\Config\Option::get('perfmon', 'enable_tablet_generator') !== 'Y'
)
{
	Main\Config\Option::set('perfmon', 'enable_tablet_generator', 'Y', '');
}

if (($arTABLES = $lAdmin->GroupAction()) && $RIGHT >= "W")
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$rsData = CPerfomanceTableList::GetList();
		while ($ar = $rsData->Fetch())
			$arTABLES[] = $ar["TABLE_NAME"];
	}

	foreach ($arEngines as $id => $ar)
	{
		if ($_REQUEST['action'] == "convert_to_".$id)
		{
			$_REQUEST["action"] = "convert";
			$_REQUEST["to"] = $id;
			break;
		}
	}

	$to = mb_strtoupper($_REQUEST["to"]);

	foreach ($arTABLES as $table_name)
	{
		$table_name = (string)$table_name;
		if ($table_name === '')
			continue;

		$res = $DB->Query("show table status like '".$DB->ForSql($table_name)."'", false);
		$arStatus = $res->Fetch();
		if (!$arStatus || $arStatus["Comment"] === "VIEW")
			continue;

		switch ($_REQUEST['action'])
		{
		case "convert":
			if ($to != mb_strtoupper($arStatus["Engine"]))
			{
				if ($to == "MYISAM")
					$res = $DB->Query("alter table ".CPerfomanceTable::escapeTable($table_name)." ENGINE = MyISAM", false);
				elseif ($to == "INNODB")
					$res = $DB->Query("alter table ".CPerfomanceTable::escapeTable($table_name)." ENGINE = InnoDB", false);
				else
					$res = true;
			}
			if (!$res)
			{
				$lAdmin->AddGroupError(GetMessage("PERFMON_TABLES_CONVERT_ERROR"), $table_name);
			}
			break;
		case "optimize":
			$DB->Query("optimize table ".CPerfomanceTable::escapeTable($table_name)."", false);
			break;
		case "orm":
			$tableParts = explode("_", $table_name);
			array_shift($tableParts);
			$moduleNamespace = ucfirst($tableParts[0]);
			$moduleName = mb_strtolower($tableParts[0]);
			if (count($tableParts) > 1)
				array_shift($tableParts);
			$className = \Bitrix\Main\Text\StringHelper::snake2camel(implode("_", $tableParts));

			$obTable = new CPerfomanceTable;
			$obTable->Init($table_name);
			$arFields = $obTable->GetTableFields(false, true);

			$arUniqueIndexes = $obTable->GetUniqueIndexes();
			$hasID = false;
			foreach ($arUniqueIndexes as $indexName => $indexColumns)
			{
				if(array_values($indexColumns) === array("ID"))
					$hasID = $indexName;
			}

			if ($hasID)
			{
				$arUniqueIndexes = array($hasID => $arUniqueIndexes[$hasID]);
			}

			$obSchema = new CPerfomanceSchema;
			$arParents = $obSchema->GetParents($table_name);
			$arValidators = array();
			$arMessages = array();

			$shortAliases = Main\Config\Option::get('perfmon', 'tablet_short_aliases') == 'Y';
			$objectSettings = Main\Config\Option::get('perfmon', 'tablet_object_settings') == 'Y';
			$useMapIndex = Main\Config\Option::get('perfmon', 'tablet_use_map_index') == 'Y';

			$dateFunctions = array(
				'curdate' => true,
				'current_date' => true,
				'current_time' => true,
				'current_timestamp' => true,
				'curtime' => true,
				'localtime' => true,
				'localtimestamp' => true,
				'now' => true
			);

			$descriptions = array();
			$fields = array();
			$fieldClassPrefix = '';
			$validatorPrefix = '';
			$referencePrefix = '';
			$datetimePrefix = '';
			$aliases = array(
				'Bitrix\Main\Localization\Loc',
				'Bitrix\Main\ORM\Data\DataManager'
			);
			if (!$shortAliases)
			{
				$fieldClassPrefix = 'Fields\\';
				$validatorPrefix = $fieldClassPrefix.'Validators\\';
				$referencePrefix = $fieldClassPrefix.'Relations\\';
				$datetimePrefix = 'Type\\';
				$aliases[] = 'Bitrix\Main\ORM\Fields';
			}

			$fieldClasses = array(
				'integer' => 'IntegerField',
				'float' => 'FloatField',
				'boolean' => 'BooleanField',
				'string' => 'StringField',
				'text' => 'TextField',
				'enum' => 'EnumField',
				'date' => 'DateField',
				'datetime' => 'DatetimeField'
			);

			foreach ($arFields as $columnName => $columnInfo)
			{
				$type = $columnInfo["orm_type"];
				if ($shortAliases)
				{
					$aliases[] = 'Bitrix\Main\ORM\Fields\\'.$fieldClasses[$type];
				}

				$match = array();
				if (
					preg_match("/^(.+)_TYPE\$/", $columnName, $match)
					&& $columnInfo["length"] == 4
					&& isset($arFields[$match[1]])
				)
				{
					$columnInfo["nullable"] = true;
					$columnInfo["orm_type"] = "enum";
					$columnInfo["enum_values"] = array("'text'", "'html'");
					$columnInfo["length"] = "";
				}

				$columnInfo["default"] = (string)$columnInfo["default"];
				if ($columnInfo["default"] !== '')
				{
					$columnInfo["nullable"] = true;
				}

				switch ($type)
				{
					case 'integer':
					case 'float':
						break;
					case 'boolean':
						if ($columnInfo["default"] !== '')
						{
							$columnInfo["default"] = "'".$columnInfo["default"]."'";
						}
						$columnInfo["type"] = "bool";
						$columnInfo["length"] = "";
						$columnInfo["enum_values"] = array("'N'", "'Y'");
						break;
					case 'string':
					case 'text':
						$columnInfo["type"] = $columnInfo["orm_type"];
						if ($columnInfo["default"] !== '')
						{
							$columnInfo["default"] = "'".$columnInfo["default"]."'";
						}
						break;
					case 'enum':
						if ($columnInfo["default"] !== '' && !is_numeric($columnInfo["default"]))
						{
							$columnInfo["default"] = "'".$columnInfo["default"]."'";
						}
						break;
					case 'date':
					case 'datetime':
					if ($columnInfo["default"] !== '' && !is_numeric($columnInfo["default"]))
						{
							$defaultValue = mb_strtolower($columnInfo["default"]);
							if (mb_strlen($defaultValue) > 2)
							{
								if (substr_compare($defaultValue, '()', -2, 2, true) === 0)
									$defaultValue = mb_substr($defaultValue, 0, -2);
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
									$columnInfo["default_text"] = 'current date';
									$columnInfo["default"] = "function()\n"
										."\t\t\t\t\t{\n"
										."\t\t\t\t\t\treturn new ".$datetimePrefix."Date();\n"
										."\t\t\t\t\t}";
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
									$columnInfo["default_text"] = 'current datetime';
									$columnInfo["default"] = "function()\n"
										."\t\t\t\t\t{\n"
										."\t\t\t\t\t\treturn new ".$datetimePrefix."DateTime();\n"
										."\t\t\t\t\t}";
								}
							}
							else
							{
								$columnInfo["default"] = "'".$columnInfo["default"]."'";
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

				$messageId = mb_strtoupper(implode("_", $tableParts)."_ENTITY_".$columnName."_FIELD");
				$arMessages[$messageId] = "";

				$descriptions[$columnName] = " * &lt;li&gt; ".$columnName
					." ".$columnInfo["type"].($columnInfo["length"] != '' ? "(".$columnInfo["length"].")": "")
					.($columnInfo["orm_type"] === "enum" || $columnInfo["orm_type"] === "boolean" ?
						" (".implode(", ", $columnInfo["enum_values"]).")"
						: ""
					)
					." ".($columnInfo["nullable"] ? "optional": "mandatory")
					.($columnInfo["default"] !== ''
						? " default ".(isset($columnInfo["default_text"])
							? $columnInfo["default_text"]
							: $columnInfo["default"]
						)
						: ""
					)
					."\n";

				$validateFunctionName = '';
				if ($columnInfo["orm_type"] == "string" && $columnInfo["length"] > 0)
				{
					if ($shortAliases)
					{
						$aliases[] = 'Bitrix\Main\ORM\Fields\Validators\LengthValidator';
					}
					$validateFunctionName = "validate".Main\Text\StringHelper::snake2camel($columnName);
					$arValidators[$validateFunctionName] = array(
						"length" => $columnInfo["length"],
						"field" => $columnName,
					);
				}

				if ($objectSettings)
				{
					$offset = ($useMapIndex ? "\t\t\t\t" : "\t\t\t");
					$fields[$columnName] = "\t\t\t"
						.($useMapIndex ? "'".$columnName."' => " : "")
						."(new ".$fieldClassPrefix.$fieldClasses[$type]."('".$columnName."',\n"
						.($validateFunctionName !== ''
							? $offset."\t[\n"
								.$offset."\t\t'validation' => [_"."_CLASS_"."_, '".$validateFunctionName."']\n"
								.$offset."\t]\n"
							: $offset."\t[]\n"
						)
						.$offset."))->configureTitle(Loc::getMessage('".$messageId."'))\n"
						.($primary ? $offset."\t\t->configurePrimary(true)\n" : "")
						.($columnInfo["increment"] ? $offset."\t\t->configureAutocomplete(true)\n" : "")
						.(!$primary && $columnInfo["nullable"] === false ? $offset."\t\t->configureRequired(true)\n" : "")
						.($columnInfo["orm_type"] === "boolean"
							? $offset."\t\t->configureValues(".implode(", ", $columnInfo["enum_values"]).")\n"
							: ""
						)
						.($columnInfo["orm_type"] === "enum"
							? $offset."\t\t->configureValues([".implode(", ", $columnInfo["enum_values"])."])\n"
							: ""
						)
						.($columnInfo["default"] !== '' ? $offset."\t\t->configureDefaultValue(".$columnInfo["default"].")\n" : "");
					$fields[$columnName] = mb_substr($fields[$columnName], 0, -1).",\n";
				}
				else
				{
					$fields[$columnName] = "\t\t\t"
						.($useMapIndex ? "'".$columnName."' => " : "")
						."new ".$fieldClassPrefix.$fieldClasses[$type]."(\n"
						."\t\t\t\t'".$columnName."',\n"
						."\t\t\t\t[\n"
						.($primary ? "\t\t\t\t\t'primary' => true,\n" : "")
						.($columnInfo["increment"] ? "\t\t\t\t\t'autocomplete' => true,\n" : "")
						.(!$primary && $columnInfo["nullable"] === false ? "\t\t\t\t\t'required' => true,\n" : "")
						.($columnInfo["orm_type"] === "boolean" || $columnInfo["orm_type"] === "enum"
							? "\t\t\t\t\t'values' => array(".implode(", ", $columnInfo["enum_values"])."),\n"
							: ""
						)
						.($columnInfo["default"] !== '' ? "\t\t\t\t\t'default' => ".$columnInfo["default"].",\n" : "")
						.($validateFunctionName !== '' ? "\t\t\t\t\t'validation' => [_"."_CLASS_"."_, '".$validateFunctionName."'],\n" : "")
						."\t\t\t\t\t'title' => Loc::getMessage('".$messageId."')\n"
						."\t\t\t\t]\n"
						."\t\t\t),\n";
				}
			}
			foreach ($arParents as $columnName => $parentInfo)
			{
				if ($shortAliases)
				{
					$aliases[] = 'Bitrix\Main\ORM\Fields\Relations\Reference';
				}

				$parentTableParts = explode("_", $parentInfo["PARENT_TABLE"]);
				array_shift($parentTableParts);
				$parentModuleNamespace = ucfirst($parentTableParts[0]);
				$parentClassName = \Bitrix\Main\Text\StringHelper::snake2camel(implode("_", $parentTableParts));

				$columnNameEx = preg_replace("/_ID\$/", "", $columnName);
				if (isset($descriptions[$columnNameEx]))
				{
					$columnNameEx = mb_strtoupper($parentClassName);
				}
				$descriptions[$columnNameEx] = " * &lt;li&gt; ".$columnName
					." reference to {@link \\Bitrix\\".$parentModuleNamespace
					."\\".$parentClassName."Table}"
					."\n";

				$fields[$columnNameEx] = "\t\t\t"
					.($useMapIndex ? "'".$columnNameEx."' => " : "")
					."new ".$referencePrefix."Reference(\n"
					."\t\t\t\t'".$columnNameEx."',\n"
					."\t\t\t\t'\Bitrix\\".$parentModuleNamespace."\\".$parentClassName."',\n"
					."\t\t\t\t['=this.".$columnName."' => 'ref.".$parentInfo["PARENT_COLUMN"]."'],\n"
					."\t\t\t\t['join_type' => 'LEFT']\n"
					."\t\t\t),\n";
			}

			$aliases = array_unique($aliases);
			sort($aliases);

			echo "\n\nFile: /bitrix/modules/".$moduleName."/lib/".mb_strtolower($className)."table.php";
			echo "<hr>";
			echo "<pre>";
			echo "&lt;", "?", "php\n";
			echo "namespace Bitrix\\".$moduleNamespace.";\n";
			echo "\n";
			echo "use ".implode(",\n\t", $aliases).";\n";
			echo "\n";
			echo "Loc::loadMessages(_"."_FILE_"."_);\n";
			echo "\n";
			echo "/"."**\n";
			echo " * Class ".$className."Table\n";
			echo " * \n";
			echo " * Fields:\n";
			echo " * &lt;ul&gt;\n";
			echo implode('', $descriptions);
			echo " * &lt;/ul&gt;\n";
			echo " *\n";
			echo " * @package Bitrix\\".$moduleNamespace."\n";
			echo " *"."*/\n";
			echo "\n";
			echo "class ".$className."Table extends DataManager\n";
			echo "{\n";
			echo "\t/**\n";
			echo "\t * Returns DB table name for entity.\n";
			echo "\t *\n";
			echo "\t * @return string\n";
			echo "\t */\n";
			echo "\tpublic static function getTableName()\n";
			echo "\t{\n";
			echo "\t\treturn '".$table_name."';\n";
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
				echo "\t * Returns validators for ".$validator["field"]." field.\n";
				echo "\t *\n";
				echo "\t * @return array\n";
				echo "\t */\n";
				echo "\tpublic static function ".$validateFunctionName."()\n";
				echo "\t{\n";
				echo "\t\treturn [\n";
				echo "\t\t\tnew ".$validatorPrefix."LengthValidator(null, ".$validator["length"]."),\n";
				echo "\t\t];\n";
				echo "\t}\n";
			}
			echo "}\n";
			echo "</pre>";
			echo "File: /bitrix/modules/".$moduleName."/lang/ru/lib/".mb_strtolower($className)."table.php";
			echo "<hr>";
			echo "<pre>";
			echo "&lt;", "?\n";
			foreach ($arMessages as $messageId => $messageText)
			{
				echo "\$MESS[\"".$messageId."\"] = \"".EscapePHPString($messageText)."\";\n";
			}
			echo "?", "&gt;\n";
			echo "</pre>";
			break;
		}
	}
}

$lAdmin->BeginPrologContent();
?>
<h4><? echo GetMessage("PERFMON_TABLES_ALL") ?></h4>
<script>
	hrefs = "";
	rows = [];
	prev = '';
</script>
<?
$lAdmin->EndPrologContent();

$arHeaders = array();
$arHeaders[] = array(
	"id" => "TABLE_NAME",
	"content" => GetMessage("PERFMON_TABLES_NAME"),
	"default" => true,
	"sort" => "TABLE_NAME",
);

if ($DB->type == "MYSQL")
{
	$arHeaders[] = array(
		"id" => "ENGINE_TYPE",
		"content" => GetMessage("PERFMON_TABLES_ENGINE_TYPE"),
		"default" => true,
		"sort" => "ENGINE_TYPE",
	);
}

$arHeaders[] = array(
	"id" => "NUM_ROWS",
	"content" => GetMessage("PERFMON_TABLES_NUM_ROWS"),
	"default" => true,
	"align" => "right",
	"sort" => "NUM_ROWS",
);

$arHeaders[] = array(
	"id" => "BYTES",
	"content" => GetMessage("PERFMON_TABLES_BYTES"),
	"default" => true,
	"align" => "right",
	"sort" => "BYTES",
);

$lAdmin->AddHeaders($arHeaders);

$bShowFullInfo = ($DB->type == "MYSQL")
	&& (
		(isset($_REQUEST["full_info"]) && $_REQUEST["full_info"] == "Y")
		|| (COption::GetOptionInt("perfmon", "tables_show_time", 0) <= 5)
	);

if ($bShowFullInfo)
	session_write_close();

$stime = time();
$arAllTables = array();
$rsData = CPerfomanceTableList::GetList($bShowFullInfo);
while ($ar = $rsData->Fetch())
	$arAllTables[] = $ar;
sortByColumn($arAllTables, array($by => $order == "desc"? SORT_DESC: SORT_ASC));
$etime = time();

if ($bShowFullInfo)
	COption::SetOptionInt("perfmon", "tables_show_time", $etime - $stime);

$rsData = new CDBResult;
$rsData->InitFromArray($arAllTables);
$rsData = new CAdminResult($rsData, $sTableID);

$generateOrm = (string)Main\Config\Option::get('perfmon', 'enable_tablet_generator') == 'Y';

while ($arRes = $rsData->GetNext())
{
	$row =& $lAdmin->AddRow($arRes["TABLE_NAME"], $arRes);
	$row->AddViewField("TABLE_NAME", '<a class="table_name" data-table-name="'.$arRes["TABLE_NAME"].'" href="perfmon_table.php?lang='.LANGUAGE_ID.'&amp;table_name='.urlencode($arRes["TABLE_NAME"]).'">'.$arRes["TABLE_NAME"].'</a>');
	$row->AddViewField("BYTES", CFile::FormatSize($arRes["BYTES"]));
	$arActions = array();
	if ($DB->type == "MYSQL" && $arRes["ENGINE_TYPE"] !== "VIEW")
	{
		if ($bShowFullInfo)
		{
			foreach ($arEngines as $id => $ar)
			{
				if (mb_strtoupper($arRes["ENGINE_TYPE"]) != $id)
					$arActions[] = array(
						"ICON" => "edit",
						"DEFAULT" => false,
						"TEXT" => GetMessage("PERFMON_TABLES_ACTION_CONVERT", array("#ENGINE_TYPE#" => $ar["NAME"])),
						"ACTION" => $lAdmin->ActionDoGroup($arRes["TABLE_NAME"], "convert", "to=".$id),
					);
			}
		}

		$arActions[] = array(
			"DEFAULT" => false,
			"TEXT" => GetMessage("PERFMON_TABLES_ACTION_OPTIMIZE"),
			"ACTION" => $lAdmin->ActionDoGroup($arRes["TABLE_NAME"], "optimize"),
		);
	}
	if ($generateOrm)
	{
		$arActions[] = array(
			"DEFAULT" => false,
			"TEXT" => "ORM",
			"ACTION" => $lAdmin->ActionDoGroup($arRes["TABLE_NAME"], "orm"),
		);
	}
	if (!empty($arActions))
		$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0",
		),
	)
);

if ($DB->type == "MYSQL")
{
	$arGroupActions = array(
		"optimize" => GetMessage("PERFMON_TABLES_ACTION_OPTIMIZE")
	);
	foreach ($arEngines as $id => $ar)
		$arGroupActions["convert_to_".$id] = GetMessage("PERFMON_TABLES_ACTION_CONVERT", array("#ENGINE_TYPE#" => $ar["NAME"]));

	$lAdmin->AddGroupActionTable($arGroupActions);

	if (!$bShowFullInfo)
	{
		$lAdmin->BeginEpilogContent();
		?>
		<script>
			BX.ready(function ()
			{
				<?=$sTableID?>.
				GetAdminList('<?echo $APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID?>&full_info=Y');
			});
		</script><?
		$lAdmin->EndEpilogContent();
	}
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_TABLES_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$strLastTables = CUserOptions::GetOption("perfmon", "last_tables");
if ($strLastTables <> '')
{
	$arLastTables = explode(",", $strLastTables);
	if (count($arLastTables) > 0)
	{
		sort($arLastTables);

		foreach ($arLastTables as $i => $table_name)
		{
			if ($DB->TableExists($table_name))
			{
				$arLastTables[$i] = array(
					"NAME" => '<a href="perfmon_table.php?lang='.LANGUAGE_ID.'&amp;table_name='.urlencode($table_name).'">'.$table_name.'</a>',
				);
			}
			else
			{
				unset($arLastTables[$i]);
			}
		}

		$sTableID2 = "t_perfmon_recent_tables";

		$lAdmin2 = new CAdminList($sTableID2);

		$lAdmin2->BeginPrologContent();
		echo "<h4>".GetMessage("PERFMON_TABLES_RECENTLY_BROWSED")."</h4>\n";
		$lAdmin2->EndPrologContent();

		$lAdmin2->AddHeaders(array(
			array(
				"id" => "NAME",
				"content" => GetMessage("PERFMON_TABLES_NAME"),
				"default" => true,
			),
		));

		$rsData = new CDBResult;
		$rsData->InitFromArray($arLastTables);
		$rsData = new CAdminResult($rsData, $sTableID2);

		$j = 0;
		while ($arRes = $rsData->NavNext(true, "f_"))
		{
			$row =& $lAdmin2->AddRow($j++, $arRes);
			foreach ($arRes as $key => $value)
				$row->AddViewField($key, $value);
		}

		$lAdmin2->CheckListMode();
		$lAdmin2->DisplayList();
	}
}
?>
<h4><? echo GetMessage("PERFMON_TABLES_QUICK_SEARCH") ?></h4>
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

			var tbody = BX('<?echo $sTableID?>').getElementsByTagName("tbody")[0];
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
</script>
<?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");