<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global array $FIELDS */

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/prolog.php';

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

Loader::includeModule('catalog');

$accessController = AccessController::getCurrent();
if (!(
	$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
	|| $accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT)
))
{
	$APPLICATION->AuthForm('');
}

$readOnly = !$accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT);

$canViewUserList = (
	$USER->CanDoOperation('view_subordinate_users')
	|| $USER->CanDoOperation('view_all_users')
	|| $USER->CanDoOperation('edit_all_users')
	|| $USER->CanDoOperation('edit_subordinate_users')
);

if ($publicMode)
{
	$canViewUserList = false;
}

$adminListTableID = 'tbl_catalog_round_rules';

$adminSort = new CAdminUiSorting($adminListTableID, 'ID', 'ASC');
$adminList = new CAdminUiList($adminListTableID, $adminSort);

$by = mb_strtoupper($adminSort->getField());
$order = mb_strtoupper($adminSort->getOrder());
$listOrder = [
	$by => $order,
];
if ($by !== 'ID')
{
	$listOrder['ID'] = 'ASC';
}

$listType = [
	'' => Loc::getMessage('PRICE_ROUND_LIST_FILTER_PRICE_TYPE_ANY'),
];
foreach (Catalog\Helpers\Admin\Tools::getPriceTypeList(false) as $id => $title)
{
	$listType[$id] = $title;
}

$filterFields = [
	[
		'id' => 'ID',
		'name' => 'ID',
		'quickSearch' => '=',
		'default' => true
	],
	[
		'id' => 'CATALOG_GROUP_ID',
		'name' => Loc::getMessage('PRICE_ROUND_LIST_FILTER_PRICE_TYPE'),
		'type' => 'list',
		'items' => $listType,
		'filterable' => '='
	]
];

$filter = [];
$adminList->AddFilter($filterFields, $filter);

$roundValues = Catalog\Helpers\Admin\RoundEdit::getPresetRoundValues(true);

