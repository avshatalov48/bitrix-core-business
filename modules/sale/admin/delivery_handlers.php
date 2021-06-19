<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_sale_delivery_handlers";

$oSort = new CAdminSorting($sTableID, "SORT", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$rsDeliveryHandlers = CSaleDeliveryHandler::GetAdminList();
		while ($arHandler = $rsDeliveryHandlers->Fetch())
		{
			$arID[] = $arHandler["SID"];
		}
	}

	$DB->StartTransaction();

	$bError = false;

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
			case "reset":
				CSaleDeliveryHandler::Reset($ID);
			break;

			case 'activate':
				CSaleDeliveryHandler::Set($ID, array('ACTIVE' => 'Y'));
			break;

			case 'deactivate':
				CSaleDeliveryHandler::Set($ID, array('ACTIVE' => 'N'));
			break;
		}

		if ($bError) break;
	}
	if (!$bError)
		$DB->Commit();
	else
		$DB->Rollback();
}

$arList = array();
$arDeliveryHandlersList = array();
$rsDeliveryHandlers = CSaleDeliveryHandler::GetAdminList(array($by => $order));
while ($arHandler = $rsDeliveryHandlers->GetNext())
{
	if ($arHandler["LID"] <> '')
		$arDeliveryHandlersList[$arHandler["SID"]][$arHandler["LID"]] = $arHandler;
	else
		$arDeliveryHandlersList[$arHandler["SID"]] = array("ALL" => $arHandler);

}

foreach ($arDeliveryHandlersList as $SID => $arSiteList)
{
	$arSites = array_keys($arSiteList);
	$SITE_ID = $arSites[0];
	$arList[] = $arDeliveryHandlersList[$SID][$SITE_ID];
}

$dbResultList = new CDBResult();
$dbResultList->InitFromArray($arList);
$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage('SALE_DH_NAV_TITLE')));

