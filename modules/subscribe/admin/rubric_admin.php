<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_rubric";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_type",
	"find_id",
	"find_name",
	"find_lid",
	"find_active",
	"find_visible",
	"find_auto",
	"find_code",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = Array(
	"ID" => ($find!="" && $find_type == "id"? $find:$find_id),
	"NAME" => ($find!="" && $find_type == "name"? $find:$find_name),
	"LID" => $find_lid,
	"ACTIVE" => $find_active,
	"VISIBLE" => $find_visible,
	"AUTO" => $find_auto,
	"CODE" => $find_code,
);

if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = intval($ID);
		$cData = new CRubric;
		if(($rsData = $cData->GetByID($ID)) && ($arData = $rsData->Fetch()))
		{
			foreach($arFields as $key=>$value)
				$arData[$key]=$value;
			if(!$cData->Update($ID, $arData))
			{
				$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".$cData->LAST_ERROR, $ID);
				$DB->Rollback();
			}
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".GetMessage("rub_no_rubric"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
	if($_REQUEST['action_target']=='selected')
	{
		$cData = new CRubric;
		$rsData = $cData->GetList(array($by=>$order), $arFilter);
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
			if(!CRubric::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("rub_del_err"), $ID);
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			$cData = new CRubric;
			if(($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch()))
			{
				$arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
				if(!$cData->Update($ID, $arFields))
					$lAdmin->AddGroupError(GetMessage("rub_save_error").$cData->LAST_ERROR, $ID);
			}
			else
				$lAdmin->AddGroupError(GetMessage("rub_save_error")." ".GetMessage("rub_no_rubric"), $ID);
			break;
		}

	}
}

$cData = new CRubric;
$rsData = $cData->GetList(array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("rub_nav")));

$lAdmin->AddHeaders(array(
	array(	"id"		=>"ID",
		"content"	=>"ID",
		"sort"		=>"id",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"NAME",
		"content"	=>GetMessage("rub_name"),
		"sort"		=>"name",
		"default"	=>true,
	),
	array(	"id"		=>"LID",
		"content"	=>GetMessage("rub_site"),
		"sort"		=>"lid",
		"default"	=>true,
	),
	array(	"id"		=>"SORT",
		"content"	=>GetMessage("rub_sort"),
		"sort"		=>"sort",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"CODE",
		"content"	=>GetMessage("rub_code"),
		"sort"		=>"name",
		"default"	=>false,
	),
	array(	"id"		=>"ACTIVE",
		"content"	=>GetMessage("rub_act"),
		"sort"		=>"act",
		"default"	=>true,
	),
	array(	"id"		=>"VISIBLE",
		"content"	=>GetMessage("rub_visible"),
		"sort"		=>"visible",
		"default"	=>true,
	),
	array(	"id"		=>"AUTO",
		"content"	=>GetMessage("rub_auto"),
		"sort"		=>"auto",
		"default"	=>true,
	),
	array(	"id"		=>"LAST_EXECUTED",
		"content"	=>GetMessage("rub_last_exec"),
		"sort"		=>"last_executed",
		"default"	=>true,
	),
));

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", '<a href="rubric_edit.php?ID='.$f_ID.'&amp;lang='.LANG.'">'.$f_NAME.'</a>');
	$row->AddEditField("LID", CLang::SelectBox("FIELDS[".$f_ID."][LID]", $f_LID));
	$row->AddInputField("SORT", array("size"=>6));
	$row->AddInputField("CODE", array("size"=>20));
	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("VISIBLE");
	$row->AddViewField("AUTO", $f_AUTO=="Y"?GetMessage("POST_U_YES"):GetMessage("POST_U_NO"));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("rub_edit"),
		"ACTION"=>$lAdmin->ActionRedirect("rubric_edit.php?ID=".$f_ID)
	);
	if ($POST_RIGHT>="W")
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("rub_del"),
			"ACTION"=>"if(confirm('".GetMessage('rub_del_conf')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);

	$arActions[] = array("SEPARATOR"=>true);

	if ($f_TEMPLATE <> '' && $f_AUTO=="Y")
		$arActions[] = array(
			"ICON"=>"",
			"TEXT"=>GetMessage("rub_check"),
			"ACTION"=>$lAdmin->ActionRedirect("template_test.php?ID=".$f_ID)
		);

	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		unset($arActions[count($arActions)-1]);
	$row->AddActions($arActions);

endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	));

$aContext = array(
	array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"LINK"=>"rubric_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("POST_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("rub_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("rub_f_name"),
		GetMessage("rub_f_site"),
		GetMessage("rub_f_active"),
		GetMessage("rub_f_public"),
		GetMessage("rub_f_auto"),
		GetMessage("rub_f_code"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("rub_f_find")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("rub_f_find_title")?>">
		<?
		$arr = array(
			"reference" => array(
				"ID",
				GetMessage("rub_f_name"),
			),
			"reference_id" => array(
				"id",
				"name",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?="ID"?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_name")?>:</td>
	<td>
		<input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_site").":"?></td>
	<td><select name="find_lid">
		<option value=""<?echo ($find_lid == "" ? ' selected' : '') ?>><?echo GetMessage("MAIN_ALL")?></option>
		<?
		$dbSites = CSite::GetList("NAME", "asc");
		while ($arSites = $dbSites->Fetch())
		{
			?><option value="<?echo htmlspecialcharsbx($arSites["ID"]) ?>"<?echo ($find_lid == $arSites["ID"] ? ' selected' : '') ?>>(<?echo htmlspecialcharsbx($arSites["ID"]) ?>) <?echo htmlspecialcharsbx($arSites["NAME"]) ?></option><?
		}
		?>
	</select></td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_active")?>:</td>
	<td>
		<?
		$arr = array(
			"reference" => array(
				GetMessage("MAIN_YES"),
				GetMessage("MAIN_NO"),
			),
			"reference_id" => array(
				"Y",
				"N",
			)
		);
		echo SelectBoxFromArray("find_active", $arr, $find_active, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_public")?>:</td>
	<td><?echo SelectBoxFromArray("find_visible", $arr, $find_visible, GetMessage("MAIN_ALL"), "");?></td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_auto")?>:</td>
	<td><?echo SelectBoxFromArray("find_auto", $arr, $find_auto, GetMessage("MAIN_ALL"), "");?></td>
</tr>
<tr>
	<td><?=GetMessage("rub_f_code")?>:</td>
	<td>
		<input type="text" name="find_code" size="47" value="<?echo htmlspecialcharsbx($find_code)?>">
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>