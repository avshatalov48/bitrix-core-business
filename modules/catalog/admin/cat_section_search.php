<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

global $APPLICATION, $USER, $DB, $USER_FIELD_MANAGER;

CModule::IncludeModule("catalog");
if (
	!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
	&& !AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_VIEW)
)
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

$n = preg_replace("/[^a-zA-Z0-9_\\[\\]]/", "", $_GET["n"] ?? '');
$k = preg_replace("/[^a-zA-Z0-9_:]/", "", $_GET["k"] ?? '');
$m = isset($_GET["m"]) && $_GET["m"] === "y";

$APPLICATION->SetTitle(GetMessage("BX_MOD_CATALOG_ADMIN_CSS_TITLE"));

$entity_id = false;

$sTableID = 'tbl_iblock_section_search_';
$oSort = new CAdminSorting($sTableID, "NAME", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$arFilterFields = Array(
	"find_iblock_id",
	"find_section_id",
	"find_section_timestamp_1",
	"find_section_timestamp_2",
	"find_section_modified_by",
	"find_section_date_create_1",
	"find_section_date_create_2",
	"find_section_created_by",
	"find_section_name",
	"find_section_active",
	"find_section_section",
	"find_section_code",
	"find_section_external_id"
);
if($entity_id)
	$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $arFilterFields);

$section_id = (int)($find_section_section ?? 0);
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;
if($find_section_section<=0)
	$find_section_section=-1;

$IBLOCK_ID = 0;
if (isset($find_iblock_id))
{
	$IBLOCK_ID = (int)$find_iblock_id;
	if (0 >= $IBLOCK_ID)
	{
		$IBLOCK_ID = 0;
	}
}

if (0 === $IBLOCK_ID)
{
	$IBLOCK_ID = (int)($_REQUEST["IBLOCK_ID"] ?? 0);
	if (0 >= $IBLOCK_ID)
	{
		$IBLOCK_ID = 0;
	}
}

$arIBTYPE = false;
if($IBLOCK_ID > 0)
{
	$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
	if($arIBlock)
	{
		$arIBTYPE = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANGUAGE_ID);
		if(!$arIBTYPE)
			$APPLICATION->AuthForm(GetMessage("BX_MOD_CATALOG_ADMIN_CSS_BAD_BLOCK_TYPE_ID"));

		$bBadBlock = !CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");
	}
	else
	{
		$bBadBlock = true;
	}
	if($bBadBlock)
		$APPLICATION->AuthForm(GetMessage("BX_MOD_CATALOG_ADMIN_CSS_BAD_IBLOCK"));
}
else
{
	$arIBlock = array(
		"ID" => 0,
		"NAME" => "",
		"SECTIONS_NAME" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_SECTIONS"),
	);
}

############################################

$arFilter = array(
	"?NAME"		=> $find_section_name,
	"SECTION_ID"	=> $find_section_section,
	"ID"		=> $find_section_id,
	">=TIMESTAMP_X"	=> $find_section_timestamp_1,
	"<=TIMESTAMP_X"	=> $find_section_timestamp_2,
	"MODIFIED_BY"	=> !empty($find_section_modified_user_id) ? $find_section_modified_user_id : $find_section_modified_by,
	">=DATE_CREATE"	=> $find_section_date_create_1,
	"<=DATE_CREATE"	=> $find_section_date_create_2,
	"CREATED_BY"	=> !empty($find_section_created_user_id) ? $find_section_created_user_id : $find_section_created_by,
	"ACTIVE"	=> $find_section_active,
	"CODE"		=> $find_section_code,
	"EXTERNAL_ID"	=> $find_section_external_id,
);
if($entity_id)
	$USER_FIELD_MANAGER->AdminListAddFilter($entity_id, $arFilter);

if($find_section_section == "")
	unset($arFilter["SECTION_ID"]);
if (isset($arFilter['ID']) && $arFilter['ID'] > 0)
	unset($arFilter["SECTION_ID"]);

if($IBLOCK_ID > 0)
	$arFilter["IBLOCK_ID"] = $IBLOCK_ID;
else
	$arFilter["IBLOCK_ID"] = -1;

$arFilter["CHECK_PERMISSIONS"]="Y";
$arFilter["MIN_PERMISSION"] = "R";

