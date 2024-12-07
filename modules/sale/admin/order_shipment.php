<?php
use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Sale\Internals\ShipmentTable;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "L")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');
Loader::includeModule('currency');
IncludeModuleLangFile(__FILE__);
global $DB, $USER;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$tableId = "b_sale_order_shipment";
$curPage = Application::getInstance()->getContext()->getCurrent()->getRequest()->getRequestUri();
$lang    = Application::getInstance()->getContext()->getLanguage();
$siteId  = Application::getInstance()->getContext()->getSite();
$errors = '';
$sAdmin = new CAdminSorting($tableId, "ORDER_ID", "DESC");
$lAdmin = new CAdminList($tableId, $sAdmin);

$filter = array(
	'filter_order_id_from',
	'filter_order_id_to',
	'filter_allow_delivery',
	'filter_deducted',
	'filter_delivery_id',
	'filter_delivery_doc_num',
	'filter_price_delivery_from',
	'filter_price_delivery_to',
	'filter_company_id',
	'filter_date_deducted_from',
	'filter_date_deducted_to',
	'filter_status',
	'filter_account_num',
	'filter_shipment_id_from',
	'filter_shipment_id_to',
	'filter_user_id',
	'filter_user_login',
	'filter_user_email',
	'filter_is_delivery_request_failed',
	'filter_is_delivery_request_sent'
);

$lAdmin->InitFilter($filter);

$arFilter = array();
$runtimeFields = array();

$filter_order_id_from = intval($filter_order_id_from);
$filter_order_id_to = intval($filter_order_id_to);

if ($filter_allow_delivery <> '' && $filter_allow_delivery != 'NOT_REF')
	$arFilter['ALLOW_DELIVERY'] = $filter_allow_delivery;

if ($filter_deducted <> '' && $filter_deducted != 'NOT_REF')
	$arFilter['DEDUCTED'] = $filter_deducted;

if (intval($filter_price_delivery_from) > 0)
	$arFilter['>=PRICE_DELIVERY'] = $filter_price_delivery_from;
if (intval($filter_price_delivery_to) > 0)
	$arFilter['<=PRICE_DELIVERY'] = $filter_price_delivery_to;

if ($filter_delivery_doc_num <> '')
	$arFilter['DELIVERY_DOC_NUM'] = $filter_delivery_doc_num;

if ($filter_order_id_from > 0)
	$arFilter['>=ORDER_ID'] = $filter_order_id_from;
if ($filter_order_id_to > 0)
	$arFilter['<=ORDER_ID'] = $filter_order_id_to;

if ($filter_shipment_id_from > 0)
	$arFilter['>=ID'] = $filter_shipment_id_from;
if ($filter_shipment_id_to > 0)
	$arFilter['<=ID'] = $filter_shipment_id_to;

if ($filter_company_id <> '' && $filter_company_id != 'NOT_REF')
	$arFilter['COMPANY_ID'] = intval($filter_company_id);

if ($filter_date_deducted_from <> '')
	$arFilter[">=DATE_DEDUCTED"] = trim($filter_date_deducted_from);

$serviceList = array();
$filterServiceList = array();

$dbRes = \Bitrix\Sale\Delivery\Services\Table::getList(array('select' => array('ID', 'NAME', 'PARENT_ID', 'CLASS_NAME'), 'order' => array('SORT' => 'ASC')));
while ($service = $dbRes->fetch())
{
	$serviceList[$service['ID']] = $service;
	if ($service['PARENT_ID'] > 0)
		$filterServiceList[$service['PARENT_ID']][] = $service['ID'];
}

if (is_array($filter_delivery_id) && count($filter_delivery_id) > 0 && $filter_delivery_id[0] != 'NOT_REF')
{
	$arFilter['DELIVERY_ID'] = $filter_delivery_id;
	foreach ($filter_delivery_id as $deliveryId)
	{
		if (array_key_exists($deliveryId, $filterServiceList))
			$arFilter['DELIVERY_ID'] = array_merge($arFilter['DELIVERY_ID'], $filterServiceList[$deliveryId]);
	}
}

