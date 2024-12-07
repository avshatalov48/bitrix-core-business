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
	ShowError(Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_RIGHTS'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if (!Loader::includeModule('sale'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_MODULE_SALE_ABSENT'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if (!Loader::includeModule('catalog'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_MODULE_CATALOG_ABSENT'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();

if (
	$request->getRequestMethod() == 'GET'
	&& $request['operation'] == 'Y'
	&& check_bitrix_sessid()
)
{
	$params = array(
		'sessID' => $request['ajaxSessionID'],
		'maxExecutionTime' => $request['maxExecutionTime'],
		'maxOperationCounter' => $request['maxOperationCounter'],
		'counter' => $request['counter'],
		'operationCounter' => $request['operationCounter'],
		'lastID' => $request['lastID']
	);

	$discountConvert = new CSaleDiscountConvertExt(
		$params['sessID'],
		$params['maxExecutionTime'],
		$params['maxOperationCounter']
	);
	$discountConvert->initStep($params['counter'], $params['operationCounter'], $params['lastID']);
	$discountConvert->run();
	$result = $discountConvert->saveStep();
	if ($result['finishOperation'])
	{
		$adminNotifyIterator = CAdminNotify::GetList(array(), array('MODULE_ID'=>'sale', 'TAG' => 'SALE_CONVERT_15'));
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
	$APPLICATION->SetTitle(Loc::getMessage('SALE_DISCOUNT_REINDEX_PAGE_TITLE'));

	$discountCounter = CSaleDiscountConvertExt::getAllCounter();
	$oneStepTime = CSaleDiscountConvertExt::getDefaultExecutionTime();

	if ($discountCounter == 0)
	{
		$adminNotifyIterator = CAdminNotify::GetList(array(), array('MODULE_ID' => 'sale', 'TAG' => 'SALE_CONVERT_15'));
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
		array('DIV' => 'discountReindexTab01', 'TAB' => Loc::getMessage('SALE_DISCOUNT_REINDEX_TAB'), 'ICON' => 'sale', 'TITLE' => Loc::getMessage('SALE_DISCOUNT_REINDEX_TAB_TITLE'))
	);
	$tabControl = new CAdminTabControl('saleDiscountReindex', $tabList, true, true);
	Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/step_operations.js');

	if ($discountCounter == 0)
	{
		ShowNote(Loc::getMessage('SALE_DISCOUNT_REINDEX_DISCOUNT_ABSENT'));
	}
	?><div id="discount_reindex_result_div" style="margin:0; display: none;"></div>
	<div id="discount_reindex_error_div" style="margin:0; display: none;">
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><? echo Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_TITLE'); ?></div>
				<div id="discount_reindex_error_cont"></div>
				<div class="adm-info-message-icon"></div>
			</div>
		</div>
	</div>
	<form name="discount_reindex_form" action="<? echo $APPLICATION->GetCurPage(); ?>" method="GET"><?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?><tr>
	<td width="40%"><? echo Loc::getMessage('SALE_DISCOUNT_REINDEX_MAX_EXECUTION_TIME')?></td>
	<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo $oneStepTime; ?>"></td>
	</tr><?
	$tabControl->Buttons();
	?>
	<input type="button" id="start_button" value="<? echo Loc::getMessage('SALE_DISCOUNT_REINDEX_UPDATE_BTN')?>"<? echo ($discountCounter > 0 ? '' : ' disabled'); ?>>
	<input type="button" id="stop_button" value="<? echo Loc::getMessage('SALE_DISCOUNT_REINDEX_STOP_BTN')?>" disabled>
	<?
	$tabControl->End();
	?></form><?

	$jsParams = array(
		'url' => $APPLICATION->GetCurPage(),
		'options' => array(
			'ajaxSessionID' => 'saleDiscountReindex',
			'maxExecutionTime' => $oneStepTime,
			'maxOperationCounter' => 10,
			'counter' => $discountCounter
		),
		'visual' => array(
			'startBtnID' => 'start_button',
			'stopBtnID' => 'stop_button',
			'resultContID' => 'discount_reindex_result_div',
			'errorContID' => 'discount_reindex_error_cont',
			'errorDivID' => 'discount_reindex_error_div',
			'timeFieldID' => 'max_execution_time'
		),
		'ajaxParams' => array(
			'operation' => 'Y'
		)
	);
?>
<script>
	var jsStepOperations = new BX.Catalog.StepOperations(<? echo CUtil::PhpToJSObject($jsParams, false, true); ?>);
</script>
<?
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
}