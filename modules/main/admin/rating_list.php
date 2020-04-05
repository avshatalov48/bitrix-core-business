<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2010 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");

if(!$USER->CanDoOperation('edit_ratings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_ratings');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_rating";
$oSort = new CAdminSorting($sTableID, "id", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	return count($lAdmin->arFilterErrors)==0;
}

$FilterArr = Array(
	"find_name",
	"find_active",
	"find_id",
	"find_entity_id",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array();
if(CheckFilter())
{
	$arFilter = Array(
		"NAME"		=> $find_name,
		"ACTIVE"	=> $find_active,
		"ID"		=> $find_id,
		"ENTITY_ID"	=> $find_entity_id,
	);
}

if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$ID = IntVal($ID);
		if($ID <= 0)
			continue;
		$arUpdate['NAME'] = $arFields['NAME'];
		$arUpdate['ACTIVE'] = $arFields['ACTIVE'] == 'Y' ? 'Y' : 'N';
		if(!CRatings::Update($ID, $arUpdate))
		{
			$e = $APPLICATION->GetException();
			$lAdmin->AddUpdateError(($e? $e->GetString():GetMessage("RATING_LIST_ERR_EDIT")), $ID);
		}
	}
}

if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CRatings::GetList(array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		$ID = IntVal($ID);
		if($ID <= 0)
			continue;
		switch($_REQUEST['action'])
		{
			case "recalculate":
				if(!CRatings::Calculate($ID, true))
					$lAdmin->AddGroupError(GetMessage("RATING_LIST_ERR_CAL"), $ID);
			break;
			case "delete":
				if(!CRatings::Delete($ID))
					$lAdmin->AddGroupError(GetMessage("RATING_LIST_ERR_DEL"), $ID);
			break;
		}
	}
}

$rsData = CRatings::GetList(array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("RATING_LIST_NAV")));

$aHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("RATING_NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("RATING_ACTIVE"), "sort"=>"active", "default"=>true),	
	array("id"=>"CREATED", "content"=>GetMessage("RATING_CREATED"), "sort"=>"created", "default"=>false),
	array("id"=>"LAST_MODIFIED", "content"=>GetMessage("RATING_LAST_MODIFIED"), "sort"=>"last_modified", "default"=>true),
	array("id"=>"LAST_CALCULATED", "content"=>GetMessage("RATING_LAST_CALCULATED"), "sort"=>"last_calculated", "default"=>true),
	array("id"=>"CALCULATED", "content"=>GetMessage("RATING_STATUS"), "sort"=>"status", "default"=>true),
	array("id"=>"ENTITY_ID", "content"=>GetMessage("RATING_ENTITY_ID"), "sort"=>"entity_id", "default"=>false),
);

$lAdmin->AddHeaders($aHeaders);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", $f_NAME);
	$row->AddViewField("ACTIVE", $f_ACTIVE == "Y" ? GetMessage("RATING_ACTIVE_YES") : GetMessage("RATING_ACTIVE_NO"));
	$row->AddViewField("LAST_CALCULATED", empty($f_LAST_CALCULATED) ? GetMessage("RATING_STATUS_WAITING") : $f_LAST_CALCULATED);
	$row->AddViewField("CALCULATED", $f_CALCULATED != 'N' ? ($f_CALCULATED == 'C' ? GetMessage("RATING_STATUS_WORKING") : GetMessage("RATING_STATUS_DONE")) : GetMessage("RATING_STATUS_WAITING"));

	$arActions = Array(
		array(
			"ICON"=>"edit",
			"DEFAULT"=>true,
			"TEXT"=>GetMessage("RATING_LIST_EDIT"),
			"ACTION"=>$lAdmin->ActionRedirect("rating_edit.php?ID=".$f_ID)
		),
		array(
			"ICON"=>"edit",
			"TEXT"=>GetMessage("RATING_LIST_RECALCULATE"),
			"ACTION"=>$lAdmin->ActionDoGroup($f_ID, "recalculate")
		),
		array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("RATING_LIST_DEL"),
			"ACTION"=>"if(confirm('".GetMessage("RATING_LIST_DEL_CONF")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		),
	);
	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
));

$aContext = array(
	array(
		"TEXT"=>GetMessage("RATING_LIST_ADD"),
		"LINK"=>"rating_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("RATING_LIST_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("MAIN_RATING_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("RATING_LIST_FLT_ACTIVE"),
		GetMessage("RATING_LIST_FLT_ID"),
		GetMessage("RATING_LIST_FLT_ENTITY_ID"),
	)
);
?>
	<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?$oFilter->Begin();?>
	<tr>
		<td><?echo GetMessage("RATING_LIST_FLT_NAME")?></td>
		<td><input type="text" name="find_name" size="40" value="<?echo htmlspecialcharsbx($find_name)?>"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("RATING_LIST_FLT_ACTIVE")?></td>
		<td><select name="find_active">
			<option value=""><?echo GetMessage("RATING_LIST_FLT_ALL")?></option>
			<option value="Y"<?if($find_active == "Y") echo " selected"?>><?echo GetMessage("RATING_LIST_FLT_ACTIVE")?></option>
			<option value="N"<?if($find_active == "N") echo " selected"?>><?echo GetMessage("RATING_LIST_FLT_INACTIVE")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("RATING_LIST_FLT_ID")?></td>
		<td><input type="text" name="find_id" size="13" value="<?echo htmlspecialcharsbx($find_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("RATING_LIST_FLT_ENTITY_ID")?></td>
		<td><input type="text" name="find_entity_id" value="<?echo htmlspecialcharsbx($find_entity_id)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"form1"));
$oFilter->End();
?>
	</form>
<?
$lAdmin->DisplayList();
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>