if (!empty($filter_date_deducted_to))
{
	if ($arDate = ParseDateTime($filter_date_deducted_to, CSite::GetDateFormat("FULL", $siteId)))
	{
		if (mb_strlen($filter_date_deducted_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_deducted_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", $siteId)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_DEDUCTED"] = $filter_date_deducted_to;
	}
	else
	{
		$filter_date_deducted_to = "";
	}
}

if (isset($filter_status) && is_array($filter_status) && count($filter_status) > 0)
{
	foreach ($filter_status as $key => $status)
	{
		$filter_status[$key] = trim($status);
		if ($filter_status[$key] <> '')
			$arFilter["=STATUS_ID"][] = $filter_status[$key];
	}
}

if (!empty($filter_account_num))
{
	$arFilter['ORDER.ACCOUNT_NUMBER'] = $filter_account_num;
}

if (!empty($filter_user_login))
{
	$arFilter["ORDER.USER.LOGIN"] = trim($filter_user_login);
}
if (!empty($filter_user_email))
{
	$arFilter["ORDER.USER.EMAIL"] = trim($filter_user_email);
}
if (!empty($filter_user_id))
{
	$arFilter["ORDER.USER_ID"] = intval($filter_user_id);
}

if (!empty($filter_is_delivery_request_failed))
{
	if ($filter_is_delivery_request_failed == 'Y')
	{
		$arFilter["!=DELIVERY_REQUEST_SHIPMENT.ERROR_DESCRIPTION"] = false;
	}
	else
	{
		$arFilter["=DELIVERY_REQUEST_SHIPMENT.ERROR_DESCRIPTION"] = false;
	}
}

if (!empty($filter_is_delivery_request_sent))
{
	if ($filter_is_delivery_request_sent == 'Y')
	{
		$arFilter["!=DELIVERY_REQUEST_SHIPMENT.REQUEST_ID"] = false;
	}
	else
	{
		$arFilter["=DELIVERY_REQUEST_SHIPMENT.REQUEST_ID"] = false;
	}
}

$allowedStatusesView = \Bitrix\Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
$allowedStatusesUpdate = \Bitrix\Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));


if($saleModulePermissions == "P")
{
	$userCompanyList = \Bitrix\Sale\Services\Company\Manager::getUserCompanyList($USER->GetID());

	$arFilter[] = array(
		'LOGIC' => 'OR',
		'=COMPANY_ID' => $userCompanyList,
		'=ORDER.RESPONSIBLE_ID' => intval($USER->GetID()),
		'=ORDER.COMPANY_ID' => $userCompanyList,
		'=RESPONSIBLE_ID' => intval($USER->GetID())
	);

}

if($saleModulePermissions < "W")
{
	if(!$arFilter["=STATUS_ID"])
		$arFilter["=STATUS_ID"] = array();

	$intersected = array_intersect($arFilter["=STATUS_ID"], $allowedStatusesView, $allowedStatusesUpdate);

	if(!empty($arFilter["=STATUS_ID"]))
	{
		if(empty($intersected))
		{
			$arFilter["=STATUS_ID"] = array_merge($arFilter["=STATUS_ID"], $allowedStatusesView, $allowedStatusesUpdate);
		}
		else
		{
			$arFilter["=STATUS_ID"] = $intersected;
		}
	}
	else
	{
		$arFilter["=STATUS_ID"] = array_merge($allowedStatusesView, $allowedStatusesUpdate);
	}

}

if (empty($arFilter["=STATUS_ID"]))
{
	unset($arFilter["=STATUS_ID"]);
}
else
{
	$arFilter["=STATUS_ID"] = array_unique($arFilter["=STATUS_ID"]);
}

