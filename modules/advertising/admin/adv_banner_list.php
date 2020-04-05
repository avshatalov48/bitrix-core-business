<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage advertising
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global $by
 * @global $order
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/include.php");

$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();
$isAdmin = CAdvContract::IsAdmin();

if(!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_adv_banner_list";
$oSort = new CAdminSorting($sTableID, "s_id", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arrPERM = CAdvContract::GetUserPermissions();

$FilterArr = array(
	"find",
	"find_type_f",
	"find_id",
	"find_id_exact_match",
	"find_lamp",
	"find_site",
	"find_visitor_count_1",
	"find_visitor_count_2",
	"find_show_count_1",
	"find_show_count_2",
	"find_click_count_1",
	"find_click_count_2",
	"find_ctr_1",
	"find_ctr_2",
	"find_contract_id",
	"find_contract",
	"find_contract_exact_match",
	"find_group",
	"find_group_exact_match",
	"find_status_sid",
	"find_type_sid",
	"find_type",
	"find_type_exact_match",
	"find_name",
	"find_name_exact_match",
	"find_code",
	"find_code_exact_match",
	"find_comments",
	"find_comments_exact_match"
);
$lAdmin->InitFilter($FilterArr);

InitBVar($find_id_exact_match);
InitBVar($find_status_exact_match);
InitBVar($find_group_exact_match);
InitBVar($find_contract_exact_match);
InitBVar($find_type_exact_match);
InitBVar($find_name_exact_match);
InitBVar($find_code_exact_match);
InitBVar($find_comments_exact_match);
$arFilter = array(
	"ID" => ($find!='' && $find_type_f == "id"? $find: $find_id),
	"ID_EXACT_MATCH" => $find_id_exact_match,
	"LAMP" => $find_lamp,
	"SITE" => $find_site,
	"VISITOR_COUNT_1" => $find_visitor_count_1,
	"VISITOR_COUNT_2" => $find_visitor_count_2,
	"SHOW_COUNT_1" => $find_show_count_1,
	"SHOW_COUNT_2" => $find_show_count_2,
	"CLICK_COUNT_1" => $find_click_count_1,
	"CLICK_COUNT_2" => $find_click_count_2,
	"CTR_1" => $find_ctr_1,
	"CTR_2" => $find_ctr_2,
	"GROUP" => $find_group,
	"GROUP_EXACT_MATCH" => $find_group_exact_match,
	"STATUS_SID" => $find_status_sid,
	"CONTRACT_ID" => $find_contract_id,
	"CONTRACT" => $find_contract,
	"CONTRACT_EXACT_MATCH" => $find_contract_exact_match,
	"TYPE_SID" => $find_type_sid,
	"TYPE" => $find_type,
	"TYPE_EXACT_MATCH" => $find_type_exact_match,
	"NAME" => ($find!='' && $find_type_f == "name"? $find: $find_name),
	"NAME_EXACT_MATCH" => $find_name_exact_match,
	"CODE" => ($find!='' && $find_type_f == "code"? $find: $find_code),
	"CODE_EXACT_MATCH" => $find_code_exact_match,
	"COMMENTS" => $find_comments,
	"COMMENTS_EXACT_MATCH" => $find_comments_exact_match
);

if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$ID = intval($ID);
		$ifrsBanner = CAdvBanner::GetByID($ID);
		if($ifarBanner = $ifrsBanner->Fetch())
			$ifCONTRACT_ID = $ifarBanner["CONTRACT_ID"];
		if(is_array($arrPERM[$ifCONTRACT_ID]) && in_array("ADD", $arrPERM[$ifCONTRACT_ID]))
		{
			$DB->StartTransaction();

			if(!$lAdmin->IsUpdated($ID))
				continue;

			if(!CAdvBanner::Set($arFields, $ID))
			{
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$ob->LAST_ERROR, $ID);
				$DB->Rollback();
			}
			$DB->Commit();
		}
		else
			$lAdmin->AddUpdateError(GetMessage("ADV_NO_RIGHTS_EDIT"), $ID);
	}
}

