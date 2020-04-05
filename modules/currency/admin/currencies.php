<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
use Bitrix\Main\Loader;
use Bitrix\Main;
use Bitrix\Currency;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/prolog.php");
$CURRENCY_RIGHT = $APPLICATION->GetGroupRight("currency");
if ($CURRENCY_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('currency');
IncludeModuleLangFile(__FILE__);

$canViewUserList = (
	$USER->CanDoOperation('view_subordinate_users')
	|| $USER->CanDoOperation('view_all_users')
	|| $USER->CanDoOperation('edit_all_users')
	|| $USER->CanDoOperation('edit_subordinate_users')
);

$adminListTableID = 't_currencies';
$adminSort = new CAdminSorting($adminListTableID, 'SORT', 'ASC');
$adminList = new CAdminList($adminListTableID, $adminSort);

$filter = array();
$filterFields = array();

if (!isset($by))
	$by = 'SORT';
if (!isset($order))
	$order = 'ASC';
$by = strtoupper($by);
$order = strtoupper($order);

if ($adminList->EditAction() && $CURRENCY_RIGHT == "W")
{
	if (isset($FIELDS) && is_array($FIELDS))
	{
		foreach ($FIELDS as $ID => $arFields)
		{
			$ID = Currency\CurrencyManager::checkCurrencyID($ID);
			if ($ID === false)
				continue;

			if (!$adminList->IsUpdated($ID))
				continue;

			if (!CCurrency::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$adminList->AddUpdateError(GetMessage("CURRENCY_SAVE_ERR", array("#ID#" => $ID, "#ERROR_TEXT#" => $ex->GetString())), $ID);
				else
					$adminList->AddUpdateError(GetMessage("CURRENCY_SAVE_ERR2", array("#ID#"=>$ID)), $ID);
			}
		}
	}
}

if ($CURRENCY_RIGHT == "W" && $arID = $adminList->GroupAction())
{
	if ($_REQUEST['action_target']=='selected')
	{
		$currencyIterator = Currency\CurrencyTable::getList(array(
			'select' => array('CURRENCY')
		));
		while ($currency = $currencyIterator->fetch())
			$arID[] = $currency['CURRENCY'];
	}

	foreach($arID as $ID)
	{
		$ID = (string)$ID;
		if ($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
			case "base":
				if (!CCurrency::SetBaseCurrency($ID))
				{
					if ($ex = $APPLICATION->GetException())
						$adminList->AddGroupError($ex->GetString(), $ID);
					else
						$adminList->AddGroupError(GetMessage("currency_err2"), $ID);
				}
				break;
			case "delete":
				if (!CCurrency::Delete($ID))
				{
					if ($ex = $APPLICATION->GetException())
						$adminList->AddGroupError($ex->GetString(), $ID);
					else
						$adminList->AddGroupError(GetMessage("currency_err1"), $ID);
				}
				break;
		}
	}
}

$headerList = array();
$headerList['CURRENCY'] = array(
	"id" => "CURRENCY",
	"content" => GetMessage('currency_curr'),
	"sort" => "CURRENCY",
	"default" => true
);
$headerList['FULL_NAME'] = array(
	"id" => "FULL_NAME",
	"content" => GetMessage('CURRENCY_FULL_NAME'),
	"sort" => "CURRENT_LANG_FORMAT.FULL_NAME",
	"default" => true
);
$headerList['SORT'] = array(
	"id" => "SORT",
	"content" => GetMessage('currency_sort'),
	"sort" => "SORT",
	"default" => true
);
$headerList['AMOUNT_CNT'] = array(
	"id" => "AMOUNT_CNT",
	"content" => GetMessage('currency_rate_cnt'),
	"default" => true
);
$headerList['AMOUNT'] = array(
	"id" => "AMOUNT",
	"content" => GetMessage('currency_rate'),
	"default" => true
);
$headerList['BASE'] = array(
	'id' => 'BASE',
	'content' => GetMessage('BT_MOD_CURRENCY_LIST_ADM_TITLE_BASE'),
	'sort' => 'BASE',
	'default' => true
);
$headerList['NUMCODE'] = array(
	'id' => 'NUMCODE',
	'content' => GetMessage('BT_MOD_CURRENCY_LIST_ADM_TITLE_NUMCODE'),
	'default' => false
);
$headerList['DATE_UPDATE'] = array(
	'id' => 'DATE_UPDATE',
	'content' => GetMessage('BT_MOD_CURRENCY_LIST_ADM_TITLE_DATE_UPDATE'),
	'sort' => 'DATE_UPDATE',
	'default' => true
);
$headerList['MODIFIED_BY'] = array(
	'id' => 'MODIFIED_BY',
	'content' => GetMessage('BT_MOD_CURRENCY_LIST_ADM_TITLE_MODIFIED_BY'),
	'sort' => 'MODIFIED_BY',
	'default' => false
);
$headerList['DATE_CREATE'] = array(
	'id' => 'DATE_CREATE',
	'content' => GetMessage('BT_MOD_CURRENCY_LIST_ADM_TITLE_DATE_CREATE'),
	'sort' => 'DATE_CREATE',
	'default' => false
);
$headerList['CREATED_BY'] = array(
	'id' => 'CREATED_BY',
	'content' => GetMessage('BT_MOD_CURRENCY_LIST_ADM_TITLE_CREATED_BY'),
	'sort' => 'CREATED_BY',
	'default' => false
);

$adminList->AddHeaders($headerList);

$selectFields = array_fill_keys($adminList->GetVisibleHeaderColumns(), true);
$selectFields['CURRENCY'] = true;
$selectFieldsMap = array_fill_keys(array_keys($headerList), false);
$selectFieldsMap = array_merge($selectFieldsMap, $selectFields);

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
if (isset($selectFields['FULL_NAME']))
{
	unset($selectFields['FULL_NAME']);
	$selectFields = array_keys($selectFields);
	$selectFields['FULL_NAME'] = 'CURRENT_LANG_FORMAT.FULL_NAME';
}
else
{
	$selectFields = array_keys($selectFields);
}
$getListParams = array(
	'select' => $selectFields,
	'filter' => $filter,
	'order' => array($by => $order)
);
if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}
$totalPages = 0;
if ($usePageNavigation)
{
	$countQuery = new Main\Entity\Query(Currency\CurrencyTable::getEntity());
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
$currencyIterator = new CAdminResult(Currency\CurrencyTable::getList($getListParams), $adminListTableID);
if ($usePageNavigation)
{
	$currencyIterator->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$currencyIterator->NavRecordCount = $totalCount;
	$currencyIterator->NavPageCount = $totalPages;
	$currencyIterator->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$currencyIterator->NavStart();
}

$adminList->NavText($currencyIterator->GetNavPrint(GetMessage('CURRENCY_TITLE')));

$userList = array();
$userIDs = array();
$nameFormat = CSite::GetNameFormat(true);

$arRows = array();
while ($arRes = $currencyIterator->Fetch())
{
	if ($selectFieldsMap['CREATED_BY'])
	{
		$arRes['CREATED_BY'] = (int)$arRes['CREATED_BY'];
		if ($arRes['CREATED_BY'] > 0)
			$userIDs[$arRes['CREATED_BY']] = true;
	}
	if ($selectFieldsMap['MODIFIED_BY'])
	{
		$arRes['MODIFIED_BY'] = (int)$arRes['MODIFIED_BY'];
		if ($arRes['MODIFIED_BY'] > 0)
			$userIDs[$arRes['MODIFIED_BY']] = true;
	}

	$arRes['FULL_NAME'] = (string)$arRes['FULL_NAME'];
	if ($arRes['FULL_NAME'] === '')
		$arRes['FULL_NAME'] = $arRes['CURRENCY'];

	$urlEdit = '/bitrix/admin/currency_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes['CURRENCY'];

	$arRows[$arRes['CURRENCY']] = $row =& $adminList->AddRow($arRes['CURRENCY'], $arRes, $urlEdit, GetMessage('CURRENCY_A_EDIT'));

	$row->AddViewField("CURRENCY", '<a href="'.$urlEdit.'" title="'.GetMessage('CURRENCY_A_EDIT_TITLE').'">'.$arRes['CURRENCY'].'</a>');
	$row->AddInputField("SORT", array("size" => "5"));
	$row->AddViewField("FULL_NAME", htmlspecialcharsex($arRes['FULL_NAME']));
	if ($arRes['BASE'] == 'Y')
	{
		$row->AddViewField('AMOUNT_CNT', $arRes['AMOUNT_CNT']);
		$row->AddViewField('AMOUNT', $arRes['AMOUNT']);
		$row->AddViewField('BASE', GetMessage('BASE_CURRENCY_YES'));
	}
	else
	{
		$row->AddInputField("AMOUNT_CNT", array("size" => "5"));
		$row->AddInputField("AMOUNT", array("size" => "10"));
		$row->AddViewField('BASE', GetMessage('BASE_CURRENCY_NO'));
	}

	if ($selectFieldsMap['DATE_CREATE'])
		$row->AddViewField('DATE_CREATE', $arRes['DATE_CREATE']);
	if ($selectFieldsMap['DATE_UPDATE'])
		$row->AddViewField('DATE_UPDATE', $arRes['DATE_UPDATE']);

	if ($selectFieldsMap['NUMCODE'])
		$row->AddInputField('NUMCODE', array('size' => 3));

	$arActions = array();

	$arActions[] = array(
		"ICON" => "edit",
		"DEFAULT" => "Y",
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION" => $adminList->ActionRedirect($urlEdit)
	);

	if ($CURRENCY_RIGHT == "W" && $arRes['BASE'] != 'Y')
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => GetMessage('CURRENCY_SET_BASE'),
			"TITLE" => GetMessage('CURRENCY_SET_BASE_TITLE'),
			"ACTION" => "if(confirm('".GetMessage('CONFIRM_SET_BASE_MESSAGE')."')) ".$adminList->ActionDoGroup($arRes['CURRENCY'], "base")
		);
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION" => "if(confirm('".GetMessage('CONFIRM_DEL_MESSAGE')."')) ".$adminList->ActionDoGroup($arRes['CURRENCY'], "delete")
		);
	}

	$row->AddActions($arActions);
}