if($arID = $lAdmin->GroupAction())
{
	$shipments = array();

	$select = array(
		'ID', 'ORDER_ID'
	);
	$filter['=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
	$filter['=SYSTEM'] = 'N';

	if($_REQUEST['action_target'] != 'selected')
		$filter['ID'] = $_REQUEST['ID'];

	$params = array(
		'select' => $select,
		'filter' => $filter,
		'limit' => 1000
	);

	$result = ShipmentTable::getList($params);

	while ($arResult = $result->fetch())
	{
		if (!isset($shipments[$arResult['ORDER_ID']]))
			$shipments[$arResult['ORDER_ID']] = array();
		$shipments[$arResult['ORDER_ID']][] = $arResult['ID'];
	}


	foreach ($shipments as $orderId => $ids)
	{
		$isOperationSuccess = false;

		$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var \Bitrix\Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		/** @var \Bitrix\Sale\Order $currentOrder */
		$currentOrder = $orderClass::load($orderId);
		if (!$currentOrder)
			continue;

		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $currentOrder->getShipmentCollection();

		foreach ($ids as $id)
		{
			if ($id == '')
				continue;

			/** @var \Bitrix\Sale\Shipment $shipment */
			$shipment = $shipmentCollection->getItemById($id);
			if (!$shipment)
				continue;

			@set_time_limit(0);

			switch ($_REQUEST['action'])
			{
				case "delete":
					$res = $shipment->delete();
					if ($res->isSuccess())
						$isOperationSuccess = true;
					else
						$lAdmin->AddGroupError(implode('\n', $res->getErrorMessages()));
					break;
				case "deducted":
				case "deducted_n":

					$deducted = $_REQUEST['action'] === 'deducted' ? 'Y' : 'N';
					$res = $shipment->setField('DEDUCTED', $deducted);
					if ($res->isSuccess())
						$isOperationSuccess = true;
					else
						$lAdmin->AddGroupError(implode('\n', $res->getErrorMessages()));
					break;
				case "allow_delivery":
				case "allow_delivery_n":
					$allowDelivery = $_REQUEST['action'] === 'allow_delivery' ? 'Y' : 'N';
					$res = $shipment->setField('ALLOW_DELIVERY', $allowDelivery);
					if ($res->isSuccess())
						$isOperationSuccess = true;
					else
						$lAdmin->AddGroupError(implode('\n', $res->getErrorMessages()));
					break;
			}
		}
		if ($isOperationSuccess)
		{
			$res = $currentOrder->save();
			if (!$res->isSuccess())
				$lAdmin->AddGroupError(implode('\n', $res->getErrorMessages()));
		}
	}
}


$headers = array(
	array("id" => "DELIVERY_DOC_DATE", "content" => GetMessage("SALE_ORDER_DELIVERY_DOC_DATE"), "sort"=> "DELIVERY_DOC_DATE", "default" => true),
	array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true),
	array("id" => "ORDER_ID", "content" => GetMessage("SALE_ORDER_ID"), "sort" => "ORDER_ID", "default" => true),
	array("id" => "ACCOUNT_NUMBER", "content" => GetMessage("SALE_ORDER_ACCOUNT_NUMBER"), "sort" => "ORDER.ACCOUNT_NUMBER", "default" => false),
	array("id" => "ORDER_USER_NAME", "content" => GetMessage("SALE_ORDER_USER_NAME"), "sort" => "ORDER_USER_NAME", "default" => true),
	array("id" => "ALLOW_DELIVERY", "content" => GetMessage("SALE_ORDER_ALLOW_DELIVERY"), "sort" => "ALLOW_DELIVERY", "default" => true),
	array("id" => "STATUS", "content" => GetMessage("SALE_ORDER_STATUS"), "sort" => 'STATUS.ID', "default" => true),
	array("id" => "DEDUCTED", "content" => GetMessage("SALE_ORDER_DEDUCTED"), "sort" => "DEDUCTED", "default" => true),
	array("id" => "DELIVERY_NAME", "content" => GetMessage("SALE_ORDER_DELIVERY_NAME"), "sort"=> "DELIVERY_NAME", "default" => true),
	array("id" => "PRICE_DELIVERY", "content" => GetMessage("SALE_ORDER_PRICE_DELIVERY"), "sort" => "PRICE_DELIVERY", "default" => true),
	array("id" => "COMPANY_BY", "content" => GetMessage("SALE_ORDER_COMPANY_ID"), "sort"=> "COMPANY_BY.NAME", "default" => true),
	array("id" => "DELIVERY_DOC_NUM", "content" => GetMessage("SALE_ORDER_DELIVERY_DOC_NUM"), "sort"=> "DELIVERY_DOC_NUM", "default" => true),
	array("id" => "RESPONSIBLE_BY", "content" => GetMessage("SALE_ORDER_DELIVERY_RESPONSIBLE_ID"), "sort"=> "", "default" => true),
	array("id" => "REASON_UNDO_DEDUCTED", "content" => GetMessage("SALE_ORDER_REASON_UNDO_DEDUCTED"), "default" => false),
	array("id" => "TRACKING_NUMBER", "content" => GetMessage("SALE_ORDER_TRACKING_NUMBER"), "sort"=> "TRACKING_NUMBER", "default" => false),
	array("id" => "XML_ID", "content" => "XML_ID", "sort"=> "XML_ID", "default" => false),
	array("id" => "PARAMETERS", "content" => GetMessage("SALE_ORDER_PARAMETERS"), "default" => false),
	array("id" => "CANCELED", "content" => GetMessage("SALE_ORDER_CANCELED"), "sort"=> "CANCELED", "default" => false),
	array("id" => "REASON_CANCELED", "content" => GetMessage("SALE_ORDER_REASON_CANCELED"), "default" => false),
	array("id" => "MARKED", "content" => GetMessage("SALE_ORDER_MARKED"), "sort"=> "MARKED", "default" => false),
	array("id" => "REASON_MARKED_ID", "content" => GetMessage("SALE_ORDER_REASON_MARKED_ID"), "default" => false),
	array("id" => "DELIVERY_REQUEST_ID", "content" => GetMessage("SALE_ORDER_DELIVERY_REQ_ID"), "default" => false),
	array("id" => "IS_DELIVERY_REQUEST_FAILED", "content" => GetMessage("SALE_ORDER_DELIVERY_REQ_DELIVERY_ERROR"), "default" => false),
);

