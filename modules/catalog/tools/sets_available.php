<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Catalog,
	Bitrix\Catalog\Access\ActionDictionary,
	Bitrix\Catalog\Access\AccessController;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

Loc::loadMessages(__FILE__);

if (
	!Loader::includeModule('catalog')
	|| !AccessController::getCurrent()->check(ActionDictionary::ACTION_PRICE_EDIT)
	|| !Catalog\Config\Feature::isProductSetsEnabled()
)
{
	ShowError(Loc::getMessage('CAT_SETS_AVAILABLE_ERRORS_FATAL'));
	die();
}
if (
	$_SERVER['REQUEST_METHOD'] == 'GET'
	&& check_bitrix_sessid()
	&& (isset($_REQUEST['operation']) && !is_array($_REQUEST['operation']) && (string)$_REQUEST['operation'] == 'Y')
)
{
	$params = array(
		'sessID' => $_GET['ajaxSessionID'],
		'maxExecutionTime' => $_GET['maxExecutionTime'],
		'maxOperationCounter' => $_GET['maxOperationCounter'],
		'counter' => $_GET['counter'],
		'operationCounter' => $_GET['operationCounter'],
		'lastID' => $_GET['lastID']
	);

	$setsAvailable = new CCatalogProductSetAvailable($params['sessID'], $params['maxExecutionTime'], $params['maxOperationCounter']);
	$setsAvailable->initStep($params['counter'], $params['operationCounter'], $params['lastID']);
	$setsAvailable->run();
	$result = $setsAvailable->saveStep();

	if ($result['finishOperation'])
	{
		$adminNotifyIterator = CAdminNotify::GetList(array(), array('MODULE_ID'=>'catalog', 'TAG' => 'CATALOG_SETS_AVAILABLE'));
		if ($adminNotify = $adminNotifyIterator->Fetch())
		{
			CAdminNotify::DeleteByTag('CATALOG_SETS_AVAILABLE');
		}
	}
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($result, false, true);
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_after.php');
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage('CAT_SETS_AVAILABLE_PAGE_TITLE'));

	$setsCounter = CCatalogProductSetAvailable::getAllCounter();
	$oneStepTime = CCatalogProductSetAvailable::getDefaultExecutionTime();

	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

	$tabList = array(
		array(
			'DIV' => 'setTab01',
			'TAB' => Loc::getMessage('CAT_SETS_AVAILABLE_TAB'),
			'ICON' => 'catalog',
			'TITLE' => Loc::getMessage('CAT_SETS_AVAILABLE_TAB_TITLE')
		)
	);
	$tabControl = new CAdminTabControl('sets_available', $tabList, true, true);
	Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/step_operations.js');

	?><div id="sets_result_div" style="margin:0; display: none;"></div>
	<div id="sets_error_div" style="margin:0; display: none;">
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><? echo Loc::getMessage('CAT_SETS_AVAILABLE_ERRORS_TITLE'); ?></div>
				<div id="sets_error_cont"></div>
				<div class="adm-info-message-icon"></div>
			</div>
		</div>
	</div>
	<form name="sets_available_form" action="<? echo $APPLICATION->GetCurPage(); ?>" method="POST"><?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?><tr>
	<td width="40%"><? echo Loc::getMessage('CAT_SETS_AVAILABLE_MAX_EXECUTION_TIME')?></td>
	<td><input type="text" name="max_execution_time" id="max_execution_time" size="3" value="<?echo $oneStepTime; ?>"></td>
	</tr><?
	$tabControl->Buttons();
	?>
	<input type="button" id="start_button" value="<? echo Loc::getMessage('CAT_SETS_AVAILABLE_UPDATE_BTN')?>"<? echo ($setsCounter > 0 ? '' : ' disabled'); ?>>
	<input type="button" id="stop_button" value="<? echo Loc::getMessage('CAT_SETS_AVAILABLE_STOP_BTN')?>" disabled>
	<?
	$tabControl->End();
	?></form><?
	$jsParams = array(
		'url' => $APPLICATION->GetCurPage(),
		'options' => array(
			'ajaxSessionID' => 'setsConv',
			'maxExecutionTime' => $oneStepTime,
			'maxOperationCounter' => 10,
			'counter' => $setsCounter
		),
		'visual' => array(
			'startBtnID' => 'start_button',
			'stopBtnID' => 'stop_button',
			'resultContID' => 'sets_result_div',
			'errorContID' => 'sets_error_cont',
			'errorDivID' => 'sets_error_div',
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
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>