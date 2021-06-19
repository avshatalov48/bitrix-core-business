<?
use Bitrix\Main\Loader;

define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
Loader::includeModule('perfmon');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$table_name = $_REQUEST["table_name"];
$obTable = new CPerfomanceTable;
$obTable->Init($table_name);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D" || !$obTable->IsExists())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if (
	$_SERVER["REQUEST_METHOD"] === "GET"
	&& isset($_GET["ajax_tooltip"]) && $_GET["ajax_tooltip"] === "y"
	&& isset($_GET["find_type"])
	&& isset($_GET["find"])
	&& check_bitrix_sessid()
)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	$rsData = $obTable->GetList(array("*"), array("=".$_GET["find_type"] => $_GET["find"]));
	$arData = $rsData->Fetch();
	if ($arData)
	{
		?>
		<table class="list"><?
		?>
		<tr>
		<td align="left" colspan="2"><b><? echo htmlspecialcharsEx($table_name) ?></b></td></tr><?
		foreach ($arData as $key => $value)
		{
			?>
			<tr>
			<td align="left"><? echo htmlspecialcharsEx($key) ?></td>
			<td align="left">&nbsp;<? echo htmlspecialcharsEx($value) ?></td></tr><?
		}
		?></table><?
	}
	else
	{
		?>no data found<?
	}
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

$arFieldsEx = $obTable->GetTableFields(false, true);
$arFields = array();
foreach ($arFieldsEx as $FIELD_NAME => $FIELD_INFO)
	$arFields[$FIELD_NAME] = $FIELD_INFO["type"];

$arUniqueIndexes = $obTable->GetUniqueIndexes();
$sTableID = "tbl_perfmon_table".md5($table_name);
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

if ($lAdmin->GroupAction() && $RIGHT >= "W")
{
	switch ($_REQUEST['action'])
	{
	case "delete":
		//Gather columns from request
		$arRowPK = is_array($_REQUEST["pk"])? $_REQUEST["pk"]: array();
		if (count($arRowPK))
		{
			foreach ($arUniqueIndexes as $arIndexColumns)
			{
				$arMissed = array_diff($arIndexColumns, array_keys($arRowPK));
				if (count($arMissed) == 0)
				{
					$strSql = "delete from ".$table_name." WHERE 1=1 ";
					foreach ($arRowPK as $column => $value)
					{
						if($value <> '')
						{
							$strSql .= " AND ".$column."='".$DB->ForSQL($value)."'";
						}
						else
						{
							$strSql .= " AND (".$column."='".$DB->ForSQL($value)."' or ".$column." is null)";
						}
					}
					$DB->Query($strSql);
					break;
				}
			}
		}
		break;
	}
}

$FilterArr = array(
	"find",
	"find_type",
);
foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
{
	if ($FIELD_TYPE != "unknown")
		$FilterArr[] = "find_".$FIELD_NAME;
}

$lAdmin->InitFilter($FilterArr);

$arFilter = array();
foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
{
	$filterValue = null;
	if ($FIELD_TYPE != "unknown")
	{
		if (
			isset($find_type) && $find_type == $FIELD_NAME
			&& isset($find) && mb_strlen($find)
		)
		{
			$filterValue = $find;
		}
		elseif (
			isset($GLOBALS["find_".$FIELD_NAME]) && mb_strlen($GLOBALS["find_".$FIELD_NAME])
		)
		{
			$filterValue = $GLOBALS["find_".$FIELD_NAME];
		}
		else
		{
		}
	}

	if (isset($filterValue))
	{
		$where = new CSQLWhere();

		$op = $where->MakeOperation($filterValue);

		if ($filterValue === $op["FIELD"])
			$op["OPERATOR"] = "%=";
		else
			$op["OPERATOR"] = mb_substr($filterValue, 0, mb_strlen($filterValue) - mb_strlen($op["FIELD"]));

		if ($op["OPERATION"] === "B" || $op["OPERATION"] === "NB")
			$op["FIELD"] = array_map('trim', explode(",", $op["FIELD"], 2));
		elseif ($op["OPERATION"] === "IN" || $op["OPERATION"] === "NIN")
			$op["FIELD"] = array_map('trim', explode(",", $op["FIELD"]));

		$arFilter[$op["OPERATOR"].$FIELD_NAME] = $op["FIELD"] === "NULL"? false: $op["FIELD"];
	}
}

$arHeaders = array();
foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
{
	$arHeaders[$FIELD_NAME] = array(
		"id" => $FIELD_NAME,
		"content" => $FIELD_NAME,
		"sort" => $arFieldsEx[$FIELD_NAME]["sortable"]? $FIELD_NAME: "",
		"default" => true,
	);
	if ($FIELD_TYPE == "int" || $FIELD_TYPE == "datetime" || $FIELD_TYPE == "date" || $FIELD_TYPE == "double")
		$arHeaders[$FIELD_NAME]["align"] = "right";
}

$lAdmin->AddHeaders($arHeaders);

