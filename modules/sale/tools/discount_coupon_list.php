<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
use Bitrix\Main\Application,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Internals,
	Bitrix\Main\UserTable,
	Bitrix\Main;

if (!defined('B_ADMIN_SUBCOUPONS') || B_ADMIN_SUBCOUPONS != 1 || !defined('B_ADMIN_SUBCOUPONS_LIST'))
	return;

$prologAbsent = (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true);
if (B_ADMIN_SUBCOUPONS_LIST === false && $prologAbsent)
	return;

$selfFolderUrl = (defined("SELF_FOLDER_URL") ? SELF_FOLDER_URL : "/bitrix/admin/");

if ($prologAbsent)
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
	Loader::includeModule('sale');

	$discountID = 0;
	if (isset($_REQUEST['find_discount_id']))
		$discountID = (int)$_REQUEST['find_discount_id'];
	$couponsAjaxPath = '/bitrix/tools/sale/discount_coupon_list.php?lang='.LANGUAGE_ID.'&find_discount_id='.$discountID;
	$saleModulePermissions = $APPLICATION->GetGroupRight('sale');
	$couponsReadOnly = ($saleModulePermissions < 'W');
}
if (!isset($discountID) || $discountID <= 0 || !isset($couponsAjaxPath) || empty($couponsAjaxPath))
	return;

if (isset($_REQUEST['mode']) && ($_REQUEST['mode'] == 'list' || $_REQUEST['mode'] == 'frame'))
	CFile::DisableJSFunction(true);

$canViewUserList = (
	$USER->CanDoOperation('view_subordinate_users')
	|| $USER->CanDoOperation('view_all_users')
	|| $USER->CanDoOperation('edit_all_users')
	|| $USER->CanDoOperation('edit_subordinate_users')
);

Loc::loadMessages(__FILE__);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');

$adminListTableID = 'tbl_sale_sub_coupons_'.md5($discountID);

$hideFields = array('DISCOUNT_ID');
$adminSort = new CAdminSubSorting($adminListTableID, 'ID', 'ASC', 'by', 'order', $couponsAjaxPath);
$adminList = new CAdminSubList($adminListTableID, $adminSort, $couponsAjaxPath, $hideFields);
$adminList->setDialogParams(array('from_module' => 'sale'));
unset($hideFields);

if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

$filter = array(
	'=DISCOUNT_ID' => $discountID
);
$filterFields = array(
	'find_discount_id'
);
$adminList->InitFilter($filterFields);

if (!$couponsReadOnly && $adminList->EditAction())
{
	if (isset($FIELDS) && is_array($FIELDS))
	{
		$conn = Application::getConnection();
		Internals\DiscountCouponTable::disableCheckCouponsUse();
		foreach ($FIELDS as $couponID => $fields)
		{
			$couponID = (int)$couponID;
			if ($couponID <= 0 || !$adminList->IsUpdated($couponID))
				continue;
			unset($fields['DISCOUNT_ID']);

			$conn->startTransaction();
			$result = Internals\DiscountCouponTable::prepareCouponData($fields);
			if ($result->isSuccess())
				$result = Internals\DiscountCouponTable::update($couponID, $fields);

			if ($result->isSuccess())
			{
				$conn->commitTransaction();
			}
			else
			{
				$conn->rollbackTransaction();
				$adminList->AddUpdateError(implode('<br>', $result->getErrorMessages()), $couponID);
			}
			unset($result);
		}
		unset($fields, $couponID);
		Internals\DiscountCouponTable::enableCheckCouponsUse();
	}
}