$lAdmin->AddHeaders($headers);
$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

$select = array(
	'*',
	'STATUS_NAME' => 'STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
	'ORDER.CURRENCY',
	'ORDER.ACCOUNT_NUMBER',
	'COMPANY_BY.NAME',
	'STATUS_COLOR' => 'STATUS.COLOR',
	'EMP_DEDUCTED_BY_NAME' => 'EMP_DEDUCTED_BY.NAME',
	'EMP_DEDUCTED_BY_LAST_NAME' => 'EMP_DEDUCTED_BY.LAST_NAME',
	'EMP_ALLOW_DELIVERY_BY_NAME' => 'EMP_ALLOW_DELIVERY_BY.NAME',
	'EMP_ALLOW_DELIVERY_BY_LAST_NAME' => 'EMP_ALLOW_DELIVERY_BY.LAST_NAME',
	'EMP_CANCELED_BY_NAME' => 'EMP_CANCELED_BY.NAME',
	'EMP_CANCELED_BY_LAST_NAME' => 'EMP_CANCELED_BY.LAST_NAME',
	'EMP_MARKED_BY_NAME' => 'EMP_MARKED_BY.NAME',
	'EMP_MARKED_BY_LAST_NAME' => 'EMP_MARKED_BY.LAST_NAME',
	'ORDER_USER_NAME' => 'ORDER.USER.NAME',
	'ORDER_USER_LAST_NAME' => 'ORDER.USER.LAST_NAME',
	'ORDER_USER_ID' => 'ORDER.USER_ID',
	'ORDER_RESPONSIBLE_ID' => 'ORDER.RESPONSIBLE_ID',
	'RESPONSIBLE_BY_LAST_NAME' => 'RESPONSIBLE_BY.LAST_NAME',
	'RESPONSIBLE_BY_NAME' => 'RESPONSIBLE_BY.NAME'
);
$arFilter['=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
$arFilter['!=SYSTEM'] = 'Y';

if(in_array('IS_DELIVERY_REQUEST_FAILED', $visibleHeaders)
	|| in_array('DELIVERY_REQUEST_ID', $visibleHeaders)
	|| $filter_is_delivery_request_failed <> ''
	|| $filter_is_delivery_request_sent <> '')
{
	$runtimeFields[] = new \Bitrix\Main\Entity\ReferenceField(
		'DELIVERY_REQUEST_SHIPMENT',
		\Bitrix\Main\Entity\Base::getInstance('\Bitrix\Sale\Delivery\Requests\ShipmentTable'),
		array('ref.SHIPMENT_ID' => 'this.ID',),
		array('join_type' => 'LEFT')
	);

	$select['DELIVERY_REQUEST_SHIPMENT_ERROR_DESCRIPTION'] = 'DELIVERY_REQUEST_SHIPMENT.ERROR_DESCRIPTION';
	$select['DELIVERY_REQUEST_ID'] = 'DELIVERY_REQUEST_SHIPMENT.REQUEST_ID';
}

$params = array(
	'select' => $select,
	'filter' => $arFilter,
	'order'  => array($by => $order),
	'runtime' => $runtimeFields,
);

$usePageNavigation = true;
$navyParams = array();

$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($tableId));
if ($navyParams['SHOW_ALL'])
{
	$usePageNavigation = false;
}
else
{
	$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
	$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
}



