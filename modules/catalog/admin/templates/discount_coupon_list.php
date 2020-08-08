<?
use Bitrix\Catalog;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

global $APPLICATION;

/*
* B_ADMIN_SUBCOUPONS
* if defined and equal 1 - working, another die
* B_ADMIN_SUBCOUPONS_LIST - true/false
* if not defined - die
* if equal true - get list mode
* 	include prolog and epilog
* other - get simple html
*
* need variables
* 		$strSubElementAjaxPath - path for ajax
*		$intDiscountID - ID for filter
*		$strSubTMP_ID - string identifier for link with new product ($intSubPropValue = 0, in edit form send -1)
*
*
*created variables
*		$arSubElements - array subelements for product with ID = 0
*/
if ((false == defined('B_ADMIN_SUBCOUPONS')) || (1 != B_ADMIN_SUBCOUPONS))
	return '';
if (false == defined('B_ADMIN_SUBCOUPONS_LIST'))
	return '';

$strSubElementAjaxPath = trim($strSubElementAjaxPath);

if ($_REQUEST['mode']=='list' || $_REQUEST['mode']=='frame')
	CFile::DisableJSFunction(true);

$intDiscountID = intval($intDiscountID);
$strSubTMP_ID = intval($strSubTMP_ID);

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/catalog/admin/cat_discount_coupon.php");
IncludeModuleLangFile(__FILE__);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');

$sTableID = 'tbl_catalog_sub_coupon_'.md5('.');

$arHideFields = array('DISCOUNT_ID');
$lAdmin = new CAdminSubList($sTableID, false, $strSubElementAjaxPath, $arHideFields);

$arFilterFields = array(
	"find_discount_id",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"DISCOUNT_ID" => $intDiscountID,
);

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	return '';

$boolCouponsReadOnly = (isset($boolCouponsReadOnly) && false === $boolCouponsReadOnly ? false : true);

if ($lAdmin->EditAction() && !$boolCouponsReadOnly)
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


if (($arID = $lAdmin->GroupAction()) && !$boolCouponsReadOnly)
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

$CAdminCalendar_ShowScript = '';
if (true == B_ADMIN_SUBCOUPONS_LIST)
	$CAdminCalendar_ShowScript = CAdminCalendar::ShowScript();

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
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

$arUserList = array();
$arUserID = array();
$strNameFormat = CSite::GetNameFormat(true);

