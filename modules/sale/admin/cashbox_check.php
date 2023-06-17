<?php

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Cashbox\Internals;
use Bitrix\Sale\Cashbox;
use Bitrix\Main\Page;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Main\Loader;
use Bitrix\Sale\Link\EntityLinkBuilder\AdminEntityLinkBuilder;
use Bitrix\Sale\Link\EntityLinkBuilder\CrmEntityLinkBuilder;
use Bitrix\Sale\Link\Html\EntityLink;
use Bitrix\Sale\Link\Html\OrderLink;

$publicMode = $adminPage->publicMode || $adminSidePanelHelper->isPublicSidePanel();
$shouldHideOrderEntities = (
	$publicMode
	&& Loader::includeModule('crm')
	&& !CCrmSaleHelper::isWithOrdersMode()
);

$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
Page\Asset::getInstance()->addJs("/bitrix/js/sale/cashbox.js");
\Bitrix\Main\Loader::includeModule('sale');

$orderEntityLinkBuilder =
	$publicMode && Loader::includeModule('crm')
		? new CrmEntityLinkBuilder()
		: new AdminEntityLinkBuilder()
;

$tableId = "tbl_sale_cashbox_check";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminUiSorting($tableId, "ID", "asc");
$lAdmin = new CAdminUiList($tableId, $oSort);

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	foreach ($ids as $id)
	{
		if (empty($id))
			continue;

		if ($_REQUEST['action'] === 'delete')
		{
			$check = Internals\CashboxCheckTable::getRowById($id);
			if ($check['STATUS'] == 'E' || $check['STATUS'] == 'N' || $check['STATUS'] == 'P')
			{
				Cashbox\CheckManager::delete($id);
			}
			else
			{
				$lAdmin->AddGroupError(Loc::getMessage('SALE_CHECK_DELETE_ERR_INCORRECT_STATUS'), $id);
			}
		}
		elseif ($_REQUEST['action'] === 'check_status')
		{
			$check = Cashbox\CheckManager::getObjectById($id);
			$cashbox = Cashbox\Manager::getObjectById($check->getField('CASHBOX_ID'));
			if ($cashbox->isCheckable())
			{
				$r = $cashbox->check($check);
				if (!$r->isSuccess())
					$lAdmin->AddGroupError(implode("\n", $r->getErrorMessages()), $id);
			}
		}
		elseif ($_REQUEST['action'] === 'check_correction_status')
		{
			$check = Cashbox\CheckManager::getObjectById($id);

			if ($check)
			{
				$cashbox = Cashbox\Manager::getObjectById($check->getField('CASHBOX_ID'));
				if (
					$cashbox
					&& $cashbox->isCorrection()
					&& $check instanceof Cashbox\CorrectionCheck
				)
				{
					/** @var Cashbox\ICorrection $cashbox $r */
					$r = $cashbox->checkCorrection($check);
					if (!$r->isSuccess())
					{
						$lAdmin->AddGroupError(implode("\n", $r->getErrorMessages()), $id);
					}
				}
			}
		}
	}
	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

$cashBox = array();
$cashBoxQueryObject = Internals\CashboxTable::getList();
while ($item = $cashBoxQueryObject->fetch())
{
	$cashBox[$item['ID']] = $item['NAME'];
}
$statusesList = array(
	'N' => Loc::getMessage('SALE_CASHBOX_STATUS_N'),
	'P' => Loc::getMessage('SALE_CASHBOX_STATUS_P'),
	'Y' => Loc::getMessage('SALE_CASHBOX_STATUS_Y'),
	'E' => Loc::getMessage('SALE_CASHBOX_STATUS_E')
);

