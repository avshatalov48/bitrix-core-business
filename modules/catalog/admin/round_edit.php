<?
/** @global CMain $APPLICATION */
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

$request = Main\Context::getCurrent()->getRequest();

$roundTypeList = Catalog\RoundingTable::getRoundTypes(true);
$roundValues = Catalog\Helpers\Admin\RoundEdit::getPresetRoundValues(true);

$returnUrl = '';
$rawReturnUrl = (string)$request->get('return_url');
if ($rawReturnUrl != '')
{
	$currentUrl = $APPLICATION->GetCurPage();
	if (strtolower(substr($rawReturnUrl, strlen($currentUrl))) != strtolower($currentUrl))
		$returnUrl = $rawReturnUrl;
}
unset($rawReturnUrl);

$tabList = array(
	array(
		'ICON' => 'catalog',
		'DIV' => 'roundEdit01',
		'TAB' => Loc::getMessage('PRICE_ROUND_EDIT_TAB_NAME_COMMON'),
		'TITLE' => Loc::getMessage('PRICE_ROUND_EDIT_TAB_TITLE_COMMON')
	)
);

$ruleFormID = 'priceRoundControl';
$control = new CAdminForm($ruleFormID, $tabList);
$control->SetShowSettings(false);
unset($tabList);
$ruleFormID .= '_form';

$errors = array();
$fields = array();
$copy = false;
$ruleId = (int)$request->get('ID');
if ($ruleId < 0)
	$ruleId = 0;

if ($ruleId > 0)
	$copy = ($request->get('action') == 'copy');

if (
	check_bitrix_sessid()
	&& !$readOnly
	&& $request->isPost()
	&& (string)$request->getPost('Update') == 'Y'
)
{
	$rawData = $request->getPostList();

	if (!empty($rawData['CATALOG_GROUP_ID']))
		$fields['CATALOG_GROUP_ID'] = $rawData['CATALOG_GROUP_ID'];
	if (isset($rawData['PRICE']))
		$fields['PRICE'] = (float)$rawData['PRICE'];
	if (isset($rawData['ROUND_TYPE']))
		$fields['ROUND_TYPE'] = (int)$rawData['ROUND_TYPE'];
	if (isset($rawData['ROUND_PRECISION']))
		$fields['ROUND_PRECISION'] = (float)$rawData['ROUND_PRECISION'];

	if ($ruleId == 0 || $copy)
		$result = Catalog\RoundingTable::add($fields);
	else
		$result = Catalog\RoundingTable::update($ruleId, $fields);
	if (!$result->isSuccess())
	{
		$errors = $result->getErrorMessages();
	}
	else
	{
		if ($ruleId == 0 || $copy)
			$ruleId = $result->getId();
	}
	unset($result);

	unset($rawData);

	if (empty($errors))
	{
		if ((string)$request->getPost('apply') != '')
			LocalRedirect('cat_round_edit.php?lang='.LANGUAGE_ID.'&ID='.$ruleId.'&'.$control->ActiveTabParam().GetFilterParams('filter_', false));
		else
			LocalRedirect('cat_round_list.php?lang='.LANGUAGE_ID.GetFilterParams('filter_', false));
	}
}

$APPLICATION->SetTitle(
	$ruleId == 0
	? Loc::getMessage('PRICE_ROUND_EDIT_TITLE_ADD')
	: (
		!$copy
		? Loc::getMessage('PRICE_ROUND_EDIT_TITLE_UPDATE', array('#ID#' => $ruleId))
		: Loc::getMessage('PRICE_ROUND_EDIT_TITLE_COPY', array('#ID#' => $ruleId))
	)
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$contextMenuItems = array(
	array(
		'ICON' => 'btn_list',
		'TEXT' => Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_LIST'),
		'LINK' => 'cat_round_list.php?lang='.LANGUAGE_ID.GetFilterParams('filter_')
	)
);
if (!$readOnly && $ruleId > 0)
{
	if (!$copy)
	{
		$contextMenuItems[] = array('SEPARATOR' => 'Y');
		$contextMenuItems[] = array(
			'ICON' => 'btn_new',
			'TEXT' => Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_NEW'),
			'LINK' => 'cat_round_list.php?lang='.LANGUAGE_ID.GetFilterParams('filter_')
		);
		$contextMenuItems[] = array(
			'ICON' => 'btn_copy',
			'TEXT' => Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_COPY'),
			'LINK' => 'cat_round_list.php?lang='.LANGUAGE_ID.'&ID='.$ruleId.'&action=copy'.GetFilterParams('filter_')
		);
		$contextMenuItems[] = array(
			'ICON' => 'btn_delete',
			'TEXT' => Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_DELETE'),
			'LINK' => "javascript:if (confirm('".CUtil::JSEscape(Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_DELETE_CONFIRM'))."')) window.location='/bitrix/admin/cat_round_list.php?lang=".LANGUAGE_ID."&ID=".$ruleId."&action=delete&".bitrix_sessid_get()."';",
			'WARNING' => 'Y',
		);
	}
}