// list header
$arHeaders = array(
	array(
		"id" => "ID",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_ID"),
		"sort" => "ID",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true,
		"align" => "center",
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_SORT"),
		"sort" => "SORT",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "CODE",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_CODE"),
		"sort" => "code",
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_XML_ID"),
	),
	array(
		"id" => "ELEMENT_CNT",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_ELEMENT_CNT"),
		"sort" => "ELEMENT_CNT",
		"align" => "right",
	),
	array(
		"id" => "SECTION_CNT",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_SECTION_CNT"),
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_TIMESTAMP"),
		"sort" => "TIMESTAMP_X",
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_MODIFIED_BY"),
		"sort" => "MODIFIED_BY",
	),
	array(
		"id" => "DATE_CREATE",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_DATE_CREATE"),
		"sort" => "DATE_CREATE",
	),
	array(
		"id" => "CREATED_BY",
		"content" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_CREATED_BY"),
		"sort" => "CREATED_BY",
	),
);
if($entity_id)
	$USER_FIELD_MANAGER->AdminListAddHeaders($entity_id, $arHeaders);
$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
if (!in_array('ID', $arVisibleColumns))
	$arVisibleColumns[] = 'ID';
if (!in_array('XML_ID', $arVisibleColumns))
	$arVisibleColumns[] = 'XML_ID';
$arVisibleColumnsMap = array();
foreach($arVisibleColumns as $value)
	$arVisibleColumnsMap[$value] = true;

if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
{
	$arFilter["CNT_ALL"] = "Y";
	$arFilter["ELEMENT_SUBSECTIONS"] = "N";
	$rsData = CIBlockSection::GetList(array($by=>$order), $arFilter, true, $arVisibleColumns);
}
else
{
	$rsData = CIBlockSection::GetList(array($by=>$order), $arFilter, false, $arVisibleColumns);
}

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint($arIBlock["SECTIONS_NAME"]));

$strPath = "";
$jsPath  = "";
if(intval($find_section_section) > 0)
{
	$nav = CIBlockSection::GetNavChain(
		$IBLOCK_ID,
		$find_section_section,
		[
			'ID',
			'NAME',
		],
		true
	);
	foreach ($nav as $ar_nav)
	{
		$strPath .= htmlspecialcharsbx($ar_nav["NAME"], ENT_QUOTES)."&nbsp;/&nbsp;";
		$jsPath .= htmlspecialcharsbx(CUtil::JSEscape($ar_nav["NAME"]), ENT_QUOTES)."&nbsp;/&nbsp;";
	}
	unset($nav);
}

$arUsersCache = array();

