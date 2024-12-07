<?php
/** @global CMain $APPLICATION */
const STOP_STATISTICS = true;
const NO_AGENT_CHECK = true;
const PUBLIC_AJAX_MODE = true;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Sale;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

$saleRights = $APPLICATION->GetGroupRight('sale');
if ($saleRights < 'W')
{
	ShowError(Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_RIGHTS'));
	die();
}

if (!check_bitrix_sessid())
{
	ShowError(Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_INCORRECT_SESSION'));
	die();
}

if (!Loader::includeModule('sale'))
{
	ShowError(Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_MODULE_SALE_ABSENT'));
	die();
}

if (!Loader::includeModule('catalog'))
{
	ShowError(Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_MODULE_CATALOG_ABSENT'));
	die();
}

/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();

if (
	$request->getRequestMethod() === 'GET'
	&& $request['operation'] === 'Y'
)
{
	$params = [
		'sessID' => $request['ajaxSessionID'],
		'maxExecutionTime' => $request['maxExecutionTime'],
		'maxOperationCounter' => $request['maxOperationCounter'],
		'counter' => $request['counter'],
		'operationCounter' => $request['operationCounter'],
		'lastID' => $request['lastID'],
	];

	$discountReindex = new CSaleDiscountReindex(
		$params['sessID'],
		$params['maxExecutionTime'],
		$params['maxOperationCounter']
	);
	$discountReindex->initStep($params['counter'], $params['operationCounter'], $params['lastID']);
	$discountReindex->run();
	$result = $discountReindex->saveStep();

	if ($result['finishOperation'])
	{
		$iterator = \CAdminNotify::GetList(
			[],
			[
				'MODULE_ID' => 'sale',
				'TAG' => Sale\Discount::ERROR_ID,
			]
		);
		$notify = $iterator->Fetch();
		unset($iterator);
		if (!empty($notify))
		{
			\CAdminNotify::Delete($notify['ID']);
		}
		unset($notify);
	}

	header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
	echo CUtil::PhpToJSObject($result, false, true);
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_after.php';
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage('SALE_DISCOUNT_REINDEX_PAGE_TITLE'));

	$discountCounter = CSaleDiscountReindex::getAllCounter();
	$oneStepTime = CSaleDiscountReindex::getDefaultExecutionTime();

	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

	$tabList = [
		[
			'DIV' => 'discountReindexTab01',
			'TAB' => Loc::getMessage('SALE_DISCOUNT_REINDEX_TAB'),
			'ICON' => 'sale',
			'TITLE' => Loc::getMessage('SALE_DISCOUNT_REINDEX_TAB_TITLE'),
		],
	];
	$tabControl = new CAdminTabControl('saleDiscountReindex', $tabList, true, true);
	Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/step_operations.js');

	?><div id="discount_reindex_result_div" style="margin:0; display: none;"></div>
	<div id="discount_reindex_error_div" style="margin:0; display: none;">
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><?= Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_TITLE'); ?></div>
				<div id="discount_reindex_error_cont"></div>
				<div class="adm-info-message-icon"></div>
			</div>
		</div>
	</div>
	<form name="discount_reindex_form" action="<?= $APPLICATION->GetCurPage(); ?>" method="GET"><?php
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?><tr>
	<td style="width: 40%;"><?= Loc::getMessage('SALE_DISCOUNT_REINDEX_MAX_EXECUTION_TIME'); ?></td>
	<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?= $oneStepTime; ?>"></td>
	</tr><?php
	$tabControl->Buttons();
	?>
	<input type="button" id="start_button" value="<?= HtmlFilter::encode(Loc::getMessage('SALE_DISCOUNT_REINDEX_UPDATE_BTN')); ?>"<?= ($discountCounter > 0 ? '' : ' disabled'); ?>>
	<input type="button" id="stop_button" value="<?= HtmlFilter::encode(Loc::getMessage('SALE_DISCOUNT_REINDEX_STOP_BTN')); ?>" disabled>
	<?php
	$tabControl->End();
	?></form><?php

	$jsParams = [
		'url' => $APPLICATION->GetCurPage(),
		'options' => [
			'ajaxSessionID' => 'saleDiscountReindex',
			'maxExecutionTime' => $oneStepTime,
			'maxOperationCounter' => 10,
			'counter' => $discountCounter,
		],
		'visual' => [
			'startBtnID' => 'start_button',
			'stopBtnID' => 'stop_button',
			'resultContID' => 'discount_reindex_result_div',
			'errorContID' => 'discount_reindex_error_cont',
			'errorDivID' => 'discount_reindex_error_div',
			'timeFieldID' => 'max_execution_time',
		],
		'ajaxParams' => [
			'operation' => 'Y',
		],
	];
?>
<script>
	var jsStepOperations = new BX.Catalog.StepOperations(<?= CUtil::PhpToJSObject($jsParams, false, true); ?>);
</script>
<?php
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
}
