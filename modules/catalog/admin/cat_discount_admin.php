<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Currency;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_discount');
$canViewUserList = (
	$USER->CanDoOperation('view_subordinate_users')
	|| $USER->CanDoOperation('view_all_users')
	|| $USER->CanDoOperation('edit_all_users')
	|| $USER->CanDoOperation('edit_subordinate_users')
);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($ex->GetString());
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_catalog_discount";

$oSort = new CAdminSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_active",
	"filter_date_active_from",
	"filter_date_active_to",
	"filter_name",
	"filter_coupon",
	"filter_renewal",
	"filter_value_start",
	"filter_value_end",
	"filter_use_coupons"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

$filterSite = array();
if (!empty($filter_site_id))
{
	if (!is_array($filter_site_id))
		$filter_site_id = ($filter_site_id == 'NOT_REF' ? array() : array($filter_site_id));
	$filterSite = $filter_site_id;
}
if (!empty($filterSite))
	$arFilter["@SITE_ID"] = $filterSite;

if (!empty($filter_active)) $arFilter["ACTIVE"] = $filter_active;
if (!empty($filter_date_active_from)) $arFilter["!>ACTIVE_FROM"] = $filter_date_active_from;
if (!empty($filter_date_active_to)) $arFilter["!<ACTIVE_TO"] = $filter_date_active_to;
if (!empty($filter_name)) $arFilter["%NAME"] = $filter_name;
if (!empty($filter_coupon)) $arFilter["COUPON"] = $filter_coupon;
if (!empty($filter_renewal)) $arFilter["RENEWAL"] = $filter_renewal;
if (isset($filter_value_start) && doubleval($filter_value_start) > 0)
	$arFilter[">=VALUE"] = doubleval($filter_value_start);
if (isset($filter_value_end) && doubleval($filter_value_end) > 0)
	$arFilter["<=VALUE"] = doubleval($filter_value_end);
if (!empty($filter_use_coupons))
	$arFilter['USE_COUPONS'] = $filter_use_coupons;

if (!$bReadOnly && $lAdmin->EditAction())
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0|| !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (isset($arFields['CONDITIONS']))
			unset($arFields['CONDITIONS']);

		if (!CCatalogDiscount::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT")), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}


if (!$bReadOnly && ($arID = $lAdmin->GroupAction()))
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogDiscount::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if (!CCatalogDiscount::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_DELETE_DISCOUNT")), $ID);
				}
				else
				{
					$DB->Commit();
				}
				break;
			case "activate":
			case "deactivate":
				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);
				if (!CCatalogDiscount::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT")), $ID);
				}
				break;
		}
	}
}

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("DSC_NAME"),
		"sort" => "NAME",
		"default" => true
	),
	array(
		"id" => "VALUE",
		"content" => GetMessage("DSC_VALUE"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("DSC_ACT"),
		"sort" => "ACTIVE",
		"default" => true
	),
	array(
		"id" => "ACTIVE_FROM",
		"content" => GetMessage('DSC_PERIOD_FROM'),
		"sort" => "ACTIVE_FROM",
		"default" => true
	),
	array(
		"id" => "ACTIVE_TO",
		"content" => GetMessage("DSC_PERIOD_TO2"),
		"sort" => "ACTIVE_TO",
		"default" => true
	),
	array(
		"id" => "PRIORITY",
		"content" => GetMessage('DSC_PRIORITY'),
		"sort" => "PRIORITY",
		"default" => true
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("DSC_SORT"),
		"sort" => "SORT",
		"default" => true
	),
	array(
		"id" => "SITE_ID",
		"content" => GetMessage("DSC_SITE"),
		"sort" => "SITE_ID",
		"default"=>true
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage('DSC_MODIFIED_BY'),
		"sort" => "MODIFIED_BY",
		"default" => true
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage('DSC_TIMESTAMP_X'),
		"sort" => "TIMESTAMP_X",
		"default" => true
	),
	array(
		"id" => "MAX_DISCOUNT",
		"content" => GetMessage('DSC_MAX_DISCOUNT'),
		"sort" => "MAX_DISCOUNT",
		"default" => false
	),
	array(
		"id" => "RENEWAL",
		"content" => GetMessage("DSC_REN"),
		"sort" => "RENEWAL",
		"default" => false
	),
	array(
		"id" => "CREATED_BY",
		"content" => GetMessage('DSC_CREATED_BY'),
		"sort" => "CREATED_BY",
		"default" => false
	),
	array(
		"id" => "DATE_CREATE",
		"content" => GetMessage('DSC_DATE_CREATE'),
		"sort" => "DATE_CREATE",
		"default" => false
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage('DSC_XML_ID'),
		"sort" => "XML_ID",
		"default" => false
	),
	array(
		"id" => "CURRENCY",
		"content" => GetMessage('DSC_CURRENCY'),
		"sort" => "CURRENCY",
		"default" => false
	),
	array(
		"id" => "LAST_DISCOUNT",
		"content" => GetMessage('DSC_LAST_DISCOUNT'),
		"sort" => "LAST_DISCOUNT",
		"default" => false
	),
	array(
		"id" => "USE_COUPONS",
		"content" => GetMessage("DSC_USE_COUPONS"),
		"sort" => "USE_COUPONS",
		"default" => true
	)
));