$filterFields = array(
	array(
		"id" => "CASHBOX_ID",
		"name" => GetMessage("SALE_F_CASHBOX"),
		"type" => "list",
		"items" => $cashBox,
		"filterable" => ""
	),
	array(
		"id" => "ID",
		"name" => GetMessage("SALE_CHECK_ID"),
		"type" => "number",
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "DATE_CREATE",
		"name" => GetMessage("SALE_F_CHECK_CREATE"),
		"type" => "date",
		"default" => true
	),
	...(
	$shouldHideOrderEntities
		? []
		: [
		[
			"id" => "ORDER_ID",
			"name" => GetMessage("SALE_F_ORDER_ID"),
			"type" => "number",
			"filterable" => "",
			"quickSearch" => ""
		]
	]
	),
	array(
		"id" => "STATUS",
		"name" => GetMessage("SALE_CASHBOX_STATUS"),
		"type" => "list",
		"items" => $statusesList,
		"filterable" => "",
		"params" => array("multiple" => "Y"),
	),
);

$filter = array();

$filterPresets = [
	'base' => [
		'name' => GetMessage('SALE_CASHBOX_CHECK_PRESET_TITLE'),
		'default' => true,
		'current' => true,
		'fields' => [
			'DATE_CREATE_datesel' => \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY,
		]
	],
];
$lAdmin->setFilterPresets($filterPresets);

$lAdmin->AddFilter($filterFields, $filter);

global $by, $order;
$by = isset($by) ? $by : "ID";
$order = isset($order) ? $order : "ASC";

$params = array(
	'filter' => $filter,
	'order' => [$by => $order],
	'runtime' => []
);

if ($shouldHideOrderEntities)
{
	$params['runtime'][] = new \Bitrix\Main\Entity\ReferenceField('ORDER_BINDING',
		\Bitrix\Crm\Binding\OrderEntityTable::getEntity(),
		[
			'=ref.ORDER_ID' => 'this.ORDER_ID',
		],
		['join_type' => \Bitrix\Main\ORM\Query\Join::TYPE_INNER]
	);
}
$dbResultList = new CAdminUiResult(Internals\CashboxCheckTable::getList($params), $tableId);

$dbResultList->NavStart();

$headers = array(
	array("id" => "ID", "content" => GetMessage("SALE_CASHBOX_ID"), "sort" => "ID", "default" => true),
	array("id" => "CHECK_TYPE", "content" => GetMessage("SALE_CASHBOX_CHECK_TYPE"), "sort" => "TYPE", "default" => true),
	(
	$shouldHideOrderEntities
		? [
		"id" => "ENTITY_ID",
		"content" => GetMessage("SALE_CASHBOX_ENTITY_ID_SOURCE"),
		"sort" => "ORDER_ID",
		"default" => true
	]
		: [
		"id" => "ORDER_ID",
		"content" => GetMessage("SALE_CASHBOX_ORDER_ID"),
		"sort" => "ORDER_ID",
		"default" => true
	]
	),
	array("id" => "CASHBOX_ID", "content" => GetMessage("SALE_CASHBOX_CASHBOX_ID"), "sort" => "CASHBOX_ID", "default" => true),
	array("id" => "DATE_CREATE", "content" => GetMessage("SALE_CASHBOX_DATE_CREATE"), "sort" => "DATE_CREATE", "default" => true),
	array("id" => "SUM", "content" => GetMessage("SALE_CASHBOX_SUM"), "sort" => "SUM", "default" => true),
	array("id" => "LINK_PARAMS", "content" => GetMessage("SALE_CASHBOX_LINK"), "default" => true),
	array("id" => "STATUS", "content" => GetMessage("SALE_CASHBOX_STATUS"), "sort" => "STATUS", "default" => true),
	array("id" => "PAYMENT", "content" => GetMessage("SALE_CASHBOX_PAYMENT_DESCR"), "sort" => "PAYMENT_ID", "default" => true),
	array("id" => "SHIPMENT", "content" => GetMessage("SALE_CASHBOX_SHIPMENT_DESCR"), "sort" => "SHIPMENT_ID", "default" => true),
	array("id" => "PAYMENT_ID", "content" => GetMessage("SALE_CASHBOX_PAYMENT_ID"), "sort" => "PAYMENT_ID", "default" => false),
	array("id" => "SHIPMENT_ID", "content" => GetMessage("SALE_CASHBOX_SHIPMENT_ID"), "sort" => "SHIPMENT_ID", "default" => false),
);

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_cashbox_check.php"));

