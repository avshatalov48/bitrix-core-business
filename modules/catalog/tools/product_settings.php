<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
const STOP_STATISTICS = true;
const NO_AGENT_CHECK = true;
const PUBLIC_AJAX_MODE = true;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('catalog'))
{
	ShowError(Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_REINDEX_ERRORS_MODULE_CATALOG_ABSENT'));
	die();
}

if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS))
{
	ShowError(Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_ACCESS_DENIED'));
	die();
}

if (!check_bitrix_sessid())
{
	ShowError(Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_ERRORS_INCORRECT_SESSION'));
	die();
}

$request = Main\Context::getCurrent()->getRequest();

$settingIds = [
	'default_quantity_trace',
	'default_can_buy_zero',
	'default_subscribe',
];
$settings = [];
foreach ($settingIds as $id)
{
	$settings[$id] = Main\Config\Option::get('catalog', $id);
}
unset($id);

if (
	$request->getRequestMethod() === 'GET'
	&& $request->get('operation') === 'Y'
)
{
	$params = [
		'sessID' => $request['ajaxSessionID'],
		'maxExecutionTime' => $request['maxExecutionTime'],
		'maxOperationCounter' => $request['maxOperationCounter'],
		'counter' => $request['counter'],
		'operationCounter' => $request['operationCounter'],
		'lastID' => $request['lastID'],
		'IBLOCK_ID' => $request['iblockId']
	];

	$productSettings = new CCatalogProductSettings(
		$params['sessID'],
		$params['maxExecutionTime'],
		$params['maxOperationCounter']
	);
	$productSettings->initStep($params['counter'], $params['operationCounter'], $params['lastID']);
	$productSettings->setParams($params);
	$productSettings->run();
	$result = $productSettings->saveStep();
	unset($productSettings);

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($result, false, true);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
elseif (
	$request->getRequestMethod() == 'GET'
	&& $request['changeSettings'] == 'Y'
)
{
	$result = array();

	$newSettings = $settings;
	foreach ($settingIds as $id)
	{
		$newValue = (string)$request[$id];
		if ($newValue == 'Y' || $newValue == 'N')
		{
			$newSettings[$id] = $newValue;
		}
		unset($newValue);
	}
	unset($id);

	foreach ($newSettings as $id => $value)
	{
		Main\Config\Option::set('catalog', $id, $value, '');

		if ($id === 'default_can_buy_zero')
			Main\Config\Option::set('catalog', 'allow_negative_amount', $value, '');
	}
	unset($id, $value);

	$result['success'] = 'Y';

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($result, false, true);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
elseif (
	$request->getRequestMethod() == 'GET'
	&& $request['getIblock'] == 'Y'
)
{
	$result = CCatalogProductSettings::getCatalogList();
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($result, false, true);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
elseif (
	$request->getRequestMethod() == 'GET'
	&& $request['finalRequest'] == 'Y'
)
{
	$iblockList = $request['iblockList'];
	if (!empty($iblockList) && is_array($iblockList))
	{
		foreach ($iblockList as $iblock)
			CIBlock::clearIblockTagCache($iblock);
		unset($iblock);
	}
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_PAGE_TITLE'));

	$oneStepTime = CCatalogProductSettings::getDefaultExecutionTime();

	$publicMode = $request->get('public_mode') === 'Y';

	if ($publicMode)
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
	}
	else
	{
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	}

	$tabList = [
		[
			'DIV' => 'productSettingsTab01',
			'TAB' => Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_TAB'),
			'ICON' => 'sale',
			'TITLE' => Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_TAB_TITLE'),
		],
	];
	$tabControl = new CAdminTabControl('productSettings', $tabList, true, true);
	if ($publicMode)
	{
		$tabControl->SetPublicMode();
	}
	Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/step_operations.js');

	?><div id="product_settings_error_div" style="margin:0; display: none;">
	<div class="adm-info-message-wrap adm-info-message-red">
		<div class="adm-info-message">
			<div class="adm-info-message-title"><?= Loc::getMessage('SALE_DISCOUNT_REINDEX_ERRORS_TITLE'); ?></div>
			<div id="product_settings_error_cont"></div>
			<div class="adm-info-message-icon"></div>
		</div>
	</div>
	</div>
	<form name="product_settings_form" id="product_settings_form" action="<?= $APPLICATION->GetCurPage(); ?>" method="GET"><?php
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?><tr>
		<td width="40%"><label for="default_quantity_trace"><?= Loc::getMessage("BX_CATALOG_PRODUCT_SETTINGS_ENABLE_QUANTITY_TRACE"); ?></label></td>
		<td width="60%">
			<input type="checkbox" name="default_quantity_trace" id="quantity_trace" value="Y"<?= ($settings['default_quantity_trace'] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><label for="default_can_buy_zero"><?= Loc::getMessage("BX_CATALOG_PRODUCT_SETTINGS_ALLOW_CAN_BUY_ZERO"); ?></label></td>
		<td width="60%">
			<input type="checkbox" name="default_can_buy_zero" id="can_buy_zero" value="Y"<?= ($settings['default_can_buy_zero'] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><label for="default_subscribe"><?= Loc::getMessage("BX_CATALOG_PRODUCT_SETTINGS_PRODUCT_SUBSCRIBE"); ?></label></td>
		<td width="60%">
			<input type="checkbox" name="default_subscribe" id="subscribe" value="Y"<?= ($settings['default_subscribe'] === 'Y' ? ' checked' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td width="40%"><?= Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_MAX_EXECUTION_TIME'); ?></td>
		<td width="60%"><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?= $oneStepTime; ?>"></td>
	</tr>
	<?php
	$tabControl->Buttons();
	?>
	<input type="button" id="product_settings_start_button" value="<?= Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_UPDATE_BTN'); ?>">
	<input type="button" id="product_settings_stop_button" value="<?= Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_STOP_BTN'); ?>" disabled>
	<div id="reindexReport" style="display: none;"></div>
	<?php
	$tabControl->End();
	?></form>
	<?php
	$jsParams = [
		'url' => $APPLICATION->GetCurPage(),
		'options' => [
			'ajaxSessionID' => 'productSettings',
			'maxExecutionTime' => $oneStepTime,
			'maxOperationCounter' => 10,
			'counter' => 0,
		],
		'visual' => [
			'startBtnID' => 'product_settings_start_button',
			'stopBtnID' => 'product_settings_stop_button',
			'timeFieldID' => 'max_execution_time',
			'reportID' => 'reindexReport',
			'prefix' => 'catalog_reindex_iblock_',
			'resultContID' => 'catalog_reindex_result_div_',
			'errorContID' => 'catalog_reindex_error_cont_',
			'errorDivID' => 'catalog_reindex_error_div_',
		],
		'ajaxParams' => [
			'operation' => 'Y',
		],
		'checkboxList' => [
			'quantity_trace',
			'can_buy_zero',
			'subscribe',
		],
		'messages' => [
			'status_yes' => Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_STATUS_YES'),
			'status_no' => Loc::getMessage('BX_CATALOG_PRODUCT_SETTINGS_STATUS_NO'),
		],
	];
	?>
	<script>
		var jsProductSettings = new BX.Catalog.ProductSettings(<?= CUtil::PhpToJSObject($jsParams, false, true); ?>);
	</script>
	<?php
	if (!$publicMode)
	{
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	}
}
