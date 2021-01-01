<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Sale;
use Bitrix\Main;

$publicMode = $adminPage->publicMode || $adminSidePanelHelper->isPublicSidePanel();
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/cashbox.js");
Main\Page\Asset::getInstance()->addJs("/bitrix/js/crm/cashbox/script.js");
Main\Page\Asset::getInstance()->addJs("/bitrix/js/crm/common.js");

\Bitrix\Main\UI\Extension::load(['sidepanel']);
\Bitrix\Main\Loader::includeModule('sale');

$tableId = "tbl_sale_cashbox_correction";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminUiSorting($tableId, "ID", "asc");
$lAdmin = new CAdminUiList($tableId, $oSort);

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

$filterFields = [
	[
		"id" => "PAID",
		"name" => GetMessage("SALE_F_CORRECTION_PAID"),
		"type" => "checkbox",
		"default" => true
	],
	[
		"id" => "DATE_BILL",
		"name" => GetMessage("SALE_F_CORRECTION_DATE_BILL"),
		"type" => "date",
	],
	[
		"id" => "ORDER_ID",
		"name" => GetMessage("SALE_F_CORRECTION_ORDER_ID"),
		"type" => "number",
		"filterable" => "",
		"quickSearch" => ""
	],
	[
		"id" => "CHECK_PRINTED",
		"name" => GetMessage("SALE_F_CORRECTION_CHECK_PRINTED"),
		"type" => "checkbox",
		"filterable" => "",
		"quickSearch" => "",
		"default" => true
	],
];

$filter = array();

$lAdmin->AddFilter($filterFields, $filter);

if (isset($filter['CHECK_PRINTED']))
{
	if ($filter['CHECK_PRINTED'] === 'Y')
	{
		$filter['=PAYMENT_CHECK_PRINTED.STATUS'] = 'Y';
	}
	else
	{
		$filter[] = [
			'LOGIC' => 'OR',
			'=PAYMENT_CHECK_PRINTED.STATUS' => null,
			'@PAYMENT_CHECK_PRINTED.STATUS' => ['N', 'P', 'E']
		];
	}

	unset($filter['CHECK_PRINTED']);
}

$params = [
	'select' => [
		'ID', 'ORDER_ID', 'SUM', 'CURRENCY', 'PAY_SYSTEM_NAME',
		'PAID', 'DATE_BILL', 'CHECK_PRINTED' => 'PAYMENT_CHECK_PRINTED.STATUS'
	],
	'filter' => $filter,
	'runtime' => [
		new Main\ORM\Fields\Relations\Reference(
			'PAYMENT_CHECK_PRINTED',
			\Bitrix\Sale\Cashbox\Internals\CashboxCheckTable::getEntity(),
			['=ref.PAYMENT_ID' => 'this.ID',],
			['join_type' => 'LEFT',]
		)
	]
];

global $by, $order;
$by = isset($by) ? $by : "ID";
$order = isset($order) ? $order : "ASC";

$params['order'] = array($by => $order);

$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
$paymentClass = $registry->getPaymentClassName();

$dbResultList = new CAdminUiResult($paymentClass::getList($params), $tableId);

$dbResultList->NavStart();

$headers = [
	[
		"id"        => "ID",
		"content"   => GetMessage("SALE_CHECK_CORRECTION_PAYMENT_ID"),
		"sort"      => "ID",
		"default"   => true
	],
	[
		"id"        => "ORDER_ID",
		"content"   => GetMessage("SALE_CHECK_CORRECTION_ORDER_ID"),
		"sort"      => "ORDER_ID",
		"default"   => true
	],
	[
		"id"        => "PAID",
		"content"   => GetMessage("SALE_CHECK_CORRECTION_ORDER_PAID"),
		"sort"      => "PAID",
		"default"   => true
	],
	[
		"id"        => "PAY_SYSTEM_NAME",
		"content"   => GetMessage("SALE_CHECK_CORRECTION_PAY_SYSTEM_NAME"),
		"sort"      => "PAY_SYSTEM_NAME",
		"default"   => true
	],
	[
		"id"        => "SUM",
		"content"   => GetMessage("SALE_CHECK_CORRECTION_ORDER_SUM"),
		"sort"      => "SUM",
		"default"   => true
	],
	[
		"id"        => "DATE_BILL",
		"content"   => GetMessage("SALE_CHECK_CORRECTION_ORDER_DATE_BILL"),
		"sort"      => "DATE_BILL",
		"default"   => false
	],
];

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_cashbox_check_correction.php"));
$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