if (!$readOnly && $adminList->EditAction())
{
	$rows = $adminList->GetEditFields();
	if (!empty($rows))
	{
		$listIds = array_filter(array_keys($rows));
		if (!empty($listIds))
		{
			$priceTypeList = [];
			$iterator = Catalog\RoundingTable::getList([
				'select' => [
					'ID',
					'CATALOG_GROUP_ID',
				],
				'filter' => [
					'@ID' => $listIds,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$priceTypeList[$row['CATALOG_GROUP_ID']] = $row['CATALOG_GROUP_ID'];
			}
			unset($row, $iterator);
			Catalog\RoundingTable::clearPriceTypeIds();
			Catalog\RoundingTable::setPriceTypeIds($priceTypeList);
			Catalog\RoundingTable::disallowClearCache();
			$conn = Main\Application::getConnection();
			foreach ($rows as $ruleId => $fields)
			{
				$ruleId = (int)$ruleId;
				if ($ruleId <= 0)
				{
					continue;
				}

				Catalog\Helpers\Admin\RoundEdit::prepareFields($fields);

				$conn->startTransaction();
				$result = Catalog\RoundingTable::update($ruleId, $fields);
				if ($result->isSuccess())
				{
					$conn->commitTransaction();
				}
				else
				{
					$conn->rollbackTransaction();
					$adminList->AddUpdateError(implode('<br>', $result->getErrorMessages()), $ruleId);
				}
				unset($result);
			}
			Catalog\RoundingTable::allowClearCache();
			Catalog\RoundingTable::clearCache();
			unset($fields, $ruleId);
			unset($priceTypeList);
		}
	}
	unset($rows);
}

$listIds = $adminList->GroupAction();
if (!$readOnly && !empty($listIds) && is_array($listIds))
{
	$priceTypeList = [];
	if ($adminList->IsGroupActionToAll())
	{
		$listIds = [];
		$ruleIterator = Catalog\RoundingTable::getList([
			'select' => [
				'ID',
				'CATALOG_GROUP_ID',
			],
			'filter' => $filter,
		]);
		while ($rule = $ruleIterator->fetch())
		{
			$listIds[] = $rule['ID'];
			$priceTypeList[$rule['CATALOG_GROUP_ID']] = $rule['CATALOG_GROUP_ID'];
		}
		unset($rule, $ruleIterator);
	}

	$listIds = array_filter($listIds);
	if (!empty($listIds))
	{
		$action = $adminList->GetAction();
		switch ($action)
		{
			case 'delete':
				if (empty($priceTypeList))
				{
					$iterator = Catalog\RoundingTable::getList([
						'select' => [
							'ID',
							'CATALOG_GROUP_ID',
						],
						'filter' => [
							'@ID' => $listIds,
						]
					]);
					while ($row = $iterator->fetch())
					{
						$priceTypeList[$row['CATALOG_GROUP_ID']] = $row['CATALOG_GROUP_ID'];
					}
					unset($row, $iterator);
				}
				Catalog\RoundingTable::clearPriceTypeIds();
				Catalog\RoundingTable::setPriceTypeIds($priceTypeList);
				Catalog\RoundingTable::disallowClearCache();
				foreach ($listIds as $ruleId)
				{
					$result = Catalog\RoundingTable::delete($ruleId);
					if (!$result->isSuccess())
					{
						$adminList->AddGroupError(implode('<br>', $result->getErrorMessages()), $ruleId);
					}
					unset($result);
				}
				unset($ruleId);
				Catalog\RoundingTable::allowClearCache();
				Catalog\RoundingTable::clearCache();
				break;
		}
		unset($action);
	}
	unset($listIds, $priceTypeList);

	if ($adminList->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($adminList->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

$headerList = [];
$headerList['ID'] = [
	'id' => 'ID',
	'content' => 'ID',
	'sort' => 'ID',
	'default' => true,
];
$headerList['CATALOG_GROUP_ID'] = [
	'id' => 'CATALOG_GROUP_ID',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_CATALOG_GROUP_ID'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_CATALOG_GROUP_ID'),
	'sort' => 'CATALOG_GROUP_ID',
	'default' => true,
];
$headerList['PRICE'] = [
	'id' => 'PRICE',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_PRICE'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_PRICE'),
	'sort' => 'PRICE',
	'default' => true,
];
$headerList['ROUND_TYPE'] = [
	'id' => 'ROUND_TYPE',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_ROUND_TYPE'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_ROUND_TYPE'),
	'default' => true,
];
$headerList['ROUND_PRECISION'] = [
	'id' => 'ROUND_PRECISION',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_ROUND_PRECISION'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_ROUND_PRECISION'),
	'default' => true,
];
$headerList['CREATED_BY'] = [
	'id' => 'CREATED_BY',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_CREATED_BY'),
	'sort' => 'CREATED_BY',
	'default' => true,
];
$headerList['DATE_CREATE'] = [
	'id' => 'DATE_CREATE',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_DATE_CREATE'),
	'sort' => 'DATE_CREATE',
	'default' => true,
];
$headerList['MODIFIED_BY'] = [
	'id' => 'MODIFIED_BY',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_MODIFIED_BY'),
	'sort' => 'MODIFIED_BY',
	'default' => true,
];
$headerList['DATE_MODIFY'] = [
	'id' => 'DATE_MODIFY',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_DATE_MODIFY'),
	'sort' => 'DATE_MODIFY',
	'default' => true,
];
$adminList->AddHeaders($headerList);

$selectFields = array_fill_keys($adminList->GetVisibleHeaderColumns(), true);
$selectFields['ID'] = true;
$selectFields['CATALOG_GROUP_ID'] = true;
$selectFieldsMap = array_fill_keys(array_keys($headerList), false);
$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);

$userList = [];
$userIds = [];
$nameFormat = CSite::GetNameFormat();

$priceTypeList = Catalog\Helpers\Admin\Tools::getPriceTypeLinkList();
$roundTypeList = Catalog\RoundingTable::getRoundTypes(true);

$rowList = [];

