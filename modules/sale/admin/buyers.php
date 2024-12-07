<?php
use Bitrix\Main\Loader;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('sale');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

IncludeModuleLangFile(__FILE__);

/** @global CMain $APPLICATION */
/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	return;
}

$isWithOrdersMode = true;
if (Loader::includeModule('crm'))
{
	$isWithOrdersMode = CCrmSaleHelper::isWithOrdersMode();
}

ClearVars();

/*****************************************************************************/
/******************************** BUYERS *************************************/
/*****************************************************************************/

$APPLICATION->SetTitle(GetMessage("BUYER_TITLE"));

$rsSites = CSite::GetList("sort", "desc", array("ACTIVE" => "Y"));
$arSites = array();
while ($arSite = $rsSites->Fetch())
	$arSites[$arSite["ID"]] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);

$arUsersGroups = array();
$dbGroups = CGroup::GetList("c_sort", "asc", array("ANONYMOUS" => "N"));
while ($arGroups = $dbGroups->Fetch())
	$arUsersGroups[] = $arGroups;

$sTableID = "tbl_sale_buyers";
$oSort = new CAdminUiSorting($sTableID, "LAST_LOGIN", "desc");
global $by, $order;
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilter = array();

$listGroup = array();
$groupQueryObject = CGroup::getDropDownList("AND ID!=2");
while ($group = $groupQueryObject->fetch())
{
	$listGroup[$group["REFERENCE_ID"]] = $group["REFERENCE"];
}
$listCurrency = array();
$currencyList = Bitrix\Currency\CurrencyManager::getCurrencyList();
foreach ($currencyList as $currencyId => $currencyName)
{
	$listCurrency[$currencyId] = $currencyName;
}
$listSite = array();
$sitesQueryObject = CSite::getList("sort", "asc", array("ACTIVE" => "Y"));
while ($site = $sitesQueryObject->fetch())
{
	$listSite[$site["LID"]] = $site["NAME"]." [".$site["LID"]."]";
}

