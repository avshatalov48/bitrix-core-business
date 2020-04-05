<?
/** @global CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);

$saleRights = $APPLICATION->GetGroupRight('sale');
if ($saleRights < 'W')
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('SALE_BASKET_DISCOUNT_CONVERT_ERRORS_RIGHTS'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if (!check_bitrix_sessid())
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('SALE_BASKET_DISCOUNT_CONVERT_ERRORS_INCORRECT_SESSION'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if (!Loader::includeModule('sale'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('SALE_BASKET_DISCOUNT_CONVERT_ERRORS_MODULE_SALE_ABSENT'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if (!Loader::includeModule('catalog'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('SALE_BASKET_DISCOUNT_CONVERT_ERRORS_MODULE_CATALOG_ABSENT'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();

if (
	$request->getRequestMethod() == 'GET'
	&& ($request['operation'] == 'Y' || $request['getCount'] == 'Y' || $request['clearTags'] == 'Y')
)
{
	CUtil::JSPostUnescape();

	$filter = array();
	$filter = CSaleBasketDiscountConvert::checkFilter($request);

	$result = array();
	if ($request['operation'] == 'Y')
	{
		$params = array(
			'sessID' => $request['ajaxSessionID'],
			'maxExecutionTime' => $request['maxExecutionTime'],
			'maxOperationCounter' => $request['maxOperationCounter'],
			'counter' => $request['counter'],
			'operationCounter' => $request['operationCounter'],
			'lastID' => $request['lastID']
		);

		$basketDiscount = new CSaleBasketDiscountConvert(
			$params['sessID'],
			$params['maxExecutionTime'],
			$params['maxOperationCounter']
		);
		$basketDiscount->initStep($params['counter'], $params['operationCounter'], $params['lastID']);
		$basketDiscount->setFilter($filter);
		$basketDiscount->run();
		$result = $basketDiscount->saveStep();
	}

	if ($request['getCount'] == 'Y')
	{
		$result = array(
			'counter' => CSaleBasketDiscountConvert::getFilterCounter($filter)
		);
	}

	if ($request['clearTags'] == 'Y')
	{
		$adminNotifyIterator = CAdminNotify::GetList(array(), array('MODULE_ID' => 'sale', 'TAG' => 'BASKET_DISCOUNT_CONVERTED'));
		if ($adminNotifyIterator)
		{
			if ($adminNotify = $adminNotifyIterator->Fetch())
				CAdminNotify::Delete($adminNotify['ID']);
			unset($adminNotify);
		}
		unset($adminNotifyIterator);
	}
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($result, false, true);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage('SALE_BASKET_DISCOUNT_CONVERT_PAGE_TITLE'));

	$ordersCounter = CSaleBasketDiscountConvert::getAllCounter();
	$oneStepTime = CSaleBasketDiscountConvert::getDefaultExecutionTime();

	if ($ordersCounter == 0)
	{
		$adminNotifyIterator = CAdminNotify::GetList(array(), array('MODULE_ID' => 'sale', 'TAG' => 'BASKET_DISCOUNT_CONVERTED'));
		if ($adminNotifyIterator)
		{
			if ($adminNotify = $adminNotifyIterator->Fetch())
				CAdminNotify::Delete($adminNotify['ID']);
			unset($adminNotify);
		}
		unset($adminNotifyIterator);
	}

	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

	$tabList = array(
		array('DIV' => 'basketDiscountTab01', 'TAB' => Loc::getMessage('SALE_BASKET_DISCOUNT_TAB'), 'ICON' => 'sale', 'TITLE' => Loc::getMessage('SALE_BASKET_DISCOUNT_TAB_TITLE'))
	);
	$tabControl = new CAdminTabControl('basketDiscountConvert', $tabList, true, true);
	CJSCore::Init(array('date'));
	Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/step_operations.js');
	Main\Page\Asset::getInstance()->addJs('/bitrix/js/sale/admin/step_operations.js');

	$startDate = new Main\Type\DateTime();
	$startDate->add('-3M');
	$startDate->setTime(0,0,0);

	?><div id="basket_discount_empty_orders" style="display: <?=($ordersCounter == 0 ? 'block' : 'none'); ?>;"><?
	ShowNote(Loc::getMessage('SALE_BASKET_DISCOUNT_MESS_ORDERS_ABSENT'));
	?></div>
	<div id="basket_discount_result_div" style="margin:0; display: none;"></div>
	<div id="basket_discount_error_div" style="margin:0; display: none;">
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_ERRORS_TITLE'); ?></div>
				<div id="basket_discount_error_cont"></div>
				<div class="adm-info-message-icon"></div>
			</div>
		</div>
	</div>
	<form name="basket_discount_form" action="<? echo $APPLICATION->GetCurPage(); ?>" method="GET"><?
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		?><tr>
			<td width="40%"><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_MAX_EXECUTION_TIME')?></td>
			<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo $oneStepTime; ?>"></td>
		</tr>
		<tr class="heading">
			<td colspan="2"><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER'); ?></td>
		</tr>
		<tr>
			<td width="40%"><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_TYPE') ?></td>
			<td>
				<select name="filter_type" id="filter_type">
					<option value="all" selected><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_TYPE_ALL'); ?></option>
					<option value="id"><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_TYPE_ID'); ?></option>
					<option value="date" selected><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_TYPE_DATE'); ?></option>
				</select>
			</td>
		</tr>
		<tr id="tr_filter_id" style="display: none;">
			<td width="40%"><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_ORDER_ID_RANGE') ?></td>
			<td><?
				echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_RANGE_FROM');
				?>&nbsp;<input type="text" name="order_id_from" id="order_id_from" size="5">&nbsp;<?
				echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_RANGE_TO');
				?>&nbsp;<input type="text" name="order_id_to" id="order_id_to" size="5">
			</td>
		</tr>
		<tr id="tr_filter_date" style="display: table-row;">
			<td width="40%"><? echo Loc::getMessage('SALE_BASKET_DISCOUNT_FILTER_DATE_RANGE') ?></td>
			<td><?
				$calendar = new CAdminCalendar;
				echo $calendar->CalendarPeriodCustom(
					'order_date_from', 'order_date_to',
					$startDate->toString(), '',
					false, 19, true
				);
			?></td>
		</tr>
		<?
		$tabControl->Buttons();
		?>
		<input type="button" id="start_button" value="<? echo Loc::getMessage('SALE_BASKET_DISCOUNT_UPDATE_BTN')?>" disabled>
		<input type="button" id="stop_button" value="<? echo Loc::getMessage('SALE_BASKET_DISCOUNT_STOP_BTN')?>" disabled>
		<?
		$tabControl->End();
	?></form><?
	$jsParams = array(
		'url' => $APPLICATION->GetCurPage(),
		'options' => array(
			'ajaxSessionID' => 'basketDiscountConv',
			'maxExecutionTime' => $oneStepTime,
			'maxOperationCounter' => 10,
			'counter' => $ordersCounter
		),
		'visual' => array(
			'startBtnID' => 'start_button',
			'stopBtnID' => 'stop_button',
			'resultContID' => 'basket_discount_result_div',
			'errorContID' => 'basket_discount_error_cont',
			'errorDivID' => 'basket_discount_error_div',
			'timeFieldID' => 'max_execution_time',
			'emptyOrdersId' => 'basket_discount_empty_orders'
		),
		'ajaxParams' => array(
			'operation' => 'Y'
		),
		'filter' => array(
			'filter_type',
			'order_id_from',
			'order_id_to',
			'order_date_from_calendar_from',
			'order_date_to_calendar_to'
		)
	);
	?>
<script type="text/javascript">
	var jsBasketDiscountConverter = new BX.Sale.Admin.StepOperations.StepOperationsFilter(<? echo CUtil::PhpToJSObject($jsParams, false, true); ?>);
	BX.ready(function(){
		var filterType = BX('filter_type'),
			filterId = BX('tr_filter_id'),
			filterDate = BX('tr_filter_date');
		if (!!filterType)
		{
			BX.bind(filterType, 'change', function(){
				BX.style(filterId, 'display', (filterType.value == 'id' ? 'table-row' : 'none'));
				BX.style(filterDate, 'display', (filterType.value == 'date' ? 'table-row' : 'none'));
			});
		}
	});
</script>
	<?
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
}