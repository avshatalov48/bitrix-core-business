<?

use Bitrix\Main\Loader;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('catalog');
if (
	!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
	&& !AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_VIEW)
)
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/include.php");

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

$sTableID = "tbl_catalog_product_search";

$oSort = new CAdminSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$IBLOCK_ID = (int)($IBLOCK_ID ?? 0);

$dbIBlock = CIBlock::GetByID($IBLOCK_ID);
if (!($arIBlock = $dbIBlock->Fetch()))
{
	$dbIBlock = CIBlock::GetList(array("NAME"=>"ASC"), array("MIN_PERMISSION"=>"R"));
	$arIBlock = $dbIBlock->Fetch();
	$IBLOCK_ID = intval($arIBlock["ID"]);
}

$bBadBlock = !CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");

if (!$bBadBlock)
{
	$arFilterFields = array(
		"filter_section",
		"filter_subsections",
		"filter_id_start",
		"filter_id_end",
		"filter_timestamp_from",
		"filter_timestamp_to",
		"filter_active",
		"filter_intext",
		"filter_name"
	);

	$lAdmin->InitFilter($arFilterFields);

	$arFilter = array();

	$arFilter = array(
		"WF_PARENT_ELEMENT_ID" => false,
		"IBLOCK_ID" => $IBLOCK_ID,
		"SECTION_ID" => $filter_section,
		"ACTIVE" => $filter_active,
		"?NAME" => $filter_name,
		"?SEARCHABLE_CONTENT" => $filter_intext,
		"SHOW_NEW" => "Y"
	);

	if (intval($filter_section) < 0 || $filter_section == '')
		unset($arFilter["SECTION_ID"]);
	elseif ($filter_subsections=="Y")
	{
		if ($arFilter["SECTION_ID"]==0)
			unset($arFilter["SECTION_ID"]);
		else
			$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
	}

	if (!empty(${"filter_id_start"})) $arFilter[">=ID"] = ${"filter_id_start"};
	if (!empty(${"filter_id_end"})) $arFilter["<=ID"] = ${"filter_id_end"};
	if (!empty(${"filter_timestamp_from"})) $arFilter["DATE_MODIFY_FROM"] = ${"filter_timestamp_from"};
	if (!empty(${"filter_timestamp_to"})) $arFilter["DATE_MODIFY_TO"] = ${"filter_timestamp_to"};

	$dbResultList = CIBlockElement::GetList(
		array($by => $order),
		$arFilter,
		false,
		array("nPageSize" => 20)
	);

	$dbResultList = new CAdminResult($dbResultList, $sTableID);
	$dbResultList->NavStart();

	$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("sale_prod_search_nav")));

	$arHeaders = array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
		array("id"=>"ACTIVE", "content"=>GetMessage("SPS_ACT"), "sort"=>"active", "default"=>true),
		array("id"=>"NAME", "content"=>GetMessage("SPS_NAME"), "sort"=>"name", "default"=>true),
		array("id"=>"ACT", "content"=>"&nbsp;", "default"=>true),
	);

	$lAdmin->AddHeaders($arHeaders);

	$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

	while ($arItems = $dbResultList->NavNext(true, "f_"))
	{
		$row =& $lAdmin->AddRow($f_ID, $arItems);

		$row->AddField("ID", $f_ID);
		$row->AddField("ACTIVE", $f_ACTIVE);
		$row->AddField("NAME", $f_NAME);

		$URL = CIBlock::ReplaceDetailUrl($arItems["DETAIL_PAGE_URL"], $arItems, true);
		$row->AddField("ACT", "<a href=\"javascript:void(0)\" onClick=\"SelEl(".$arItems["ID"].", '".htmlspecialcharsbx(str_replace("'", "\'", str_replace("\\", "\\\\", $arItems["NAME"])))."', '".htmlspecialcharsbx(str_replace("'", "\'", str_replace("\\", "\\\\", $URL)))."')\">".GetMessage("SPS_SELECT")."</a>");
	}

	$lAdmin->AddFooter(
		array(
			array(
				"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
				"value" => $dbResultList->SelectedRowsCount()
			),
		)
	);
}
else
{
	echo ShowError(GetMessage("SPS_NO_PERMS").".");
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SPS_SEARCH_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

$func_name = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST['func_name'] ?? '');
$form_name = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST['form_name'] ?? '');
$field_name = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST['field_name'] ?? '');
$field_name_name = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST['field_name_name'] ?? '');
$field_name_url = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST['field_name_url'] ?? '');
$alt_name = preg_replace("/[^a-z0-9_\\[\\]:]/i", "", $_REQUEST['alt_name'] ?? '');
?>