if (!$couponsReadOnly && ($listID = $adminList->GroupAction()))
{
	$checkUseCoupons = ($_REQUEST['action'] == 'delete');
	Internals\DiscountCouponTable::clearDiscountCheckList();
	if ($_REQUEST['action_target'] == 'selected')
	{
		$listID = array();
		$couponIterator = Internals\DiscountCouponTable::getList(array(
			'select' => array('ID'),
			'filter' => $filter
		));
		while ($coupon = $couponIterator->fetch())
		{
			$listID[] = $coupon['ID'];
		}
	}

	$listID = array_filter($listID);
	if (!empty($listID))
	{
		switch ($_REQUEST['action'])
		{
			case 'activate':
			case 'deactivate':
				Internals\DiscountCouponTable::disableCheckCouponsUse();
				$fields = array(
					'ACTIVE' => ($_REQUEST['action'] == 'activate' ? 'Y' : 'N')
				);
				foreach ($listID as &$couponID)
				{
					$result = Internals\DiscountCouponTable::update($couponID, $fields);
					if (!$result->isSuccess())
						$adminList->AddGroupError(implode('<br>', $result->getErrorMessages()), $couponID);
					unset($result);
				}
				unset($couponID, $fields);
				Internals\DiscountCouponTable::enableCheckCouponsUse();
				break;
			case 'delete':
				Internals\DiscountCouponTable::setDiscountCheckList($discountID);
				Internals\DiscountCouponTable::disableCheckCouponsUse();
				foreach ($listID as &$couponID)
				{
					$result = Internals\DiscountCouponTable::delete($couponID);
					if (!$result->isSuccess())
						$adminList->AddGroupError(implode('<br>', $result->getErrorMessages()), $couponID);
					unset($result);
				}
				unset($couponID);
				Internals\DiscountCouponTable::enableCheckCouponsUse();
				Internals\DiscountCouponTable::updateUseCoupons();
				break;
		}
	}
	unset($listID);
}

CJSCore::Init(array('date'));

$headerList = array();
$headerList['ID'] = array(
	'id' => 'ID',
	'content' => 'ID',
	'sort' => 'ID',
	'default' => true
);
$headerList['COUPON'] = array(
	'id' => 'COUPON',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_COUPON'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_COUPON'),
	'sort' => 'COUPON',
	'default' => true
);
$headerList['ACTIVE'] = array(
	'id' => 'ACTIVE',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_ACTIVE'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_ACTIVE'),
	'sort' => 'ACTIVE',
	'default' => true
);
$headerList['ACTIVE_FROM'] = array(
	'id' => 'ACTIVE_FROM',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_ACTIVE_FROM'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_ACTIVE_FROM'),
	'sort' => 'ACTIVE_FROM',
	'default' => true
);
$headerList['ACTIVE_TO'] = array(
	'id' => 'ACTIVE_TO',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_ACTIVE_TO'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_ACTIVE_TO'),
	'sort' => 'ACTIVE_TO',
	'default' => true
);
$headerList['TYPE'] = array(
	'id' => 'TYPE',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_TYPE'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_TYPE'),
	'sort' => 'TYPE',
	'default' => true
);
$headerList['MAX_USE'] = array(
	'id' => 'MAX_USE',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_MAX_USE'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_MAX_USE'),
	'sort' => 'MAX_USE',
	'default' => true
);
$headerList['USE_COUNT'] = array(
	'id' => 'USE_COUNT',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_USE_COUNT'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_USE_COUNT'),
	'sort' => 'USE_COUNT',
	'default' => true
);
$headerList['USER_ID'] = array(
	'id' => 'USER_ID',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_USER_ID'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_USER_ID'),
	'sort' => 'USER_ID',
	'default' => true
);
$headerList['DATE_APPLY'] = array(
	'id' => 'DATE_APPLY',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_DATE_APPLY'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_DATE_APPLY'),
	'sort' => 'DATE_APPLY',
	'default' => true
);
$headerList['MODIFIED_BY'] = array(
	'id' => 'MODIFIED_BY',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_MODIFIED_BY'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_MODIFIED_BY'),
	'sort' => 'MODIFIED_BY',
	'default' => true
);
$headerList['TIMESTAMP_X'] = array(
	'id' => 'TIMESTAMP_X',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_TIMESTAMP_X'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_TIMESTAMP_X'),
	'sort' => 'TIMESTAMP_X',
	'default' => true
);
$headerList['CREATED_BY'] = array(
	'id' => 'CREATED_BY',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_CREATED_BY'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_CREATED_BY'),
	'sort' => 'CREATED_BY',
	'default' => false
);
$headerList['DATE_CREATE'] = array(
	'id' => 'DATE_CREATE',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_DATE_CREATE'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_DATE_CREATE'),
	'sort' => 'DATE_CREATE',
	'default' => false
);
$headerList['DESCRIPTION'] = array(
	'id' => 'DESCRIPTION',
	'content' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_NAME_DESCRIPTION'),
	'title' => Loc::getMessage('SALE_ADM_DSC_CPN_HEADER_TITLE_DESCRIPTION'),
	'default' => false
);
$adminList->AddHeaders($headerList);

