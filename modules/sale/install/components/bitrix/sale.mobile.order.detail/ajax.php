<?
define("NO_KEEP_STATISTIC", true);
define('NO_AGENT_CHECK', true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/ajax.php");

if (!CModule::IncludeModule('sale')) die(GetMessage("SMOD_SALE_NOT_INSTALLED"));

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions == "D")
{
	die('Access denied');
}

if(!isset($_REQUEST['id'])) die();

$id = (int)($_REQUEST['id']);
$order = \Bitrix\Sale\Order::load($id);
$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
$isAllowView = in_array($order->getField('STATUS_ID'), $allowedStatusesView);

if($USER->IsAuthorized() && check_bitrix_sessid() && $isAllowView)
{
	if (!CModule::IncludeModule('mobileapp')) die(GetMessage('SMOD_MOBILEAPP_NOT_INSTALLED'));

	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';
	$status_id = isset($_REQUEST['status_id']) ? trim($_REQUEST['status_id']): '';

	$result = false;

	switch ($action)
	{
		case "get_transact":

			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:sale.mobile.order.transact',
				'.default',
				array(),
				false
			);

			$result = ob_get_contents();
			ob_end_clean();

		break;

		case "get_history":

			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:sale.mobile.order.history',
				'.default',
				array(),
				false
			);

			$result = ob_get_contents();
			ob_end_clean();

		break;

		case "status_save":

			if(!$status_id)
				break;

			if (!CSaleOrder::CanUserChangeOrderStatus($id, $status_id, $GLOBALS["USER"]->GetUserGroupArray()))
				break;

			$result =  CSaleOrder::StatusOrder($id, $status_id);

		break;

		case "order_cancel":

			$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($id, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());


			if (!$bUserCanCancelOrder || CSaleOrder::IsLocked($id, $lockedBY, $dateLock))
				break;

			$cancel = isset($_REQUEST['cancel']) ? trim($_REQUEST['cancel']) : 'N';
			$comment = isset($_REQUEST['comment']) ? trim($_REQUEST['comment']) : '';

			if(strlen($comment) > 0)
				$comment = $APPLICATION->ConvertCharset($comment, 'utf-8', SITE_CHARSET);

			$result = CSaleOrder::CancelOrder($id, $cancel, $comment);

		break;

		case "get_order_html":

			$arOrder = CSaleMobileOrderUtils::getOrderInfoDetail($id);
			$result = CSaleMobileOrderUtils::makeDetailClassFromOrder($arOrder);

		break;

		case "delivery_allow":

			$bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($id, "PERM_DELIVERY", $GLOBALS["USER"]->GetUserGroupArray());
			if(!$bUserCanDeliverOrder)
				break;

			$arAdditionalFields = array();

			if($status_id && CSaleOrder::CanUserChangeOrderStatus($id, $status_id, $GLOBALS["USER"]->GetUserGroupArray()))
				$arAdditionalFields = array("STATUS_ID" => $status_id);

			$deliver = isset($_REQUEST['deliver']) ? trim($_REQUEST['deliver']) : '';

			if($deliver)
				$result = CSaleOrder::DeliverOrder($id, $deliver, 0, $arAdditionalFields);
			elseif(!empty($arAdditionalFields))
				$result =  CSaleOrder::Update($id, $arAdditionalFields);
		break;

		case "order_pay":

			$bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($id, "PERM_PAYMENT", $GLOBALS["USER"]->GetUserGroupArray());
			if(!$bUserCanPayOrder)
				break;

			$payed = isset($_REQUEST['payed']) ? trim($_REQUEST['payed']) : 'N';
			$pay_from_account = isset($_REQUEST['pay_from_account']) ? trim($_REQUEST['pay_from_account']) : 'N';
			$pay_from_account_back = isset($_REQUEST['pay_from_account_back']) ? trim($_REQUEST['pay_from_account_back']) : 'N';

			$arAdditionalFields = array();
			if($status_id && CSaleOrder::CanUserChangeOrderStatus($id, $status_id, $GLOBALS["USER"]->GetUserGroupArray()))
				$arAdditionalFields = array("STATUS_ID" => $status_id);

			$bWithdraw = true;
			$bPay = true;

			if ($_REQUEST["pay_from_account"] == "Y")
				$bPay = false;

			if ($payed == "N" && $_REQUEST["pay_from_account_back"] != "Y")
				$bWithdraw = false;

			$result = CSaleOrder::PayOrder($id, $payed, $bWithdraw, $bPay, 0, $arAdditionalFields);

		break;
	}

	echo $result;
}
?>
