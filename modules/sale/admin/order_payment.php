<?php

use \Bitrix\Main\Application;
use \Bitrix\Sale\Internals\PaymentTable;
use \Bitrix\Sale\Order;
use \Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('sale');
Loader::includeModule('currency');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$tableId = "b_sale_order_payment";
$curPage = Application::getInstance()->getContext()->getCurrent()->getRequest()->getRequestUri();
$lang    = Application::getInstance()->getContext()->getLanguage();

$sAdmin = new CAdminSorting($tableId, "ORDER_ID", "DESC");
$lAdmin = new CAdminList($tableId, $sAdmin);

$filter = array(
	'filter_payment_id_from',
	'filter_payment_id_to',
	'filter_order_id_from',
	'filter_order_id_to',
	'filter_date_paid_from',
	'filter_date_paid_to',
	'filter_order_paid',
	'filter_site_id',
	'filter_pay_system_id',
	'filter_company_id',
	'filter_account_num',
	'filter_sum_from',
	'filter_sum_to',
	'filter_pay_voucher_num',
	'filter_user_id',
	'filter_user_login',
	'filter_user_email'
);

$lAdmin->InitFilter($filter);

$arFilter = array();
$runtimeFields = array();

$filter_order_id_from = intval($filter_order_id_from);
$filter_order_id_to = intval($filter_order_id_to);
$filter_sum_from = intval($filter_sum_from);
$filter_sum_to = intval($filter_sum_to);

if ($filter_payment_id_from > 0 && $filter_payment_id_to > 0)
	$arFilter['><ID'] = array($filter_payment_id_from, $filter_payment_id_to);
if ($filter_order_id_from > 0 && $filter_order_id_to > 0)
	$arFilter['><ORDER_ID'] = array($filter_order_id_from, $filter_order_id_to);
if (strlen($filter_order_paid) > 0 && $filter_order_paid != 'NOT_REF')
	$arFilter['PAID'] = $filter_order_paid;
if (strlen($filter_site_id) > 0 && $filter_site_id != 'NOT_REF')
	$arFilter['ORDER.LID'] = $filter_site_id;
if (is_array($filter_pay_system_id) && count($filter_pay_system_id) > 0 && $filter_pay_system_id[0] != 'NOT_REF')
	$arFilter['PAY_SYSTEM_ID'] = $filter_pay_system_id;
if (strlen($filter_company_id) > 0 && $filter_company_id != 'NOT_REF')
	$arFilter['COMPANY_ID'] = $filter_company_id;
if (strlen($filter_account_num) > 0)
	$arFilter['ORDER.ACCOUNT_NUMBER'] = $filter_account_num;
if ($filter_sum_from > 0 && $filter_sum_to > 0)
	$arFilter['><SUM'] = array($filter_sum_from, $filter_sum_to);
if (strlen($filter_currency) > 0 && $filter_currency != 'NOT_REF')
	$arFilter['CURRENCY'] = $filter_currency;
if (strlen($filter_pay_voucher_num) > 0)
	$arFilter['PAY_VOUCHER_NUM'] = $filter_pay_voucher_num;
if (strlen($filter_user_login)>0)
	$arFilter["ORDER.USER.LOGIN"] = trim($filter_user_login);
if (strlen($filter_user_email)>0)
	$arFilter["ORDER.USER.EMAIL"] = trim($filter_user_email);
if (IntVal($filter_user_id)>0)
	$arFilter["ORDER.USER_ID"] = IntVal($filter_user_id);