$bDelete = false;
$arPKColumns = array();
$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
{
	$arSelectedFields = array(
		"*",
	);
	$bDelete = count($arUniqueIndexes) > 0;
	$arPKColumns = array_shift($arUniqueIndexes);
}
else
{
	foreach ($arUniqueIndexes as $arIndexColumns)
	{
		$arMissed = array_diff($arIndexColumns, $arSelectedFields);
		if (count($arMissed) == 0)
		{
			$bDelete = true;
			$arPKColumns = $arIndexColumns;
			break;
		}
	}
}

$bDelete = $bDelete && $RIGHT >= "W";

$obSchema = new CPerfomanceSchema;
$arChildren = $obSchema->GetChildren($table_name);
$arParents = $obSchema->GetParents($table_name);

CTimeZone::Disable();
$rsData = $obTable->GetList($arSelectedFields, $arFilter, array($by => $order), array("nPageSize" => CAdminResult::GetNavSize($sTableID)));
CTimeZone::Enable();

function TableExists($tableName)
{
	global $DB;
	static $cache = array();
	if (!isset($cache[$tableName]))
	{
		$cache[$tableName] = $DB->TableExists($tableName);
	}
	return $tableName;
}

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_TABLE_PAGE")));
$precision = ini_get('precision') >= 0? ini_get('precision'): 2;
$max_display_url = COption::GetOptionInt("perfmon", "max_display_url");
while ($arRes = $rsData->Fetch()):

	$row =& $lAdmin->AddRow($arRes["ID"], $arRes);

	$arRowPK = array();
	foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
	{
		if ($arRes[$FIELD_NAME] <> '')
		{
			if ($FIELD_TYPE == "int")
			{
				$val = perfmon_NumberFormat($arRes[$FIELD_NAME], 0);
			}
			elseif ($FIELD_TYPE == "double")
			{
				$val = htmlspecialcharsEx($arRes[$FIELD_NAME]);
			}
			elseif ($FIELD_TYPE == "datetime")
			{
				$val = str_replace(" ", "&nbsp;", $arRes["FULL_".$FIELD_NAME]);
			}
			elseif ($FIELD_TYPE == "date")
			{
				$val = str_replace(" ", "&nbsp;", $arRes["SHORT_".$FIELD_NAME]);
			}
			else
			{
				$val = htmlspecialcharsbx($arRes[$FIELD_NAME]);
			}

			if (array_key_exists($FIELD_NAME, $arParents) && TableExists($arParents[$FIELD_NAME]["PARENT_TABLE"]))
				$val = '<a onmouseover="addTimer(this)" onmouseout="removeTimer(this)" href="perfmon_table.php?set_filter=Y&table_name='.$arParents[$FIELD_NAME]["PARENT_TABLE"].'&find='.urlencode($arRes[$FIELD_NAME]).'&find_type='.$arParents[$FIELD_NAME]["PARENT_COLUMN"].'">'.$val.'</a>';

			$row->AddViewField($FIELD_NAME, $val);
		}

		if ($bDelete && in_array($FIELD_NAME, $arPKColumns))
		{
			$arRowPK[] = urlencode("pk[".$FIELD_NAME."]")."=".urlencode($arRes[$FIELD_NAME]);
		}
	}

	$arActions = array();
	if ($bDelete && (count($arPKColumns) == count($arRowPK)))
	{
		$arActions[] = array(
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => GetMessage("MAIN_EDIT"),
			"ACTION" => $lAdmin->ActionRedirect("perfmon_row_edit.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name)."&".implode("&", $arRowPK)),
		);
		$arActions[] = array(
			"ICON" => "delete",
			"DEFAULT" => false,
			"TEXT" => GetMessage("MAIN_DELETE"),
			"ACTION" => $lAdmin->ActionDoGroup($arRes["ID"], "delete", "table_name=".urlencode($table_name)."&".implode("&", $arRowPK)),
		);
	}

	if (count($arChildren))
	{
		$arActions[] = array("SEPARATOR" => true);
		foreach ($arChildren as $arChild)
		{
			if (TableExists($arChild["CHILD_TABLE"]))
			{
				$arActions[] = array(
					"ICON" => "",
					"DEFAULT" => false,
					"TEXT" => $arChild["CHILD_TABLE"].".".$arChild["CHILD_COLUMN"]." = ".$arChild["PARENT_COLUMN"],
					"ACTION" => $lAdmin->ActionRedirect("perfmon_table.php?set_filter=Y&table_name=".$arChild["CHILD_TABLE"]."&find=".urlencode($arRes[$arChild["PARENT_COLUMN"]])."&find_type=".$arChild["CHILD_COLUMN"]),
				);
			}
		}
	}

	if (count($arActions))
		$row->AddActions($arActions);

endwhile;

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
	)
);

$aContext = array();

$sLastTables = CUserOptions::GetOption("perfmon", "last_tables", "");
if ($sLastTables <> '')
	$arLastTables = array_flip(explode(",", $sLastTables));
else
	$arLastTables = array();