if ($usePageNavigation)
{
	$params['limit'] = $navyParams['SIZEN'];
	$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

$totalPages = 0;

if ($usePageNavigation)
{
	$countQuery = new \Bitrix\Main\Entity\Query(ShipmentTable::getEntity());
	$countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($params['filter']);

	foreach ($params['runtime'] as $key => $field)
		$countQuery->registerRuntimeField($key, clone $field);

	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];

	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);

		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;

		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = 0;
	}
}

$dbResultList = new CAdminResult(ShipmentTable::getList($params), $tableId);
if ($usePageNavigation)
{
	$dbResultList->NavStart($params['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$dbResultList->NavRecordCount = $totalCount;
	$dbResultList->NavPageCount = $totalPages;
	$dbResultList->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$dbResultList->NavStart();
}

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("group_admin_nav")));

$allSelectedFields = array(
	"ORDER_ID" => false,
	"PAID" => false,
	"DATE_PAID" => false
);

$allSelectedFields = array_merge($allSelectedFields, array_fill_keys($visibleHeaders, true));

while ($shipment = $dbResultList->Fetch())
{
	$renderStateField = function ($field) use ($shipment) {
		$view = $shipment[$field] == "Y" ? GetMessage("SHIPMENT_ORDER_YES") : GetMessage("SHIPMENT_ORDER_NO");

		if (
			$shipment['EMP_'.$field.'_ID']
			&& (
				$shipment['EMP_'.$field.'_BY_LAST_NAME']
				|| $shipment['EMP_'.$field.'_BY_NAME']
			)
		)
		{
			$view .= "<br><a href=\"user_edit.php?ID=".$shipment['EMP_'.$field.'_ID']."\">".htmlspecialcharsbx($shipment['EMP_'.$field.'_BY_LAST_NAME'])." ".htmlspecialcharsbx($shipment['EMP_'.$field.'_BY_NAME'])."</a>";
		}

		if ($shipment['DATE_'.$field])
		{
			$view .= "<br>".htmlspecialcharsbx($shipment['DATE_'.$field]);
		}

		return $view;
	};

	$row =& $lAdmin->AddRow($shipment['ID'], $shipment);
	$row->AddField("ID", "<a href=\"sale_order_shipment_edit.php?order_id=".$shipment['ORDER_ID']."&shipment_id=".$shipment['ID']."&lang=".$lang.GetFilterParams("filter_")."\">".$shipment['ID']."</a>");
	$row->AddField("ORDER_ID", "<a href=\"sale_order_edit.php?ID=".$shipment['ORDER_ID']."&lang=".$lang.GetFilterParams("filter_")."\">".$shipment['ORDER_ID']."</a>");
	$row->AddField("DELIVERY_NAME", "<a href=\"sale_delivery_service_edit.php?ID=".$shipment['DELIVERY_ID']."&lang=".$lang.GetFilterParams("filter_")."\">".htmlspecialcharsbx($shipment['DELIVERY_NAME'])."</a>");
	$row->AddField("ACCOUNT_NUMBER", htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_ORDER_ACCOUNT_NUMBER']));
	$row->AddField("COMPANY_BY", "<a href=\"sale_company_edit.php?ID=".$shipment['COMPANY_ID']."&lang=".$lang.GetFilterParams("filter_")."\">".htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_COMPANY_BY_NAME'])."</a>");
	$row->AddField("ORDER_USER_NAME", "<a href='/bitrix/admin/user_edit.php?ID=".$shipment['ORDER_USER_ID']."&lang=".$lang."'>".htmlspecialcharsbx($shipment['ORDER_USER_NAME'])." ".htmlspecialcharsbx($shipment['ORDER_USER_LAST_NAME'])."</a>");
	$row->AddField("PRICE_DELIVERY", \CCurrencyLang::CurrencyFormat($shipment['PRICE_DELIVERY'], $shipment['SALE_INTERNALS_SHIPMENT_ORDER_CURRENCY']));

	$row->AddField("DEDUCTED", $renderStateField('DEDUCTED'));

	$row->AddField("RESPONSIBLE_BY", "<a href=\"user_edit.php?ID=".$shipment['RESPONSIBLE_ID']."\">".htmlspecialcharsbx($shipment['RESPONSIBLE_BY_NAME'])." ".htmlspecialcharsbx($shipment['RESPONSIBLE_BY_LAST_NAME'])."</a>");

	$row->AddField("ALLOW_DELIVERY", $renderStateField('ALLOW_DELIVERY'));

	$row->AddField("CANCELED", $renderStateField('CANCELED'));

	$row->AddField("MARKED", $renderStateField('MARKED'));

	if (in_array("DELIVERY_REQUEST_ID", $visibleHeaders))
	{
		$row->AddField("DELIVERY_REQUEST_ID", intval($shipment["DELIVERY_REQUEST_ID"]) > 0 ? '<a href="/bitrix/admin/sale_delivery_request_view.php?lang='.LANGUAGE_ID.'&ID='.$shipment["DELIVERY_REQUEST_ID"].'">'.$shipment["DELIVERY_REQUEST_ID"].'</a>' : '');
	}

	if (in_array("IS_DELIVERY_REQUEST_FAILED", $visibleHeaders))
	{
		$row->AddField("IS_DELIVERY_REQUEST_FAILED", $shipment["DELIVERY_REQUEST_SHIPMENT_ERROR_DESCRIPTION"] <> '' ? GetMessage("SHIPMENT_ORDER_YES") : GetMessage("SHIPMENT_ORDER_NO"));
	}

	$status = '';
	if ($shipment['STATUS_COLOR'])
	{
		$colorRGB = sscanf($shipment['STATUS_COLOR'], "#%02x%02x%02x");

		if (is_array($colorRGB) && count($colorRGB))
		{
			$color = "background:rgba(".$colorRGB[0].",".$colorRGB[1].",".$colorRGB[2].",0.6);";
			$status = '<div style=	"'.$color.'
										margin: -11px 0 -10px -16px;
										padding: 11px 0 10px 16px;
										min-height: 100%;
									">'.htmlspecialcharsbx($shipment['STATUS_NAME'])."</div>";
		}
	}

	if ($status === '')
	{
		$status = htmlspecialcharsbx($shipment['STATUS_NAME']);
	}

	$row->AddField("STATUS", $status);

	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("EDIT_SHIPMENT_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_shipment_edit.php?order_id=".$shipment['ORDER_ID']."&shipment_id=".$shipment['ID']."&lang=".$lang.GetFilterParams("filter_").""), "DEFAULT"=>true);

	if (empty($bReadOnly))
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("DELETE_SHIPMENT_ALT"), "ACTION"=>"if(confirm('".GetMessageJS('DELETE_SHIPMENT_CONFIRM')."')) ".$lAdmin->ActionDoGroup($shipment['ID'], "delete"));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"deducted" => GetMessage("SALE_ORDER_DELIVERY_ACTION_DEDUCT"),
		"deducted_n" => GetMessage("SALE_ORDER_DELIVERY_ACTION_DEDUCT_N"),
		"allow_delivery" => GetMessage("SALE_ORDER_DELIVERY_ACTION_ALLOW_DLV"),
		"allow_delivery_n" => GetMessage("SALE_ORDER_DELIVERY_ACTION_ALLOW_DLV_N"),
	)
);

