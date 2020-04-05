<?
/** @global CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\SiteTable,
	Bitrix\Main\UserTable,
	Bitrix\Main\Config\Option,
	Bitrix\Sale\Internals;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

$saleModulePermissions = $APPLICATION->GetGroupRight('sale');
$readOnly = ($saleModulePermissions < 'W');
if ($saleModulePermissions < 'R')
	$APPLICATION->AuthForm('');

Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

$catalogNamePostfix = ' (' . Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_TITLE_CATALOG_ID') . ')';
$catalogNamePostfixLength = strlen($catalogNamePostfix);

$adminListTableID = 'tbl_sale_discount';

$adminSort = new CAdminSorting($adminListTableID, 'ID', 'ASC');
$adminList = new CAdminList($adminListTableID, $adminSort);

$filter = array();
$filterFields = array(
	'filter_id_from',
	'filter_id_to',
	'filter_lang',
	'filter_active',
	'filter_date_active_from',
	'filter_date_active_to',
	'filter_name',
	'filter_priority',
	'filter_last_discount',
	'filter_last_level_discount',
	'filter_xml_id',
	'filter_use_coupons'
);
$filterValues = array_fill_keys($filterFields, null);

$adminList->InitFilter($filterFields);

if (isset($filter_id_from) && is_string($filter_id_from))
{
	$filter_id_from = trim($filter_id_from);
	if ($filter_id_from !== '')
		$filterValues['filter_id_from'] = (int)$filter_id_from;
}
if (isset($filter_id_to) && is_string($filter_id_to))
{
	$filter_id_to = trim($filter_id_to);
	if ($filter_id_to !== '')
		$filterValues['filter_id_to'] = (int)$filter_id_to;
}

if (!empty($filter_lang))
{
	if (!is_array($filter_lang))
		$filter_lang = ($filter_lang == 'NOT_REF' ? array() : array($filter_lang));
	if (!empty($filter_lang))
		$filterValues['filter_lang'] = $filter_lang;
}

if (isset($filter_active) && is_string($filter_active))
{
	if ($filter_active === 'Y' || $filter_active === 'N')
		$filterValues['filter_active'] = $filter_active;
}

if (isset($filter_date_active_from) && is_string($filter_date_active_from))
{
	$filter_date_active_from = trim($filter_date_active_from);
	if ($filter_date_active_from !== '')
	{
		$correct = Main\Type\DateTime::isCorrect($filter_date_active_from);
		if (!$correct)
			$correct = Main\Type\Date::isCorrect($filter_date_active_from);
		if ($correct)
			$filterValues['filter_date_active_from'] = Main\Type\DateTime::createFromUserTime($filter_date_active_from);
		unset($correct);
	}
}
if (isset($filter_date_active_to) && is_string($filter_date_active_to))
{
	$filter_date_active_to = trim($filter_date_active_to);
	if ($filter_date_active_to !== '')
	{
		$correct = Main\Type\DateTime::isCorrect($filter_date_active_to);
		if (!$correct)
			$correct = Main\Type\Date::isCorrect($filter_date_active_to);
		if ($correct)
			$filterValues['filter_date_active_to'] = Main\Type\DateTime::createFromUserTime($filter_date_active_to);
		unset($correct);
	}
}

if (isset($filter_name) && is_string($filter_name))
{
	$filter_name = trim($filter_name);
	if ($filter_name !== '')
		$filterValues['filter_name'] = $filter_name;
}

if (isset($filter_priority) && is_string($filter_priority))
{
	$filter_priority = trim($filter_priority);
	if ($filter_priority !== '')
		$filterValues['filter_priority'] = (int)$filter_priority;
}

if (isset($filter_last_discount) && is_string($filter_last_discount))
{
	if ($filter_last_discount === 'Y' || $filter_last_discount === 'N')
		$filterValues['filter_last_discount'] = $filter_last_discount;
}
if (isset($filter_last_level_discount) && is_string($filter_last_level_discount))
{
	if ($filter_last_level_discount === 'Y' || $filter_last_level_discount === 'N')
		$filterValues['filter_last_level_discount'] = $filter_last_level_discount;
}

if (isset($filter_xml_id) && is_string($filter_xml_id))
{
	$filter_xml_id = trim($filter_xml_id);
	if ($filter_xml_id !== '')
		$filterValues['filter_xml_id'] = $filter_xml_id;
}

if (isset($filter_use_coupons) && is_string($filter_use_coupons))
{
	if ($filter_use_coupons === 'Y' || $filter_use_coupons === 'N')
		$filterValues['filter_use_coupons'] = $filter_use_coupons;
}

if ($filterValues['filter_id_from'] !== null || $filterValues['filter_id_to'] !==  null)
{
	if ($filterValues['filter_id_from'] === $filterValues['filter_id_to'])
	{
		$filter['=ID'] = $filterValues['filter_id_from'];
	}
	else
	{
		if ($filterValues['filter_id_from'] !== null)
			$filter['>=ID'] = $filterValues['filter_id_from'];
		if ($filterValues['filter_id_to'] !== null)
			$filter['<=ID'] = $filterValues['filter_id_to'];
	}
}

if (!empty($filterValues['filter_lang']))
	$filter["@LID"] = $filterValues['filter_lang'];

if ($filterValues['filter_active'] !== null)
	$filter['=ACTIVE'] = $filterValues['filter_active'];

if ($filterValues['filter_date_active_from'] !== null)
{
	$filter[] = array(
		'LOGIC' => 'OR',
		'ACTIVE_FROM' => '',
		'<=ACTIVE_FROM' => $filterValues['filter_date_active_from']
	);
}
if ($filterValues['filter_date_active_to'] !== null)
{
	$filter[] = array(
		'LOGIC' => 'OR',
		'ACTIVE_TO' => '',
		'>=ACTIVE_TO' => $filterValues['filter_date_active_to']
	);
}

if ($filterValues['filter_name'] !== null)
{
	$nameFilter = $filterValues['filter_name'];
	if (substr($nameFilter, -$catalogNamePostfixLength) == $catalogNamePostfix)
		$nameFilter = substr($nameFilter, 0, -$catalogNamePostfixLength);
	$filter['%NAME'] = $nameFilter;
	unset($nameFilter);
}

if ($filterValues['filter_priority'] !== null)
	$filter['=PRIORITY'] = $filterValues['filter_priority'];

if ($filterValues['filter_last_discount'] !== null)
	$filter['=LAST_DISCOUNT'] = $filterValues['filter_last_discount'];
if ($filterValues['filter_last_level_discount'] !== null)
	$filter['=LAST_LEVEL_DISCOUNT'] = $filterValues['filter_last_level_discount'];

if ($filterValues['filter_xml_id'] !== null)
	$filter['=XML_ID'] = $filterValues['filter_xml_id'];

if ($filterValues['filter_use_coupons'] !== null)
	$filter['=USE_COUPONS'] = $filterValues['filter_use_coupons'];

$presetFilter = '';
if (isset($_REQUEST['filter_preset_id']) && is_string($_REQUEST['filter_preset_id']))
{
	$presetId = trim($_REQUEST['filter_preset_id']);
	if ($presetId !== '')
	{
		$filter['=PRESET_ID'] = $presetId;
		$presetFilter = 'filter_preset_id=' . htmlspecialcharsbx($presetId);
	}
	unset($presetId);
}

if (!$readOnly && $adminList->EditAction())
{
	if (isset($FIELDS) && is_array($FIELDS))
	{
		$conn = Application::getConnection();
		foreach ($FIELDS as $ID => $fields)
		{
			$ID = (int)$ID;
			if ($ID <= 0 || !$adminList->IsUpdated($ID))
				continue;

			if (isset($fields['ACTIVE_FROM']) && is_string($fields['ACTIVE_FROM']))
			{
				$fields['ACTIVE_FROM'] = trim($fields['ACTIVE_FROM']);
				$fields['ACTIVE_FROM'] = (
					$fields['ACTIVE_FROM'] === ''
					? null
					: Main\Type\DateTime::createFromUserTime($fields['ACTIVE_FROM'])
				);
			}

			if (isset($fields['ACTIVE_TO']) && is_string($fields['ACTIVE_TO']))
			{
				$fields['ACTIVE_TO'] = trim($fields['ACTIVE_TO']);
				$fields['ACTIVE_TO'] = (
					$fields['ACTIVE_TO'] === ''
					? null
					: Main\Type\DateTime::createFromUserTime($fields['ACTIVE_TO'])
				);
			}

			$conn->startTransaction();
			$result = Internals\DiscountTable::update($ID, $fields);
			if ($result->isSuccess())
			{
				$conn->commitTransaction();
			}
			else
			{
				$conn->rollbackTransaction();
				$adminList->AddUpdateError(implode('<br>', $result->getErrorMessages()), $ID);
			}
		}
		unset($fields, $ID);
	}
}

if (!$readOnly && ($listID = $adminList->GroupAction()))
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$listID = array();
		$discountIterator = Internals\DiscountTable::getList(array(
			'select' => array('ID'),
			'filter' => $filter
		));
		while ($discount = $discountIterator->fetch())
			$listID[] = $discount['ID'];
	}

	$listID = array_filter($listID);
	if (!empty($listID))
	{
		switch ($_REQUEST['action'])
		{
			case 'activate':
			case 'deactivate':
				$fields = array(
					'ACTIVE' => ($_REQUEST['action'] == 'activate' ? 'Y' : 'N')
				);
				foreach ($listID as &$discountID)
				{
					$result = Internals\DiscountTable::update($discountID, $fields);
					if (!$result->isSuccess())
						$adminList->AddGroupError(implode('<br>', $result->getErrorMessages()), $discountID);
					unset($result);
				}
				unset($discountID, $fields);
				break;
			case 'delete':
				foreach ($listID as &$discountID)
				{
					$result = Internals\DiscountTable::delete($discountID);
					if (!$result->isSuccess())
						$adminList->AddGroupError(implode('<br>', $result->getErrorMessages()), $discountID);
					unset($result);
				}
				unset($discountID);
				break;
		}
	}
	unset($listID);
}

$headerList = array();
$headerList['ID'] = array(
	'id' => 'ID',
	'content' => 'ID',
	'title' => '',
	'sort' => 'ID',
	'default' => true
);
$headerList['LID'] = array(
	'id' => 'LID',
	'content' => Loc::getMessage('PERS_TYPE_LID'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_LID'),
	'sort' => 'LID',
	'default' => true
);
$headerList['NAME'] = array(
	'id' => 'NAME',
	'content' => Loc::getMessage('BT_SALE_DISCOUNT_ADM_TITLE_NAME'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_NAME'),
	'default' => true
);
$headerList['ACTIVE'] = array(
	'id' => 'ACTIVE',
	'content' => Loc::getMessage('PERS_TYPE_ACTIVE'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_ACTIVE'),
	'sort' => 'ACTIVE',
	'default' => true
);
$headerList['PRIORITY'] = array(
	'id' => 'PRIORITY',
	'content' => Loc::getMessage('SDSN_PRIORITY'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_PRIORITY'),
	'sort' => 'PRIORITY',
	'default' => true
);
$headerList['SORT'] = array(
	'id' => 'SORT',
	'content' => Loc::getMessage("PERS_TYPE_SORT"),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_SORT'),
	'sort' => 'SORT',
	'default' => true
);
$headerList['LAST_DISCOUNT'] = array(
	'id' => 'LAST_DISCOUNT',
	'content' => Loc::getMessage('SDSN_LAST_DISCOUNT_NEW'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_LAST_DISCOUNT'),
	'sort' => 'LAST_DISCOUNT',
	'default' => true
);
$headerList['LAST_LEVEL_DISCOUNT'] = array(
	'id' => 'LAST_LEVEL_DISCOUNT',
	'content' => Loc::getMessage('SDSN_LAST_LEVEL_DISCOUNT_NEW'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_LAST_LEVEL_DISCOUNT'),
	'sort' => 'LAST_LEVEL_DISCOUNT',
	'default' => true
);
$headerList['EXECUTE_MODULE'] = array(
	'id' => 'EXECUTE_MODULE',
	'content' => Loc::getMessage('SDSN_SHOW_IN_CATALOG'),
	'title' => "",
	'sort' => 'EXECUTE_MODULE',
	'default' => true
);
$headerList['ACTIVE_FROM'] = array(
	'id' => 'ACTIVE_FROM',
	'content' => Loc::getMessage("SDSN_ACTIVE_FROM"),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_ACTIVE_FROM'),
	'sort' => 'ACTIVE_FROM',
	'default' => true
);
$headerList['ACTIVE_TO'] = array(
	'id' => 'ACTIVE_TO',
	'content' => Loc::getMessage("SDSN_ACTIVE_TO"),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_ACTIVE_TO'),
	'sort' => 'ACTIVE_TO',
	'default' => true
);
$headerList['MODIFIED_BY'] = array(
	'id' => 'MODIFIED_BY',
	'content' => Loc::getMessage('SDSN_MODIFIED_BY_NEW'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_MODIFIED_BY'),
	'sort' => 'MODIFIED_BY',
	'default' => true
);
$headerList['TIMESTAMP_X'] = array(
	'id' => 'TIMESTAMP_X',
	'content' => Loc::getMessage('SDSN_TIMESTAMP_X'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_TIMESTAMP_X'),
	'sort' => 'TIMESTAMP_X',
	'default' => true
);
$headerList['CREATED_BY'] = array(
	'id' => 'CREATED_BY',
	'content' => Loc::getMessage('SDSN_CREATED_BY_NEW'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_CREATED_BY'),
	'sort' => 'CREATED_BY',
	'default' => false
);
$headerList['DATE_CREATE'] = array(
	'id' => 'DATE_CREATE',
	'content' => Loc::getMessage('SDSN_DATE_CREATE'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_DATE_CREATE'),
	'sort' => 'DATE_CREATE',
	'default' => false
);
$headerList['XML_ID'] = array(
	'id' => 'XML_ID',
	'content' => Loc::getMessage('SDSN_XML_ID'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_XML_ID'),
	'sort' => 'XML_ID',
	'default' => false
);
$headerList['USE_COUPONS'] = array(
	'id' => 'USE_COUPONS',
	'content' => Loc::getMessage('SDSN_USE_COUPONS'),
	'title' => Loc::getMessage('BX_SALE_ADM_DSC_HEADER_TITLE_USE_COUPONS'),
	'sort' => 'USE_COUPONS',
	'default' => true
);

if (Option::get('sale', 'use_sale_discount_only') !== 'Y')
{
	unset($headerList['EXECUTE_MODULE']);
}

$adminList->AddHeaders($headerList);

$selectFields = array_fill_keys($adminList->GetVisibleHeaderColumns(), true);
$selectFields['ID'] = true;
$selectFieldsMap = array_fill_keys(array_keys($headerList), false);

$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);
$selectFields['ACTIVE'] = true;
$selectFields['PRESET_ID'] = true;
$selectFields['ACTIONS_LIST'] = true;

$filterSiteList = array();
$arSitesShop = array();
$arSitesTmp = array();
$siteList = array();
$siteIterator = SiteTable::getList(array(
	'select' => array('LID', 'NAME', 'ACTIVE', 'SORT'),
	'order' => array('SORT' => 'ASC')
));
while ($site = $siteIterator->fetch())
{
	$filterSiteList[] = $site;
	$siteList[$site['LID']] = $site['LID'];
	if ($site['ACTIVE'] != 'Y')
		continue;
	$arSitesTmp[] = array(
		'ID' => $site['LID'],
		'NAME' => $site['NAME']
	);
	$saleSite = (string)Option::get('sale', 'SHOP_SITE_'.$site['LID']);
	if ($site['LID'] == $site)
	{
		$arSitesShop[] = array(
			'ID' => $site['LID'],
			'NAME' => $site['NAME']
		);
	}
}
unset($site, $siteIterator);
if (empty($arSitesShop))
{
	$arSitesShop = $arSitesTmp;
}
unset($arSitesTmp);

if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

$usePageNavigation = true;
$navyParams = array();
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
{
	$usePageNavigation = false;
}
else
{
	$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($adminListTableID));
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
$getListParams = array(
	'select' => array_keys($selectFields),
	'filter' => $filter,
	'order' => array($by => $order)
);
if(Option::get('sale', 'use_sale_discount_only', false) === 'Y' && Loader::includeModule('catalog'))
{
	$getListParams['runtime'] = array(
		new Main\Entity\ReferenceField(
			"CATALOG_DISCOUNT",
			'Bitrix\Catalog\DiscountTable',
			array("=this.ID" => "ref.SALE_ID")
		)
	);
	$getListParams['select']['CATALOG_DISCOUNT_ID'] = 'CATALOG_DISCOUNT.ID';
}

if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}
$totalCount = 0;
$totalPages = 0;
if ($usePageNavigation)
{
	$countQuery = new Main\Entity\Query(Internals\DiscountTable::getEntity());
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
	}
	else
	{
		$navyParams['PAGEN'] = 1;
	}
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

$discountIterator = new CAdminResult(Internals\DiscountTable::getList($getListParams), $adminListTableID);
if ($usePageNavigation)
{
	$discountIterator->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$discountIterator->NavRecordCount = $totalCount;
	$discountIterator->NavPageCount = $totalPages;
	$discountIterator->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$discountIterator->NavStart();
}

$adminList->NavText($discountIterator->GetNavPrint(Loc::getMessage("BT_SALE_DISCOUNT_LIST_MESS_NAV")));

$userList = array();
$arUserID = array();
$nameFormat = CSite::GetNameFormat(true);

function canShowDiscountInCatalog(array $discount)
{
	if(
		isset($discount['USE_COUPONS']) && $discount['USE_COUPONS'] === 'N' &&
		($discount['EXECUTE_MODULE'] == 'all' || $discount['EXECUTE_MODULE'] == 'catalog')
	)
	{
		if (empty($discount['ACTIONS_LIST']) || empty($discount['ACTIONS_LIST']['CHILDREN']))
		{
			return true;
		}

		$actionConfiguration = \Bitrix\Sale\Discount\Actions::getActionConfiguration($discount);
		if (!$actionConfiguration ||
			$actionConfiguration['VALUE_TYPE'] === \Bitrix\Sale\Discount\Actions::VALUE_TYPE_SUMM)
		{
			return false;
		}

		if ($actionConfiguration['TYPE'] == 'Extra')
		{
			return false;
		}

		return true;
	}

	//in basket
	return false;
}

$arRows = array();
while ($discount = $discountIterator->Fetch())
{
	$discount['ID'] = (int)$discount['ID'];

	if ($selectFieldsMap['CREATED_BY'])
	{
		$discount['CREATED_BY'] = (int)$discount['CREATED_BY'];
		if ($discount['CREATED_BY'] > 0)
			$arUserID[$discount['CREATED_BY']] = true;
	}
	if ($selectFieldsMap['MODIFIED_BY'])
	{
		$discount['MODIFIED_BY'] = (int)$discount['MODIFIED_BY'];
		if ($discount['MODIFIED_BY'] > 0)
			$arUserID[$discount['MODIFIED_BY']] = true;
	}

	$urlEdit = 'sale_discount_edit.php?ID='.$discount['ID'].'&lang='.LANGUAGE_ID.GetFilterParams('filter_');
	if($discount['PRESET_ID'])
	{
		$urlEdit = 'sale_discount_preset_detail.php?DISCOUNT_ID='.$discount['ID'].'&from_list=discount&lang='.LANGUAGE_ID;
	}

	if ($selectFieldsMap['EXECUTE_MODULE'])
	{
		if(canShowDiscountInCatalog($discount))
		{
			//in catalog
			$discount['EXECUTE_MODULE'] = 'Y';
		}
		else
		{
			//in basket
			$discount['EXECUTE_MODULE'] = 'N';
		}
	}

	$arRows[$discount['ID']] = $row = &$adminList->AddRow(
		$discount['ID'],
		$discount,
		$urlEdit,
		Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_EDIT_DISCOUNT')
	);
	$row->AddViewField('ID', '<a href="'.$urlEdit.'">'.$discount['ID'].'</a>');

	if ($selectFieldsMap['DATE_CREATE'])
		$row->AddViewField('DATE_CREATE', $discount['DATE_CREATE']);
	if ($selectFieldsMap['TIMESTAMP_X'])
		$row->AddViewField('TIMESTAMP_X', $discount['TIMESTAMP_X']);
	if ($selectFieldsMap['USE_COUPONS'])
		$row->AddCheckField('USE_COUPONS', false);

	if (!empty($discount['CATALOG_DISCOUNT_ID']))
	{
		if ($selectFieldsMap['NAME'])
			$row->AddViewField('NAME', htmlspecialcharsbx($discount['NAME'].$catalogNamePostfix));
	}
	if (!$readOnly)
	{
		if ($selectFieldsMap['LID'])
			$row->AddViewField('LID', $siteList[$discount['LID']]);
		if ($selectFieldsMap['ACTIVE'])
			$row->AddCheckField('ACTIVE');

		if ($selectFieldsMap['NAME'])
			$row->AddInputField('NAME', array('size' => 50, 'maxlength' => 255));

		if ($selectFieldsMap['SORT'])
			$row->AddInputField('SORT', array('size' => 4));

		if ($selectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField('ACTIVE_FROM', array(), true);
		if ($selectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField('ACTIVE_TO', array(), true);

		if ($selectFieldsMap['PRIORITY'])
			$row->AddInputField('PRIORITY');
		if ($selectFieldsMap['LAST_DISCOUNT'])
			$row->AddCheckField('LAST_DISCOUNT');
		if ($selectFieldsMap['LAST_LEVEL_DISCOUNT'])
			$row->AddCheckField('LAST_LEVEL_DISCOUNT');
		if ($selectFieldsMap['EXECUTE_MODULE'])
			$row->AddCheckField('EXECUTE_MODULE', false);

		if ($selectFieldsMap['XML_ID'])
			$row->AddInputField('XML_ID', array('size' => 20, 'maxlength' => 255));
	}
	else
	{
		if ($selectFieldsMap['LID'])
			$row->AddViewField('LID', $siteList[$discount['LID']]);
		if ($selectFieldsMap['ACTIVE'])
			$row->AddCheckField('ACTIVE', false);

		if ($selectFieldsMap['NAME'])
			$row->AddInputField('NAME', false);

		if ($selectFieldsMap['SORT'])
			$row->AddInputField('SORT', false);

		if ($selectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField('ACTIVE_FROM', false);
		if ($selectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField('ACTIVE_TO', false);

		if ($selectFieldsMap['PRIORITY'])
			$row->AddInputField('PRIORITY', false);
		if ($selectFieldsMap['LAST_DISCOUNT'])
			$row->AddCheckField('LAST_DISCOUNT', false);
		if ($selectFieldsMap['LAST_LEVEL_DISCOUNT'])
			$row->AddCheckField('LAST_LEVEL_DISCOUNT', false);
		if ($selectFieldsMap['EXECUTE_MODULE'])
			$row->AddCheckField('EXECUTE_MODULE', false);

		if ($selectFieldsMap['XML_ID'])
			$row->AddInputField('XML_ID', false);
	}

	$arActions = array();
	$arActions[] = array(
		'ICON' => 'edit',
		'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_EDIT_DISCOUNT_SHORT'),
		'ACTION' => $adminList->ActionRedirect($urlEdit),
		'DEFAULT' => true
	);
	if (!$readOnly)
	{
		if(empty($discount['PRESET_ID']))
		{
			$arActions[] = array(
				'ICON' => 'copy',
				'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_COPY_DISCOUNT_SHORT'),
				'ACTION' => $adminList->ActionRedirect($urlEdit.'&action=copy'),
				'DEFAULT' => false,
			);
		}
		if ($discount['ACTIVE'] == 'Y')
		{
			$arActions[] = array(
				'ICON' => 'deactivate',
				'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_DEACTIVATE_DISCOUNT_SHORT'),
				'ACTION' => $adminList->ActionDoGroup($discount['ID'], 'deactivate'),
				'DEFAULT' => false,
			);
		}
		else
		{
			$arActions[] = array(
				'ICON' => 'activate',
				'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_ACTIVATE_DISCOUNT_SHORT'),
				'ACTION' => $adminList->ActionDoGroup($discount['ID'], 'activate'),
				'DEFAULT' => false,
			);
		}
		$arActions[] = array('SEPARATOR' => true);
		$arActions[] = array(
			'ICON' => 'delete',
			'TEXT' => Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_DELETE_DISCOUNT_SHORT'),
			'ACTION' => "if(confirm('".Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_DELETE_DISCOUNT_CONFIRM')."')) ".$adminList->ActionDoGroup($discount['ID'], 'delete'),
			'DEFAULT' => false,
		);
	}

	$row->AddActions($arActions);
}
if (isset($row))
	unset($row);

if ($selectFieldsMap['CREATED_BY'] || $selectFieldsMap['MODIFIED_BY'])
{
	if (!empty($arUserID))
	{
		$userIterator = UserTable::getList(array(
			'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'),
			'filter' => array('ID' => array_keys($arUserID)),
		));
		while ($arOneUser = $userIterator->fetch())
		{
			$arOneUser['ID'] = (int)$arOneUser['ID'];
			$userList[$arOneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arOneUser['ID'].'">'.CUser::FormatName($nameFormat, $arOneUser).'</a>';
		}
		unset($arOneUser, $userIterator);
	}

	foreach ($arRows as &$row)
	{
		if ($selectFieldsMap['CREATED_BY'])
		{
			$strCreatedBy = '';
			if ($row->arRes['CREATED_BY'] > 0 && isset($userList[$row->arRes['CREATED_BY']]))
			{
				$strCreatedBy = $userList[$row->arRes['CREATED_BY']];
			}
			$row->AddViewField("CREATED_BY", $strCreatedBy);
		}
		if ($selectFieldsMap['MODIFIED_BY'])
		{
			$strModifiedBy = '';
			if ($row->arRes['MODIFIED_BY'] > 0 && isset($userList[$row->arRes['MODIFIED_BY']]))
			{
				$strModifiedBy = $userList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		}
	}
	if (isset($row))
		unset($row);
}

$adminList->AddFooter(
	array(
		array(
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $discountIterator->SelectedRowsCount()
		),
		array(
			'counter' => true,
			'title' => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
			'value' => "0"
		),
	)
);

$adminList->AddGroupActionTable(
	array(
		"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

if (!$readOnly && !isset($filter['=PRESET_ID']))
{
	$siteLID = '';
	$arSiteMenu = array();

	if (count($arSitesShop) == 1)
	{
		$siteLID = "&LID=".$arSitesShop[0]['ID'];
	}
	else
	{
		foreach ($arSitesShop as $val)
		{
			$arSiteMenu[] = array(
				"TEXT" => $val["NAME"]." (".$val['ID'].")",
				"ACTION" => "window.location = 'sale_discount_edit.php?lang=".LANGUAGE_ID."&LID=".$val['ID']."';"
			);
		}
	}
	$aContext = array(
		array(
			"TEXT" => Loc::getMessage("BT_SALE_DISCOUNT_LIST_MESS_NEW_DISCOUNT"),
			"ICON" => "btn_new",
			"LINK" => "sale_discount_edit.php?lang=".LANGUAGE_ID.$siteLID,
			"TITLE" => Loc::getMessage("BT_SALE_DISCOUNT_LIST_MESS_NEW_DISCOUNT_TITLE"),
			"MENU" => $arSiteMenu
		),
	);

	$adminList->AddAdminContextMenu($aContext);
}

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('BT_SALE_DISCOUNT_LIST_MESS_TITLE'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$adminListTableID.'_filter',
	array(
		'ID',
		Loc::getMessage('LANG_FILTER_NAME'),
		Loc::getMessage('FILTER_ACTIVE'),
		Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_DATE'),
		Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_NAME'),
		Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_PRIORITY'),
		Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_LAST_DISCOUNT'),
		Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_LAST_LEVEL_DISCOUNT'),
		Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_XML_ID'),
		Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_USE_COUPONS')
	)
);

$oFilter->Begin();
?>
	<tr>
		<td>ID:</td>
		<td>
			<?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_MESS_RANGE_FROM');?>
			<input id="filter_id_from" type="text" name="filter_id_from" value="<?=htmlspecialcharsbx($filterValues['filter_id_from']); ?>" size="6">
			<?echo Loc::getMessage("BX_SALE_DISCOUNT_LIST_MESS_RANGE_TO");?>
			<input id="filter_id_to" type="text" name="filter_id_to" value="<?=htmlspecialcharsbx($filterValues['filter_id_to']); ?>" size="6">
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('LANG_FILTER_NAME')?>:</td>
		<td><?
			$siteSize = count($siteList);
			if ($siteSize > 10)
				$siteSize = 10;
			elseif ($siteSize < 3)
				$siteSize = 3;
			?><select name="filter_lang[]" multiple size="<?=$siteSize; ?>"><?
			$filterSite = is_array($filterValues['filter_lang']) ? $filterValues['filter_lang'] : array();
			foreach ($filterSiteList as $row)
			{
				?><option value="<?=$row['LID']; ?>"<?=(in_array($row['LID'], $filterSite) ? ' selected' : ''); ?>>[<?=$row['LID']; ?>]&nbsp;<?=htmlspecialcharsEx($row['NAME']); ?></option><?
			}
			unset($row);
			unset($filterSite);
			?></select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('FILTER_ACTIVE'); ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><? echo Loc::getMessage('DS_ALL'); ?></option>
				<option value="Y"<?=($filterValues['filter_active'] === 'Y' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_YES')) ?></option>
				<option value="N"<?=($filterValues['filter_active'] === 'N' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_NO')) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_DATE'); ?>:</td>
		<td>
			<?=CAdminCalendar::CalendarPeriod(
				'filter_date_active_from',
				'filter_date_active_to',
				$filterValues['filter_date_active_from'],
				$filterValues['filter_date_active_to'],
				true,
				10,
				false
			); ?>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_NAME'); ?>:</td>
		<td><input type="text" name="filter_name" value="<?=htmlspecialcharsbx($filterValues['filter_name']); ?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_PRIORITY'); ?>:</td>
		<td><input type="text" name="filter_priority" value="<?=htmlspecialcharsbx($filterValues['filter_priority']); ?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_LAST_DISCOUNT'); ?>:</td>
		<td>
			<select name="filter_last_discount">
				<option value=""><? echo Loc::getMessage('DS_ALL'); ?></option>
				<option value="Y"<?=($filterValues['filter_last_discount'] === 'Y' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_YES')) ?></option>
				<option value="N"<?=($filterValues['filter_last_discount'] === 'N' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_NO')) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_LAST_LEVEL_DISCOUNT'); ?>:</td>
		<td>
			<select name="filter_last_level_discount">
				<option value=""><? echo Loc::getMessage('DS_ALL'); ?></option>
				<option value="Y"<?=($filterValues['filter_last_level_discount'] === 'Y' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_YES')) ?></option>
				<option value="N"<?=($filterValues['filter_last_level_discount'] === 'N' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_NO')) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_XML_ID'); ?>:</td>
		<td><input type="text" name="filter_xml_id" value="<?=htmlspecialcharsbx($filterValues['filter_xml_id']); ?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('BX_SALE_DISCOUNT_LIST_FILTER_USE_COUPONS'); ?>:</td>
		<td>
			<select name="filter_use_coupons">
				<option value=""><? echo Loc::getMessage('DS_ALL'); ?></option>
				<option value="Y"<?=($filterValues['filter_use_coupons'] === 'Y' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_YES')) ?></option>
				<option value="N"<?=($filterValues['filter_use_coupons'] === 'N' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('DSC_NO')) ?></option>
			</select>
		</td>
	</tr>
	<?
$oFilter->Buttons(
	array(
		"table_id" => $adminListTableID,
		"url" => $APPLICATION->GetCurPage().'?'.$presetFilter,
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<script type="text/javascript">
function changeIdTo()
{
	var destination = BX('filter_id_to');

	if (this.value === '')
		return;
	if (!BX.type.isElementNode(destination))
		return;
	if (destination.value !== '')
		return;
	destination.value = this.value;
	destination = null;
}
BX.ready(function(){
	var control = BX('filter_id_from');
	if (!BX.type.isElementNode(control))
		return;
	BX.bind(control, 'change', changeIdTo);
});
</script>
<?
$adminList->DisplayList();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');