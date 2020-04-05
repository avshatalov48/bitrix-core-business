<?
define("ADMIN_MODULE_NAME", "perfmon");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/include.php");
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

	$to = strtoupper($_REQUEST["to"]);

	foreach ($arTABLES as $table_name)
	{
		if (strlen($table_name) <= 0)
			continue;

		$res = $DB->Query("show table status like '".$DB->ForSql($table_name)."'", false);
		$arStatus = $res->Fetch();
		if (!$arStatus || $arStatus["Comment"] === "VIEW")
			continue;

		switch ($_REQUEST['action'])
		{
		case "convert":
			if ($to != strtoupper($arStatus["Engine"]))
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
			$_GET["orm"] = "y";

			$tableParts = explode("_", $table_name);
			array_shift($tableParts);
			$moduleNamespace = ucfirst($tableParts[0]);
			$moduleName = strtolower($tableParts[0]);
			if (count($tableParts) > 1)
				array_shift($tableParts);
			$className = \Bitrix\Main\ORM\Entity::snake2camel(implode("_", $tableParts));

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

			echo "File: /bitrix/modules/".$moduleName."/lib/".strtolower($className).".php";
			echo "<hr>";
			echo "<pre>";
			echo "&lt;", "?", "php\n";
			echo "namespace Bitrix\\".$moduleNamespace.";\n";
			echo "\n";
			echo "use Bitrix\\Main,\n";
			echo "	Bitrix\\Main\\Localization\\Loc;\n";
			echo "Loc::loadMessages(_"."_FILE_"."_);\n";
			echo "\n";
			echo "/"."**\n";
			echo " * Class ".$className."Table\n";
			echo " * \n";
			echo " * Fields:\n";
			echo " * &lt;ul&gt;\n";
			foreach ($arFields as $columnName => $columnInfo)
			{
				if ($columnInfo["orm_type"] === "boolean")
				{
					$columnInfo["nullable"] = true;
					$columnInfo["type"] = "bool";
					$columnInfo["length"] = "";
					$columnInfo["enum_values"] = array("'N'", "'Y'");
				}

				if (
					$columnInfo["type"] === "int"
					&& ($columnInfo["default"] > 0)
					&& !$columnInfo["nullable"]
				)
				{
					$columnInfo["nullable"] = true;
				}

				$match = array();
				if (
					preg_match("/^(.+)_TYPE\$/", $columnName, $match)
					&& array_key_exists($match[1], $arFields)
					&& $columnInfo["length"] == 4
				)
				{
					$columnInfo["nullable"] = true;
					$columnInfo["type"] = "enum";
					$columnInfo["enum_values"] = array("'text'", "'html'");
					$columnInfo["length"] = "";
				}

				$default = $columnInfo["default"];
				if (!is_numeric($default) && $default != "")
					$default = "'".$default."'";

				echo " * &lt;li&gt; ".$columnName
					." ".$columnInfo["type"].($columnInfo["length"]? "(".$columnInfo["length"].")": "")
					.($columnInfo["type"] === "enum"? " (".implode(", ", $columnInfo["enum_values"]).")": "")
					." ".($columnInfo["nullable"]? "optional": "mandatory")
					.($default? " default ".$default: "")
					."\n";
			}
			foreach ($arParents as $columnName => $parentInfo)
			{
				$parentTableParts = explode("_", $parentInfo["PARENT_TABLE"]);
				array_shift($parentTableParts);
				$parentModuleNamespace = ucfirst($parentTableParts[0]);
				$parentClassName = \Bitrix\Main\ORM\Entity::snake2camel(implode("_", $parentTableParts));

				$columnName = preg_replace("/_ID\$/", "", $columnName);
				echo " * &lt;li&gt; ".$columnName
					." reference to {@link \\Bitrix\\".$parentModuleNamespace
					."\\".$parentClassName."Table}"
					."\n";
			}
			echo " * &lt;/ul&gt;\n";
			echo " *\n";
			echo " * @package Bitrix\\".$moduleNamespace."\n";
			echo " *"."*/\n";
			echo "\n";
			echo "class ".$className."Table extends Main\\Entity\\DataManager\n";
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
			echo "\t\treturn array(\n";
			foreach ($arFields as $columnName => $columnInfo)
			{
				if ($columnInfo["orm_type"] === "boolean")
				{
					$columnInfo["nullable"] = true;
					$columnInfo["type"] = "bool";
					$columnInfo["length"] = "";
					$columnInfo["enum_values"] = array("'N'", "'Y'");
				}

				if (
					$columnInfo["type"] === "int"
					&& ($columnInfo["default"] > 0)
					&& !$columnInfo["nullable"]
				)
				{
					$columnInfo["nullable"] = true;
				}

				$match = array();
				if (
					preg_match("/^(.+)_TYPE\$/", $columnName, $match)
					&& array_key_exists($match[1], $arFields)
					&& $columnInfo["length"] == 4
				)
				{
					$columnInfo["nullable"] = true;
					$columnInfo["orm_type"] = "enum";
					$columnInfo["enum_values"] = array("'text'", "'html'");
				}

				$default = $columnInfo["default"];
				if (!is_numeric($default) && $default != "")
					$default = "'".$default."'";

				echo "\t\t\t'".$columnName."' => array(\n";
				echo "\t\t\t\t'data_type' => '".$columnInfo["orm_type"]."',\n";
				$primary = false;
				foreach ($arUniqueIndexes as $indexName => $arColumns)
				{
					if (in_array($columnName, $arColumns))
					{
						echo "\t\t\t\t'primary' => true,\n";
						$primary = true;
						break;
					}
				}
				if ($columnInfo["increment"])
				{
					echo "\t\t\t\t'autocomplete' => true,\n";
				}
				if (!$primary && $columnInfo["nullable"] === false)
				{
					echo "\t\t\t\t'required' => true,\n";
				}
				if ($columnInfo["orm_type"] === "boolean" || $columnInfo["orm_type"] === "enum")
				{
					echo "\t\t\t\t'values' => array(".implode(", ", $columnInfo["enum_values"])."),\n";
				}
				if ($columnInfo["length"] > 0 && $columnInfo["orm_type"] == "string")
				{
					$validateFunctionName = "validate".\Bitrix\Main\ORM\Entity::snake2camel($columnName);
					echo "\t\t\t\t'validation' => array(_"."_CLASS_"."_, '".$validateFunctionName."'),\n";
					$arValidators[$validateFunctionName] = array(
						"length" => $columnInfo["length"],
						"field" => $columnName,
					);
				}
				$messageId = strtoupper(implode("_", $tableParts)."_ENTITY_".$columnName."_FIELD");
				$arMessages[$messageId] = "";
				echo "\t\t\t\t'title' => Loc::getMessage('".$messageId."'),\n";
				echo "\t\t\t),\n";
			}
			foreach ($arParents as $columnName => $parentInfo)
			{
				$parentTableParts = explode("_", $parentInfo["PARENT_TABLE"]);
				array_shift($parentTableParts);
				$parentModuleNamespace = ucfirst($parentTableParts[0]);
				$parentClassName = \Bitrix\Main\ORM\Entity::snake2camel(implode("_", $parentTableParts));

				$columnNameEx = preg_replace("/_ID\$/", "", $columnName);

				echo "\t\t\t'".$columnNameEx."' => array(\n";
				echo "\t\t\t\t'data_type' => 'Bitrix\\".$parentModuleNamespace."\\".$parentClassName."',\n";
				echo "\t\t\t\t'reference' => array('=this.".$columnName."' => 'ref.".$parentInfo["PARENT_COLUMN"]."'),\n";
				echo "\t\t\t),\n";
			}
			echo "\t\t);\n";
			echo "\t}\n";
			foreach ($arValidators as $validateFunctionName => $validator)
			{
				echo "\t/**\n";
				echo "\t * Returns validators for ".$validator["field"]." field.\n";
				echo "\t *\n";
				echo "\t * @return array\n";
				echo "\t */\n";
				echo "\tpublic static function ".$validateFunctionName."()\n";
				echo "\t{\n";
				echo "\t\treturn array(\n";
				echo "\t\t\tnew Main\\Entity\\Validator\\Length(null, ".$validator["length"]."),\n";
				echo "\t\t);\n";
				echo "\t}\n";
			}
			echo "}\n";
			echo "</pre>";
			echo "File: /bitrix/modules/".$moduleName."/lang/ru/lib/".strtolower($className).".php";
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
	rows = new Array();
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
				if (strtoupper($arRes["ENGINE_TYPE"]) != $id)
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
	if ($_GET["orm"] === "y")
	{
		$arActions[] = array(
			"DEFAULT" => false,
			"TEXT" => "ORM",
			"ACTION" => $lAdmin->ActionDoGroup($arRes["TABLE_NAME"], "orm"),
		);
	}
	if (count($arActions))
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
if (strlen($strLastTables) > 0)
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
		if (haystack.match(expr))
			return true;

		return false;
	}
</script>
<?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