$lAdmin->AddAdminContextMenu();

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

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SHIPMENT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?=$curPage?>?">
<?
$filter = array(
	"filter_order_id_from" => GetMessage("PAYMENT_ORDER_ID"),
	"filter_order_paid"     => GetMessage("PAYMENT_ORDER_PAID"),
	"filter_date_paid" => GetMessage("PAYMENT_DATE_PAID"),
	"filter_account_num" => GetMessage("PAYMENT_ACCOUNT_NUM"),
	"filter_user_id" => GetMessage("SALE_SHIPMENT_F_USER_ID"),
	"filter_user_login" => GetMessage("SALE_SHIPMENT_F_USER_LOGIN"),
	"filter_user_email" => GetMessage("SALE_SHIPMENT_F_USER_EMAIL"),
	"filter_is_delivery_request_failed" => GetMessage("SALE_ORDER_DELIVERY_REQ_DELIVERY_ERROR"),
	"filter_is_delivery_request_sent" => GetMessage("SALE_ORDER_DELIVERY_REQ_IS_SENT"),
);

$oFilter = new CAdminFilter(
	$tableId."_filter",
	$filter
);

$oFilter->Begin();
?>
<tr>
	<td><?=GetMessage("SHIPMENT_ORDER_ID");?>:</td>
	<td>
		<script>
			function changeFilterOrderIdFrom()
			{
				if (document.find_form.filter_order_id_to.value.length<=0)
					document.find_form.filter_order_id_to.value = document.find_form.filter_order_id_from.value;
			}
		</script>
		<?=GetMessage("SHIPMENT_ORDER_ID_FROM");?>
		<input type="text" name="filter_order_id_from" OnChange="changeFilterOrderIdFrom()" value="<?=(intval($filter_order_id_from)>0)?intval($filter_order_id_from):""?>" size="10">
		<?=GetMessage("SHIPMENT_ORDER_ID_TO");?>
		<input type="text" name="filter_order_id_to" value="<?=(intval($filter_order_id_to)>0)?intval($filter_order_id_to):""?>" size="10">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SHIPMENT_ID");?>:</td>
	<td>
		<script>
			function changeFilterOrderIdFrom()
			{
				if (document.find_form.filter_shipment_id_to.value.length<=0)
					document.find_form.filter_shipment_id_to.value = document.find_form.filter_shipment_id_from.value;
			}
		</script>
		<?=GetMessage("SHIPMENT_ORDER_ID_FROM");?>
		<input type="text" name="filter_shipment_id_from" OnChange="changeFilterOrderIdFrom()" value="<?=(intval($filter_shipment_id_from) > 0) ? intval($filter_shipment_id_from) : ""?>" size="10">
		<?=GetMessage("SHIPMENT_ORDER_ID_TO");?>
		<input type="text" name="filter_shipment_id_to" value="<?=(intval($filter_shipment_id_to) > 0) ? intval($filter_shipment_id_to) : ""?>" size="10">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_ALLOW_DELIVERY");?>:</td>
	<td>
		<select name="filter_allow_delivery">
			<option value="NOT_REF">(<?=GetMessage("SALE_ORDER_ALL");?>)</option>
			<option value="Y"<?if ($filter_allow_delivery=="Y") echo " selected"?>><?=GetMessage("SHIPMENT_ORDER_YES");?></option>
			<option value="N"<?if ($filter_allow_delivery=="N") echo " selected"?>><?=GetMessage("SHIPMENT_ORDER_NO");?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_DEDUCTED");?>:</td>
	<td>
		<select name="filter_deducted">
			<option value="NOT_REF">(<?=GetMessage("SALE_ORDER_ALL");?>)</option>
			<option value="Y"<?if ($filter_allow_delivery=="Y") echo " selected"?>><?=GetMessage("SHIPMENT_ORDER_YES");?></option>
			<option value="N"<?if ($filter_allow_delivery=="N") echo " selected"?>><?=GetMessage("SHIPMENT_ORDER_NO");?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?=GetMessage("SHIPMENT_DATE_DEDUCTED");?>:</td>
	<td>
			<?=CalendarPeriod("filter_date_deducted_from", htmlspecialcharsbx($filter_date_deducted_from), "filter_date_deducted_to",
				htmlspecialcharsbx($filter_date_deducted_to), "find_form", "Y")?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_DELIVERY_NAME");?>:</td>
	<td>
		<select multiple name="filter_delivery_id[]">
			<option value="NOT_REF">(<?=GetMessage("SALE_ORDER_ALL");?>)</option>
			<?
			\Bitrix\Sale\Delivery\Services\Manager::getHandlersList();

			$result = array();
			foreach ($serviceList as $serviceId => $service)
			{
				if (is_callable($service['CLASS_NAME'].'::canHasChildren') && $service['CLASS_NAME']::canHasChildren())
					continue;

				if ((int)$service['PARENT_ID'] > 0)
					$name = $serviceList[$service['PARENT_ID']]['NAME'].': '.$service['NAME'];
				else
					$name = $service['NAME'];

				$selected = (is_array($filter_delivery_id) && in_array($serviceId, $filter_delivery_id)) ? 'selected' : '';
				$name = htmlspecialcharsbx($name);
				echo '<option title="'.$name.'" value="'.htmlspecialcharsbx($serviceId).'" '.$selected.'">['.htmlspecialcharsbx($serviceId).'] '.$name.'</option>';
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_DELIVERY_DOC_NUM");?>:</td>
	<td>
		<input type="text" name="filter_delivery_doc_num" value="<?=htmlspecialcharsbx($filter_delivery_doc_num);?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_PRICE_DELIVERY");?>:</td>
	<td>
		<?echo GetMessage("PRICE_DELIVERY_FROM");?>
		<input type="text" name="filter_price_delivery_from" value="<?=($filter_price_delivery_from!=0) ? htmlspecialcharsbx($filter_price_delivery_from) : '';?>" size="3">

		<?echo GetMessage("PRICE_DELIVERY_TO");?>
		<input type="text" name="filter_price_delivery_to" value="<?=($filter_price_delivery_to!=0) ? htmlspecialcharsbx($filter_price_delivery_to) : '';?>" size="3">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_ACCOUNT_NUM");?>:</td>
	<td>
		<input type="text" name="filter_account_num" value="<?=htmlspecialcharsbx($filter_account_num)?>">
	</td>