$lAdmin->AddHeaders(array(
	//array("id"=>"INSTALLED", "content" => GetMessage('SALE_DH_TABLE_ISCONFIG'), "sort"=>"ISCONFIG", "default"=>true),
	array("id"=>"ACTIVE", "content" => GetMessage('SALE_DH_TABLE_ACTIVE'), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("SALE_DH_TABLE_SORT"),  "sort"=>"SORT", "default"=>true),
	array("id"=>"SID", "content"=>"SID", "sort"=>"SID", "default"=>true),
	array("id"=>"NAME", "content" => GetMessage("SALE_DH_TABLE_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"HANDLER", "content" => GetMessage("SALE_DH_TABLE_PATH"), "sort"=>"HANDLER", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arDeliveryService = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($arDeliveryService['SID'], $arDeliveryService, "sale_delivery_handler_edit.php?SID=".$arDeliveryService['SID']."&lang=".LANGUAGE_ID, GetMessage("SALE_EDIT_DESCR"));

	$row->AddField("SID", $arDeliveryService['SID']);
	//$row->AddViewField("INSTALLED", '<div class="lamp-'.($f_INSTALLED == "Y" ? "green" : "red").'"></div>');

	if ($arDeliveryService['INSTALLED'] == 'Y')
	{
		$res = '';
		$bUseTable = true;
		foreach ($arDeliveryHandlersList[$arDeliveryService['SID']] as $siteID => $arHandler)
		{
			if ($siteID == 'ALL')
			{
				$res = '<div class="lamp-'.($arDeliveryService['ACTIVE'] == "Y" ? "green" : "red").'">';
				$bUseTable = false;
				break;
			}
			else
			{
				$res .= '<tr><td><div class="lamp-'.($arHandler['ACTIVE']== "Y" ? "green" : "red").'"></div></td><td>'.$siteID.'</td></tr>';
			}
		}

		if ($bUseTable)	$res = '<table>'.$res.'</table>';
	}
	else
	{
		$res = '<div class="lamp-red"></div>';
	}
	$row->AddViewField("ACTIVE", $res);

	$row->AddField("HANDLER", $arDeliveryService['HANDLER']);
	$row->AddField("NAME", $arDeliveryService['NAME']);
	$row->AddField("SORT", $arDeliveryService['SORT']);

	if ($saleModulePermissions >= "W")
	{
		$arActions = Array();

		if ($arDeliveryService['INSTALLED'] == "Y")
		{
			$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SALE_DH_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_delivery_handler_edit.php?SID=".urlencode($arDeliveryService['SID'])."&lang=".LANGUAGE_ID), "DEFAULT"=>true);
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SALE_DH_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessageJS('SALE_DH_CONFIRM_UNINSTALL')."')) ".$lAdmin->ActionDoGroup($arDeliveryService['SID'], "reset"));
		}
		else
		{
			$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SALE_DH_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_delivery_handler_edit.php?SID=".urlencode($arDeliveryService['SID'])."&lang=".LANGUAGE_ID), "DEFAULT"=>true);
		}
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
		"reset" => GetMessage("SALE_DH_RESET_DESCR"),
		"activate" => GetMessage("SALE_DH_ACTIVATE_DESCR"),
		"deactivate" => GetMessage("SALE_DH_DEACTIVATE_DESCR"),
	)
);



if ($saleModulePermissions == "W")
{
	$arContext = array(
		array(
			"TEXT" => GetMessage("SALE_DH_ORDINARY"),
			"LINK" => "sale_delivery.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("SALE_DH_ORDINARY_ALT"),
			"ICON" => "btn_list"
		),
	);

	$lAdmin->AddAdminContextMenu($arContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SALE_DH_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

echo BeginNote();
$location_diff = COption::GetOptionString('sale', 'ADDRESS_different_set', 'N');
if ($location_diff == "Y")
{
	$siteList = array();
	$rsSites = CSite::GetList();
	while($arRes = $rsSites->Fetch())
	{
		$arRes["ID"];

		$location = COption::GetOptionString('sale', 'location', '', $arRes["ID"]);
		$location_zip = COption::GetOptionString('sale', 'location_zip', '', $arRes["ID"]);

		echo GetMessage('SALE_DH_HINT_SHOP_ADDRESS').' ('.$arRes["ID"].'): ';
		if ($location > 0)
		{
			$arLocation = CSaleLocation::GetByID($location);
			if ($arLocation["ID"] > 0)
			{
				echo '<b>'.htmlspecialcharsEx($arLocation["COUNTRY_NAME"]." - ".$arLocation["CITY_NAME"])."</b><br />";
			}
			else
			{
				echo '<span style="color: red;"><b>'.GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ERROR').'</b></span><br />';
			}
		}
		else
		{
			echo '<span style="color: red;"><b>'.GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ERROR').'</b></span><br />';
		}

		echo GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ZIP').' ('.$arRes["ID"].'): ';
		if ($location_zip > 0)
		{
			echo '<b>'.htmlspecialcharsEx($location_zip)."</b><br />";
		}
		else
		{
			echo '<span style="color: red;"><b>'.GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ZIP_ERROR').'</b></span><br />';
		}

		echo '<br />';
	}
}
else
{
	$location = COption::GetOptionString('sale', 'location');
	$location_zip = COption::GetOptionString('sale', 'location_zip');

	echo GetMessage('SALE_DH_HINT_SHOP_ADDRESS').': ';
	if ($location > 0)
	{
		$arLocation = CSaleLocation::GetByID($location);
		if ($arLocation["ID"] > 0)
		{
			echo '<b>'.htmlspecialcharsEx($arLocation["COUNTRY_NAME"]." - ".$arLocation["CITY_NAME"])."</b><br />";
		}
		else
		{
			echo '<span style="color: red;"><b>'.GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ERROR').'</b></span><br />';
		}
	}
	else
	{
		echo '<span style="color: red;"><b>'.GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ERROR').'</b></span><br />';
	}

	echo GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ZIP').': ';
	if ($location_zip > 0)
	{
		echo '<b>'.htmlspecialcharsEx($location_zip)."</b><br />";
	}
	else
	{
		echo '<span style="color: red;"><b>'.GetMessage('SALE_DH_HINT_SHOP_ADDRESS_ZIP_ERROR').'</b></span><br />';
	}

	echo '<br />';
}

echo '<a href="/bitrix/admin/settings.php?mid=sale&lang='.LANG.'&back_url_settings='.$APPLICATION->GetCurPage().'&tabControl_active_tab=edit5">'.GetMessage('SALE_DH_SHOP_ADDRESS_CHANGE').'</a>';

echo EndNote();

echo BeginNote();
echo GetMessage("SALE_DH_HINT_ADD")." ".htmlspecialcharsEx(COption::GetOptionString('sale', 'delivery_handles_custom_path', BX_PERSONAL_ROOT."/php_interface/include/sale_delivery/"));
echo EndNote();

$lAdmin->DisplayList();

echo BeginNote();
echo GetMessage("SALE_DH_LOCATIONS_STATS").': <ul style="font-size: 100%">';

$rsLocations = CSaleLocation::GetList(array(), array(), array("COUNTRY_ID", "COUNT" => "CITY_ID"));

$numLocations = 0;
$numCountries = 0;
$numCities = 0;

while ($arStat = $rsLocations->Fetch())
{
	$numCountries++;
	$numCities += $arStat["CITY_ID"];
	$numLocations += $arStat['CNT'];
}

echo '<li>'.GetMessage('SALE_DH_LOCATIONS_COUNTRY_STATS').': '.$numCountries.'</li>';
echo '<li>'.GetMessage('SALE_DH_LOCATIONS_CITY_STATS').': '.$numCities.'</li>';
echo '<li>'.GetMessage('SALE_DH_LOCATIONS_LOC_STATS').': '.$numLocations.'</li>';

$rsLocationGroups = CSaleLocationGroup::GetList();
$numGroups = 0;
while ($arGroup = $rsLocationGroups->Fetch()) $numGroups++;

echo '<li>'.GetMessage('SALE_DH_LOCATIONS_GROUP_STATS').': '.$numGroups.'</li>';

echo '</ul>';

echo '<a href="/bitrix/admin/sale_location_admin.php?lang='.LANG.'">'.GetMessage('SALE_DH_LOCATIONS_LINK').'</a>';
echo '&nbsp;|&nbsp;';
echo '<a href="/bitrix/admin/sale_location_import.php?lang='.LANG.'">'.GetMessage('SALE_DH_LOCATIONS_IMPORT_LINK').'</a>';

echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>