while($arRes = $rsData->NavNext(true, "f_"))
{
	$sec_list_url = 'cat_section_search.php?IBLOCK_ID='.$IBLOCK_ID.'&amp;lang='.LANGUAGE_ID.'&amp;find_section_section='.$f_ID.'&amp;n='.urlencode($n).'&amp;k='.urlencode($k).($m? "&amp;m=y": "");

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($entity_id)
		$USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);

	$row->AddViewField("NAME", '<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionAjaxReload($sec_list_url).'; return false;" title="'.GetMessage("BX_MOD_CATALOG_ADMIN_CSS_LIST").'">'.$f_NAME.'</a><div style="display:none" id="name_'.$f_ID.'">'.$strPath.$f_NAME.'&nbsp;/&nbsp;'.'</div>');

	$row->AddCheckField("ACTIVE", false);

	if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
		$row->AddViewField("ELEMENT_CNT", $f_ELEMENT_CNT.'('.intval(CIBlockSection::GetSectionElementsCount($f_ID, Array("CNT_ALL"=>"Y"))).')');

	if(array_key_exists("SECTION_CNT", $arVisibleColumnsMap))
	{
		$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, "SECTION_ID"=>$f_ID);
		$row->AddViewField("SECTION_CNT", '<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionAjaxReload($sec_list_url).'; return false;" title="'.GetMessage("BX_MOD_CATALOG_ADMIN_CSS_LIST").'">'.intval(CIBlockSection::GetCount($arFilter)).'</a>');
	}

	if(array_key_exists("MODIFIED_BY", $arVisibleColumnsMap) && intval($f_MODIFIED_BY) > 0)
	{
		if(!array_key_exists($f_MODIFIED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_MODIFIED_BY);
			$arUsersCache[$f_MODIFIED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_MODIFIED_BY])
			$row->AddViewField("MODIFIED_BY", "[".$f_MODIFIED_BY."]&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]);
	}

	if(array_key_exists("CREATED_BY", $arVisibleColumnsMap) && intval($f_CREATED_BY) > 0)
	{
		if(!array_key_exists($f_CREATED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_CREATED_BY);
			$arUsersCache[$f_CREATED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_MODIFIED_BY])
			$row->AddViewField("CREATED_BY", "[".$f_CREATED_BY."]&nbsp;(".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"]);
	}

	$row->AddActions(array(
		array(
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_SELECT"),
			"ACTION"=>"javascript:SelEl('".(!empty($get_xml_id) ? $f_XML_ID : $f_ID)."', '".$jsPath.htmlspecialcharsbx(CUtil::JSEscape($arRes["NAME"]), ENT_QUOTES)."&nbsp;/&nbsp;"."')"
		),
	));
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if($m)
{
	$lAdmin->AddGroupActionTable(array(
		array(
			"action" => "SelAll()",
			"value" => "select",
			"type" => "button",
			"name" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_SELECT"),
			)
	), array("disable_action_target"=>true));
}

$lAdmin->AddAdminContextMenu(array(), false);

if($IBLOCK_ID > 0)
{
	$chain = $lAdmin->CreateChain();
	if(intval($find_section_section)>0)
	{
		$nav = CIBlockSection::GetNavChain(
			$IBLOCK_ID,
			$find_section_section,
			[
				'ID',
				'NAME',
			],
			true
		);
		foreach ($nav as $ar_nav)
		{
			if($find_section_section==$ar_nav["ID"])
			{
				$chain->AddItem(array(
					"TEXT" => $ar_nav["NAME"],
				));
			}
			else
			{
				$chain->AddItem(array(
					"TEXT" => $ar_nav["NAME"],
					"LINK" => 'cat_section_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section=-1'.'&amp;n='.urlencode($n).'&amp;k='.urlencode($k).($m? "&amp;m=y": ""),
					"ONCLICK" => $lAdmin->ActionAjaxReload('cat_section_search.php?lang='.LANGUAGE_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$ar_nav["ID"].'&n='.urlencode($n).'&k='.urlencode($k).($m? "&m=y": "")).';return false;',
				));
			}
		}
	}
	$lAdmin->ShowChain($chain);
}
else
{
	$lAdmin->BeginPrologContent();
	$message = new CAdminMessage(array("MESSAGE"=>GetMessage("BX_MOD_CATALOG_ADMIN_CSS_IBLOCK"), "TYPE"=>"OK"));
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$lAdmin->CheckListMode();

/***************************************************************************
				HTML form
****************************************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

$chain = new CAdminChain("main_navchain");
$chain->AddItem(array(
	"TEXT" => htmlspecialcharsEx($arIBlock["NAME"]),
	"LINK" => 'cat_section_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$IBLOCK_ID.'&amp;find_section_section=0'.'&amp;n='.urlencode($n).'&amp;k='.urlencode($k).($m? "&amp;m=y": ""),
	"ONCLICK" => $lAdmin->ActionAjaxReload('cat_section_search.php?lang='.LANGUAGE_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section=0'.'&n='.urlencode($n).'&k='.urlencode($k).($m? "&m=y": "")).';return false;',
));
$chain->Show();
?>
<form method="GET" name="find_section_form" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = Array(
	"iblock_id" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_IBLOCK_ID"),
	"name" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_NAME"),
	"id" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_ID"),
	"timestamp_x" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_TIMESTAMP"),
	"modified_by" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_MODIFIED_BY"),
	"date_create" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_DATE_CREATE"),
	"created_by" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_CREATED_BY"),
	"code" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_CODE"),
	"xml_id" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_XML_ID"),
	"active" => GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_ACTIVE"),
);
if ($entity_id)
	$USER_FIELD_MANAGER->AddFindFields($entity_id, $arFindFields);

$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);

$oFilter->Begin();
?>
<script type="text/javascript">
function SelEl(id, name)
{
	<?if($m):?>
	window.opener.InS<?echo md5($n)?>(id, name);
	<?else:?>
	el = window.opener.document.getElementById('<?echo $n?>[<?echo $k?>]');
	if(!el)
		el = window.opener.document.getElementById('<?echo $n?>');
	if(el)
		el.value = id;
	el = window.opener.document.getElementById('<?echo md5($n)?>_<?echo $k?>_link');
	if(!el)
		el = window.opener.document.getElementById('<?echo $n?>_link');
	if(el)
		el.innerHTML = name;
	window.close();
	<?endif;?>
}

function SelAll()
{
	var frm = document.getElementById('form_tbl_iblock_section_search_<?=intval($arIBlock["ID"])?>');
	if(frm)
	{
		var e = frm.elements['ID[]'];
		if(e && e.nodeName)
		{
			var v = e.value;
			var n = document.getElementById('name_'+v).innerHTML;
			SelEl(v, n);
		}
		else if(e)
		{
			var l = e.length;
			for(i=0;i<l;i++)
			{
				var a = e[i].checked;
				if (a == true)
				{
					var v = e[i].value;
					var n = document.getElementById('name_'+v).innerHTML;
					SelEl(v, n);
				}
			}
		}
		window.close();
	}
}
</script>
	<tr>
		<td><b><? echo GetMessage('BX_MOD_CATALOG_ADMIN_CSS_HEAD_IBLOCK_ID'); ?></b></td>
		<td><? echo GetIBlockDropDownListEx($IBLOCK_ID, 'find_iblock_type_id', 'find_iblock_id'); ?></td>
	</tr>
	<tr>
		<td><b><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_NAME")?>:</b></td>
		<td><input type="text" name="find_section_name" value="<?echo htmlspecialcharsEx($find_section_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_ID")?>:</td>
		<td><input type="text" name="find_section_id" size="47" value="<?echo htmlspecialcharsbx($find_section_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_TIMESTAMP").":"?></td>
		<td><?echo CalendarPeriod("find_section_timestamp_1", htmlspecialcharsbx($find_section_timestamp_1), "find_section_timestamp_2", htmlspecialcharsbx($find_section_timestamp_2), "find_section_form","Y")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_MODIFIED_BY")?>:</td>
		<td><input type="text" name="find_section_modified_user_id" value="<?echo htmlspecialcharsEx($find_section_modified_by)?>" size="3">&nbsp;<?
		$gr_res = CIBlock::GetGroupPermissions($IBLOCK_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
			$res = CUser::GetList("NAME", "ASC", Array("GROUP_MULTI"=>$res));
		?><select name="find_section_modified_by">
		<option value=""><?echo GetMessage("IBLOCK_ALL")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($find_section_modified_by==$arr["ID"]?" selected":"").">(".htmlspecialcharsEx($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_DATE_CREATE").":"?></td>
		<td><?echo CalendarPeriod("find_section_date_create_1", htmlspecialcharsEx($find_section_date_create_1), "find_section_date_create_2", htmlspecialcharsEx($find_section_date_create_2), "find_section_form")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_CREATED_BY")?>:</td>
		<td><input type="text" name="find_section_created_user_id" value="<?echo htmlspecialcharsEx($find_section_created_by)?>" size="3">&nbsp;<?
		$gr_res = CIBlock::GetGroupPermissions($IBLOCK_ID);
		$res = Array(1);
		foreach($gr_res as $gr=>$perm)
			if($perm>"R")
				$res[] = $gr;
		$res = CUser::GetList("NAME", "ASC", Array("GROUP_MULTI"=>$res));
		?><select name="find_section_created_by">
		<option value=""><?echo GetMessage("IBLOCK_ALL")?></option><?
		while($arr = $res->Fetch())
			echo "<option value='".$arr["ID"]."'".($find_section_created_by==$arr["ID"]?" selected":"").">(".htmlspecialcharsEx($arr["LOGIN"].") ".$arr["NAME"]." ".$arr["LAST_NAME"])."</option>";
		?></select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_CODE")?>:</td>
		<td><input type="text" name="find_section_code" size="47" value="<?echo htmlspecialcharsbx($find_section_code)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_XML_ID")?>:</td>
		<td><input type="text" name="find_section_external_id" size="47" value="<?echo htmlspecialcharsbx($find_section_external_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("BX_MOD_CATALOG_ADMIN_CSS_HEAD_ACTIVE")?>:</td>
		<td>
			<select name="find_section_active" >
				<option value=""><?=htmlspecialcharsEx(GetMessage('IBLOCK_ALL'))?></option>
				<option value="Y"<?if($find_section_active=="Y")echo " selected"?>><?=htmlspecialcharsEx(GetMessage("IBLOCK_YES"))?></option>
				<option value="N"<?if($find_section_active=="N")echo " selected"?>><?=htmlspecialcharsEx(GetMessage("IBLOCK_NO"))?></option>
			</select>
		</td>
	</tr>
<?
if ($entity_id)
	$USER_FIELD_MANAGER->AdminListShowFilter($entity_id);
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage().'?IBLOCK_ID='.$IBLOCK_ID, "form"=>"find_section_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