$tempResult = clone($dbResultList);

while ($payment = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($payment['ID'], $payment, false, GetMessage("SALE_EDIT_DESCR"));

	$urlPayment = '/shop/orders/payment/details/'.$payment['ID'].'/';
	$row->AddField('ID', '<a href="'.$urlPayment.'">'.$payment['ID'].'</a>');

	$urlOrder = '/shop/orders/details/'.$payment['ORDER_ID'].'/';
	$row->AddField('ORDER_ID', '<a href="'.$urlOrder.'">'.$payment['ORDER_ID'].'</a>');

	$row->AddField("PAID", GetMessage('SALE_CHECK_CORRECTION_ORDER_PAID_'.$payment['PAID']));

	$row->AddField('SUM', \CCurrencyLang::CurrencyFormat($payment['SUM'], $payment['CURRENCY']));

	$arActions = [];

	if ($payment['CHECK_PRINTED'] !== 'Y')
	{
		$componentPath = \CComponentEngine::makeComponentPath('bitrix:crm.check.correction.details');
		$componentPath = getLocalPath('components'.$componentPath.'/slider.php');
		$url = $componentPath.'?payment_id[]='.$payment['ID'];

		$arActions[] = [
			"ICON" => "create_correction",
			"TEXT" => GetMessage("SALE_CASHBOX_CORRECTION_ADD"),
			"ACTION" => 'BX.Crm.Page.openSlider("'.$url.'", { width: 500, cacheable : false });'
		];
	}

	if ($arActions)
	{
		$row->AddActions($arActions);
	}
}

if ($saleModulePermissions == "W")
{
	if ($publicMode
		&& !$adminSidePanelHelper->isPublicSidePanel()
		&& \Bitrix\Main\Loader::includeModule('crm')
	)
	{
		$lAdmin->AddGroupActionTable([
			[
				'action' => 'addCorrectionCheck()',
				'value' => 'group_add',
				'name' => GetMessage('SALE_CASHBOX_CORRECTION_GROUP_ADD'),
			]
		]);

		$addButton = [
			'TEXT' => GetMessage('SALE_CASHBOX_CORRECTION_ADD'),
			'TITLE' => GetMessage('SALE_CASHBOX_CORRECTION_ADD'),
			'ICON' => 'btn-new check-correction-add',
		];

		$APPLICATION->IncludeComponent(
			'bitrix:crm.interface.toolbar',
			'',
			[
				'TOOLBAR_ID' => $tableId.'_toolbar',
				'BUTTONS' => [$addButton]
			],
			null,
			['HIDE_ICONS' => 'Y']
		);
	}
	else
	{
		$aContext = [];

		$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_cashbox_correction.php"));
		$lAdmin->AddAdminContextMenu($aContext);
	}
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SALE_CASHBOX_CORRECTION_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$lAdmin->DisplayFilter($filterFields);
	$lAdmin->DisplayList();
}


$jsData = [
	'ADD_CHECK_CORRECTION_URL' => getLocalPath('components'.\CComponentEngine::makeComponentPath('bitrix:crm.check.correction.details').'/slider.php')
];

?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.Crm.CheckCorrection.create(<?=$tableId?>, <?=CUtil::PhpToJSObject($jsData)?>);

			BX.addCustomEvent('SidePanel.Slider:onMessage', function(event) {
				if (event.getEventId() === 'CRM:CheckCorrection:onSave')
				{
					BX.loadExt('ui.notification').then(function() {
						BX.UI.Notification.Center.notify({
							content: "<?=GetMessageJS('SALE_CASHBOX_CORRECTION_SAVE_CHECK')?>"
						});
					});
				}
			});
		}
	);

	function addCorrectionCheck()
	{
		var oForm = document.form_<?=$tableId?>;

		var url = '<?=getLocalPath('components'.\CComponentEngine::makeComponentPath('bitrix:crm.check.correction.details').'/slider.php');?>?';

		for (var i = 0; i < oForm.elements.length; i++)
		{
			if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
				&& oForm.elements[i].type.toUpperCase() == "CHECKBOX"
				&& oForm.elements[i].name.toUpperCase() == "ID[]"
				&& oForm.elements[i].checked == true)
			{
				url += '&payment_id[]=' + oForm.elements[i].value;
			}
		}

		BX.Crm.Page.openSlider(url, { width: 500 , cacheable: false});
	}
</script>

<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>