unset($arLastTables[mb_strtolower($table_name)]);
$arLastTables[mb_strtolower($table_name)] = true;
if (count($arLastTables) > 10)
	array_shift($arLastTables);
CUserOptions::SetOption("perfmon", "last_tables", implode(",", array_keys($arLastTables)));

unset($arLastTables[$table_name]);
if (count($arLastTables) > 0)
{
	$ar = array(
		"MENU" => array(),
	);
	ksort($arLastTables);
	foreach ($arLastTables as $table => $flag)
	{
		if (TableExists($table))
			$ar["MENU"][] = array(
				"TEXT" => $table,
				"ACTION" => $lAdmin->ActionRedirect("perfmon_table.php?table_name=".$table),
			);
		else
			unset($arLastTables[$table]);
	}
	$ar["TEXT"] = GetMessage("PERFMON_TABLE_RECENTLY_BROWSED", array("#COUNT#" => count($arLastTables)));
	$aContext[] = $ar;
}

if ($bDelete)
{
	foreach ($arFieldsEx as $Field => $arField)
	{
		if ($arField["increment"])
		{
			foreach ($arUniqueIndexes as $arIndexColumns)
			{
				$arMissed = array_diff($arIndexColumns, array($Field));
				if (count($arMissed) == 0)
				{
					$aContext[] = array(
						"TEXT" => GetMessage("MAIN_ADD"),
						"LINK" => "/bitrix/admin/perfmon_row_edit.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name),
						"ICON" => "btn_new",
					);
					break;
				}
			}
		}
	}
}

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_TABLE_ALT_TITLE", array("#TABLE_NAME#" => $table_name)));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFilter = array();
foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
	if ($FIELD_TYPE != "unknown")
		$arFilter[$FIELD_NAME] = $FIELD_NAME;
$oFilter = new CAdminFilter($sTableID."_filter", $arFilter);

CJSCore::Init(array("ajax", "popup"));
?>
<script>
	var toolTipCache = new Array;

	function drawTooltip(result, _this)
	{
		if (!_this) _this = this;

		_this.toolTip = BX.PopupWindowManager.create(
			'table_tooltip_' + (parseInt(Math.random() * 100000)), _this,
			{
				autoHide: true,
				closeIcon: true,
				closeByEsc: true,
				content: result
			}
		);

		_this.toolTip.show();
		toolTipCache[_this.href] = result;
	}

	function sendRequest()
	{
		if (this.toolTip)
			this.toolTip.show();
		else if (toolTipCache[this.href])
			drawTooltip(toolTipCache[this.href], this);
		else
			BX.ajax.get(
				this.href + '&sessid=' + BX.message('bitrix_sessid') + '&ajax_tooltip=y',
				BX.proxy(drawTooltip, this)
			);
	}

	function addTimer(p_href)
	{
		p_href.timerID = setTimeout(BX.proxy(sendRequest, p_href), 1000);
	}

	function removeTimer(p_href)
	{
		if (p_href.timerID)
		{
			clearTimeout(p_href.timerID);
			p_href.timerID = null;
		}
	}
</script>
<form name="find_form" id="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
	<input type="hidden" value="<? echo htmlspecialcharsbx($table_name) ?>" name="table_name">
	<? $oFilter->Begin(); ?>
	<tr>
		<td><b><?=GetMessage("PERFMON_TABLE_FIND")?>:</b></td>
		<td>
			<input type="text" size="25" name="find" value="<? echo htmlspecialcharsbx($find) ?>"
				title="<?=GetMessage("PERFMON_TABLE_FIND")?>">
			<?
			$arr = array(
				"reference" => array_keys($arFilter),
				"reference_id" => array_keys($arFilter),
			);
			echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
			?>
		</td>
	</tr>
	<? foreach ($arFields as $FIELD_NAME => $FIELD_TYPE): ?>
		<? if ($FIELD_TYPE != "unknown"): ?>
			<tr>
				<td><? echo htmlspecialcharsbx($FIELD_NAME) ?></td>
				<td><input type="text" name="find_<? echo htmlspecialcharsbx($FIELD_NAME) ?>" size="47"
					value="<? echo htmlspecialcharsbx(${"find_".$FIELD_NAME}) ?>"></td>
			</tr>
		<? endif ?>
	<? endforeach ?>
	<?
	$oFilter->Buttons(array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form",
	));
	$oFilter->End();
	?>
</form>

<?
$lAdmin->DisplayList();
echo BeginNote();
echo '
	<ul>
	<li>= Identical</li>
	<li>&gt; Greater</li>
	<li>&gt;= Greater or Equal</li>
	<li>&lt; Less</li>
	<li>&lt;= Less or Equal</li>
	<li>% Substring</li>
	<li>? Logic</li>
	<li>&gt;lt;MIN,MAX Between</li>
	<li>#64;N1,N2,...,NN IN</li>
	<li>NULL Empty</li>
	<li>! Negate any of above</li>
	</ul>
';
echo EndNote();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
