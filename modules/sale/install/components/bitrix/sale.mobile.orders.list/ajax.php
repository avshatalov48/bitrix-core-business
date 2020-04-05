<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

if (!CModule::IncludeModule('sale')) die('module sale not installed');

if($USER->IsAuthorized() && check_bitrix_sessid())
{
	$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : false;
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
	$orderDetailPath = isset($_REQUEST['order_detail_path']) ? trim($_REQUEST['order_detail_path']) : false;
	$arFilter = array();
	$arParams = array('RETURN_AS_ARRAY' => 'Y');

	if($orderDetailPath)
		$arParams['ORDER_DETAIL_PATH'] = $orderDetailPath;

	if(isset($_REQUEST['filter']) && is_array($_REQUEST['filter']) && !empty($_REQUEST['filter']))
		foreach ($_REQUEST['filter'] as $key => $value)
			$arFilter[$key] = $value;

	$saleModulePermissions = $APPLICATION->GetGroupRight('sale');

	if ($saleModulePermissions == 'D')
	{
		$arFilter['USER_ID'] = IntVal($USER->GetID());
	}
	elseif ($saleModulePermissions != 'W')
	{
		$arFilter['STATUS_PERMS_GROUP_ID'] = $GLOBALS['USER']->GetUserGroupArray();
		$arFilter['>=STATUS_PERMS_PERM_VIEW'] = 'Y';
	}

	switch ($action)
	{
		case 'get_order':

			if(!$id)
				die(false);

			$arFilter = array_merge($arFilter, array('ID' => $id));

		break;

		case 'get_orders':

			$last = isset($_REQUEST['last']) ? trim($_REQUEST['last']): false;

			if(!$last)
				die(false);

			$arFilter = array_merge($arFilter, array('<ID' => $last));

		break;

		case 'get_updated_orders':

			$arDateUpdated = array();

			if(isset($_REQUEST['timestamp']) && !empty($_REQUEST['timestamp']))
				$arDateUpdated = array('>=DATE_UPDATE' => FormatDate('FULL', round(intval($_REQUEST['timestamp'])/1000), ''));

			$arFilter = array_merge($arFilter, $arDateUpdated);

		break;

	}

	$arParams['FILTER'] = $arFilter;

	//component prints json & dies with RETURN_AS_ARRAY param
	$APPLICATION->IncludeComponent(
		'bitrix:sale.mobile.orders.list',
		'.default',
		$arParams,
		false
	);
}

require($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin_after.php');
?>
