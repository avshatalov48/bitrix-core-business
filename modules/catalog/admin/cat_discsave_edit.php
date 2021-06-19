<?
use Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

global $APPLICATION;
global $DB;

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_discount');

if (!Catalog\Config\Feature::isCumulativeDiscountsEnabled())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	CCatalogDiscountSave::Disable();
	ShowError(GetMessage("CAT_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
IncludeModuleLangFile(__FILE__);

$APPLICATION->AddHeadScript('/bitrix/js/catalog/tbl_edit.js');

define('RANGE_EMPTY_ROW_SIZE',3);
define('RANGE_ROW_PREFIX','RNG');

function __cmpRange($a,$b)
{
	if ($a['RANGE_FROM'] == $b['RANGE_FROM'])
		return 0;
	return ($a['RANGE_FROM'] < $b['RANGE_FROM']) ? -1 : 1;
}

function __GetRangeInfo($ID,$strPrefix)
{
	$arResult = false;
	if ((true == isset($_POST[$strPrefix.$ID.'_SUMM'])) && ($_POST[$strPrefix.$ID.'_SUMM'] <> ''))
	{
		$arResult['RANGE_FROM'] = doubleval(str_replace(',','.',$_POST[$strPrefix.$ID.'_SUMM']));
		$arResult['VALUE'] = 0;
		if (isset($_POST[$strPrefix.$ID.'_VALUE']))
			$arResult['VALUE'] = doubleval(str_replace(',','.',$_POST[$strPrefix.$ID.'_VALUE']));
		if (0 > $arResult['VALUE'])
			$arResult['VALUE'] = 0;
		$arResult['TYPE'] = 'P';
		if (isset($_POST[$strPrefix.$ID.'_TYPE']) && $_POST[$strPrefix.$ID.'_TYPE'] == 'F')
			$arResult['TYPE'] = 'F';
	}
	return $arResult;
}

function __AddRangeCellSum($intRangeID,$strPrefix,$arRange)
{
	return '<td>'.htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_RANGE_FROM')).' <input type="text" name="'.$strPrefix.$intRangeID.'_SUMM" size="13" value="'.htmlspecialcharsbx($arRange['RANGE_FROM']).'"></td>';
}

function __AddRangeCellDiscount($intRangeID,$strPrefix,$arRange)
{
	static $discsaveTypes = null;
	if ($discsaveTypes === null)
	{
		$discsaveTypes = CCatalogDiscountSave::GetDiscountSaveTypes(true);
	}
	$result = '<td><input type="text" name="'.$strPrefix.$intRangeID.'_VALUE" size="13" value="'.htmlspecialcharsbx($arRange['VALUE']).'"> <select name="'.$strPrefix.$intRangeID.'_TYPE" style="width:150px;">';
	foreach ($discsaveTypes as $key => $value)
	{
		$result .= '<option value="'.$key.'" '.($key == $arRange['TYPE'] ? 'selected' : '').'>'.$value.'</option>';
	}
	$result .= '</select></td>';
	return $result;
}

function __AddRangeRow($intRangeID,$strPrefix,$arRange)
{
	return '<tr id="'.$strPrefix.$intRangeID.'">'.__AddRangeCellSum($intRangeID,$strPrefix,$arRange).__AddRangeCellDiscount($intRangeID,$strPrefix,$arRange).'</tr>';
}

$arDefRange = array(
	'RANGE_FROM' => '',
	'DSC_VALUE' => '',
	'DSC_TYPE' => '',
);

function __AddHiddenRow($intRangeID,$strPrefix,$arRange)
{
	return '<input type="hidden" name="'.$strPrefix.$intRangeID.'_SUMM" value="'.htmlspecialcharsbx($arRange['RANGE_FROM']).'">'.
		'<input type="hidden" name="'.$strPrefix.$intRangeID.'_VALUE" value="'.htmlspecialcharsbx($arRange['VALUE']).'">'.
		'<input type="hidden" name="'.$strPrefix.$intRangeID.'_TYPE" value="'.htmlspecialcharsbx($arRange['TYPE']).'">';
}

$arCellTemplates = array();
$arCellTemplates[] = CUtil::JSEscape(__AddRangeCellSum('tmp_xxx','PREFIX',$arRange));
$arCellTemplates[] = CUtil::JSEscape(__AddRangeCellDiscount('tmp_xxx','PREFIX',$arRange));

$message = false;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_NAME_MAIN"), "ICON"=>"catalog", "TITLE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_TITLE_MAIN")),
	array("DIV" => "edit2", "TAB" => GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_NAME_RANGES"), "ICON"=>"catalog", "TITLE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_TITLE_RANGES")),
	array("DIV" => "edit3", "TAB" => GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_NAME_GROUPS"), "ICON"=>"catalog", "TITLE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_TITLE_GROUPS")),
	array("DIV" => "edit4", "TAB" => GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_NAME_MISC"), "ICON" => "catalog", "TITLE" => GetMessage("BT_CAT_DISC_SAVE_EDIT_TAB_TITLE_MISC")),
);
$tabControl = new CAdminForm("cat_disc_save", $aTabs);

$ID = intval($ID);
$bVarsFromForm = false;
$arErrors = array();

$boolCopy = false;
if (0 < $ID)
{
	$boolCopy = (isset($_REQUEST['action']) && 'copy' == $_REQUEST['action']);
}

if(
	$_SERVER['REQUEST_METHOD'] == "POST"
	&&
	(!empty($save) || !empty($apply))
	&&
	!$bReadOnly
	&&
	check_bitrix_sessid()
)
{
	$APPLICATION->ResetException();

	$obDiscSave = new CCatalogDiscountSave();

	if (isset($_POST['ACTION_PERIOD']))
	{
		$ACTION_PERIOD = mb_substr(trim($_POST['ACTION_PERIOD']), 0, 1);
		if ('D' == $ACTION_PERIOD)
		{
			if (empty($_POST['ACTIVE_FROM']) && empty($_POST['ACTIVE_TO']))
			{
				$arErrors[] = array('id' => 'ACTIVE_FROM', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_ACTION_DATE_EMPTY'));
				$bVarsFromForm = true;
			}
			else
			{
				$_POST['ACTION_SIZE'] = 0;
				$_POST['ACTION_TYPE'] = 'Y';
				$ACTION_SIZE = 0;
				$ACTION_TYPE = 'Y';
			}
		}
		elseif ('P' == $ACTION_PERIOD)
		{
			if (0 >= intval($_POST['ACTION_SIZE']) || empty($_POST['ACTION_TYPE']))
			{
				$arErrors[] = array('id' => 'ACTION_SIZE', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_ACTION_SIZE_EMPTY'));
				$bVarsFromForm = true;
			}
			else
			{
				$_POST['ACTIVE_FROM'] = '';
				$_POST['ACTIVE_TO'] = '';
				$ACTIVE_FROM = '';
				$ACTIVE_TO = '';
			}
		}
		elseif ('U' == $ACTION_PERIOD)
		{
			$_POST['ACTION_SIZE'] = 0;
			$_POST['ACTION_TYPE'] = 'Y';
			$ACTION_SIZE = 0;
			$ACTION_TYPE = 'Y';
			$_POST['ACTIVE_FROM'] = '';
			$_POST['ACTIVE_TO'] = '';
			$ACTIVE_FROM = '';
			$ACTIVE_TO = '';
		}
		else
		{
			$arErrors[] = array('id' => 'ACTION_PERIOD', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_ACTION_PERIOD'));
			$bVarsFromForm = true;
		}
	}
	else
	{
		$arErrors[] = array('id' => 'ACTION_PERIOD', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_ACTION_PERIOD'));
		$bVarsFromForm = true;
	}

	if (isset($_POST['COUNT_PERIOD']))
	{
		$COUNT_PERIOD = mb_substr(trim($_POST['COUNT_PERIOD']), 0, 1);
		if ('D' == $COUNT_PERIOD)
		{
			if (empty($_POST['COUNT_FROM']) && empty($_POST['COUNT_TO']))
			{
				$arErrors[] = array('id' => 'COUNT_FROM', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_COUNT_DATE_EMPTY'));
				$bVarsFromForm = true;
			}
			else
			{
				$_POST['COUNT_SIZE'] = 0;
				$_POST['COUNT_TYPE'] = 'Y';
				$COUNT_SIZE = 0;
				$COUNT_TYPE = 'Y';
			}
		}
		elseif ('P' == $COUNT_PERIOD)
		{
			if (0 >= intval($_POST['COUNT_SIZE']) || empty($_POST['COUNT_TYPE']))
			{
				$arErrors[] = array('id' => 'COUNT_SIZE', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_COUNT_SIZE_EMPTY'));
				$bVarsFromForm = true;
			}
			else
			{
				$_POST['COUNT_FROM'] = '';
				$_POST['COUNT_TO'] = '';
				$COUNT_FROM = '';
				$COUNT_TO = '';
			}
		}
		elseif ('U' == $COUNT_PERIOD)
		{
			$_POST['COUNT_SIZE'] = 0;
			$_POST['COUNT_TYPE'] = 'Y';
			$COUNT_SIZE = 0;
			$COUNT_TYPE = 'Y';
			$_POST['COUNT_FROM'] = '';
			$_POST['COUNT_TO'] = '';
			$COUNT_FROM = '';
			$COUNT_TO = '';
		}
		else
		{
			$arErrors[] = array('id' => 'COUNT_PERIOD', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_COUNT_PERIOD'));
			$bVarsFromForm = true;
		}
	}
	else
	{
		$arErrors[] = array('id' => 'COUNT_PERIOD', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_COUNT_PERIOD'));
		$bVarsFromForm = true;
	}

	$arFormRanges = array();
	if (!empty($_POST['RANGES_COUNT']) && 0 < intval($_POST['RANGES_COUNT']))
	{
		$intCount = intval($_POST['RANGES_COUNT']);
		for ($i = 0; $i < $intCount; $i++)
		{
			$arOneRange = __GetRangeInfo($i,RANGE_ROW_PREFIX);
			if (!empty($arOneRange))
				$arFormRanges[] = $arOneRange;
		}
		if (!empty($arFormRanges))
		{
			usort($arFormRanges, "__cmpRange");
		}
	}
	if (empty($arFormRanges))
	{
		$arErrors[] = array('id' => 'RANGES', "text" => GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_RANGES_EMPTY'));
		$bVarsFromForm = true;
	}

	if (!$bVarsFromForm)
	{
		$arFields = array(
			"XML_ID" => $_POST['XML_ID'],
			"SITE_ID" => $_POST['SITE_ID'],
			"NAME" => $_POST['NAME'],
			"ACTIVE" => ($_POST['ACTIVE'] != "Y"? "N":"Y"),
			"SORT" => intval($_POST['SORT']),
			"CURRENCY" => $_POST['CURRENCY'],
			"ACTIVE_FROM" => $_POST['ACTIVE_FROM'],
			"ACTIVE_TO" => $_POST['ACTIVE_TO'],
			"COUNT_SIZE" => $_POST['COUNT_SIZE'],
			"COUNT_TYPE" => $_POST['COUNT_TYPE'],
			"ACTION_SIZE" => $_POST['ACTION_SIZE'],
			"ACTION_TYPE" => $_POST['ACTION_TYPE'],
			"COUNT_FROM" => $_POST['COUNT_FROM'],
			"COUNT_TO" => $_POST['COUNT_TO'],
			"GROUP_IDS" => $_POST['GROUP_IDS'],
			"RANGES" => $arFormRanges,
		);
	}
	if (!$bVarsFromForm)
	{
		if($ID > 0 && !$boolCopy)
		{
			$mxRes = $obDiscSave->Update($ID, $arFields);
		}
		else
		{
			$ID = $obDiscSave->Add($arFields);
			$mxRes = ($ID > 0);
		}

		if($mxRes)
		{
			if (!empty($apply))
				LocalRedirect("/bitrix/admin/cat_discsave_edit.php?ID=".$ID."&mess=ok&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
			else
				LocalRedirect("/bitrix/admin/cat_discsave_admin.php?lang=".urlencode(LANGUAGE_ID));
		}
		else
		{
			$bVarsFromForm = true;
		}
	}
}

if ($bVarsFromForm && !empty($arErrors))
{
	$obError = new CAdminException($arErrors);
	$APPLICATION->ThrowException($obError);
}

ClearVars('str_');
$str_XML_ID = '';
$str_SITE_ID = '';
$str_NAME = '';
$str_ACTIVE = "Y";
$str_SORT = 500;
$str_CURRENCY = '';
$str_ACTIVE_FROM = '';
$str_ACTION_TO = '';
$str_COUNT_PERIOD = 'U';
$str_ACTION_PERIOD = 'U';
$str_COUNT_SIZE = 0;
$str_COUNT_TYPE = 'M';
$str_COUNT_FROM = '';
$str_COUNT_TO = '';
$str_ACTION_SIZE = 0;
$str_ACTION_TYPE = 'M';

$arRanges = array();
$arGroupList = array();

if($ID > 0)
{
	$rsDiscSaves = CCatalogDiscountSave::GetByID($ID);
	if(!$rsDiscSaves->ExtractFields("str_"))
		$ID = 0;
}

if ($ID > 0)
{
	$rsDiscGroups = CCatalogDiscountSave::GetGroupByDiscount(array(),array('DISCOUNT_ID' => $ID));
	while ($arDiscGroup = $rsDiscGroups->Fetch())
	{
		$arGroupList[] = $arDiscGroup['GROUP_ID'];
	}
	$rsDiscRanges = CCatalogDiscountSave::GetRangeByDiscount(array('RANGE_FROM' => 'ASC'),array('DISCOUNT_ID' => $ID));
	while ($arDiscRange = $rsDiscRanges->Fetch())
	{
		$arRanges[] = $arDiscRange;
	}
}

if (!isset($COUNT_PERIOD))
{
	if ('' != $str_COUNT_FROM || '' != $str_COUNT_TO)
	{
		$str_COUNT_PERIOD = 'D';
	}
	elseif (0 < intval($str_COUNT_SIZE))
	{
		$str_COUNT_PERIOD = 'P';
	}
	else
	{
		$str_COUNT_PERIOD = 'U';
	}
}
else
{
	$str_COUNT_PERIOD = $COUNT_PERIOD;
}

if (!isset($ACTION_PERIOD))
{
	if ('' != $str_ACTIVE_FROM || '' != $str_ACTIVE_TO)
	{
		$str_ACTION_PERIOD = 'D';
	}
	elseif (0 < intval($str_ACTION_SIZE))
	{
		$str_ACTION_PERIOD = 'P';
	}
	else
	{
		$str_ACTION_PERIOD = 'U';
	}
}
else
{
	$str_ACTION_PERIOD = $ACTION_PERIOD;
}

if($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_catalog_discount", "", "str_");
	if (is_array($_POST['GROUP_IDS']))
		$arGroupList = $_POST['GROUP_IDS'];
	if (!empty($arFormRanges))
	{
		$arRanges = $arFormRanges;
	}
}

$arActionPeriod = array(
	'U' => GetMessage('BT_CAT_DISC_SAVE_ACTION_TYPE_UNLIM'),
	'D' => GetMessage('BT_CAT_DISC_SAVE_ACTION_TYPE_DATE'),
	'P' => GetMessage('BT_CAT_DISC_SAVE_ACTION_TYPE_SIZE'),
);

$arCountPeriod = array(
	'U' => GetMessage('BT_CAT_DISC_SAVE_COUNT_TYPE_UNLIM'),
	'D' => GetMessage('BT_CAT_DISC_SAVE_COUNT_TYPE_DATE'),
	'P' => GetMessage('BT_CAT_DISC_SAVE_COUNT_TYPE_SIZE'),
);

if ($ID > 0 && !$boolCopy)
{
	$APPLICATION->SetTitle(str_replace('#ID#',$ID,GetMessage("BT_CAT_DISC_SAVE_EDIT_PAGE_NAME_ID")));
}
else
{
	$APPLICATION->SetTitle(GetMessage("BT_CAT_DISC_SAVE_EDIT_PAGE_NAME_ADD"));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_NAME_LIST"),
		"TITLE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_TITLE_LIST"),
		"LINK"=>"/bitrix/admin/cat_discsave_admin.php?lang=".urlencode(LANGUAGE_ID),
		"ICON"=>"btn_list",
	)
);

if (!$bReadOnly)
{
	if($ID > 0)
	{
		$aMenu[] = array("SEPARATOR"=>"Y");
		$aMenu[] = array(
			"TEXT"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_NAME_ADD"),
			"TITLE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_TITLE_ADD"),
			"LINK"=>"/bitrix/admin/cat_discsave_edit.php?lang=".urlencode(LANGUAGE_ID),
			"ICON"=>"btn_new",
		);
		if (!$boolCopy)
		{
			$aMenu[] = array(
				"TEXT"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_NAME_COPY"),
				"TITLE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_TITLE_COPY"),
				"LINK"=>"/bitrix/admin/cat_discsave_edit.php?ID=".$ID."&action=copy&lang=".urlencode(LANGUAGE_ID),
				"ICON"=>"btn_copy",
			);
			$aMenu[] = array(
				"TEXT"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_NAME_DELETE"),
				"TITLE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_TITLE_DELETE"),
				"LINK"=>"javascript:if(confirm('".GetMessage("BT_CAT_DISC_SAVE_EDIT_CONT_CONF_DELETE")."'))window.location='/bitrix/admin/cat_discsave_admin.php?ID=".$ID."&action=delete&lang=".urlencode(LANGUAGE_ID)."&".bitrix_sessid_get()."';",
				"ICON"=>"btn_delete",
			);
		}
	}
}

$context = new CAdminContextMenu($aMenu);

$context->Show();

if ($_REQUEST["mess"] == "ok" && $ID > 0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("BT_CAT_DISC_SAVE_EDIT_MESS_OK2"), "TYPE"=>"OK"));
$obMessages = false;
if ($bVarsFromForm)
{
	if ($ex = $APPLICATION->GetException())
	{
		$obMessages = new CAdminMessage(GetMessage('BT_CAT_DISC_SAVE_EDIT_ERR_SAVE2'),$ex);
		echo $obMessages->Show();
	}
}
?>

<?
$arSiteList = array();
$rsSites = CSite::GetList();
while ($arSite = $rsSites->Fetch())
{
	$arSiteList[$arSite['LID']] = '('.$arSite['LID'].') '.$arSite['NAME'];
}

$arCurrencyList = array();
$rsCurrencies = CCurrency::GetList('sort', 'asc');
while ($arCurrency = $rsCurrencies->Fetch())
{
	$arCurrencyList[$arCurrency['CURRENCY']] = $arCurrency['CURRENCY'];
}

$arTypeList = CCatalogDiscountSave::GetPeriodTypeList(true);

$tabControl->BeginPrologContent();

$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
?>
<? echo bitrix_sessid_post()?>
<? echo GetFilterHiddens("find_");?>
<input type="hidden" name="lang" value="<? echo htmlspecialcharsbx(LANGUAGE_ID); ?>">
<?if($ID > 0)
{
	?><input type="hidden" name="ID" value="<? echo $ID?>"><?
}
if ($boolCopy)
{
	?><input type="hidden" name="action" value="copy"><?
}

$tabControl->EndEpilogContent();
$tabControl->Begin(array(
	"FORM_ACTION" => '/bitrix/admin/cat_discsave_edit.php?lang='.urlencode(LANGUAGE_ID),
));
$tabControl->BeginNextFormTab();
if ($ID > 0 && !$boolCopy)
	$tabControl->AddViewField('ID','ID:',$ID,false);

$tabControl->AddCheckBoxField("ACTIVE", GetMessage("BT_CAT_DISC_SAVE_EDIT_FIELDS_ACTIVE").":", false, "Y", $str_ACTIVE=="Y");
$tabControl->AddEditField("NAME", GetMessage("BT_CAT_DISC_SAVE_EDIT_FIELDS_NAME").":", true, array("size" => 50, "maxlength" => 255), $str_NAME);
$tabControl->AddDropDownField("SITE_ID", GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_SITE_ID').':', true, $arSiteList, $str_SITE_ID);

$tabControl->BeginNextFormTab();

$tabControl->AddSection("BT_CAT_DISC_SAVE_EDIT_FIELDS_COUNT", GetMessage("BT_CAT_DISC_SAVE_EDIT_SECTIONS_COUNT"));

$tabControl->BeginCustomField("COUNT",GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_COUNT').":",true);
?>
	<tr id="tr_COUNT_PERIOD" class="adm-detail-required-field">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_COUNT_TYPE')); ?>:</td>
		<td width="60%"><select name="COUNT_PERIOD" id="COUNT_PERIOD"><?
		foreach ($arCountPeriod as $key => $value)
		{
			?><option value="<? echo htmlspecialcharsbx($key); ?>" <? echo ($key == $str_COUNT_PERIOD ? 'selected' : ''); ?>><? echo htmlspecialcharsex($value); ?></option><?
		}
		?></select></td>
	</tr>
	<tr id="tr_COUNT_FROM" style="display: <? echo 'D' == $str_COUNT_PERIOD ? 'table-row' : 'none'; ?>;">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_COUNT_FROM')) ?>:</td>
		<td width="60%"><? echo CAdminCalendar::CalendarDate("COUNT_FROM", $str_COUNT_FROM, 19, true); ?></td>
	</tr>
	<tr id="tr_COUNT_TO" style="display: <? echo 'D' == $str_COUNT_PERIOD ? 'table-row' : 'none'; ?>;">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_COUNT_TO')) ?>:</td>
		<td width="60%"><? echo CAdminCalendar::CalendarDate("COUNT_TO", $str_COUNT_TO, 19, true); ?></td>
	</tr>
	<tr id="tr_COUNT_TYPE_SIZE" style="display: <? echo 'P' == $str_COUNT_PERIOD ? 'table-row' : 'none'; ?>;">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_COUNT_TYPE')); ?>:</td>
		<td width="60%"><input type="text" name="COUNT_SIZE" id="COUNT_SIZE" value="<? echo intval($str_COUNT_SIZE); ?>" size="7" maxlength="10">&nbsp;<select name="COUNT_TYPE" id="COUNT_TYPE"><?
		foreach ($arTypeList as $key => $value)
		{
			?><option value="<? echo htmlspecialcharsbx($key); ?>" <? echo ($key == $str_COUNT_TYPE ? 'selected' : ''); ?>><? echo htmlspecialcharsex($value); ?></option><?
		}
		?></select>
		</td>
	</tr>
<?
$tabControl->EndCustomField("COUNT",
	'<input type="hidden" name="COUNT_PERIOD" value="'.$str_COUNT_PERIOD.'">'.
	'<input type="hidden" name="COUNT_SIZE" value="'.$str_COUNT_SIZE.'">'.
	'<input type="hidden" name="COUNT_TYPE" value="'.$str_COUNT_TYPE.'">'.
	'<input type="hidden" name="COUNT_FROM" value="'.$str_COUNT_FROM.'">'.
	'<input type="hidden" name="COUNT_TO" value="'.$str_COUNT_TO.'">'
);

$tabControl->AddSection("BT_CAT_DISC_SAVE_EDIT_SECTIONS_ACTIVITY", GetMessage("BT_CAT_DISC_SAVE_EDIT_SECTIONS_ACTIVITY"));

$tabControl->BeginCustomField('ACTIVITY_INFO',GetMessage("BT_CAT_DISC_SAVE_EDIT_FILEDS_ACTIVITY").":", true);
?>
	<tr id="tr_ACTIVE_PERIOD" class="adm-detail-required-field">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_ACTION_TYPE')); ?>:</td>
		<td width="60%"><select name="ACTION_PERIOD" id="ACTION_PERIOD"><?
		foreach ($arActionPeriod as $key => $value)
		{
			?><option value="<? echo htmlspecialcharsbx($key); ?>" <? echo ($key == $str_ACTION_PERIOD ? 'selected' : ''); ?>><? echo htmlspecialcharsex($value); ?></option><?
		}
		?></select></td>
	</tr>
	<tr id="tr_ACTIVE_FROM" style="display: <? echo 'D' == $str_ACTION_PERIOD ? 'table-row' : 'none'; ?>;">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_ACTIVE_FROM')); ?>:</td>
		<td width="60%"><? echo CAdminCalendar::CalendarDate("ACTIVE_FROM", $str_ACTIVE_FROM, 19, true); ?></td>
	</tr>
	<tr id="tr_ACTIVE_TO" style="display: <? echo 'D' == $str_ACTION_PERIOD ? 'table-row' : 'none'; ?>;">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_ACTIVE_TO')); ?>:</td>
		<td width="60%"><? echo CAdminCalendar::CalendarDate("ACTIVE_TO", $str_ACTIVE_TO, 19, true); ?></td>
	</tr>
	<tr id="tr_ACTION_TYPE_SIZE" style="display: <? echo 'P' == $str_ACTION_PERIOD ? 'table-row' : 'none'; ?>;">
		<td width="40%"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_ACTION_TYPE')); ?>:</td>
		<td width="60%"><input type="text" name="ACTION_SIZE" id="ACTION_SIZE" value="<? echo intval($str_ACTION_SIZE); ?>" size="7" maxlength="10">&nbsp;<select name="ACTION_TYPE" id="ACTION_TYPE"><?
		foreach ($arTypeList as $key => $value)
		{
			?><option value="<? echo htmlspecialcharsbx($key); ?>" <? echo ($key == $str_ACTION_TYPE ? 'selected' : ''); ?>><? echo htmlspecialcharsex($value); ?></option><?
		}
		?></select>
		</td>
	</tr>
<?
$tabControl->EndCustomField("ACTIVITY_INFO",
	'<input type="hidden" name="ACTION_PERIOD" value="'.$str_ACTION_PERIOD.'">'.
	'<input type="hidden" name="ACTIVE_FROM" value="'.$str_ACTIVE_FROM.'">'.
	'<input type="hidden" name="ACTIVE_TO" value="'.$str_ACTIVE_TO.'">'.
	'<input type="hidden" name="ACTION_SIZE" value="'.$str_ACTION_SIZE.'">'.
	'<input type="hidden" name="ACTION_TYPE" value="'.$str_ACTION_TYPE.'">'
);

$tabControl->AddSection("BT_CAT_DISC_SAVE_EDIT_SECTIONS_RANGES", GetMessage("BT_CAT_DISC_SAVE_EDIT_SECTIONS_RANGES"));

$tabControl->AddDropDownField("CURRENCY", GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_CURRENCY').':', true, $arCurrencyList, $str_CURRENCY);

$tabControl->BeginCustomField('RANGES',GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_RANGES'),true);
?>
	<tr id="tr_RANGES" class="adm-detail-required-field">
		<td valign="top" width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?>:</td>
		<td width="60%">
<script type="text/javascript">
var CellTPL = new Array();
<?
foreach ($arCellTemplates as $key => $value)
{
	?>CellTPL[<? echo $key; ?>] = '<? echo $value; ?>';
<?
}
?>
var CellAttr = new Array();

var obRanges = new JCCatTblEdit({
	'PREFIX': '<? echo RANGE_ROW_PREFIX; ?>',
	'TABLE_PROP_ID': 'range_list',
	'PROP_COUNT_ID': 'RANGES_COUNT'
});
obRanges.SetCells(CellTPL,CellAttr);
</script>
			<table id="range_list" class="internal" cellspacing="0" cellpadding="0" border="0" style="width: auto;"><tbody>
			<tr class="heading"><td align="center"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_RANGE_SUMM'))?></td><td align="center"><? echo htmlspecialcharsex(GetMessage('BT_CAT_DISC_SAVE_EDIT_RANGE_DISCOUNT'))?></td></tr><?
			$intCount = 0;
			foreach ($arRanges as &$arOneRange)
			{
				echo __AddRangeRow($intCount,RANGE_ROW_PREFIX,$arOneRange);
				$intCount++;
			}
			if (isset($arOneRange))
				unset($arOneRange);
			for ($i = 0; $i < RANGE_EMPTY_ROW_SIZE; $i++)
			{
				echo __AddRangeRow($intCount,RANGE_ROW_PREFIX,$arDefRange);
				$intCount++;
			}
		?></tbody></table>
		<div style="width: 100%; text-align: left; margin-top: 10px;">
			<input class="adm-btn-big" onclick="obRanges.addRow();" type="button" value="<? echo GetMessage('BT_CAT_DISC_SAVE_RANGE_MORE')?>" title="<? echo GetMessage('BT_CAT_DISC_SAVE_RANGE_MORE_DESCR')?>">
		</div>
		<input type="hidden" name="RANGES_COUNT" id="RANGES_COUNT" value="<? echo intval($intCount); ?>">
	</td></tr>
<?
$strHiddenRanges = '';
$intCount = 0;
foreach ($arRanges as &$arOneRange)
{
	$strHiddenRanges .= __AddHiddenRow($intCount,RANGE_ROW_PREFIX,$arOneRange);
	$intCount++;
}
if (isset($arOneRange))
	unset($arOneRange);
$strHiddenRanges .= '<input type="hidden" name="RANGES_COUNT" value="'.intval($intCount).'">';
$tabControl->EndCustomField('RANGES',
	$strHiddenRanges
);

$tabControl->BeginNextFormTab();

$tabControl->BeginCustomField('GROUP_IDS',GetMessage('BT_CAT_DISC_SAVE_EDIT_FIELDS_GROUP_IDS'),true);
?>
	<tr id="tr_GROUP_IDS" class="adm-detail-required-field">
		<td valign="top" width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?>:</td>
		<td width="60%" align="left"><select name="GROUP_IDS[]" multiple size="8"><?
		$rsUserGroups = CGroup::GetList();
		while ($arUserGroup = $rsUserGroups->Fetch())
		{
			if (2 != $arUserGroup['ID'])
			{
				?><option value="<? echo intval($arUserGroup['ID'])?>" <? echo (in_array($arUserGroup['ID'], $arGroupList) ? 'selected' : ''); ?>><? echo htmlspecialcharsex($arUserGroup['NAME']);?></option><?
			}
		}

		?></select></td>
	</tr>
<?
if ($ID > 0 && !empty($arGroupList))
{
	$strGroupsHidden = '';
	foreach ($arGroupList as &$value)
	{
		if (0 < intval($value))
			$strGroupsHidden .= '<input type="hidden" name="GROUP_IDS[]" value="'.intval($value).'">';
	}
	if (isset($value))
		unset($value);
}
else
{
	$strGroupsHidden = '<input type="hidden" name="GROUP_IDS[]" value="">';
}
$tabControl->EndCustomField('GROUP_IDS',
	$strGroupsHidden
);

$tabControl->BeginNextFormTab();

$tabControl->AddEditField("XML_ID", GetMessage("BT_CAT_DISC_SAVE_EDIT_FIELDS_XML_ID").":", false, array("size" => 50, "maxlength" => 255), $str_XML_ID);
$tabControl->AddEditField("SORT", GetMessage("BT_CAT_DISC_SAVE_EDIT_FIELDS_SORT").":", false, array("size" => 7, "maxlength" => 10), $str_SORT);

$arButtonsParams = array(
	'disabled' => $bReadOnly,
	'back_url' => '/bitrix/admin/cat_discsave_admin.php?lang='.urlencode(LANGUAGE_ID)
);

$tabControl->Buttons($arButtonsParams);

$tabControl->Show();

$tabControl->ShowWarnings("cat_disc_save", $obMessages);

?><script type="text/javascript">
BX.ready(function(){
	var obCountPeriod = BX('COUNT_PERIOD');
	var obCountFrom = BX('tr_COUNT_FROM');
	var obCountTo = BX('tr_COUNT_TO');
	var obCountType = BX('tr_COUNT_TYPE_SIZE');

	if (!!obCountPeriod && !!obCountFrom && !!obCountTo && !!obCountType)
	{
		BX.bind(obCountPeriod, 'change', function(){
			BX.style(obCountFrom, 'display', (-1 < obCountPeriod.selectedIndex && 'D' == obCountPeriod.options[obCountPeriod.selectedIndex].value ? 'table-row' : 'none'));
			BX.style(obCountTo, 'display', (-1 < obCountPeriod.selectedIndex && 'D' == obCountPeriod.options[obCountPeriod.selectedIndex].value ? 'table-row' : 'none'));
			BX.style(obCountType, 'display', (-1 < obCountPeriod.selectedIndex && 'P' == obCountPeriod.options[obCountPeriod.selectedIndex].value ? 'table-row' : 'none'));
		});
		BX.style(obCountFrom, 'display', (-1 < obCountPeriod.selectedIndex && 'D' == obCountPeriod.options[obCountPeriod.selectedIndex].value ? 'table-row' : 'none'));
		BX.style(obCountTo, 'display', (-1 < obCountPeriod.selectedIndex && 'D' == obCountPeriod.options[obCountPeriod.selectedIndex].value ? 'table-row' : 'none'));
		BX.style(obCountType, 'display', (-1 < obCountPeriod.selectedIndex && 'P' == obCountPeriod.options[obCountPeriod.selectedIndex].value ? 'table-row' : 'none'));
	}

	var obActivePeriod = BX('ACTION_PERIOD');
	var obActiveFrom = BX('tr_ACTIVE_FROM');
	var obActiveTo = BX('tr_ACTIVE_TO');
	var obActiveType = BX('tr_ACTION_TYPE_SIZE');

	if (!!obActivePeriod && !!obActiveFrom && !!obActiveTo && !!obActiveType)
	{
		BX.bind(obActivePeriod, 'change', function(){
			BX.style(obActiveFrom, 'display', (-1 < obActivePeriod.selectedIndex && 'D' == obActivePeriod.options[obActivePeriod.selectedIndex].value ? 'table-row' : 'none'));
			BX.style(obActiveTo, 'display', (-1 < obActivePeriod.selectedIndex && 'D' == obActivePeriod.options[obActivePeriod.selectedIndex].value ? 'table-row' : 'none'));
			BX.style(obActiveType, 'display', (-1 < obActivePeriod.selectedIndex && 'P' == obActivePeriod.options[obActivePeriod.selectedIndex].value ? 'table-row' : 'none'));
		});
		BX.style(obActiveFrom, 'display', (-1 < obActivePeriod.selectedIndex && 'D' == obActivePeriod.options[obActivePeriod.selectedIndex].value ? 'table-row' : 'none'));
		BX.style(obActiveTo, 'display', (-1 < obActivePeriod.selectedIndex && 'D' == obActivePeriod.options[obActivePeriod.selectedIndex].value ? 'table-row' : 'none'));
		BX.style(obActiveType, 'display', (-1 < obActivePeriod.selectedIndex && 'P' == obActivePeriod.options[obActivePeriod.selectedIndex].value ? 'table-row' : 'none'));
	}
});
</script><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>