// обработка действий групповых и одиночных
if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$rsData = CAdvBanner::GetList($by, $order, $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(intval($ID)<=0)
			continue;
		$ID = intval($ID);

		$ifrsBanner = CAdvBanner::GetByID($ID);
		if($ifarBanner = $ifrsBanner->Fetch())
			$ifCONTRACT_ID = $ifarBanner["CONTRACT_ID"];
		if(is_array($arrPERM[$ifCONTRACT_ID]) && in_array("ADD", $arrPERM[$ifCONTRACT_ID]))
		{
			switch($_REQUEST['action'])
			{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if(!CAdvBanner::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
				}
				$DB->Commit();
				break;
			case "activate":
			case "deactivate":
				$cData = new CAdvBanner;
				$arFields = array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
				if(!$cData->Set($arFields, $ID))
					$lAdmin->AddGroupError(GetMessage("MAIN_EDIT_ERROR").$cData->LAST_ERROR, $ID);
				break;
			case "copy":
				$cData = new CAdvBanner;
				if(!$cData->Copy($ID))
					$lAdmin->AddGroupError(GetMessage("MAIN_EDIT_ERROR").$cData->LAST_ERROR, $ID);
				break;
			}
		}
		else
			$lAdmin->AddUpdateError(GetMessage("ADV_NO_RIGHTS_EDIT"), $ID);

	}
}

$rsBanners = CAdvBanner::GetList($by, $order, $arFilter, $is_filtered);

$rsData = new CAdminResult($rsBanners, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("AD_PAGES")));
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true, "align"=>"right"),
	array("id"=>"LAMP", "content"=>GetMessage("AD_LAMP"), "sort"=>"s_lamp", "default"=>true, "align" => "center"),
	array("id"=>"NAME", "content"=>GetMessage("AD_NAME"), "sort"=>"s_name", "default"=>true),
	array("id"=>"TYPE_SID", "content"=>GetMessage("AD_TYPE"), "sort"=>"s_type_sid", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("AD_ACTIVE"), "sort"=>"s_active", "default"=>true),
	array("id"=>"WEIGHT", "content"=>GetMessage("AD_WEIGHT"), "sort"=>"s_weight", "default"=>true, "align"=>"right"),
	array("id"=>"GROUP_SID", "content"=>GetMessage("AD_GROUP"), "sort"=>"s_group_sid"),
	array("id"=>"CONTRACT_ID", "content"=>GetMessage("AD_CONTRACT"), "sort"=>"s_contract_id"),
	array("id"=>"SITE", "content"=>GetMessage("AD_SITE"), "default"=>true),
	array("id"=>"STATUS_SID", "content"=>GetMessage("AD_STATUS"), "sort"=>"s_status_sid", "default"=>true),
	array("id"=>"DATE_CREATE", "content"=>GetMessage("ad_list_created"), "title"=>GetMessage("ad_list_created_title"), "sort"=>"s_date_create"),
	array("id"=>"DATE_MODIFY", "content"=>GetMessage("ad_list_modified"), "title"=>GetMessage("ad_list_modified_title"), "sort"=>"s_date_modify"),
	array("id"=>"VISITOR_COUNT", "content"=>GetMessage("AD_VISITOR_COUNT"), "sort"=>"s_visitor_count", "align"=>"right"),
	array("id"=>"MAX_VISITOR_COUNT", "content"=>GetMessage("AD_VISITOR_COUNT_MAX"), "sort"=>"s_max_visitor_count", "align"=>"right"),
	array("id"=>"SHOW_COUNT", "content"=>GetMessage("AD_SHOW_COUNT"), "sort"=>"s_show_count", "default"=>true, "align"=>"right"),
	array("id"=>"MAX_SHOW_COUNT", "content"=>GetMessage("AD_SHOW_COUNT_MAX"), "sort"=>"s_max_show_count", "align"=>"right"),
	array("id"=>"SHOW_COUNT_LAST_SHOW", "content"=>GetMessage("AD_DATE_LAST_SHOW"), "sort"=>"s_date_last_show"),
	array("id"=>"CLICK_COUNT", "content"=>GetMessage("AD_CLICK_COUNT"), "sort"=>"s_click_count", "align"=>"right"),
	array("id"=>"MAX_CLICK_COUNT", "content"=>GetMessage("AD_CLICK_COUNT_MAX"), "sort"=>"s_max_click_count", "align"=>"right"),
	array("id"=>"CLICK_COUNT_LAST_CLICK", "content"=>GetMessage("AD_DATE_LAST_CLICK"), "sort"=>"s_date_last_click"),
	array("id"=>"CTR", "content"=>"CTR (%)", "sort"=>"s_ctr", "align"=>"right"),
	array("id"=>"FIRST_SHOW", "content"=>GetMessage("AD_FIRST_SHOW_DATE"), "sort"=>"s_firstd_c"),
	array("id"=>"UNIFORM_COEF", "content"=>GetMessage("AD_UNIFORM_COEF_VIEW"), "sort"=>"s_uniform_c", "align"=>"right"),
	array("id"=>"COMMENTS", "content"=>GetMessage("AD_COMMENTS")),
));

