<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);
Loader::includeModule('sale');
global $APPLICATION;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_DSL_ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/prolog.php");

$by = isset($_GET['by']) ? trim($_GET['by']) : '';
$order = isset($_GET['order']) ? trim($_GET['order']) : '';

$sTableID = "tbl_sale_vk_export_list";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

//we no need filters
$arFilterFields = array();
$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
$arFilter["LID"] = LANGUAGE_ID;

$vk = Vk\Vk::getInstance();

//GOUPS actions for items

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	foreach ($arID as $id)
	{
		if ($id == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$vk->removeProfile($id);

				break;
		}
	}
}


//HEADERS for columns
$lAdmin->AddHeaders(array(
	array("id" => "ID", "content" => Loc::getMessage("SALE_VK_TABLE__ID"), "sort" => "ID", "default" => true),
	array("id" => "NAME", "content" => Loc::getMessage("SALE_VK_TABLE__DESCRIPTION"), "sort" => "", "default" => true),
	array("id" => "ACTIVE", "content" => Loc::getMessage("SALE_VK_TABLE__ACTIVE"), "sort" => "ACTIVE", "default" => true),
));


//find ITEMS for list
$resProfiles = Vk\ExportProfileTable::GetList(array(
		'filter' => array('PLATFORM_ID' => $vk->getId()),
		'select' => array('ID', 'DESCRIPTION', 'EXPORT_SETTINGS'),
	)
);
$resProfiles = new CAdminResult($resProfiles, $sTableID);
$resProfiles->NavStart();
$lAdmin->NavText($resProfiles->GetNavPrint(GetMessage("STATUS_NAV")));

while ($profile = $resProfiles->NavNext(true))
{
	$exportId = $profile["ID"];
	$row =& $lAdmin->AddRow($exportId, $profile);

	$row->AddField("ID", "<a href=\"/bitrix/admin/sale_vk_export_edit.php?ID=" . $exportId . "&lang=" . LANG . "\">" . $exportId . "</a>");
	$row->AddField("NAME", $profile['DESCRIPTION'] ? HtmlFilter::encode($profile['DESCRIPTION']) : '');
	if ($profile["EXPORT_SETTINGS"]['ACTIVE'] == 'N')
	{
		$row->AddField("ACTIVE", Loc::getMessage("SALE_VK_TABLE__ACTIVE_NO"));
	}
	else
	{
		$row->AddField("ACTIVE", $profile["EXPORT_SETTINGS"]['ACTIVE'] == 'Y' ? Loc::getMessage("SALE_VK_TABLE__YES") : Loc::getMessage("SALE_VK_TABLE__ACTIVE_NO"));
	}

//		add ACTIONS to item
	$arActions = Array();
	$arActions[] = array("ICON" => "edit", "TEXT" => Loc::GetMessage("SALE_VK_TABLE__EDIT"), "ACTION" => $lAdmin->ActionRedirect("sale_vk_export_edit.php?ID=" . $exportId . "&lang=" . LANG), "DEFAULT" => true);

	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON" => "delete", "TEXT" => Loc::GetMessage("SALE_VK_TABLE__DELETE"), "ACTION" => "if(confirm('" . Loc::getMessage('SALE_VK_TABLE__DELETE_ALERT') . "')) " . $lAdmin->ActionDoGroup($exportId, "delete"));
	}

	$row->AddActions($arActions);
}


//FOOTER add
$lAdmin->AddFooter(
	array(
		array(
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $resProfiles->SelectedRowsCount(),
		),
		array(
			"counter" => true,
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0",
		),
	)
);


//buttons for GROUP ACTIONS
$lAdmin->AddGroupActionTable(
	array("delete" => Loc::getMessage("SALE_VK_TABLE__DELETE"))
);


//buttons for CONTEXT ACTIONS in top menu
if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SALE_VK_TABLE__NEW_EXPORT"),
			"ICON" => "btn_new",
			"LINK" => "sale_vk_export_edit.php?lang=" . LANG,
			"TITLE" => GetMessage("SALE_VK_TABLE__NEW_EXPORT_ALT"),
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}
$lAdmin->CheckListMode();


/****************************************************************************/
/***********  PRINT PAGE  ***************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(Loc::getMessage("SALE_VK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");


echo 'tratata';


require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");