<?
define("ADMIN_MODULE_NAME", "perfmon");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule('perfmon'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$message = new CAdminMessage(GetMessage("PERFMON_ROW_EDIT_MODULE_ERROR"));
	echo $message->Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$hasTokenizer = function_exists('token_get_all');
$isAdmin = $USER->CanDoOperation('edit_php');
$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

function var_import_r($tokens, &$pos, &$result)
{
	while (isset($tokens[$pos]))
	{
		if ($tokens[$pos][0] === T_STRING)
			$uc = strtoupper($tokens[$pos][1]);
		else
			$uc = "";

		if ($uc === "NULL" || $uc === "TRUE" || $uc === "FALSE")
		{
			$result = eval("return ".$tokens[$pos][1].";");
			$pos++;
		}
		elseif ($tokens[$pos][0] === T_LNUMBER || $tokens[$pos][0] === T_DNUMBER || $tokens[$pos][0] === T_CONSTANT_ENCAPSED_STRING)
		{
			$result = eval("return ".$tokens[$pos][1].";");
			$pos++;
		}
		elseif ($tokens[$pos][0] === T_ARRAY)
		{
			$pos++;
			while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
				$pos++;

			if ($tokens[$pos][0] !== "(")
				return;
			else
				$pos++;

			$result = array();
			while (true)
			{
				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
					$pos++;

				if ($tokens[$pos][0] === ")")
					break;

				$key = null;
				var_import_r($tokens, $pos, $key);

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
					$pos++;

				if ($tokens[$pos][0] === "," || $tokens[$pos][0] === ")")
				{
					$result[] = $key;
					$pos++;
					continue;
				}

				if ($tokens[$pos][0] !== T_DOUBLE_ARROW)
					return;
				else
					$pos++;

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
					$pos++;

				$value = null;
				var_import_r($tokens, $pos, $value);

				while (isset($tokens[$pos]) && $tokens[$pos][0] === T_WHITESPACE)
					$pos++;

				if ($tokens[$pos][0] === "," || $tokens[$pos][0] === ")")
					$result[$key] = $value;

				if ($tokens[$pos][0] === ",")
					$pos++;
			}
			$pos++;
		}
		else
		{
			return;
		}
	}
}

function var_import($str)
{
	$tokens = token_get_all("<? ".trim($str));
	$pos = 2;
	$result = null;
	var_import_r($tokens, $pos, $result);
	return $result;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && check_bitrix_sessid() && $isAdmin && $_REQUEST["action"] === "unserialize")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	CUtil::JSPostUnescape();
	echo var_export(unserialize($_POST["data"]), true);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
	die();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && check_bitrix_sessid() && $isAdmin && $_REQUEST["action"] === "serialize")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	CUtil::JSPostUnescape();

	echo serialize(var_import($_POST["data"]));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
	die();
}

$table_name = $_REQUEST["table_name"];
$obTable = new CPerfomanceTable;
$obTable->Init($table_name);
if (!$obTable->IsExists())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$message = new CAdminMessage(GetMessage("PERFMON_ROW_EDIT_TABLE_ERROR", array(
		"#TABLE_NAME#" => htmlspecialcharsbx($table_name),
	)));
	echo $message->Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$arUniqueIndexes = $obTable->GetUniqueIndexes();
$arFields = $obTable->GetTableFields(false, true);
$arFilter = array();
$strWhere = "";
$bNewRow = false;

$arRowPK = isset($_REQUEST["pk"]) && is_array($_REQUEST["pk"])? $_REQUEST["pk"]: array();
if (count($arRowPK))
{
	foreach ($arUniqueIndexes as $arIndexColumns)
	{
		$arMissed = array_diff($arIndexColumns, array_keys($arRowPK));
		if (count($arMissed) == 0)
		{
			$strWhere = "WHERE 1 = 1";
			foreach ($arRowPK as $column => $value)
			{
				$arFilter["=".$column] = $value;
				if ($value != "")
					$strWhere .= " AND ".$column." = '".$DB->ForSQL($value)."'";
				else
					$strWhere .= " AND (".$column." = '' OR ".$column." IS NULL)";
			}
			break;
		}
	}
}

if (!isset($_REQUEST["pk"]) && !empty($arUniqueIndexes))
{
	foreach ($arFields as $Field => $arField)
	{
		if ($arField["increment"])
		{
			foreach ($arUniqueIndexes as $arIndexColumns)
			{
				$arMissed = array_diff($arIndexColumns, array($Field));
				if (count($arMissed) == 0)
				{
					$bNewRow = true;
					break;
				}
			}
		}
	}
}

