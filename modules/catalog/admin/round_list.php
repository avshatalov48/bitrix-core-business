<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global array $FIELDS */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/prolog.php');

Loc::loadMessages(__FILE__);

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_group')))
	$APPLICATION->AuthForm('');
Loader::includeModule('catalog');
$readOnly = !$USER->CanDoOperation('catalog_group');

$canViewUserList = (
	$USER->CanDoOperation('view_subordinate_users')
	|| $USER->CanDoOperation('view_all_users')
	|| $USER->CanDoOperation('edit_all_users')
	|| $USER->CanDoOperation('edit_subordinate_users')
);

$request = Main\Context::getCurrent()->getRequest();

$adminListTableID = 'tbl_catalog_round_rules';

$adminSort = new CAdminSorting($adminListTableID, 'ID', 'ASC');
$adminList = new CAdminList($adminListTableID, $adminSort);

$filter = array();
$filterFields = array(
	'filter_price_type'
);
$adminList->InitFilter($filterFields);
$filterValues = array(
	'filter_price_type' => (isset($filter_price_type) ? $filter_price_type : '')
);

if ($filterValues['filter_price_type'] != '')
	$filter['=CATALOG_GROUP_ID'] = $filterValues['filter_price_type'];

$roundValues = Catalog\Helpers\Admin\RoundEdit::getPresetRoundValues(true);

