<?php
const BX_SESSION_ID_CHANGE = false;
const BX_SKIP_POST_UNQUOTE = true;
const NO_AGENT_CHECK = true;
const STATISTIC_SKIP_ACTIVITY_CHECK = true;
const BX_FORCE_DISABLE_SEPARATED_SESSION_MODE = true;

/** @global CMain $APPLICATION */
/** @global CUserTypeManager $CACHE_MANAGER */

$type = (string)($_REQUEST['type'] ?? '');
if ($type === "crm")
{
	define("ADMIN_SECTION", true);
}

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "GET")
{
	//from main 20.0.1300 only POST allowed
	if(isset($_GET["USER_LOGIN"]) && isset($_GET["USER_PASSWORD"]) && isset($_GET["AUTH_FORM"]) && isset($_GET["TYPE"]))
	{
		$_POST["USER_LOGIN"] = $_GET["USER_LOGIN"];
		$_POST["USER_PASSWORD"] = $_GET["USER_PASSWORD"];
		$_POST["AUTH_FORM"] = $_GET["AUTH_FORM"];
		$_POST["TYPE"] = $_GET["TYPE"];
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($type === 'sale')
{
	$APPLICATION->IncludeComponent("bitrix:sale.export.1c", "", Array(
		"SITE_LIST" => COption::GetOptionString("sale", "1C_SALE_SITE_LIST", ""),
		"EXPORT_PAYED_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_PAYED_ORDERS", ""),
		"EXPORT_ALLOW_DELIVERY_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS", ""),
		"EXPORT_FINAL_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_FINAL_ORDERS", ""),
		"CHANGE_STATUS_FROM_1C" => COption::GetOptionString("sale", "1C_CHANGE_STATUS_FROM_1C", ""),
		"FINAL_STATUS_ON_DELIVERY" => COption::GetOptionString("sale", "1C_FINAL_STATUS_ON_DELIVERY", "F"),
		"REPLACE_CURRENCY" => COption::GetOptionString("sale", "1C_REPLACE_CURRENCY", ""),
		"GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("sale", "1C_SALE_GROUP_PERMISSIONS", "1")),
		"USE_ZIP" => COption::GetOptionString("sale", "1C_SALE_USE_ZIP", "Y"),
		"INTERVAL" => COption::GetOptionString("sale", "1C_INTERVAL", 30),
		"FILE_SIZE_LIMIT" => COption::GetOptionString("sale", "1C_FILE_SIZE_LIMIT", 200*1024),
		"SITE_NEW_ORDERS" => COption::GetOptionString("sale", "1C_SITE_NEW_ORDERS", "s1"),
		"IMPORT_NEW_ORDERS" => COption::GetOptionString("sale", "1C_IMPORT_NEW_ORDERS", "N"),
		)
	);
}
elseif ($type === "crm")
{
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$orderId = intval($_POST["ORDER_ID"]);
		$modifLabel = intval($_POST["MODIFICATION_LABEL"]);
		$ZZZ = intval($_POST["ZZZ"]);
		$IMPORT_SIZE = intval($_POST["IMPORT_SIZE"]);
		$GZ_COMPRESSION_SUPPORTED = intval($_POST["GZ_COMPRESSION_SUPPORTED"]);
	}
	else
	{
		$orderId = intval($_GET["ORDER_ID"]);
		$modifLabel = intval($_GET["MODIFICATION_LABEL"]);
		$ZZZ = intval($_GET["ZZZ"]);
		$IMPORT_SIZE = intval($_GET["IMPORT_SIZE"]);
		$GZ_COMPRESSION_SUPPORTED = intval($_GET["GZ_COMPRESSION_SUPPORTED"]);
	}

	$APPLICATION->IncludeComponent("bitrix:sale.export.1c", "", Array(
			"CRM_MODE" => "Y",
			"ORDER_ID" => $orderId,
			"MODIFICATION_LABEL" => $modifLabel,
			"ZZZ" => $ZZZ,
			"IMPORT_SIZE" => $IMPORT_SIZE,
			"GZ_COMPRESSION_SUPPORTED" => $GZ_COMPRESSION_SUPPORTED,
			"GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("sale", "1C_SALE_GROUP_PERMISSIONS", "1")),
			"REPLACE_CURRENCY" => COption::GetOptionString("sale", "1C_REPLACE_CURRENCY", ""),
			"USE_ZIP" => "N",
		)
	);
}
elseif ($type === "catalog")
{
	$APPLICATION->IncludeComponent(
		"bitrix:catalog.import.1c",
		"",
		[
			"IBLOCK_TYPE" => COption::GetOptionString("catalog", "1C_IBLOCK_TYPE", "-"),
			"SITE_LIST" => [COption::GetOptionString("catalog", "1C_SITE_LIST", "-")],
			"INTERVAL" => COption::GetOptionString("catalog", "1C_INTERVAL", "-"),
			"GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("catalog", "1C_GROUP_PERMISSIONS", "1")),
			"GENERATE_PREVIEW" => COption::GetOptionString("catalog", "1C_GENERATE_PREVIEW", "Y"),
			"PREVIEW_WIDTH" => COption::GetOptionString("catalog", "1C_PREVIEW_WIDTH", "100"),
			"PREVIEW_HEIGHT" => COption::GetOptionString("catalog", "1C_PREVIEW_HEIGHT", "100"),
			"DETAIL_RESIZE" => COption::GetOptionString("catalog", "1C_DETAIL_RESIZE", "Y"),
			"DETAIL_WIDTH" => COption::GetOptionString("catalog", "1C_DETAIL_WIDTH", "300"),
			"DETAIL_HEIGHT" => COption::GetOptionString("catalog", "1C_DETAIL_HEIGHT", "300"),
			"ELEMENT_ACTION" => COption::GetOptionString("catalog", "1C_ELEMENT_ACTION", "D"),
			"SECTION_ACTION" => COption::GetOptionString("catalog", "1C_SECTION_ACTION", "D"),
			"FILE_SIZE_LIMIT" => COption::GetOptionString("catalog", "1C_FILE_SIZE_LIMIT", 200*1024),
			"USE_CRC" => COption::GetOptionString("catalog", "1C_USE_CRC", "Y"),
			"USE_ZIP" => COption::GetOptionString("catalog", "1C_USE_ZIP", "Y"),
			"USE_OFFERS" => COption::GetOptionString("catalog", "1C_USE_OFFERS", "N"),
			"FORCE_OFFERS" => COption::GetOptionString("catalog", "1C_FORCE_OFFERS", "N"),
			"USE_IBLOCK_TYPE_ID" => COption::GetOptionString("catalog", "1C_USE_IBLOCK_TYPE_ID", "N"),
			"USE_IBLOCK_PICTURE_SETTINGS" => COption::GetOptionString("catalog", "1C_USE_IBLOCK_PICTURE_SETTINGS", "N"),
			"TRANSLIT_ON_ADD" => COption::GetOptionString("catalog", "1C_TRANSLIT_ON_ADD", "Y"),
			"TRANSLIT_ON_UPDATE" => COption::GetOptionString("catalog", "1C_TRANSLIT_ON_UPDATE", "Y"),
			"TRANSLIT_REPLACE_CHAR" => COption::GetOptionString("catalog", "1C_TRANSLIT_REPLACE_CHAR", "_"),
			"SKIP_ROOT_SECTION" => COption::GetOptionString("catalog", "1C_SKIP_ROOT_SECTION", "N"),
			"DISABLE_CHANGE_PRICE_NAME" => COption::GetOptionString("catalog", "1C_DISABLE_CHANGE_PRICE_NAME"),
			"IBLOCK_CACHE_MODE" => COption::GetOptionString("catalog", "1C_IBLOCK_CACHE_MODE"),
		]
	);
}
elseif ($type ==="reference")
{
	$APPLICATION->IncludeComponent("bitrix:catalog.import.hl", "", Array(
		"INTERVAL" => COption::GetOptionString("catalog", "1C_INTERVAL", "-"),
		"GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("catalog", "1C_GROUP_PERMISSIONS", "1")),
		"FILE_SIZE_LIMIT" => COption::GetOptionString("catalog", "1C_FILE_SIZE_LIMIT", 200*1024),
		"USE_CRC" => COption::GetOptionString("catalog", "1C_USE_CRC", "Y"),
		"USE_ZIP" => COption::GetOptionString("catalog", "1C_USE_ZIP", "Y"),
		)
	);
}
elseif ($type === "get_catalog")
{
	$APPLICATION->IncludeComponent("bitrix:catalog.export.1c", "", Array(
		"IBLOCK_ID" => COption::GetOptionString("catalog", "1CE_IBLOCK_ID", ""),
		"INTERVAL" => COption::GetOptionString("catalog", "1CE_INTERVAL", "-"),
		"ELEMENTS_PER_STEP" => COption::GetOptionString("catalog", "1CE_ELEMENTS_PER_STEP", 100),
		"GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("catalog", "1CE_GROUP_PERMISSIONS", "1")),
		"USE_ZIP" => COption::GetOptionString("catalog", "1CE_USE_ZIP", "Y"),
		)
	);
}
elseif ($type === "listen")
{
	$APPLICATION->RestartBuffer();

	CModule::IncludeModule('sale');

	$timeLimit = 60;//1 minute
	$startExecTime = time();
	$max_execution_time = (intval(ini_get("max_execution_time")) * 0.75);
	$max_execution_time = ($max_execution_time > $timeLimit )? $timeLimit:$max_execution_time;

	if(CModule::IncludeModule("sale") && defined("CACHED_b_sale_order"))
	{
		while(!$CACHE_MANAGER->getImmediate(CACHED_b_sale_order, "sale_orders"))
		{
			usleep(1000);

			if(intval(time() - $startExecTime) > $max_execution_time)
			{
				break;
			}
		}
	}

	if($CACHE_MANAGER->getImmediate(CACHED_b_sale_order, "sale_orders"))
	{
		echo "success\n";
	}
	else
	{
		CHTTP::SetStatus("304 Not Modified");
	}
}
else
{
	$APPLICATION->RestartBuffer();
	echo "failure\n";
	echo "Unknown command type.";
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
