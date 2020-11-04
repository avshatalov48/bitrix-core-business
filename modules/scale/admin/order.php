<?
/**
 * Bitrix Framework
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

define("ADMIN_MODULE_NAME", "scale");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main;

Loc::loadMessages(__FILE__);

if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(Loc::getMessage("SCALE_ORDER_ACCESS_DENIED"));

if(!\Bitrix\Main\Loader::includeModule("scale"))
	ShowError(Loc::getMessage("SCALE_ORDER_MODULE_NOT_INSTALLED"));

$APPLICATION->SetTitle(Loc::getMessage("SCALE_ORDER_TITLE"));

$tableID = "tbl_scale_provider";
$sorting = new CAdminSorting($tableID, "id");
$adminList = new CAdminList($tableID, $sorting);

/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();

if(($orderIds = $adminList->GroupAction()))
{
	foreach($orderIds as $orderId)
	{
		if($orderId == '')
			continue;

		$ids = explode("::", $orderId);

		if(!isset($ids[0]) || !isset($ids[1]) || $ids[0] == '' || $ids[1] == '')
			continue;

		$providerId = $ids[0];
		$orderId = $ids[1];

		switch($request['action_button'])
		{
			case "add_to_pull":

				$result = \Bitrix\Scale\Provider::addToPullFromOrder($providerId, $orderId);

				if($result === false || (isset($result["error"]) && $result["error"] == 1))
				{
					$message  = Loc::getMessage("SCALE_ORDER_ADD_PULL_ERROR");

					if(isset($result["message"]))
						$message .= ": \"".$result["message"]."\"";

					$adminList->AddGroupError($message);
				}
				else
				{
					$adminList->AddActionSuccessMessage(Loc::getMessage("SCALE_ORDER_ADD_PULL_SUCCESS"));

					try
					{
						// add to monitoring
						$actionUpdateMonitoring = \Bitrix\Scale\ActionsData::getActionObject("MONITORING_UPDATE");
						$actionUpdateMonitoring->start();
					}
					catch(Exception $e)
					{
						$adminList->AddGroupError($e->getMessage());
					}
				}

				break;
		}
	}
}

$ordersList = \Bitrix\Scale\Provider::getOrdersList();
$orders = array();

foreach($ordersList as $providerId => $providerOrders)
{
	if(!is_array($providerOrders))
		continue;
	
	foreach($providerOrders as $orderId => $order)
	{
		$order["provider"] = $providerId;
		$order["order_id"] = $orderId;
		$orders[] = $order;
	}
}

$rsList = new CDBResult;
$rsList->InitFromArray($orders);
$rsList->NavStart(20);

$data = new CAdminResult($rsList, $tableID);
$data->NavStart();
$adminList->NavText($data->GetNavPrint(Loc::getMessage("PAGES"), false));

$adminList->AddHeaders(array(
	array("id"=>"provider", "content"=>Loc::getMessage("SCALE_ORDER_PROVIDER"), "sort"=>"provider", "default"=>true),
	array("id"=>"order_id", "content"=>Loc::getMessage("SCALE_ORDER_ID"), "sort"=>"order_id", "default"=>true),
	array("id"=>"status", "content"=>Loc::getMessage("SCALE_ORDER_STATUS"), "sort"=>"status", "default"=>true),
	array("id"=>"mtime", "content"=>Loc::getMessage("SCALE_ORDER_MTIME"), "sort"=>"mtime", "default"=>true),
	array("id"=>"error", "content"=>Loc::getMessage("SCALE_ORDER_ERROR"), "default"=>false),
	array("id"=>"message", "content"=>Loc::getMessage("SCALE_ORDER_MESSAGE"), "default"=>true),
));

while($order = $data->Fetch())
{
	$provider = htmlspecialcharsbx($order["provider"]);
	$order_id = htmlspecialcharsbx($order["order_id"]);

	$row = &$adminList->AddRow($provider."::".$order_id, $order, "?provider=".$provider."&order_id=".$order_id."&lang=".LANGUAGE_ID, Loc::getMessage("SCALE_ORDER_EDIT"));
	$row->AddViewField("provider", $order["provider"]);
	$row->AddViewField("order_id", $order["order_id"]);

	$langStatuses = array(
		"finished" => Loc::getMessage("SCALE_ORDER_STATUS_FINISHED"),
		"complete" => Loc::getMessage("SCALE_ORDER_STATUS_COMPLETED"),
		"error" => Loc::getMessage("SCALE_ORDER_STATUS_ERROR"),
		"in_progress" => Loc::getMessage("SCALE_ORDER_STATUS_INPROCESS")
	);

	$status = isset($langStatuses[$order["status"]]) ? $langStatuses[$order["status"]] : $order["status"];
	$row->AddViewField("status", $status);

	$date = \Bitrix\Main\Type\Date::createFromTimestamp($order["mtime"]);
	$row->AddViewField("mtime",$date->toString());
	$row->AddViewField("error", $order["error"]);
	$row->AddViewField("message", $order["message"]);

	$arActions = array();

	if($order["status"] == "finished")
	{
		$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SCALE_ORDER_ADD_TO_PULL"), "ACTION"=>$adminList->ActionDoGroup($provider."::".$order_id, "add_to_pull"));
	}

	$row->AddActions($arActions);
}

$adminList->CheckListMode();

\CUserCounter::Increment($USER->GetID(),'SCALE_ORDER_VISITS', SITE_ID, false);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminList->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");