$filterFields = array(
	array(
		"id" => "BUYER",
		"name" => GetMessage('BUYER_ROW_BUYER'),
		"filterable" => "",
		"quickSearch" => "",
		"default" => true
	),
	array(
		"id" => "USER_ID",
		"name" => GetMessage('BUYER_F_ID'),
		"type" => "custom_entity",
		"selector" => array("type" => "user"),
		"filterable" => ""
	),
	array(
		"id" => "USER.LOGIN",
		"name" => GetMessage("BUYER_F_LOGIN"),
		"filterable" => ""
	),
	array(
		"id" => "USER.EMAIL",
		"name" => GetMessage("BUYER_F_MAIL"),
		"filterable" => ""
	),
	array(
		"id" => "USER.PERSONAL_PHONE",
		"name" => GetMessage("BUYER_F_PHONE"),
		"filterable" => "%"
	),
	array(
		"id" => "USER.LAST_LOGIN",
		"name" => GetMessage("BUYER_F_DATE_AUTH"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "SUM_PAID",
		"name" => GetMessage("BUYER_F_PAID_ALL"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "COUNT_FULL_PAID_ORDER",
		"name" => GetMessage("BUYER_F_QUANTITY_FULL"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "COUNT_PART_PAID_ORDER",
		"name" => GetMessage("BUYER_F_QUANTITY_PART"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "LAST_ORDER_DATE",
		"name" => GetMessage("BUYER_F_LAST_ORDER_DATE"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "USER.DATE_REGISTER",
		"name" => GetMessage("BUYER_ROW_DATE_REGISTER"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "GROUP.GROUP_ID",
		"name" => GetMessage("BUYER_F_GROUP"),
		"type" => "list",
		"items" => $listGroup,
		"params" => array("multiple" => "Y"),
		"filterable" => ""
	),
	array(
		"id" => "CURRENCY",
		"name" => GetMessage("BUYER_F_CURRENCY"),
		"type" => "list",
		"items" => $listCurrency,
		"filterable" => ""
	),
	array(
		"id" => "LID",
		"name" => GetMessage("BUYER_ORDERS_LID"),
		"type" => "list",
		"items" => $listSite,
		"filterable" => ""
	),
);

$filterPresets = array(
	"best_buyers" => array(
		"name" => GetMessage("BUYER_F_BEST")
	),
	"new_buyers" => array(
		"name" => GetMessage("BUYER_F_BUYERS_NEW")
	)
);
$lAdmin->setFilterPresets($filterPresets);

$lAdmin->AddFilter($filterFields, $arFilter);

if (isset($arFilter["BUYER"]) && $arFilter["BUYER"] <> '')
{
	$nameSearch = trim($arFilter["BUYER"]);
	$searchFilter = \Bitrix\Main\UserUtils::getAdminSearchFilter([
		'FIND' => $nameSearch
	]);

	$renameUserFields = function ($fields) use (&$renameUserFields)
	{
		$result = [];
		foreach ($fields as $key => $value)
		{
			if (is_array($value))
			{
				$result[$key] = $renameUserFields($value);
			}
			else
			{
				if (mb_strpos($key, 'INDEX') !== false)
				{
					$key = str_replace('INDEX', 'USER.INDEX', $key);
				}
				elseif ($key !== 'LOGIC')
				{
					$namePosition = mb_strpos($key, preg_replace('/^\W+/', '', $key));
					$key = mb_substr($key, 0, $namePosition)."USER.".mb_substr($key, $namePosition);
				}
				$result[$key] = $value;
			}
		}
		return $result;
	};

	$arFilter = array_merge($arFilter, $renameUserFields($searchFilter));
	unset($arFilter["BUYER"]);
}

$arSitesShop = array();
foreach ($arSites as $key => $val)
{
	$site = COption::GetOptionString("sale", "SHOP_SITE_".$key, "");
	if ($key == $site)
		$arSitesShop[] = array("ID" => $key, "NAME" => $val["NAME"]);
}
if (empty($arSitesShop))
	$arSitesShop = $arSites;

$arHeaders = array(
	array("id"=>"USER_ID", "content"=>"ID", "sort"=>"USER_ID"),
	array("id"=>"BUYER","content"=>GetMessage("BUYER_ROW_BUYER"), "sort"=>"NAME", "default"=>true),
	array("id"=>"LOGIN","content"=>GetMessage("BUYER_ROW_LOGIN"), "sort"=>"LOGIN"),
	array("id"=>"LAST_NAME","content"=>GetMessage("BUYER_ROW_LAST"), "sort"=>"LAST_NAME"),
	array("id"=>"NAME","content"=>GetMessage("BUYER_ROW_NAME"), "sort"=>"NAME"),
	array("id"=>"SECOND_NAME","content"=>GetMessage("BUYER_ROW_SECOND"), "sort"=>"SECOND_NAME"),
	array("id"=>"EMAIL","content"=>GetMessage("BUYER_ROW_MAIL"), "sort"=>"EMAIL", "default"=>true),
	array("id"=>"PERSONAL_PHONE","content"=>GetMessage("BUYER_ROW_PHONE"), "sort"=>"PERSONAL_PHONE", "default"=>true),
	array("id"=>"LAST_LOGIN","content"=>GetMessage('BUYER_ROW_LAST_LOGIN'), "sort"=>"LAST_LOGIN", "default"=>false),
	array("id"=>"DATE_REGISTER","content"=>GetMessage('BUYER_ROW_DATE_REGISTER'), "sort"=>"DATE_REGISTER", "default"=>true),
	array("id"=>"LAST_ORDER_DATE","content"=>GetMessage('BUYER_ROW_LAST_ORDER_DATE'), "sort"=>"LAST_ORDER_DATE", "default"=>false),
	array("id"=>"LID","content"=>GetMessage('BUYER_ROW_LID'), "default"=>true),
	array("id"=>"COUNT_FULL_PAID_ORDER","content"=>GetMessage('BUYER_ROW_COUNT_FULL_PAID_ORDER'), "sort"=>"COUNT_FULL_PAID_ORDER", "default"=>true, "align" => "right"),
	array("id"=>"COUNT_PART_PAID_ORDER","content"=>GetMessage('BUYER_ROW_COUNT_PART_PAID_ORDER'), "sort"=>"COUNT_PART_PAID_ORDER", "default"=>true, "align" => "right"),
	array("id"=>"SUM_PAID","content"=>GetMessage('BUYER_ROW_SUM_PAID'), "sort"=>"SUM_PAID", "default"=>true, "align" => "right"),
	array("id"=>"GROUPS_ID","content"=>GetMessage('BUYER_ROW_GROUP')),
);
$lAdmin->AddHeaders($arHeaders);
$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$userFields = [
	'DATE_REGISTER', 'LOGIN', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME',
	'PERSONAL_PHONE', 'LAST_LOGIN', 'PERSONAL_BIRTHDAY'
];
$orderFields = ['SUM_PAID', 'COUNT_FULL_PAID_ORDER', 'COUNT_PART_PAID_ORDER'];

$userIdList = [];

if ($publicMode && \Bitrix\Main\Loader::includeModule('crm'))
{
	$gridOptions = new \Bitrix\Main\Grid\Options($sTableID);
	$sorting = $gridOptions->getSorting(['sort' => ['NAME' => 'ASC']]);

	$by = key($sorting['sort']);
	$order = mb_strtoupper(current($sorting['sort'])) === 'ASC' ? 'ASC' : 'DESC';

	$sortByUserField = isset($by) && in_array($by, $userFields);

	if ($sortByUserField)
	{
		$userBy = $by;
	}
	elseif ($by === 'USER_ID')
	{
		$userBy = 'ID';
	}
	else
	{
		$userBy = 'NAME';
	}

	$filter = [
		'!=ID' => \Bitrix\Crm\Order\Manager::getAnonymousUserID(),
		'=GROUP.GROUP_ID' => \Bitrix\Crm\Order\BuyerGroup::getSystemGroupId(),
	];

	$filterOptions = new \Bitrix\Main\UI\Filter\Options($sTableID);
	$searchString = $filterOptions->getSearchString();

	if ($searchString !== '')
	{
		$searchFields = ['FIND' => $searchString];
		$filter = array_merge(\Bitrix\Main\UserUtils::getAdminSearchFilter($searchFields), $filter);
	}

	foreach ($arFilter as $key => $searchValue)
	{
		if (mb_strpos($key, 'USER.') === 0 || mb_strpos($key, '%USER.') === 0 || mb_strpos($key, '*USER.') === 0)
		{
			$field = str_replace('USER.', '', $key);
			$filter[$field] = $searchValue;
		}
	}

	$gridColumns = $gridOptions->getUsedColumns();
	$selectColumns = array_merge($gridColumns, ['ID', 'EXTERNAL_AUTH_ID']);
	$selectColumns = array_intersect($selectColumns, array_keys(\Bitrix\Main\UserTable::getEntity()->getFields()));

	$navyParams = CDBResult::GetNavParams(CAdminUiResult::GetNavSize($sTableID));
	$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
	$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];

	$groupReference = new \Bitrix\Main\Entity\ReferenceField(
		'GROUP',
		'\Bitrix\Main\UserGroupTable',
		['=ref.USER_ID' => 'this.ID'],
		['join_type' => 'LEFT']
	);

	$query = (new \Bitrix\Main\Entity\Query(\Bitrix\Main\UserTable::getEntity()))
		->registerRuntimeField('', $groupReference)
		->addFilter('!=ID', \Bitrix\Crm\Order\Manager::getAnonymousUserID())
		->addFilter('=GROUP.GROUP_ID', \Bitrix\Crm\Order\BuyerGroup::getSystemGroupId())
		->countTotal(true);

	$totalCount = $query->exec()->getCount();

	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount / $navyParams['SIZEN']);

		if ($navyParams['PAGEN'] > $totalPages)
		{
			$navyParams['PAGEN'] = $totalPages;
		}

		$navLimit = $navyParams['SIZEN'];
		$navOffset = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
	}
	else
	{
		$totalPages = 0;
		$navyParams['PAGEN'] = 1;
		$navLimit = $navyParams['SIZEN'];
		$navOffset = 0;
	}

	$buyersData = \Bitrix\Main\UserTable::getList([
		'select' => $selectColumns,
		'filter' => $filter,
		'order' => [$userBy => $order],
		'offset' => $navOffset,
		'limit' => $navLimit,
		'runtime' => [$groupReference],
	]);
	while ($user = $buyersData->Fetch())
	{
		$userIdList[] = $user['ID'];
	}

	$sortByOrderField = isset($by) && in_array($by, $orderFields);

	if ($sortByOrderField)
	{
		$order = [$by => $order];
	}
	else
	{
		$order = ['ID' => 'ASC'];
	}

	$dbUsersOrderData = \Bitrix\Sale\BuyerStatistic::getList([
		'filter' => [
			'USER_ID' => $userIdList
		],
		'order' => $order
	])->fetchAll();
	$dbUsersOrderData = array_column($dbUsersOrderData, null, 'USER_ID');

	if ($sortByOrderField)
	{
		$userIdList = array_unique(array_merge(array_keys($dbUsersOrderData), $userIdList));
	}

	$userOrderData = [];

	$defaultUsersOrderData = array_fill_keys($userIdList, [
		'SUM_PAID' => 0,
		'COUNT_FULL_PAID_ORDER' => 0,
		'COUNT_PART_PAID_ORDER' => 0,
		'CURRENCY' => Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency(SITE_ID),
	]);

	foreach ($defaultUsersOrderData as $userId => $userData)
	{
		$userOrderData[$userId] = isset($dbUsersOrderData[$userId]) ? array_merge($userData, $dbUsersOrderData[$userId]) : $userData;
	}
}
else
{
	$buyersFilter = [];
	$buyersFilter['filter'] = $arFilter;
	$buyersFilter['select'] = array('LID', 'CURRENCY');

	foreach ($arVisibleColumns as $column)
	{
		if ($column === 'BUYER')
		{
			$buyersFilter['select'][] = "USER_ID";
			$buyersFilter['select']['NAME'] = "USER.NAME";
			$buyersFilter['select']['LAST_NAME'] = "USER.LAST_NAME";
			$buyersFilter['select']['EMAIL'] = "USER.EMAIL";
		}
		elseif (in_array($column, $userFields))
		{
			$columnUserName = "USER.".$column;
			$buyersFilter['select'][$column] = $columnUserName;
		}
		elseif ($column === 'COUNT_ORDER')
		{
			$buyersFilter['select'][] = 'COUNT_FULL_PAID_ORDER';
		}
		elseif ($column !== 'GROUPS_ID')
		{
			$buyersFilter['select'][] = $column;
		}
	}

	$order = isset($order) ? $order : "ASC";
	if (in_array($by, $userFields))
	{
		$by = "USER.$by";
	}
	elseif ($by === 'COUNT_ORDER')
	{
		$by = 'COUNT_FULL_PAID_ORDER';
	}
	else
	{
		$by = "USER.NAME";
	}
	$buyersFilter['order'] = array($by => $order);

	$buyersData = \Bitrix\Sale\BuyerStatistic::getList($buyersFilter);

	while($buyer = $buyersData->fetch())
	{
		$userIdList[] = $buyer['USER_ID'];
	}
}

if (!empty($userIdList) && is_array($userIdList))
{
	$buyerNames = GetFormatedUserName($userIdList, false, !$publicMode);
}

$resultUsersList = new CAdminUiResult($buyersData, $sTableID);

if (isset($navyParams, $navLimit))
{
	$resultUsersList->NavStart($navLimit, $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$resultUsersList->NavRecordCount = $totalCount;
	$resultUsersList->NavPageCount = $totalPages;
	$resultUsersList->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$resultUsersList->NavStart();
}

$lAdmin->SetNavigationParams($resultUsersList, array("BASE_LINK" => $selfFolderUrl."sale_buyers.php"));
$isIntranetInstalled = IsModuleInstalled('intranet');
$isCrmInstalled = IsModuleInstalled('crm');

while ($arBuyers = $resultUsersList->Fetch())
{
	$userId = isset($arBuyers["USER_ID"]) ? $arBuyers["USER_ID"] : $arBuyers["ID"];

	if (isset($userOrderData[$userId]))
	{
		$arBuyers += $userOrderData[$userId];
	}

	$profileUrl = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$userId."&lang=".LANGUAGE_ID;
	$profileUrl = $adminSidePanelHelper->editUrlToPublicPage($profileUrl);

	if(
		$publicMode
		&& $isIntranetInstalled
		&& $isCrmInstalled
		&& $arBuyers['EXTERNAL_AUTH_ID'] !== \Bitrix\Crm\Order\Buyer::AUTH_ID
	)
	{
		$editUrl = '/company/personal/user/'.$userId.'/';
	}
	else
	{
		$editUrl = '/shop/buyer/'.$userId.'/edit/';
	}

	$row =& $lAdmin->AddRow($userId, $arBuyers, $profileUrl, GetMessage("BUYER_SUB_ACTION_PROFILE"));

	$profile = '<a href="'.$profileUrl.'">'.$userId.'</a>';
	$row->AddField("USER_ID", $profile);

	if (in_array("SUM_PAID", $arVisibleColumns))
		$row->AddField("SUM_PAID", SaleFormatCurrency($arBuyers["SUM_PAID"], $arBuyers["CURRENCY"]));

	if (in_array("GROUPS_ID", $arVisibleColumns))
	{
		$strUserGroup = '';
		$arUserGroups = CUser::GetUserGroup($userId);

		foreach ($arUsersGroups as $arGroup)
		{
			if (in_array($arGroup["ID"], $arUserGroups))
				$strUserGroup .= htmlspecialcharsbx($arGroup["NAME"])."<br>";
		}
		$row->AddField("GROUPS_ID", $strUserGroup);
	}

	if (in_array("LID", $arVisibleColumns))
	{
		$buyerLidId = null;
		if (isset($arBuyers['LID']))
		{
			$buyerLidId = $arBuyers['LID'];
		}
		else if (isset($userOrderData[$userId]))
		{
			$buyerLidId = $userOrderData[$userId]['LID'] ?? null;
		}
		else if (defined('SITE_ID'))
		{
			$buyerLidId = SITE_ID;
		}

		$row->AddField('LID', htmlspecialcharsbx((string)($arSites[$buyerLidId]['NAME'] ?? '')));
	}

	/*BUYER*/
	$fieldBuyer = $buyerNames[$userId];
	$row->AddField("BUYER", $fieldBuyer);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "view",
		"TEXT" => GetMessage("BUYER_SUB_ACTION_PROFILE"),
		"LINK" => $profileUrl,
		"DEFAULT" => true
	);

	if ($publicMode)
	{
		$arActions[] = array(
			'ICON' => 'edit',
			'TEXT' => GetMessage('BUYER_SUB_ACTION_EDIT_PROFILE'),
			'LINK' => $editUrl,
		);
	}

	foreach($arSitesShop as $val)
	{
		$addOrderUrl = "sale_order_create.php?USER_ID=".$userId."&SITE_ID=".$val["ID"]."&lang=".LANGUAGE_ID;
		if ($publicMode)
		{
			$addOrderUrl = "/shop/orders/details/0/?USER_ID=".$userId."&SITE_ID=".$val["ID"]."&lang=".LANGUAGE_ID;
		}
		if (!$publicMode || $isWithOrdersMode)
		{
			$arActions[] = array(
				"ICON" => "view",
				"TEXT" => GetMessage("BUYER_SUB_ACTION_ORDER")." [".$val["ID"]."]",
				"LINK" => $addOrderUrl,
			);
		}
	}

	$row->AddActions($arActions);
}

$aContext = array();
if ($publicMode)
{
	$aContext[] = array(
		"TEXT" => GetMessage("BUYER_ADD_USER"),
		"TITLE" => GetMessage("BUYER_ADD_USER"),
		"LINK" => "/shop/buyer/0/edit/",
		"PUBLIC" => true,
		"ICON" => "btn_new"
	);
}

$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_buyers.php"));
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$filterParams = [
		'CONFIG' => [
			'popupWidth' => 800,
		],
		'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => \Bitrix\Main\ModuleManager::isModuleInstalled('ui'),
		'ENABLE_FIELDS_SEARCH' => 'Y',
	];
	$lAdmin->DisplayFilter($filterFields, $filterParams);

	$listParams = [
		'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => \Bitrix\Main\ModuleManager::isModuleInstalled('ui'),
		'ENABLE_FIELDS_SEARCH' => 'Y',
	];
	$lAdmin->DisplayList($listParams);
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