if (empty($arFilter) && !$bNewRow)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$message = new CAdminMessage(GetMessage("PERFMON_ROW_EDIT_PK_ERROR"));
	echo $message->Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if ($bNewRow)
{
	$arRecord = array();
	foreach ($arFields as $Field => $arField)
	{
		$arRecord[$Field] = $arField["default"];
	}
}
else
{
	CTimeZone::Disable();
	$rsRecord = $obTable->GetList(array_keys($arFields), $arFilter, array());
	CTimeZone::Enable();
	$arRecord = $rsRecord->Fetch();
}

if (!$arRecord)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$message = new CAdminMessage(GetMessage("PERFMON_ROW_EDIT_NOT_FOUND"));
	echo $message->Show();
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$obSchema = new CPerfomanceSchema;
$arChildren = $obSchema->GetChildren($table_name);
$arParents = $obSchema->GetParents($table_name);

$aTabs = array(
	array(
		"DIV" => "edit",
		"TAB" => GetMessage("PERFMON_ROW_EDIT_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("PERFMON_ROW_EDIT_TAB_TITLE", array("#TABLE_NAME#" => $table_name)),
	),
	array(
		"DIV" => "cache",
		"TAB" => GetMessage("PERFMON_ROW_CACHE_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("PERFMON_ROW_CACHE_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl_".ToLower($table_name), $aTabs);
$bVarsFromForm = false;
$strError = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && check_bitrix_sessid() && $isAdmin)
{
	CTimeZone::Disable();
	if (isset($_REQUEST["delete"]) && $_REQUEST["delete"] != "")
	{
		if (!$bNewRow)
		{
			$res = $DB->Query("
				delete from ".CPerfomanceTable::escapeTable($table_name)."
				".$strWhere."
			", true);
			if ($res)
			{
				LocalRedirect("perfmon_table.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name));
			}
			else
			{
				$strError = $DB->GetErrorMessage();
			}
		}
		$bVarsFromForm = true;
	}
	elseif ($bNewRow)
	{
		$arToInsert = array();
		foreach ($arFields as $Field => $arField)
		{
			if (!in_array($Field, $arIndexColumns))
			{
				if (isset($_POST["mark_".$Field."_"]) && $_POST["mark_".$Field."_"] === "Y")
					$arToInsert[$Field] = var_import($_POST[$Field]);
				elseif (isset($_POST[$Field]) && strlen($_POST[$Field]) > 0)
					$arToInsert[$Field] = $_POST[$Field];
				else
					$arToInsert[$Field] = false;
			}
		}
		$res = $DB->Add($table_name, $arToInsert, array(), "", true);
		if (!$res)
		{
			$bVarsFromForm = true;
			$strError = $DB->GetErrorMessage();
		}
	}
	else
	{
		$arToUpdate = array();
		foreach ($arFields as $Field => $arField)
		{
			if (!in_array($Field, $arIndexColumns))
			{
				if (isset($_POST["mark_".$Field."_"]) && $_POST["mark_".$Field."_"] === "Y")
					$arToUpdate[$Field] = serialize(var_import($_POST[$Field]));
				elseif (isset($_POST[$Field]) && strlen($_POST[$Field]) > 0)
					$arToUpdate[$Field] = $_POST[$Field];
				else
					$arToUpdate[$Field] = false;
			}
		}

		$strUpdate = $DB->PrepareUpdate($table_name, $arToUpdate);
		if (strlen($strUpdate))
		{
			$res = $DB->Query("
				update ".CPerfomanceTable::escapeTable($table_name)."
				set ".$strUpdate."
				".$strWhere."
			", true);
			if (!$res)
			{
				$bVarsFromForm = true;
				$strError = $DB->GetErrorMessage();
			}
		}
		else
		{
			$res = true;
		}
	}
	CTimeZone::Enable();

	if ($res)
	{
		if ($_POST["clear_managed_cache"] === "Y")
		{
			$CACHE_MANAGER->CleanAll();
			$stackCacheManager->CleanAll();
		}

		if ($_POST["apply"] != "")
		{
			$s = "";
			if ($bNewRow)
			{
				foreach ($arIndexColumns as $Field)
					$s = "&".urlencode("pk[".$Field."]")."=".urlencode($res);
			}
			LocalRedirect($APPLICATION->GetCurPageParam()."&".$tabControl->ActiveTabParam().$s);
		}
		else
		{
			LocalRedirect("perfmon_table.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name));
		}
	}
}

$APPLICATION->SetTitle(GetMessage("PERFMON_ROW_EDIT_TITLE", array("#TABLE_NAME#" => $table_name)));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => $table_name,
		"TITLE" => GetMessage("PERFMON_ROW_EDIT_MENU_LIST_TITLE"),
		"LINK" => "perfmon_table.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name),
		"ICON" => "btn_list",
	)
);
if(!$bNewRow)
{
	$aMenu[] = array(
		"TEXT" => GetMessage("PERFMON_ROW_EDIT_MENU_DELETE"),
		"TITLE" => GetMessage("PERFMON_ROW_EDIT_MENU_DELETE_TITLE"),
		"LINK" => "javascript:jsDelete('editform', '".GetMessage("PERFMON_ROW_EDIT_MENU_DELETE_CONF")."')",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($strError)
{
	$message  = new CAdminMessage(array(
		"MESSAGE" => GetMessage("admin_lib_error"),
		"DETAILS" => $strError,
		"TYPE" => "ERROR",
	));
	echo $message->Show();
}

?>
	<script>
		function jsDelete(form_id, message)
		{
			var _form = document.getElementById(form_id);
			var _flag = document.getElementById('delete');
			if(_form && _flag)
			{
				if(confirm(message))
				{
					_flag.value = 'y';
					_form.submit();
				}
			}
		}
		function AdjustHeight()
		{
			var TEXTS = BX.findChildren(BX('editform'), {tag: /^(textarea)$/i}, true);
			if (TEXTS)
			{
				for (var i = 0; i < TEXTS.length; i++)
				{
					var TEXT = TEXTS[i];
					if (TEXT.scrollHeight > TEXT.clientHeight)
					{
						var dy = TEXT.offsetHeight - TEXT.clientHeight;
						var newHeight = TEXT.scrollHeight + dy;
						TEXT.style.height = newHeight + 'px';
					}
				}
			}
		}
		function editAsSerialize(button, Field, mark)
		{
			var textArea = BX(Field);
			var markHidden = BX(mark);
			if (textArea && markHidden)
			{
				var action = (button.value == 'unserialize' ? 'unserialize' : 'serialize');
				var url = 'perfmon_row_edit.php?lang=<?echo LANGUAGE_ID?>&<?echo bitrix_sessid_get()?>&action=' + action;
				BX.showWait();
				BX.ajax.post(
					url,
					{data: textArea.value},
					function (result)
					{
						BX.closeWait();
						if (result.length > 0)
						{
							textArea.value = result;
							if (action == 'unserialize')
							{
								markHidden.value = 'Y';
								button.value = 'serialize';
							}
							else
							{
								markHidden.value = '';
								button.value = 'unserialize';
							}
							AdjustHeight();
						}
					}
				);
			}
		}
		BX.ready(function ()
		{
			AdjustHeight();
		});
	</script>
	<form method="POST" action="<? echo $APPLICATION->GetCurPageParam() ?>" enctype="multipart/form-data" name="editform" id="editform">
	<?
	$tabControl->Begin();

	$tabControl->BeginNextTab();
	foreach ($arFields as $Field => $arField)
	{
		if (
			(
				$arField["type"] === "string"
				|| $arField["type"] === "int"
			)
			&& array_key_exists($Field, $arParents)
			&& $DB->TableExists($arParents[$Field]["PARENT_TABLE"])
		)
		{
			$rs = $DB->Query(
				$DB->TopSql("
					select distinct ".$arParents[$Field]["PARENT_COLUMN"]."
					from ".$arParents[$Field]["PARENT_TABLE"]."
					order by 1
				", 21)
			);
			$arSelect = array(
				"REFERENCE" => array(),
				"REFERENCE_ID" => array(),
			);
			while ($ar = $rs->Fetch())
			{
				$arSelect["REFERENCE"][] = $ar[$arParents[$Field]["PARENT_COLUMN"]];
				$arSelect["REFERENCE_ID"][] = $ar[$arParents[$Field]["PARENT_COLUMN"]];
			}
			if (count($arSelect["REFERENCE"]) < 21)
			{
				$arFields[$Field]["SELECT"] = $arSelect;
			}
			///TODO lookup window
		}
		///TODO visual editor for FIELD FIELD_TYPE couple
	}

	foreach ($arFields as $Field => $arField)
	{
		$trClass = $arField["nullable"]? "": "adm-detail-required-field";
		?><tr class="<?echo $trClass?>"><?
	
		if (in_array($Field, $arIndexColumns))
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord[$Field];
			?>
			<td width="40%"><? echo htmlspecialcharsbx($Field) ?>:</td>
			<td width="60%"><? echo htmlspecialcharsex($value); ?></td>
		<?
		}
		elseif ($arField["type"] === "datetime")
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord["FULL_".$Field];
			?>
			<td width="40%"><? echo htmlspecialcharsbx($Field) ?>:</td>
			<td width="60%"><? echo CAdminCalendar::CalendarDate($Field, $value, 20, true) ?>
		<?
		}
		elseif ($arField["type"] === "date")
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord["SHORT_".$Field];
			?>
			<td width="40%"><? echo htmlspecialcharsbx($Field) ?>:</td>
			<td width="60%"><? echo CAdminCalendar::CalendarDate($Field, $value, 10, false) ?>
		<?
		}
		elseif (isset($arField["SELECT"]))
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord[$Field];
			?>
				<td width="40%"><? echo htmlspecialcharsbx($Field) ?>:</td>
				<td width="60%"><?
					echo SelectBoxFromArray($Field, $arField["SELECT"], $value, $arField["nullable"]? "(null)": "");
					?></td>
		<?
		}
		elseif (
			$arField["type"] === "string"
			&& $arField["length"] == 1
			&& ($arField["default"] === "Y" || $arField["default"] === "N")
			&& ($arRecord[$Field] === "Y" || $arRecord[$Field] === "N")
			&& !$arField["nullable"]
		)
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord[$Field];
			?>
				<td width="40%"><label
						for="<? echo htmlspecialcharsbx($Field) ?>"
						><? echo htmlspecialcharsbx($Field) ?></label>:
				</td>
				<td width="60%"><input
						type="hidden"
						name="<? echo htmlspecialcharsbx($Field) ?>"
						value="N"
						><input
						type="checkbox"
						name="<? echo htmlspecialcharsbx($Field) ?>"
						id="<? echo htmlspecialcharsbx($Field) ?>"
						value="Y"
						<? if ($value === "Y")
							echo 'checked="checked"' ?>
						></td>
		<?
		}
		elseif (
			$arField["type"] === "string"
			&& $arField["length"] > 0
			&& $arField["length"] <= 100
		)
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord[$Field];
			?>
				<td width="40%"><? echo htmlspecialcharsbx($Field) ?>:</td>
				<td width="60%"><input
						type="text"
						maxsize="<? echo $arField["length"] ?>"
						size="<? echo min($arField["length"], 35) ?>"
						name="<? echo htmlspecialcharsbx($Field) ?>"
						value="<? echo htmlspecialcharsbx($value) ?>"
						></td>
		<?
		}
		elseif (
			$arField["type"] === "string"
		)
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord[$Field];
			?>
				<td width="40%" class="adm-detail-valign-top"
					style="padding-top:14px"><? echo htmlspecialcharsbx($Field) ?>:
				</td>
				<td width="60%"><textarea
						style="width:100%"
						rows="1"
						name="<? echo htmlspecialcharsbx($Field) ?>"
						id="<? echo htmlspecialcharsbx($Field) ?>"
						><? echo htmlspecialcharsex($value) ?></textarea>
					<input
						type="hidden"
						value=""
						name="<? echo htmlspecialcharsbx("mark_".$Field."_") ?>"
						id="<? echo htmlspecialcharsbx("mark_".$Field."_") ?>"
						>
					<?if ($hasTokenizer):?>
					<input
						type="button"
						value="unserialize"
						onclick="<? echo htmlspecialcharsbx("editAsSerialize(this, '".CUtil::JSEscape($Field)."', 'mark_".CUtil::JSEscape($Field)."_');") ?>"/>
					<?endif;?>
				</td>
		<?
		}
		elseif (
			$arField["type"] === "int"
			|| $arField["type"] === "double"
		)
		{
			$value = $bVarsFromForm? $_REQUEST[$Field]: $arRecord[$Field];
			?>
				<td width="40%"><? echo htmlspecialcharsbx($Field) ?>:</td>
				<td width="60%"><input
						type="text"
						maxsize="20"
						size="15"
						name="<? echo htmlspecialcharsbx($Field) ?>"
						value="<? echo htmlspecialcharsbx($value) ?>"
						></td>
		<?
		}
		else
		{
			?>
				<td width="40%"><? echo htmlspecialcharsbx($Field) ?>:</td>
				<td width="60%">UNSUPPORTED DATA TYPE</td>
			<?
		}
		?></tr><?
	}
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><label
				for="clear_managed_cache"
				><? echo GetMessage("PERFMON_ROW_CACHE_CLEAR") ?></label>:
		</td>
		<td width="60%"><input
				type="checkbox"
				name="clear_managed_cache"
				id="clear_managed_cache"
				value="Y"
				></td>
	</tr>
	<?
	?>
	<? echo bitrix_sessid_post(); ?>
	<input type="hidden" name="lang" value="<? echo LANGUAGE_ID ?>">
	<input type="hidden" name="delete" id="delete" value="">
	<?
	$tabControl->Buttons(
		array(
			"disabled" => !$isAdmin,
			"back_url" => "perfmon_table.php?lang=".LANGUAGE_ID."&table_name=".urlencode($table_name),
		)
	);
	$tabControl->End();
	?>
	</form>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>