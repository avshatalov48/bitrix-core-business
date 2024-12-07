<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "favorites/favorite_admin.php");

if(!$USER->CanDoOperation('edit_own_profile') && !$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_favorites";
if($isAdmin)
	$oSort = new CAdminSorting($sTableID, "id", "desc");
else
	$oSort = new CAdminSorting($sTableID, "sort", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter() // проверка введенных полей
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	$date_1_ok = false;
	$date1_stm = MkDateTime(FmtDate($find_date1,"D.M.Y"),"d.m.Y");
	$date2_stm = MkDateTime(FmtDate($find_date2,"D.M.Y")." 23:59","d.m.Y H:i");
	if (!$date1_stm && trim($find_date1) <> '')
		$lAdmin->AddFilterError(GetMessage("MAIN_WRONG_DATE_FROM"));
	else
		$date_1_ok = true;
	if(!$date2_stm && trim($find_date2) <> '')
		$lAdmin->AddFilterError(GetMessage("MAIN_WRONG_DATE_TILL"));
	elseif($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
		$lAdmin->AddFilterError(GetMessage("MAIN_FROM_TILL_DATE"));
	return empty($lAdmin->arFilterErrors);
}

$FilterArr = Array(
	"find",
	"find_type",
	"find_name",
	"find_url",
	"find_id",
	"find_language_id",
	"find_date1",
	"find_date2",
	"find_modified",
	"find_created",
	"find_keywords",
	"find_module_id",
	"find_common",
	"find_user_id",
	"find_menu_id"
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array();
if(CheckFilter())
{
	$arFilter = Array(
		"NAME"			=> ($find!="" && $find_type == "name"? $find:$find_name),
		"URL"			=> ($find!="" && $find_type == "url"? $find:$find_url),
		"ID"			=> ($find!="" && $find_type == "id"? $find:$find_id),
		"LANGUAGE_ID"	=> $find_language_id,
		"DATE1"			=> $find_date1,
		"DATE2"			=> $find_date2,
		"MODIFIED"		=> $find_modified,
		"CREATED"		=> $find_created,
		"KEYWORDS"		=> $find_keywords,
		"MODULE_ID"		=> $find_module_id,
		"COMMON"		=> $find_common,
		"USER_ID"		=> $find_user_id,
		"MENU_ID"		=> $find_menu_id,
	);
}
if(!$isAdmin)
	$arFilter["USER_ID"] = $USER->GetID();

if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$ID = intval($ID);
		if($ID <= 0)
			continue;
		if(!$lAdmin->IsUpdated($ID))
			continue;
		if(!$isAdmin)
		{
			$db_fav = CFavorites::GetByID($ID);
			if(($db_fav_arr = $db_fav->Fetch()) && $USER->GetID() <> $db_fav_arr["USER_ID"])
				continue;
		}
		if(!CFavorites::Update($ID, $arFields))
		{
			$e = $APPLICATION->GetException();
			$lAdmin->AddUpdateError(($e? $e->GetString():GetMessage("fav_list_err")), $ID);
		}
	}
}

if(($arID = $lAdmin->GroupAction()))
{
	if (isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
	{
		$rsData = CFavorites::GetList(array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		$ID = intval($ID);
		if($ID <= 0)
			continue;
		if(!$isAdmin)
		{
			$db_fav = CFavorites::GetByID($ID);
			if(($db_fav_arr = $db_fav->Fetch()) && $USER->GetID() <> $db_fav_arr["USER_ID"])
				continue;
		}
		switch($_REQUEST['action'])
		{
			case "delete":
				if(!CFavorites::Delete($ID))
					$lAdmin->AddGroupError(GetMessage("fav_list_err_del"), $ID);
				break;
		}
	}
}

$rsData = CFavorites::GetList(array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("fav_list_nav")));

$aHeaders = array(
	array("id"=>"NAME", "content"=>GetMessage("MAIN_TITLE"), "sort"=>"name", "default"=>true),
	array("id"=>"URL", "content"=>GetMessage("fav_list_head_link"), "sort"=>"url", "default"=>true),
	array("id"=>"C_SORT", "content"=>GetMessage("MAIN_SORT"), "sort"=>"sort", "align"=>"right", "default"=>true),
	array("id"=>"LANGUAGE_ID", "content"=>GetMessage("fav_list_head_lang"), "sort"=>"language_id", "default"=>true),
	array("id"=>"MENU_ID", "content"=>GetMessage("fav_list_flt_menu_id"), "sort"=>"menu_id", "default"=>true),
);
if($isAdmin)
{
	$aHeaders[] = array("id"=>"COMMON", "content"=>GetMessage("fav_list_head_common"), "sort"=>"common", "default"=>true);
	$aHeaders[] = array("id"=>"USER_ID", "content"=>GetMessage("fav_list_head_user"), "sort"=>"user_id", "default"=>true);
	$aHeaders[] = array("id"=>"MODULE_ID", "content"=>GetMessage("MAIN_MODULE"), "sort"=>"module_id", "default"=>true);
}
$aHeaders[] = array("id"=>"TIMESTAMP_X", "content"=>GetMessage("MAIN_TIMESTAMP_X"), "sort"=>"timestamp_x", "default"=>false);
$aHeaders[] = array("id"=>"MODIFIED_BY", "content"=>GetMessage("MAIN_MODIFIED_BY"), "sort"=>"modified_by", "default"=>false);
$aHeaders[] = array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true);

$lAdmin->AddHeaders($aHeaders);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("MENU_ID", array("size"=>20));
	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", '<a href="'.$f_URL.'" title="'.GetMessage("fav_list_go_title").'">'.$f_NAME.'</a>');
	$row->AddInputField("URL", array("size"=>20));
	$row->AddViewField("URL", '<a href="favorite_edit.php?ID='.$f_ID.'&amp;lang='.LANG.'" title="'.GetMessage("fav_list_edit_title").'">'.(mb_strlen($f_URL) > 60 && $_REQUEST["mode"]<>'excel'? mb_substr($f_URL, 0, 60)."...":$f_URL).'</a>');
	$row->AddInputField("C_SORT", array("size"=>5));
	$row->AddViewField("MODIFIED_BY", '[<a title="'.GetMessage("MAIN_USER_PROFILE").'" href="user_edit.php?lang='.LANG.'&amp;ID='.$f_MODIFIED_BY.'">'.$f_MODIFIED_BY.'</a>] ('.$f_M_LOGIN.') '.$f_M_USER_NAME);
	$row->AddViewField("USER_ID", ($f_USER_ID>0? '[<a title="'.GetMessage("MAIN_USER_PROFILE").'" href="user_edit.php?lang='.LANG.'&amp;ID='.$f_USER_ID.'">'.$f_USER_ID.'</a>] ('.$f_LOGIN.') '.$f_USER_NAME:''));
	$row->AddViewField("COMMON", ($f_COMMON == "Y"? GetMessage("fav_list_yes"):GetMessage("fav_list_no")));

	$arActions = Array(
		array(
			"ICON"=>"edit",
			"DEFAULT"=>true,
			"TEXT"=>GetMessage("fav_list_edit"),
			"ACTION"=>$lAdmin->ActionRedirect("favorite_edit.php?ID=".$f_ID)
		),
		array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("fav_list_del"),
			"ACTION"=>"if(confirm('".GetMessage("fav_list_del_conf")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		),
	);
	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
));