$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();
$cashboxList = array();
$dbRes = Internals\CashboxTable::getList();
while ($item = $dbRes->fetch())
	$cashboxList[$item['ID']] = $item;

$tempResult = clone($dbResultList);
$paymentIdList = array();
$shipmentIdList = array();
$shipmentStatuses = array();
$paymentRows = array();
$shipmentRows = array();
$relatedEntities = array();
while ($check = $tempResult->Fetch())
{
	$paymentIdList[] = $check['PAYMENT_ID'];
	$shipmentIdList[] = $check['SHIPMENT_ID'];

	$relatedDbRes = Internals\CheckRelatedEntitiesTable::getList(array(
		'filter' => array('=CHECK_ID' => $check['ID'])
	));
	while ($data = $relatedDbRes->fetch())
	{
		if ($data['ENTITY_TYPE'] === Internals\CheckRelatedEntitiesTable::ENTITY_TYPE_SHIPMENT)
		{
			$shipmentIdList[] = $data['ENTITY_ID'];
			$relatedEntities[$data['CHECK_ID']][Internals\CheckRelatedEntitiesTable::ENTITY_TYPE_SHIPMENT][] = $data['ENTITY_ID'];
		}
		elseif ($data['ENTITY_TYPE'] === Internals\CheckRelatedEntitiesTable::ENTITY_TYPE_PAYMENT)
		{
			$paymentIdList[] = $data['ENTITY_ID'];
			$relatedEntities[$data['CHECK_ID']][Internals\CheckRelatedEntitiesTable::ENTITY_TYPE_PAYMENT][] = $data['ENTITY_ID'];
		}
	}
}
$paymentIdList = array_unique($paymentIdList);
$shipmentIdList = array_unique($shipmentIdList);
unset($tempResult);

$paymentData = Payment::getList(
	array(
		'select' => array('ID', 'ORDER_ID', 'PAY_SYSTEM_NAME', 'PAID', 'PS_STATUS', 'SUM', 'CURRENCY'),
		'filter' => array('=ID' => $paymentIdList)
	)
);

while ($payment = $paymentData->fetch())
{
	$linkIdUrl = $orderEntityLinkBuilder->getPaymentDetailsLink(
		(int)$payment['ORDER_ID'],
		(int)$payment['ID']
	);
	$linkId = $linkIdUrl
		? '[<a href="'.$linkIdUrl.'">'.$payment["ID"].'</a>]'
		: '['.$payment["ID"].']';

	$paymentRows[$payment['ID']] = $linkId.','.htmlspecialcharsbx($payment["PAY_SYSTEM_NAME"]).','.
		($payment["PAID"] == "Y" ? Loc::getMessage("SALE_CHECK_PAYMENTS_PAID") :  Loc::getMessage("SALE_CHECK_PAYMENTS_UNPAID")).", ".
		($payment["PS_STATUS"] <> '' ? Loc::getMessage("SALE_CASHBOX_STATUS").": ".htmlspecialcharsbx($payment["PS_STATUS"]).", " : "").
		'<span style="white-space:nowrap;">'.SaleFormatCurrency($payment["SUM"], $payment["CURRENCY"]).'</span>';
}

