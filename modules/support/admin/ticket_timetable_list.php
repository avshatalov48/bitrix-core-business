<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";

if($bAdmin!="Y" && $bDemo!="Y")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_timetable";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_name"
);
$USER_FIELD_MANAGER->AdminListAddFilterFields("TIMETABLE", $arFilterFields);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if (strlen($filter_name) > 0)
{
	$arFilter["~NAME"] = "%".$filter_name."%";
}

$USER_FIELD_MANAGER->AdminListAddFilter("TIMETABLE", $arFilter);

if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target'] == 'selected')
	{
		$arID = Array();
		$dbResultList = CSupportTimetable::GetList(array($by => $order), $arFilter);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
		{
			continue;
		}
		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				if (!CSupportTimetable::Delete($ID))
				{
					if ($e = $APPLICATION->GetException())
					{
						$lAdmin->AddGroupError($e->GetString(), $ID);
					}
				}				
				break;
		}
	}
}


$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SUP_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage('SUP_DESCRIPTION'), "default"=>true),
);
$USER_FIELD_MANAGER->AdminListAddHeaders("TIMETABLE", $arHeaders);
$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSelectedFields = array("ID", "NAME", "DESCRIPTION");

foreach($arVisibleColumns as $val)
{
	if(!in_array($val, $arSelectedFields))
	{
		$arSelectedFields[] = $val;
	}
}

$dbResultList = CSupportTimetable::GetList(array($by => $order), $arFilter);


$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SUP_GROUP_NAV")));

while ($arBlog = $dbResultList->NavNext(true, "f_"))
{      
	$row =& $lAdmin->AddRow($f_ID, $arBlog, "/bitrix/admin/ticket_timetable_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID, GetMessage("SUP_UPDATE_ALT"));

	$row->AddField("ID", '<a href="/bitrix/admin/ticket_timetable_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("SUP_UPDATE_ALT").'">'.$f_ID.'</a>');
	$row->AddField("NAME", '<a href="/bitrix/admin/ticket_timetable_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("SUP_UPDATE_ALT").'">'.$f_NAME.'</a>');
	$row->AddField("DESCRIPTION", $f_DESCRIPTION);
	
	//$USER_FIELD_MANAGER->AddUserFields("TIMETABLE", $arBlog, $row);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SUP_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("ticket_timetable_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true);
	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SUP_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('SUP_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));


	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);


$aContext = array(
	array(
		"TEXT" => GetMessage("SUP_ADD_NEW"),
		"ICON" => "btn_new",
		"LINK" => "ticket_timetable_edit.php?lang=".LANG,
		"TITLE" => GetMessage("SUP_ADD_NEW_ALT")
	),
);
$lAdmin->AddAdminContextMenu($aContext);


$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SUP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?

$oFilter = new CAdminFilter($sTableID."_filter", array());


$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SUP_FILTER_NAME")?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
<?
$USER_FIELD_MANAGER->AdminListShowFilter("TIMETABLE");

$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>