$selectFields = array_fill_keys($adminList->GetVisibleHeaderColumns(), true);
$selectFields['ID'] = true;
$selectFields['ACTIVE'] = true;
$selectFields['TYPE'] = true;
$selectFieldsMap = array_fill_keys(array_keys($headerList), false);
$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);

$userList = array();
$userIDs = array();
$nameFormat = CSite::GetNameFormat(true);

$rowList = array();

$couponTypeList = Internals\DiscountCouponTable::getCouponTypes(true);

$usePageNavigation = true;
$navyParams = array();
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
{
	$usePageNavigation = false;
}
else
{
	$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($adminListTableID, array('nPageSize' => 20, 'sNavID' => $adminList->GetListUrl(true))));
	if ($navyParams['SHOW_ALL'])
	{
		$usePageNavigation = false;
	}
	else
	{
		$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
		$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
	}
}
if ($selectFields['TYPE'])
	$selectFields['USE_COUNT'] = true;

$selectFields = array_keys($selectFields);

$getListParams = array(
	'select' => $selectFields,
	'filter' => $filter,
	'order' => array($by => $order)
);
$totalPages = 0;
if ($usePageNavigation)
{
	$countQuery = new Main\Entity\Query(Internals\DiscountCouponTable::getEntity());
	$countQuery->addSelect(new Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($getListParams['filter']);
	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];
	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = 0;
	}
}

