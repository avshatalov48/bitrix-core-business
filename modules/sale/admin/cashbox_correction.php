<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
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

\Bitrix\Main\UI\Extension::load(['sidepanel', 'ui.stepprocessing']);
\Bitrix\Main\Loader::includeModule('sale');

$tableId = Sale\Helpers\Admin\Correction::TABLE_ID;
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

$filterFields = Sale\Helpers\Admin\Correction::getFilterFields();

$filter = array();

$lAdmin->AddFilter($filterFields, $filter);

$filter = Sale\Helpers\Admin\Correction::prepareFilter($filter);

$params = Sale\Helpers\Admin\Correction::getPaymentSelectParams($filter);

global $by, $order;
$by = isset($by) ? $by : "ID";
$order = isset($order) ? $order : "ASC";

$params['order'] = array($by => $order);

$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
$paymentClass = $registry->getPaymentClassName();

$dbResultList = new CAdminUiResult($paymentClass::getList($params), $tableId);

$dbResultList->NavStart();

$headers = Sale\Helpers\Admin\Correction::getTableHeaders();

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
		$exportCsvParams = [
			'id' => 'EXPORT_' . CCrmOwnerType::CheckCorrectionName . '_CSV',
			'controller' => 'bitrix:crm.api.export',
			'queue' => [
				[
					'action' => 'dispatcher',
				],
			],
			'params' => [
				'SITE_ID' => SITE_ID,
				'EXPORT_TYPE' => 'csv',
				'ENTITY_TYPE' => CCrmOwnerType::CheckCorrectionName,
				'COMPONENT_NAME' => 'bitrix:crm.check.correction.export',
				'signedParameters' => \Bitrix\Main\Component\ParameterSigner::signParameters(
					'bitrix:crm.check.correction.export',
					[]
				),
			],
			'messages' => array(
				'DialogTitle' => Loc::getMessage('CORRECTION_CHECK_EXPORT_CSV_TITLE'),
				'DialogSummary' => Loc::getMessage('CORRECTION_CHECK_STEXPORT_SUMMARY'),
			),
			'dialogMaxWidth' => 650,
		];

		$exportExcelParams = $exportCsvParams;
		$exportExcelParams['id'] = 'EXPORT_' . CCrmOwnerType::CheckCorrectionName . '_EXCEL';
		$exportExcelParams['params']['EXPORT_TYPE'] = 'excel';
		$exportExcelParams['messages']['DialogTitle'] = Loc::getMessage('CORRECTION_CHECK_EXPORT_EXCEL_TITLE');

		$lAdmin->AddGroupActionTable([
			[
				'action' => 'addCorrectionCheck()',
				'value' => 'group_add',
				'name' => Loc::getMessage('SALE_CASHBOX_CORRECTION_GROUP_ADD'),
			],
			'for_all' => true,
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

	$menu = [
		[
			'TITLE' => Loc::getMessage('SALE_CASHBOX_CORRECTION_GROUP_EXPORT_TO_EXCEL'),
			'TEXT' => Loc::getMessage('SALE_CASHBOX_CORRECTION_GROUP_EXPORT_TO_EXCEL'),
			'ONCLICK' => "exportToExcel()",
		],
		[
			'TITLE' => Loc::getMessage('SALE_CASHBOX_CORRECTION_GROUP_EXPORT_TO_CSV'),
			'TEXT' => Loc::getMessage('SALE_CASHBOX_CORRECTION_GROUP_EXPORT_TO_CSV'),
			'ONCLICK' => "exportToCsv()",
		],
	];
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_cashbox_correction.php"));
	$lAdmin->SetContextMenu([], $menu);
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

	function exportToExcel()
	{
		var excelExportParams = <?= Json::encode($exportExcelParams) ?>;
		var excelExportProcess = BX.UI.StepProcessing.ProcessManager.create(excelExportParams);

		excelExportProcess.showDialog();
	}

	function exportToCsv()
	{
		var csvExportParams = <?= Json::encode($exportCsvParams) ?>;
		var csvExportProcess = BX.UI.StepProcessing.ProcessManager.create(csvExportParams);

		csvExportProcess.showDialog();
	}

	function getSelectedIds()
	{
		return BX.Main.gridManager.getInstanceById(<?= CUtil::PhpToJSObject($tableId) ?>).getRows().getSelectedIds();
	}

	function getIsForAll()
	{
		return document.getElementById('actallrows_' + <?= CUtil::PhpToJSObject($tableId) ?>).checked ? 'Y' : 'N';
	}

	function addCorrectionCheck()
	{
		var url = '<?= getLocalPath('components'.\CComponentEngine::makeComponentPath('bitrix:crm.check.correction.details').'/slider.php'); ?>';

		var paymentIds = getSelectedIds();
		var isForAll = getIsForAll();

		var params = {
			payment_id: paymentIds,
			is_for_all: isForAll,
		};

		BX.Crm.Page.openSlider(url, {
			width: 500,
			cacheable: false,
			requestMethod: 'post',
			requestParams: params,
		});
	}
</script>

<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>