$arSelectFieldsMap = array(
	"ID" => false,
	"NAME" => false,
	"VALUE" => false,
	"ACTIVE" => false,
	"ACTIVE_FROM" => false,
	"ACTIVE_TO" => false,
	"PRIORITY" => false,
	"SORT" => false,
	"SITE_ID" => false,
	"MODIFIED_BY" => false,
	"TIMESTAMP_X" => false,
	"MAX_DISCOUNT" => false,
	"RENEWAL" => false,
	"CREATED_BY" => false,
	"DATE_CREATE" => false,
	"XML_ID" => false,
	"CURRENCY" => false,
	"LAST_DISCOUNT" => false,
	"USE_COUPONS" => false
);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if (!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

if (in_array('VALUE', $arSelectFields) || in_array('MAX_DISCOUNT', $arSelectFields))
{
	if (!in_array('VALUE_TYPE', $arSelectFields))
		$arSelectFields[] = 'VALUE_TYPE';
	if (!in_array('CURRENCY', $arSelectFields))
		$arSelectFields[] = 'CURRENCY';
}

$arSelectFields = array_values($arSelectFields);
$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

$siteList = array();
$arSiteList = array();
$arSiteLinkList = array();
$iterator = Main\SiteTable::getList(array(
	'select' => array('LID', 'SORT', 'NAME'),
	'order' => array('SORT' => 'ASC')
));
while ($row = $iterator->fetch())
{
	$siteList[] = $row;
	$arSiteList[$row['LID']] = $row['LID'];
	$arSiteLinkList[$row['LID']] = '<a href="/bitrix/admin/site_edit.php?lang='.LANGUAGE_ID.'&LID='.$row['LID'].'" title="'.GetMessage('BT_CAT_DISCOUNT_ADM_MESS_SITE_ID').'">'.$row['LID'].'</a>';
}
unset($row, $iterator);

$arCurrencyList = array();
if ($arSelectFieldsMap['CURRENCY'])
{
	$currencyList = array_keys(Currency\CurrencyManager::getCurrencyList());
	foreach ($currencyList as $currency)
		$arCurrencyList[$currency] = $currency;
	unset($currencyList);
}

$arNavParams = (isset($_REQUEST['mode']) && 'excel' == $_REQUEST["mode"]
	? false
	: array("nPageSize" => CAdminResult::GetNavSize($sTableID))
);

$dbResultList = CCatalogDiscount::GetList(
	array($by => $order),
	$arFilter,
	false,
	$arNavParams,
	$arSelectFields
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("DSC_NAV")));

$arUserList = array();
$arUserID = array();
$nameFormat = CSite::GetNameFormat(true);

$arRows = array();