$aContext = array(
	array(
		"TEXT"=>GetMessage("fav_list_add"),
		"LINK"=>"favorite_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("fav_list_add_title"),
		"ICON"=>"btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("MAIN_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<?

$arFRows = 	array(
		"find_name" => GetMessage("fav_list_flt_name"),
		"find_url" => GetMessage("fav_list_flt_url"),
		"find_id" => GetMessage("fav_list_flt_id"),
		"find_language_id" => GetMessage("fav_list_flt_lang"),
		"find_date" => GetMessage("fav_list_flt_date"),
		"find_modified" => GetMessage("fav_list_flt_modified"),
		"find_created" => GetMessage("fav_list_flt_created"),
		"find_menu_id" => GetMessage("fav_list_flt_menu_id"),
		"find_keywords" =>GetMessage("fav_list_flt_desc")
	);

if($isAdmin)
{
	$arFRows["find_common"] = GetMessage("fav_list_flt_comon");
	$arFRows["find_user_id"] = GetMessage("fav_list_flt_user");
	$arFRows["find_module_id"] = GetMessage("fav_list_flt_modules");
}

$oFilter = new CAdminFilter($sTableID."_filter", $arFRows);

?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?echo GetMessage("fav_list_flt_find")?></b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?echo GetMessage("fav_list_flt_find_title")?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage("fav_list_flt_name1"),
				GetMessage("fav_list_flt_url1"),
				"ID",
			),
			"reference_id" => array(
				"name",
				"url",
				"id",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("fav_list_flt_name2")?></td>
	<td><input type="text" name="find_name" size="40" value="<?echo htmlspecialcharsbx($find_name)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("fav_list_flt_url2")?></td>
	<td><input type="text" name="find_url" size="40" value="<?echo htmlspecialcharsbx($find_url)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_ID")?></td>
	<td><input type="text" name="find_id" size="40" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("fav_list_flt_lang2")?></td>
	<td><?echo CLanguage::SelectBox("find_language_id", $find_language_id, GetMessage("fav_list_flt_all"))?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_DATE").":"?></td>
	<td><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_MODIFIED_BY")?></td>
	<td><input type="text" name="find_modified" value="<?echo htmlspecialcharsbx($find_modified)?>" size="40"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_F_CREATED_BY")?></td>
	<td><input type="text" name="find_created" value="<?echo htmlspecialcharsbx($find_created)?>" size="40"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("fav_list_flt_menu_id")?>:</td>
	<td><input type="text" name="find_menu_id" size="40" value="<?echo htmlspecialcharsbx($find_menu_id)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("fav_list_flt_desc1")?></td>
	<td><input type="text" name="find_keywords" value="<?echo htmlspecialcharsbx($find_keywords)?>" size="40"><?=ShowFilterLogicHelp()?></td>
</tr>
<?if($isAdmin):?>
<tr>
	<td><?echo GetMessage("fav_list_flt_common1")?></td>
	<td><select name="find_common">
		<option value=""><?echo GetMessage("fav_list_flt_all")?></option>
		<option value="Y"<?if($find_common == "Y") echo " selected"?>><?echo GetMessage("fav_list_yes")?></option>
		<option value="N"<?if($find_common == "N") echo " selected"?>><?echo GetMessage("fav_list_no")?></option>
	</select></td>
</tr>
<tr>
	<td><?echo GetMessage("fav_list_flt_user1")?></td>
	<td><input type="text" name="find_user_id" size="40" value="<?echo htmlspecialcharsbx($find_user_id)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("fav_list_flt_mod1")?></td>
	<td><?
	$a = CModule::GetDropDownList();
	while ($ar = $a->fetch())
	{
		$ref_id[] = $ar["REFERENCE_ID"];
		$ref[] = $ar["REFERENCE"];
	}
	$arr = array("reference"=>$ref, "reference_id"=>$ref_id);
	echo SelectBoxFromArray("find_module_id", $arr, $find_module_id, GetMessage("MAIN_ALL"));
	?></td>
</tr>
<?endif;?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"form1"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
