<?
use Bitrix\Main,
	Bitrix\Currency,
	Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

global $APPLICATION, $DB, $USER;

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_discount');

if (!Catalog\Config\Feature::isCumulativeDiscountsEnabled())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("CAT_FEATURE_NOT_ALLOW"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_catalog_disc_save";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = array(
	"find_id_from",
	"find_id_to",
	"find_site_id",
	"find_name",
	"find_active",
	"find_currency",
	"find_active_from_from",
	"find_active_from_to",
	"find_active_to_from",
	"find_active_to_to",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = array();

$filterSite = array();
if (!empty($find_site_id))
{
	if (!is_array($find_site_id))
		$find_site_id = ($find_site_id == 'NOT_REF' ? array() : array($find_site_id));
	$filterSite = $find_site_id;
}
if (!empty($filterSite))
	$arFilter["@SITE_ID"] = $filterSite;

if (!empty($find_id_from))
	$arFilter['>=ID'] = $find_id_from;
if (!empty($find_id_to))
	$arFilter['<=ID'] = $find_id_to;
if ($find_name <> '')
	$arFilter['%NAME'] = $find_name;
if (!empty($find_active))
	$arFilter['ACTIVE'] = $find_active;
if (!empty($find_currency))
	$arFilter['CURRENCY'] = $find_currency;
if (!empty($find_active_from_from))
	$arFilter['+>=ACTIVE_FROM'] = $find_active_from_from;
if (!empty($find_active_from_to))
	$arFilter['+<=ACTIVE_FROM'] = $find_active_from_to;
if (!empty($find_active_to_from))
	$arFilter['+>=ACTIVE_TO'] = $find_active_to_from;
if (!empty($find_active_to_to))
	$arFilter['+<=ACTIVE_TO'] = $find_active_to_to;

if($lAdmin->EditAction() && !$bReadOnly)
{
	$obDiscSave = new CCatalogDiscountSave();
	foreach($_POST['FIELDS'] as $ID=>$arFields)
	{
		$ID = (int)$ID;
		if($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();

		if(($rsDiscSaves = $obDiscSave->GetList(array(), array('ID' => $ID), false, false, array('ID'))) && ($arData = $rsDiscSaves->Fetch()))
		{
			if(!$obDiscSave->Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
					$lAdmin->AddGroupError(str_replace('#ERR#',$ex->GetString(),GetMessage("BT_CAT_DISC_SAVE_ADM_ERR_UPDATE_ERR")), $ID);
				else
					$lAdmin->AddGroupError(GetMessage("BT_CAT_DISC_SAVE_ADM_ERR_UPDATE_UNKNOWN"), $ID);
				$DB->Rollback();
			}
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage('BT_CAT_DISC_SAVE_ADM_ERR_UPDATE_ABSENT'), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if(($arID = $lAdmin->GroupAction()) && !$bReadOnly)
{
	$obDiscSave = new CCatalogDiscountSave();
	if($_REQUEST['action_target']=='selected')
	{
		$rsDiscSaves = $obDiscSave->GetList(array($by => $order), $arFilter, false, false, array('ID'));
		while($arRes = $rsDiscSaves->Fetch())
			$arID[] = (int)$arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID <= 0)
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CCatalogDiscountSave::Delete($ID))
			{
				if ($ex = $APPLICATION->GetException())
				{
					$lAdmin->AddGroupError(str_replace('#ERR#',$ex->GetString(),GetMessage("BT_CAT_DISC_SAVE_ADM_ERR_DELETE_ERR")), $ID);
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("BT_CAT_DISC_SAVE_ADM_ERR_UPDATE_DELETE"), $ID);
				}
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}
			break;
		case "activate":
		case "deactivate":
			if(($rsDiscSaves = $obDiscSave->GetByID($ID)) && ($arFields = $rsDiscSaves->Fetch()))
			{
				$arFields["ACTIVE"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
				if(!$obDiscSave->Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
					{
						$lAdmin->AddGroupError(str_replace('#ERR#',$ex->GetString(),GetMessage("BT_CAT_DISC_SAVE_ADM_ERR_UPDATE_ERR")), $ID);
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("BT_CAT_DISC_SAVE_ADM_ERR_UPDATE_UNKNOWN"), $ID);
					}
				}
			}
			else
			{
				$lAdmin->AddGroupError(GetMessage('BT_CAT_DISC_SAVE_ADM_ERR_UPDATE_ABSENT'), $ID);
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
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "SITE_ID",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_SITE_ID"),
		"sort" => "SITE_ID",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_NAME"),
		"sort" => "NAME",
		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true,
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_SORT"),
		"sort" => "SORT",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" =>"CURRENCY",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_CURRENCY"),
		"sort" => "CURRENCY",
	),
	array(
		"id" =>"ACTIVE_FROM",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_ACTIVE_FROM"),
		"sort" => "ACTIVE_FROM",
	),
	array(
		"id" => "ACTIVE_TO",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_ACTIVE_TO"),
		"sort" => "ACTIVE_TO",
	),
	array(
		"id" => "ACTION",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_ACTION"),
		"sort" => "",
	),
	array(
		"id" =>"COUNT_FROM",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_COUNT_FROM"),
		"sort" => "COUNT_FROM",
	),
	array(
		"id" => "COUNT_TO",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_COUNT_TO"),
		"sort" => "COUNT_TO",
	),
	array(
		"id" => "COUNT",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_COUNT"),
		"sort" => "",
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_XML_ID"),
		"sort" => "XML_ID",
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage('BT_CAT_DISC_SAVE_ADM_TITLE_MODIFIED_BY'),
		"sort" => "MODIFIED_BY",
		"default" => true
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage('BT_CAT_DISC_SAVE_ADM_TITLE_TIMESTAMP_X'),
		"sort" => "TIMESTAMP_X",
		"default" => true
	),
	array(
		"id" => "CREATED_BY",
		"content" => GetMessage('BT_CAT_DISC_SAVE_ADM_TITLE_CREATED_BY'),
		"sort" => "CREATED_BY",
		"default" => false
	),
	array(
		"id" => "DATE_CREATE",
		"content" => GetMessage('BT_CAT_DISC_SAVE_ADM_TITLE_DATE_CREATE'),
		"sort" => "DATE_CREATE",
		"default" => false
	),
));

$arSelectFieldsMap = array(
	"ID" => false,
	"SITE_ID" => false,
	"NAME" => false,
	"ACTIVE" => false,
	"SORT" => false,
	"CURRENCY" => false,
	"ACTIVE_FROM" => false,
	"ACTIVE_TO" => false,
	"ACTION" => false,
	"COUNT_FROM" => false,
	"COUNT_TO" => false,
	"COUNT" => false,
	"XML_ID" => false,
	"MODIFIED_BY" => false,
	"TIMESTAMP_X" => false,
	"CREATED_BY" => false,
	"DATE_CREATE" => false,
);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();

if (!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

$intKey = array_search('ACTION', $arSelectFields);
if (false !== $intKey)
{
	if (!in_array('ACTION_SIZE', $arSelectFields))
		$arSelectFields[] = 'ACTION_SIZE';
	if (!in_array('ACTION_TYPE', $arSelectFields))
		$arSelectFields[] = 'ACTION_TYPE';
}

$intKey = array_search('COUNT', $arSelectFields);
if (false !== $intKey)
{
	if (!in_array('COUNT_SIZE', $arSelectFields))
		$arSelectFields[] = 'COUNT_SIZE';
	if (!in_array('COUNT_TYPE', $arSelectFields))
		$arSelectFields[] = 'COUNT_TYPE';
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

$arPeriodTypeList = CCatalogDiscountSave::GetPeriodTypeList(true);

$arUserList = array();
$arUserID = array();
$strNameFormat = CSite::GetNameFormat(true);

$arNavParams = (isset($_REQUEST["mode"]) && "excel" == $_REQUEST["mode"]
	? false
	: array("nPageSize" => CAdminResult::GetNavSize($sTableID))
);

$obDiscSave = new CCatalogDiscountSave();
$rsDiscSaves = $obDiscSave->GetList(
	array($by=>$order),
	$arFilter,
	false,
	$arNavParams,
	$arSelectFields
);

$rsDiscSaves = new CAdminResult($rsDiscSaves, $sTableID);

$rsDiscSaves->NavStart();

$lAdmin->NavText($rsDiscSaves->GetNavPrint(GetMessage("BT_CAT_DISC_SAVE_ADM_DISCOUNTS")));

$arRows = array();

while($arRes = $rsDiscSaves->Fetch())
{
	$arRes['ID'] = (int)$arRes['ID'];
	if ($arSelectFieldsMap['CREATED_BY'])
	{
		$arRes['CREATED_BY'] = (int)$arRes['CREATED_BY'];
		if (0 < $arRes['CREATED_BY'])
			$arUserID[$arRes['CREATED_BY']] = true;
	}
	if ($arSelectFieldsMap['MODIFIED_BY'])
	{
		$arRes['MODIFIED_BY'] = (int)$arRes['MODIFIED_BY'];
		if (0 < $arRes['MODIFIED_BY'])
			$arUserID[$arRes['MODIFIED_BY']] = true;
	}

	$arRows[$arRes['ID']] = $row = &$lAdmin->AddRow($arRes['ID'], $arRes);

	if ($arSelectFieldsMap['DATE_CREATE'])
		$row->AddCalendarField("DATE_CREATE", false);
	if ($arSelectFieldsMap['TIMESTAMP_X'])
		$row->AddCalendarField("TIMESTAMP_X", false);

	$row->AddViewField("ID", '<a href="/bitrix/admin/cat_discsave_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes["ID"].'">'.$arRes["ID"].'</a>');

	if ($arSelectFieldsMap['ACTION'])
	{
		if (intval($arRes['ACTION_SIZE']) == 0)
		{
			$strViewAction = '';
		}
		else
		{
			$strViewAction = str_replace('#TYPE#',htmlspecialcharsEx($arPeriodTypeList[$arRes['ACTION_TYPE']]),GetMessage('BT_CAT_DISC_SAVE_ADM_MESS_ACTION_TYPE')).'<br />'.str_replace('#SIZE#',$arRes['ACTION_SIZE'],GetMessage('BT_CAT_DISC_SAVE_ADM_MESS_ACTION_SIZE'));
		}
		$strHtmlAction = '<input type="text" name="FIELDS['.$arRes['ID'].'][ACTION_SIZE]" size="3" value="'.intval($arRes['ACTION_SIZE']).'"> ';
		$strHtmlAction .= '<select name="FIELDS['.$arRes['ID'].'][ACTION_TYPE]">';
		foreach ($arPeriodTypeList as $strTypeID => $strTypeName)
		{
			$strHtmlAction .= '<option value="'.htmlspecialcharsbx($strTypeID).'" '.($strTypeID == $arRes['ACTION_TYPE'] ? 'selected' : '').'>'.htmlspecialcharsEx($strTypeName).'</option>';
		}
		$strHtmlAction .= '</select>';
	}

	if ($arSelectFieldsMap['COUNT'])
	{
		if (intval($arRes['COUNT_SIZE']) == 0)
		{
			$strViewCount = '';
		}
		else
		{
			$strViewCount = str_replace('#TYPE#',htmlspecialcharsEx($arPeriodTypeList[$arRes['COUNT_TYPE']]),GetMessage('BT_CAT_DISC_SAVE_ADM_MESS_COUNT_TYPE')).'<br />'.str_replace('#SIZE#',$arRes['COUNT_SIZE'],GetMessage('BT_CAT_DISC_SAVE_ADM_MESS_COUNT_SIZE'));
		}
		$strHtmlCount = '<input type="text" name="FIELDS['.$arRes['ID'].'][COUNT_SIZE]" size="3" value="'.intval($arRes['COUNT_SIZE']).'"> ';
		$strHtmlCount .= '<select name="FIELDS['.$arRes['ID'].'][COUNT_TYPE]">';
		foreach ($arPeriodTypeList as $strTypeID => $strTypeName)
		{
			$strHtmlCount .= '<option value="'.htmlspecialcharsbx($strTypeID).'" '.($strTypeID == $arRes['COUNT_TYPE'] ? 'selected' : '').'>'.htmlspecialcharsEx($strTypeName).'</option>';
		}
		$strHtmlCount .= '</select>';
	}

	if (!$bReadOnly)
	{
		if ($arSelectFieldsMap['SITE_ID'])
		{
			$row->AddSelectField("SITE_ID", $arSiteList);
			$row->AddViewField('SITE_ID',$arSiteLinkList[$arRes['SITE_ID']]);
		}
		if ($arSelectFieldsMap['NAME'])
			$row->AddInputField("NAME", array("size" => 30));
		if ($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE");
		if ($arSelectFieldsMap['SORT'])
			$row->AddInputField("SORT", array("size" => 4));
		if ($arSelectFieldsMap['CURRENCY'])
			$row->AddSelectField("CURRENCY", $arCurrencyList);

		if ($arSelectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField("ACTIVE_FROM");
		if ($arSelectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField("ACTIVE_TO");
		if ($arSelectFieldsMap['ACTION'])
		{
			$row->AddViewField('ACTION',$strViewAction);
			$row->AddEditField('ACTION',$strHtmlAction);
		}

		if ($arSelectFieldsMap['COUNT_FROM'])
			$row->AddCalendarField("COUNT_FROM");
		if ($arSelectFieldsMap['COUNT_TO'])
			$row->AddCalendarField("COUNT_TO");
		if ($arSelectFieldsMap['COUNT'])
		{
			$row->AddViewField('COUNT',$strViewCount);
			$row->AddEditField('COUNT',$strHtmlCount);
		}

		if ($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", array("size" => 20));
	}
	else
	{
		if ($arSelectFieldsMap['SITE_ID'])
			$row->AddViewField('SITE_ID',$arSiteLinkList[$arRes['SITE_ID']]);
		if ($arSelectFieldsMap['NAME'])
			$row->AddViewField("NAME", '<a href="/bitrix/admin/cat_discsave_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes["ID"].'">'.htmlspecialcharsEx($arRes['NAME']).'</a>');
		if ($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE", false);
		if ($arSelectFieldsMap['SORT'])
			$row->AddInputField('SORT', false);

		if ($arSelectFieldsMap['ACTIVE_FROM'])
			$row->AddCalendarField("ACTIVE_FROM", false);
		if ($arSelectFieldsMap['ACTIVE_TO'])
			$row->AddCalendarField("ACTIVE_TO", false);
		if ($arSelectFieldsMap['ACTION'])
			$row->AddViewField('ACTION', $strViewAction);

		if ($arSelectFieldsMap['COUNT_FROM'])
			$row->AddCalendarField("COUNT_FROM", false);
		if ($arSelectFieldsMap['COUNT_TO'])
			$row->AddCalendarField("COUNT_TO", false);
		if ($arSelectFieldsMap['COUNT'])
			$row->AddViewField('COUNT',$strViewCount);

		if ($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", false);

		if ($arSelectFieldsMap['CURRENCY'])
			$row->AddViewField("CURRENCY", $arRes['CURRENCY']);
	}

	$arActions = array();

	$arActions[] = array(
		"ICON" => "edit",
		"DEFAULT" => true,
		"TEXT" => GetMessage("BT_CAT_DISC_SAVE_ADM_CONT_EDIT"),
		"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_discsave_edit.php?ID=".$arRes['ID'].'&lang='.LANGUAGE_ID)
	);
	if (!$bReadOnly)
	{
		$arActions[] = array(
			"ICON" => "copy",
			"DEFAULT" => false,
			"TEXT" => GetMessage("BT_CAT_DISC_SAVE_ADM_CONT_COPY"),
			"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_discsave_edit.php?ID=".$arRes['ID'].'&action=copy&lang='.LANGUAGE_ID)
		);

		$arActions[] = array(
			"SEPARATOR" => true
		);

		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("BT_CAT_DISC_SAVE_ADM_CONT_DELETE"),
			"ACTION" => "if(confirm('".GetMessageJS('BT_CAT_DISC_SAVE_ADM_CONT_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($arRes['ID'], "delete")
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
		$rsUsers = CUser::GetList(
			'ID',
			'ASC',
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
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsDiscSaves->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	));
}

$aContext = array();
if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("BT_CAT_DISC_SAVE_ADM_PAGECONT_ADD"),
			"LINK" => "/bitrix/admin/cat_discsave_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("BT_CAT_DISC_SAVE_ADM_PAGECONT_ADD_TITLE"),
			"ICON" => "btn_new",
		),
	);
}
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("BT_CAT_DISC_SAVE_ADM_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_SITE_ID"),
		GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_NAME2"),
		GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_ACTIVE2"),
		GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_CURRENCY"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><? echo "ID" ?>:</td>
	<td>
		<input type="text" name="find_id_from" size="10" value="<?echo htmlspecialcharsbx($find_id_from)?>">
			...
		<input type="text" name="find_id_to" size="10" value="<?echo htmlspecialcharsbx($find_id_to)?>">
	</td>
</tr>
<tr>
	<td><? echo GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_SITE_ID"); ?>:</td>
	<td><?
		$siteSize = count($siteList);
		if ($siteSize > 10)
			$siteSize = 10;
		elseif ($siteSize < 3)
			$siteSize = 3;
		?><select name="find_site_id[]" multiple size="<?=$siteSize; ?>"><?
		foreach ($siteList as $row)
		{
			?><option value="<?=$row['LID']; ?>"<?=(in_array($row['LID'], $filterSite) ? ' selected' : ''); ?>>[<?=$row['LID']; ?>]&nbsp;<?=htmlspecialcharsEx($row['NAME']); ?></option><?
		}
		unset($row);
		?></select>
	</td>
</tr>
<tr>
	<td><? echo GetMessage("BT_CAT_DISC_SAVE_ADM_TITLE_NAME2")?>:</td>
	<td><input type="text" size="30" name="find_name" value="<? echo htmlspecialcharsbx($find_name); ?>"></td>
</tr>
<tr>
	<td><? echo GetMessage('BT_CAT_DISC_SAVE_ADM_TITLE_ACTIVE2') ?>:</td>
	<td><select name="find_active">
		<option value=""><? echo htmlspecialcharsEx(GetMessage('BT_CAT_DISC_SAVE_ADM_MESS_ACTIVE_ANY'))?></option>
		<option value="Y"<?if($find_active=="Y")echo " selected"?>><? echo htmlspecialcharsEx(GetMessage("BT_CAT_DISC_SAVE_ADM_MESS_ACTIVE_YES"))?></option>
		<option value="N"<?if($find_active=="N")echo " selected"?>><? echo htmlspecialcharsEx(GetMessage("BT_CAT_DISC_SAVE_ADM_MESS_ACTIVE_NO"))?></option>
		</select>
	</td>
</tr>
<tr>
	<td><? echo htmlspecialcharsEx(GetMessage('BT_CAT_DISC_SAVE_ADM_TITLE_CURRENCY')); ?>:</td>
	<td><select name="find_currency"><option value="" <? echo ($find_currency == '' ? 'selected' : ''); ?>><? echo htmlspecialcharsEx(GetMessage('BT_CAT_DISC_SAVE_ADM_MESS_CURRENCY_ANY')); ?></option>
	<?
	foreach ($arCurrencyList as $strCurrencyID => $strCurrencyName)
	{
		?><option value="<? echo htmlspecialcharsbx($strCurrencyID); ?>" <? echo ($strCurrencyID == $find_currency ? 'selected' : ''); ?>><? echo htmlspecialcharsEx($strCurrencyName); ?></option><?
	}
	?>
	</select></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?></form><?

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");