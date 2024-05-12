<?
##############################################
# Bitrix Site Manager Forum					 #
# Copyright (c) 2002-2009 Bitrix			 #
# https://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$sTableID = "tbl_vote_channel";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_site",
	"find_active",
	"find_title",
	"find_title_exact_match",
	"find_sid",
	"find_sid_exact_match"
	);
/********************************************************************
				Actions 
********************************************************************/
$lAdmin->InitFilter($arFilterFields);

InitBVar($find_id_exact_match);
InitBVar($find_sid_exact_match);
InitBVar($find_title_exact_match);

$aMenu = array();
$arFilter = Array(
	"ID"				=> $find_id,
	"ID_EXACT_MATCH"	=> $find_id_exact_match,
	"SITE"				=> $find_site,
	"ACTIVE"			=> $find_active,
	"SYMBOLIC_NAME"				=> $find_sid,
	"SYMBOLIC_NAME_EXACT_MATCH"	=> $find_sid_exact_match,
	"TITLE"				=> $find_title,
	"TITLE_EXACT_MATCH"	=> $find_title_exact_match
	);

if ($lAdmin->EditAction() && $VOTE_RIGHT>="W" && check_bitrix_sessid())
{
	$bupdate = false;
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = intval($ID);
		$arFieldsStore = Array(
			"TIMESTAMP_X"	=> $DB->GetNowFunction(),
			"ACTIVE"		=> "'".$DB->ForSql($arFields["ACTIVE"])."'",
			"C_SORT"		=> "'".intval($arFields["C_SORT"])."'",
			"TITLE"		=> "'".$DB->ForSql($arFields["TITLE"])."'",
			"SYMBOLIC_NAME"		=> "'".$DB->ForSql($arFields["SYMBOLIC_NAME"])."'",
			);
		if (!$DB->Update("b_vote_channel",$arFieldsStore,"WHERE ID='$ID'",$err_mess.__LINE__))
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".GetMessage("VOTE_SAVE_ERROR"), $ID);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
			$bupdate = true;
		}
	}

	if ($bupdate)
		$CACHE_MANAGER->CleanDir("b_vote_channel");
}


if(($arID = $lAdmin->GroupAction()) && $VOTE_RIGHT=="W" && check_bitrix_sessid())
{
		if($_REQUEST['action_target']=='selected')
		{
				$arID = Array();
				$rsData = CVoteChannel::GetList('', '', $arFilter);
				while($arRes = $rsData->Fetch())
						$arID[] = $arRes['ID'];
		}

		foreach($arID as $ID)
		{
				if($ID == '')
						continue;
				$ID = intval($ID);
				switch($_REQUEST['action'])
				{
				case "delete":
						@set_time_limit(0);
						$DB->StartTransaction();
						if(!CVoteChannel::Delete($ID))
						{
							$DB->Rollback();
							$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
						}
						else
						{
							$DB->Commit();
						}
						break;
				case "activate":
				case "deactivate":
						$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"'Y'":"'N'"));
						if (!$DB->Update("b_vote_channel",$arFields,"WHERE ID='$ID'",$err_mess.__LINE__))
								$lAdmin->AddGroupError(GetMessage("VOTE_SAVE_ERROR"), $ID);
						else
							$CACHE_MANAGER->CleanDir("b_vote_channel");
						break;
				}
		}
}
$rsData = CVoteChannel::GetList('', '', $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("VOTE_PAGES")));