$arrUserPerm = array();
$canAdd = false;// хоть один баннер может быть отредатирован, добавлен илу удален
$canAddbanner = false;// баннер может быть удален, отредактирован, добавлен
$arrContractSite = array();

$type_id = array();
$rsTypies = CAdvType::GetList($v1, $v2, array(), $v3);
while ($arType = $rsTypies->Fetch())
{
	$type_id[$arType["SID"]] = htmlspecialcharsbx($arType["NAME"]);
}

$contract_id = array();
$rsContract = CAdvContract::GetList($v1, $v2, array(), $v3);
while ($arContract = $rsContract->Fetch())
{
	$contract_id[$arContract["ID"]] = $arContract["NAME"];
	$arrContractSite[$arContract["ID"]] =  CAdvContract::GetSiteArray($arContract["ID"]);
}

$arrStatus = CAdvBanner::GetStatusList();

$arrSites = array();
$rs = CSite::GetList($b="sort", $o="asc");
while ($ar = $rs->Fetch())
	$arrSites[$ar["ID"]] = $ar;

while($arRes = $rsData->NavNext(true, "f_"))
{
	$lamp_alt = GetMessage("AD_".strtoupper($f_LAMP)."_ALT");
	$lamp = '<div class="lamp-'.$f_LAMP.'" title="'.$lamp_alt.'"></div>';
	$arrUserPerm = is_array($arrPERM[$f_CONTRACT_ID]) ? $arrPERM[$f_CONTRACT_ID] : array();
	$canAddbanner = in_array("ADD", $arrUserPerm) ? true : false;

	$row =& $lAdmin->AddRow($f_ID, $arRes, "adv_banner_edit.php?ID=".$f_ID."&CONTRACT_ID=".$f_CONTRACT_ID."&lang=".LANGUAGE_ID, GetMessage("ADV_EDIT_TITLE"));
	$row->AddViewField("ID", '<a href="adv_banner_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$f_ID.'&amp;CONTRACT_ID='.$f_CONTRACT_ID.'" title="'.GetMessage("ADV_EDIT_TITLE").'">'.$f_ID.'</a>');
	$row->AddViewField("LAMP", $lamp);

	$sites = "";
	$arrSITE = CAdvBanner::GetSiteArray($f_ID);
	if (is_array($arrSITE))
	{
		foreach($arrSITE as $sid)
		{
			if (in_array($sid, $arrContractSite[$f_CONTRACT_ID]))
			{
				if ($isAdmin)
					$sites .= htmlspecialcharsbx($arrSites[$sid]["NAME"]).' [<a href="/bitrix/admin/site_edit.php?LID='.htmlspecialcharsbx($sid).'&amp;lang='.LANGUAGE_ID.'" title="'.GetMessage("ADV_SITE_VIEW").'">'.htmlspecialcharsbx($sid).'</a>]<br>';
				else
					$sites .= htmlspecialcharsbx($arrSites[$sid]["NAME"])." [".htmlspecialcharsbx($sid)."]<br>";
			}
		}
	}
	$row->AddViewField("SITE", $sites);

	if($canAddbanner)
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("WEIGHT");
		if($f_NAME <> '')
			$row->AddViewField("NAME", '<a href="adv_banner_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$f_ID.'&amp;CONTRACT_ID='.$f_CONTRACT_ID.'" title="'.GetMessage("ADV_EDIT_TITLE").'">'.$f_NAME.'</a>');
		$row->AddInputField("NAME");
		$row->AddInputField("GROUP_SID");
	}
	else
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddViewField("WEIGHT", $f_WEIGHT);
		if($f_NAME <> '')
			$row->AddViewField("NAME", '<a href="adv_banner_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$f_ID.'&amp;CONTRACT_ID='.$f_CONTRACT_ID.'" title="'.GetMessage("ADV_EDIT_TITLE").'">'.$f_NAME.'</a>');
		$row->AddViewField("GROUP_SID", $f_GROUP_SID);
	}

	if($canAddbanner)
	{
		$row->AddSelectField("TYPE_SID", $type_id);
		$row->AddSelectField("CONTRACT_ID", $contract_id);
	}
	else
	{
		$row->AddViewField("TYPE_SID", "[<a href='adv_type_edit.php?SID=".$f_TYPE_SID."&lang=".LANGUAGE_ID."&action=view' title='".GetMessage("ADV_TYPE_VIEW")."'>".$f_TYPE_SID."</a>] ".$f_TYPE_NAME);
		$row->AddViewField("CONTRACT_ID", "[<a href='adv_contract_edit.php?ID=".$f_CONTRACT_ID."&lang=".LANGUAGE_ID."&action=view' title='".GetMessage("ADV_CONTRACT_VIEW")."'>".$f_CONTRACT_ID."</a>] ".$f_CONTRACT_NAME);
	}

	foreach($arrStatus["reference_id"] as $key => $val)
		$arStatus[$val] = $arrStatus["reference"][$key];
	if($canAddbanner)
		$row->AddSelectField("STATUS_SID", $arStatus);
	else
		$row->AddViewField("STATUS_SID", $arStatus[$f_STATUS_SID]);

	$row->AddViewField("COMMENTS", $f_COMMENTS);

	$row->AddViewField("VISITOR_COUNT", $f_VISITOR_COUNT);
	if($canAddbanner)
		$row->AddInputField("MAX_VISITOR_COUNT");
	else
		$row->AddViewField("MAX_VISITOR_COUNT", $f_MAX_VISITOR_COUNT);
	$row->AddViewField("SHOW_COUNT", $f_SHOW_COUNT);
	if($canAddbanner)
		$row->AddInputField("MAX_SHOW_COUNT");
	else
		$row->AddViewField("MAX_SHOW_COUNT", $f_MAX_SHOW_COUNT);
	$f_DATE_LAST_SHOW = explode(" ",$f_DATE_LAST_SHOW);
	$row->AddViewField("SHOW_COUNT_LAST_SHOW", $f_DATE_LAST_SHOW[0]."<br>".$f_DATE_LAST_SHOW[1]);
	$row->AddViewField("CLICK_COUNT", $f_CLICK_COUNT);
	if($canAddbanner)
		$row->AddInputField("MAX_CLICK_COUNT");
	else
		$row->AddViewField("MAX_CLICK_COUNT", $f_MAX_CLICK_COUNT);

	$f_DATE_LAST_CLICK = explode(" ",$f_DATE_LAST_CLICK);
	$row->AddViewField("CLICK_COUNT_LAST_CLICK", $f_DATE_LAST_CLICK[0]."<br>".$f_DATE_LAST_CLICK[1]);
	$row->AddViewField("CTR", $f_CTR);

	// Calculate UNIFORM FIELD
	$f_UNIFORM_COEF = ($arRes["FLYUNIFORM"]=="N")?GetMessage("AD_NO"):GetMessage("AD_YES");
	if (isset($arRes["FLYUNIFORM"]) && $arRes["FLYUNIFORM"]=="Y")
	{
		$rot = CAdvBanner_all::CalculateRotationProgress($arRes);
		$tim = CAdvBanner_all::CalculateTimeProgress($arRes);
		if ($rot && $tim)
		{
			$arProgress = $rot/$tim;
			$f_UNIFORM_COEF = round($rot*100)."%&nbsp;/&nbsp;".round($tim*100)."%&nbsp;=&nbsp;".round($arProgress,3);
		}
	}
	$row->AddViewField("UNIFORM_COEF", $f_UNIFORM_COEF);
	///

	// FIRST_SHOW
	$f_FIRST_SHOW = GetMessage("AD_NOFIRST_SHOW_DATE");
	if (isset($arRes["DATE_SHOW_FIRST"])) $f_FIRST_SHOW = $arRes["DATE_SHOW_FIRST"];
	$row->AddViewField("FIRST_SHOW", $f_FIRST_SHOW);
	///

	$arActions = array();
	if ($isDemo || $canAddbanner)
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("AD_BANNER_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("adv_banner_edit.php?ID=".$f_ID."&CONTRACT_ID=".$f_CONTRACT_ID), "DEFAULT"=>true);
	}

	$arActions[] = array("ICON"=>"view", "TEXT"=>GetMessage("AD_BANNER_VIEW_SETTINGS"), "TITLE"=>GetMessage("AD_BANNER_VIEW_SETTINGS_TITLE"), "ACTION"=>$lAdmin->ActionRedirect("adv_banner_edit.php?ID=".$f_ID."&CONTRACT_ID=".$f_CONTRACT_ID."&action=view"));

	$arActions[] = array("ICON"=>"adv_graph", "TEXT"=>GetMessage("AD_BANNER_STATISTICS_VIEW"), "TITLE"=>GetMessage("AD_BANNER_STATISTICS_VIEW_TITLE"), "ACTION"=>$lAdmin->ActionRedirect("adv_banner_graph.php?find_banner_id[]=".$f_ID."&find_what_show[]=ctr&set_filter=Y"));

	if ($isDemo || $canAddbanner)
	{
		$arActions[] = array("ICON"=>"copy", "TEXT"=>GetMessage("AD_BANNER_COPY"), "TITLE"=>GetMessage("AD_BANNER_COPY_TITLE"), "ACTION"=>$lAdmin->ActionDoGroup($f_ID, "copy"));
		$arActions[] = array("SEPARATOR"=>true);

		if ($f_ACTIVE == 'Y')
			$arActions[] = array("ICON"=>"deactivate", "TEXT"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE_UC"), "ACTION"=>$lAdmin->ActionDoGroup($f_ID, "deactivate"));
		else
			$arActions[] = array("ICON"=>"activate", "TEXT"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE_UC"), "ACTION"=>$lAdmin->ActionDoGroup($f_ID, "activate"));

		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("AD_DELETE_BANNER"), "ACTION"=>"if(confirm('".GetMessage('AD_DELETE_BANNER_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}
	$row->AddActions($arActions);
	if($canAddbanner)
		$canAdd = true;
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if($isAdmin || $isDemo || $canAdd)
	$lAdmin->AddGroupActionTable(array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE")
	));

$aContext = array(
	array(
		"TEXT"	=> GetMessage("AD_ADD_BANNER"),
		"LINK"	=> "adv_banner_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("AD_ADD_BANNER_TITLE"),
		"ICON"	=> "btn_new",
		"MENU" 	=> array()
	),
);

$AllowedAddBanner = false;
$rsContract = CAdvContract::GetList($v1="s_sort", $v2="desc", array(), $v3);
while ($arContract = $rsContract->Fetch())
{
	if (is_array($arrPERM[$arContract["ID"]]) && in_array("ADD", $arrPERM[$arContract["ID"]]))
		$AllowedAddBanner = true;

	$aContext[0]["MENU"][] = array(
		"TEXT" 		=> "[".$arContract["ID"]."] ".htmlspecialcharsbx($arContract["NAME"]),
		"ACTION" 	=> $lAdmin->ActionRedirect("adv_banner_edit.php?CONTRACT_ID=".$arContract["ID"]),
		//"TITLE"		=> GetMessage("AD_ADD_BANNER_TITLE")." ".$arContract["NAME"],
	);
}

if ($isAdmin || $isDemo || $isManager || $AllowedAddBanner)
	$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("AD_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" action="<?=$APPLICATION->GetCurPage()?>">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("AD_F_ID"),
		GetMessage("AD_F_LAMP"),
		GetMessage("AD_F_STATUS"),
		GetMessage("AD_F_SITE"),
		GetMessage("AD_F_VISITOR_COUNT"),
		GetMessage("AD_F_SHOW_COUNT"),
		GetMessage("AD_F_CLICK_COUNT"),
		GetMessage("AD_F_CTR"),
		GetMessage("AD_F_GROUP"),
		GetMessage("AD_F_CONTRACT"),
		GetMessage("AD_F_CONTRACT_LIST"),
		GetMessage("AD_F_TYPE"),
		GetMessage("AD_F_TYPE_LIST"),
		GetMessage("AD_F_NAME"),
		GetMessage("AD_F_CODE"),
		GetMessage("AD_F_COMMENTS")
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?=GetMessage("ADV_FLT_SEARCH")?></b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("ADV_FLT_SEARCH_TITLE")?>">
		<select name="find_type_f">
			<option value="id"<?if($find_type_f=="id") echo " selected"?>><?=GetMessage('AD_F_ID')?></option>
			<option value="name"<?if($find_type_f=="name") echo " selected"?>><?=GetMessage('AD_F_NAME')?></option>
			<option value="code"<?if($find_type_f=="code") echo " selected"?>><?=GetMessage('AD_F_CODE')?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_LAMP")?>:</td>
	<td><?
			$arr = array(
				"reference" => array(
					GetMessage("AD_RED"),
					GetMessage("AD_GREEN")),
				"reference_id" => array(
					"red",
					"green")
				);
		echo SelectBoxFromArray("find_lamp", $arr, htmlspecialcharsbx($find_lamp), GetMessage("AD_ALL"));
		?></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_STATUS")?>:</td>
	<td><?
		$arrStatus = CAdvBanner::GetStatusList();
		echo SelectBoxMFromArray("find_status_sid[]",$arrStatus, $find_status_sid, "",false,"3");
	?></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_SITE")?>:</td>
	<td><?
	$ref = array();
	$ref_id = array();
	$rs = CSite::GetList($v1="sort", $v2="asc");
	while ($ar = $rs->Fetch())
	{
		$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
		$ref_id[] = $ar["ID"];
	}
	echo SelectBoxMFromArray("find_site[]", array("reference" => $ref, "reference_id" => $ref_id), $find_site, "",false,"3");
	?></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_VISITOR_COUNT")?>:</td>
	<td><input type="text" name="find_visitor_count_1" size="10" value="<?=htmlspecialcharsbx($find_visitor_count_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_visitor_count_2" size="10" value="<?=htmlspecialcharsbx($find_visitor_count_2)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_SHOW_COUNT")?>:</td>
	<td><input type="text" name="find_show_count_1" size="10" value="<?=htmlspecialcharsbx($find_show_count_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_show_count_2" size="10" value="<?=htmlspecialcharsbx($find_show_count_2)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_CLICK_COUNT")?>:</td>
	<td><input type="text" name="find_click_count_1" size="10" value="<?=htmlspecialcharsbx($find_click_count_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_click_count_2" size="10" value="<?=htmlspecialcharsbx($find_click_count_2)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("AD_F_CTR")?>:</td>
	<td><input type="text" name="find_ctr_1" size="10" value="<?=htmlspecialcharsbx($find_ctr_1)?>"><?echo "&nbsp;".GetMessage("AD_TILL")."&nbsp;"?><input type="text" name="find_ctr_2" size="10" value="<?=htmlspecialcharsbx($find_ctr_2)?>"></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_GROUP")?>:</td>
	<td><input type="text" name="find_group" size="47" value="<?echo htmlspecialcharsbx($find_group)?>"><?=InputType("checkbox", "find_group_exact_match", "Y", $find_group_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_CONTRACT")?>:</td>
	<td><input type="text" name="find_contract" size="47" value="<?echo htmlspecialcharsbx($find_contract)?>"><?=InputType("checkbox", "find_contract_exact_match", "Y", $find_contract_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_CONTRACT_LIST")?>:</td>
	<td><?
		$contract_ref_id = array();
		$contract_ref = array();
		$rsContract = CAdvContract::GetList($v1="s_sort", $v2="desc", array(), $v3);
		while ($arContract = $rsContract->Fetch())
		{
			$contract_ref_id[] = $arContract["ID"];
			$contract_ref[] = "[".$arContract["ID"]."] ".$arContract["NAME"];
		}
		$contract_arr = array("REFERENCE" => $contract_ref, "REFERENCE_ID" => $contract_ref_id);
		echo SelectBoxMFromArray("find_contract_id[]",$contract_arr, $find_contract_id, "",false,"5");
	?></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_TYPE")?>:</td>
	<td><input type="text" name="find_type" size="47" value="<?echo htmlspecialcharsbx($find_type)?>"><?=InputType("checkbox", "find_type_exact_match", "Y", $find_type_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td valign="top"><?=GetMessage("AD_F_TYPE_LIST")?>:</td>
	<td>
		<?
		$ref_id = array();
		$ref = array();
		$rsType = CAdvType::GetList($v1="s_sort", $v2="asc", array(), $v3);
		while ($arType = $rsType->Fetch())
		{
			$ref_id[] = $arType["SID"];
			$ref[] = "[".$arType["SID"]."] ".$arType["NAME"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxMFromArray("find_type_sid[]",$arr, $find_type_sid, "",false,"5");
	?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_NAME")?>:</td>
	<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"><?=InputType("checkbox", "find_name_exact_match", "Y", $find_name_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?echo GetMessage("AD_F_CODE")?>:</td>
	<td><input type="text" name="find_code" size="47" value="<?echo htmlspecialcharsbx($find_code)?>"><?=InputType("checkbox", "find_code_exact_match", "Y", $find_code_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("AD_F_COMMENTS")?>:</td>
	<td><input type="text" name="find_comments" size="47" value="<?echo htmlspecialcharsbx($find_comments)?>"><?=InputType("checkbox", "find_comments_exact_match", "Y", $find_comments_exact_match, false, "", "title='".GetMessage("AD_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$oFilter->End();
?>

</form>
<?$lAdmin->DisplayList();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
