<?
define("ADMIN_MODULE_NAME", "perfmon");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

if(!CModule::IncludeModule('perfmon'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");
IncludeModuleLangFile(__FILE__);

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("PERFMON_IDETAIL_TABLE_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("PERFMON_IDETAIL_TABLE_TAB_TITLE"),
	),
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("PERFMON_IDETAIL_QUERY_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("PERFMON_IDETAIL_QUERY_TAB_TITLE"),
	),
	array(
		"DIV" => "edit3",
		"TAB" => GetMessage("PERFMON_IDETAIL_INDEX_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("PERFMON_IDETAIL_INDEX_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($_REQUEST["ID"]); // Id of the edited record
$rsSuggest = CPerfomanceIndexSuggest::GetList(
	array("ID", "TABLE_NAME", "TABLE_ALIAS", "COLUMN_NAMES", "SQL_TEXT", "SQL_EXPLAIN", "SQL_TIME", "SQL_COUNT"),
	array("=ID" => $ID),
	array()
);
$arSuggest = $rsSuggest->Fetch();
if(!$arSuggest)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sql = CPerfomanceSQL::Format($arSuggest["SQL_TEXT"]);
$sql = htmlspecialcharsEx($sql);
$sql = preg_replace("/(".preg_quote($arSuggest["TABLE_NAME"])."\\s+(?i:as\\s+)*".$arSuggest["TABLE_ALIAS"].")\\s+/", "<b>\\1</b> ", $sql);
foreach(explode(",", $arSuggest["COLUMN_NAMES"]) as $column_name)
	$sql = str_replace($arSuggest["TABLE_ALIAS"].".".$column_name, "<b>".$arSuggest["TABLE_ALIAS"].".".$column_name."</b>", $sql);
$arSuggest["FORMATTED_SQL_TEXT"] = $sql;

$arColumns = explode(",", $arSuggest["COLUMN_NAMES"]);
$arTableStat = CPerfQueryStat::GatherTableStat($arSuggest["TABLE_NAME"]);

$obTable = new CPerfomanceTable;
$arIndexes = $obTable->GetIndexes($arSuggest["TABLE_NAME"]);

$arQueries = array();

$rsQueries = CPerfomanceSQL::GetList(
	array("ID", "SQL_TEXT"),
	array("=SUGGEST_ID" => $ID),
	array("ID" => "ASC"),
	false,
	array("nTopCount" => 10)
);
while($arQuery = $rsQueries->Fetch())
{
	$arQuery["STAT"] = array();
	$arQuery["WHERE"] = array();
	$arQuery["JOIN"] = array();

	$q = new CPerfQuery;
	if($q->parse($q->transform2select($arQuery["SQL_TEXT"])))
	{
		foreach($arColumns as $column_name)
		{
			$arQuery["WHERE"][$column_name] = $q->find_value($arSuggest["TABLE_NAME"], $column_name);
			if($arQuery["WHERE"][$column_name] == "")
				$arQuery["JOIN"][$column_name] = $q->find_join($arSuggest["TABLE_NAME"], $column_name);
			else
				$arQuery["JOIN"][$column_name] = "";
		}
	}

	$sql = CPerfomanceSQL::Format($arQuery["SQL_TEXT"]);
	$sql = htmlspecialcharsEx($sql);
	$sql = preg_replace("/(".preg_quote($arSuggest["TABLE_NAME"])."\\s+(?i:as\\s+)*".$arSuggest["TABLE_ALIAS"].")\\s+/", "<b>\\1</b> ", $sql);
	foreach(explode(",", $arSuggest["COLUMN_NAMES"]) as $column_name)
		$sql = str_replace($arSuggest["TABLE_ALIAS"].".".$column_name, "<b>".$arSuggest["TABLE_ALIAS"].".".$column_name."</b>", $sql);

	$arQuery["FORMATTED_SQL_TEXT"] = $sql;

	foreach($arColumns as $column_name)
	{
		if($arQuery["WHERE"][$column_name])
		{
			$arColStat = CPerfQueryStat::GatherColumnStatByValue($arSuggest["TABLE_NAME"], $column_name, trim($arQuery["WHERE"][$column_name], "'"));
			if($arColStat && $arColStat["TABLE_ROWS"] > 0)
			{
				$arQuery["STAT"][$column_name] = $arColStat["COLUMN_ROWS"]/$arColStat["TABLE_ROWS"];
			}
		}
		elseif($arQuery["JOIN"][$column_name])
		{
			$arColStat = CPerfQueryStat::GatherColumnStatOverall($arSuggest["TABLE_NAME"], $column_name);
			if($arColStat && $arColStat["TABLE_ROWS"] > 0)
			{
				$arQuery["STAT"][$column_name] = 1/$arColStat["TABLE_ROWS"];
			}
		}
		else
		{
			$arQuery["STAT"] = "";
		}
	}

	$arQueries[] = $arQuery;
}

function _sort_index_columns($a, $b)
{
	if($a > $b)
		return 1;
	elseif($a < $b)
		return -1;
	else
		return 0;
}

$arIndexColumns = array();
foreach($arColumns as $column_name)
{
	$arIndexColumns[$column_name] = 0;
	$i = 0;
	foreach($arQueries as $i => $arQuery)
		$arIndexColumns[$column_name] += $arQuery["STAT"][$column_name];
	$arIndexColumns[$column_name] /= $i + 1;
}
if(count($arIndexColumns) > 1)
{
	$bHasSelective = false;
	foreach($arIndexColumns as $column_name => $stat)
		if($stat < 0.05)
			$bHasSelective = true;

	if($bHasSelective)
		foreach($arIndexColumns as $column_name => $stat)
			if($stat > 0.05)
				unset($arIndexColumns[$column_name]);

	uasort($arIndexColumns, "_sort_index_columns");
}
$arIndexColumns = array_keys($arIndexColumns);

$table = trim($arSuggest["TABLE_NAME"], "`");
$index = array();
foreach ($arIndexColumns as $indColumn)
	$index[] = trim($indColumn, "`");

$IndexExists = $DB->GetIndexName($table, $index);

$strError = '';

if($_SERVER["REQUEST_METHOD"] === "POST" && check_bitrix_sessid() && $isAdmin)
{
	if(isset($_REQUEST["create_index"]) && isset($_REQUEST["ddl"]))
	{
		$res = $DB->Query($_REQUEST["ddl"], true);
		if(is_object($res))
		{
			CPerfomanceIndexComplete::Add(array(
				"TABLE_NAME" => $arSuggest["TABLE_NAME"],
				"COLUMN_NAMES" => $arSuggest["COLUMN_NAMES"],
				"INDEX_NAME" => $_REQUEST["index_name"],
				"BANNED" => "N",
			));
			LocalRedirect("/bitrix/admin/perfmon_index_detail.php?ID=".$ID."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
		}
		else
		{
			$strError = $DB->GetErrorMessage();
		}
	}
	elseif(isset($_REQUEST["drop_index"]) && isset($_REQUEST["ddl"]))
	{
		$res = $DB->Query($_REQUEST["ddl"], true);
		if(is_object($res))
		{
			CPerfomanceIndexComplete::DeleteByTableName($arSuggest["TABLE_NAME"], $arSuggest["COLUMN_NAMES"]);
			LocalRedirect("/bitrix/admin/perfmon_index_detail.php?ID=".$ID."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
		}
		else
			$strError = $DB->GetErrorMessage();
	}
	elseif(isset($_REQUEST["ban_index"]))
	{
		CPerfomanceIndexComplete::Add(array(
			"TABLE_NAME" => $arSuggest["TABLE_NAME"],
			"COLUMN_NAMES" => $arSuggest["COLUMN_NAMES"],
			"INDEX_NAME" => false,
			"BANNED" => "Y",
		));
		LocalRedirect("/bitrix/admin/perfmon_index_list.php?lang=".LANGUAGE_ID);
	}
}

$APPLICATION->SetTitle(GetMessage("PERFMON_IDETAIL_TABLE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("PERFMON_IDETAIL_MENU_LIST"),
		"TITLE" => GetMessage("PERFMON_IDETAIL_MENU_LIST_TITLE"),
		"LINK" => "perfmon_index_list.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_list",
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($strError)
{
	$message  = new CAdminMessage(array(
		"MESSAGE" => GetMessage("admin_lib_error"),
		"DETAILS" => $strError,
		"TYPE" => "ERROR",
	));
	echo $message->Show();
}
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>"  enctype="multipart/form-data" name="editform" id="editform">
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
		<?echo BeginNote(), GetMessage("PERFMON_IDETAIL_TABLE_NOTE"), EndNote(), "<br>";?>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("PERFMON_IDETAIL_QUERY")?></td>
	</tr>
	<tr>
		<td colspan="2"><?echo str_replace(
			array(" ", "\n"),
			array(" &nbsp;", "<br>"),
			$arSuggest["FORMATTED_SQL_TEXT"]
		);?></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("PERFMON_IDETAIL_TABLE_STAT")?></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("PERFMON_IDETAIL_TABLE_NAME")?>:</td>
		<td width="60%"><?echo htmlspecialcharsex($arSuggest["TABLE_NAME"]);?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("PERFMON_IDETAIL_TABLE_SIZE")?>:</td>
		<td><?echo htmlspecialcharsex(CFile::FormatSize($arTableStat["TABLE_SIZE"]));?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("PERFMON_IDETAIL_TABLE_ROWS")?>:</td>
		<td><?echo htmlspecialcharsex($arTableStat["TABLE_ROWS"]);?></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("PERFMON_IDETAIL_TABLE_STRUCT")?></td>
	</tr>
	<tr>
		<td align="right" class="adm-detail-valign-top">
			<table class="internal">
			<tr class="heading">
				<td><?echo GetMessage("PERFMON_IDETAIL_TABLE_COLUMN")?></td>
				<td><?echo GetMessage("PERFMON_IDETAIL_COLUMN_DATA_TYPE")?></td>
			</tr>
			<?
			foreach(CPerfQueryStat::GetTableColumns($arSuggest["TABLE_NAME"]) as $column_name => $arColumn):
				$b = in_array($column_name, $arColumns);
			?>
			<tr>
				<td><?echo ($b? "<b>": "").htmlspecialcharsex($column_name).($b? "</b>": "")?></td>
				<td><?echo htmlspecialcharsex($arColumn["Type"])?></td>
			</tr>
			<?endforeach?>
			</table>
		</td>
		<td align="left" class="adm-detail-valign-top">
			<table class="internal" style="width:100%">
			<tr class="heading">
				<td><?echo GetMessage("PERFMON_IDETAIL_INDEX_NAME")?></td>
				<td><?echo GetMessage("PERFMON_IDETAIL_INDEX_COLUMNS")?></td>
			</tr>
			<?
			foreach($arIndexes as $index_name => $arIndexColumnsTmp):
				$arIndexColumnsTmp2 = $arIndexColumnsTmp;
				foreach($arIndexColumnsTmp2 as $i => $index_column)
					if(in_array($index_column, $arColumns))
						$arIndexColumnsTmp2[$i] = "<b>".$arIndexColumnsTmp2[$i]."</b>";
			?>
			<tr valign="top">
				<td><?echo htmlspecialcharsbx($index_name)?></td>
				<td><?echo implode("<br>", $arIndexColumnsTmp2)?></td>
			</tr>
			<?endforeach?>
			</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td>
		<?echo BeginNote(), GetMessage("PERFMON_IDETAIL_QUERY_TAB_NOTE"), EndNote(), "<br>";?>
		<table class="internal" style="width:100%">
		<tr class="heading">
			<td><?echo GetMessage("PERFMON_IDETAIL_QUERY")?></td>
			<?foreach(explode(",", $arSuggest["COLUMN_NAMES"]) as $column_name):?>
			<td><?echo htmlspecialcharsex($column_name)?></td>
			<?endforeach?>
		</tr>
		<?foreach($arQueries as $arQuery):?>
		<tr>
			<td align="left"><?
				echo str_replace(
					array(" ", "\n"),
					array(" &nbsp;", "<br>"),
					$arQuery["FORMATTED_SQL_TEXT"]
				);
			?></td>
			<?foreach($arQuery["STAT"] as $column_name => $ratio):?>
			<td style="text-align:center"><?
				if(isset($arQuery["WHERE"][$column_name]))
					echo htmlspecialcharsEx($arQuery["WHERE"][$column_name]);
				else
					echo htmlspecialcharsEx($arQuery["JOIN"][$column_name]);

				$proc = max($ratio*100, 0.0001);
				if($proc > 5)
					echo '<br><span class="errortext">',round($proc, 4),'%</span><br>'.GetMessage("PERFMON_IDETAIL_QUERY_IS_BAD");
				else
					echo '<br><span class="notetext">',round($proc, 4),'%</span><br>'.GetMessage("PERFMON_IDETAIL_QUERY_IS_GOOD");
			?></td>
			<?endforeach?>
		</tr>
		<?endforeach?>
		</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2" align="center">
		<?echo BeginNote(), GetMessage("PERFMON_IDETAIL_INDEX_NOTE"), EndNote(), "<br>";?>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("PERFMON_IDETAIL_QUERY")?></td>
	</tr>
	<tr>
		<td colspan="2"><?echo str_replace(
			array(" ", "\n"),
			array(" &nbsp;", "<br>"),
			$arSuggest["FORMATTED_SQL_TEXT"]
		);?></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("PERFMON_IDETAIL_EXPLAIN_BEFORE")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table class="internal">
				<tr class="heading">
					<td><?echo GetMessage("PERFMON_IDETAIL_F_SELECT_TYPE");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_TABLE");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_TYPE");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_POSSIBLE_KEYS");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_KEY");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_KEY_LEN");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_REF");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_ROWS");?></td>
					<td><?echo GetMessage("PERFMON_IDETAIL_F_EXTRA");?></td>
				</tr>
				<?foreach(unserialize($arSuggest["SQL_EXPLAIN"]) as $arRes):?>
					<tr>
						<td><?echo htmlspecialcharsEx($arRes["select_type"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["table"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["type"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["possible_keys"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["key"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["key_len"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["ref"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["rows"]);?></td>
						<td><?echo htmlspecialcharsEx($arRes["Extra"]);?></td>
					</tr>
				<?endforeach?>
			</table>
		</td>
	</tr>
	<tr>
		<td width="50%"><?echo GetMessage("PERFMON_IDETAIL_AVG_QUERY_TIME")?>:</td>
		<td width="50%"><?echo perfmon_NumberFormat($arSuggest["SQL_TIME"]/$arSuggest["SQL_COUNT"], 6)?></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("PERFMON_IDETAIL_INDEX")?></td>
	</tr>
	<?if($IndexExists == ""):?>
		<tr>
			<td><?echo GetMessage("PERFMON_IDETAIL_CREATE_INDEX_DDL")?>:</td>
			<?
				$prefix = substr("ix_perf_".trim($arSuggest["TABLE_NAME"], "`"), 0, 27)."_";
				$i = 1;
				while(array_key_exists($prefix.$i, $arIndexes))
					$i++;

				$ddl = $obTable->getCreateIndexDDL($arSuggest["TABLE_NAME"], $prefix.$i, $arIndexColumns);
			?>
			<td>
				<?echo htmlspecialcharsEx($ddl)?>
				<input type="hidden" name="ddl" value="<?echo htmlspecialcharsbx($ddl)?>">
				<input type="hidden" name="index_name" value="<?echo htmlspecialcharsbx($prefix.$i)?>">
				<input type="hidden" name="ID" value="<?echo $ID?>">
			</td>
		</tr>
		<?if($isAdmin)
		{?>
		<tr>
			<td>&nbsp;</td>
			<td valign="middle">
				<input type="submit" value="<?echo GetMessage("PERFMON_IDETAIL_CREATE_INDEX")?>" name="create_index" class="adm-btn-green">
				<span style="line-height:27px"><?echo GetMessage("PERFMON_IDETAIL_OR")?></span>
				<input type="submit" value="<?echo GetMessage("PERFMON_IDETAIL_BAN_INDEX")?>" name="ban_index">
			</td>
		</tr>
		<?}
		?>
	<?else:?>
		<tr>
			<td><?echo GetMessage("PERFMON_IDETAIL_CREATED_INDEX_DDL")?>:</td>
			<td><?echo $obTable->getCreateIndexDDL($arSuggest["TABLE_NAME"], $IndexExists, $arIndexes[$IndexExists])?></td>
		</tr>
		<tr>
			<td><?echo GetMessage("PERFMON_IDETAIL_DROP_INDEX_DDL")?>:</td>
			<?
				$ddl = "ALTER TABLE ".$arSuggest["TABLE_NAME"]." DROP INDEX ".$IndexExists;
			?>
			<td>
				<?echo htmlspecialcharsEx($ddl)?>
				<input type="hidden" name="ddl" value="<?echo htmlspecialcharsbx($ddl)?>">
				<input type="hidden" name="ID" value="<?echo $ID?>">
			</td>
		</tr>
		<?if($isAdmin)
		{?>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="submit" value="<?echo GetMessage("PERFMON_IDETAIL_DROP_INDEX")?>" name="drop_index">
			</td>
		</tr>
		<?}
		?>
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("PERFMON_IDETAIL_EXPLAIN_AFTER")?></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<table class="internal">
					<tr class="heading">
						<td><?echo GetMessage("PERFMON_IDETAIL_F_SELECT_TYPE");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_TABLE");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_TYPE");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_POSSIBLE_KEYS");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_KEY");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_KEY_LEN");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_REF");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_ROWS");?></td>
						<td><?echo GetMessage("PERFMON_IDETAIL_F_EXTRA");?></td>
					</tr>
					<?
					$rsExplain = $DB->Query("explain ".CPerfQuery::transform2select($arSuggest["SQL_TEXT"]));
					while($arRes = $rsExplain->Fetch()):
					?>
						<tr>
							<td><?echo htmlspecialcharsEx($arRes["select_type"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["table"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["type"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["possible_keys"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["key"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["key_len"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["ref"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["rows"]);?></td>
							<td><?echo htmlspecialcharsEx($arRes["Extra"]);?></td>
						</tr>
					<?endwhile?>
				</table>
			</td>
		</tr>
		<?
			$sql = CPerfQuery::transform2select($arSuggest["SQL_TEXT"]);
			$sql = preg_replace("/^\\s*select\\s+/is", "SELECT /*SQL_NO_CACHE*/ ", $sql);
			$stime = getmicrotime();
			$rs = $DB->Query($sql);
			while($rs->Fetch());
			$etime = getmicrotime();

			$ratio = ($arSuggest["SQL_TIME"]/$arSuggest["SQL_COUNT"])/($etime-$stime);
		?>
		<tr>
			<td><?echo GetMessage("PERFMON_IDETAIL_QUERY_TIME")?>:</td>
			<td><?echo perfmon_NumberFormat($etime - $stime, 6)?></td>
		</tr>
		<?
		if($ratio > 1)
		{?>
		<tr>
			<td><?echo GetMessage("PERFMON_IDETAIL_GAIN")?>:</td>
			<td><span class="notetext"><?echo perfmon_NumberFormat($ratio*100, 2), "%"?></span></td>
		</tr>
		<?}
		?>
	<?endif;?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("editform", $message);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