if (empty($shipmentStatuses))
{
	$dbRes = StatusTable::getList(array(
		'select' => array('ID', 'NAME' => 'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
		'filter' => array(
			'=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => LANGUAGE_ID,
			'=TYPE' => 'D'
		),
	));

	while ($shipmentStatus = $dbRes->fetch())
		$shipmentStatuses[$shipmentStatus["ID"]] = $shipmentStatus["NAME"]." [".$shipmentStatus["ID"]."]";
}

$shipmentData = Shipment::getList(
	array(
		'filter' => array('=ID' => $shipmentIdList)
	)
);

while ($shipment = $shipmentData->fetch())
{
	$linkIdUrl = $orderEntityLinkBuilder->getShipmentDetailsLink(
		(int)$shipment['ORDER_ID'],
		(int)$shipment['ID']
	);
	$linkId = $linkIdUrl
		? '[<a href="'.$linkIdUrl.'">'.$shipment["ID"].'</a>]'
		: '[' . $shipment["ID"] . ']';

	$fieldValue = $linkId.", ".
		($shipment["DELIVERY_NAME"] <> '' ? htmlspecialcharsbx($shipment["DELIVERY_NAME"]).",</br> " : "").
		'<span style="white-space:nowrap;">'.SaleFormatCurrency($shipment["PRICE_DELIVERY"], $shipment["CURRENCY"])."</span>, ".
		($shipment["ALLOW_DELIVERY"] == "Y" ? Loc::getMessage("SALE_CASHBOX_ALLOW_DELIVERY") : Loc::getMessage("SALE_CASHBOX_NOT_ALLOW_DELIVERY")).", ".
		($shipment["CANCELED"] == "Y" ? Loc::getMessage("SALE_CASHBOX_CANCELED").", " : "").
		($shipment["DEDUCTED"] == "Y" ? Loc::getMessage("SALE_CASHBOX_DEDUCTED").", " : "").
		($shipment["MARKED"] == "Y" ? Loc::getMessage("SALE_CASHBOX_MARKED").", " : "");

	if($shipment["STATUS_ID"] <> '')
		$fieldValue .= "<br>".($shipmentStatuses[$shipment["STATUS_ID"]] ? htmlspecialcharsbx($shipmentStatuses[$shipment["STATUS_ID"]]) : Loc::getMessage("SALE_CASHBOX_STATUS").": ".$shipment["STATUS_ID"]);

	$shipmentRows[$shipment['ID']] = $fieldValue;
}

$checkTypeMap = Cashbox\CheckManager::getCheckTypeMap();

while ($check = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($check['ID'], $check, false, GetMessage("SALE_EDIT_DESCR"));

	$row->AddField("ID", $check['ID']);

	$checkClass = $checkTypeMap[$check['TYPE']];
	$checkName = class_exists($checkClass) ? $checkClass::getName() : '';
	$row->AddField("CHECK_TYPE", $checkName);

	$orderId = (int)$check['ORDER_ID'];
	if ($shouldHideOrderEntities)
	{
		$entityLink = $orderEntityLinkBuilder->getOrderDetailUrl($orderId);
		if ($orderEntityLinkBuilder instanceof CrmEntityLinkBuilder)
		{
			$relatedEntityLink = $orderEntityLinkBuilder->getEntityDetailUrl($orderId);
			if ($relatedEntityLink)
			{
				$entityLink = $relatedEntityLink;
			}
		}

		$row->AddField("ENTITY_ID", EntityLink::createByOrder($orderId, $entityLink)->render());
	}
	else
	{
		$orderField = '';
		if ($orderId > 0)
		{
			$orderField = new OrderLink(
				$orderId,
				$orderEntityLinkBuilder->getOrderDetailUrl($orderId)
			);
			$orderField = $orderField->render();
		}

		$row->AddField("ORDER_ID",  $orderField);
	}

	$paymentIdField = '';
	if ($check['PAYMENT_ID'] > 0)
	{
		$paymentIdUrl = $orderEntityLinkBuilder->getPaymentDetailsLink(
			(int)$check['ORDER_ID'],
			(int)$check['PAYMENT_ID']
		);
		$paymentIdField = $paymentIdUrl
			? '<a href="'.$paymentIdUrl.'">'.(int)$check['PAYMENT_ID'].'</a>'
			: (int)$check['PAYMENT_ID'];
	}

	$paymentEntityIds = $relatedEntities[$check['ID']]['P'] ?? null;
	if (is_array($paymentEntityIds))
	{
		foreach ($paymentEntityIds as $entityId)
		{
			if ($paymentIdField)
				$paymentIdField .= "<br>";

			$paymentIdUrl = $orderEntityLinkBuilder->getPaymentDetailsLink(
				(int)$check['ORDER_ID'],
				(int)$entityId
			);
			$paymentIdField = $paymentIdUrl
				? '<a href="'.$paymentIdUrl.'">'.(int)$entityId.'</a>'
				: (int)$entityId;
		}
	}

	$row->AddField("PAYMENT_ID",  $paymentIdField);

	$paymentId = (int)($check['PAYMENT_ID'] ?? 0);
	$paymentField = $paymentRows[$paymentId] ?? null;
	if (is_array($paymentEntityIds))
	{
		foreach ($paymentEntityIds as $entityId)
		{
			if ($paymentField)
				$paymentField .= "<br>";
			$paymentField .= $paymentRows[(int)$entityId];
		}
	}
	$row->AddField("PAYMENT",  $paymentField);

	$shipmentIdField = '';
	if ($check['SHIPMENT_ID'] > 0)
	{
		$shipmentIdUrl = $orderEntityLinkBuilder->getShipmentDetailsLink(
			(int)$check['ORDER_ID'],
			(int)$check['SHIPMENT_ID']
		);
		$shipmentIdField = $shipmentIdUrl
			? '[<a href="'.$shipmentIdUrl.'">'.(int)$check['SHIPMENT_ID'].'</a>]'
			: '[' . (int)$check['SHIPMENT_ID'] . ']';
	}

	$shipmentEntityIds = $relatedEntities[$check['ID']]['S'] ?? null;
	if (is_array($shipmentEntityIds))
	{
		foreach ($shipmentEntityIds as $entityId)
		{
			if ($shipmentIdField)
				$shipmentIdField .= "<br>";

			$shipmentIdUrl = $orderEntityLinkBuilder->getShipmentDetailsLink(
				(int)$check['ORDER_ID'],
				(int)$entityId
			);
			$shipmentIdField = $shipmentIdUrl
				? '<a href="'.$shipmentIdUrl.'">'.(int)$entityId.'</a>'
				: (int)$entityId;
		}
	}
	$row->AddField("SHIPMENT_ID",  $shipmentIdField);

	$shipmentId = (int)($check['SHIPMENT_ID'] ?? 0);
	$shipmentField = $shipmentRows[$shipmentId] ?? '';
	if (is_array($shipmentEntityIds))
	{
		foreach ($shipmentEntityIds as $entityId)
		{
			if ($shipmentField)
				$shipmentField .= "<br>";
			$shipmentField .= $shipmentRows[(int)$entityId];
		}
	}
	$row->AddField("SHIPMENT",  $shipmentField);

	$row->AddField("DATE_CREATE", $check['DATE_CREATE']);
	$row->AddField("SUM", SaleFormatCurrency($check['SUM'], $check['CURRENCY']));

	$cashboxName = $cashboxList[$check['CASHBOX_ID']]['NAME'] ?? null;
	$row->AddField("CASHBOX_ID", htmlspecialcharsbx($cashboxName));

	$cashbox = null;
	$checkLink = '';
	if ($check['CASHBOX_ID'] > 0)
	{
		$cashbox = \Bitrix\Sale\Cashbox\Manager::getObjectById($check['CASHBOX_ID']);
		if ($cashbox && is_array($check['LINK_PARAMS']))
		{
			$link = $cashbox->getCheckLink($check['LINK_PARAMS']);
			if ($link)
				$checkLink = '<a href="'.$link.'" target="_blank">'.Loc::getMessage('SALE_CHECK_LOOK').'</a>';
		}
	}
	$row->AddField("LINK_PARAMS", $checkLink);

	$errorMessage = null;
	if (isset($check['ERROR_MESSAGE']))
	{
		$errorMessage = ' (' . $check['ERROR_MESSAGE'] . ')';
	}

	$row->AddField("STATUS", Loc::getMessage('SALE_CASHBOX_STATUS_'.$check['STATUS']) . $errorMessage);

	$arActions = array();
	if ($check['STATUS'] === 'E' || $check['STATUS'] == 'N' || $check['STATUS'] == 'P')
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_CHECK_DELETE"),
			"ACTION" => "if(confirm('".Loc::getMessage('SALE_CHECK_DELETE_CONFIRM', array('#CHECK_ID#' => $check['ID']))."')) ".$lAdmin->ActionDoGroup($check["ID"], "delete")
		);
	}

	if (
		$check['STATUS'] === 'P'
		&& $cashbox
	)
	{
		if (
			is_subclass_of($checkClass, Cashbox\CorrectionCheck::class)
			&& $cashbox->isCorrection()
		)
		{
			$arActions[] = [
				"ICON" => "check_correction_status",
				"TEXT" => GetMessage("SALE_CHECK_CHECK_STATUS"),
				"ACTION" => $lAdmin->ActionDoGroup($check["ID"], "check_correction_status", GetFilterParams())
			];
		}
		elseif (
			is_subclass_of($checkClass, Cashbox\Check::class)
			&& $cashbox->isCheckable()
		)
		{
			$arActions[] = [
				"ICON" => "check_status",
				"TEXT" => GetMessage("SALE_CHECK_CHECK_STATUS"),
				"ACTION" => $lAdmin->ActionDoGroup($check["ID"], "check_status", GetFilterParams())
			];
		}
	}

	if ($arActions)
		$row->AddActions($arActions);
}