if ($selectFieldsMap['CREATED_BY'] || $selectFieldsMap['MODIFIED_BY'])
{
	if (!empty($userIDs))
	{
		$userIterator = Main\UserTable::getList(array(
			'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'),
			'filter' => array('ID' => array_keys($userIDs)),
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
		array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $currencyIterator->SelectedRowsCount()),
		array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
	)
);

if ($CURRENCY_RIGHT == "W")
{
	$adminList->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("currency_add_from_classifier"),
		"LINK" => "/bitrix/admin/currency_add_from_classifier.php?lang=".LANGUAGE_ID,
		"TITLE" => GetMessage("currency_add_from_classifier_title")
	),
	array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("currency_add"),
		"LINK" => "/bitrix/admin/currency_edit.php?lang=".LANGUAGE_ID,
		"TITLE" => GetMessage("currency_add")
	),
	array(
		"ICON" => "",
		"TEXT" => GetMessage("currency_list"),
		"LINK" => "/bitrix/admin/currencies_rates.php?lang=".LANGUAGE_ID,
		"TITLE" => GetMessage("currency_list")
	),
);

$adminList->AddAdminContextMenu($aContext);

$adminList->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CURRENCY_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminList->DisplayList();
echo BeginNote();
echo GetMessage('CURRENCY_CODES_ISO_STANDART', array('#ISO_LINK#' => CURRENCY_ISO_STANDART_URL));
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");