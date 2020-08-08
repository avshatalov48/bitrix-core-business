<?
/**
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUser $USER
 */
use Bitrix\Main;
use Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Main\Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_discount');

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_catalog_discount_coupon";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_id_start",
	"filter_id_end",
	"filter_discount_id",
	"filter_active",
	"filter_coupon",
	"filter_one_time",
	"filter_apply_time_start",
	"filter_apply_time_end",
	"filter_description"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (!empty($filter_id_start))
	$arFilter[">=ID"] = $filter_id_start;
if (!empty($filter_id_end))
	$arFilter["<=ID"] = $filter_id_end;
if (!empty($filter_discount_id))
	$arFilter["DISCOUNT_ID"] = $filter_discount_id;
if (!empty($filter_active))
	$arFilter["ACTIVE"] = $filter_active;
if (!empty($filter_coupon))
	$arFilter["COUPON"] = $filter_coupon;
if (!empty($filter_one_time))
	$arFilter["ONE_TIME"] = $filter_one_time;
if (!empty($filter_apply_time_start))
	$arFilter[">=DATE_APPLY"] = $filter_apply_time_start;
if (!empty($filter_apply_time_end))
	$arFilter["<=DATE_APPLY"] = $filter_apply_time_end;
if (!empty($filter_description))
	$arFilter["%DESCRIPTION"] = $filter_description;

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CCatalogDiscountCoupon::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT_CPN")), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}


