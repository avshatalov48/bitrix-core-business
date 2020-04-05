<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Catalog;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);

if (!$USER->CanDoOperation('catalog_price'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('BX_CATALOG_REINDEX_ACCESS_DENIED'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if (!check_bitrix_sessid())
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('BX_CATALOG_REINDEX_ERRORS_INCORRECT_SESSION'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if (!Loader::includeModule('catalog'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('BX_CATALOG_REINDEX_REINDEX_ERRORS_MODULE_CATALOG_ABSENT'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

$request = Main\Context::getCurrent()->getRequest();

if (
	$request->getRequestMethod() == 'GET'
	&& $request['operation'] == 'Y'
)
{
	CUtil::JSPostUnescape();

	$params = array(
		'sessID' => $request['ajaxSessionID'],
		'maxExecutionTime' => $request['maxExecutionTime'],
		'maxOperationCounter' => $request['maxOperationCounter'],
		'counter' => $request['counter'],
		'operationCounter' => $request['operationCounter'],
		'lastID' => $request['lastID'],
		'IBLOCK_ID' => $request['iblockId']
	);

	$catalogReindex = new CCatalogIblockReindex(
		$params['sessID'],
		$params['maxExecutionTime'],
		$params['maxOperationCounter']
	);
	$catalogReindex->initStep($params['counter'], $params['operationCounter'], $params['lastID']);
	$catalogReindex->setParams($params);
	$catalogReindex->run();
	$result = $catalogReindex->saveStep();

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($result, false, true);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
elseif (
	$request->getRequestMethod() == 'GET'
	&& $request['getIblock'] == 'Y'
)
{
	$result = CCatalogIblockReindex::getIblockList($request['iblock']);
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
		foreach ($iblockList as &$iblock)
			CIBlock::clearIblockTagCache($iblock);
		unset($iblock);
	}
	$emptyAvailable = Catalog\ProductTable::getList(array(
		'select' => array('ID', 'AVAILABLE'),
		'filter' => array('=AVAILABLE' => null),
		'limit' => 1
	))->fetch();
	if (empty($emptyAvailable))
	{
		$adminNotifyIterator = CAdminNotify::GetList(array(), array('MODULE_ID' => 'catalog', 'TAG' => 'CATALOG_16'));
		if ($adminNotifyIterator)
		{
			if ($adminNotify = $adminNotifyIterator->Fetch())
				CAdminNotify::Delete($adminNotify['ID']);
			unset($adminNotify);
		}
		unset($adminNotifyIterator);
	}
	unset($emptyAvailable);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage('BX_CATALOG_REINDEX_PAGE_TITLE'));

	$oneStepTime = CCatalogIblockReindex::getDefaultExecutionTime();

	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

	$tabList = array(
			array('DIV' => 'catalogReindexTab01', 'TAB' => Loc::getMessage('BX_CATALOG_REINDEX_TAB'), 'ICON' => 'sale', 'TITLE' => Loc::getMessage('BX_CATALOG_REINDEX_TAB_TITLE'))
	);
	$tabControl = new CAdminTabControl('catalogReindex', $tabList, true, true);
	Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/step_operations.js');

	?><div id="catalog_reindex_error_div" style="margin:0; display: none;">
	<div class="adm-info-message-wrap adm-info-message-red">
		<div class="adm-info-message">
			<div class="adm-info-message-title"><? echo Loc::getMessage('BX_CATALOG_REINDEX_ERRORS_TITLE'); ?></div>
			<div id="catalog_reindex_error_cont"></div>
			<div class="adm-info-message-icon"></div>
		</div>
	</div>
	</div>
	<form name="catalog_reindex_form" action="<? echo $APPLICATION->GetCurPage(); ?>" method="GET"><?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?><tr>
		<td width="40%"><? echo Loc::getMessage('BX_CATALOG_REINDEX_IBLOCK_ID'); ?></td>
		<td width="60%"><?
		$catalogList = array();
		$catalogIterator = Catalog\CatalogIblockTable::getList(array(
			'select' => array('IBLOCK_ID'),
			'filter' => array('=PRODUCT_IBLOCK_ID' => 0)
		));
		while ($catalog = $catalogIterator->fetch())
		{
			$catalog['IBLOCK_ID'] = (int)$catalog['IBLOCK_ID'];
			$catalogList[$catalog['IBLOCK_ID']] = $catalog['IBLOCK_ID'];
		}
		unset($catalog, $catalogIterator);
		$catalogIterator = Catalog\CatalogIblockTable::getList(array(
				'select' => array('PRODUCT_IBLOCK_ID'),
				'filter' => array('>PRODUCT_IBLOCK_ID' => 0)
		));
		while ($catalog = $catalogIterator->fetch())
		{
			$catalog['PRODUCT_IBLOCK_ID'] = (int)$catalog['PRODUCT_IBLOCK_ID'];
			$catalogList[$catalog['PRODUCT_IBLOCK_ID']] = $catalog['PRODUCT_IBLOCK_ID'];
		}
		unset($catalog, $catalogIterator);
		if (!empty($catalogList))
			echo GetIBlockDropDownList(0, 'catalog_reindex_iblock_type', 'catalog_reindex_iblock_id', array('ID' => $catalogList));
		unset($catalogList);
		?></td>
	</tr>
	<tr>
		<td width="40%"><? echo Loc::getMessage('BX_CATALOG_REINDEX_MAX_EXECUTION_TIME')?></td>
		<td width="60%"><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo $oneStepTime; ?>"></td>
	</tr>
	<?
	$tabControl->Buttons();
	?>
	<input type="button" id="catalog_reindex_start_button" value="<? echo Loc::getMessage('BX_CATALOG_REINDEX_UPDATE_BTN')?>">
	<input type="button" id="catalog_reindex_stop_button" value="<? echo Loc::getMessage('BX_CATALOG_REINDEX_STOP_BTN')?>" disabled>
	<?
	$tabControl->End();
	?></form>
	<div id="reindexReport" style="display: none;"></div>
	<?
	$jsParams = array(
		'url' => $APPLICATION->GetCurPage(),
		'options' => array(
			'ajaxSessionID' => 'catalogReindex',
			'maxExecutionTime' => $oneStepTime,
			'maxOperationCounter' => 10,
			'counter' => 0
		),
		'visual' => array(
			'startBtnID' => 'catalog_reindex_start_button',
			'stopBtnID' => 'catalog_reindex_stop_button',
			'catalogSelectID' => 'catalog_reindex_iblock_id',
			'timeFieldID' => 'max_execution_time',
			'reportID' => 'reindexReport',
			'prefix' => 'catalog_reindex_iblock_',
			'resultContID' => 'catalog_reindex_result_div_',
			'errorContID' => 'catalog_reindex_error_cont_',
			'errorDivID' => 'catalog_reindex_error_div_'
		),
		'ajaxParams' => array(
			'operation' => 'Y'
		)
	);
	?>
	<script type="text/javascript">
		var jsCatalogReindex = new BX.Catalog.CatalogReindex(<? echo CUtil::PhpToJSObject($jsParams, false, true); ?>);
	</script>
	<?
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
}