<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
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

$DONT_USE_CONTRACT = COption::GetOptionString("advertising", "DONT_USE_CONTRACT", "N");

IncludeModuleLangFile(__FILE__);

/***************************************************************************
						Обработка GET | POST
****************************************************************************/
$sTableID = "tbl_adv_contract_list";

// инициализация сортировки
$oSort = new CAdminSorting($sTableID, "s_sort", "asc");
// инициализация списка
$lAdmin = new CAdminList($sTableID, $oSort);

// массив доступов по всем контрактам для текущего пользователя
$arrPERM = CAdvContract::GetUserPermissions();

// фильтр
$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_id_exact_match",
	"find_site",
	"find_date_modify_1",
	"find_date_modify_2",
	"find_name",
	"find_name_exact_match",
	"find_description",
	"find_description_exact_match",
	"find_lamp",
	"find_owner",
	"find_owner_exact_match",
	"find_banner_count_1",
	"find_banner_count_2",
	"find_show_count_1",
	"find_show_count_2",
	"find_click_count_1",
	"find_click_count_2",
	"find_visitor_count_1",
	"find_visitor_count_2",
	"find_ctr_1",
	"find_ctr_2",
	"find_admin_comments",
	"find_admin_comments_exact_match"
	);

$lAdmin->InitFilter($FilterArr);

InitBVar($find_id_exact_match);
InitBVar($find_name_exact_match);
InitBVar($find_description_exact_match);
InitBVar($find_owner_exact_match);
InitBVar($find_admin_comments_exact_match);
$arFilter = Array(
	"ID"							=> ($find!='' && $find_type == "id"? $find: $find_id),
	"ID_EXACT_MATCH"				=> $find_id_exact_match,
	"SITE"						=> $find_site,
	"DATE_MODIFY_1"				=> $find_date_modify_1,
	"DATE_MODIFY_2"				=> $find_date_modify_2,
	"NAME"						=> ($find!='' && $find_type == "name"? $find: $find_name),
	"NAME_EXACT_MATCH"				=> $find_name_exact_match,
	"DESCRIPTION"					=> ($find!='' && $find_type == "description"? $find: $find_description),
	"DESCRIPTION_EXACT_MATCH"		=> $find_description_exact_match,
	"LAMP"						=> $find_lamp,
	"OWNER"						=> $find_owner,
	"OWNER_EXACT_MATCH"				=> $find_owner_exact_match,
	"BANNER_COUNT_1"				=> $find_banner_count_1,
	"BANNER_COUNT_2"				=> $find_banner_count_2,
	"SHOW_COUNT_1"					=> $find_show_count_1,
	"SHOW_COUNT_2"					=> $find_show_count_2,
	"CLICK_COUNT_1"				=> $find_click_count_1,
	"CLICK_COUNT_2"				=> $find_click_count_2,
	"VISITOR_COUNT_1"				=> $find_visitor_count_1,
	"VISITOR_COUNT_2"				=> $find_visitor_count_2,
	"CTR_1"						=> $find_ctr_1,
	"CTR_2"						=> $find_ctr_2,
	"ADMIN_COMMENTS"				=> $find_admin_comments,
	"ADMIN_COMMENTS_EXACT_MATCH"		=> $find_admin_comments_exact_match
	);
if($lAdmin->EditAction() && $isAdmin)
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
		{
			continue;
		}

		$DB->StartTransaction();

		if (CAdvContract::Set($arFields, $ID))
		{
			$DB->Commit();
		}
		else
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$ob->LAST_ERROR, $ID);
			$DB->Rollback();
		}
	}
}

// обработка действий групповых и одиночных
if(($arID = $lAdmin->GroupAction()) && $isAdmin)
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CAdvContract::GetList('', '', $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		$ob = new CAdvContract;
		if(intval($ID)<=0)
			continue;
		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			if(!CAdvContract::Delete($ID))
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

$rsContracts = CAdvContract::GetList($by, $order, $arFilter);

$rsData = new CAdminResult($rsContracts, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("AD_PAGES")));
$Headers = Array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true, "align"=>"right"),
	array("id"=>"LAMP", "content"=>GetMessage("AD_LAMP"), "sort"=>"s_lamp", "default"=>true, "align" => "center", "default"=>true),
	array("id"=>"DATE_MODIFY", "content"=>GetMessage("AD_DATE_MODIFY"), "sort"=>"s_date_modify", "default"=>true),
	array("id"=>"SITE", "content"=>GetMessage("AD_SITE"), "sort"=>"", "default"=>true)
);
if($isAdmin || $isDemo)
	$Headers[] = array("id"=>"SORT", "content"=>GetMessage("AD_SORT"), "sort"=>"s_sort", "default"=>true, "align"=>"right");
