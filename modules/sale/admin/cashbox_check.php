<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Cashbox\Internals;
use Bitrix\Sale\Cashbox;
use Bitrix\Main\Page;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Internals\StatusTable;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
Page\Asset::getInstance()->addJs("/bitrix/js/sale/cashbox.js");
\Bitrix\Main\Loader::includeModule('sale');

$tableId = "tbl_sale_cashbox_check";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminSorting($tableId, "ID", "asc");
$lAdmin = new CAdminList($tableId, $oSort);

$arFilterFields = array(
	'filter_cashbox_id'
);
if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	foreach ($ids as $id)
	{
		if (empty($id))
			continue;

		if ($_REQUEST['action'] === 'delete')
		{
			$check = Internals\CashboxCheckTable::getRowById($id);
			if ($check['STATUS'] == 'E' || $check['STATUS'] == 'N')
			{
				Internals\CashboxCheckTable::delete($id);
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
	}
}

$lAdmin->InitFilter($arFilterFields);

$filter = array();

if (strlen($filter_cashbox_id) > 0 && $filter_cashbox_id != "NOT_REF")
	$filter["CASHBOX_ID"] = trim($filter_cashbox_id);

if (strlen($filter_date_create_from)>0)
{
	$filter[">=DATE_CREATE"] = trim($filter_date_create_from);
}
elseif($set_filter!="Y" && $del_filter != "Y")
{
	$filter_date_create_from_FILTER_PERIOD = 'day';
	$filter_date_create_from_FILTER_DIRECTION = 'current';
	$filter[">=DATE_CREATE"] = new \Bitrix\Main\Type\Date();
}

if (strlen($filter_date_create_to)>0)
{
	if($arDate = ParseDateTime($filter_date_create_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(strlen($filter_date_create_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_create_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$filter["<=DATE_CREATE"] = $filter_date_create_to;
	}
	else
	{
		$filter_date_create_to = "";
	}
}

if (strlen($filter_cashbox_id) > 0 && $filter_cashbox_id != "NOT_REF")
	$filter["CASHBOX_ID"] = trim($filter_cashbox_id);


if((int)($filter_order_id_from)>0) $filter[">=ORDER_ID"] = (int)($filter_order_id_from);
if((int)($filter_order_id_to)>0) $filter["<=ORDER_ID"] = (int)($filter_order_id_to);
if((int)($filter_id_from)>0) $filter[">=ID"] = (int)($filter_id_from);
if((int)($filter_id_to)>0) $filter["<=ID"] = (int)($filter_id_to);

if(isset($filter_check_status) && is_array($filter_check_status) && count($filter_check_status) > 0)
{
	$countFilter = count($filter_check_status);
	for ($i = 0; $i < $countFilter; $i++)
	{
		$filter_check_status[$i] = trim($filter_check_status[$i]);
		if(strlen($filter_check_status[$i]) > 0)
			$filter["=STATUS"][] = $filter_check_status[$i];
	}
}

$navyParams = array();

if ($del_filter !== 'Y')
{
	$params = array(
		'filter' => $filter
	);
}

if (isset($by))
{
	$order = isset($order) ? $order : "ASC";
	$params['order'] = array($by => $order);
}
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
	$countQuery = new \Bitrix\Main\Entity\Query(Internals\CashboxCheckTable::getEntity());
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

$dbResultList = new CAdminResult(Internals\CashboxCheckTable::getList($params), $tableId);

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

$headers = array(
	array("id" => "ID", "content" => GetMessage("SALE_CASHBOX_ID"), "sort" => "ID", "default" => true),
	array("id" => "CHECK_TYPE", "content" => GetMessage("SALE_CASHBOX_CHECK_TYPE"), "sort" => "TYPE", "default" => true),
	array("id" => "ORDER_ID", "content" => GetMessage("SALE_CASHBOX_ORDER_ID"), "sort" => "ORDER_ID", "default" => true),
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

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("group_admin_nav")));

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
	$linkId = '[<a href="/bitrix/admin/sale_order_payment_edit.php?order_id='.$payment['ORDER_ID'].'&payment_id='.$payment["ID"].'&lang='.LANGUAGE_ID.'">'.$payment["ID"].'</a>]';
	$paymentRows[$payment['ID']] = $linkId.','.htmlspecialcharsbx($payment["PAY_SYSTEM_NAME"]).','.
		($payment["PAID"] == "Y" ? Loc::getMessage("SALE_CHECK_PAYMENTS_PAID") :  Loc::getMessage("SALE_CHECK_PAYMENTS_UNPAID")).", ".
		(strlen($payment["PS_STATUS"]) > 0 ? Loc::getMessage("SALE_CASHBOX_STATUS").": ".htmlspecialcharsbx($payment["PS_STATUS"]).", " : "").
		'<span style="white-space:nowrap;">'.htmlspecialcharsbx(SaleFormatCurrency($payment["SUM"], $payment["CURRENCY"])).'</span>';
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
	$linkId = '[<a href="/bitrix/admin/sale_order_shipment_edit.php?order_id='.$shipment['ORDER_ID'].'&shipment_id='.$shipment["ID"].'&lang='.LANGUAGE_ID.'">'.$shipment["ID"].'</a>]';

	$fieldValue = $linkId.", ".
		(strlen($shipment["DELIVERY_NAME"]) > 0 ? htmlspecialcharsbx($shipment["DELIVERY_NAME"]).",</br> " : "").
		'<span style="white-space:nowrap;">'.htmlspecialcharsbx(SaleFormatCurrency($shipment["PRICE_DELIVERY"], $shipment["CURRENCY"]))."</span>, ".
		($shipment["ALLOW_DELIVERY"] == "Y" ? Loc::getMessage("SALE_CASHBOX_ALLOW_DELIVERY") : Loc::getMessage("SALE_CASHBOX_NOT_ALLOW_DELIVERY")).", ".
		($shipment["CANCELED"] == "Y" ? Loc::getMessage("SALE_CASHBOX_CANCELED").", " : "").
		($shipment["DEDUCTED"] == "Y" ? Loc::getMessage("SALE_CASHBOX_DEDUCTED").", " : "").
		($shipment["MARKED"] == "Y" ? Loc::getMessage("SALE_CASHBOX_MARKED").", " : "");

	if(strlen($shipment["STATUS_ID"]) > 0)
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

	$row->AddField("ORDER_ID",  "<a href=\"sale_order_view.php?ID=".(int)$check['ORDER_ID']."&lang=".LANG."\">".(int)$check['ORDER_ID']."</a>");

	$paymentIdField = '';
	if ($check['PAYMENT_ID'] > 0)
	{
		$paymentIdField = "<a href=\"sale_order_payment_edit.php?order_id=".(int)$check['ORDER_ID']."&payment_id=".(int)$check['PAYMENT_ID']."&lang=".LANG."\">".(int)$check['PAYMENT_ID']."</a>";
	}

	if ($relatedEntities[$check['ID']]['P'])
	{
		foreach ($relatedEntities[$check['ID']]['P'] as $entityId)
		{
			if ($paymentIdField)
				$paymentIdField .= "<br>";

			$paymentIdField .= "<a href=\"sale_order_payment_edit.php?order_id=".(int)$check['ORDER_ID']."&payment_id=".$entityId."&lang=".LANG."\">".(int)$entityId."</a>";
		}
	}

	$row->AddField("PAYMENT_ID",  $paymentIdField);

	$paymentField = $paymentRows[(int)$check['PAYMENT_ID']];
	if ($relatedEntities[$check['ID']]['P'])
	{
		foreach ($relatedEntities[$check['ID']]['P'] as $entityId)
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
		$shipmentIdField .= "<a href=\"sale_order_shipment_edit.php?order_id=".(int)$check['ORDER_ID']."&shipment_id=".(int)$check['SHIPMENT_ID']."&lang=".LANG."\">".(int)$check['SHIPMENT_ID']."</a>";
	}
	if ($relatedEntities[$check['ID']]['S'])
	{
		foreach ($relatedEntities[$check['ID']]['S'] as $entityId)
		{
			if ($shipmentIdField)
				$shipmentIdField .= "<br>";

			$shipmentIdField .= "<a href=\"sale_order_shipment_edit.php?order_id=".(int)$check['ORDER_ID']."&shipment_id=".(int)$entityId."&lang=".LANG."\">".(int)$entityId."</a>";
		}
	}
	$row->AddField("SHIPMENT_ID",  $shipmentIdField);

	$shipmentField = $shipmentRows[(int)$check['SHIPMENT_ID']];
	if ($relatedEntities[$check['ID']]['S'])
	{
		foreach ($relatedEntities[$check['ID']]['S'] as $entityId)
		{
			if ($shipmentField)
				$shipmentField .= "<br>";
			$shipmentField .= $shipmentRows[(int)$entityId];
		}
	}
	$row->AddField("SHIPMENT",  $shipmentField);

	$row->AddField("DATE_CREATE", $check['DATE_CREATE']);
	$row->AddField("SUM", SaleFormatCurrency($check['SUM'], $check['CURRENCY']));
	$row->AddField("CASHBOX_ID", htmlspecialcharsbx($cashboxList[$check['CASHBOX_ID']]['NAME']));

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
	$row->AddField("STATUS", Loc::getMessage('SALE_CASHBOX_STATUS_'.$check['STATUS']));

	$arActions = array();
	if ($check['STATUS'] === 'E' || $check['STATUS'] == 'N')
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_CHECK_DELETE"),
			"ACTION" => "if(confirm('".Loc::getMessage('SALE_CHECK_DELETE_CONFIRM', array('#CHECK_ID#' => $check['ID']))."')) ".$lAdmin->ActionDoGroup($check["ID"], "delete")
		);
	}

	if ($check['STATUS'] === 'P' && $cashbox && $cashbox->isCheckable() )
	{
		$arActions[] = array(
			"ICON" => "check_status",
			"TEXT" => GetMessage("SALE_CHECK_CHECK_STATUS"),
			"ACTION" => $lAdmin->ActionDoGroup($check["ID"], "check_status", GetFilterParams())
		);
	}

	if ($arActions)
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

$dbRes = Internals\CashboxTable::getList(array('filter' => array('=ACTIVE' => 'Y', '=ENABLED' => 'Y')));
if ($saleModulePermissions == "W" && $dbRes->fetch())
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SALE_CASHBOX_ADD_NEW"),
			"TITLE" => GetMessage("SALE_CASHBOX_ADD_NEW"),
			"LINK" => "#",
			"ICON" => "btn_new",
			'ONCLICK' => "BX.Sale.Cashbox.showCreateCheckWindow()"
		)
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SALE_CASHBOX_CHECK_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$tableId."_filter",
	array(
		"filter_cashbox_id" => Loc::getMessage("SALE_F_CASHBOX"),
		"filter_cashbox_id" => Loc::getMessage("SALE_CHECK_ID"),
		"filter_check_create" => Loc::getMessage("SALE_CHECK_CREATE"),
		"filter_order_id" => Loc::getMessage("SALE_F_ORDER_ID"),
		"filter_check_status" => Loc::getMessage("SALE_CASHBOX_STATUS"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SALE_F_CASHBOX")?>:</td>
		<td>
			<select name="filter_cashbox_id">
				<option value="NOT_REF">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?
					$dbRes = Internals\CashboxTable::getList();
					while ($item = $dbRes->fetch()): ?>
						<option value="<?=$item['ID']?>"><?= htmlspecialcharsbx($item['NAME']);?></option>
					<?endwhile;?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_CHECK_ID");?>:</td>
		<td>
			<script type="text/javascript">
				function filter_id_from_change()
				{
					if(document.find_form.filter_id_to.value.length<=0)
					{
						document.find_form.filter_id_to.value = document.find_form.filter_id_from.value;
					}
				}
			</script>
			<?echo Loc::getMessage("SALE_F_FROM");?>
			<input type="text" name="filter_id_from" onchange="filter_id_from_change()" value="<?echo ((int)($filter_id_from)>0)?(int)($filter_id_from):""?>" size="10">
			<?echo Loc::getMessage("SALE_F_TO");?>
			<input type="text" name="filter_id_to" value="<?echo ((int)($filter_id_to)>0)?(int)($filter_id_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SALE_F_CHECK_CREATE");?>:</td>
		<td>
			<?=CalendarPeriod("filter_date_create_from", htmlspecialcharsbx($filter_date_create_from), "filter_date_create_to", htmlspecialcharsbx($filter_date_create_to), "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("SALE_F_ORDER_ID");?>:</td>
		<td>
			<script type="text/javascript">
				function filter_order_id_from_change()
				{
					if(document.find_form.filter_order_id_to.value.length<=0)
					{
						document.find_form.filter_order_id_to.value = document.find_form.filter_order_id_from.value;
					}
				}
			</script>
			<?echo Loc::getMessage("SALE_F_FROM");?>
			<input type="text" name="filter_order_id_from" onchange="filter_order_id_from_change()" value="<?echo ((int)($filter_order_id_from)>0)?(int)($filter_order_id_from):""?>" size="10">
			<?echo Loc::getMessage("SALE_F_TO");?>
			<input type="text" name="filter_order_id_to" value="<?echo ((int)($filter_order_id_to)>0)?(int)($filter_order_id_to):""?>" size="10">
		</td>
	</tr>
	<tr>
		<td valign="top"><?echo Loc::getMessage("SALE_CASHBOX_STATUS")?>:</td>
		<td valign="top">
			<select name="filter_check_status[]" multiple size="3">
				<?
					$statusesList = array('N','P','Y', 'E');
					foreach($statusesList as  $statusCode)
					{
						?>
						<option value="<?= htmlspecialcharsbx($statusCode) ?>"<?if(is_array($filter_check_status) && in_array($statusCode, $filter_check_status)) echo " selected"?>>
							<?= Loc::getMessage('SALE_CASHBOX_STATUS_'.$statusCode)?>
						</option>
						<?
					}
				?>
			</select>
		</td>
	</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $tableId,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
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
			CASHBOX_ADD_CHECK_ADDITIONAL_ENTITIES: '<?=Loc::getMessage("SALE_CASHBOX_ADD_CHECK_ADDITIONAL_ENTITIES")?>',
		}
	);
</script>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>