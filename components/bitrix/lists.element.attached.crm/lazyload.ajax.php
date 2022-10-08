<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site'])? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if ($siteID !== '') define('SITE_ID', $siteID);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!check_bitrix_sessid()) die();

if (isset($_REQUEST["download"]) && $_REQUEST["download"] === "y")
{
	Bitrix\Main\Localization\Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/lists.file/component.php');

	if (!Bitrix\Main\Loader::includeModule('lists'))
		completeLazyLoad(GetMessage("CC_BLF_MODULE_NOT_INSTALLED"));

	$IBLOCK_ID = is_array($arParams["~IBLOCK_ID"])? 0: intval($arParams["~IBLOCK_ID"]);
	$ELEMENT_ID = is_array($arParams["~ELEMENT_ID"])? 0: intval($arParams["~ELEMENT_ID"]);

	$iblockId = !empty($_REQUEST['list_id']) ? intval($_REQUEST['list_id']) : 0;
	$elementId = !empty($_REQUEST['element_id']) ? intval($_REQUEST['element_id']) : 0;
	$fieldId = !empty($_REQUEST['field_id']) ? $_REQUEST['field_id'] : '';
	$fileId = !empty($_REQUEST['file_id']) ? intval($_REQUEST['file_id']) : 0;
	$iblockTypeId = '';
	$queryObject = CIBlock::getList(array(), array('ID' => $iblockId));
	if ($iblock = $queryObject->fetch())
		$iblockTypeId = $iblock['IBLOCK_TYPE_ID'];

	$listsPerm = CListPermissions::checkAccess($USER, $iblockTypeId, $iblockId);
	if (!CListPermissions::checkFieldId($iblockId, $fieldId))
	{
		completeLazyLoad(GetMessage('CC_BLF_UNKNOWN_ERROR'));
	}
	elseif ($listsPerm < 0)
	{
		switch ($listsPerm)
		{
			case CListPermissions::WRONG_IBLOCK_TYPE:
				completeLazyLoad(GetMessage('CC_BLF_WRONG_IBLOCK_TYPE'));
				break;
			case CListPermissions::WRONG_IBLOCK:
				completeLazyLoad(GetMessage('CC_BLF_WRONG_IBLOCK'));
				break;
			case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
				completeLazyLoad(GetMessage('CC_BLF_LISTS_FOR_SONET_GROUP_DISABLED'));
				break;
			default:
				completeLazyLoad(GetMessage('CC_BLF_UNKNOWN_ERROR'));
		}
	}
	elseif ($elementId > 0 && $listsPerm <= CListPermissions::CAN_READ &&
		!CIBlockElementRights::userHasRightTo($iblockId, $elementId, 'element_read'))
	{
		completeLazyLoad(GetMessage('CC_BLF_ACCESS_DENIED'));
	}

	$canFullEdit = ($elementId > 0 && ($listsPerm >= CListPermissions::IS_ADMIN
		|| CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'iblock_edit')));

	$files = array();
	if($elementId > 0)
	{
		$queryObject = CIBlockElement::getList(
			array(),
			array(
				'IBLOCK_ID' => $iblockId,
				'=ID' => $elementId,
				'CHECK_PERMISSIONS' => 'N',
				'SHOW_NEW' => ($canFullEdit ? 'Y' : 'N')
			),
			false,
			false,
			array('ID', $fieldId)
		);
		while($element = $queryObject->getNext())
		{
			if (isset($element[$fieldId]))
			{
				$files[] = $element[$fieldId];
			}
			elseif (isset($element[$fieldId.'_VALUE']))
			{
				if (is_array($element[$fieldId.'_VALUE']))
					$files = array_merge($files, $element[$fieldId.'_VALUE']);
				else
					$files[] = $element[$fieldId.'_VALUE'];
			}
		}
	}

	if(!in_array($fileId, $files))
	{
		completeLazyLoad(GetMessage('CC_BLF_WRONG_FILE'));
	}
	else
	{
		$file = CFile::getFileArray($fileId);
		if (is_array($file))
			CFile::viewByUser($file, array('content_type' => $file['CONTENT_TYPE'], 'force_download' => true));
	}
	completeLazyLoad();
}

$requestParams = (
	isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS'])
		? $_REQUEST['PARAMS']
		: []
);
$params = (
	isset($requestParams['params']) && is_array($requestParams['params'])
		? $requestParams['params']
		: []
);

global $APPLICATION;
Header('Content-Type: text/html; charset='.LANG_CHARSET);
$APPLICATION->ShowAjaxHead();

$componentParams = [];
if (isset($params['ENTITY_ID']))
{
	$componentParams['ENTITY_ID'] = $params['ENTITY_ID'];
}
if (isset($params['ENTITY_TYPE']))
{
	$componentParams['ENTITY_TYPE'] = $params['ENTITY_TYPE'];
}
if (isset($params['TAB_ID']))
{
	$componentParams['TAB_ID'] = $params['TAB_ID'];
}
if (isset($params['IBLOCK_ID']))
{
	$componentParams['IBLOCK_ID'] = $params['IBLOCK_ID'];
}

$APPLICATION->IncludeComponent(
	'bitrix:lists.element.attached.crm',
	'',
	$componentParams,
	false,
	['HIDE_ICONS' => 'Y']
);

completeLazyLoad();

function completeLazyLoad($message = '')
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
	die($message);
}