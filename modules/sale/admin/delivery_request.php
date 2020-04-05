<?

use \Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loc::loadMessages(__FILE__);

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('SALE_DELIVERY_REQUEST_SEND'));

if ($APPLICATION->GetGroupRight("sale") < "U")
	$APPLICATION->AuthForm(Loc::getMessage('SALE_DELIVERY_REQUEST_ACCESS_DENIED'));

$adminErrorMessages = array();

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$adminErrorMessages = "Error! Can't include module \"Sale\"";

$shipmentIds = array();
$shipmentsIdsByDelivery = array();
$deliveryClass = array();
$action = isset($_REQUEST['ACTION']) ? $_REQUEST['ACTION'] : '';
$backUrl = isset($_REQUEST['BACK_URL']) ? $_REQUEST['BACK_URL'] : '';

if(isset($_REQUEST['SHIPMENT_IDS']) && is_array($_REQUEST['SHIPMENT_IDS']))
{
	$shipmentIds = $_REQUEST['SHIPMENT_IDS'];
}
elseif(isset($_SESSION["SALE_DELIVERY_REQUEST_SHIPMENT_IDS"]) && is_array($_SESSION["SALE_DELIVERY_REQUEST_SHIPMENT_IDS"]))
{
	$shipmentIds = $_SESSION["SALE_DELIVERY_REQUEST_SHIPMENT_IDS"];
	//unset($_SESSION["SALE_DELIVERY_REQUEST_SHIPMENT_IDS"]);
}

foreach($shipmentIds as $id => $shipmentId)
	$shipmentIds[$id] = intval($shipmentId);

if(empty($shipmentIds))
{
	$adminErrorMessages[] = Loc::getMessage('SALE_DELIVERY_REQUEST_SHIPMENT_IDS_EMPTY');
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(!empty($adminErrorMessages))
{
	$adminMessage = new CAdminMessage(
		array(
			"DETAILS" => implode("<br>\n", $adminErrorMessages),
			"TYPE" => "ERROR",
			"HTML" => true
		)
	);
	echo $adminMessage->Show();
}
else
{
	$context = new CAdminContextMenu(array(
		array(
			"TEXT" => !empty($backUrl) ? Loc::getMessage('SALE_DELIVERY_REQUEST_BACK') : Loc::getMessage('SALE_DELIVERY_REQUEST_LIST'),
			"LINK" => !empty($backUrl) ? $backUrl : "/bitrix/admin/sale_delivery_request_list.php?lang=".LANGUAGE_ID,
			"ICON" => "btn_list"
		)
	));

	$context->Show();

	$APPLICATION->IncludeComponent(
		"bitrix:sale.delivery.request",
		"",
		array(
			"SHIPMENT_IDS" => $shipmentIds,
			"ACTION" => $action
		)
	);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");