if (!$readOnly && $adminList->EditAction())
{
	if (!empty($FIELDS) && is_array($FIELDS))
	{
		$listIds = array_filter(array_keys($FIELDS));
		if (!empty($listIds))
		{
			$priceTypeList = array();
			$iterator = Catalog\RoundingTable::getList(array(
				'select' => array('ID', 'CATALOG_GROUP_ID'),
				'filter' => array('@ID' => $listIds)
			));
			while ($row = $iterator->fetch())
				$priceTypeList[$row['CATALOG_GROUP_ID']] = $row['CATALOG_GROUP_ID'];
			unset($row, $iterator);
			Catalog\RoundingTable::clearPriceTypeIds();
			Catalog\RoundingTable::setPriceTypeIds($priceTypeList);
			Catalog\RoundingTable::disallowClearCache();
			$conn = Main\Application::getConnection();
			foreach ($FIELDS as $ruleId => $fields)
			{
				$ruleId = (int)$ruleId;
				if ($ruleId <= 0 || !$adminList->IsUpdated($ruleId))
					continue;

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
}

if (!$readOnly && ($listIds = $adminList->GroupAction()))
{
	$priceTypeList = array();
	if ($request['action_target'] == 'selected')
	{
		$listIds = array();
		$ruleIterator = Catalog\RoundingTable::getList(array(
			'select' => array('ID', 'CATALOG_GROUP_ID'),
			'filter' => $filter
		));
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
		$action = $request['action'];
		if (!empty($request['action_button']))
			$action = $request['action_button'];
		switch ($action)
		{
			case 'delete':
				if (empty($priceTypeList))
				{
					$iterator = Catalog\RoundingTable::getList(array(
						'select' => array('ID', 'CATALOG_GROUP_ID'),
						'filter' => array('@ID' => $listIds)
					));
					while ($row = $iterator->fetch())
						$priceTypeList[$row['CATALOG_GROUP_ID']] = $row['CATALOG_GROUP_ID'];
					unset($row, $iterator);
				}
				Catalog\RoundingTable::clearPriceTypeIds();
				Catalog\RoundingTable::setPriceTypeIds($priceTypeList);
				Catalog\RoundingTable::disallowClearCache();
				foreach ($listIds as $ruleId)
				{
					$result = Catalog\RoundingTable::delete($ruleId);
					if (!$result->isSuccess())
						$adminList->AddGroupError(implode('<br>', $result->getErrorMessages()), $ruleId);
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
}

$headerList = array();
$headerList['ID'] = array(
	'id' => 'ID',
	'content' => 'ID',
	'sort' => 'ID',
	'default' => true
);
$headerList['CATALOG_GROUP_ID'] = array(
	'id' => 'CATALOG_GROUP_ID',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_CATALOG_GROUP_ID'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_CATALOG_GROUP_ID'),
	'sort' => 'CATALOG_GROUP_ID',
	'default' => true
);
$headerList['PRICE'] = array(
	'id' => 'PRICE',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_PRICE'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_PRICE'),
	'sort' => 'PRICE',
	'default' => true
);
$headerList['ROUND_TYPE'] = array(
	'id' => 'ROUND_TYPE',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_ROUND_TYPE'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_ROUND_TYPE'),
	'default' => true
);
$headerList['ROUND_PRECISION'] = array(
	'id' => 'ROUND_PRECISION',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_ROUND_PRECISION'),
	'title' => Loc::getMessage('PRICE_ROUND_HEADER_TITLE_ROUND_PRECISION'),
	'default' => true
);
$headerList['CREATED_BY'] = array(
	'id' => 'CREATED_BY',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_CREATED_BY'),
	'sort' => 'CREATED_BY',
	'default' => true
);
$headerList['DATE_CREATE'] = array(
	'id' => 'DATE_CREATE',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_DATE_CREATE'),
	'sort' => 'DATE_CREATE',
	'default' => true
);
$headerList['MODIFIED_BY'] = array(
	'id' => 'MODIFIED_BY',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_MODIFIED_BY'),
	'sort' => 'MODIFIED_BY',
	'default' => true
);
$headerList['DATE_MODIFY'] = array(
	'id' => 'DATE_MODIFY',
	'content' => Loc::getMessage('PRICE_ROUND_HEADER_NAME_DATE_MODIFY'),
	'sort' => 'DATE_MODIFY',
	'default' => true
);
$adminList->AddHeaders($headerList);

$selectFields = array_fill_keys($adminList->GetVisibleHeaderColumns(), true);
$selectFields['ID'] = true;
$selectFields['CATALOG_GROUP_ID'] = true;
$selectFieldsMap = array_fill_keys(array_keys($headerList), false);
$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);

if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

$userList = array();
$userIds = array();
$nameFormat = CSite::GetNameFormat(true);

$priceTypeList = Catalog\Helpers\Admin\Tools::getPriceTypeLinkList();
$roundTypeList = Catalog\RoundingTable::getRoundTypes(true);

$rowList = array();

$usePageNavigation = true;
$navyParams = array();
if ($request['mode'] == 'excel')
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
if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}
$totalPages = 0;
$totalCount = 0;
if ($usePageNavigation)
{
	$countQuery = new Main\Entity\Query(Catalog\RoundingTable::getEntity());
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

$ruleIterator = new CAdminResult(Catalog\RoundingTable::getList($getListParams), $adminListTableID);
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
$adminList->NavText($ruleIterator->GetNavPrint(Loc::getMessage('PRICE_ROUND_LIST_MESS_NAV')));
while ($rule = $ruleIterator->Fetch())
{
	$rule['ID'] = (int)$rule['ID'];
	if ($selectFieldsMap['CREATED_BY'])
	{
		$rule['CREATED_BY'] = (int)$rule['CREATED_BY'];
		if ($rule['CREATED_BY'] > 0)
			$userIds[$rule['CREATED_BY']] = true;
	}
	if ($selectFieldsMap['MODIFIED_BY'])
	{
		$rule['MODIFIED_BY'] = (int)$rule['MODIFIED_BY'];
		if ($rule['MODIFIED_BY'] > 0)
			$userIds[$rule['MODIFIED_BY']] = true;
	}

	$urlEdit = 'cat_round_edit.php?ID='.$rule['ID'].'&lang='.LANGUAGE_ID.GetFilterParams('filter_');

	$row = &$adminList->AddRow(
		$rule['ID'],
		$rule,
		$urlEdit,
		(!$readOnly ? Loc::getMessage('PRICE_ROUND_LIST_MESS_EDIT_RULE') : Loc::getMessage('PRICE_ROUND_LIST_MESS_VIEW_RULE'))
	);
	$row->AddViewField('ID', '<a href="'.$urlEdit.'">'.$rule['ID'].'</a>');

	if ($selectFieldsMap['DATE_CREATE'])
		$row->AddViewField('DATE_CREATE', $rule['DATE_CREATE']);
	if ($selectFieldsMap['TIMESTAMP_X'])
		$row->AddViewField('TIMESTAMP_X', $rule['TIMESTAMP_X']);

	$row->AddViewField(
		'CATALOG_GROUP_ID',
		(isset($priceTypeList[$rule['CATALOG_GROUP_ID']]) ? $priceTypeList[$rule['CATALOG_GROUP_ID']] : (int)$rule['CATALOG_GROUP_ID'])
	);

	if ($selectFieldsMap['PRICE'])
	{
		$row->AddViewField(
			'PRICE',
			Loc::getMessage(
				'PRICE_ROUND_LIST_PRICE_TEMPLATE',
				array('#PRICE#' => $rule['PRICE'])
			)
		);
	}

	if (!$readOnly)
	{
		if ($selectFieldsMap['PRICE'])
			$row->AddInputField('PRICE');
		if ($selectFieldsMap['ROUND_TYPE'])
			$row->AddSelectField('ROUND_TYPE', $roundTypeList);
		if ($selectFieldsMap['ROUND_PRECISION'])
			$row->AddSelectField('ROUND_PRECISION', $roundValues);
	}
	else
	{
		if ($selectFieldsMap['ROUND_TYPE'])
			$row->AddSelectField('ROUND_TYPE', $roundTypeList, false);
		if ($selectFieldsMap['ROUND_PRECISION'])
			$row->AddSelectField('ROUND_PRECISION', $roundValues, false);
	}
	$actions = array();
	$actions[] = array(
		'ICON' => 'edit',
		'TEXT' => (!$readOnly ? Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_EDIT') : Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_VIEW')),
		'ACTION' => $adminList->ActionRedirect($urlEdit),
		'DEFAULT' => true
	);

	if (!$readOnly)
	{
		$actions[] = array(
			'ICON' => 'copy',
			'TEXT' => Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_COPY'),
			'ACTION' => $adminList->ActionRedirect($urlEdit.'&action=copy'),
			'DEFAULT' => false,
		);
		$actions[] = array('SEPARATOR' => true);
		$actions[] = array(
			'ICON' =>'delete',
			'TEXT' => Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_DELETE'),
			'ACTION' => "if (confirm('".Loc::getMessage('PRICE_ROUND_LIST_CONTEXT_DELETE_CONFIRM')."')) ".$adminList->ActionDoGroup($rule['ID'], 'delete')
		);
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
		$userIterator = Main\UserTable::getList(array(
			'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'),
			'filter' => array('@ID' => array_keys($userIds)),
		));
		while ($oneUser = $userIterator->fetch())
		{
			$oneUser['ID'] = (int)$oneUser['ID'];
			if ($canViewUserList)
				$userList[$oneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$oneUser['ID'].'">'.CUser::FormatName($nameFormat, $oneUser).'</a>';
			else
				$userList[$oneUser['ID']] = CUser::FormatName($nameFormat, $oneUser);
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
		unset($userName);
	}
	unset($row);
}

$adminList->AddFooter(
	array(
		array(
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_SELECTED'),
			'value' => $ruleIterator->SelectedRowsCount()
		),
		array(
			'counter' => true,
			'title' => Loc::getMessage('MAIN_ADMIN_LIST_CHECKED'),
			'value' => 0
		),
	)
);

$adminList->AddGroupActionTable(
	array(
		'delete' => Loc::getMessage('MAIN_ADMIN_LIST_DELETE'),
	)
);

$contextMenu = array();
if (!$readOnly)
{
	$contextMenu[] = array(
		'ICON' => 'btn_new',
		'TEXT' => Loc::getMessage('PRICE_ROUND_LIST_MESS_NEW_RULE'),
		'TITLE' => Loc::getMessage('PRICE_ROUND_LIST_MESS_NEW_RULE_TITLE'),
		'LINK' => 'cat_round_edit.php?ID=0&lang='.LANGUAGE_ID.GetFilterParams('filter_')
	);
}
if (!empty($contextMenu))
	$adminList->AddAdminContextMenu($contextMenu);

unset($ruleEditUrl);

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('PRICE_ROUND_LIST_TITLE'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

?>
<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage();?>">
	<?
	$filterForm = new CAdminFilter(
		$adminListTableID.'_filter',
		array(
			Loc::getMessage('PRICE_ROUND_LIST_FILTER_PRICE_TYPE')
		)
	);
	$filterForm->Begin();
	?>
	<tr>
		<td><? echo Loc::getMessage('PRICE_ROUND_LIST_FILTER_PRICE_TYPE'); ?>:</td>
		<td><select name="filter_price_type">
			<option value=""<?=($filterValues['filter_price_type'] == '' ? ' selected' : ''); ?>><?=htmlspecialcharsbx(Loc::getMessage('PRICE_ROUND_LIST_FILTER_PRICE_TYPE_ANY')); ?></option><?
			foreach (Catalog\Helpers\Admin\Tools::getPriceTypeList(false) as $id => $title)
			{
				?><option value="<?=$id; ?>"<?=($filterValues['filter_price_type'] == $id ? ' selected' : ''); ?>><?=htmlspecialcharsbx($title); ?></option><?
			}
			unset($title, $id);
			?></select>
		</td>
	</tr>
	<?
	$filterForm->Buttons(
		array(
			'table_id' => $adminListTableID,
			'url' => $APPLICATION->GetCurPage(),
			'form' => 'find_form'
		)
	);
	$filterForm->End();
	?>
</form>
<?

$adminList->DisplayList();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');