<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/prolog.php");
Loader::includeModule('advertising');

$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();
$isAdmin = CAdvContract::IsAdmin();

if(!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

/***************************************************************************
						Обработка GET | POST
****************************************************************************/
$sTableID = "tbl_adv_type_list";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "s_sort", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_type",
	"find_sid",
	"find_sid_exact_match",
	"find_date_modify_1",
	"find_date_modify_2",
	"find_active",
	"find_name",
	"find_name_exact_match",
	"find_description",
	"find_description_exact_match"
	);

$lAdmin->InitFilter($FilterArr);

InitBVar($find_sid_exact_match);
InitBVar($find_name_exact_match);
InitBVar($find_description_exact_match);
$arFilter = Array(
	"SID"					=> ($find!='' && $find_type == "sid"? $find: $find_sid),
	"SID_EXACT_MATCH"			=> $find_sid_exact_match,
	"DATE_MODIFY_1"			=> $find_date_modify_1,
	"DATE_MODIFY_2"			=> $find_date_modify_2,
	"ACTIVE"					=> $find_active,
	"NAME"					=> ($find!='' && $find_type == "name"? $find: $find_name),
	"NAME_EXACT_MATCH"			=> $find_name_exact_match,
	"DESCRIPTION"				=> ($find!='' && $find_type == "description"? $find: $find_description),
	"DESCRIPTION_EXACT_MATCH"	=> $find_description_exact_match,
	);

if($lAdmin->EditAction() && $isAdmin)
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$DB->StartTransaction();
		$ID = trim($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		if(!CAdvType::Set($arFields, $ID))
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$ob->LAST_ERROR, $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

// обработка действий групповых и одиночных
if(($arID = $lAdmin->GroupAction()) && $isAdmin)
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CAdvType::GetList('', '', $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['SID'];
	}

	foreach($arID as $ID)
	{
		$ob = new CAdvType;

		if($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			if(!$ob->Delete($ID))
			{
				$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
			}
			break;
		case "activate":
		case "deactivate":
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Set($arFields, $ID))
				$lAdmin->AddGroupError(GetMessage("MAIN_EDIT_ERROR").$ob->LAST_ERROR, $ID);
			break;
		}
	}
}

global $by, $order;

$rsAdvType = CAdvType::GetList($by, $order, $arFilter);

$rsData = new CAdminResult($rsAdvType, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("AD_PAGES")));
$Headers = Array(
	array("id"=>"SID", "content"=>"ID", "sort"=>"s_sid", "default"=>true),
	array("id"=>"DATE_MODIFY", "content"=>GetMessage("AD_DATE_MODIFY"), "sort"=>"s_date_modify", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("AD_ACTIVE"), "sort"=>"s_active", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("AD_SORT"), "sort"=>"s_sort", "default"=>true, "align"=>"right"),
	array("id"=>"NAME", "content"=>GetMessage("AD_NAME"), "sort"=>"s_name", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage("AD_DESCRIPTION"), "sort"=>"s_description", "default"=>true),
	array("id"=>"BANNER_COUNT", "content"=>GetMessage("AD_BANNERS"), "sort"=>"s_banners", "default"=>true, "align"=>"right"),
	);