$usePageNavigation = true;
$navyParams = [];
if ($adminList->isExportMode())
{
	$usePageNavigation = false;
}
else
{
	$navyParams = CDBResult::GetNavParams(CAdminUiResult::GetNavSize($adminListTableID));
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
$getListParams = [
	'select' => array_keys($selectFields),
	'filter' => $filter,
	'order' => $listOrder,
];
if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}
$totalPages = 0;
$totalCount = 0;
if ($usePageNavigation)
{
	$totalCount = Catalog\RoundingTable::getCount($getListParams['filter']);
	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages)
		{
			$navyParams['PAGEN'] = $totalPages;
		}
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

$ruleIterator = new CAdminUiResult(Catalog\RoundingTable::getList($getListParams), $adminListTableID);
if ($usePageNavigation)
{
	$ruleIterator->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$ruleIterator->NavRecordCount = $totalCount;
	$ruleIterator->NavPageCount = $totalPages;
	$ruleIterator->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$ruleIterator->NavStart();
}

CTimeZone::Disable();
$adminList->SetNavigationParams($ruleIterator, ['BASE_LINK' => $selfFolderUrl . 'cat_round_list.php']);
while ($rule = $ruleIterator->Fetch())
{
	$rule['ID'] = (int)$rule['ID'];
	if (($selectFieldsMap['PRICE']))
	{
		$rule['PRICE'] = (float)$rule['PRICE'];
	}
	if ($selectFieldsMap['ROUND_PRECISION'])
	{
		$rule['ROUND_PRECISION'] = (float)$rule['ROUND_PRECISION'];
	}
	if ($selectFieldsMap['CREATED_BY'])
	{
		$rule['CREATED_BY'] = (int)$rule['CREATED_BY'];
		if ($rule['CREATED_BY'] > 0)
		{
			$userIds[$rule['CREATED_BY']] = true;
		}
	}
	if ($selectFieldsMap['MODIFIED_BY'])
	{
		$rule['MODIFIED_BY'] = (int)$rule['MODIFIED_BY'];
		if ($rule['MODIFIED_BY'] > 0)
		{
			$userIds[$rule['MODIFIED_BY']] = true;
		}
	}

	$urlEdit = $selfFolderUrl . 'cat_round_edit.php?ID=' . $rule['ID'] . '&lang=' . LANGUAGE_ID;
	$urlEdit = $adminSidePanelHelper->editUrlToPublicPage($urlEdit);
	$row = &$adminList->AddRow(
		$rule['ID'],
		$rule,
		$urlEdit,
		(!$readOnly ? Loc::getMessage('PRICE_ROUND_LIST_MESS_EDIT_RULE') : Loc::getMessage('PRICE_ROUND_LIST_MESS_VIEW_RULE'))
	);
	$row->AddViewField('ID', '<a href="' . $urlEdit . '">' . $rule['ID'] . '</a>');

	if ($selectFieldsMap['DATE_CREATE'])
	{
		$row->AddViewField('DATE_CREATE', $rule['DATE_CREATE']);
	}
	if ($selectFieldsMap['TIMESTAMP_X'])
	{
		$row->AddViewField('TIMESTAMP_X', $rule['TIMESTAMP_X']);
	}

	$row->AddViewField(
		'CATALOG_GROUP_ID',
		($priceTypeList[$rule['CATALOG_GROUP_ID']] ?? (int)$rule['CATALOG_GROUP_ID'])
	);

	if ($selectFieldsMap['PRICE'])
	{
		$row->AddViewField(
			'PRICE',
			Loc::getMessage(
				'PRICE_ROUND_LIST_PRICE_TEMPLATE',
				[
					'#PRICE#' => $rule['PRICE'],
				]
			)
		);
	}

	if (!$readOnly)
	{
		if ($selectFieldsMap['PRICE'])
		{
			$row->AddInputField('PRICE');
		}
		if ($selectFieldsMap['ROUND_TYPE'])
		{
			$row->AddSelectField('ROUND_TYPE', $roundTypeList);
		}
		if ($selectFieldsMap['ROUND_PRECISION'])
		{
			$row->AddSelectField('ROUND_PRECISION', $roundValues);
		}
	}
	else
	{
		if ($selectFieldsMap['ROUND_TYPE'])
		{
			$row->AddSelectField('ROUND_TYPE', $roundTypeList, false);
		}
		if ($selectFieldsMap['ROUND_PRECISION'])
		{
			$row->AddSelectField('ROUND_PRECISION', $roundValues, false);
		}
	}
	$actions = [];
	$actions[] = [
		'ICON' => 'edit',
		'TEXT' => (!$readOnly ? Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_EDIT') : Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_VIEW')),
		'LINK' => $urlEdit,
		'DEFAULT' => true,
	];

	if (!$readOnly)
	{
		$actions[] = [
			'ICON' => 'copy',
			'TEXT' => Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_COPY'),
			'LINK' => $urlEdit.'&action=copy',
			'DEFAULT' => false,
		];
		$actions[] = [
			'ICON' =>'delete',
			'TEXT' => Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_DELETE'),
			'ACTION' => "if (confirm('".Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_DELETE_CONFIRM')."')) ".$adminList->ActionDoGroup($rule['ID'], 'delete')
		];
	}

	$row->AddActions($actions);
	unset($actions);

	$rowList[$rule['ID']] = $row;
	unset($row);
	unset($urlEdit);
}
unset($rule);
CTimeZone::Enable();