$dbRes = Internals\CashboxTable::getList(array('filter' => array('=ACTIVE' => 'Y', '=ENABLED' => 'Y')));
if ($saleModulePermissions == "W" && $dbRes->fetch())
{
	if ($publicMode)
	{
		$aContext = array();
	}
	else
	{
		$aContext = array(
			array(
				"TEXT" => GetMessage("SALE_CASHBOX_ADD_NEW"),
				"TITLE" => GetMessage("SALE_CASHBOX_ADD_NEW"),
				"ICON" => "btn_new",
				'ONCLICK' => "BX.Sale.Cashbox.showCreateCheckWindow()"
			)
		);
	}
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_cashbox_check.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SALE_CASHBOX_CHECK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	?>
	<script language="JavaScript">
		BX.message(
			{
				CASHBOX_CREATE_WINDOW_NOT_SELECT: '<?=Loc::getMessage("CASHBOX_CREATE_WINDOW_NOT_SELECT")?>',
				CASHBOX_CREATE_WINDOW_TITLE: '<?=Loc::getMessage("CASHBOX_CREATE_WINDOW_TITLE")?>',
				CASHBOX_ADD_CHECK_INPUT_ORDER: '<?=Loc::getMessage("CASHBOX_ADD_CHECK_INPUT_ORDER")?>',
				CASHBOX_ADD_CHECK_TITLE: '<?=Loc::getMessage("CASHBOX_ADD_CHECK_TITLE")?>',
				CASHBOX_ADD_CHECK_OPTGROUP_PAYMENTS: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_OPTGROUP_PAYMENTS")?>',
				CASHBOX_ADD_CHECK_OPTGROUP_SHIPMENTS: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_OPTGROUP_SHIPMENTS")?>',
				CASHBOX_ADD_CHECK_PAYMENT: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_PAYMENT")?>',
				CASHBOX_ADD_CHECK_SHIPMENT: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_SHIPMENT")?>',
				CASHBOX_ADD_CHECK_ENTITIES: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_ENTITIES")?>',
				CASHBOX_ADD_CHECK_TYPE_CHECKS: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_TYPE_CHECKS")?>',
				CASHBOX_ADD_CHECK_ADDITIONAL_ENTITIES: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_ADDITIONAL_ENTITIES")?>'
			}
		);
	</script>
	<?
	$lAdmin->DisplayFilter($filterFields);
	$lAdmin->DisplayList();
}

require($_SERVER["DOCUMENT_ROOT"]. '/bitrix/modules/main/include/epilog_admin.php');