</tr>
<?
	$params = array(
		'select' => array('ID', 'NAME')
	);
	$res = \Bitrix\Sale\Internals\CompanyTable::getList($params);
	$companies = $res->fetchAll();
?>
<tr>
	<td><?=GetMessage("SALE_ORDER_COMPANY_ID");?>:</td>
	<td>
		<select name="filter_company_id">
			<option value="NOT_REF">(<?=GetMessage("SALE_ORDER_ALL");?>)</option>
			<?
			foreach ($companies as $company)
				echo '<option value="'.$company['ID'].'">'.htmlspecialcharsbx($company['NAME']).'</option>';
			?>
		</select>
	</td>
</tr>
<?
	$statusesList = \Bitrix\Sale\DeliveryStatus::getStatusesUserCanDoOperations(
		$USER->GetID(),
		array('view')
	);

	$allStatusNames = \Bitrix\Sale\DeliveryStatus::getAllStatusesNames();
?>
<tr>
	<td valign="top"><?echo GetMessage("SALE_ORDER_SHIPMENT_STATUS")?>:<br /><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
	<td valign="top">
		<select name="filter_status[]" multiple size="3">
			<?
			foreach($statusesList as  $statusCode)
			{
				if (!$statusName = $allStatusNames[$statusCode])
					continue;
				?><option value="<?= htmlspecialcharsbx($statusCode) ?>"<?if (is_array($filter_status) && in_array($statusCode, $filter_status)) echo " selected"?>>[<?=htmlspecialcharsbx($statusCode)?>] <?= htmlspecialcharsEx($statusName) ?></option><?
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td><?echo \Bitrix\Main\Localization\Loc::getMessage("SALE_SHIPMENT_F_USER_ID");?>:</td>
	<td>
		<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
	</td>
</tr>
<tr>
	<td><?echo \Bitrix\Main\Localization\Loc::getMessage("SALE_SHIPMENT_F_USER_LOGIN");?>:</td>
	<td>
		<input type="text" name="filter_user_login" value="<?echo htmlspecialcharsbx($filter_user_login)?>" size="40">
	</td>
</tr>
<tr>
	<td><?echo \Bitrix\Main\Localization\Loc::getMessage("SALE_SHIPMENT_F_USER_EMAIL");?>:</td>
	<td>
		<input type="text" name="filter_user_email" value="<?echo htmlspecialcharsbx($filter_user_email)?>" size="40">
	</td>
</tr>
<tr>
	<td><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_ORDER_DELIVERY_REQ_DELIVERY_ERROR')?>:</td>
	<td>
		<select name="filter_is_delivery_request_failed" class="adm-select">
			<option value="">(<?=\Bitrix\Main\Localization\Loc::getMessage('SALE_ORDER_ALL')?>)</option>
			<option value="Y"><?=\Bitrix\Main\Localization\Loc::getMessage('SHIPMENT_ORDER_YES')?></option>
			<option value="N"><?=\Bitrix\Main\Localization\Loc::getMessage('SHIPMENT_ORDER_NO')?></option>
		</select>
	</td>
</tr>
	<tr>
		<td><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_ORDER_DELIVERY_REQ_IS_SENT')?>:</td>
		<td>
			<select name="filter_is_delivery_request_sent" class="adm-select">
				<option value="">(<?=\Bitrix\Main\Localization\Loc::getMessage('SALE_ORDER_ALL')?>)</option>
				<option value="Y"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_ORDER_DELIVERY_REQ_IS_SENT_Y')?></option>
				<option value="N"><?=\Bitrix\Main\Localization\Loc::getMessage('SALE_ORDER_DELIVERY_REQ_IS_SENT_N')?></option>
			</select>
		</td>
	</tr>
<?

$oFilter->Buttons(
	array(
		"table_id" => $tableId,
		"url" => $curPage,
		"form" => "find_form"
	)
);

$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");