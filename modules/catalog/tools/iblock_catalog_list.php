<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global array $FIELDS */
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;

if (!defined('B_ADMIN_IBLOCK_CATALOGS') || B_ADMIN_IBLOCK_CATALOGS != 1 || !defined('B_ADMIN_IBLOCK_CATALOGS_LIST'))
	return;

$prologAbsent = (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true);
if (B_ADMIN_IBLOCK_CATALOGS_LIST === false && $prologAbsent)
	return;

if ($prologAbsent)
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
	CUtil::JSPostUnescape();
	Loader::includeModule('catalog');

	$readOnly = !$USER->CanDoOperation('catalog_settings');
}

$catalogsAjaxPath = '/bitrix/tools/catalog/iblock_catalog_list.php?lang='.LANGUAGE_ID;
$saleRecurring = CBXFeatures::IsFeatureEnabled('SaleRecurring');

if (isset($_REQUEST['mode']) && ($_REQUEST['mode'] == 'list' || $_REQUEST['mode'] == 'frame'))
	CFile::DisableJSFunction(true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');

Loc::loadMessages(__FILE__);

$adminListTableID = 'tbl_catalog_iblocks';

$hideFields = array();
$adminSort = new CAdminSubSorting($adminListTableID, 'ID', 'ASC', 'by', 'order', $catalogsAjaxPath);
$adminList = new CAdminSubList($adminListTableID, $adminSort, $catalogsAjaxPath, $hideFields);
$adminList->setDialogParams(array('from_module' => 'catalog'));
$adminList->setDialogButtons(array());
unset($hideFields);

if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

$filter = array();
$filterFields = array();

$adminList->InitFilter($filterFields);

if (!$readOnly && $adminList->EditAction())
{

}

if (!$readOnly && ($listID = $adminList->GroupAction()))
{

}

$headerList = array();
$headerList['ID'] = array(
	'id' => 'ID',
	'content' => 'ID',
	'sort' => 'ID',
	'default' => true
);
$headerList['NAME'] = array(
	'id' => 'NAME',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_NAME'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_NAME'),
	'sort' => 'NAME',
	'default' => true
);
$headerList['IBLOCK_TYPE_ID'] = array(
	'id' => 'IBLOCK_TYPE_ID',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_IBLOCK_TYPE_ID'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_IBLOCK_TYPE_ID'),
	'sort' => 'IBLOCK_TYPE_ID',
	'default' => true
);
$headerList['SORT'] = array(
	'id' => 'SORT',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_SORT'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_SORT'),
	'sort' => 'SORT',
	'default' => true
);
$headerList['IBLOCK_SITES'] = array(
	'id' => 'IBLOCK_SITES',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_IBLOCK_SITES'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_IBLOCK_SITES'),
	'external' => true,
	'default' => true
);
$headerList['ACTIVE'] = array(
	'id' => 'ACTIVE',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_ACTIVE'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_ACTIVE'),
	'sort' => 'ACTIVE',
	'default' => true
);
$headerList['IS_CATALOG'] = array(
	'id' => 'IS_CATALOG',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_IS_CATALOG'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_IS_CATALOG'),
	'field' => 'CATALOG_IBLOCK.IBLOCK_ID',
	'sort' => 'CATALOG_IBLOCK.IBLOCK_ID',
	'default' => true
);
$headerList['SKU_IBLOCK'] = array(
	'id' => 'SKU_IBLOCK',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_SKU_IBLOCK'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_SKU_IBLOCK'),
	'field' => 'SKU.IBLOCK_ID',
	'default' => true
);
if ($saleRecurring)
{
	$headerList['SUBSCRIPTION'] = array(
		'id' => 'SUBSCRIPTION',
		'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_SUBSCRIPTION'),
		'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_SUBSCRIPTION'),
		'field' => 'CATALOG_IBLOCK.SUBSCRIPTION',
		'sort' => 'CATALOG_IBLOCK.SUBSCRIPTION',
		'default' => true
	);
}
$headerList['YANDEX_EXPORT'] = array(
	'id' => 'YANDEX_EXPORT',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_YANDEX_EXPORT'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_YANDEX_EXPORT'),
	'field' => 'CATALOG_IBLOCK.YANDEX_EXPORT',
	'sort' => 'CATALOG_IBLOCK.YANDEX_EXPORT',
	'default' => true
);
$headerList['VAT_ID'] = array(
	'id' => 'VAT_ID',
	'content' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_NAME_VAT_ID'),
	'title' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_HEADER_TITLE_VAT_ID'),
	'field' => 'CATALOG_IBLOCK.VAT_ID',
	'default' => true
);
$adminList->AddHeaders($headerList);