if (($arID = $lAdmin->GroupAction()) && !$bReadOnly)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogDiscountCoupon::GetList(
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
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if (!CCatalogDiscountCoupon::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_DELETE_DISCOUNT_CPN")), $ID);
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
				if (!CCatalogDiscountCoupon::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_DISCOUNT_CPN")), $ID);
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
		"default"=>true
	),
	array(
		"id" => "DISCOUNT_NAME",
		"content" => GetMessage("DSC_CPN_NAME"),
		"sort" => "DISCOUNT_NAME",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("DSC_CPN_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true
	),
	array(
		"id" => "COUPON",
		"content" => GetMessage("DSC_CPN_CPN"),
		"sort" => "COUPON",
		"default" => true
	),
	array(
		"id" => "DATE_APPLY",
		"content" => GetMessage("DSC_CPN_DATE"),
		"sort" => "DATE_APPLY",
		"default" => true
	),
	array(
		"id" => "ONE_TIME",
		"content" => GetMessage("DSC_CPN_TIME2"),
		"sort" => "ONE_TIME",
		"default" => true
	),
	array(
		"id" => "DESCRIPTION",
		"content" => GetMessage("DSC_CPN_DESCRIPTION"),
		"sort" => "",
		"default" => false
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
));

$arSelectFieldsMap = array(
	"ID" => false,
	"DISCOUNT_NAME" => false,
	"ACTIVE" => false,
	"COUPON" => false,
	"DATE_APPLY" => false,
	"ONE_TIME" => false,
	"DESCRIPTION" => false,
	"MODIFIED_BY" => false,
	"TIMESTAMP_X" => false,
	"CREATED_BY" => false,
	"DATE_CREATE" => false,
);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if (!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

$arSelectFields = array_values($arSelectFields);
$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

$arCouponType = Catalog\DiscountCouponTable::getCouponTypes(true);

$arNavParams = (isset($_REQUEST["mode"]) && 'excel' == $_REQUEST["mode"]
	? false
	: array("nPageSize" => CAdminResult::GetNavSize($sTableID))
);

$dbResultList = CCatalogDiscountCoupon::GetList(
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
$strNameFormat = CSite::GetNameFormat(true);

$arRows = array();

while ($arDiscount = $dbResultList->Fetch())
{
	$arDiscount['ID'] = (int)$arDiscount['ID'];
	if ($arSelectFieldsMap['CREATED_BY'])
	{
		$arDiscount['CREATED_BY'] = (int)$arDiscount['CREATED_BY'];
		if (0 < $arDiscount['CREATED_BY'])
			$arUserID[$arDiscount['CREATED_BY']] = true;
	}
	if ($arSelectFieldsMap['MODIFIED_BY'])
	{
		$arDiscount['MODIFIED_BY'] = (int)$arDiscount['MODIFIED_BY'];
		if (0 < $arDiscount['MODIFIED_BY'])
			$arUserID[$arDiscount['MODIFIED_BY']] = true;
	}

	$arRows[$arDiscount['ID']] = $row = &$lAdmin->AddRow($arDiscount['ID'], $arDiscount);

	if ($arSelectFieldsMap['DATE_CREATE'])
		$row->AddCalendarField("DATE_CREATE", false);
	if ($arSelectFieldsMap['TIMESTAMP_X'])
		$row->AddCalendarField("TIMESTAMP_X", false);

	$row->AddViewField("ID", '<a href="/bitrix/admin/cat_discount_coupon_edit.php?lang='.LANGUAGE_ID.'&ID='.$arDiscount["ID"].'">'.$arDiscount["ID"].'</a>');
	if ($arSelectFieldsMap['DISCOUNT_NAME'])
		$row->AddInputField("DISCOUNT_NAME", false);

	if ($arSelectFieldsMap['ONE_TIME'])
		$row->AddViewField("ONE_TIME", htmlspecialcharsex($arCouponType[$arDiscount['ONE_TIME']]));

	if ($bReadOnly)
	{
		if ($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE", false);
		if ($arSelectFieldsMap['COUPON'])
			$row->AddInputField("COUPON", false);
		if ($arSelectFieldsMap['DATE_APPLY'])
			$row->AddCalendarField("DATE_APPLY", false);
		if ($arSelectFieldsMap['DESCRIPTION'])
			$row->AddInputField("DESCRIPTION", false);
	}
	else
	{
		if ($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE");
		if ($arSelectFieldsMap['COUPON'])
			$row->AddInputField("COUPON", array("size" => 30));
		if ($arSelectFieldsMap['DATE_APPLY'])
			$row->AddCalendarField("DATE_APPLY");
		if ($arSelectFieldsMap['DESCRIPTION'])
			$row->AddInputField("DESCRIPTION");
	}

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("DSC_UPDATE_ALT"),
		"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_discount_coupon_edit.php?ID=".$arDiscount['ID']."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false).""),
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
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
		$byUser = 'ID';
		$byOrder = 'ASC';
		$rsUsers = CUser::GetList(
			$byUser,
			$byOrder,
			array('ID' => implode(' | ', array_keys($arUserID))),
			array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
		);
		while ($arOneUser = $rsUsers->Fetch())
		{
			$arOneUser['ID'] = (int)$arOneUser['ID'];
			$arUserList[$arOneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arOneUser['ID'].'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
		}
	}

	/** @var CAdminListRow $row */
	foreach ($arRows as &$row)
	{
		if ($arSelectFieldsMap['CREATED_BY'])
		{
			$strCreatedBy = '';
			if (0 < $row->arRes['CREATED_BY'] && isset($arUserList[$row->arRes['CREATED_BY']]))
			{
				$strCreatedBy = $arUserList[$row->arRes['CREATED_BY']];
			}
			$row->AddViewField("CREATED_BY", $strCreatedBy);
		}
		if ($arSelectFieldsMap['MODIFIED_BY'])
		{
			$strModifiedBy = '';
			if (0 < $row->arRes['MODIFIED_BY'] && isset($arUserList[$row->arRes['MODIFIED_BY']]))
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

if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("DSC_CPN_ADD"),
			"ICON" => "btn_new",
			"LINK" => "cat_discount_coupon_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("DSC_CPN_ADD_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("DSC_CPN_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("DSC_CPN_DISC"),
		GetMessage("DSC_CPN_ACT"),
		GetMessage("DSC_CPN_CPN"),
		GetMessage("DSC_CPN_TIME2"),
		GetMessage("DSC_CPN_DATE"),
		GetMessage("DSC_CPN_DESCRIPTION"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td>ID:</td>
		<td>
			<input type="text" name="filter_id_start" size="10" value="<?echo htmlspecialcharsex($filter_id_start)?>">
			...
			<input type="text" name="filter_id_end" size="10" value="<?echo htmlspecialcharsex($filter_id_end)?>">
		</td>
	</tr>
	<tr>
		<td><? echo GetMessage("DSC_CPN_DISC") ?>:</td>
		<td>
			<select name="filter_discount_id">
				<option value="">(<? echo GetMessage("DSC_CPN_ALL") ?>)</option>
				<?
				$dbDiscountList = CCatalogDiscount::GetList(
					array("NAME" => "ASC"),
					array(),
					false,
					false,
					array("ID", "SITE_ID", "NAME")
				);
				while ($arDiscountList = $dbDiscountList->Fetch())
				{
					?><option value="<? echo $arDiscountList["ID"] ?>"<?if ($filter_discount_id == $arDiscountList["ID"]) echo " selected";?>><? echo "[".$arDiscountList["ID"]."] ".htmlspecialcharsbx($arDiscountList["NAME"]." (".$arDiscountList["SITE_ID"].")") ?></option><?
				}
				unset($arDiscountList, $dbDiscountList);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><? echo GetMessage("DSC_CPN_ACT") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><? echo htmlspecialcharsex("(".GetMessage("DSC_CPN_ALL").")") ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><? echo htmlspecialcharsex(GetMessage("DSC_CPN_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><? echo htmlspecialcharsex(GetMessage("DSC_CPN_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><? echo GetMessage("DSC_CPN_CPN") ?>:</td>
		<td>
			<input type="text" name="filter_coupon" value="<?echo htmlspecialcharsbx($filter_coupon)?>" />
		</td>
	</tr>
	<tr>
		<td><? echo GetMessage("DSC_CPN_TIME2") ?>:</td>
		<td>
			<select name="filter_one_time">
				<option value=""><? echo htmlspecialcharsex("(".GetMessage("DSC_CPN_ALL").")") ?></option><?
				foreach ($arCouponType as $strType => $strName)
				{
					?><option value="<? echo $strType; ?>"<?if ($filter_one_time == $strType) echo " selected"; ?>><? echo htmlspecialcharsex($strName); ?></option><?
				}
				?></select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("DSC_CPN_DATE").":"?></td>
		<td><?echo CalendarPeriod("filter_apply_time_start", htmlspecialcharsex($filter_apply_time_start), "filter_apply_time_end", htmlspecialcharsex($filter_apply_time_end), "find_form")?></td>
	</tr>
	<tr>
		<td><? echo GetMessage("DSC_CPN_DESCRIPTION") ?>:</td>
		<td>
			<textarea name="filter_description"><?echo htmlspecialcharsbx($filter_description)?></textarea>
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