$contextMenu = new CAdminContextMenu($contextMenuItems);
$contextMenu->Show();
unset($contextMenu, $contextMenuItems);

if (!empty($errors))
{
	$errorMessage = new CAdminMessage(
		array(
			'DETAILS' => implode('<br>', $errors),
			'TYPE' => 'ERROR',
			'MESSAGE' => Loc::getMessage('PRICE_ROUND_EDIT_ERR_SAVE'),
			'HTML' => true
		)
	);
	echo $errorMessage->Show();
	unset($errorMessage);
}

$defaultValues = array(
	'CATALOG_GROUP_ID' => 0,
	'PRICE' => '',
	'ROUND_TYPE' => Catalog\RoundingTable::ROUND_MATH,
	'ROUND_PRECISION' => 1
);
$selectFields = array_keys($defaultValues);
$selectFields[] = 'ID';

$rule = array();
if ($ruleId > 0)
{
	$rule = Catalog\RoundingTable::getList(array(
		'select' => $selectFields,
		'filter' => array('=ID' => $ruleId)
	))->fetch();
	if (!$rule)
		$ruleId = 0;
}
if ($ruleId == 0)
	$rule = $defaultValues;

$rule['CATALOG_GROUP_ID'] = (int)$rule['CATALOG_GROUP_ID'];
$rule['PRICE'] = (float)$rule['PRICE'];
$rule['ROUND_TYPE'] = (int)$rule['ROUND_TYPE'];
$rule['ROUND_PRECISION'] = (float)$rule['ROUND_PRECISION'];

if (!empty($errors))
	$rule = array_merge($rule, $fields);

$control->BeginPrologContent();
$control->EndPrologContent();
$control->BeginEpilogContent();
echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
	<input type="hidden" name="ID" value="<? echo $ruleId; ?>">
<?
if ($copy)
{
	?><input type="hidden" name="action" value="copy"><?
}
if (!empty($returnUrl))
{
	?><input type="hidden" name="return_url" value="<? echo htmlspecialcharsbx($returnUrl); ?>"><?
}
echo bitrix_sessid_post();
$control->EndEpilogContent();
$control->Begin(array(
	'FORM_ACTION' => 'cat_round_edit.php?lang='.LANGUAGE_ID
));
$control->BeginNextFormTab();

if ($ruleId > 0 && !$copy)
	$control->AddViewField('ID', Loc::getMessage('PRICE_ROUND_EDIT_FIELD_ID'), $ruleId, false);
$control->AddDropDownField(
	'CATALOG_GROUP_ID',
	Loc::getMessage('PRICE_ROUND_EDIT_FIELD_PRICE_TYPE'),
	true,
	Catalog\Helpers\Admin\Tools::getPriceTypeList(false),
	$rule['CATALOG_GROUP_ID']
);
$control->AddEditField('PRICE', Loc::getMessage('PRICE_ROUND_EDIT_FIELD_PRICE'), true, array(), $rule['PRICE']);
$control->AddDropDownField(
	'ROUND_TYPE',
	Loc::getMessage('PRICE_ROUND_EDIT_FIELD_ROUND_TYPE'),
	true,
	$roundTypeList,
	$rule['ROUND_TYPE'],
	array('size="3"')
);
$control->AddDropDownField(
	'ROUND_PRECISION',
	Loc::getMessage('PRICE_ROUND_EDIT_FIELD_ROUND_PRECISION'),
	true,
	$roundValues,
	$rule['ROUND_PRECISION']
);
$control->Buttons(
	array(
		'disabled' => $readOnly,
		'back_url' => "cat_round_list.php?lang=".LANGUAGE_ID.GetFilterParams('filter_')
	)
);
$control->Show();
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');