$lAdmin->AddHeaders($Headers);
while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_SID, $arRes, "adv_type_edit.php?SID=".$f_SID, GetMessage("ADV_EDIT_TITLE"));
	$row->AddViewField("SID", "<a href='adv_type_edit.php?lang=".LANGUAGE_ID."&SID=".$f_SID."' title='".GetMessage("ADV_EDIT_TITLE")."'>".$f_SID."</a>");

	$arr = explode(" ",$f_DATE_MODIFY);
	$row->AddViewField("DATE_MODIFY", $arr[0]."<br>".$arr[1]);

	if ($isAdmin || $isDemo)
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("SORT");
		$row->AddInputField("NAME");
	}
	else
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddViewField("SORT", $f_SORT);
		$row->AddViewField("NAME", $f_NAME);
	}
	$row->AddViewField("DESCRIPTION", TruncateText($f_DESCRIPTION, 180));
	$row->AddViewField("BANNER_COUNT", '<a href="/bitrix/admin/adv_banner_list.php?find_type_sid[]='.$f_SID.'&set_filter=Y" title="'.GetMessage("ADV_BANNER_LIST").'">'.$f_BANNER_COUNT.'</a>');

	$arActions = Array();
	if ($isAdmin || $isDemo)
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("AD_TYPE_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("adv_type_edit.php?SID=".$f_SID));

	$arActions[] = array("ICON"=>"view", "TEXT"=>GetMessage("AD_TYPE_VIEW"), "ACTION"=>$lAdmin->ActionRedirect("adv_type_edit.php?SID=".$f_SID."&action=view"), "TITLE"=>GetMessage("AD_TYPE_VIEW_SETTINGS"));

	$arActions[] = array("ICON"=>"adv_graph", "TEXT"=>GetMessage("AD_TYPE_STATISTICS_VIEW"), "ACTION"=>$lAdmin->ActionRedirect("adv_banner_graph.php?find_type_sid=".$f_SID."&find_what_show[]=ctr&find_banner_summa=Y&set_filter=Y&lang=".LANGUAGE_ID), "TITLE"=>GetMessage("AD_TYPE_STATISTICS_VIEW_TITLE"));

	if ($isAdmin || $isDemo)
	{
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("AD_DELETE_TYPE"), "ACTION"=>"if(confirm('".GetMessage('AD_DELETE_TYPE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_SID, "delete"));
	}
	$row->AddActions($arActions);
endwhile;
// "подвал" списка
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

// показ формы с кнопками добавления, ...
if ($isAdmin || $isDemo)
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE")
		));

if($isAdmin || $isDemo)
{
	$aContext = array(
		array(
			"TEXT"	=> GetMessage("AD_ADD"),
			"LINK"	=> "adv_type_edit.php?lang=".LANGUAGE_ID,
			"TITLE"	=> GetMessage("AD_ADD_TITLE"),
			"ICON"	=> "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

// проверка на вывод только списка (в случае списка, скрипт дальше выполняться не будет)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("AD_PAGE_TITLE"));
/***************************************************************************
								HTML форма
****************************************************************************/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form method="GET" action="<?=$APPLICATION->GetCurPage()?>" name="form1">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("AD_F_ID"),
		GetMessage("AD_F_DATE_MODIFY"),
		GetMessage("AD_F_ACTIVE"),
		GetMessage("AD_F_NAME"),
		GetMessage("AD_F_DESCRIPTION"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("ADV_FLT_SEARCH")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("ADV_FLT_SEARCH_TITLE")?>">
		<select name="find_type">
			<option value="sid"<?if($find_type=="sid") echo " selected"?>><?=GetMessage('AD_F_ID')?></option>
			<option value="name"<?if($find_type=="name") echo " selected"?>><?=GetMessage('AD_F_NAME')?></option>
			<option value="description"<?if($find_type=="description") echo " selected"?>><?=GetMessage('AD_F_DESCRIPTION')?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_ID")?>:</td>
	<td><input type="text" name="find_sid" size="47" value="<?echo htmlspecialcharsbx($find_sid)?>"><?=InputType("checkbox", "find_sid_exact_match", "Y", $find_sid_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_DATE_MODIFY")." (".CSite::GetDateFormat("SHORT")."):"?></td>
	<td><?echo CalendarPeriod("find_date_modify_1", $find_date_modify_1, "find_date_modify_2", $find_date_modify_2, "form1","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_ACTIVE")?>:</td>
	<td><?
		$arr = array("reference"=>array(GetMessage("AD_YES"), GetMessage("AD_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage('AD_ALL'));
		?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_NAME")?>:</td>
	<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"><?=InputType("checkbox", "find_name_exact_match", "Y", $find_name_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_DESCRIPTION")?>:</td>
	<td><input type="text" name="find_description" size="47" value="<?echo htmlspecialcharsbx($find_description)?>"><?=InputType("checkbox", "find_description_exact_match", "Y", $find_description_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$oFilter->End();
?>
</form>
<?$lAdmin->DisplayList();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>