$lAdmin->AddHeaders(array(
		array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
		array("id"=>"TIMESTAMP_X", "content"=>GetMessage("VOTE_TIMESTAMP"), "sort"=>"s_timestamp", "default"=>true),
		array("id"=>"SITE", "content"=>GetMessage("VOTE_SITE"), "default"=>true),
		array("id"=>"ACTIVE", "content"=>GetMessage("VOTE_ACTIVE"), "sort"=>"s_active", "default"=>true),
		array("id"=>"HIDDEN", "content"=>GetMessage("VOTE_HIDDEN"), "sort"=>"s_hidden", "default"=>true),
		array("id"=>"C_SORT", "content"=>GetMessage("VOTE_C_SORT"), "sort"=>"s_c_sort", "default"=>true),
		array("id"=>"SYMBOLIC_NAME", "content"=>GetMessage("VOTE_SID"), "sort"=>"s_symbolic_name", "default"=>true),
		array("id"=>"TITLE", "content"=>GetMessage("VOTE_TITLE"), "sort"=>"s_title", "default"=>true),
		array("id"=>"VOTES", "content"=>GetMessage("VOTE_VOTES"), "sort"=>"s_votes", "default"=>true),
	)
);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$arrSITE =	CVoteChannel::GetSiteArray($f_ID);
	$str = "";
	if(is_array($arrSITE))
	{
		foreach($arrSITE as $sid)
			$str .= '<a title="'.GetMessage("VOTE_SITE_EDIT").'" href="/bitrix/admin/site_edit.php?LID='.$sid.'&lang='.LANGUAGE_ID.'">'.$sid.'</a>, ';
	};

	if ($VOTE_RIGHT=="W")
	{
		$row->AddViewField("SITE", trim($str, " ,"));
		$row->AddCheckField("ACTIVE");
		$row->AddViewField("HIDDEN", ($f_HIDDEN=="Y"? GetMessage("VOTE_YES"):GetMessage("VOTE_NO")));
		$row->AddInputField("C_SORT");
		$row->AddInputField("SYMBOLIC_NAME");
		$row->AddInputField("TITLE");
		$row->AddViewField("TITLE", '<a href="vote_channel_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_ID.'" title="'.GetMessage("VOTE_EDIT_TITLE").'">'.$f_TITLE.'</a>');
	}
	else
	{
		$row->AddViewField("SITE", ($f_SITE=="Y"? GetMessage("VOTE_YES"):GetMessage("VOTE_NO")));
		$row->AddViewField("ACTIVE", ($f_ACTIVE=="Y"? GetMessage("VOTE_YES"):GetMessage("VOTE_NO")));
		$row->AddViewField("HIDDEN", ($f_HIDDEN=="Y"? GetMessage("VOTE_YES"):GetMessage("VOTE_NO")));
	}

	$row->AddViewField("VOTES", '<a title="'.GetMessage("VOTE_OPEN_VOTES").'" href="vote_list.php?lang='.LANGUAGE_ID.'&find_channel='.$f_ID.'&set_filter=Y">'.$f_VOTES.'</a>&nbsp;[<a title="'.GetMessage("VOTE_ADD_VOTE").'" href="vote_edit.php?CHANNEL_ID='.$f_ID.'&lang='.LANGUAGE_ID.'">+</a>]');

	$arActions = Array();
	$arActions[] = array("DEFAULT"=>"Y","ICON"=>"edit", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("vote_channel_edit.php?ID=".$f_ID));
	if($f_ID!='1' && $VOTE_RIGHT=="W")
	{
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"), "ACTION"=>"if(confirm('".GetMessage("VOTE_CONFIRM_DEL_CHANNEL")."')) window.location='vote_channel_list.php?lang=".LANGUAGE_ID."&action=delete&ID=$f_ID&".bitrix_sessid_get()."'");
	}

	if ($VOTE_RIGHT=="W")
		$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($VOTE_RIGHT=="W")
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("VOTE_DELETE"),
		"activate"=>GetMessage("VOTE_ACTIVATE"),
		"deactivate"=>GetMessage("VOTE_DEACTIVATE"),
		));

if ($VOTE_RIGHT=="W")
{
	$aMenu[] = array(
		"TEXT"	=> GetMessage("VOTE_CREATE"),
		"TITLE"=>GetMessage("VOTE_ADD_GROUP_TITLE"),
		"LINK"=>"vote_channel_edit.php?lang=".LANG,
		"ICON" => "btn_new"
	);
	
	$aContext = $aMenu;
	$lAdmin->AddAdminContextMenu($aContext);
}


$lAdmin->CheckListMode();

/********************************************************************
				Form
********************************************************************/
$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<a name="tb"></a>

<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("VOTE_FLT_ID"),
		GetMessage("VOTE_SITE"),
		GetMessage("VOTE_FLT_ACTIVE"),
		GetMessage("VOTE_F_SID")
	)
);

$oFilter->Begin();

?>
<tr>
	<td nowrap><b><?=GetMessage("VOTE_F_TITLE")?></b></td>
	<td nowrap><input type="text" name="find_title" value="<?echo htmlspecialcharsbx($find_title)?>" size="47"><?=InputType("checkbox", "find_title_exact_match", "Y", $find_title_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td>ID:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="top">
	<td><?=GetMessage("VOTE_F_SITE")?><br><img src="/bitrix/images/vote/mouse.gif" width="44" height="21" border=0 alt=""></td>
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
	<td nowrap><?=GetMessage("VOTE_F_ACTIVE")?></td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("VOTE_YES"), GetMessage("VOTE_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("VOTE_ALL"));
		?></td>
</tr>
<tr>
	<td nowrap><?=GetMessage("VOTE_F_SID")?></td>
	<td nowrap><input type="text" name="find_sid" value="<?echo htmlspecialcharsbx($find_sid)?>" size="47"><?=InputType("checkbox", "find_sid_exact_match", "Y", $find_sid_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
