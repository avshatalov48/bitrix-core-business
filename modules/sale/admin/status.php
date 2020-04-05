<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_status";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
$arFilter["LID"] = LANGUAGE_ID;

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleStatus::GetList(
				array($by => $order),
				$arFilter,
				false,
				false,
				array("ID", $by)
			);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				$lockedStatusList = array(
					\Bitrix\Sale\OrderStatus::getInitialStatus(),
					\Bitrix\Sale\OrderStatus::getFinalStatus(),
					\Bitrix\Sale\DeliveryStatus::getInitialStatus(),
					\Bitrix\Sale\DeliveryStatus::getFinalStatus(),
				);

				if (in_array($ID, $lockedStatusList))
				{
					continue;
				}

				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSaleStatus::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_DEL_STATUS"), $ID);
				}

				$DB->Commit();

				break;

		}
	}
}

$dbResultList = CSaleStatus::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array('ID', 'SORT', 'TYPE', 'NOTIFY', 'LID', 'COLOR' ,'NAME', 'DESCRIPTION', $by)
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("STATUS_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("STATUS_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("STATUS_SORT"), "sort"=>"SORT", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SALE_NAME"), "sort"=>"", "default"=>true),
	array("id"=>"TYPE", "content"=>GetMessage("SSAN_TYPE"), "sort"=>"TYPE", "default"=>true),
	array("id"=>"COLOR", "content"=>GetMessage("SSAN_COLOR"), "sort"=>"", "default"=>true),
	array('id' => 'NOTIFY', 'content' => GetMessage('SSAN_NOTIFY'), 'sort' => 'NOTIFY', 'default' => true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arCCard);

	$row->AddField("ID", "<a href=\"/bitrix/admin/sale_status_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_")."\" title=\"".GetMessage("SALE_EDIT_DESCR")."\">".$f_ID."</a>");
	$row->AddField("SORT", $f_SORT);
	$row->AddField("NAME", $f_NAME."<br><small>".$f_DESCRIPTION."</small><br>");
	$row->AddField(
		"COLOR",
		strlen($f_COLOR) ? "<div style=\"background:".$f_COLOR."; width: 23px; border: 1px solid #87919c; border-radius: 4px; height: 23px;\"></div>" : $f_COLOR
	);
	$row->AddField("TYPE", (
		$f_TYPE == 'O' ? GetMessage('SSEN_TYPE_O') :
		($f_TYPE == 'D' ? GetMessage('SSEN_TYPE_D') :
		'Invalid '.$f_TYPE)));
	$row->AddField("NOTIFY", $f_NOTIFY == 'Y'
		? '<a href="/bitrix/admin/message_admin.php?find_event_type=SALE_STATUS_CHANGED_'.$f_ID.'" target="_blank">'.GetMessage('SSAN_NOTIFY_Y').'</a>'
		: GetMessage('SSAN_NOTIFY_N'));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SALE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_status_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W" && $f_ID != "N" && $f_ID != "F")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SALE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('STATUS_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

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
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SSAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "sale_status_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SSAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("STATUS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();
?>
<br>
<?echo BeginNote();?>
	<?echo GetMessage("SALE_NOTES1")?><br>
	<?echo GetMessage("SALE_NOTES2")?><br>
<?echo EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>