$Headers[] = array("id"=>"ACTIVE", "content"=>GetMessage("AD_ACTIVE"), "sort"=>"s_active", "default"=>true);
if($isAdmin || $isDemo)
	$Headers[] = array("id"=>"WEIGHT", "content"=>GetMessage("AD_WEIGHT"), "sort"=>"s_weight", "default"=>true, "align"=>"right");
$Headers[] = array("id"=>"NAME", "content"=>GetMessage("AD_NAME"), "sort"=>"s_name", "default"=>true);
$Headers[] = array("id"=>"DESCRIPTION", "content"=>GetMessage("AD_DESCRIPTION"), "sort"=>"s_description");
$Headers[] = array("id"=>"BANNER_COUNT", "content"=>GetMessage("AD_BANNER_COUNT"), "sort"=>"s_banner_count", "default"=>true, "align"=>"right");
$Headers[] = array("id"=>"VISITOR_COUNT", "content"=>GetMessage("AD_VISITOR_COUNT"), "sort"=>"s_visitor_count", "align"=>"right");
$Headers[] = array("id"=>"MAX_VISITOR_COUNT", "content"=>GetMessage("AD_VISITOR_COUNT_MAX"), "sort"=>"s_max_visitor_count", "align"=>"right");
$Headers[] = array("id"=>"SHOW_COUNT", "content"=>GetMessage("AD_SHOW_COUNT"), "sort"=>"s_show_count", "align"=>"right");
$Headers[] = array("id"=>"MAX_SHOW_COUNT", "content"=>GetMessage("AD_SHOW_COUNT_MAX"), "sort"=>"s_max_show_count", "align"=>"right");
$Headers[] = array("id"=>"CLICK_COUNT", "content"=>GetMessage("AD_CLICK_COUNT"), "sort"=>"s_click_count", "align"=>"right");
$Headers[] = array("id"=>"MAX_CLICK_COUNT", "content"=>GetMessage("AD_CLICK_COUNT_MAX"), "sort"=>"s_max_click_count", "align"=>"right");
$Headers[] = array("id"=>"CTR", "content"=>"CTR (%)", "sort"=>"s_ctr", "align"=>"right");
$lAdmin->AddHeaders($Headers);

$arrSites = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
	$arrSites[$ar["ID"]] = $ar;

