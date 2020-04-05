<?
/** @global CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/prolog.php');

Loc::loadMessages(__FILE__);

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."cat_round_list.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

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
	$adminSidePanelHelper->decodeUriComponent($request);

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
		if ($adminSidePanelHelper->isAjaxRequest())
		{
			$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $ruleId));
		}
		else
		{
			if ((string)$request->getPost('apply') != '')
			{
				$applyUrl = $selfFolderUrl."cat_round_edit.php?lang=".$lang."&ID=".$ruleId.'&'.$control->ActiveTabParam();
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				LocalRedirect($applyUrl);
			}
			else
			{
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
		}
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errors);
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
		'LINK' => $listUrl
	)
);
if (!$readOnly && $ruleId > 0)
{
	if (!$copy)
	{
		$addUrl = $selfFolderUrl."cat_round_edit.php?lang=".LANGUAGE_ID;
		$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
		$contextMenuItems[] = array('SEPARATOR' => 'Y');
		$contextMenuItems[] = array(
			'ICON' => 'btn_new',
			'TEXT' => Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_NEW'),
			'LINK' => $addUrl
		);
		$contextMenuItems[] = array(
			'ICON' => 'btn_copy',
			'TEXT' => Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_COPY'),
			'LINK' => $addUrl.'&ID='.$ruleId.'&action=copy'
		);
		$deleteUrl = $selfFolderUrl."cat_round_list.php?lang=".LANGUAGE_ID."&ID=".$ruleId."&action=delete&".bitrix_sessid_get()."";
		$buttonAction = "LINK";
		if ($adminSidePanelHelper->isPublicFrame())
		{
			$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
			$buttonAction = "ONCLICK";
		}
		$contextMenuItems[] = array(
			'ICON' => 'btn_delete',
			'TEXT' => Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_DELETE'),
			$buttonAction => "javascript:if (confirm('".CUtil::JSEscape(Loc::getMessage('PRICE_ROUND_EDIT_CONTEXT_DELETE_CONFIRM'))."')) top.window.location.href='".$deleteUrl."';",
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
$formActionUrl = $selfFolderUrl.'cat_round_edit.php?lang='.LANGUAGE_ID;
$formActionUrl = $adminSidePanelHelper->setDefaultQueryParams($formActionUrl);
$control->Begin(array('FORM_ACTION' => $formActionUrl));
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
$control->Buttons(array('disabled' => $readOnly, 'back_url' => $listUrl));
$control->Show();
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');