unset($roundValues);

if (!empty($rowList) && ($selectFieldsMap['CREATED_BY'] || $selectFieldsMap['MODIFIED_BY']))
{
	if (!empty($userIds))
	{
		$userIterator = Main\UserTable::getList([
			'select' => [
				'ID',
				'LOGIN',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'EMAIL',
			],
			'filter' => [
				'@ID' => array_keys($userIds),
			],
		]);
		while ($oneUser = $userIterator->fetch())
		{
			$oneUser['ID'] = (int)$oneUser['ID'];
			if ($canViewUserList)
			{
				$userList[$oneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang=' . LANGUAGE_ID
					. '&ID='  . $oneUser['ID'] . '">'
					. CUser::FormatName($nameFormat, $oneUser)
					. '</a>';
			}
			else
			{
				$userList[$oneUser['ID']] = CUser::FormatName($nameFormat, $oneUser);
			}
		}
		unset($oneUser, $userIterator);
	}

	/** @var CAdminListRow $row */
	foreach ($rowList as &$row)
	{
		if ($selectFieldsMap['CREATED_BY'])
		{
			$userName = '';
			if ($row->arRes['CREATED_BY'] > 0 && isset($userList[$row->arRes['CREATED_BY']]))
			{
				$userName = $userList[$row->arRes['CREATED_BY']];
			}
			$row->AddViewField('CREATED_BY', $userName);
			unset($userName);
		}
		if ($selectFieldsMap['MODIFIED_BY'])
		{
			$userName = '';
			if ($row->arRes['MODIFIED_BY'] > 0 && isset($userList[$row->arRes['MODIFIED_BY']]))
			{
				$userName = $userList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField('MODIFIED_BY', $userName);
			unset($userName);
		}
	}
	unset($row);
}

$adminList->AddFooter([
	[
		'title' => Loc::getMessage('MAIN_ADMIN_LIST_SELECTED'),
		'value' => $ruleIterator->SelectedRowsCount(),
	],
	[
		'counter' => true,
		'title' => Loc::getMessage('MAIN_ADMIN_LIST_CHECKED'),
		'value' => 0,
	],
]);

if (!$readOnly)
{
	$adminList->AddGroupActionTable([
		'edit' => true,
		'delete' => true
	]);
}

$contextMenu = [];
if (!$readOnly)
{
	$addUrl = $selfFolderUrl."cat_round_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$contextMenu[] = [
		'ICON' => 'btn_new',
		'TEXT' => Loc::getMessage('PRICE_ROUND_LIST_MESS_NEW_RULE'),
		'TITLE' => Loc::getMessage('PRICE_ROUND_LIST_MESS_NEW_RULE_TITLE'),
		'LINK' => $addUrl,
	];
}
if (!empty($contextMenu))
{
	$adminList->setContextSettings(['pagePath' => $selfFolderUrl . 'cat_round_list.php']);
	$adminList->AddAdminContextMenu($contextMenu);
}

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('PRICE_ROUND_LIST_TITLE'));
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$adminList->DisplayFilter($filterFields);
$adminList->DisplayList();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