while ($arDiscount = $dbResultList->Fetch())
{
	$arDiscount['ID'] = (int)$arDiscount['ID'];
	if ($arSelectFieldsMap['CREATED_BY'])
	{
		$arDiscount['CREATED_BY'] = (int)$arDiscount['CREATED_BY'];
		if ($arDiscount['CREATED_BY'] > 0)
			$arUserID[$arDiscount['CREATED_BY']] = true;
	}
	if ($arSelectFieldsMap['MODIFIED_BY'])
	{
		$arDiscount['MODIFIED_BY'] = (int)$arDiscount['MODIFIED_BY'];
		if ($arDiscount['MODIFIED_BY'] > 0)
			$arUserID[$arDiscount['MODIFIED_BY']] = true;
	}

	$arRows[$arDiscount['ID']] = $row = &$lAdmin->AddRow($arDiscount['ID'], $arDiscount);

	if ($arSelectFieldsMap['DATE_CREATE'])
		$row->AddCalendarField("DATE_CREATE", false);
	if ($arSelectFieldsMap['TIMESTAMP_X'])
		$row->AddCalendarField("TIMESTAMP_X", false);

	$row->AddViewField("ID", '<a href="/bitrix/admin/cat_discount_edit.php?lang='.LANGUAGE_ID.'&ID='.$arDiscount["ID"].'">'.$arDiscount["ID"].'</a>');
	$row->AddCheckField('USE_COUPONS', false);

	if ($bReadOnly)
	{
		if ($arSelectFieldsMap['SITE_ID'])
			$row->AddViewField('SITE_ID',$arSiteLinkList[$arDiscount['SITE_ID']]);
		if ($arSelectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField("ACTIVE_FROM", false);
		if ($arSelectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField("ACTIVE_TO", false);
		if ($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE", false);
		if ($arSelectFieldsMap['NAME'])
			$row->AddInputField("NAME", false);
		if ($arSelectFieldsMap['SORT'])
			$row->AddInputField("SORT", false);
		if ($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", false);
		if ($arSelectFieldsMap['CURRENCY'])
			$row->AddViewField("CURRENCY", $arDiscount['CURRENCY']);
		if ($arSelectFieldsMap['PRIORITY'])
			$row->AddInputField("PRIORITY", false);
		if ($arSelectFieldsMap['LAST_DISCOUNT'])
			$row->AddCheckField("LAST_DISCOUNT", false);
	}
	else
	{
		if ($arSelectFieldsMap['SITE_ID'])
		{
			$row->AddSelectField("SITE_ID", $arSiteList);
			$row->AddViewField('SITE_ID',$arSiteLinkList[$arDiscount['SITE_ID']]);
		}
		if ($arSelectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField("ACTIVE_FROM");
		if ($arSelectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField("ACTIVE_TO");
		if ($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE");
		if ($arSelectFieldsMap['NAME'])
			$row->AddInputField("NAME", array("size" => 30));
		if ($arSelectFieldsMap['SORT'])
			$row->AddInputField("SORT", array("size" => 4));
		if ($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", array("size" => 20));
		if ($arSelectFieldsMap['CURRENCY'])
			$row->AddSelectField("CURRENCY", $arCurrencyList);
		if ($arSelectFieldsMap['PRIORITY'])
			$row->AddInputField("PRIORITY");
		if ($arSelectFieldsMap['LAST_DISCOUNT'])
			$row->AddCheckField("LAST_DISCOUNT");
	}

	if ($arSelectFieldsMap['VALUE'])
	{
		$strDiscountValue = '';
		if ($arDiscount["VALUE_TYPE"] == CCatalogDiscount::TYPE_PERCENT)
		{
			$strDiscountValue = $arDiscount["VALUE"]."%";
		}
		elseif ($arDiscount["VALUE_TYPE"] == CCatalogDiscount::TYPE_SALE)
		{
			$strDiscountValue = '= '.CCurrencyLang::CurrencyFormat($arDiscount["VALUE"], $arDiscount["CURRENCY"], true);
		}
		else
		{
			$strDiscountValue = CCurrencyLang::CurrencyFormat($arDiscount["VALUE"], $arDiscount["CURRENCY"], true);
		}
		$row->AddViewField("VALUE", $strDiscountValue);
	}

	if ($arSelectFieldsMap['MAX_DISCOUNT'])
	{
		$row->AddViewField("MAX_DISCOUNT", (0 < $arDiscount['MAX_DISCOUNT'] ? CCurrencyLang::CurrencyFormat($arDiscount['MAX_DISCOUNT'], $arDiscount["CURRENCY"], true) : GetMessage('DSC_MAX_DISCOUNT_UNLIM')));
	}

	if ($arSelectFieldsMap['RENEWAL'])
		$row->AddCheckField("RENEWAL", false);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("DSC_UPDATE_ALT"),
		"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_discount_edit.php?ID=".$arDiscount['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false).""),
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		$arActions[] = array(
			"ICON" => "copy",
			"DEFAULT" => false,
			"TEXT" => GetMessage("BT_CAT_DISCOUNT_ADM_CONT_COPY"),
			"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_discount_edit.php?ID=".$arDiscount['ID'].'&action=copy&lang='.LANGUAGE_ID)
		);

		$arActions[] = array(
			"SEPARATOR" => true
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("DSC_DELETE_ALT"),
			"ACTION" => "if(confirm('".GetMessageJS('DSC_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($arDiscount['ID'], "delete")
		);
	}

	$row->AddActions($arActions);
}
if (isset($row))
	unset($row);

if ($arSelectFieldsMap['CREATED_BY'] || $arSelectFieldsMap['MODIFIED_BY'])
{
	if (!empty($arUserID))
	{
		$userIterator = Main\UserTable::getList(array(
			'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'),
			'filter' => array('@ID' => array_keys($arUserID)),
		));
		while ($oneUser = $userIterator->fetch())
		{
			$oneUser['ID'] = (int)$oneUser['ID'];
			if ($canViewUserList)
				$arUserList[$oneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$oneUser['ID'].'">'.CUser::FormatName($nameFormat, $oneUser).'</a>';
			else
				$arUserList[$oneUser['ID']] = CUser::FormatName($nameFormat, $oneUser);
		}
		unset($oneUser, $userIterator);
	}

	/** @var CAdminListRow $row */
	foreach ($arRows as &$row)
	{
		if ($arSelectFieldsMap['CREATED_BY'])
		{
			$strCreatedBy = '';
			if ($row->arRes['CREATED_BY'] > 0 && isset($arUserList[$row->arRes['CREATED_BY']]))
			{
				$strCreatedBy = $arUserList[$row->arRes['CREATED_BY']];
			}
			$row->AddViewField("CREATED_BY", $strCreatedBy);
		}
		if ($arSelectFieldsMap['MODIFIED_BY'])
		{
			$strModifiedBy = '';
			if ($row->arRes['MODIFIED_BY'] > 0 && isset($arUserList[$row->arRes['MODIFIED_BY']]))
			{
				$strModifiedBy = $arUserList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		}
	}
	if (isset($row))
		unset($row);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
			"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
			"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		)
	);
}

$aContext = array();
if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("CDAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "cat_discount_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("CDAN_ADD_NEW_ALT")
		),
	);
}
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("DISCOUNT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("DSC_ACTIVE"),
		GetMessage("DSC_PERIOD"),
		GetMessage("DSC_NAME"),
		GetMessage("DSC_COUPON"),
		GetMessage("DSC_RENEW"),
		GetMessage("DSC_VALUE"),
		GetMessage('DSC_FILTER_USE_COUPONS')
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("DSC_SITE") ?>:</td>
		<td><?
			$siteSize = count($siteList);
			if ($siteSize > 10)
				$siteSize = 10;
			elseif ($siteSize < 3)
				$siteSize = 3;
			?><select name="filter_site_id[]" multiple size="<?=$siteSize; ?>"><?
			foreach ($siteList as $row)
			{
				?><option value="<?=$row['LID']; ?>"<?=(in_array($row['LID'], $filterSite) ? ' selected' : ''); ?>>[<?=$row['LID']; ?>]&nbsp;<?=htmlspecialcharsbx($row['NAME']); ?></option><?
			}
			unset($row);
			?></select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_ACTIVE") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?=htmlspecialcharsbx("(".GetMessage("DSC_ALL").")"); ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("DSC_YES")); ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("DSC_NO")); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_PERIOD") ?> (<?= CSite::GetDateFormat("SHORT") ?>):</td>
		<td>
			<?echo CalendarPeriod("filter_date_active_from", $filter_date_active_from, "filter_date_active_to", $filter_date_active_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_NAME") ?>:</td>
		<td>
			<input type="text" name="filter_name" size="50" value="<?=htmlspecialcharsbx($filter_name); ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_COUPON") ?>:</td>
		<td>
			<input type="text" name="filter_coupon" size="50" value="<?=htmlspecialcharsbx($filter_coupon); ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_RENEW") ?>:</td>
		<td>
			<select name="filter_renewal">
				<option value=""><?=htmlspecialcharsbx("(".GetMessage("DSC_ALL").")"); ?></option>
				<option value="Y"<?if ($filter_renewal=="Y") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("DSC_YES")); ?></option>
				<option value="N"<?if ($filter_renewal=="N") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("DSC_NO")); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><? echo GetMessage('DSC_VALUE'); ?>:</td>
		<td>
			<input type="text" name="filter_value_start" value="<?=htmlspecialcharsbx($filter_value_start); ?>" size="15">
			...
			<input type="text" name="filter_value_end" value="<?=htmlspecialcharsbx($filter_value_end); ?>" size="15">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("DSC_FILTER_USE_COUPONS") ?>:</td>
		<td>
			<select name="filter_use_coupons">
				<option value=""><?=htmlspecialcharsbx("(".GetMessage("DSC_ALL").")"); ?></option>
				<option value="Y"<?if ($filter_use_coupons=="Y") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("DSC_YES")); ?></option>
				<option value="N"<?if ($filter_use_coupons=="N") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("DSC_NO")); ?></option>
			</select>
		</td>
	</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");