$hiddenSelectFields = array(
	'PRODUCT_IBLOCK_ID' => 'CATALOG_IBLOCK.PRODUCT_IBLOCK_ID'
);
if (!$saleRecurring)
	$hiddenSelectFields['SUBSCRIPTION'] = 'CATALOG_IBLOCK.SUBSCRIPTION';

$selectFields = array_fill_keys($adminList->GetVisibleHeaderColumns(), true);
$selectFields['ID'] = true;
$selectFields['IS_CATALOG'] = true;
$selectFields['SKU_IBLOCK'] = true;
$selectFieldsMap = array_fill_keys(array_keys($headerList), false);
$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);

$catalogEditUrl = '/bitrix/tools/catalog/iblock_catalog_edit.php?lang='.LANGUAGE_ID.'&IBLOCK_ID=';

$vatList = array(0 => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_MESS_NOT_SELECT'));
if ($selectFieldsMap['VAT_ID'])
{
	$vatIterator = Catalog\VatTable::getList(array(
		'select' => array('ID', 'NAME', 'SORT'),
		'order' => array('SORT' => 'ASC', 'ID' => 'ASC')
	));
	while ($vat = $vatIterator->fetch())
		$vatList[$vat['ID']] = $vat['NAME'];
	unset($vat, $vatIterator);
}

$usePageNavigation = true;
$navyParams = array();
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

$select = array();
$selectFields = array_keys($selectFields);
foreach ($selectFields as &$fieldName)
{
	if (isset($headerList[$fieldName]['external']))
		continue;
	if (!isset($headerList[$fieldName]['field']))
		$select[] = $fieldName;
	else
		$select[$fieldName] = $headerList[$fieldName]['field'];
}
unset($fieldName, $selectFields);
foreach ($hiddenSelectFields as $alias => $field)
	$select[$alias] = $field;
unset($alias, $field);

$getListParams = array(
	'select' => $select,
	'filter' => $filter,
	'order' => array($by => $order),
	'runtime' => array(
		'CATALOG_IBLOCK' => new Main\Entity\ReferenceField(
			'CATALOG_IBLOCK',
			'Bitrix\Catalog\CatalogIblock',
			array('=this.ID' => 'ref.IBLOCK_ID'),
			array('join_type' => 'LEFT')
		),
		'SKU' => new Main\Entity\ReferenceField(
			'SKU',
			'Bitrix\Catalog\CatalogIblock',
			array('=this.ID' => 'ref.PRODUCT_IBLOCK_ID'),
			array('join_type' => 'LEFT')
		)
	)
);
if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}
$totalPages = 0;
if ($usePageNavigation)
{
	$countQuery = new Main\Entity\Query(Iblock\IblockTable::getEntity());
	$countQuery->addSelect(new Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
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

$catalogIterator = new CAdminSubResult(Iblock\IblockTable::getList($getListParams), $adminListTableID, $adminList->GetListUrl(true));
if ($usePageNavigation)
{
	$catalogIterator->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$catalogIterator->NavRecordCount = $totalCount;
	$catalogIterator->NavPageCount = $totalPages;
	$catalogIterator->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$catalogIterator->NavStart();
}

$iblockList = array();
$skuList = array();
$rowList = array();
$adminList->NavText($catalogIterator->GetNavPrint(Loc::getMessage('SALE_DISCOUNT_COUPON_LIST_MESS_NAV')));
while ($catalog = $catalogIterator->Fetch())
{
	$catalog['ID'] = (int)$catalog['ID'];
	$catalog['IS_CATALOG'] = ((int)$catalog['IS_CATALOG'] > 0 ? 'Y' : 'N');
	$catalog['SKU_IBLOCK'] = (int)$catalog['SKU_IBLOCK'];
	if ($catalog['SKU_IBLOCK'] > 0)
		$skuList[$catalog['SKU_IBLOCK']] = $catalog['ID'];
	else
		$catalog['SKU_IBLOCK'] = '';
	$iblockList[] = $catalog['ID'];

	$urlEdit = $catalogEditUrl.$catalog['ID'];
	$rowList[$catalog['ID']] = $row = &$adminList->AddRow(
		$catalog['ID'],
		$catalog,
		$urlEdit,
		Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_LIST_MESS_EDIT_CATALOG'),
		true
	);

	$alert = '';
	$alertMessages = array();
	if (!$saleRecurring && $catalog['SUBSCRIPTION'] == 'Y')
		$alertMessages[] = Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_ALERT_SUBSCRIPTION_FEATURE');
	if ($catalog['SUBSCRIPTION'] == 'Y' && $catalog['SKU_IBLOCK'] > 0)
		$alertMessages[] = Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_ALERT_SUBSCRIPTION_WITH_SKU');
	if (!empty($alertMessages))
		$alert = '<span class="row-alert" title="'.htmlspecialcharsbx(implode(' ', $alertMessages)).'"></span>';
	$row->AddViewField('ID', $alert.$catalog['ID']);
	unset($alertMessages, $alert);

	if ($selectFieldsMap['NAME'])
		$row->AddViewField('NAME', $catalog['NAME']);
	if ($selectFieldsMap['IBLOCK_TYPE_ID'])
		$row->AddViewField('IBLOCK_TYPE_ID', $catalog['IBLOCK_TYPE_ID']);
	if ($selectFieldsMap['SORT'])
		$row->AddViewField('SORT', $catalog['SORT']);

	if ($selectFieldsMap['ACTIVE'])
		$row->AddCheckField('ACTIVE', false);
	if ($selectFieldsMap['IS_CATALOG'])
		$row->AddCheckField('IS_CATALOG', false);

	if ($catalog['IS_CATALOG'] == 'Y')
	{
		if ($saleRecurring && $selectFieldsMap['SUBSCRIPTION'])
			$row->AddCheckField('SUBSCRIPTION', false);
		if ($selectFieldsMap['YANDEX_EXPORT'])
			$row->AddCheckField('YANDEX_EXPORT', false);
		if ($selectFieldsMap['VAT_ID'])
			$row->AddViewField('VAT_ID', (isset($vatList[$catalog['VAT_ID']]) ? $vatList[$catalog['VAT_ID']] : $vatList[0]));
	}
	else
	{
		if ($saleRecurring && $selectFieldsMap['SUBSCRIPTION'])
			$row->AddViewField('SUBSCRIPTION', '');
		if ($selectFieldsMap['YANDEX_EXPORT'])
			$row->AddViewField('YANDEX_EXPORT', '');
		if ($selectFieldsMap['VAT_ID'])
			$row->AddViewField('VAT_ID', '');
	}

	$actions = array();
	$actions[] = array(
		'ICON' => 'edit',
		'TEXT' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_LIST_CONTEXT_EDIT'),
		'ACTION' => $adminList->getRowAction(CUtil::JSEscape($urlEdit)),
		'DEFAULT' => true
	);
	if (!$readOnly)
	{

	}
	$row->AddActions($actions);
	unset($actions);
}
if (isset($row))
	unset($row);

if (!empty($rowList))
{
	if ($selectFieldsMap['IBLOCK_SITES'])
	{
		$siteList = array();
		$sitesIterator = Iblock\IblockSiteTable::getList(array(
			'select' => array('IBLOCK_ID', 'SITE_ID'),
			'filter' => array('@IBLOCK_ID' => $iblockList)
		));
		while ($site = $sitesIterator->fetch())
		{
			if (!isset($siteList[$site['IBLOCK_ID']]))
				$siteList[$site['IBLOCK_ID']] = array();
			$siteList[$site['IBLOCK_ID']][] = $site['SITE_ID'];
		}
		unset($site, $sitesIterator);
		foreach ($siteList as $iblock => $sites)
			$rowList[$iblock]->AddViewField('IBLOCK_SITES', implode(' ', $sites));
		unset($iblock, $sites);
	}
	if ($selectFieldsMap['SKU_IBLOCK'] && !empty($skuList))
	{
		$iblocksIterator = Iblock\IblockTable::getList(array(
			'select' => array('ID', 'NAME'),
			'filter' => array('@ID' => array_keys($skuList))
		));
		while ($iblock = $iblocksIterator->fetch())
		{
			$parentIblock = $skuList[$iblock['ID']];
			$rowList[$parentIblock]->AddViewField('SKU_IBLOCK', '['.$iblock['ID'].'] '.$iblock['NAME']);
		}
		unset($parentIblock, $iblock, $iblocksIterator);
	}
}

$adminList->AddFooter(
	array(
		array(
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $catalogIterator->SelectedRowsCount()
		),
	)
);

if (!isset($_REQUEST["mode"]) || ($_REQUEST["mode"] != 'excel' && $_REQUEST["mode"] != 'subsettings'))
{
	$contextListMenu = array();

	$contextListMenu[] = array(
		'TEXT' => htmlspecialcharsex(Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_LIST_REFRESH')),
		'TITLE' => Loc::getMessage('CATALOG_ADM_IBLOCK_CATALOG_LIST_REFRESH_TITLE'),
		'ICON' => 'btn_sub_refresh',
		'LINK' => "javascript:".$adminList->ActionAjaxReload($adminList->GetListUrl(true)),
	);

	$adminList->AddAdminContextMenu($contextListMenu);
	unset($contextListMenu);
}

$adminList->CheckListMode();

$adminList->DisplayList(B_ADMIN_IBLOCK_CATALOGS_LIST);

if ($prologAbsent)
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_popup_admin.php');