while($arRes = $rsData->NavNext(true, "f_")):
	$lamp_alt = GetMessage("AD_".mb_strtoupper($f_LAMP)."_ALT");
	$lamp = '<div class="lamp-'.$f_LAMP.'" title="'.$lamp_alt.'"></div>';
	$arrUserPerm = is_array($arrPERM[$f_ID]) ? $arrPERM[$f_ID] : array();

	$row =& $lAdmin->AddRow($f_ID, $arRes, "adv_contract_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID, GetMessage("ADV_EDIT_TITLE"));
	$row->AddViewField("ID", "<a href='adv_contract_edit.php?lang=".LANGUAGE_ID."&ID=".$f_ID."' title='".GetMessage("ADV_EDIT_TITLE")."'>".$f_ID."</a>");
	$row->AddViewField("LAMP", $lamp);

	$arr = explode(" ",$f_DATE_MODIFY);
	$row->AddViewField("DATE_MODIFY", $arr[0]."<br>".$arr[1]);

	$sites = "";
	$arrSITE = CAdvContract::GetSiteArray($f_ID);
	reset($arrSITE);
	if (is_array($arrSITE)):
		foreach($arrSITE as $sid):
			if ($isAdmin)
				$sites .= '<a href="/bitrix/admin/site_edit.php?LID='.htmlspecialcharsbx($sid).'&amp;lang='.LANGUAGE_ID.'" title="'.GetMessage("ADV_SITE_VIEW").'">'.htmlspecialcharsbx($arrSites[$sid]["NAME"]).'</a><br>';
			else
				$sites .= htmlspecialcharsbx($arrSites[$sid]["NAME"])."<br>";
		endforeach;
	endif;
	$row->AddViewField("SITE", $sites);

	if ((is_array($arrUserPerm) && in_array("EDIT", $arrUserPerm)) || $isDemo)
	{
		$row->AddInputField("SORT");
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("WEIGHT");
	}
	else
		$row->AddCheckField("ACTIVE", false);
	if ((is_array($arrUserPerm) && in_array("EDIT", $arrUserPerm)) || $isDemo)
		$row->AddInputField("NAME");
	else
		$row->AddViewField("NAME", $f_NAME);
	$row->AddViewField("DESCRIPTION", TruncateText($f_DESCRIPTION, 100));
	$row->AddViewField("BANNER_COUNT", '<a href="/bitrix/admin/adv_banner_list.php?find_contract_id[]='.$f_ID.'&set_filter=Y" title="'.GetMessage("ADV_BANNER_LIST").'">'.$f_BANNER_COUNT.'</a>');

	$row->AddViewField("VISITOR_COUNT", $f_VISITOR_COUNT);
	if ((is_array($arrUserPerm) && in_array("EDIT", $arrUserPerm)) || $isDemo)
		$row->AddInputField("MAX_VISITOR_COUNT");
	else
		$row->AddViewField("MAX_VISITOR_COUNT", $f_MAX_VISITOR_COUNT);
	$row->AddViewField("SHOW_COUNT", $f_SHOW_COUNT);
	if ((is_array($arrUserPerm) && in_array("EDIT", $arrUserPerm)) || $isDemo)
		$row->AddInputField("MAX_SHOW_COUNT");
	else
		$row->AddViewField("MAX_SHOW_COUNT", $f_MAX_SHOW_COUNT);
	$row->AddViewField("CLICK_COUNT", $f_CLICK_COUNT);
	if ((is_array($arrUserPerm) && in_array("EDIT", $arrUserPerm)) || $isDemo)
		$row->AddInputField("MAX_CLICK_COUNT");
	else
		$row->AddViewField("MAX_CLICK_COUNT", $f_MAX_CLICK_COUNT);
	$row->AddViewField("CTR", $f_CTR);

	$arActions = Array();
	if ((is_array($arrUserPerm) && in_array("EDIT", $arrUserPerm)) || $isDemo)
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("AD_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("adv_contract_edit.php?ID=".$f_ID));
	}

	$arActions[] = array("ICON"=>"view", "TEXT"=>GetMessage("AD_VIEW"), "ACTION"=>$lAdmin->ActionRedirect("adv_contract_edit.php?ID=".$f_ID."&action=view"), "TITLE"=>GetMessage("AD_VIEW_TITILE"));

	$arActions[] = array("ICON"=>"adv_graph", "TEXT"=>GetMessage("AD_STATISTICS"), "ACTION"=>$lAdmin->ActionRedirect("adv_contract_graph.php?find_contract_id[]=".$f_ID."&find_what_show[]=ctr&set_filter=Y&lang=".LANGUAGE_ID), "TITLE" => GetMessage("AD_CONTRACT_STATISTICS_VIEW"));

	if ($f_ID>1 && ($isAdmin || $isDemo))
	{
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("AD_DELETE"), "ACTION"=>"if(confirm('".GetMessage('AD_DELETE_CONTRACT_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
if((is_array($arrUserPerm) && in_array("EDIT", $arrUserPerm)) || $isDemo)
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
			"LINK"	=> "adv_contract_edit.php?lang=".LANGUAGE_ID,
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
if ($DONT_USE_CONTRACT == "Y")
	CAdminMessage::ShowNote(GetMessage("AD_CONTRACT_DISABLE"));
?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("AD_F_ID"),
		GetMessage("AD_F_DATE_MODIFY"),
		GetMessage("AD_F_LAMP"),
		GetMessage("AD_F_SITE"),
		GetMessage("AD_F_BANNER_COUNT"),
		GetMessage("AD_F_VISITORS"),
		GetMessage("AD_F_SHOWN"),
		GetMessage("AD_F_CLICKED"),
		GetMessage("AD_F_CTR"),
		GetMessage("AD_F_OWNER"),
		GetMessage("AD_F_NAME"),
		GetMessage("AD_F_DESCRIPTION"),
		GetMessage("AD_F_ADMIN_COMMENTS"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("ADV_FLT_SEARCH")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("ADV_FLT_SEARCH_TITLE")?>">
		<select name="find_type">
			<option value="id"<?if($find_type=="id") echo " selected"?>><?=GetMessage('AD_F_ID')?></option>
			<option value="name"<?if($find_type=="name") echo " selected"?>><?=GetMessage('AD_F_NAME')?></option>
			<option value="description"<?if($find_type=="description") echo " selected"?>><?=GetMessage('AD_F_DESCRIPTION')?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_DATE_MODIFY")." (".CSite::GetDateFormat("SHORT")."):"?></td>
	<td><?echo CalendarPeriod("find_date_modify_1", htmlspecialcharsbx($find_date_modify_1), "find_date_modify_2", htmlspecialcharsbx($find_date_modify_2), "form1","Y")?></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_LAMP")?>:</td>
	<td><?
			$arr = array(
				"reference" => array(
					GetMessage("AD_GREEN"),
					GetMessage("AD_RED")
					),
				"reference_id" => array(
					"green",
					"red"
					)
				);
		echo SelectBoxFromArray("find_lamp", $arr, htmlspecialcharsbx($find_lamp), GetMessage("AD_ALL"));
		?></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_SITE")?>:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td><?
	$ref = array();
	$ref_id = array();
	$rs = CSite::GetList();
	while ($ar = $rs->Fetch())
	{
		$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
		$ref_id[] = $ar["ID"];
	}
	echo SelectBoxMFromArray("find_site[]", array("reference" => $ref, "reference_id" => $ref_id), $find_site, "",false,"3");
	?></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_BANNER_COUNT")?>:</td>
	<td><input type="text" name="find_banner_count_1" size="10" value="<?=htmlspecialcharsbx($find_banner_count_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_banner_count_2" size="10" value="<?=htmlspecialcharsbx($find_banner_count_2)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_VISITORS")?>:</td>
	<td><input type="text" name="find_visitor_count_1" size="10" value="<?=htmlspecialcharsbx($find_visitor_count_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_visitor_count_2" size="10" value="<?=htmlspecialcharsbx($find_visitor_count_2)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_SHOWN")?>:</td>
	<td><input type="text" name="find_show_count_1" size="10" value="<?=htmlspecialcharsbx($find_show_count_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_show_count_2" size="10" value="<?=htmlspecialcharsbx($find_show_count_2)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_CLICKED")?>:</td>
	<td><input type="text" name="find_click_count_1" size="10" value="<?=htmlspecialcharsbx($find_click_count_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_click_count_2" size="10" value="<?=htmlspecialcharsbx($find_click_count_2)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_CTR")?>:</td>
	<td><input type="text" name="find_ctr_1" size="10" value="<?=htmlspecialcharsbx($find_ctr_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_ctr_2" size="10" value="<?=htmlspecialcharsbx($find_ctr_2)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_OWNER")?>:</td>
	<td><input type="text" name="find_owner" size="47" value="<?echo htmlspecialcharsbx($find_owner)?>"><?=InputType("checkbox", "find_owner_exact_match", "Y", $find_owner_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_NAME")?>:</td>
	<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"><?=InputType("checkbox", "find_name_exact_match", "Y", $find_name_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_DESCRIPTION")?>:</td>
	<td><input type="text" name="find_description" size="47" value="<?echo htmlspecialcharsbx($find_description)?>"><?=InputType("checkbox", "find_description_exact_match", "Y", $find_description_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?if ($isAdmin || $isDemo):?>
<tr>
	<td><?echo GetMessage("AD_F_ADMIN_COMMENTS")?>:</td>
	<td><input type="text" name="find_admin_comments" size="47" value="<?echo htmlspecialcharsbx($find_admin_comments)?>"><?=InputType("checkbox", "find_admin_comments_exact_match", "Y", $find_owner_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?endif;?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$oFilter->End();
?>
</form>
<?$lAdmin->DisplayList();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