$couponIterator = new CAdminSubResult(Internals\DiscountCouponTable::getList($getListParams), $adminListTableID, $adminList->GetListUrl(true));
if ($usePageNavigation)
{
	$couponIterator->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$couponIterator->NavRecordCount = $totalCount;
	$couponIterator->NavPageCount = $totalPages;
	$couponIterator->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$couponIterator->NavStart();
}
$adminList->NavText($couponIterator->GetNavPrint(Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_MESS_NAV')));

while ($coupon = $couponIterator->Fetch())
{
	$coupon['ID'] = (int)$coupon['ID'];
	if ($selectFieldsMap['MAX_USE'])
		$coupon['MAX_USE'] = (int)$coupon['MAX_USE'];
	if ($selectFieldsMap['USE_COUNT'])
		$coupon['USE_COUNT'] = (int)$coupon['USE_COUNT'];
	if ($coupon['TYPE'] != Internals\DiscountCouponTable::TYPE_MULTI_ORDER)
	{
		$coupon['MAX_USE'] = 0;
		$coupon['USE_COUNT'] = 0;
	}
	if ($selectFieldsMap['CREATED_BY'])
	{
		$coupon['CREATED_BY'] = (int)$coupon['CREATED_BY'];
		if ($coupon['CREATED_BY'] > 0)
			$userIDs[$coupon['CREATED_BY']] = true;
	}
	if ($selectFieldsMap['MODIFIED_BY'])
	{
		$coupon['MODIFIED_BY'] = (int)$coupon['MODIFIED_BY'];
		if ($coupon['MODIFIED_BY'] > 0)
			$userIDs[$coupon['MODIFIED_BY']] = true;
	}
	if ($selectFieldsMap['USER_ID'])
	{
		$coupon['USER_ID'] = (int)$coupon['USER_ID'];
		if ($coupon['USER_ID'] > 0)
			$userIDs[$coupon['USER_ID']] = true;
	}
	$urlEdit = $selfFolderUrl.'sale_discount_coupon_edit.php?ID='.$coupon['ID'].'&DISCOUNT_ID='.$discountID.'&lang='.LANGUAGE_ID.'&bxpublic=Y';

	$rowList[$coupon['ID']] = $row = &$adminList->AddRow(
		$coupon['ID'],
		$coupon,
		$urlEdit,
		Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_MESS_EDIT_COUPON'),
		true
	);
	$row->AddViewField('ID', $coupon['ID']);

	if ($selectFieldsMap['DATE_CREATE'])
		$row->AddViewField('DATE_CREATE', $coupon['DATE_CREATE']);
	if ($selectFieldsMap['TIMESTAMP_X'])
		$row->AddViewField('TIMESTAMP_X', $coupon['TIMESTAMP_X']);

	if ($selectFieldsMap['MAX_USE'])
		$row->AddViewField('MAX_USE', ($coupon['MAX_USE'] > 0 ? $coupon['MAX_USE'] : ''));
	if ($selectFieldsMap['USE_COUNT'])
		$row->AddViewField('USE_COUNT', ($coupon['USE_COUNT'] > 0 ? $coupon['USE_COUNT'] : ''));
	if ($selectFieldsMap['TYPE'])
		$row->AddViewField('TYPE', $couponTypeList[$coupon['TYPE']]);
	if ($selectFieldsMap['DESCRIPTION'])
		$row->AddViewField('DESCRIPTION', htmlspecialcharsbx($coupon['DESCRIPTION']));
	if (!$couponsReadOnly)
	{
		if ($selectFieldsMap['COUPON'])
			$row->AddInputField('COUPON', array('size' => 32));
		if ($selectFieldsMap['ACTIVE'])
			$row->AddCheckField('ACTIVE');
		if ($selectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField('ACTIVE_FROM');
		if ($selectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField('ACTIVE_TO');
	}
	else
	{
		if ($selectFieldsMap['COUPON'])
			$row->AddInputField('COUPON', false);
		if ($selectFieldsMap['ACTIVE'])
			$row->AddCheckField('ACTIVE', false);
		if ($selectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField('ACTIVE_FROM', false);
		if ($selectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField('ACTIVE_TO');
	}

	$actions = array();
	$actions[] = array(
		'ICON' => 'edit',
		'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_CONTEXT_EDIT'),
		'ACTION' => "(new BX.CAdminDialog({
				'content_url': '".CUtil::JSEscape($urlEdit)."',
				'content_post': 'bxpublic=Y',
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
			})).Show();",
		'DEFAULT' => true
	);
	if (!$couponsReadOnly)
	{
		$actions[] = array(
			'ICON' => 'copy',
			'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_CONTEXT_COPY'),
			'ACTION'=>"(new BX.CAdminDialog({
				'content_url': '".CUtil::JSEscape($urlEdit.'&action=copy')."',
				'content_post': 'bxpublic=Y',
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
			})).Show();",
			'DEFAULT' => false,
		);
		if ($coupon['ACTIVE'] == 'Y')
		{
			$actions[] = array(
				'ICON' => 'deactivate',
				'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_CONTEXT_DEACTIVATE'),
				'ACTION' => $adminList->ActionDoGroup($coupon['ID'], 'deactivate'),
				'DEFAULT' => false,
			);
		}
		else
		{
			$actions[] = array(
				'ICON' => 'activate',
				'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_CONTEXT_ACTIVATE'),
				'ACTION' => $adminList->ActionDoGroup($coupon['ID'], 'activate'),
				'DEFAULT' => false,
			);
		}
		$actions[] = array('SEPARATOR' => true);
		$actions[] = array(
			'ICON' =>'delete',
			'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_CONTEXT_DELETE'),
			'ACTION' => "if(confirm('".Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_CONTEXT_DELETE_CONFIRM')."')) ".$adminList->ActionDoGroup($coupon['ID'], 'delete')
		);
	}
	$row->AddActions($actions);
	unset($actions, $row);
}