<script type="text/javascript">
function SelEl(id, name, url)
{
	<?if ($new_value=="Y"):?>
		window.opener.<?= $func_name ?>(id, name, url);
	<?else:?>
		el = eval("window.opener.document.<?= $form_name ?>.<?= $field_name ?>");
		if(el)
			el.value = id;
		<?if ($field_name_name <> ''):?>
			el = eval("window.opener.document.<?= $form_name ?>.<?= $field_name_name ?>");
			if(el)
				el.value = name;
		<?endif;?>
		<?if ($field_name_url <> ''):?>
			el = eval("window.opener.document.<?= $form_name ?>.<?= $field_name_url ?>");
			if(el)
				el.value = url;
		<?endif;?>
		<?if ($alt_name <> ''):?>
			el = window.opener.document.getElementById("<?= $alt_name ?>");
			if(el)
				el.innerHTML = name;
		<?endif;?>
		window.close();
	<?endif;?>
}
</script>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="field_name" value="<?echo htmlspecialcharsbx($field_name)?>">
	<input type="hidden" name="field_name_name" value="<?echo htmlspecialcharsbx($field_name_name)?>">
	<input type="hidden" name="field_name_url" value="<?echo htmlspecialcharsbx($field_name_url)?>">
	<input type="hidden" name="alt_name" value="<?echo htmlspecialcharsbx($alt_name)?>">
	<input type="hidden" name="form_name" value="<?echo htmlspecialcharsbx($form_name)?>">
	<input type="hidden" name="func_name" value="<?echo htmlspecialcharsbx($func_name)?>">
	<input type="hidden" name="new_value" value="<?echo htmlspecialcharsbx($new_value)?>">
<?
$arIBTYPE = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANG);

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID (".GetMessage("SPS_ID_FROM_TO").")",
		GetMessage("SPS_TIMESTAMP"),
		($arIBTYPE["SECTIONS"]=="Y"? GetMessage("SPS_SECTION") : null),
		GetMessage("SPS_ACTIVE"),
		GetMessage("SPS_NAME"),
		GetMessage("SPS_DESCR"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("SPS_CATALOG") ?>:</td>
		<td>
			<select name="IBLOCK_ID">
			<?
			$db_iblocks = CIBlock::GetList(array("NAME"=>"ASC"));
			ClearVars('str_iblock_');
			while ($db_iblocks->ExtractFields("str_iblock_"))
			{
				?><option value="<?=$str_iblock_ID?>"<?if($IBLOCK_ID==$str_iblock_ID)echo " selected"?>><?=$str_iblock_NAME?> [<?=$str_iblock_LID?>] (<?=$str_iblock_ID?>)</option><?
			}
			?>
			</select>
		</td>
	</tr>

	<tr>
		<td>ID (<?= GetMessage("SPS_ID_FROM_TO") ?>):</td>
		<td>
			<nobr>
			<input type="text" name="filter_id_start" size="10" value="<?echo htmlspecialcharsex($filter_id_start)?>">
			...
			<input type="text" name="filter_id_end" size="10" value="<?echo htmlspecialcharsex($filter_id_end)?>">
			</nobr>
		</td>
	</tr>

	<tr>
		<td  nowrap><?= GetMessage("SPS_TIMESTAMP") ?>:</td>
		<td nowrap><? echo CalendarPeriod("filter_timestamp_from", htmlspecialcharsex($filter_timestamp_from), "filter_timestamp_to", htmlspecialcharsex($filter_timestamp_to), "form1")?></td>
	</tr>

<?
if ($arIBTYPE["SECTIONS"]=="Y"):
?>
		<tr>
			<td nowrap valign="top"><?= GetMessage("SPS_SECTION") ?>:</td>
			<td nowrap>
				<select name="filter_section">
					<option value="">(<?= GetMessage("SPS_ANY") ?>)</option>
					<option value="0"<?if($filter_section=="0")echo" selected"?>><?= GetMessage("SPS_TOP_LEVEL") ?></option>
					<?
					$bsections = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$IBLOCK_ID));
					while($bsections->ExtractFields("s_")):
						?><option value="<?echo $s_ID?>"<?if($s_ID==$filter_section)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $s_DEPTH_LEVEL)?><?echo $s_NAME?></option><?
					endwhile;
					?>
				</select><br>
				<input type="checkbox" name="filter_subsections" value="Y"<?if($filter_subsections=="Y")echo" checked"?>> <?= GetMessage("SPS_INCLUDING_SUBS") ?>
			</td>
		</tr>
<?
endif;
?>

	<tr>
		<td nowrap><?= GetMessage("SPS_ACTIVE") ?>:</td>
		<td nowrap>
			<select name="filter_active">
				<option value=""><?=htmlspecialcharsex("(".GetMessage("SPS_ANY").")")?></option>
				<option value="Y"<?if($filter_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("SPS_YES"))?></option>
				<option value="N"<?if($filter_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("SPS_NO"))?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td nowrap><?= GetMessage("SPS_NAME") ?>:</td>
		<td nowrap>
			<input type="text" name="filter_name" value="<?echo htmlspecialcharsex($filter_name)?>" size="30">
		</td>
	</tr>
	<tr>
		<td nowrap><?= GetMessage("SPS_DESCR") ?>:</td>
		<td nowrap>
			<input type="text" name="filter_intext" size="50" value="<?echo htmlspecialcharsex(${"filter_intext"})?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
<?
$oFilter->Buttons();
?>
<input type="submit" name="set_filter" value="<?echo GetMessage("prod_search_find")?>" title="<?echo GetMessage("prod_search_find_title")?>">
<input type="submit" name="del_filter" value="<?echo GetMessage("prod_search_cancel")?>" title="<?echo GetMessage("prod_search_cancel_title")?>">
<?
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>
<br>
<input type="button" class="typebutton" value="<?= GetMessage("SPS_CLOSE") ?>" onClick="window.close();">
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>
