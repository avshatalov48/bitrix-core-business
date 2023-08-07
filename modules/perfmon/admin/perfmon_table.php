<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use Bitrix\Main\Loader;

const ADMIN_MODULE_NAME = "perfmon";
const PERFMON_STOP = true;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('perfmon');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$table_name = $_REQUEST["table_name"];
$obTable = new CPerfomanceTable;
$obTable->Init($table_name);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D" || !$obTable->IsExists())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

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
		?><table class="list"><?
			?><tr><?
				?><td align="left" colspan="2"><b><? echo htmlspecialcharsEx($table_name) ?></b></td></tr><?
				foreach ($arData as $key => $value)
				{
					?><tr><?
						?><td align="left"><? echo htmlspecialcharsEx($key) ?></td><?
						?><td align="left">&nbsp;<? echo htmlspecialcharsEx($value) ?></td></tr><?
				}
		?></table><?
	}
	else
	{
		?>no data found<?
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

$arFields = [];
$arFieldsEx = $obTable->GetTableFields(false, true);

foreach ($arFieldsEx as $FIELD_NAME => $FIELD_INFO)
{
	$arFields[$FIELD_NAME] = $FIELD_INFO["type"];
}

$arUniqueIndexes = $obTable->GetUniqueIndexes();
$sTableID = "tbl_perfmon_table".md5($table_name);
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arID = $lAdmin->GroupAction();
if ($arID && $RIGHT >= "W")
{
	foreach ($arID as $ID)
	{
		if ($ID == '')
		{
			continue;
		}

		//Gather columns from request
		$arRowPK = unserialize(base64_decode($ID), ['allowed_classes' => false]);
		if (!is_array($arRowPK) || count($arRowPK) < 1)
		{
			continue;
		}

		switch ($_REQUEST['action'])
		{
		case "delete":
			foreach ($arUniqueIndexes as $arIndexColumns)
			{
				$arMissed = array_diff($arIndexColumns, array_keys($arRowPK));
				if (count($arMissed) == 0)
				{
					$strSql = "delete from ".$table_name." WHERE 1=1 ";
					foreach ($arRowPK as $column => $value)
					{
						if ($value <> '')
						{
							$strSql .= " AND `" . $DB->ForSQL($column) . "` = '" . $DB->ForSQL($value) . "'";
						}
						else
						{
							$strSql .= " AND (`" . $DB->ForSQL($column) . "` = '" . $DB->ForSQL($value) . "' OR `" . $DB->ForSQL($column) . "` is null)";
						}
					}
					$DB->Query($strSql);
					break;
				}
			}
			break;
		default:
			$obSchema = new CPerfomanceSchema;
			$arRowActions = $obSchema->GetRowActions($table_name);
			if (
				array_key_exists($_REQUEST['action'], $arRowActions)
				&& is_callable($arRowActions[$_REQUEST['action']]['callback'])
			)
			{
				foreach ($arUniqueIndexes as $arIndexColumns)
				{
					$arMissed = array_diff($arIndexColumns, array_keys($arRowPK));
					if (count($arMissed) == 0)
					{
						$callbackArgs = [];
						foreach ($arRowPK as $column => $value)
						{
							$callbackArgs[] = $value;
						}
						$callbackResult = call_user_func_array($arRowActions[$_REQUEST['action']]['callback'], $callbackArgs);
						if (!$callbackResult->isSuccess())
						{
							$lAdmin->AddGroupError(implode('</br>', $callbackResult->getErrorMessages()), $ID);
						}
						break;
					}
				}
			}
		}
	}
}

$filterFields = [];
foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
{
	if ($FIELD_TYPE != "unknown")
	{
		$filterFields[] = array(
			"id" => $FIELD_NAME,
			"name" => $FIELD_NAME,
			"filterable" => "%=",
		);
	}
}

$arFilterForm = array();
$lAdmin->AddFilter($filterFields, $arFilterForm);

$where = new CSQLWhere();
$arFilter = array();
foreach ($arFilterForm as $key => $filterValue)
{
	$FIELD_NAME = substr($key, 2);
	$op = $where->MakeOperation($filterValue);

	if ($filterValue === $op["FIELD"])
		$op["OPERATOR"] = "%=";
	else
		$op["OPERATOR"] = mb_substr($filterValue, 0, mb_strlen($filterValue) - mb_strlen($op["FIELD"]));

	if ($op["OPERATION"] === "B" || $op["OPERATION"] === "NB")
		$op["FIELD"] = array_map('trim', explode(",", $op["FIELD"], 2));
	elseif ($op["OPERATION"] === "IN" || $op["OPERATION"] === "NIN")
		$op["FIELD"] = array_map('trim', explode(",", $op["FIELD"]));

	$arFilter[$op["OPERATOR"].$FIELD_NAME] = $op["FIELD"] === "NULL" ? false : $op["FIELD"];
}

$filterOption = new Bitrix\Main\UI\Filter\Options($sTableID);
$filterData = $filterOption->getFilter($filterFields);
$find = trim($filterData["FIND"], " \t\n\r");
if ($find)
{
	$c = count($filterFields);
	for ($i = 0; $i < $c; $i++)
	{
		$field = $filterFields[$i];
		if (preg_match('/^\s*' . $field['name'] . '\s*:\s*(.+)\s*$/i', $find, $match))
		{
			$filterValue = $match[1];

			$op = $where->MakeOperation($filterValue);

			if ($filterValue === $op["FIELD"])
				$op["OPERATOR"] = "%=";
			else
				$op["OPERATOR"] = mb_substr($filterValue, 0, mb_strlen($filterValue) - mb_strlen($op["FIELD"]));

			$arFilter[$op["OPERATOR"] . $field['name']] = $op["FIELD"] === "NULL" ? false : $op["FIELD"];
			break;
		}
	}
	if ($i == $c)
	{
		$field = $filterFields[0];
		$filterValue = $find;

		$op = $where->MakeOperation($filterValue);

		if ($filterValue === $op["FIELD"])
			$op["OPERATOR"] = "%=";
		else
			$op["OPERATOR"] = mb_substr($filterValue, 0, mb_strlen($filterValue) - mb_strlen($op["FIELD"]));

		$arFilter[$op["OPERATOR"] . $field['name']] = $op["FIELD"] === "NULL" ? false : $op["FIELD"];
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
		'prevent_default' => false,
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
	$arSelectedFields = ["*",];
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
$arRowActions = $obSchema->GetRowActions($table_name);

$nav = $lAdmin->getPageNavigation("nav-permon-table");
if ($lAdmin->isTotalCountRequest())
{
	CTimeZone::Disable();
	$count = $obTable->GetList(
		array("ID"),
		$arFilter,
		array(),
		array("bOnlyCount" => true)
	);
	CTimeZone::Enable();
	$lAdmin->sendTotalCountResponse($count);
}
elseif ($_REQUEST["mode"] == "excel")
{
	$arNavParams = false;
}
else
{
	$arNavParams = array(
		"nTopCount" => $nav->getLimit() + 1,
		"nOffset" => $nav->getOffset(),
	);
}

CTimeZone::Disable();
$rsData = $obTable->GetList(
	$arSelectedFields,
	$arFilter,
	array($by => $order),
	$arNavParams
);
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
$precision = ini_get('precision') >= 0? ini_get('precision'): 2;
$max_display_url = COption::GetOptionInt("perfmon", "max_display_url");

$n = 0;
$pageSize = $lAdmin->getNavSize();
while ($arRes = $rsData->Fetch())
{
	$n++;
	if ($n > $pageSize && !($_REQUEST["mode"] == "excel"))
	{
		break;
	}

	$ID = $arRes["ID"];
	if ($arPKColumns)
	{
		$arRowPK = [];
		foreach ($arPKColumns as $FIELD_NAME)
		{
			$arRowPK[$FIELD_NAME] = $arRes[$FIELD_NAME];
		}
		$ID = base64_encode(serialize($arRowPK));
	}

	$arRowPK = array();
	foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
	{
		if ($bDelete && in_array($FIELD_NAME, $arPKColumns))
		{
			$arRowPK[] = urlencode("pk[".$FIELD_NAME."]")."=".urlencode($arRes[$FIELD_NAME]);
		}
	}

	$editUrl = '';
	if ($bDelete && (count($arPKColumns) == count($arRowPK)))
	{
		$editUrl = "perfmon_row_edit.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name)."&".implode("&", $arRowPK);
	}

	$row =& $lAdmin->AddRow($ID, $arRes, $editUrl);

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
			{
				$href = 'perfmon_table.php?lang='.LANGUAGE_ID.'&table_name='.$arParents[$FIELD_NAME]["PARENT_TABLE"].'&apply_filter=Y&find='.urlencode($arRes[$FIELD_NAME]).'&find_type='.urlencode($arParents[$FIELD_NAME]["PARENT_COLUMN"]).'&'.urlencode($arParents[$FIELD_NAME]["PARENT_COLUMN"]).'='.urlencode($arRes[$FIELD_NAME]);
				$val = '<a onmouseover="addTimer(this)" onmouseout="removeTimer(this)" href="'.htmlspecialcharsbx($href).'">'.$val.'</a>';
			}

			$row->AddViewField($FIELD_NAME, $val);
		}
	}

	$arActions = array();
	if ($editUrl)
	{
		$arActions[] = array(
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => GetMessage("MAIN_EDIT"),
			"ACTION" => $lAdmin->ActionRedirect($editUrl),
		);
		$arActions[] = array(
			"ICON" => "delete",
			"DEFAULT" => false,
			"TEXT" => GetMessage("MAIN_DELETE"),
			"ACTION" => $lAdmin->ActionDoGroup($ID, "delete", "table_name=".urlencode($table_name)),
		);
		if ($arRowActions)
		{
			$arActions[] = array("SEPARATOR" => true);
			foreach ($arRowActions as $rowActionId => $rowAction)
			{
				$confirm = $rowAction['confirm'] ? "if(confirm('".CUtil::JSEscape($rowAction['confirm'])."')) " : '';
				$arActions[] = array(
					'TEXT' => $rowAction['title'],
					'ACTION' => $confirm.$lAdmin->ActionDoGroup($ID, $rowActionId, 'table_name=' . urlencode($table_name)),
				);
			}
		}
	}

	if (count($arChildren))
	{
		$arActions[] = array("SEPARATOR" => true);
		foreach ($arChildren as $arChild)
		{
			if (TableExists($arChild["CHILD_TABLE"]))
			{
				$href = "perfmon_table.php?lang=".LANGUAGE_ID."&table_name=".urlencode($arChild["CHILD_TABLE"]).'&apply_filter=Y&'.urlencode($arChild["CHILD_COLUMN"]).'='.urlencode($arRes[$arChild["PARENT_COLUMN"]]);
				$arActions[] = array(
					"ICON" => "",
					"DEFAULT" => false,
					"TEXT" => $arChild["CHILD_TABLE"].".".$arChild["CHILD_COLUMN"]." = ".$arChild["PARENT_COLUMN"],
					"ACTION" => $lAdmin->ActionRedirect($href),
				);
			}
		}
	}

	if (count($arActions))
		$row->AddActions($arActions);

}

$nav->setRecordCount($nav->getOffset() + $n);
$lAdmin->setNavigation($nav, GetMessage("PERFMON_TABLE_PAGE"), false);

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
	)
);

$aContext = array();

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

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->BeginPrologContent();
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
<?
$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_TABLE_ALT_TITLE", array("#TABLE_NAME#" => $table_name)));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CJSCore::Init(array("ajax", "popup"));

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList([
	"SHOW_COUNT_HTML" => true,
	"SERVICE_URL" => "perfmon_table.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name),
]);

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
	<li>&gt;&lt;MIN,MAX Between</li>
	<li>@N1,N2,...,NN IN</li>
	<li>NULL Empty</li>
	<li>! Negate any of above</li>
	</ul>
';
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