if (!(false == B_ADMIN_SUBCOUPONS_LIST && $bCopy))
{
	$arNavParams = (isset($_REQUEST['mode']) && 'excel' == $_REQUEST["mode"]
		? false
		: array("nPageSize" => CAdminSubResult::GetNavSize($sTableID, 20, $lAdmin->GetListUrl(true)))
	);

	$dbResultList = CCatalogDiscountCoupon::GetList(
		array($by => $order),
		$arFilter,
		false,
		$arNavParams,
		$arSelectFields
	);
	$dbResultList = new CAdminSubResult($dbResultList, $sTableID, $lAdmin->GetListUrl(true));
	$dbResultList->NavStart();
	$lAdmin->NavText($dbResultList->GetNavPrint(htmlspecialcharsbx(GetMessage("DSC_NAV"))));

	$arRows = array();

	while ($arCouponDiscount = $dbResultList->Fetch())
	{
		$edit_url = '/bitrix/admin/cat_subcoupon_edit.php?ID='.$arCouponDiscount['ID'].'&DISCOUNT_ID='.$intDiscountID.'&lang='.LANGUAGE_ID.'&TMP_ID='.$strSubTMP_ID;

		$arCouponDiscount['ID'] = (int)$arCouponDiscount['ID'];
		if ($arSelectFieldsMap['CREATED_BY'])
		{
			$arCouponDiscount['CREATED_BY'] = (int)$arCouponDiscount['CREATED_BY'];
			if (0 < $arCouponDiscount['CREATED_BY'])
				$arUserID[$arCouponDiscount['CREATED_BY']] = true;
		}
		if ($arSelectFieldsMap['MODIFIED_BY'])
		{
			$arCouponDiscount['MODIFIED_BY'] = (int)$arCouponDiscount['MODIFIED_BY'];
			if (0 < $arCouponDiscount['MODIFIED_BY'])
				$arUserID[$arCouponDiscount['MODIFIED_BY']] = true;
		}

		$arRows[$arCouponDiscount['ID']] = $row =& $lAdmin->AddRow($arCouponDiscount['ID'], $arCouponDiscount, $edit_url, '', true);

		if ($arSelectFieldsMap['DATE_CREATE'])
			$row->AddCalendarField("DATE_CREATE", false);
		if ($arSelectFieldsMap['TIMESTAMP_X'])
			$row->AddCalendarField("TIMESTAMP_X", false);

		$row->AddField("ID", $arCouponDiscount['ID']);
		if ($arSelectFieldsMap['DISCOUNT_NAME'])
			$row->AddEditField("DISCOUNT_NAME", false);

		if ($arSelectFieldsMap['ONE_TIME'])
			$row->AddViewField("ONE_TIME", htmlspecialcharsex($arCouponType[$arCouponDiscount['ONE_TIME']]));

		if ($boolCouponsReadOnly)
		{
			if ($arSelectFieldsMap['ACTIVE'])
				$row->AddCheckField("ACTIVE", false);
			if ($arSelectFieldsMap['COUPON'])
				$row->AddEditField("COUPON", false);
			if ($arSelectFieldsMap['DATE_APPLY'])
				$row->AddCalendarField("DATE_APPLY", false);
			if ($arSelectFieldsMap['DESCRIPTION'])
				$row->AddEditField("DESCRIPTION", false);
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
			"DEFAULT" => true,
			"ACTION"=>"(new BX.CAdminDialog({
				'content_url': '/bitrix/admin/cat_subcoupon_edit.php?ID=".$arCouponDiscount['ID']."&DISCOUNT_ID=".$intDiscountID."&lang=".LANGUAGE_ID."&TMP_ID=".$strSubTMP_ID."',
				'content_post': 'bxpublic=Y',
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
			})).Show();",
		);

		if (!$boolCouponsReadOnly)
		{
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("DSC_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessageJS('DSC_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($arCouponDiscount['ID'], "delete"));
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

	if (!$boolCouponsReadOnly)
	{
		$lAdmin->AddGroupActionTable(
			array(
				"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
				"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
				"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
			)
		);
	}

	if (!isset($_REQUEST["mode"]) || ('excel' != $_REQUEST["mode"] && 'subsettings' != $_REQUEST["mode"]))
	{
		?><script type="text/javascript">
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
		'content_url': '/bitrix/admin/cat_subcoupon_edit.php',
		'content_post': PostParams,
		'draggable': true,
		'resizable': true,
		'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
	})).Show();
}
</script><?

		$aContext = array();
		if (!$boolCouponsReadOnly)
		{
			if (0 < $intDiscountID)
			{
				$arAddMenu = array();
				$arAddMenu[] = array(
					"TEXT" => GetMessage("BT_CAT_DISC_COUPON_LIST_ADD_ONE_COUPON"),
					"TITLE" => GetMessage("BT_CAT_DISC_COUPON_LIST_ADD_ONE_COUPON_TITLE"),
					"LINK" => "javascript:ShowNewCoupons(".$intDiscountID.", 'N')",
					"SHOW_TITLE" => true
				);
				$arAddMenu[] = array(
					"TEXT" => GetMessage("BT_CAT_DISC_COUPON_LIST_ADD_MULTI_COUPON"),
					"TITLE" => GetMessage("BT_CAT_DISC_COUPON_LIST_ADD_MULTI_COUPON_TITLE"),
					"LINK" => "javascript:ShowNewCoupons(".$intDiscountID.", 'Y')",
					"SHOW_TITLE" => true
				);

				$aContext[] = array(
					"TEXT" => GetMessage("DSC_CPN_ADD"),
					"ICON" => "btn_new",
					"MENU" => $arAddMenu,
				);
			}
		}

		$aContext[] = array(
			"ICON"=>"btn_sub_refresh",
			"TEXT"=>htmlspecialcharsex(GetMessage('BT_CAT_DISC_COUPON_LIST_REFRESH')),
			"LINK" => "javascript:".$lAdmin->ActionAjaxReload($lAdmin->GetListUrl(true)),
			"TITLE"=>GetMessage("BT_CAT_DISC_COUPON_LIST_REFRESH_TITLE"),
		);

		$lAdmin->AddAdminContextMenu($aContext);
	}
	$lAdmin->CheckListMode();

	if (true == B_ADMIN_SUBCOUPONS_LIST)
	{
		echo $CAdminCalendar_ShowScript;
	}

	$lAdmin->DisplayList(B_ADMIN_SUBCOUPONS_LIST);
}
else
{
	ShowMessage(GetMessage('BT_CAT_DISC_COUPON_LIST_SHOW_AFTER_COPY'));
}
?>