if(strlen($filter_date_paid_from)>0) $arFilter[">=DATE_PAID"] = trim($filter_date_paid_from);
if(strlen($filter_date_paid_to)>0)
{
	if($arDate = ParseDateTime($filter_date_paid_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(StrLen($filter_date_paid_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_paid_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_PAID"] = $filter_date_paid_to;
	}
	else
	{
		$filter_date_paid_to = "";
	}
}

$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
$allowedStatusesUpdate = \Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

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
	$statusList = array();
	if (!empty($allowedStatusesView) && is_array($allowedStatusesView))
	{
		$statusList = $allowedStatusesView;
	}
	if (!empty($allowedStatusesUpdate) && is_array($allowedStatusesUpdate))
	{
		$statusList = array_merge($statusList, $allowedStatusesUpdate);
	}
	$arFilter["=ORDER.STATUS_ID"] = array_unique($statusList);
}

if (($ids = $lAdmin->GroupAction()) && !$bReadOnly)
{
	$payments = array();

	$select = array(
		'ID', 'ORDER_ID'
	);

	$params = array(
		'select' => $select,
		'filter' => array_merge($arFilter, array('ID' => $ids)),
		'order'  => array($by => $order),
		'limit'  => 1000,
		'runtime' => $runtimeFields
	);

	$dbResultList = PaymentTable::getList($params);

	$payments = $dbResultList->fetchAll();
	foreach ($payments as $item)
	{
		if ($item['ID'] <= 0 || $item['ORDER_ID'] <= 0)
			continue;

		/** @var \Bitrix\Sale\Order $currentOrder */
		$currentOrder = Order::load($item['ORDER_ID']);

		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $currentOrder->getPaymentCollection();

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $paymentCollection->getItemById($item['ID']);
		if (!$payment)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$result = $payment->delete();
				if ($result->isSuccess())
					$result = $currentOrder->save();

				if (!$result->isSuccess())
					$lAdmin->AddGroupError(implode('\n', $result->getErrorMessages()));

				break;
			case "paid":
			case "paid_n":
				@set_time_limit(0);
				$paid = $_REQUEST['action'] === 'paid' ? 'Y' : 'N';
				$result = $payment->setPaid($paid);
				if ($result->isSuccess())
					$result = $currentOrder->save();

				if (!$result->isSuccess())
					$lAdmin->AddGroupError(implode('\n', $result->getErrorMessages()));

				break;
		}
	}
}

$headers = array(
	array(
		"id"        => "DATE_PAID",
		"content"   => GetMessage("SALE_ORDER_DATE_PAID"),
		"sort"      => "DATE_PAID",
		"default"   => true
	),
	array(
		"id"        => "ID",
		"content"   => "ID",
		"sort"      => "ID",
		"default"   => true
	),
	array(
		"id"        => "ORDER_ID",
		"content"   => GetMessage("SALE_ORDER_ID"),
		"sort"      => "ORDER_ID",
		"default"   => true
	),
	array(
		"id"        => "ACCOUNT_NUMBER",
		"content"   => GetMessage("SALE_ACCOUNT_NUMBER"),
		"sort"      => "ORDER.ACCOUNT_NUMBER",
		"default"   => false
	),
	array(
		"id"        => "ORDER_USER_NAME",
		"content"   => GetMessage("SALE_ORDER_USER_NAME"),
		"sort"      => "ORDER_USER_NAME",
		"default"   => true
	),
	array(
		"id"        => "PAID",
		"content"   => GetMessage("SALE_ORDER_PAID"),
		"sort"      => "PAID",
		"default"   => true
	),
	array(
		"id"        => "PAY_SYSTEM_NAME",
		"content"   => GetMessage("SALE_ORDER_PAY_SYSTEM_NAME"),
		"sort"      => "PAY_SYSTEM_NAME",
		"default"   => true
	),
	array(
		"id"        => "SUM",
		"content"   => GetMessage("SALE_ORDER_SUM"),
		"sort"      => "SUM",
		"default"   => true
	),
	array(
		"id"        => "COMPANY_BY",
		"content"   => GetMessage("SALE_ORDER_COMPANY_BY"),
		"sort"      => "COMPANY_BY.NAME",
		"default"   => true
	),
	array(
		"id"        => "PAY_VOUCHER_NUM",
		"content"   => GetMessage("SALE_ORDER_PAY_VOUCHER_NUM"),
		"sort"      => "PAY_VOUCHER_NUM",
		"default"   => true
	),
	array(
		"id"        => "RESPONSIBLE_BY",
		"content"   => GetMessage("SALE_ORDER_RESPONSIBLE_BY"),
		"sort"      => "",
		"default"   => true
	),
	array(
		"id"        => "PS_STATUS",
		"content"   => GetMessage("SALE_ORDER_PS_STATUS"),
		"sort"      => "PS_STATUS",
		"default"   => false
	),
	array(
		"id"        => "PS_STATUS_CODE",
		"content"   => GetMessage("SALE_ORDER_PS_STATUS_CODE"),
		"sort"      => "PS_STATUS_CODE",
		"default"   => false
	),
	array(
		"id"        => "PS_STATUS_DESCRIPTION",
		"content"   => GetMessage("SALE_ORDER_PS_STATUS_DESCRIPTION"),
		"sort"      => "PS_STATUS_DESCRIPTION",
		"default"   => false
	),
	array(
		"id"        => "PS_STATUS_MESSAGE",
		"content"   => GetMessage("SALE_ORDER_PS_STATUS_MESSAGE"),
		"sort"      => "PS_STATUS_MESSAGE",
		"default"   => false
	),
	array(
		"id"        => "PS_SUM",
		"content"   => GetMessage("SALE_ORDER_PS_SUM"),
		"sort"      => "PS_SUM",
		"default"   => false
	),
	array(
		"id"        => "PS_CURRENCY",
		"content"   => GetMessage("SALE_ORDER_PS_CURRENCY"),
		"sort"      => "PS_CURRENCY",
		"default"   => false
	),
	array(
		"id"        => "PS_RESPONSE_DATE",
		"content"   => GetMessage("SALE_ORDER_PS_RESPONSE_DATE"),
		"sort"      => "PS_RESPONSE_DATE",
		"default"   => false
	),
	array(
		"id"        => "PAY_VOUCHER_DATE",
		"content"   => GetMessage("SALE_ORDER_PAY_VOUCHER_DATE"),
		"sort"      => "PAY_VOUCHER_DATE",
		"default"   => false
	),
	array(
		"id"        => "DATE_PAY_BEFORE",
		"content"   => GetMessage("SALE_ORDER_DATE_PAY_BEFORE"),
		"sort"      => "DATE_PAY_BEFORE",
		"default"   => false
	),
	array(
		"id"        => "DATE_BILL",
		"content"   => GetMessage("SALE_ORDER_DATE_BILL"),
		"sort"      => "DATE_BILL",
		"default"   => false
	),
	array(
		"id"        => "PAY_SYSTEM_NAME",
		"content"   => GetMessage("SALE_ORDER_PAY_SYSTEM_NAME"),
		"sort"      => "PAY_SYSTEM_NAME",
		"default"   => false
	)
);


$select = array(
	'*',
	'COMPANY_BY_NAME' => 'COMPANY_BY.NAME',
	'RESPONSIBLE_BY_NAME' => 'RESPONSIBLE_BY.NAME',
	'RESPONSIBLE_BY_LAST_NAME' => 'RESPONSIBLE_BY.LAST_NAME',
	'ORDER_ACCOUNT_NUMBER' => 'ORDER.ACCOUNT_NUMBER',
	'ORDER_USER_LOGIN' => 'ORDER.USER.LOGIN',
	'ORDER_USER_NAME' => 'ORDER.USER.NAME',
	'ORDER_USER_LAST_NAME' => 'ORDER.USER.LAST_NAME',
	'ORDER_USER_ID' => 'ORDER.USER_ID',
	'ORDER_RESPONSIBLE_ID' => 'ORDER.RESPONSIBLE_ID',
);

$params = array(
	'select' => $select,
	'filter' => $arFilter,
	'order'  => array($by => $order),
	'runtime' => $runtimeFields
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
	$countQuery = new \Bitrix\Main\Entity\Query(PaymentTable::getEntity());
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

$dbResultList = new CAdminResult(PaymentTable::getList($params), $tableId);

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

$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

while ($payment = $dbResultList->Fetch())
{

	if ($payment['ORDER_USER_NAME'] || $payment['ORDER_USER_LAST_NAME'])
		$userName = htmlspecialcharsbx($payment['ORDER_USER_NAME'])." ".htmlspecialcharsbx($payment['ORDER_USER_LAST_NAME']);
	else
		$userName = htmlspecialcharsbx($payment['ORDER_USER_LOGIN']);

	$row =& $lAdmin->AddRow($payment['ID'], $payment);

	$row->AddField("ID", "<a href=\"sale_order_payment_edit.php?order_id=".$payment['ORDER_ID']."&payment_id=".$payment['ID']."&lang=".$lang.GetFilterParams("filter_")."\">".$payment['ID']."</a>");
	$row->AddField("ORDER_ID", "<a href=\"sale_order_edit.php?ID=".$payment['ORDER_ID']."&lang=".$lang.GetFilterParams("filter_")."\">".$payment['ORDER_ID']."</a>");
	$row->AddField("ACCOUNT_NUMBER", "<a href=\"sale_order_edit.php?ID=".$payment['ORDER_ID']."&lang=".$lang.GetFilterParams("filter_")."\">".htmlspecialcharsbx($payment['ORDER_ACCOUNT_NUMBER'])."</a>");
	$row->AddField("SUM", \CCurrencyLang::CurrencyFormat($payment['SUM'], $payment['CURRENCY']));
	$row->AddField("PAID", ($payment['PAID'] == 'Y') ? GetMessage("PAYMENT_ORDER_PAID_YES"): GetMessage("PAYMENT_ORDER_PAID_NO"));
	$row->AddField("PAY_SYSTEM_NAME", "<a href='sale_pay_system_edit.php?ID=".$payment['PAY_SYSTEM_ID']."&lang=".$lang."'>".htmlspecialcharsbx($payment['PAY_SYSTEM_NAME'])."</a>");
	$row->AddField("COMPANY_BY", "<a href='sale_pay_system_edit.php?ID=".$payment['COMPANY_ID']."&lang=".$lang."'>".htmlspecialcharsbx($payment['COMPANY_BY_NAME'])."</a>");
	$row->AddField("ORDER_USER_NAME", "<a href='/bitrix/admin/user_edit.php?ID=".$payment['ORDER_USER_ID']."&lang=".$lang."'>".$userName."</a>");
	$row->AddField("RESPONSIBLE_BY", "<a href=\"user_edit.php?ID=".$payment['RESPONSIBLE_ID']."\">".htmlspecialcharsbx($payment['RESPONSIBLE_BY_NAME'])." ".htmlspecialcharsbx($payment['RESPONSIBLE_BY_LAST_NAME'])."</a>");
	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("EDIT_PAYMENT_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_order_payment_edit.php?order_id=".$payment['ORDER_ID']."&payment_id=".$payment['ID']."&lang=".$lang.GetFilterParams("filter_").""), "DEFAULT"=>true);

	if(!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("DELETE_PAYMENT_ALT"), "ACTION"=>"if(confirm('".GetMessageJS('DELETE_PAYMENT_CONFIRM')."')) ".$lAdmin->ActionDoGroup($payment['ID'], "delete", "order_id=".$payment['ORDER_ID']));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"paid" => GetMessage("SALE_ORDER_PAYMENT_PAID"),
		"paid_n" => GetMessage("SALE_ORDER_PAYMENT_PAID_N"),
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

$APPLICATION->SetTitle(GetMessage("PAYMENT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?=$curPage?>?">
<?
$filter = array(
	"filter_order_id" => GetMessage("PAYMENT_ORDER_ID"),
	"filter_order_paid" => GetMessage("PAYMENT_ORDER_PAID"),
	"filter_date_paid" => GetMessage("PAYMENT_DATE_PAID"),
	"filter_site_id" => GetMessage("PAYMENT_SITE_ID"),
	"filter_pay_system_id" => GetMessage("PAYMENT_PAY_SYSTEM_ID"),
	"filter_account_num" => GetMessage("PAYMENT_ACCOUNT_NUM"),
	"filter_sum" => GetMessage("PAYMENT_PRICE"),
	"filter_currency" => GetMessage("PAYMENT_CURRENCY"),
	"filter_pay_voucher_num" => GetMessage("PAYMENT_VOUCHER_NUM"),
	"filter_user_id" => GetMessage("SALE_PAYMENT_F_USER_ID"),
	"filter_user_login" => GetMessage("SALE_PAYMENT_F_USER_LOGIN"),
	"filter_user_email" => GetMessage("SALE_PAYMENT_F_USER_EMAIL")
);

$oFilter = new CAdminFilter(
	$tableId."_filter",
	$filter
);

$oFilter->Begin();
?>
<tr>
	<td>ID:</td>
	<td>
		<script type="text/javascript">
			function changeFilterPaymentIdFrom()
			{
				if (document.find_form.filter_payment_id_to.value.length<=0)
					document.find_form.filter_payment_id_to.value = document.find_form.filter_payment_id_from.value;
			}
		</script>
		<?=GetMessage("PAYMENT_ORDER_ID_FROM");?>
		<input type="text" name="filter_payment_id_from" onchange="changeFilterPaymentIdFrom()" value="<?=(intval($filter_payment_id_from)>0)?intval($filter_payment_id_from):""?>" size="10">
		<?=GetMessage("PAYMENT_ORDER_ID_TO");?>
		<input type="text" name="filter_payment_id_to" value="<?=(intval($filter_payment_id_to)>0)?intval($filter_payment_id_to):""?>" size="10">
	</td>
</tr>
<tr>
	<td><?=GetMessage("PAYMENT_ORDER_ID");?>:</td>
	<td>
		<script type="text/javascript">
			function changeFilterOrderIdFrom()
			{
				if (document.find_form.filter_order_id_to.value.length<=0)
					document.find_form.filter_order_id_to.value = document.find_form.filter_order_id_from.value;
			}
		</script>
		<?=GetMessage("PAYMENT_ORDER_ID_FROM");?>
		<input type="text" name="filter_order_id_from" onchange="changeFilterOrderIdFrom()" value="<?=(intval($filter_order_id_from)>0)?intval($filter_order_id_from):""?>" size="10">
		<?=GetMessage("PAYMENT_ORDER_ID_TO");?>
		<input type="text" name="filter_order_id_to" value="<?=(intval($filter_order_id_to)>0)?intval($filter_order_id_to):""?>" size="10">
	</td>
</tr>
<tr>
	<td><?echo GetMessage("PAYMENT_ORDER_PAID")?>:</td>
	<td>
		<select name="filter_order_paid">
			<option value="NOT_REF">(<?=GetMessage("PAYMENT_ORDER_PAID_ALL");?>)</option>
			<option value="Y"<?if ($filter_order_paid=="Y") echo " selected"?>><?=GetMessage("PAYMENT_ORDER_PAID_YES");?></option>
			<option value="N"<?if ($filter_order_paid=="N") echo " selected"?>><?=GetMessage("PAYMENT_ORDER_PAID_NO");?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?=GetMessage("PAYMENT_DATE_PAID");?>:</td>
	<td>
		<?=CalendarPeriod("filter_date_paid_from", htmlspecialcharsbx($filter_date_paid_from), "filter_date_paid_to", htmlspecialcharsbx($filter_date_paid_to), "find_form", "Y")?>
	</td>
</tr>
<?
	$res = CSite::GetList($bySite="sort", $orderSite="asc");
	$siteInfo = array();
	while ($site = $res->Fetch())
		$siteInfo[$site['ID']] = $site['SITE_NAME'];
?>
<tr>
	<td><?=GetMessage("PAYMENT_SITE_ID");?>:</td>
	<td>
		<select name="filter_site_id">
			<option value="NOT_REF">(<?=GetMessage("PAYMENT_ORDER_PAID_ALL");?>)</option>
			<?
			foreach ($siteInfo as $id => $siteName)
				echo '<option value="'.$id.'">'.htmlspecialcharsbx($siteName).'</option>';
			?>
		</select>
	</td>
</tr>
<?
	$params = array(
		'select' => array('ID', 'NAME'),
		'filter' => array('ACTIVE' => 'Y'),
		'order' => array("SORT"=>"ASC", "NAME"=>"ASC")
	);
	$res = \Bitrix\Sale\PaySystem\Manager::getList($params);
	$paySystems = $res->fetchAll();
?>
<tr>
	<td><?=GetMessage("PAYMENT_PAY_SYSTEM_ID");?>:</td>
	<td>
		<select multiple name="filter_pay_system_id[]">
			<option value="NOT_REF">(<?=GetMessage("PAYMENT_ORDER_PAID_ALL");?>)</option>
			<?
			$ptRes = \Bitrix\Sale\Internals\PersonTypeTable::getList(array(
				'order' => array("SORT"=>"ASC", "NAME"=>"ASC")
			));

			$personTypes = array();
			while ($personType = $ptRes->fetch())
				$personTypes[$personType['ID']] = $personType;

			foreach ($paySystems as $paySystem):
				$dbRestRes = \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::getList(array(
					'select' => array('PARAMS'),
					'filter' => array(
						'=CLASS_NAME' => '\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType',
						'SERVICE_ID' => $paySystem['ID']
					)
				));

				$ptParams = $dbRestRes->fetch();
				$personTypeString = '';
				if ($ptParams['PARAMS']['PERSON_TYPE_ID'])
				{
					$psPt = array();
					foreach ($ptParams['PARAMS']['PERSON_TYPE_ID'] as $id)
						$psPt[] = ((strlen($personTypes[$id]['NAME']) > 15) ? substr($personTypes[$id]['NAME'], 0, 6)."...".substr($personTypes[$id]['NAME'], -7) : $personTypes[$id]['NAME'])."/".$personTypes[$id]["LID"]."";
					if ($psPt)
						$personTypeString = ' ('.join(', ', $psPt).')';
				}
				?><option title="<?echo htmlspecialcharsbx($paySystem["NAME"].$personTypeString);?>" value="<?echo htmlspecialcharsbx($paySystem["ID"])?>"<?if(is_array($filter_pay_system_id) && in_array($paySystem["ID"], $filter_pay_system_id)) echo " selected"?>>[<?echo htmlspecialcharsbx($paySystem["ID"]) ?>] <?echo htmlspecialcharsbx($paySystem["NAME"].$personTypeString);?></option><?
			endforeach;
			?>
		</select>
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
	<td><?=GetMessage("PAYMENT_ACCOUNT_NUM");?>:</td>
	<td>
		<input type="text" name="filter_account_num" value="<?=htmlspecialcharsbx($filter_account_num)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("PAYMENT_PRICE");?>:</td>
	<td>
		<?echo GetMessage("PAYMENT_PRICE_FROM");?>
		<input type="text" name="filter_sum_from" value="<?=($filter_sum_from!=0) ? htmlspecialcharsbx($filter_sum_from) : '';?>" size="3">

		<?echo GetMessage("PAYMENT_PRICE_TO");?>
		<input type="text" name="filter_sum_to" value="<?=($filter_sum_to!=0) ? htmlspecialcharsbx($filter_sum_to) : '';?>" size="3">
	</td>
</tr>
<tr>
	<td><?=GetMessage("PAYMENT_CURRENCY");?>:</td>
	<td>
		<?echo CCurrency::SelectBox("filter_currency", htmlspecialcharsbx($filter_currency), GetMessage("PAYMENT_CURRENCY_ALL"), false, "", ""); ?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("PAYMENT_VOUCHER_NUM");?>:</td>
	<td>
		<input type="text" name="filter_pay_voucher_num" value="<?=htmlspecialcharsbx($filter_pay_voucher_num);?>">
	</td>
</tr>
<tr>
	<td><?echo \Bitrix\Main\Localization\Loc::getMessage("SALE_PAYMENT_F_USER_ID");?>:</td>
	<td>
		<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
	</td>
</tr>
<tr>
	<td><?echo \Bitrix\Main\Localization\Loc::getMessage("SALE_PAYMENT_F_USER_LOGIN");?>:</td>
	<td>
		<input type="text" name="filter_user_login" value="<?echo htmlspecialcharsbx($filter_user_login)?>" size="40">
	</td>
</tr>
<tr>
	<td><?echo \Bitrix\Main\Localization\Loc::getMessage("SALE_PAYMENT_F_USER_EMAIL");?>:</td>
	<td>
		<input type="text" name="filter_user_email" value="<?echo htmlspecialcharsbx($filter_user_email)?>" size="40">
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