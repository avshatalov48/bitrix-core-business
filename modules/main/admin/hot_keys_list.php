<?
// v.091

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

$sTableID = "tbl_hk_codes";
$oSort = new CAdminSorting($sTableID, "id", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);
$hotKeyCodes = new CHotKeysCode;

$FilterArr = Array(
	"find_class_name",
	"find_code",
	"find_name",
	"find_url",
	"find_is_custom",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"CLASS_NAME"	=> $find_class_name,
	"CODE"			=> $find_code,
	"NAME"			=> $find_name,
	"URL"			=> $find_url,
	"IS_CUSTOM"		=> $find_is_custom,
);

if ($isAdmin)
{
	if($lAdmin->EditAction())
	{
		foreach($FIELDS as $ID=>$arFields)
		{
			$ID = IntVal($ID);
			if($ID <= 0)
				continue;
			if(!$hotKeyCodes->Update($ID, $arFields))
			{
				$e = $APPLICATION->GetException();
				$lAdmin->AddUpdateError(($e? $e->GetString():GetMessage("HK_UPDATE_ERROR")), $ID);
			}
		}
	}

	if(($arID = $lAdmin->GroupAction()))
	{
		if($_REQUEST['action_target']=='selected')
		{
			$rsData = $hotKeyCodes->GetList(array($by=>$order), $arFilter);
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
				case "delete":
					if(!$hotKeyCodes->Delete($ID))
						$lAdmin->AddGroupError(GetMessage("HK_DELETION_ERROR"), $ID);
					break;
			}
		}
	}
}

$rsData = $hotKeyCodes->GetList(array($by=>$order), $arFilter,false);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("HK_NAVIGATION")));

$aHeaders = array(
	array("id"=>"ID", "content"=>GetMessage("HK_ID"), "sort"=>"id", "default"=>false),
	array("id"=>"CLASS_NAME", "content"=>GetMessage("HK_CLASS_NAME"), "sort"=>"class_name", "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage("HK_CODE"), "default"=>false),
	array("id"=>"NAME", "content"=>GetMessage("HK_NAME"), "default"=>true),
	array("id"=>"COMMENTS", "content"=>GetMessage("HK_COMMENTS"), "sort"=>"comments", "default"=>true),
	array("id"=>"TITLE_OBJ", "content"=>GetMessage("HK_TITLE_OBJ"), "sort"=>"title_obj", "default"=>true),
	array("id"=>"URL", "content"=>GetMessage("HK_URL"), "sort"=>"url", "default"=>false),
	array("id"=>"IS_CUSTOM", "content"=>GetMessage("HK_IS_CUSTOM"), "sort"=>"is_custom", "default"=>false),
);

$lAdmin->AddHeaders($aHeaders);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("IS_CUSTOM",$f_IS_CUSTOM ? GetMessage('HK_FLT_TRUE') : GetMessage('HK_FLT_FALSE'));

	if($f_IS_CUSTOM)
	{
		$row->AddViewField("NAME",$f_NAME);
		$row->AddViewField("COMMENTS",$f_COMMENTS);
	}
	else
	{
		$row->AddViewField("NAME",GetMessage($f_NAME));
		$row->AddViewField("COMMENTS",$f_COMMENTS ? GetMessage($f_COMMENTS) : "");
	}

	if ($isAdmin)
	{
		$arActions = array();

		$arActions[] = 	array(
						"ICON"=>"edit",
						"DEFAULT"=>true,
						"TEXT"=>GetMessage("HK_ACTION_EDIT"),
						"ACTION"=>$lAdmin->ActionRedirect("hot_keys_edit.php?ID=".$f_ID)
						);

		if($f_IS_CUSTOM)
			$arActions[] = 	array(
							"ICON"=>"delete",
							"TEXT"=>GetMessage("HK_ACTION_DEL"),
							"ACTION"=>"if(confirm('".GetMessage("HK_DEL_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
							);

		$row->AddActions($arActions);
	}
}

$aContext=array();

if($isAdmin)
{
	$aContext = array(
		array(
			"TEXT"=>GetMessage("HK_CONTEXT_ADD"),
			"LINK"=>"hot_keys_edit.php?lang=".LANG,
			"TITLE"=>GetMessage("HK_CONTEXT_ADD_TITLE"),
			"ICON"=>"btn_new",
		),
	);
}

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("HK_TITLE"));
require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");


$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("HK_CLASS_NAME"),
		GetMessage("HK_CODE"),
		GetMessage("HK_NAME"),
		GetMessage("HK_URL"),
		GetMessage("HK_IS_CUSTOM"),
		)
	);
?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?$oFilter->Begin();?>
<tr>
	<td><?=GetMessage("HK_NAME").":"?></td>
	<td><input type="text" name="find_name" size="40" value="<?= htmlspecialcharsbx($find_name)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("HK_CLASS_NAME").":"?></td>
	<td><input type="text" name="find_class_name" size="40" value="<?= htmlspecialcharsbx($find_class_name)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("HK_CODE").":"?></td>
	<td><input type="text" name="find_code" size="40" value="<?= htmlspecialcharsbx($find_code)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("HK_URL").":"?></td>
	<td><input type="text" name="find_url" size="40" value="<?= htmlspecialcharsbx($find_url)?>"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("HK_FLT_IS_CUSTOM").":"?></td>
	<td>
		<select name="find_is_custom">
			<option value=""><?echo GetMessage("MAIN_ALL")?></option>
			<option value="1"<?if($find_is_custom == "1") echo " selected"?>><?=GetMessage("HK_FLT_TRUE")?></option>
			<option value="0"<?if($find_is_custom == "0") echo " selected"?>><?=GetMessage("HK_FLT_FALSE")?></option>
		</select>
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