if (!empty($rowList) && ($selectFieldsMap['CREATED_BY'] || $selectFieldsMap['MODIFIED_BY'] || $selectFieldsMap['USER_ID']))
{
	if (!empty($userIDs))
	{
		$userIterator = UserTable::getList(array(
			'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'),
			'filter' => array('@ID' => array_keys($userIDs)),
		));
		while ($oneUser = $userIterator->fetch())
		{
			$oneUser['ID'] = (int)$oneUser['ID'];
			if ($canViewUserList && !$adminSidePanelHelper->isPublicSidePanel())
				$userList[$oneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$oneUser['ID'].'">'.CUser::FormatName($nameFormat, $oneUser).'</a>';
			else
				$userList[$oneUser['ID']] = CUser::FormatName($nameFormat, $oneUser);
		}
		unset($oneUser, $userIterator);
	}

	foreach ($rowList as &$row)
	{
		if ($selectFieldsMap['CREATED_BY'])
		{
			$userName = '';
			if ($row->arRes['CREATED_BY'] > 0 && isset($userList[$row->arRes['CREATED_BY']]))
				$userName = $userList[$row->arRes['CREATED_BY']];
			$row->AddViewField('CREATED_BY', $userName);
		}
		if ($selectFieldsMap['MODIFIED_BY'])
		{
			$userName = '';
			if ($row->arRes['MODIFIED_BY'] > 0 && isset($userList[$row->arRes['MODIFIED_BY']]))
				$userName = $userList[$row->arRes['MODIFIED_BY']];
			$row->AddViewField('MODIFIED_BY', $userName);
		}
		if ($selectFieldsMap['USER_ID'])
		{
			$userName = '';
			if ($row->arRes['USER_ID'] > 0 && isset($userList[$row->arRes['USER_ID']]))
				$userName = $userList[$row->arRes['USER_ID']];
			$row->AddViewField('USER_ID', $userName);
		}
		unset($userName);
	}
	unset($row);
}

$adminList->AddFooter(
	array(
		array(
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $couponIterator->SelectedRowsCount()
		),
		array(
			'counter' => true,
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_CHECKED'),
			'value' => 0
		),
	)
);
if (!$couponsReadOnly)
{
	$adminList->AddGroupActionTable(
		array(
			'delete' => Loc::getMessage('MAIN_ADMIN_LIST_DELETE'),
			'activate' => Loc::getMessage('MAIN_ADMIN_LIST_ACTIVATE'),
			'deactivate' => Loc::getMessage('MAIN_ADMIN_LIST_DEACTIVATE'),
		)
	);
}
if (!isset($_REQUEST["mode"]) || ($_REQUEST["mode"] != 'excel' && $_REQUEST["mode"] != 'subsettings'))
{
	?><script>
	function ShowNewCoupons(id, multi)
	{
		var PostParams = {
			lang: '<? echo LANGUAGE_ID; ?>',
			DISCOUNT_ID: id,
			MULTI: multi,
			ID: 0,
			bxpublic: 'Y',
			sessid: BX.bitrix_sessid()
		};
		(new BX.CAdminDialog({
			'content_url': '<?=$selfFolderUrl?>sale_discount_coupon_edit.php',
			'content_post': PostParams,
			'draggable': true,
			'resizable': true,
			'width': 800,
			'height': 500,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
	}
	</script><?
	$aContext = array();
	if (!$couponsReadOnly)
	{
		$addSubMenu = array();
		$addSubMenu[] = array(
			'TEXT' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_LIST_ADD_ONE_COUPON'),
			'TITLE' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_LIST_ADD_ONE_COUPON_TITLE'),
			'LINK' => "javascript:ShowNewCoupons(".$discountID.", 'N')",
			'SHOW_TITLE' => true
		);
		$addSubMenu[] = array(
			'TEXT' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_LIST_ADD_MULTI_COUPON'),
			'TITLE' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_LIST_ADD_MULTI_COUPON_TITLE'),
			'LINK' => "javascript:ShowNewCoupons(".$discountID.", 'Y')",
			'SHOW_TITLE' => true
		);

		$aContext[] = array(
			'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_COUPONT_LIST_MESS_NEW_COUPON'),
			'TITLE' => Loc::getMessage('BT_SALE_DISCOUNT_COUPON_LIST_MESS_NEW_COUPON_TITLE'),
			'ICON' => 'btn_new',
			'MENU' => $addSubMenu,
		);
	}

	$aContext[] = array(
		'TEXT' => htmlspecialcharsbx(Loc::getMessage('BX_SALE_DISCOUNT_COUPON_LIST_REFRESH')),
		'TITLE' => Loc::getMessage('BX_SALE_DISCOUNT_COUPON_LIST_REFRESH_TITLE'),
		'ICON' => 'btn_sub_refresh',
		'LINK' => "javascript:".$adminList->ActionAjaxReload($adminList->GetListUrl(true)),
	);

	$adminList->AddAdminContextMenu($aContext);
}
$adminList->CheckListMode();

$adminList->DisplayList(B_ADMIN_SUBCOUPONS_LIST);

if ($prologAbsent)
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_popup_admin.php');