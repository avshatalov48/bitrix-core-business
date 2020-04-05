<?
use Bitrix\Main\Loader,
	Bitrix\Catalog;

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("PUBLIC_AJAX_MODE", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__FILE__);
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$userId = (int)$USER->GetID();

if ($userId <= 0)
{
	echo CUtil::PhpToJSObject(array('ERROR' => 'AUTHORIZE_ERROR'));
	die();
}
if (!Loader::includeModule('catalog'))
{
	echo CUtil::PhpToJSObject(array('ERROR' => 'CATALOG_MODULE_NOT_INSTALL'));
	die();
}
$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();
$buttonId = htmlspecialcharsbx($_REQUEST['elId']);
$strDateAction = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time());
if(check_bitrix_sessid())
{
	$iblockId = intval($_REQUEST["iblockId"]);
	if($iblockId <= 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR' => 'IBLOCK_ERROR'));
		die();
	}
	CUtil::JSPostUnescape();
	if($_REQUEST['action'] == "clearQuantity")
	{
		if (!$useStoreControl)
		{
			$dbElements = CCatalogProduct::GetList(array(), array("ELEMENT_IBLOCK_ID" => $iblockId, "!QUANTITY" => 0), false, false, array("ID"));
			while($arElements = $dbElements->Fetch())
			{
				CCatalogProduct::Update($arElements["ID"], array("QUANTITY" => 0));
			}
			COption::SetOptionString('catalog', 'clear_quantity_user', $userId);
			COption::SetOptionString('catalog', 'clear_quantity_date', $strDateAction);
		}
		else
		{
			echo CUtil::PhpToJSObject(Array('ERROR' => 'STORE_CONTROL_ERROR'));
			die();
		}
	}
	elseif($_REQUEST['action'] == "clearReservedQuantity")
	{
		$dbElements = CCatalogProduct::GetList(array(), array("ELEMENT_IBLOCK_ID" => $iblockId, "!QUANTITY_RESERVED" => 0), false, false, array("ID", "QUANTITY_RESERVED", "QUANTITY"));
		while($arElements = $dbElements->Fetch())
		{
			$quantity = $arElements["QUANTITY_RESERVED"] + $arElements["QUANTITY"];
			CCatalogProduct::Update($arElements["ID"], array("QUANTITY_RESERVED" => 0, "QUANTITY" => $quantity));
		}
		COption::SetOptionString('catalog', 'clear_reserved_quantity_user', $userId);
		COption::SetOptionString('catalog', 'clear_reserved_quantity_date', $strDateAction);

	}
	elseif($_REQUEST['action'] == "clearStore")
	{
		$storeId = (isset($_REQUEST["storeId"]) ? intval($_REQUEST["storeId"]) : 0);
		if ($storeId > 0 || $storeId == -1)
		{
			$arElementsId = array();

			$dbElements = CCatalogProduct::GetList(array(), array("ELEMENT_IBLOCK_ID" => $iblockId), false, false, array("ID", "QUANTITY"));
			while($arElements = $dbElements->Fetch())
			{
				$arElementsId[$arElements["ID"]] = $arElements["QUANTITY"];
			}
			if($storeId === -1)
			{
				$dbStoreElements = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => array_keys($arElementsId)), false, false, array("ID", "AMOUNT", "PRODUCT_ID"));
			}
			else
			{
				$dbStoreElements = CCatalogStoreProduct::GetList(array(), array("STORE_ID" => $storeId, "PRODUCT_ID" => array_keys($arElementsId)), false, false, array("ID", "AMOUNT", "PRODUCT_ID"));
			}

			while($arStoreElements = $dbStoreElements->Fetch())
			{
				CCatalogStoreProduct::Update($arStoreElements["ID"], array("AMOUNT" => 0));
				if ($useStoreControl)
				{
					$arElementsId[$arStoreElements["PRODUCT_ID"]] = $arElementsId[$arStoreElements["PRODUCT_ID"]] - $arStoreElements["AMOUNT"];
					CCatalogProduct::Update($arStoreElements["PRODUCT_ID"], array("QUANTITY" => $arElementsId[$arStoreElements["PRODUCT_ID"]]));
				}
			}
			COption::SetOptionString('catalog', 'clear_store_user', $userId);
			COption::SetOptionString('catalog', 'clear_store_date', $strDateAction);
		}
	}
}
echo CUtil::PhpToJSObject($buttonId);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");