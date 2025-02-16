<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CAdminSidePanelHelper $adminSidePanelHelper */

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Iblock\Template;
use Bitrix\Iblock\InheritedProperty;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('iblock');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$request = Context::getCurrent()->getRequest();

$ID = (int)$request->get('ID');

if (!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit"))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Main\Page\Asset::getInstance()->addJs('/bitrix/js/iblock/iblock_edit.js');

const CATALOG_NEW_OFFERS_IBLOCK_NEED = -1;
const PROPERTY_EMPTY_ROW_SIZE = 5;
$strPREFIX_OF_PROPERTY = 'OF_PROPERTY_';
$strPREFIX_IB_PROPERTY = 'IB_PROPERTY_';

$arDefPropInfo = [
	'ID' => 0,
	'IBLOCK_ID' => 0,
	'FILE_TYPE' => '',
	'LIST_TYPE' => 'L',
	'ROW_COUNT' => '1',
	'COL_COUNT' => '30',
	'LINK_IBLOCK_ID' => '0',
	'DEFAULT_VALUE' => '',
	'USER_TYPE_SETTINGS' => [],
	'WITH_DESCRIPTION' => '',
	'SEARCHABLE' => '',
	'FILTRABLE' => '',
	'ACTIVE' => 'Y',
	'MULTIPLE_CNT' => '5',
	'XML_ID' => '',
	'PROPERTY_TYPE' => 'S',
	'NAME' => '',
	'HINT' => '',
	'USER_TYPE' => '',
	'MULTIPLE' => 'N',
	'IS_REQUIRED' => 'N',
	'SORT' => '500',
	'CODE' => '',
	'SHOW_DEL' => 'N',
	'VALUES' => [],
	'SECTION_PROPERTY' => 'Y',
	'SMART_FILTER' => 'N',
	'DISPLAY_TYPE' => '',
	'DISPLAY_EXPANDED' => 'N',
	'FILTER_HINT' => '',
	'FEATURES' => [],
];

$arDisabledPropFields = [
	'ID',
	'IBLOCK_ID',
	'TIMESTAMP_X',
	'TMP_ID',
	'VERSION',
	'PROPINFO',
];

$arHiddenPropFields = [
	'IBLOCK_ID',
	'FILE_TYPE',
	'LIST_TYPE',
	'ROW_COUNT',
	'COL_COUNT',
	'LINK_IBLOCK_ID',
	'DEFAULT_VALUE',
	'USER_TYPE_SETTINGS',
	'WITH_DESCRIPTION',
	'SEARCHABLE',
	'FILTRABLE',
	'MULTIPLE_CNT',
	'HINT',
	'XML_ID',
	'VALUES',
	'SECTION_PROPERTY',
	'SMART_FILTER',
	'DISPLAY_TYPE',
	'DISPLAY_EXPANDED',
	'FILTER_HINT',
	'FEATURES',
];

function CheckIBlockTypeID($strIBlockTypeID,$strNewIBlockTypeID,$strNeedAdd): array
{
	$strNeedAdd = ('Y' == $strNeedAdd ? 'Y': 'N');
	$strNewIBlockTypeID = trim($strNewIBlockTypeID);
	$strIBlockTypeID = trim($strIBlockTypeID);
	if ('Y' == $strNeedAdd)
	{
		$obIBlockType = new CIBlockType();
		if ('' != $strNewIBlockTypeID)
		{
			$rsIBlockTypes = CIBlockType::GetByID($strNewIBlockTypeID);
			$arIBlockType = $rsIBlockTypes->Fetch();
			if ($arIBlockType)
			{
				$arResult = [
					'RESULT' => 'OK',
					'VALUE' => $strNewIBlockTypeID,
				];
			}
			else
			{
				$arFields = [
					'ID' => $strNewIBlockTypeID,
					'SECTIONS' => 'N',
					'IN_RSS' => 'N',
					'SORT' => 500,
				];
				$rsLanguages = CLanguage::GetList("sort", "desc", ['ACTIVE' => 'Y']);
				while ($arLanguage = $rsLanguages->Fetch())
				{
					$arFields['LANG'][$arLanguage['LID']]['NAME'] = $strNewIBlockTypeID;
				}
				$mxOffersType = $obIBlockType->Add($arFields);
				if (!$mxOffersType)
				{
					$arResult = [
						'RESULT' => 'ERROR',
						'MESSAGE' => $obIBlockType->getLastError(),
					];
				}
				else
				{
					$arResult = [
						'RESULT' => 'OK',
						'VALUE' => $strNewIBlockTypeID,
					];
				}
			}
		}
		else
		{
			$arResult = [
				'RESULT' => 'ERROR',
				'MESSAGE' => GetMessage('IB_E_OF_ERR_NEW_IBLOCK_TYPE_ABSENT'),
			];
		}
	}
	else
	{
		if ('' == $strIBlockTypeID)
		{
			$arResult = [
				'RESULT' => 'ERROR',
				'MESSAGE' => GetMessage('IB_E_OF_ERR_IBLOCK_TYPE_ABSENT')
			];
		}
		else
		{
			$rsIBlockTypes = CIBlockType::GetByID($strIBlockTypeID);
			if (!($arIBlockType = $rsIBlockTypes->Fetch()))
			{
				$arResult = [
					'RESULT' => 'ERROR',
					'MESSAGE' => GetMessage('IB_E_OF_ERR_IBLOCK_TYPE_BAD')
				];
			}
			else
			{
				$arResult = [
					'RESULT' => 'OK',
					'VALUE' => $strIBlockTypeID,
				];
			}
		}
	}

	return $arResult;
}

function ConvProp(&$arProperty, $arHiddenPropFields): void
{
	$arEncodedProp = [];
	foreach ($arHiddenPropFields as $strPropField)
	{
		if (isset($arProperty[$strPropField]))
		{
			$arEncodedProp[$strPropField] = $arProperty[$strPropField];
			unset($arProperty[$strPropField]);
		}
	}
	$arProperty['PROPINFO'] = base64_encode(serialize($arEncodedProp));
}

function GetPropertyInfo($strPrefix, $ID, $boolUnpack = true, $arHiddenPropFields = [])
{
	global $arDefPropInfo;
	$boolUnpack = ($boolUnpack === true);
	if (!is_array($arHiddenPropFields))
	{
		return false;
	}

	$arResult = false;
	if (isset($_POST[$strPrefix.$ID.'_NAME']) && ($_POST[$strPrefix.$ID.'_NAME'] <> '') && isset($_POST[$strPrefix.$ID.'_PROPINFO']))
	{
		$strEncodePropInfo = $_POST[$strPrefix.$ID.'_PROPINFO'];
		$strPropInfo = base64_decode($strEncodePropInfo);
		if (CheckSerializedData($strPropInfo))
		{
			$arResult = [
				'ID' => (isset($_POST[$strPrefix.$ID.'_ID']) && 0 < intval($_POST[$strPrefix.$ID.'_ID']) ? intval($_POST[$strPrefix.$ID.'_ID']) : 0),
				'NAME' => strval($_POST[$strPrefix.$ID."_NAME"]),
				'SORT' => (0 < intval($_POST[$strPrefix.$ID."_SORT"]) ? intval($_POST[$strPrefix.$ID."_SORT"]) : 500),
				'CODE' => (isset($_POST[$strPrefix.$ID."_CODE"]) ? strval($_POST[$strPrefix.$ID."_CODE"]) : ''),
				'MULTIPLE' => (isset($_POST[$strPrefix.$ID."_MULTIPLE"]) && 'Y' == $_POST[$strPrefix.$ID."_MULTIPLE"] ? 'Y' : 'N'),
				'IS_REQUIRED' => (isset($_POST[$strPrefix.$ID."_IS_REQUIRED"]) && 'Y' == $_POST[$strPrefix.$ID."_IS_REQUIRED"] ? 'Y' : 'N'),
				'ACTIVE' => (isset($_POST[$strPrefix.$ID."_ACTIVE"]) && 'Y' == $_POST[$strPrefix.$ID."_ACTIVE"] ? 'Y' : 'N'),
				'USER_TYPE' => false,
			];

			if (isset($_POST[$strPrefix . $ID . '_PROPERTY_TYPE']))
			{
				if (false !== mb_strpos($_POST[$strPrefix . $ID . '_PROPERTY_TYPE'], ':'))
				{
					[$arResult['PROPERTY_TYPE'], $arResult['USER_TYPE']] = explode(
						':',
						$_POST[$strPrefix . $ID . '_PROPERTY_TYPE'],
						2
					);
				}
				else
				{
					$arResult['PROPERTY_TYPE'] = $_POST[$strPrefix . $ID . '_PROPERTY_TYPE'];
				}
			}

			if ($boolUnpack)
			{
				$arPropInfo = unserialize($strPropInfo, ['allowed_classes' => false]);
				foreach ($arHiddenPropFields as $strFieldKey)
				{
					$arResult[$strFieldKey] = ($arPropInfo[$strFieldKey] ?? $arDefPropInfo[$strFieldKey]);
				}
				$arResult['ROW_COUNT'] = (int)$arResult['ROW_COUNT'];
				if (0 >= $arResult['ROW_COUNT'])
					$arResult['ROW_COUNT'] = $arDefPropInfo['ROW_COUNT'];
				$arResult['COL_COUNT'] = (int)$arResult['COL_COUNT'];
				if (0 >= $arResult['COL_COUNT'])
					$arResult['COL_COUNT'] = $arDefPropInfo['COL_COUNT'];
				$arResult['LINK_IBLOCK_ID'] = (int)$arResult['LINK_IBLOCK_ID'];
				if (0 > $arResult['LINK_IBLOCK_ID'])
					$arResult['LINK_IBLOCK_ID'] = $arDefPropInfo['LINK_IBLOCK_ID'];
				$arResult['WITH_DESCRIPTION'] = ('Y' == $arResult['WITH_DESCRIPTION'] ? 'Y' : 'N');
				$arResult['FILTRABLE'] = ('Y' == $arResult['FILTRABLE'] ? 'Y' : 'N');
				$arResult['SEARCHABLE'] = ('Y' == $arResult['SEARCHABLE'] ? 'Y' : 'N');
				$arResult['SECTION_PROPERTY'] = ('N' == $arResult['SECTION_PROPERTY'] ? 'N' : 'Y');
				$arResult['SMART_FILTER'] = ('Y' == $arResult['SMART_FILTER'] ? 'Y' : 'N');
				$arResult['DISPLAY_TYPE'] = mb_substr($arResult['DISPLAY_TYPE'], 0, 1);
				$arResult['DISPLAY_EXPANDED'] = ('Y' == $arResult['DISPLAY_EXPANDED'] ? 'Y' : 'N');
				$arResult['MULTIPLE_CNT'] = (int)$arResult['MULTIPLE_CNT'];
				if (0 >= $arResult['MULTIPLE_CNT'])
					$arResult['MULTIPLE_CNT'] = $arDefPropInfo['MULTIPLE_CNT'];
				$arResult['LIST_TYPE'] = ('C' == $arResult['LIST_TYPE'] ? 'C' : 'L');
				if ('Y' != COption::GetOptionString("iblock", "show_xml_id") && isset($arResult["XML_ID"]))
					unset($arResult["XML_ID"]);
			}
			else
			{
				$arResult['PROPINFO'] = $strEncodePropInfo;
			}
			if (0 < (int)$ID)
			{
				$arResult['DEL'] = (isset($_POST[$strPrefix.$ID."_DEL"]) && ('Y' == $_POST[$strPrefix.$ID."_DEL"]) ? 'Y' : 'N');
			}
		}
	}

	return $arResult;
}

function CheckSKUProperty($ID, $SKUID): array
{
	$ID = (int)$ID;
	$SKUID = (int)$SKUID;
	if ($ID > 0 && $SKUID > 0)
	{
		$propertyId = CIBlockPropertyTools::createProperty($SKUID, CIBlockPropertyTools::CODE_SKU_LINK, ['LINK_IBLOCK_ID' => $ID]);
		if ($propertyId)
		{
			$arResult = [
				'RESULT' => 'OK',
				'VALUE' => $propertyId
			];
		}
		else
		{
			$arResult = [
				'RESULT' => 'ERROR',
				'MESSAGE' => implode('. ', CIBlockPropertyTools::getErrors())
			];
		}
	}
	else
	{
		$arResult = [
			'RESULT' => 'ERROR',
			'MESSAGE' => GetMessage('IB_E_OF_ERR_SKU_IBLOCKS_IS_ABSENT'),
		];
	}

	return $arResult;
}

function ConvertToSafe($arProp, $arDisFields)
{
	if (is_array($arProp))
	{
		foreach ($arProp as $key => $value)
		{
			if (!in_array($key, $arDisFields))
			{
				if (!is_array($value))
				{
					$arProp[$key] = htmlspecialcharsbx($value);
				}
				else
				{
					$arTempo = [];
					foreach ($value as $subkey => $subvalue)
					{
						$arTempo[$subkey] = htmlspecialcharsbx($subvalue);
					}
					$arProp[$key] = $arTempo;
				}
			}
		}
	}
	else
	{
		$arProp = htmlspecialcharsbx($arProp);
	}

	return $arProp;
}

function __AddPropCellID($intOFPropID, $strPrefix, $arPropInfo)
{
	$intOFPropID = (int)$intOFPropID;

	return ($intOFPropID > 0 ? $intOFPropID : '');
}

function __AddPropCellName($intOFPropID, $strPrefix, $arPropInfo)
{
	ob_start();
	?><input type="text" size="25" maxlength="255" name="<?= $strPrefix . $intOFPropID ?>_NAME" id="<?= $strPrefix . $intOFPropID ?>_NAME" value="<?= $arPropInfo['NAME'] ?>"><?php
	?><input type="hidden" name="<?= $strPrefix.$intOFPropID?>_PROPINFO" id="<?= $strPrefix.$intOFPropID?>_PROPINFO" value="<?=htmlspecialcharsbx($arPropInfo['PROPINFO']); ?>"><?php
	$strResult = ob_get_contents();
	ob_end_clean();

	return $strResult;
}

function __AddPropCellType($intOFPropID, $strPrefix, $arPropInfo)
{
	static $baseTypeList = null;
	static $arUserTypeList = null;

	if ($baseTypeList === null)
		$baseTypeList = Iblock\Helpers\Admin\Property::getBaseTypeList(true);
	if ($arUserTypeList === null)
	{
		$arUserTypeList = CIBlockProperty::GetUserType();
		\Bitrix\Main\Type\Collection::sortByColumn($arUserTypeList, ['DESCRIPTION' => SORT_STRING]);
	}
	$boolUserPropExist = !empty($arUserTypeList);
	ob_start();
	?><select name="<?= $strPrefix.$intOFPropID?>_PROPERTY_TYPE" id="<?= $strPrefix.$intOFPropID?>_PROPERTY_TYPE" style="width:150px"><?php
	if ($boolUserPropExist)
	{
		?><optgroup label="<?= GetMessage('IB_E_PROP_BASE_TYPE_GROUP'); ?>"><?php
	}
	foreach ($baseTypeList as $typeId => $typeTitle)
	{
		?><option value="<?=$typeId; ?>" <?=($arPropInfo['PROPERTY_TYPE'] == $typeId && !$arPropInfo['USER_TYPE'] ? ' selected' : '');?>><?=htmlspecialcharsbx($typeTitle); ?></option><?php
	}
	unset($typeTitle);
	unset($typeId);

	if ($boolUserPropExist)
	{
		?></optgroup><optgroup label="<?= GetMessage('IB_E_PROP_USER_TYPE_GROUP'); ?>"><?php
	}
	foreach($arUserTypeList as $ar)
	{
		?><option value="<?=htmlspecialcharsbx($ar["PROPERTY_TYPE"].":".$ar["USER_TYPE"])?>" <?if($arPropInfo['PROPERTY_TYPE']==$ar["PROPERTY_TYPE"] && $arPropInfo['USER_TYPE']==$ar["USER_TYPE"])echo " selected"?>><?=htmlspecialcharsbx($ar["DESCRIPTION"])?></option>
		<?php
	}
	if ($boolUserPropExist)
	{
		?></optgroup><?php
	}
	?>
	</select><?php
	$strResult = ob_get_contents();
	ob_end_clean();

	return $strResult;
}

function __AddPropCellActive($intOFPropID, $strPrefix, $arPropInfo)
{
	ob_start();
	?><input type="hidden" name="<?= $strPrefix.$intOFPropID?>_ACTIVE" id="<?= $strPrefix.$intOFPropID?>_ACTIVE_N" value="N">
	<input type="checkbox" name="<?= $strPrefix.$intOFPropID?>_ACTIVE" id="<?= $strPrefix.$intOFPropID?>_ACTIVE_Y" value="Y"<?php
	if ($arPropInfo['ACTIVE']=="Y") echo " checked"; ?> title="<?=htmlspecialcharsbx(GetMessage("IB_E_PROP_ACTIVE_SHORT")); ?>"><?php
	$strResult = ob_get_contents();
	ob_end_clean();

	return $strResult;
}

function __AddPropCellMulti($intOFPropID, $strPrefix, $arPropInfo)
{
	ob_start();
	?><input type="hidden" name="<?= $strPrefix.$intOFPropID?>_MULTIPLE" id="<?= $strPrefix.$intOFPropID?>_MULTIPLE_N" value="N">
	<input type="checkbox" name="<?= $strPrefix.$intOFPropID?>_MULTIPLE" id="<?= $strPrefix.$intOFPropID?>_MULTIPLE_Y" value="Y"<?php
	if($arPropInfo['MULTIPLE']=="Y")echo " checked"?> title="<?=htmlspecialcharsbx(GetMessage("IB_E_PROP_MULT_SHORT")); ?>">
	<?php
	$strResult = ob_get_contents();
	ob_end_clean();

	return $strResult;
}

function __AddPropCellReq($intOFPropID, $strPrefix, $arPropInfo)
{
	ob_start();
	?><input type="hidden" name="<?= $strPrefix.$intOFPropID?>_IS_REQUIRED" id="<?= $strPrefix.$intOFPropID?>_IS_REQUIRED_N" value="N">
	<input type="checkbox" name="<?= $strPrefix.$intOFPropID?>_IS_REQUIRED" id="<?= $strPrefix.$intOFPropID?>_IS_REQUIRED_Y" value="Y"<?php
	if($arPropInfo['IS_REQUIRED']=="Y")echo " checked"?> title="<?=htmlspecialcharsbx(GetMessage("IB_E_PROP_REQIRED_SHORT")); ?>"><?php
	$strResult = ob_get_contents();
	ob_end_clean();

	return $strResult;
}

function __AddPropCellSort($intOFPropID, $strPrefix, $arPropInfo)
{
	ob_start();
	?><input type="text" size="3" maxlength="10"  name="<?= $strPrefix.$intOFPropID?>_SORT" id="<?= $strPrefix.$intOFPropID?>_SORT" value="<?= $arPropInfo['SORT']?>"><?php
	$strResult = ob_get_contents();
	ob_end_clean();

	return $strResult;
}

function __AddPropCellCode($intOFPropID, $strPrefix, $arPropInfo)
{
	ob_start();
	?><input type="text" size="20" maxlength="50" name="<?= $strPrefix.$intOFPropID?>_CODE" id="<?= $strPrefix.$intOFPropID?>_CODE" value="<?= $arPropInfo['CODE']?>"><?php
	$strResult = ob_get_contents();
	ob_end_clean();

	return $strResult;
}

function __AddPropCellDetail($intOFPropID,$strPrefix,$arPropInfo): string
{
	return '<input type="button" title="'.GetMessage("IB_E_PROP_EDIT_TITLE").'" name="'.$strPrefix.$intOFPropID.'_BTN" id="'.$strPrefix.$intOFPropID.'_BTN" value="..." data-propid="'.$intOFPropID.'">';
}

function __AddPropCellDelete($intOFPropID,$strPrefix,$arPropInfo): string
{
	$strResult = '&nbsp;';
	if (isset($arPropInfo['SHOW_DEL']) && $arPropInfo['SHOW_DEL'] == 'Y')
		$strResult = '<input type="checkbox" name="'.$strPrefix.$intOFPropID.'_DEL" id="'.$strPrefix.$intOFPropID.'_DEL" value="Y">';

	return $strResult;
}

function __AddPropRow($intOFPropID,$strPrefix,$arPropInfo): string
{
	return '<tr id="'.$strPrefix.$intOFPropID.'">
	<td style="vertical-align:middle;">'.__AddPropCellID($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td>'.__AddPropCellName($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td>'.__AddPropCellType($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td style="text-align: center; vertical-align:middle;">'.__AddPropCellActive($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td style="text-align: center; vertical-align:middle;">'.__AddPropCellMulti($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td style="text-align: center; vertical-align:middle;">'.__AddPropCellReq($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td>'.__AddPropCellSort($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td>'.__AddPropCellCode($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td style="text-align: center; vertical-align:middle;">'.__AddPropCellDetail($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td style="text-align: center; vertical-align:middle;">'.__AddPropCellDelete($intOFPropID,$strPrefix,$arPropInfo).'</td>
	</tr>';
}

$arNewPropInfo = $arDefPropInfo;
ConvProp($arNewPropInfo,$arHiddenPropFields);
$arCellTemplates = [];
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellID('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellName('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellType('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellActive('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellMulti('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellReq('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellSort('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellCode('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellDetail('tmp_xxx','PREFIX',$arNewPropInfo));
$arCellTemplates[] = CUtil::JSEscape(__AddPropCellDelete('tmp_xxx','PREFIX',$arNewPropInfo));

$arCellAttr = [4,5,6,9,10];

$returnUrl = trim((string)$request->get('return_url'));
$isAdminUrl = $request->get('admin') === 'Y' ? 'Y' : 'N';

$bBizproc = Loader::includeModule('bizproc');
$bCatalog = Loader::includeModule('catalog');

$canUseYandexMarket = $bCatalog && \Bitrix\Catalog\Config\Feature::isCanUseYandexExport();

$type = (string)$request->get('type');
$arIBTYPE = CIBlockType::GetByIDLang($type, LANGUAGE_ID);

if ($arIBTYPE === false)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$strWarning = '';
$bVarsFromForm = false;
$defaultIblockValues = [
	'LID' => [], // need check
	'CODE' => '',
	'API_CODE' => '',
	'REST_ON' => '',
	'NAME' => '',
	'ACTIVE' => '',
	'SORT' => 500,
	'LIST_PAGE_URL' => '',
	'DETAIL_PAGE_URL' => '',
	'SECTION_PAGE_URL' => '',
	'CANONICAL_PAGE_URL' => '',
	'PICTURE' => null, // need check
	'DESCRIPTION' => '',
	'DESCRIPTION_TYPE' => 'text',
	'RSS_TTL' => 24,
	'RSS_ACTIVE' => 'Y',
	'RSS_FILE_ACTIVE' => 'N',
	'RSS_FILE_LIMIT' => 0,
	'RSS_FILE_DAYS' => 0,
	'RSS_YANDEX_ACTIVE' => 'N',
	'XML_ID' => '',
	'INDEX_ELEMENT' => 'Y',
	'INDEX_SECTION' => 'N',
	'WORKFLOW' => 'Y',
	'BIZPROC' => 'N',
	'SECTION_CHOOSER' => '', // need check
	'LIST_MODE' => '', // need check
	'RIGHTS_MODE' => Iblock\IblockTable::RIGHTS_SIMPLE,
	'VERSION' =>  Iblock\IblockTable::PROPERTY_STORAGE_COMMON,
	'EDIT_FILE_BEFORE' => '',
	'EDIT_FILE_AFTER' => '',
	'SECTIONS_NAME' => '',
	'SECTION_NAME' => '',
	'ELEMENTS_NAME' => '',
	'ELEMENT_NAME' => '',
];
$defaultCatalogValues = [
	'IS_CATALOG' => 'N',
	'SUBSCRIPTION' => 'N',
	'YANDEX_EXPORT' => 'N',
	'VAT_ID' => 0,
	'USED_SKU' => 'N',
	'OF_IBLOCK_ID' => 0,
	'OF_IBLOCK_TYPE_ID' => '',
	'OF_NEW_IBLOCK_TYPE_ID' => '',
	'OF_CREATE_IBLOCK_TYPE_ID' => 'N',
	'OFFERS_PROPERTY_COUNT' => 0,
	'OF_IBLOCK_NAME' => '',
];
$formFields = [];

if (
	$request->isPost()
	&& check_bitrix_sessid()
	&& CIBlockRights::UserHasRightTo($ID, $ID, 'iblock_edit')
	&& $request->getPost('Update') === 'Y'
	&& $request->getPost('propedit') === null
)
{
	$DB->StartTransaction();

	$arPICTURE = $_FILES["PICTURE"] ?? [];
	$arPICTURE["del"] = (string)$request->getPost('PICTURE_del');
	$arPICTURE["MODULE_ID"] = "iblock";

	$VERSION = (int)$request->getPost('VERSION');
	if ($VERSION !== Iblock\IblockTable::PROPERTY_STORAGE_SEPARATE)
	{
		$VERSION = Iblock\IblockTable::PROPERTY_STORAGE_COMMON;
	}
	$RSS_ACTIVE = (string)$request->getPost('RSS_ACTIVE');
	if ($RSS_ACTIVE !== 'Y')
	{
		$RSS_ACTIVE = 'N';
	}
	$RSS_FILE_ACTIVE = (string)$request->getPost('RSS_FILE_ACTIVE');
	if ($RSS_FILE_ACTIVE !== 'Y')
	{
		$RSS_FILE_ACTIVE = 'N';
	}
	$RSS_YANDEX_ACTIVE = (string)$request->getPost('RSS_YANDEX_ACTIVE');
	if ($RSS_YANDEX_ACTIVE !== 'Y')
	{
		$RSS_YANDEX_ACTIVE = 'N';
	}

	$ib = new CIBlock();

	$formFields['IBLOCK_TYPE_ID'] = $type;
	$formFields['PICTURE'] = $arPICTURE;

	$stringList = [
		'NAME',
		'CODE',
		'API_CODE',
		'LIST_PAGE_URL',
		'DETAIL_PAGE_URL',
		'CANONICAL_PAGE_URL',
		'DESCRIPTION',
		'DESCRIPTION_TYPE',
		'EDIT_FILE_BEFORE',
		'EDIT_FILE_AFTER',
		'SECTION_CHOOSER',
		'LIST_MODE',
		'ELEMENTS_NAME',
		'ELEMENT_NAME',
		'ELEMENT_ADD',
		'ELEMENT_EDIT',
		'ELEMENT_DELETE',
	];
	if (Main\Config\Option::get('iblock', 'show_xml_id') === 'Y')
	{
		$stringList[] = 'XML_ID';
	}
	if ($arIBTYPE['SECTIONS'] === 'Y')
	{
		$stringList[] = 'SECTION_PAGE_URL';
		$stringList[] = 'SECTIONS_NAME';
		$stringList[] = 'SECTION_NAME';
		$stringList[] = 'SECTION_ADD';
		$stringList[] = 'SECTION_EDIT';
		$stringList[] = 'SECTION_DELETE';
	}
	if (CIBlockRights::UserHasRightTo($ID, $ID, 'iblock_rights_edit'))
	{
		$stringList[] = 'RIGHTS_MODE';
	}
	foreach ($stringList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$formFields[$fieldId] = $value;
		}
	}

	$booleanList = [
		'ACTIVE',
		'REST_ON',
		'INDEX_ELEMENT',
	];
	if ($arIBTYPE['SECTIONS'] == 'Y')
	{
		$booleanList[] = 'INDEX_SECTION';
	}
	if ($arIBTYPE['IN_RSS'] === 'Y')
	{
		$booleanList[] = 'RSS_ACTIVE';
		$booleanList[] = 'RSS_FILE_ACTIVE';
		$booleanList[] = 'RSS_YANDEX_ACTIVE';
	}
	foreach ($booleanList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if ($value === 'Y' || $value === 'N')
		{
			$formFields[$fieldId] = $value;
		}
	}

	$arrayList = [
		'LID',
		'FIELDS',
	];
	foreach ($arrayList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (!empty($value) && is_array($value))
		{
			$formFields[$fieldId] = $value;
		}
	}

	$intList = [
		'SORT',
	];
	if ($arIBTYPE['IN_RSS'] === 'Y')
	{
		$intList[] = 'RSS_FILE_LIMIT';
		$intList[] = 'RSS_FILE_DAYS';
		$intList[] = 'RSS_TTL';
	}
	foreach ($intList as $fieldId)
	{
		$value = $request->getPost($fieldId);
		if (is_string($value))
		{
			$value = (int)$value;
			if ($value > 0)
			{
				$formFields[$fieldId] = $value;
			}
		}
	}

	$value = $request->getPost('WF_TYPE');
	$formFields['WORKFLOW'] = $value === 'WF' ? 'Y' : 'N';
	$formFields['BIZPROC'] = $value === 'BP' ? 'Y' : 'N';

	$arFields = $formFields;

	if (CIBlockRights::UserHasRightTo($ID, $ID, "iblock_rights_edit"))
	{
		if (isset($arFields["RIGHTS_MODE"]) && $arFields["RIGHTS_MODE"] === Iblock\IblockTable::RIGHTS_EXTENDED)
		{
			$extendedRights = $request->getPost('RIGHTS');
			$groupRights = $request->getPost('GROUP');
			if (is_array($extendedRights))
			{
				$arFields["RIGHTS"] = CIBlockRights::Post2Array($extendedRights);
			}
			elseif (is_array($groupRights))
			{
				$arFields["GROUP_ID"] = $groupRights;
			}
			else
			{
				$arFields["RIGHTS"] = [];
			}
			unset($groupRights, $extendedRights);
		}
		else
		{
			$groupRights = $request->getPost('GROUP');
			$arFields['GROUP_ID'] = is_array($groupRights) ? $groupRights : [];
			unset($groupRights);
		}
	}

	//Assembly properties for check followed by add/update

	$ibp = new CIBlockProperty();
	$arProperties = [];
	if($ID > 0)
	{
		$props = CIBlockProperty::GetList([], ["IBLOCK_ID" => $ID, "CHECK_PERMISSIONS" => "N"]);
		while($p = $props->Fetch())
		{
			$arProperty = GetPropertyInfo($strPREFIX_IB_PROPERTY, $p['ID'], true, $arHiddenPropFields);
			if (!is_array($arProperty))
			{
				$strWarning .= GetMessage("IB_E_PROPERTY_ERROR")." [".$p['ID']."] ".$p['NAME']."<br>";
				$bVarsFromForm = true;
			}
			else
			{
				$res = $ibp->CheckFields($arProperty, $p["ID"], true);
				if (!$res)
				{
					$strWarning .= GetMessage("IB_E_PROPERTY_ERROR") . ': ' . $ibp->getLastError() . '<br>';
					$bVarsFromForm = true;
				}
			}
			$arProperties[$p["ID"]] = $arProperty;
		}
	}

	$intPropCount = (int)($_POST['IBLOCK_PROPERTY_COUNT'] ?? 0);
	for ($i = 0; $i < $intPropCount; $i++)
	{
		$arProperty = GetPropertyInfo($strPREFIX_IB_PROPERTY, 'n'.$i, true, $arHiddenPropFields);
		if (!is_array($arProperty))
			continue;
		$res = $ibp->CheckFields($arProperty, false, true);
		if(!$res)
		{
			$strWarning .= $ibp->getLastError() . '<br>';
			$bVarsFromForm = true;
		}

		$arProperties["n".$i] = $arProperty;
	}

	$bDublicate = false;
	$arDublicateCodes = [];
	$arPropertyCodes = [];
	$bSectionProperty = false;
	foreach($arProperties as $i => $arProperty)
	{
		if($arProperty["SECTION_PROPERTY"] === "N")
			$bSectionProperty = true;
		if($arProperty["SMART_FILTER"] === "Y")
			$bSectionProperty = true;
		if ('' != $arProperty['CODE'])
		{
			$strPropertyCode = mb_strtoupper($arProperty['CODE']);
			if (!isset($arProperty['DEL']) || $arProperty['DEL'] == 'N')
			{
				if (isset($arPropertyCodes[$strPropertyCode]))
				{
					$bDublicate = true;
					$arDublicateCodes[$strPropertyCode] = true;
				}
				else
				{
					$arPropertyCodes[$strPropertyCode] = true;
				}
			}
		}
	}
	if($bSectionProperty)
		$arFields["SECTION_PROPERTY"] = "Y";
	unset($arPropertyCodes);
	if ($bDublicate)
	{
		$bVarsFromForm = true;
		$strWarning .= GetMessage('IB_E_ERR_PROPERTY_CODE_DUBLICATE_EXT').' '.implode(', ', array_keys($arDublicateCodes)).'<br>';
	}
	unset($arDublicateCodes);
	unset($bDublicate);

	$ipropertyTemplates = $request->getPost('IPROPERTY_TEMPLATES');
	if (is_array($ipropertyTemplates))
	{
		$arFields['IPROPERTY_TEMPLATES'] = [];

		$simpleTemplateNameList = [
			'SECTION_META_TITLE',
			'SECTION_META_KEYWORDS',
			'SECTION_META_DESCRIPTION',
			'SECTION_PAGE_TITLE',
			'ELEMENT_META_TITLE',
			'ELEMENT_META_KEYWORDS',
			'ELEMENT_META_DESCRIPTION',
			'ELEMENT_PAGE_TITLE',
			'SECTION_PICTURE_FILE_ALT',
			'SECTION_PICTURE_FILE_TITLE',
			'SECTION_DETAIL_PICTURE_FILE_ALT',
			'SECTION_DETAIL_PICTURE_FILE_TITLE',
			'ELEMENT_PREVIEW_PICTURE_FILE_ALT',
			'ELEMENT_PREVIEW_PICTURE_FILE_TITLE',
			'ELEMENT_DETAIL_PICTURE_FILE_ALT',
			'ELEMENT_DETAIL_PICTURE_FILE_TITLE',
		];
		foreach ($simpleTemplateNameList as $templateName)
		{
			if (isset($ipropertyTemplates[$templateName]['TEMPLATE']) && is_string($ipropertyTemplates[$templateName]['TEMPLATE']))
			{
				$arFields['IPROPERTY_TEMPLATES'][$templateName] = $ipropertyTemplates[$templateName]['TEMPLATE'];
			}
		}
		unset($templateName, $simpleTemplateNameList);

		$fileNameTemplateList = [
			'SECTION_PICTURE_FILE_NAME',
			'SECTION_DETAIL_PICTURE_FILE_NAME',
			'ELEMENT_PREVIEW_PICTURE_FILE_NAME',
			'ELEMENT_DETAIL_PICTURE_FILE_NAME',
		];
		foreach ($fileNameTemplateList as $templateName)
		{
			if (isset($ipropertyTemplates[$templateName]) && is_array($ipropertyTemplates[$templateName]))
			{
				$arFields['IPROPERTY_TEMPLATES'][$templateName] = Template\Helper::convertArrayToModifiers(
					$ipropertyTemplates[$templateName]
				);
			}
		}
		unset($templateName, $fileNameTemplateList);

		if (empty($arFields['IPROPERTY_TEMPLATES']))
		{
			unset($arFields['IPROPERTY_TEMPLATES']);
		}
	}
	unset($ipropertyTemplates);

	$bCreateRecord = $ID <= 0;

	if (!$bVarsFromForm)
	{
		$res_log = [
			'NAME' => ($arFields['NAME'] ?? ''),
		];
		if ($ID > 0)
		{
			$res = $ib->Update($ID, $arFields);
			if(COption::GetOptionString("iblock", "event_log_iblock", "N") === "Y" && $res)
				CEventLog::Log(
					"IBLOCK",
					"IBLOCK_EDIT",
					"iblock",
					$ID,
					serialize($res_log)
				);
		}
		else
		{
			$arFields["VERSION"] = $VERSION;
			$ID = $ib->Add($arFields);
			$res = ($ID>0);
			if(COption::GetOptionString("iblock", "event_log_iblock", "N") === "Y" && $res)
				CEventLog::Log(
					"IBLOCK",
					"IBLOCK_ADD",
					"iblock",
					$ID,
					serialize($res_log)
				);
		}
		unset($res_log);

		if (!$res)
		{
			$strWarning .= $ib->getLastError() . '<br>';
			$bVarsFromForm = true;
		}
		else
		{
			// RSS agent creation
			$agentPeriod = ($arFields['RSS_TTL'] ?? $defaultIblockValues['RSS_TTL']) * 36000;

			CAgent::RemoveAgent('CIBlockRSS::PreGenerateRSS(' . $ID . ', false);', 'iblock');
			if (($arFields['RSS_FILE_ACTIVE'] ?? $defaultIblockValues['RSS_FILE_ACTIVE']) === 'Y')
			{
				CAgent::AddAgent(
					'CIBlockRSS::PreGenerateRSS(' . $ID . ', false);',
					'iblock',
					'N',
					$agentPeriod,
					'',
					'Y'
				);
			}

			CAgent::RemoveAgent('CIBlockRSS::PreGenerateRSS(' . $ID . ', true);', 'iblock');
			if (($arFields['RSS_YANDEX_ACTIVE'] ?? $defaultIblockValues['RSS_YANDEX_ACTIVE']) === 'Y')
			{
				CAgent::AddAgent(
					'CIBlockRSS::PreGenerateRSS(' . $ID . ', true);',
					'iblock',
					'N',
					$agentPeriod,
					'',
					'Y'
				);
			}

			if ($request->getPost('IPROPERTY_CLEAR_VALUES') === 'Y')
			{
				$ipropValues = new InheritedProperty\IblockValues($ID);
				$ipropValues->clearValues();
				unset($ipropValues);
			}

			/********************/
			CIBlock::disableClearTagCache();
			$ibp = new CIBlockProperty();
			foreach($arProperties as $property_id => $arProperty)
			{
				$property_id = (int)$property_id;
				$arProperty["IBLOCK_ID"] = $ID;
				if ($property_id > 0)
				{
					if (isset($arProperty['DEL']) && $arProperty['DEL'] == 'Y')
					{
						if(!CIBlockProperty::Delete($property_id) && ($ex = $APPLICATION->GetException()))
						{
							$strWarning .= GetMessage("IB_E_PROPERTY_ERROR").": ".$ex->GetString()."<br>";
							$bVarsFromForm = true;
						}
					}
					else
					{
						$res = $ibp->Update($property_id, $arProperty);
						if(!$res)
						{
							$strWarning .= GetMessage("IB_E_PROPERTY_ERROR") . ': ' . $ibp->getLastError() . '<br>';
							$bVarsFromForm = true;
						}
					}
				}
				else
				{
					$PropID = (int)$ibp->Add($arProperty);
					if($PropID <= 0)
					{
						$strWarning .= $ibp->getLastError() . '<br>';
						$bVarsFromForm = true;
					}
				}
			}
			CIBlock::enableClearTagCache();
			if (!$bVarsFromForm)
			{
				CIBlock::clearIblockTagCache($ID);
			}
			/*******************************************/

			if (!$bVarsFromForm && $arIBTYPE['IN_RSS'] === 'Y')
			{
				CIBlockRSS::Delete($ID);
				$arNodesRSS = CIBlockRSS::GetRSSNodes();
				foreach ($arNodesRSS as $key => $val)
				{
					$formNodeValue = (string)$request->getPost('RSS_NODE_VALUE_' . $key);
					if ($formNodeValue !== '')
					{
						CIBlockRSS::Add($ID, $val, $formNodeValue);
					}
				}
			}

			if (!$bVarsFromForm && !$bCreateRecord && $bBizproc)
			{
				$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(["iblock", "CIBlockDocument", "iblock_".$ID]);
				foreach ($arWorkflowTemplates as $t)
				{
					$create_bizproc = $request->getPost('create_bizproc_' . $t['ID']) === 'Y';
					$edit_bizproc = $request->getPost('edit_bizproc_' . $t['ID']) === 'Y';

					$create_bizproc1 = (($t["AUTO_EXECUTE"] & 1) != 0);
					$edit_bizproc1 = (($t["AUTO_EXECUTE"] & 2) != 0);

					if ($create_bizproc !== $create_bizproc1 || $edit_bizproc !== $edit_bizproc1)
					{
						$arErrorsTmp = [];
						CBPDocument::UpdateWorkflowTemplate(
							$t["ID"],
							["iblock", "CIBlockDocument", "iblock_".$ID],
							[
								"AUTO_EXECUTE" => (($create_bizproc ? 1 : 0) | ($edit_bizproc ? 2 : 0))
							],
							$arErrorsTmp
						);
					}
				}
			}

			$boolNeedAgent = false;
			if (!$bVarsFromForm && $bCatalog)
			{
				$boolFlag = true;
				$obCatalog = new CCatalog();
				$arCatalog = CCatalog::GetByIDExt($ID);

				if (!isset($IS_CATALOG) || ('Y' != $IS_CATALOG && 'N' != $IS_CATALOG))
				{
					$bVarsFromForm = true;
					$strWarning .= GetMessage('IB_E_OF_ERR_IS_CATALOG') . '<br>';
				}
				if (!isset($SUBSCRIPTION) || ('Y' != $SUBSCRIPTION && 'N' != $SUBSCRIPTION))
				{
					$bVarsFromForm = true;
					$strWarning .= GetMessage('IB_E_OF_ERR_SUBSCRIPTION') . '<br>';
				}

				if (!$bVarsFromForm)
				{
					if (('Y' == $IS_CATALOG) || ('Y' == $SUBSCRIPTION))
					{
						if ($canUseYandexMarket)
						{
							if (!isset($YANDEX_EXPORT) || ('Y' != $YANDEX_EXPORT && 'N' != $YANDEX_EXPORT))
							{
								$bVarsFromForm = true;
								$strWarning .= GetMessage('IB_E_OF_ERR_YANDEX_EXPORT') . '<br>';
							}
						}
						if (!isset($VAT_ID))
						{
							$bVarsFromForm = true;
							$strWarning .= GetMessage('IB_E_OF_ERR_VAT_ID') . '<br>';
						}
					}
				}
				if (!isset($USED_SKU) || ('Y' != $USED_SKU && 'N' != $USED_SKU))
				{
					$bVarsFromForm = true;
					$strWarning .= GetMessage('IB_E_OF_ERR_USED_SKU') . '<br>';
				}

				if (!$bVarsFromForm)
				{
					$IS_CATALOG = ($request->getPost('IS_CATALOG') === 'Y' ? 'Y' : 'N');
					$SUBSCRIPTION = ($request->getPost('SUBSCRIPTION') === 'Y' ? 'Y' : 'N');
					if (!(CBXFeatures::IsFeatureEnabled('SaleRecurring')))
					{
						$SUBSCRIPTION = 'N';
					}
					$YANDEX_EXPORT = ($request->getPost('YANDEX_EXPORT') === 'Y' ? 'Y' : 'N');
					if ($IS_CATALOG === 'Y')
					{
						$VAT_ID = (int)($request->getPost('VAT_ID') ?? 0);
						if ($VAT_ID < 0)
						{
							$VAT_ID = 0;
						}
					}
					else
					{
						$VAT_ID = 0;
					}

					//$SKU_RIGHTS = ('Y' == $SKU_RIGHTS ? 'Y' : 'N');
					$SKU_RIGHTS = 'N';

					if (is_array($arCatalog) && $arCatalog['CATALOG_TYPE'] == 'O')
					{
						$IS_CATALOG = 'Y';
						$arOffersFields = [
							'IBLOCK_ID' => $ID,
							'SUBSCRIPTION' => $SUBSCRIPTION,
							'YANDEX_EXPORT' => $YANDEX_EXPORT,
							'VAT_ID' => $VAT_ID,
						];
						$boolFlag = $obCatalog->Update($ID,$arOffersFields);
						if (!$boolFlag)
						{
							$bVarsFromForm = true;
							$ex = $APPLICATION->GetException();
							if ($ex)
							{
								$strWarning .= $ex->GetString() . '<br>';
							}
						}
						else
						{
							$boolNeedAgent = ($YANDEX_EXPORT != $arCatalog['YANDEX_EXPORT']);
						}
					}
					else
					{
						$arOffersFields = [
							'IBLOCK_ID' => $ID,
							'SUBSCRIPTION' => $SUBSCRIPTION,
							'YANDEX_EXPORT' => $YANDEX_EXPORT,
							'VAT_ID' => $VAT_ID,
						];

						if (false == $arCatalog || 'P' == $arCatalog['CATALOG_TYPE'])
						{
							if ($IS_CATALOG == 'Y')
							{
								$boolFlag = $obCatalog->Add($arOffersFields);
							}
							if ($boolFlag && $arOffersFields['YANDEX_EXPORT'] == 'Y')
							{
								$boolNeedAgent = true;
							}
						}
						else
						{
							if ($IS_CATALOG == 'Y' || $SUBSCRIPTION == 'Y')
							{
								$boolFlag = $obCatalog->Update($ID, $arOffersFields);
								if ($boolFlag)
									$boolNeedAgent = ($YANDEX_EXPORT != $arCatalog['YANDEX_EXPORT']);
							}
							elseif (('Y' != $IS_CATALOG) && ('Y' != $SUBSCRIPTION))
							{
								$boolFlag = $obCatalog->Delete($ID);
								if ($boolFlag)
									$boolNeedAgent == ('Y' == $arCatalog['YANDEX_EXPORT']);
							}
						}
						if (!$boolFlag)
						{
							$bVarsFromForm = true;
							$ex = $APPLICATION->GetException();
							if ($ex)
							{
								$strWarning .= $ex->GetString() . '<br>';
							}
						}
						if (!$bVarsFromForm)
						{
							// start offers
							if ('Y' == $USED_SKU)
							{
								if (0 == $OF_IBLOCK_ID)
								{
									$bVarsFromForm = true;
									$strWarning .= GetMessage('IB_E_OF_ERR_OFFERS_IS_ABSENT') . '<br>';
								}
								elseif (CATALOG_NEW_OFFERS_IBLOCK_NEED == $OF_IBLOCK_ID)
								{
									$arCheckIBlockType = CheckIBlockTypeID($OF_IBLOCK_TYPE_ID,$OF_NEW_IBLOCK_TYPE_ID,$OF_CREATE_IBLOCK_TYPE_ID);
									if (!$arCheckIBlockType)
									{
										$bVarsFromForm = true;
										$strWarning .= GetMessage('IB_E_OF_ERR_IBLOCK_TYPE_UNKNOWN_ERR') . '<br>';
									}
									else
									{
										if ($arCheckIBlockType['RESULT'] === 'ERROR')
										{
											$bVarsFromForm = true;
											$strWarning .= $arCheckIBlockType['MESSAGE'] . '<br>';
										}
										else
										{
											$OF_IBLOCK_TYPE_ID = $arCheckIBlockType['VALUE'];
											$OF_CREATE_IBLOCK_TYPE_ID = 'N';
										}
									}

									$ibp = new CIBlockProperty();
									if (!$bVarsFromForm)
									{
										$intCountOFProp = intval($OFFERS_PROPERTY_COUNT);
										$arOfPropList = [];
										for ($i = 0; $i < $intCountOFProp; $i++)
										{
											$arOFProperty = GetPropertyInfo(
												$strPREFIX_OF_PROPERTY,
												'n' . $i,
												true,
												$arHiddenPropFields
											);
											if ($arOFProperty !== false)
											{
												$res = $ibp->CheckFields($arOFProperty, false, true);
												if (!$res)
												{
													$strWarning .= GetMessage('IB_E_PROPERTY_ERROR') . ': ' . $ibp->getLastError() . '<br>';
													$bVarsFromForm = true;
												}
												$arOfPropList[] = $arOFProperty;
											}
										}
									}

									if (!$bVarsFromForm)
									{
										$arOffersFields = [
											"ACTIVE"=>'Y',
											"NAME"=>$OF_IBLOCK_NAME,
											"IBLOCK_TYPE_ID"=>$OF_IBLOCK_TYPE_ID,
											"LID"=>$LID,
											"WORKFLOW"=>"N",
											"BIZPROC"=>"N",
											"LIST_PAGE_URL" => '',
											"SECTION_PAGE_URL" => '',
											"DETAIL_PAGE_URL" => '#PRODUCT_URL#',
											"INDEX_SECTION" => "N",
										];
										$arOffersFields["RIGHTS_MODE"] = $RIGHTS_MODE;
										if ($arOffersFields["RIGHTS_MODE"] == "E")
										{
											if(is_array($_POST["RIGHTS"]))
											{
												$arOffersFields["RIGHTS"] = [];
												$s_rights = new CIBlockRights($ID);
												foreach($s_rights->GetRights() as $k=>$v)
													$arOffersFields["RIGHTS"]["n".$k] = $v;
											}
											elseif(is_array($_POST["GROUP"]))
											{
												$arGroup = $_POST["GROUP"];
												foreach ($arGroup as &$value)
												{
													if ($value === 'U')
													{
														$value = 'W';
													}
												}
												unset($value);

												$arOffersFields["GROUP_ID"] = $arGroup;
											}
											else
											{
												$arOffersFields["RIGHTS"] = [];
											}
										}
										else
										{
											$arGroup = $GROUP;
											foreach ($arGroup as &$value)
											{
												if ($value === 'U')
												{
													$value = 'W';
												}
											}
											unset($value);

											$arOffersFields["GROUP_ID"] = $arGroup;
										}
										$arLogFields = [];
										if (!empty($_REQUEST["FIELDS"]))
										{
											foreach ($_REQUEST["FIELDS"] as $strLogField => $arOneLogField)
											{
												if(!preg_match("/^LOG_/", $strLogField)) continue;
												$arLogFields[$strLogField] = $arOneLogField;
											}
										}
										if (!empty($arLogFields))
											$arOffersFields["FIELDS"] = $arLogFields;

										$obIBlock = new CIBlock();
										$mxOffersID = $obIBlock->Add($arOffersFields);
										if (!$mxOffersID)
										{
											$strWarning .= $obIBlock->getLastError() . '<br>';
											$bVarsFromForm = true;
										}
										else
										{
											$res_log = [];
											$res_log['NAME'] = $OF_IBLOCK_NAME;
											$OF_IBLOCK_ID = $mxOffersID;
											if (COption::GetOptionString("iblock", "event_log_iblock", "N") === "Y")
												CEventLog::Log(
													"IBLOCK",
													"IBLOCK_ADD",
													"iblock",
													$OF_IBLOCK_ID,
													serialize($res_log)
												);
										}
									}

									if (!$bVarsFromForm)
									{
										\CIBlock::disableClearTagCache();
										foreach ($arOfPropList as $arOFProperty)
										{
											$arOFProperty['IBLOCK_ID'] = $OF_IBLOCK_ID;
											$PropID = $ibp->Add($arOFProperty);
											if (intval($PropID)<=0)
											{
												$strWarning .= $ibp->getLastError() . '<br>';
												$bVarsFromForm = true;
											}
										}
										\CIBlock::enableClearTagCache();
									}
								}
								else
								{
									if (CIBlockRights::UserHasRightTo($OF_IBLOCK_ID, $OF_IBLOCK_ID, "iblock_edit"))
									{
										$arOffersFields = [];
										$arOffersFields['LID'] = $LID;

										if ('Y' == $SKU_RIGHTS)
										{
											$arOffersFields["RIGHTS_MODE"] = $RIGHTS_MODE;
											if ($arOffersFields["RIGHTS_MODE"] == "E")
											{
												if(is_array($_POST["RIGHTS"]))
												{
													$arOffersFields["RIGHTS"] = [];
													$s_rights = new CIBlockRights($ID);
													foreach($s_rights->GetRights() as $k=>$v)
														$arOffersFields["RIGHTS"]["n".$k] = $v;
												}
												elseif(is_array($_POST["GROUP"]))
												{
													$arGroup = $_POST["GROUP"];
													foreach ($arGroup as &$value)
													{
														if ($value === 'U')
														{
															$value = 'W';
														}
													}
													unset($value);

													$arOffersFields["GROUP_ID"] = $arGroup;
												}
												else
												{
													$arOffersFields["RIGHTS"] = [];
												}
											}
											else
											{
												$arGroup = $GROUP;
												foreach ($arGroup as &$value)
												{
													if ($value === 'U')
													{
														$value = 'W';
													}
												}
												unset($value);

												$arOffersFields["GROUP_ID"] = $arGroup;
											}
										}
										$arLogFields = [];
										if (!empty($_REQUEST["FIELDS"]))
										{
											foreach ($_REQUEST["FIELDS"] as $strLogField => $arOneLogField)
											{
												if(!preg_match("/^LOG_/", $strLogField)) continue;
												$arLogFields[$strLogField] = $arOneLogField;
											}
										}
										if (!empty($arLogFields))
										{
											$arOffersOldFields = CIBlock::GetFields($OF_IBLOCK_ID);
											$arOffersFields["FIELDS"] = $arOffersOldFields;
											foreach ($arLogFields as $keyLogField => $valueLogField)
											{
												$arOffersFields["FIELDS"][$keyLogField] = $valueLogField;
											}
										}

										$obIBlock = new CIBlock();
										$mxOffersID = $obIBlock->Update($OF_IBLOCK_ID,$arOffersFields);
										if (!$mxOffersID)
										{
											$strWarning .= $obIBlock->getLastError() . '<br>';
											$bVarsFromForm = true;
										}
										else
										{
											$res_log = [];
											$res_log['NAME'] = CIBlock::GetArrayByID($OF_IBLOCK_ID,'NAME');
											if (COption::GetOptionString("iblock", "event_log_iblock", "N") === "Y")
												CEventLog::Log(
													"IBLOCK",
													"IBLOCK_EDIT",
													"iblock",
													$OF_IBLOCK_ID,
													serialize($res_log)
												);
										}
									}
									else
									{
										$strWarning .=
											str_replace(
												'#ID#',
												$catalogFields['OF_IBLOCK_ID'],
												GetMessage('IB_E_RIGHTS_IBLOCK_ACCESS_DENIED')
											)
											. '<br>'
										;
										$bVarsFromForm = true;
									}
								}

								if (!$bVarsFromForm)
								{
									$arSKUProp = CheckSKUProperty($ID,$OF_IBLOCK_ID);
									if ('OK' == $arSKUProp['RESULT'])
									{
										$intSKUPropID = $arSKUProp['VALUE'];
									}
									else
									{
										$bVarsFromForm = true;
										$strWarning .= $arSKUProp['MESSAGE'].'<br>';
									}
								}

								if (!$bVarsFromForm)
								{
									if ((false !== $arCatalog) && (0 < intval($arCatalog['OFFERS_IBLOCK_ID'])) &&($arCatalog['OFFERS_IBLOCK_ID'] != $OF_IBLOCK_ID))
									{
										$boolFlag = $obCatalog->UnLinkSKUIBlock($ID);
									}
									if ((false === $arCatalog) || ($arCatalog['OFFERS_IBLOCK_ID'] != $OF_IBLOCK_ID))
									{
										$arOffersFields = [
											'IBLOCK_ID' => $OF_IBLOCK_ID,
											'PRODUCT_IBLOCK_ID' => $ID,
											'SKU_PROPERTY_ID' => $intSKUPropID,
										];
										$arOFCatalog = CCatalog::GetByID($OF_IBLOCK_ID);
										if ($arOFCatalog)
										{
											$boolFlag = $obCatalog->Update($OF_IBLOCK_ID,$arOffersFields);
										}
										else
										{
											$boolFlag = $obCatalog->Add($arOffersFields);
										}
									}
									$ex = $APPLICATION->GetException();
									if ($ex)
									{
										$strWarning .= $ex->GetString() . '<br>';
										$bVarsFromForm = true;
									}
								}
							}
							else
							{
								if ((false !== $arCatalog) && (0 < intval($arCatalog['OFFERS_IBLOCK_ID'])))
								{
									$boolFlag = $obCatalog->UnLinkSKUIBlock($ID);
									if (!$boolFlag)
									{
										$bVarsFromForm = true;
										$ex = $APPLICATION->GetException();
										if ($ex)
										{
											$strWarning .= $ex->GetString() . '<br>';
										}
									}
								}
							}
						}
					}
				}

				if (!$boolFlag)
				{
					$ex = $APPLICATION->GetException();
					if ($ex)
					{
						$strWarning .= $ex->GetString() . '<br>';
						$bVarsFromForm = true;
					}
				}
			}

			if (!$bVarsFromForm)
			{
				CIBlockSectionPropertyLink::CleanIBlockLinks($ID);
			}

			if(!$bVarsFromForm)
			{
				if(
					$bBizproc
					&& $_REQUEST['BIZ_PROC_ADD_DEFAULT_TEMPLATES']=='Y'
					&& CBPDocument::GetNumberOfWorkflowTemplatesForDocumentType(["iblock", "CIBlockDocument", "iblock_".$ID])<=0
					&& $arFields["BIZPROC"] == "Y"
				)
					CBPDocument::AddDefaultWorkflowTemplates(["iblock", "CIBlockDocument", "iblock_".$ID]);

				$DB->Commit();

				//Check if index needed
				CIBlock::CheckForIndexes($ID);

				if ($bCatalog)
				{
					if ($boolNeedAgent)
					{
						$intYandexExport = CCatalog::GetList([], ['YANDEX_EXPORT' => 'Y'], []);
						CAgent::RemoveAgent("CCatalog::PreGenerateXML(\"yandex\");", "catalog");
						if (0 < $intYandexExport)
							CAgent::AddAgent("CCatalog::PreGenerateXML(\"yandex\");", "catalog", "N", intval(COption::GetOptionString("catalog", "yandex_xml_period", "24"))*60*60, "", "Y");
					}
				}

				$reloadUrl =
					'/bitrix/admin/iblock_edit.php?type=' . $type
					. '&tabControl_active_tab=' . urlencode((string)$request->get('tabControl_active_tab'))
					. '&lang=' . LANGUAGE_ID
					. '&ID=' . $ID
					. '&admin=' . urlencode($isAdminUrl)
					. ($returnUrl !== '' ? '&return_url=' . urlencode($returnUrl) : '')
				;
				if ($adminSidePanelHelper->isAjaxRequest())
				{
					$reloadUrl .= '&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER';
					$adminSidePanelHelper->sendSuccessResponse(
						'apply',
						[
							'ID' => $ID,
							'reloadUrl' => $reloadUrl,
						]
					);
				}
				else
				{
					$ob = new CAutoSave();
					if ($request->getPost('apply') === null)
					{
						if ($returnUrl !== '')
						{
							LocalRedirect($returnUrl);
						}
						else
						{
							LocalRedirect(
								'/bitrix/admin/iblock_admin.php?type=' . urlencode($type)
								. '&lang=' . LANGUAGE_ID
								. '&admin=' . urlencode($isAdminUrl)
							);
						}
					}
					LocalRedirect($reloadUrl);
				}
			}
		}
	}

	$DB->Rollback();
}

if (
	check_bitrix_sessid()
	&& $request->getRequestMethod() === 'GET'
	&& intval($_REQUEST["delete_bizproc_template"]) > 0
	&& $bBizproc
	&& CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit")
)
{
	$arErrorTmp = [];
	CBPDocument::DeleteWorkflowTemplate($_REQUEST["delete_bizproc_template"], ["iblock", "CIBlockDocument", "iblock_".$ID], $arErrorTmp);
	if (!empty($arErrorTmp))
	{
		foreach ($arErrorTmp as $e)
			$strWarning .= $e["message"]."<br />";
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPageParam("", ["delete_bizproc_template", "sessid"]));
	}
}

if ($adminSidePanelHelper->isAjaxRequest() && $bVarsFromForm && $strWarning)
{
	$adminSidePanelHelper->sendJsonErrorResponse($strWarning);
}


if($ID>0)
	$APPLICATION->SetTitle(GetMessage("IB_E_EDIT_TITLE", ["#IBLOCK_TYPE#"=>$arIBTYPE["NAME"]]));
else
	$APPLICATION->SetTitle(GetMessage("IB_E_NEW_TITLE", ["#IBLOCK_TYPE#"=>$arIBTYPE["NAME"]]));


ClearVars("str_");
$str_LID = [];
$str_ACTIVE="Y";
$str_CODE = '';
$str_API_CODE = '';
$str_REST_ON = '';
$str_NAME = '';
$str_WORKFLOW="N";
$str_BIZPROC="N";
$str_SECTION_CHOOSER="L";
$str_LIST_MODE="";
$str_INDEX_ELEMENT="Y";
$str_INDEX_SECTION="Y";
$str_PROPERTY_FILE_TYPE = "jpg, gif, bmp, png, jpeg, webp";
$str_LIST_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/index.php?ID=#IBLOCK_ID#";
$str_SECTION_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/list.php?SECTION_ID=#SECTION_ID#";
$str_DETAIL_PAGE_URL="#SITE_DIR#/".$arIBTYPE["ID"]."/detail.php?ID=#ELEMENT_ID#";
$str_CANONICAL_PAGE_URL="";
$str_SORT="500";
$str_VERSION="1";
$str_RSS_ACTIVE="N";
$str_RSS_TTL="24";
$str_RSS_FILE_ACTIVE="N";
$str_RSS_FILE_LIMIT="10";
$str_RSS_FILE_DAYS="7";
$str_RSS_YANDEX_ACTIVE="N";
$str_PICTURE = '';
$str_DESCRIPTION = '';
$str_DESCRIPTION_TYPE = '';

$str_RIGHTS_MODE = Iblock\IblockTable::RIGHTS_SIMPLE;

$str_IS_CATALOG = $defaultCatalogValues['IS_CATALOG'];
$str_SUBSCRIPTION = $defaultCatalogValues['SUBSCRIPTION'];
$str_YANDEX_EXPORT = $defaultCatalogValues['YANDEX_EXPORT'];
$str_VAT_ID = $defaultCatalogValues['VAT_ID'];
$str_USED_SKU = 'N';
$str_CATALOG_TYPE = '';

$str_OF_IBLOCK_ID = 0;
$str_OF_IBLOCK_NAME = '';
$str_OF_IBLOCK_TYPE_ID = '';
$str_OF_CREATE_IBLOCK_TYPE_ID = 'N';
$str_OF_NEW_IBLOCK_TYPE_ID = '';
$int_OFFERS_PROPERTY_COUNT = PROPERTY_EMPTY_ROW_SIZE;

$str_PRODUCT_IBLOCK_ID = 0;
$str_PRODUCT_IBLOCK_TYPE_ID = '';
$str_PRODUCT_IBLOCK_NAME = '';
$str_SKU_PROPERTY_ID = 0;

$str_IPROPERTY_TEMPLATES = [];

$str_SKU_RIGHTS = 'N';

$str_EDIT_FILE_BEFORE = '';
$str_EDIT_FILE_AFTER = '';

$boolRecurringError = false;

$bCurrentBPDisabled = true;

$ib_result = CIBlock::GetList([], ["=ID" => $ID, "CHECK_PERMISSIONS"=>"N"]);
if(!$ib_result->ExtractFields("str_"))
{
	$ID = 0;
}
else
{
	$bCurrentBPDisabled = ($str_BIZPROC!='Y');

	$str_LID = [];
	$db_LID = CIBlock::GetSite($ID);
	while($ar_LID = $db_LID->Fetch())
	{
		$str_LID[] = $ar_LID["LID"];
	}
	unset($ar_LID, $db_LID);

	$ipropTemlates = new \Bitrix\Iblock\InheritedProperty\IblockTemplates($ID);
	$str_IPROPERTY_TEMPLATES = $ipropTemlates->findTemplates();
	$str_IPROPERTY_TEMPLATES["SECTION_PICTURE_FILE_NAME"] = Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["SECTION_PICTURE_FILE_NAME"] ?? null);
	$str_IPROPERTY_TEMPLATES["SECTION_DETAIL_PICTURE_FILE_NAME"] = Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["SECTION_DETAIL_PICTURE_FILE_NAME"] ?? null);
	$str_IPROPERTY_TEMPLATES["ELEMENT_PREVIEW_PICTURE_FILE_NAME"] = Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["ELEMENT_PREVIEW_PICTURE_FILE_NAME"] ?? null);
	$str_IPROPERTY_TEMPLATES["ELEMENT_DETAIL_PICTURE_FILE_NAME"] = Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["ELEMENT_DETAIL_PICTURE_FILE_NAME"] ?? null);

	if ($bCatalog)
	{
		$arCatalog = CCatalog::GetByIDExt($ID);
		if (false !== $arCatalog)
		{
			$str_IS_CATALOG = $arCatalog['CATALOG'];
			$str_CATALOG_TYPE = $arCatalog['CATALOG_TYPE'];
			if ('Y' == $arCatalog['CATALOG'])
			{
				$str_SUBSCRIPTION = $arCatalog['SUBSCRIPTION'];
				if (!CBXFeatures::IsFeatureEnabled('SaleRecurring') && 'Y' == $str_SUBSCRIPTION)
				{
					$str_SUBSCRIPTION = 'N';
					$boolRecurringError = true;
					$strWarning .= GetMessage('IB_E_CAT_SUBSCRIPTION').'<br />';
				}
				$str_YANDEX_EXPORT = $arCatalog['YANDEX_EXPORT'];
				$str_VAT_ID = $arCatalog['VAT_ID'];
				$str_PRODUCT_IBLOCK_ID = $arCatalog['PRODUCT_IBLOCK_ID'];
				$str_SKU_PROPERTY_ID = $arCatalog['SKU_PROPERTY_ID'];
			}
			if (in_array($arCatalog['CATALOG_TYPE'], ['P','X']))
			{
				$str_USED_SKU = 'Y';
				$str_OF_IBLOCK_ID = $arCatalog['OFFERS_IBLOCK_ID'];
				$str_OF_IBLOCK_NAME = CIBlock::GetArrayByID($arCatalog['OFFERS_IBLOCK_ID'],'NAME');
				$str_OF_IBLOCK_TYPE_ID = CIBlock::GetArrayByID($arCatalog['OFFERS_IBLOCK_ID'],'IBLOCK_TYPE_ID');
			}
			if (0 < $str_PRODUCT_IBLOCK_ID)
			{
				$str_PRODUCT_IBLOCK_TYPE_ID = CIBlock::GetArrayByID($str_PRODUCT_IBLOCK_ID,'IBLOCK_TYPE_ID');
				$str_PRODUCT_IBLOCK_NAME = CIBlock::GetArrayByID($str_PRODUCT_IBLOCK_ID,'NAME');
			}
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($bVarsFromForm)
{
	$ACTIVE = ($ACTIVE != "Y"? "N":"Y");
	$WORKFLOW = $WF_TYPE == "WF"? "Y": "N";
	$BIZPROC = $WF_TYPE == "BP"? "Y": "N";
	$RSS_FILE_ACTIVE = ($RSS_FILE_ACTIVE != "Y"? "N":"Y");
	$RSS_YANDEX_ACTIVE = ($RSS_YANDEX_ACTIVE != "Y"? "N":"Y");
	$RSS_ACTIVE = ($RSS_ACTIVE != "Y"? "N":"Y");
	$VERSION = ($VERSION != 2? 1:2);
	unset($PICTURE);
	$DB->InitTableVarsForEdit("b_iblock", "", "str_");
	$str_LID = $LID;
	$str_IPROPERTY_TEMPLATES = $_POST["IPROPERTY_TEMPLATES"];

	if ($bCatalog)
	{
		$DB->InitTableVarsForEdit("b_catalog_iblock", "", "str_");
		if (isset($USED_SKU) && ('Y' == $USED_SKU || 'N' == $USED_SKU))
			$str_USED_SKU = ('Y' != $USED_SKU ? "N" : "Y");
		if (isset($IS_CATALOG) && ('Y' == $IS_CATALOG || 'N' == $IS_CATALOG))
			$str_IS_CATALOG = ('Y' == $IS_CATALOG ? "Y" : "N");
		$str_CATALOG_TYPE = $CATALOG_TYPE;

		$str_OF_IBLOCK_ID = intval($OF_IBLOCK_ID);
		$str_OF_IBLOCK_NAME = $OF_IBLOCK_NAME;
		$str_OF_IBLOCK_TYPE_ID = $OF_IBLOCK_TYPE_ID;
		$str_OF_CREATE_IBLOCK_TYPE_ID = ('Y' != $OF_CREATE_IBLOCK_TYPE_ID ? 'N' : 'Y');
		$str_OF_NEW_IBLOCK_TYPE_ID = $OF_NEW_IBLOCK_TYPE_ID;

		$int_OFFERS_PROPERTY_COUNT = intval($OFFERS_PROPERTY_COUNT);
		if (0 >= $int_OFFERS_PROPERTY_COUNT)
			$int_OFFERS_PROPERTY_COUNT = PROPERTY_EMPTY_ROW_SIZE;

		//$str_SKU_RIGHTS = ('Y' == $SKU_RIGHTS ? 'Y' : 'N');
		$str_SKU_RIGHTS = 'N';
	}
}

if(CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit")):
	$aMenu = [
		[
			"TEXT" => GetMessage("IBLOCK_BACK_TO_ADMIN"),
			"LINK" => '/bitrix/admin/iblock_admin.php?lang=' . LANGUAGE_ID
				. '&type=' . urlencode($type)
				. '&admin=' . urlencode($isAdminUrl)
			,
			"ICON" => "btn_list",
		],
	];

$context = new CAdminContextMenu($aMenu);
$context->Show();

$u = new CAdminPopupEx(
	"mnu_LIST_PAGE_URL",
	CIBlockParameters::GetPathTemplateMenuItems("LIST", "__SetUrlVar", "mnu_LIST_PAGE_URL", "LIST_PAGE_URL"),
	["zIndex" => 2000]
);
$u->Show();

$u = new CAdminPopupEx(
	"mnu_SECTION_PAGE_URL",
	CIBlockParameters::GetPathTemplateMenuItems("SECTION", "__SetUrlVar", "mnu_SECTION_PAGE_URL", "SECTION_PAGE_URL"),
	["zIndex" => 2000]
);
$u->Show();

$arItems = CIBlockParameters::GetPathTemplateMenuItems("DETAIL", "__SetUrlVar", "mnu_DETAIL_PAGE_URL", "DETAIL_PAGE_URL");
if($str_CATALOG_TYPE == 'O')
{
	$arItems[] = ["SEPARATOR" => true];
	$arItems[] = [
		"TEXT" => GetMessage("IB_E_URL_PRODUCT_ID"),
		"TITLE" => "#PRODUCT_URL# - ".GetMessage("IB_E_URL_PRODUCT_ID"),
		"ONCLICK" => "__SetUrlVar('#PRODUCT_URL#', 'mnu_DETAIL_PAGE_URL', 'DETAIL_PAGE_URL')",
	];
}
$u = new CAdminPopupEx(
	"mnu_DETAIL_PAGE_URL",
	$arItems,
	["zIndex" => 2000]
);
$u->Show();

$arItems = CIBlockParameters::GetPathTemplateMenuItems("DETAIL", "__SetUrlVar", "mnu_CANONICAL_PAGE_URL", "CANONICAL_PAGE_URL");
array_unshift($arItems, ["SEPARATOR" => true]);
array_unshift($arItems, [
	"TEXT" => "https://",
	"TITLE" => "",
	"ONCLICK" => "__SetUrlVar('https://', 'mnu_CANONICAL_PAGE_URL', 'CANONICAL_PAGE_URL')",
]);
array_unshift($arItems, [
	"TEXT" => "http://",
	"TITLE" => "",
	"ONCLICK" => "__SetUrlVar('http://', 'mnu_CANONICAL_PAGE_URL', 'CANONICAL_PAGE_URL')",
]);
$u = new CAdminPopupEx(
	"mnu_CANONICAL_PAGE_URL",
	$arItems,
	["zIndex" => 2000]
);
$u->Show();
?>
<script>
	var InheritedPropertiesTemplates = new JCInheritedPropertiesTemplates(
		'frm',
		'iblock_templates.ajax.php?ENTITY_TYPE=B&ENTITY_ID=<?= (int)$ID ?>&bxpublic=y'
	);
	BX.ready(function(){
		setTimeout(function(){
			InheritedPropertiesTemplates.updateInheritedPropertiesTemplates(true);
		}, 1000);
	});
	function __SetUrlVar(id, mnu_id, el_id)
	{
		var obj_ta = BX(el_id);
		//IE
		if (document.selection)
		{
			obj_ta.focus();
			var sel = document.selection.createRange();
			sel.text = id;
			//var range = obj_ta.createTextRange();
			//range.move('character', caretPos);
			//range.select();
		}
		//FF
		else if (obj_ta.selectionStart || obj_ta.selectionStart == '0')
		{
			var startPos = obj_ta.selectionStart;
			var endPos = obj_ta.selectionEnd;
			var caretPos = startPos + id.length;
			obj_ta.value = obj_ta.value.substring(0, startPos) + id + obj_ta.value.substring(endPos, obj_ta.value.length);
			obj_ta.setSelectionRange(caretPos, caretPos);
			obj_ta.focus();
		}
		else
		{
			obj_ta.value += id;
			obj_ta.focus();
		}

		BX.fireEvent(obj_ta, 'change');
		obj_ta.focus();
	}
</script>
<script>
var CellTPL = [],
	CellAttr = [];
<?php
foreach ($arCellTemplates as $key => $value)
{
	?>CellTPL[<?= $key; ?>] = '<?= $value; ?>';
<?php
}
foreach ($arCellAttr as $key => $value)
{
	?>CellAttr[<?= $key; ?>] = '<?= $value; ?>';
<?php
}
?>
</script>

<form method="POST" name="frm" id="frm" action="/bitrix/admin/iblock_edit.php?type=<?= htmlspecialcharsbx($type) ?>&amp;lang=<?= LANGUAGE_ID ?>&amp;admin=<?= htmlspecialcharsbx($isAdminUrl)?>"  ENCTYPE="multipart/form-data">
<?=bitrix_sessid_post()?>
<?= GetFilterHiddens("find_") ?>
<?php
if($bBizproc && $bCurrentBPDisabled):
	?>
	<input type="hidden" name="BIZ_PROC_ADD_DEFAULT_TEMPLATES" value="Y">
	<?php
endif;
?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?= $ID ?>">
<?php
if ($returnUrl !== ''):?>
	<input type="hidden" name="return_url" value="<?=htmlspecialcharsbx($returnUrl); ?>">
<?php
endif;

CAdminMessage::ShowOldStyleError($strWarning);

$bTab3 = ($arIBTYPE["IN_RSS"]=="Y");
$bWorkflow = Loader::includeModule("workflow");
$bBizprocTab = $bBizproc && $str_BIZPROC == "Y";

$aTabs = [
	[
		"DIV" => "edit1",
		"TAB" => GetMessage("IB_E_TAB2"),
		"ICON" => "iblock",
		"TITLE" => GetMessage("IB_E_TAB2_T"),
	],
	[
		"DIV" => "edit10",
		"TAB" => GetMessage("IB_E_TAB10"),
		"ICON" => "iblock_iprops",
		"TITLE" => GetMessage("IB_E_TAB10_T"),
	],
	[
		"DIV" => "edit6",
		"TAB" => GetMessage("IB_E_TAB6"),
		"ICON" => "iblock_fields",
		"TITLE" => GetMessage("IB_E_TAB6_T"),
	],
	[
		"DIV" => "edit2",
		"TAB" => GetMessage("IB_E_TAB3"),
		"ICON" => "iblock_props",
		"TITLE" => GetMessage("IB_E_TAB3_T"),
	],
	[
		"DIV" => "edit8",
		"TAB" => GetMessage("IB_E_TAB8"),
		"ICON" => "section_fields",
		"TITLE" => GetMessage("IB_E_TAB8_T"),
	],
];
if($bTab3)
{
	$aTabs[] = [
		"DIV" => "edit3",
		"TAB" => GetMessage("IB_E_TAB7"),
		"ICON" => "iblock_rss",
		"TITLE" => GetMessage("IB_E_TAB7_T"),
	];
}
if($bCatalog)
{
	$aTabs[] = [
		"DIV" => "edit9",
		"TAB" => GetMessage("IB_E_TAB9"),
		"ICON" => "iblock",
		"TITLE" => GetMessage("IB_E_TAB9_T"),
	];
}
if(CIBlockRights::UserHasRightTo($ID, $ID, "iblock_rights_edit"))
{
	$aTabs[] = [
		"DIV" => "edit4",
		"TAB" => GetMessage("IB_E_TAB4"),
		"ICON"=>"iblock_access",
		"TITLE"=>GetMessage("IB_E_TAB4_T"),
	];
}
$aTabs[] = [
	"DIV" => "edit5",
	"TAB" => GetMessage("IB_E_TAB5"),
	"ICON" => "iblock",
	"TITLE" => GetMessage("IB_E_TAB5_T"),
];
if ($bBizprocTab)
{
	$aTabs[] = [
		"DIV" => "edit7",
		"TAB" => GetMessage("IB_E_TAB7_BP"),
		"ICON" => "iblock",
		"TITLE" => GetMessage("IB_E_TAB7_BP"),
	];
}
$aTabs[] = [
	"DIV" => "log",
	"TAB" => GetMessage("IB_E_TAB_LOG"),
	"ICON" => "iblock",
	"TITLE" => GetMessage("IB_E_TAB_LOG_TITLE"),
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
	if ($ID > 0):
		?>
		<tr>
			<td width="40%"><?= GetMessage("IB_E_ID")?>:</td>
			<td width="60%"><?= $str_ID ?></td>
		</tr>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><?= GetMessage("IB_E_PROPERTY_STORAGE")?></td>
			<td width="60%">
				<input type="hidden" name="VERSION" value="<?= $str_VERSION ?>">
				<?php
				if ($str_VERSION==1)
				{
					echo GetMessage("IB_E_COMMON_STORAGE");
				}
				if ($str_VERSION==2)
				{
					echo GetMessage("IB_E_SEPARATE_STORAGE");
				}
				?>
				<br><a href="/bitrix/admin/iblock_convert.php?lang=<?= LANGUAGE_ID ?>&amp;IBLOCK_ID=<?= $str_ID ?>"><?=$str_LAST_CONV_ELEMENT>0?"<span class=\"required\">".GetMessage("IB_E_CONVERT_CONTINUE"):GetMessage("IB_E_CONVERT_START")."</span>"?></a>
			</td>
		</tr>
		<tr>
			<td ><?= GetMessage("IB_E_LAST_UPDATE")?></td>
			<td><?= $str_TIMESTAMP_X ?></td>
		</tr>
		<?php
	else:
		?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><?= GetMessage("IB_E_PROPERTY_STORAGE")?></td>
			<td width="60%">
				<label><input type="radio" name="VERSION" value="1"<?= ($str_VERSION==1 ? ' checked' : '') ?>><?= GetMessage("IB_E_COMMON_STORAGE")?></label><br>
				<label><input type="radio" name="VERSION" value="2" <?= ($str_VERSION==2 ? ' checked' : '') ?>><?= GetMessage("IB_E_SEPARATE_STORAGE")?></label>
			</td>
		</tr>
		<?php
	endif;
	?>
	<tr>
		<td><label for="ACTIVE"><?= GetMessage("IB_E_ACTIVE")?>:</label></td>
		<td>
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" id="ACTIVE" name="ACTIVE" value="Y"<?= ($str_ACTIVE=="Y" ? ' checked' : '') ?>>
			<span style="display:none;"><input type="submit" name="save" value="Y" style="width:0px;height:0px"></span>
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("IB_E_CODE")?>:</td>
		<td width="60%">
			<input type="text" name="CODE" size="50" maxlength="50" value="<?= $str_CODE ?>" >
		</td>
	</tr>
	<tr>
		<td width="40%"><?= GetMessage("IB_E_API_CODE")?>:</td>
		<td width="60%">
			<input type="text" name="API_CODE" size="50" maxlength="50" value="<?= $str_API_CODE ?>" >
		</td>
	</tr>
	<tr>
		<td><label for="REST_ON"><?= GetMessage("IB_E_REST_ON")?></label></td>
		<td>
			<input type="hidden" name="REST_ON" value="N">
			<input type="checkbox" id="REST_ON" name="REST_ON" value="Y"<?= ($str_REST_ON == "Y" ? ' checked' : '') ?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?= GetMessage("IB_E_SITES")?></td>
		<td>
			<?php
		if ('O' == $str_CATALOG_TYPE)
		{
			?><div class="adm-list"><?php
			$l = CLang::GetList();
			$arLidValue = $str_LID;
			if (!is_array($arLidValue))
			{
				$arLidValue = [$arLidValue];
			}
			while($l_arr = $l->Fetch())
			{
				?><div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="LID_SHOW[]" value="<?= htmlspecialcharsex($l_arr["LID"]); ?>" id="<?= htmlspecialcharsex($l_arr["LID"]);?>" class="typecheckbox"<?= (in_array($l_arr["LID"], $arLidValue) ? ' checked' : '' ) ?> disabled></div>
					<div class="adm-list-label"><label for="<?= htmlspecialcharsex($l_arr["LID"]); ?>">[<?= htmlspecialcharsex($l_arr["LID"]); ?>]&nbsp;<?= htmlspecialcharsex($l_arr["NAME"]); ?></label></div>
				</div><?php
			}
			echo "<br>".str_replace('#LINK#','/bitrix/admin/iblock_edit.php?type='.$str_PRODUCT_IBLOCK_TYPE_ID.'&lang='.LANGUAGE_ID.'&ID='.$str_PRODUCT_IBLOCK_ID.'&admin=Y',GetMessage('IB_E_OF_SITES'));

			foreach ($arLidValue as &$strLid)
			{
				?><input type="hidden" name="LID[]" value="<?= htmlspecialcharsex($strLid); ?>"><?php
			}
			?></div><?php
		}
		else
		{
			?><?=CLang::SelectBoxMulti("LID", $str_LID);?><?php
		}
		?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td ><?= GetMessage("IB_E_NAME")?>:</td>
		<td>
			<input type="text" name="NAME" size="55" maxlength="255" value="<?= $str_NAME ?>">
		</td>
	</tr>
	<tr>
		<td ><?= GetMessage("IB_E_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" size="10" maxlength="10" value="<?= $str_SORT ?>">
		</td>
	</tr>
	<?php
	if(COption::GetOptionString("iblock", "show_xml_id") == "Y"):
		?>
		<tr>
			<td ><?= GetMessage("IB_E_XML_ID")?>:</td>
			<td>
				<input type="text" name="XML_ID"  size="55" maxlength="255" value="<?= $str_XML_ID ?>">
			</td>
		</tr>
		<?php
	endif;
	?>
	<tr>
		<td ><?= GetMessage("IB_E_LIST_PAGE_URL")?></td>
		<td>
			<input type="text" name="LIST_PAGE_URL" id="LIST_PAGE_URL" size="55" maxlength="255" value="<?= $str_LIST_PAGE_URL ?>">
			<input type="button" id="mnu_LIST_PAGE_URL" value='...'>
		</td>
	</tr>
	<?php
	if($arIBTYPE["SECTIONS"]=="Y"):
		?>
		<tr>
			<td ><?= GetMessage("IB_E_SECTION_PAGE_URL")?></td>
			<td>
				<input type="text" name="SECTION_PAGE_URL" id="SECTION_PAGE_URL" size="55" maxlength="255" value="<?= $str_SECTION_PAGE_URL ?>">
				<input type="button" id="mnu_SECTION_PAGE_URL" value='...'>
			</td>
		</tr>
		<?php
	endif;
	?>
	<tr>
		<td ><?= GetMessage("IB_E_DETAIL_PAGE_URL")?></td>
		<td>
			<input type="text" name="DETAIL_PAGE_URL" id="DETAIL_PAGE_URL" size="55" maxlength="255" value="<?= $str_DETAIL_PAGE_URL ?>">
			<input type="button" id="mnu_DETAIL_PAGE_URL" value='...'>
		</td>
	</tr>
	<tr>
		<td ><?= GetMessage("IB_E_CANONICAL_PAGE_URL")?></td>
		<td>
			<input type="text" name="CANONICAL_PAGE_URL" id="CANONICAL_PAGE_URL" size="55" maxlength="255" value="<?= $str_CANONICAL_PAGE_URL ?>">
			<input type="button" id="mnu_CANONICAL_PAGE_URL" value='...'>
		</td>
	</tr>
	<?php
	if($arIBTYPE["SECTIONS"]=="Y"):
		?>
		<tr>
			<td><label for="INDEX_SECTION"><?= GetMessage("IB_E_INDEX_SECTION")?></label></td>
			<td>
				<input type="hidden" name="INDEX_SECTION" value="N">
				<input type="checkbox" id="INDEX_SECTION" name="INDEX_SECTION" value="Y"<?= ($str_INDEX_SECTION=="Y" ? ' checked' : '') ?>>
			</td>
		</tr>
		<?php
	endif;
	?>
	<tr>
		<td><label for="INDEX_ELEMENT"><?= GetMessage("IB_E_INDEX_ELEMENT")?></label></td>
		<td>
			<input type="hidden" name="INDEX_ELEMENT" value="N">
			<input type="checkbox" id="INDEX_ELEMENT" name="INDEX_ELEMENT" value="Y"<?= ($str_INDEX_ELEMENT=="Y" ? ' checked' : '') ?>>
		</td>
	</tr>
	<?php
	if($bWorkflow && $bBizproc):
		?>
		<tr>
			<td><?= GetMessage("IB_E_WF_TYPE")?></td>
			<td>
				<select name="WF_TYPE">
					<option value="N"<?= ($str_WORKFLOW != "Y" && $str_BIZPROC !="Y" ? ' selected' : '') ?>><?= GetMessage("IB_E_WF_TYPE_NONE")?></option>
					<option value="WF"<?= ($str_WORKFLOW=="Y" ? ' selected' : '') ?>><?= GetMessage("IB_E_WF_TYPE_WORKFLOW")?></option>
					<option value="BP"<?= ($str_BIZPROC=="Y" ? ' selected' : '') ?>><?= GetMessage("IB_E_WF_TYPE_BIZPROC")?></option>
				</select>
			</td>
		</tr>
		<?php
	elseif ($bWorkflow && !$bBizproc):
		?>
		<tr>
			<td><label for="WF_TYPE"><?= GetMessage("IB_E_WORKFLOW")?></label></td>
			<td>
				<input type="hidden" name="WF_TYPE" value="N">
				<input type="checkbox" id="WF_TYPE" name="WF_TYPE" value="WF"<?= ($str_WORKFLOW=="Y" ? ' checked' : '') ?>>
			</td>
		</tr>
		<?php
	elseif ($bBizproc && !$bWorkflow):
		?>
		<tr>
			<td><label for="WF_TYPE"><?= GetMessage("IB_E_BIZPROC")?></label></td>
			<td>
				<input type="hidden" name="WF_TYPE" value="N">
				<input type="checkbox" id="WF_TYPE" name="WF_TYPE" value="BP"<?= ($str_BIZPROC=="Y" ? ' checked' : '') ?>>
			</td>
		</tr>
		<?php
	endif;
	?>
	<tr>
		<td><?= GetMessage("IB_E_SECTION_CHOOSER")?>:</td>
		<td>
			<select name="SECTION_CHOOSER">
			<option value="L"<?= ($str_SECTION_CHOOSER=="L" ? ' selected' : '') ?>><?= GetMessage("IB_E_SECTION_CHOOSER_LIST")?></option>
			<option value="D"<?= ($str_SECTION_CHOOSER=="D" ? ' selected' : '') ?>><?= GetMessage("IB_E_SECTION_CHOOSER_DROPDOWNS")?></option>
			<option value="P"<?= ($str_SECTION_CHOOSER=="P" ? ' selected' : '') ?>><?= GetMessage("IB_E_SECTION_CHOOSER_POPUP")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_LIST_MODE")?>:</td>
		<td>
			<select name="LIST_MODE">
			<option value=""><?= GetMessage("IB_E_LIST_MODE_GLOBAL")?></option>
			<option value="S"<?= ($str_LIST_MODE=="S" ? ' selected' : '') ?>><?= GetMessage("IB_E_LIST_MODE_SECTIONS")?></option>
			<option value="C"<?= ($str_LIST_MODE=="C" ? ' selected' : '') ?>><?= GetMessage("IB_E_LIST_MODE_COMBINED")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
		<?php
		CAdminFileDialog::ShowScript([
			"event" => "BtnClick",
			"arResultDest" => [
				"FORM_NAME" => "frm",
				"FORM_ELEMENT_NAME" => "EDIT_FILE_BEFORE",
			],
			"arPath" => [
				"PATH" => GetDirPath($str_EDIT_FILE_BEFORE),
			],
			"select" => 'F',// F - file only, D - folder only
			"operation" => 'O',// O - open, S - save
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'php',
			"allowAllFiles" => true,
			"SaveConfig" => true,
		]);
		?>
		<?= GetMessage("IB_E_FILE_BEFORE")?></td>
		<td><input type="text" name="EDIT_FILE_BEFORE" size="55"  maxlength="255" value="<?= $str_EDIT_FILE_BEFORE ?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick()"></td>
	</tr>
	<tr>
		<td>
		<?php
		CAdminFileDialog::ShowScript([
			"event" => "BtnClick2",
			"arResultDest" => [
				"FORM_NAME" => "frm",
				"FORM_ELEMENT_NAME" => "EDIT_FILE_AFTER",
			],
			"arPath" => [
				"PATH" => GetDirPath($str_EDIT_FILE_AFTER),
			],
			"select" => 'F',// F - file only, D - folder only
			"operation" => 'O',// O - open, S - save
			"showUploadTab" => true,
			"showAddToMenuTab" => false,
			"fileFilter" => 'php',
			"allowAllFiles" => true,
			"SaveConfig" => true,
		]);
		?>
		<?= GetMessage("IB_E_FILE_AFTER")?></td>
		<td><input type="text" name="EDIT_FILE_AFTER" size="55"  maxlength="255" value="<?= $str_EDIT_FILE_AFTER ?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick2()"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("IB_E_DESCRIPTION")?></td>
	</tr>
	<tr class="adm-detail-file-row">
		<td class="adm-detail-valign-top"><?= GetMessage("IB_E_PICTURE")?></td>
		<td>
			<?= CFileInput::Show(
				'PICTURE',
				$str_PICTURE,
				[
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
					"MAX_SIZE" => [
						"W" => COption::GetOptionString("iblock", "detail_image_size"),
						"H" => COption::GetOptionString("iblock", "detail_image_size"),
					],
				],
				[
					'upload' => true,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => true,
					'description' => false,
				]
			);?>
		</td>
	</tr>
	<?php
	if(COption::GetOptionString("iblock", "use_htmledit") === "Y" && Loader::includeModule("fileman")):?>
		<tr>
			<td colspan="2" align="center">
				<?php
				CFileMan::AddHTMLEditorFrame(
					"DESCRIPTION",
					$str_DESCRIPTION,
					"DESCRIPTION_TYPE",
					$str_DESCRIPTION_TYPE,
					[
						'height' => 450,
						'width' => '100%',
					]
				);
				?>
			</td>
		</tr>
		<?php
	else:
		?>
		<tr>
			<td ><?= GetMessage("IB_E_DESCRIPTION_TYPE")?></td>
			<td >
				<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE1" value="text"<?= ($str_DESCRIPTION_TYPE!="html" ? ' checked' : '') ?>><label for="DESCRIPTION_TYPE1"> <?= GetMessage("IB_E_DESCRIPTION_TYPE_TEXT")?></label> /
				<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE2" value="html"<?= ($str_DESCRIPTION_TYPE=="html" ? ' checked' : '') ?>><label for="DESCRIPTION_TYPE2"> <?= GetMessage("IB_E_DESCRIPTION_TYPE_HTML")?></label>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<textarea cols="60" rows="15" name="DESCRIPTION" style="width:100%;"><?= $str_DESCRIPTION ?></textarea>
			</td>
		</tr>
		<?php
	endif;

$tabControl->BeginNextTab();
?>
<tr class="heading">
	<td colspan="2"><?= GetMessage("IB_E_SEO_FOR_SECTIONS")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_META_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_META_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_META_KEYWORDS")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_META_DESCRIPTION")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_SECTION_PAGE_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?= GetMessage("IB_E_SEO_FOR_ELEMENTS")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_META_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_META_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_META_KEYWORDS")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_META_DESCRIPTION")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_PAGE_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?= GetMessage("IB_E_SEO_FOR_SECTIONS_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?= GetMessage("IB_E_SEO_FOR_SECTIONS_DETAIL_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "SECTION_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?= GetMessage("IB_E_SEO_FOR_ELEMENTS_PREVIEW_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_PREVIEW_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_PREVIEW_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_PREVIEW_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?= GetMessage("IB_E_SEO_FOR_ELEMENTS_DETAIL_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?= GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?= IBlockInheritedPropertyInput($ID, "ELEMENT_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?= GetMessage("IB_E_SEO_MANAGEMENT")?></td>
</tr>
<tr>
	<td width="40%"><label for="IPROPERTY_CLEAR_VALUES"><?= GetMessage("IB_E_SEO_CLEAR_VALUES")?></label></td>
	<td width="60%">
		<input type="checkbox" id="IPROPERTY_CLEAR_VALUES" name="IPROPERTY_CLEAR_VALUES" value="Y" />
	</td>
</tr>
<?php
$tabControl->BeginNextTab();
?>
	<tr><td colspan="2"><table border="0" cellspacing="0" cellpadding="0" class="internal" style="width:690px; margin: 0 auto;">
		<tr class="heading">
			<td width="125" style="text-align: left !important;"><?= GetMessage("IB_E_FIELD_NAME")?></td>
			<td width="40"><?= GetMessage("IB_E_FIELD_IS_REQUIRED")?></td>
			<td width="450" style="text-align: left !important;"><?= GetMessage("IB_E_FIELD_DEFAULT_VALUE")?></td>
		</tr>
		<?php
		if($bVarsFromForm)
			$arFields = $_REQUEST["FIELDS"];
		else
			$arFields = CIBlock::GetFields($ID);
		$arDefFields = CIBlock::GetFieldsDefaults();
		foreach($arDefFields as $FIELD_ID => $arField):
			if ($arField["VISIBLE"] === "N")
			{
				continue;
			}
			if (preg_match("/^(SECTION_|LOG_)/", $FIELD_ID))
			{
				continue;
			}
			$checkboxAttrs = '';
			if ($arFields[$FIELD_ID]["IS_REQUIRED"] === "Y" || $arField["IS_REQUIRED"] !== false)
			{
				$checkboxAttrs .= ' checked';
			}
			if ($arField["IS_REQUIRED"] !==false )
			{
				$checkboxAttrs .= ' disabled';
			}
			?>
		<tr <?php
			if (
				$FIELD_ID === "PREVIEW_PICTURE"
				|| $FIELD_ID === "PREVIEW_TEXT"
				|| $FIELD_ID === "DETAIL_PICTURE"
				|| $FIELD_ID === "DETAIL_TEXT"
				|| $FIELD_ID === "CODE"
			)
				echo  'class="adm-detail-valign-top"';
		?>>
			<td><?= $arField["NAME"] ?></td>
			<td style="text-align:center">
				<input type="hidden" value="N" name="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]">
				<input type="checkbox" value="Y" name="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]"<?= $checkboxAttrs ?>>
			</td>
			<td>
			<?php
			switch($FIELD_ID)
			{
			case "IBLOCK_SECTION":
				?>
				<input type="hidden" name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]" value="N">
				<input type="checkbox"
					name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]"
					id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]"
					value="Y"
					<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["KEEP_IBLOCK_SECTION_ID"] === "Y" ? 'checked="checked"' : '') ?>
				/><label for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]">
				<?= GetMessage("IB_E_FIELD_IBLOCK_SECTION_KEEP_IBLOCK_SECTION_ID")?>
				</label>
				<?php
				break;
			case "ACTIVE":
				?>
				<select name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" height="1">
					<option value="Y"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "Y" ? ' selected' : '') ?>><?= GetMessage("MAIN_YES")?></option>
					<option value="N"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="N" ? ' selected' : '') ?>><?= GetMessage("MAIN_NO")?></option>
				</select>
				<?php
				break;
			case "ACTIVE_FROM":
				?>
				<select name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" height="1">
					<option value=""<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "" ? ' selected' : '') ?>><?= GetMessage("IB_E_FIELD_ACTIVE_FROM_EMPTY")?></option>
					<option value="=now"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "=now" ? ' selected' : '') ?>><?= GetMessage("IB_E_FIELD_ACTIVE_FROM_NOW")?></option>
					<option value="=today"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "=today" ? ' selected' : '') ?>><?= GetMessage("IB_E_FIELD_ACTIVE_FROM_TODAY")?></option>
				</select>
				<?php
				break;
			case "ACTIVE_TO":
				?>
				<label for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]"><?= GetMessage("IB_E_FIELD_ACTIVE_TO")?></label>
				<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="5">
				<?php
				break;
			case "NAME":
				?>
				<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="60">
				<?php
				break;
			case "SORT":
				?>
				<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" type="hidden" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>">
				<?php
				break;
			case "DETAIL_TEXT_TYPE":
			case "PREVIEW_TEXT_TYPE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<select name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" height="1">
						<option value="text"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "text" ? ' selected' : '') ?>>text</option>
						<option value="html"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "html" ? ' selected' : '') ?>>html</option>
					</select>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							name="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							<?php
							if($arFields[$FIELD_ID."_ALLOW_CHANGE"]["DEFAULT_VALUE"]!=="N")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						><?= GetMessage("IB_E_FIELD_TEXT_TYPE_ALLOW_CHANGE")?></label>
					</div>
				</div>
				</div>
				<?php
				break;
			case "DETAIL_TEXT":
			case "PREVIEW_TEXT":
				?>
				<textarea name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" rows="5" cols="47"><?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?></textarea>
				<?php
				break;
			case "PREVIEW_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]').style.display =
								this.checked ? 'block': 'none';
								"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
						><?= GetMessage("IB_E_FIELD_PREVIEW_PICTURE_FROM_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
					style="padding-left: 16px; display:<?php
					echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
						><?= GetMessage("IB_E_FIELD_PREVIEW_PICTURE_UPDATE_WITH_DETAIL_EXT")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
						><?= GetMessage("IB_E_FIELD_PREVIEW_PICTURE_DELETE_WITH_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?= GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value=""
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						>
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						><?= GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage(
							"IB_E_FIELD_PICTURE_COMPRESSION_EXT",
							['#DEFAULT_VALUE#' => CIBlock::getDefaultJpegQuality()]
						)?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif,webp',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);?>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?= $FIELD_ID ?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?php
					echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);
					?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?= $FIELD_ID ?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?= $FIELD_ID ?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?php
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							[
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							]
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?php
					echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?php
				break;
			case "DETAIL_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?= GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value=""
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						>
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						><?= GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage(
						"IB_E_FIELD_PICTURE_COMPRESSION_EXT",
						['#DEFAULT_VALUE#' => CIBlock::getDefaultJpegQuality()]
					)?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif,webp',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);
					?>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?= $FIELD_ID ?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?php
					echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);
					?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?= $FIELD_ID ?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?= $FIELD_ID ?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?php
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							[
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							]
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?php
					echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?php
				break;
			case "CODE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UNIQUE"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
						><?= GetMessage("IB_E_FIELD_CODE_UNIQUE")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_LEN]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_CASE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_SPACE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_OTHER]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
						><?= GetMessage("IB_E_FIELD_EL_TRANSLITERATION")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_LEN]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_LEN")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_LEN]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_LEN"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_CASE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_CASE")?>:&nbsp;<select name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_CASE]">
						<option value=""><?= GetMessage("IB_E_FIELD_TRANS_CASE_LEAVE")?>
						</option>
						<option value="L"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"] === "L" ? ' selected' : '') ?>>
							<?= GetMessage("IB_E_FIELD_TRANS_CASE_LOWER")?>
						</option>
						<option value="U"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"] === "U" ? ' selected' : '') ?>>
							<?= GetMessage("IB_E_FIELD_TRANS_CASE_UPPER")?>
						</option>
					</select>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_SPACE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_SPACE")?>&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_SPACE]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_SPACE"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_OTHER]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_OTHER")?>&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_OTHER]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_OTHER"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_EAT"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
						><?= GetMessage("IB_E_FIELD_TRANS_EAT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_GOOGLE"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
						><?= GetMessage("IB_E_FIELD_EL_TRANS_USE_SERVICE")?></label>
					</div>
				</div>
				</div>
				<?php
				break;
			default:
				?>
				<input type="hidden" value="" name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]">&nbsp;
				<?php
				break;
			}
			?>
			</td>
		</tr>
		<?php
		endforeach;
		?>
	</table> </td> </tr>
<?php
$tabControl->BeginNextTab();
?>
	<tr>
		<td>
			<script>
			var obIBProps = new JCIBlockProperty({
				'PREFIX': '<?= $strPREFIX_IB_PROPERTY ?>',
				'FORM_ID': 'frm',
				'TABLE_PROP_ID': 'ib_prop_list',
				'PROP_COUNT_ID': 'INT_IBLOCK_PROPERTY_COUNT',
				'IBLOCK_ID': <?= $ID ?>,
				'LANG': '<?= LANGUAGE_ID ?>',
				'TITLE': '<?= GetMessageJS('IB_E_IB_PROPERTY_DETAIL') ?>',
				'OBJ': 'obIBProps'
			});
			obIBProps.SetCells(CellTPL,8,CellAttr);
			</script>
			<table class="internal" style="margin: 0 auto" id="ib_prop_list">
				<tr class="heading">
					<td>ID</td>
					<td><?= GetMessage("IB_E_PROP_NAME_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_TYPE_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_ACTIVE_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_MULT_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_REQIRED_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_SORT_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_CODE_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_MODIFY_SHORT"); ?></td>
					<td><?= GetMessage("IB_E_PROP_DELETE_SHORT"); ?></td>
				</tr>
				<?php
				$arPropList = [];
				if (0 < $ID)
				{
					$arPropLinks = CIBlockSectionPropertyLink::GetArray($ID, 0);
					$rsProps =  CIBlockProperty::GetList(
						[
							"SORT" => "ASC",
							'ID' => 'ASC',
						],
						[
							"IBLOCK_ID" => $ID,
							"CHECK_PERMISSIONS" => "N",
						]
					);
					while ($arProp = $rsProps->Fetch())
					{
						if ('L' == $arProp['PROPERTY_TYPE'])
						{
							$arProp['VALUES'] = [];
							$rsLists = CIBlockProperty::GetPropertyEnum(
								$arProp['ID'],
								[
									'SORT' => 'ASC',
									'ID' => 'ASC',
								]
							);
							while($res = $rsLists->Fetch())
							{
								$arProp['VALUES'][$res["ID"]] = [
									'ID' => $res["ID"],
									'VALUE' => $res["VALUE"],
									'SORT' => $res['SORT'],
									'XML_ID' => $res["XML_ID"],
									'DEF' => $res['DEF'],
								];
							}
						}

						$arProp['FEATURES'] = [];
						$iterator = Iblock\PropertyFeatureTable::getList([
							'select' => ['ID', 'MODULE_ID', 'FEATURE_ID', 'IS_ENABLED'],
							'filter' => ['=PROPERTY_ID' => $arProp['ID']]
						]);
						while ($row = $iterator->fetch())
						{
							$index = Iblock\Model\PropertyFeature::getIndex($row);
							$arProp['FEATURES'][$index] = $row;
						}
						unset($index, $row, $iterator);

						if(array_key_exists($arProp["ID"], $arPropLinks))
						{
							$arProp["SECTION_PROPERTY"] = "Y";
							$arProp["SMART_FILTER"] = ($arPropLinks[$arProp["ID"]]["SMART_FILTER"] ?? 'N');
							$arProp["DISPLAY_TYPE"] = ($arPropLinks[$arProp["ID"]]["DISPLAY_TYPE"] ?? '');
							$arProp["DISPLAY_EXPANDED"] = ($arPropLinks[$arProp["ID"]]["DISPLAY_EXPANDED"] ?? 'N');
							$arProp["FILTER_HINT"] = ($arPropLinks[$arProp["ID"]]["FILTER_HINT"] ?? '');
						}
						else
						{
							$arProp["SECTION_PROPERTY"] = "N";
							$arProp["SMART_FILTER"] = "N";
							$arProp["DISPLAY_TYPE"] = "";
							$arProp["DISPLAY_EXPANDED"] = "N";
							$arProp["FILTER_HINT"] = "";
						}

						ConvProp($arProp,$arHiddenPropFields);

						if ($bVarsFromForm)
						{
							$intPropID = $arProp['ID'];
							$arTempo = GetPropertyInfo($strPREFIX_IB_PROPERTY, $intPropID, false, $arHiddenPropFields);
							if (is_array($arTempo))
								$arProp = $arTempo;
							$arProp['ID'] = $intPropID;
						}
						$arProp = ConvertToSafe($arProp,$arDisabledPropFields);
						$arProp['SHOW_DEL'] = 'Y';
						$arPropList[$arProp['ID']] = $arProp;
					}
				}
				$intPropCount = 0;
				if ($request->isPost())
				{
					$intPropCount = (int)$request->getPost('IBLOCK_PROPERTY_COUNT');
				}
				if ($intPropCount <= 0)
				{
					$intPropCount = PROPERTY_EMPTY_ROW_SIZE;
				}
				$intPropNumber = 0;
				for ($i = 0; $i < $intPropCount; $i++)
				{
					$arProp = GetPropertyInfo($strPREFIX_IB_PROPERTY, 'n'.$i, false, $arHiddenPropFields);
					if (is_array($arProp))
					{
						$arProp = ConvertToSafe($arProp,$arDisabledPropFields);
						$arProp['ID'] = 'n'.$intPropNumber;
						$arPropList['n'.$intPropNumber] = $arProp;
						$intPropNumber++;
					}
				}
				for ($i = 0; $intPropNumber < PROPERTY_EMPTY_ROW_SIZE; $intPropNumber++)
				{
					$arProp = $arDefPropInfo;
					ConvProp($arProp,$arHiddenPropFields);
					$arProp['ID'] = 'n'.$intPropNumber;
					$arPropList['n'.$intPropNumber] = $arProp;
				}
				foreach ($arPropList as $mxPropID => $arProp)
				{
					$arProp['IBLOCK_ID'] = $ID;
					echo __AddPropRow($mxPropID,$strPREFIX_IB_PROPERTY,$arProp);
				}
			?></table>
			<div style="width: 100%; text-align: center; margin: 10px 0;">
				<input class="adm-btn-big" onclick="obIBProps.addPropRow();" type="button" value="<?= GetMessage('IB_E_SHOW_ADD_PROP_ROW')?>" title="<?= GetMessage('IB_E_SHOW_ADD_PROP_ROW_DESCR')?>">
			</div>
			<input type="hidden" name="IBLOCK_PROPERTY_COUNT" id="INT_IBLOCK_PROPERTY_COUNT" value="<?= $intPropNumber ?>">
		</td>
	</tr>
<?php
$tabControl->BeginNextTab();
?>
	<tr><td colspan="2"><table border="0" cellspacing="0" cellpadding="0" class="internal" style="width:690px; margin: 0 auto;">
		<tr class="heading">
			<td width="125" style="text-align: left !important;"><?= GetMessage("IB_E_SECTION_FIELD_NAME")?></td>
			<td width="40"><?= GetMessage("IB_E_SECTION_FIELD_IS_REQUIRED")?></td>
			<td width="450" style="text-align: left !important;"><?= GetMessage("IB_E_SECTION_FIELD_DEFAULT_VALUE")?></td>
		</tr>
		<?php
		if($bVarsFromForm)
			$arFields = $_REQUEST["FIELDS"];
		else
			$arFields = CIBlock::GetFields($ID);
		$arDefFields = CIBlock::GetFieldsDefaults();
		foreach($arDefFields as $FIELD_ID => $arField):
			if ($arField["VISIBLE"] === "N")
			{
				continue;
			}
			if (!preg_match("/^SECTION_/", $FIELD_ID))
			{
				continue;
			}
			$checkboxAttrs = '';
			if ($arFields[$FIELD_ID]['IS_REQUIRED'] === 'Y' || $arField['IS_REQUIRED'] !== false)
			{
				$checkboxAttrs .= ' checked';
			}
			if ($arField['IS_REQUIRED'] !== false)
			{
				$checkboxAttrs .= ' disabled';
			}
			?>
		<tr <?php
			if (
				$FIELD_ID === "SECTION_DESCRIPTION"
				|| $FIELD_ID === "SECTION_PICTURE"
				|| $FIELD_ID === "SECTION_DETAIL_PICTURE"
				|| $FIELD_ID === "SECTION_CODE"
			)
				echo  'class="adm-detail-valign-top"';
		?>>
			<td><?= $arField["NAME"] ?></td>
			<td style="text-align:center">
				<input type="hidden" value="N" name="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]">
				<input type="checkbox" value="Y" name="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]"<?= $checkboxAttrs ?>>
			</td>
			<td>
			<?php
			switch($FIELD_ID)
			{
			case "SECTION_NAME":
				?>
				<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="60">
				<?php
				break;
			case "SECTION_DESCRIPTION_TYPE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<select name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" height="1">
						<option value="text"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "text" ? ' selected' : '') ?>>text</option>
						<option value="html"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"] === "html" ? ' selected' : '') ?>>html</option>
					</select>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							name="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							<?php
							if($arFields[$FIELD_ID."_ALLOW_CHANGE"]["DEFAULT_VALUE"]!=="N")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						><?= GetMessage("IB_E_FIELD_TEXT_TYPE_ALLOW_CHANGE")?></label>
					</div>
				</div>
				</div>
				<?php
				break;
			case "SECTION_DESCRIPTION":
				?>
				<textarea name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]" rows="5" cols="47"><?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?></textarea>
				<?php
				break;
			case "SECTION_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]').style.display =
								this.checked ? 'block': 'none';
								"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][FROM_DETAIL]"
						><?= GetMessage("IB_E_FIELD_PREVIEW_PICTURE_FROM_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
					style="padding-left: 16px; display:<?php
					echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y") ? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
						><?= GetMessage("IB_E_FIELD_PREVIEW_PICTURE_UPDATE_WITH_DETAIL_EXT")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
						><?= GetMessage("IB_E_FIELD_PREVIEW_PICTURE_DELETE_WITH_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?= GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value=""
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						>
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						><?= GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage(
						"IB_E_FIELD_PICTURE_COMPRESSION_EXT",
						['#DEFAULT_VALUE#' => CIBlock::getDefaultJpegQuality()]
					)?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif,webp',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);
					?>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?= $FIELD_ID ?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?php
					echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);
					?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?= $FIELD_ID ?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?= $FIELD_ID ?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?php
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							[
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							]
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?php
					echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?php
				break;
			case "SECTION_DETAIL_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][SCALE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?= GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value=""
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						>
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][METHOD]"
						><?= GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?= GetMessage(
						"IB_E_FIELD_PICTURE_COMPRESSION_EXT",
						['#DEFAULT_VALUE#' => CIBlock::getDefaultJpegQuality()]
					)?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif,webp',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);
					?>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?= $FIELD_ID ?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?php
					echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?= GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?php
					CAdminFileDialog::ShowScript([
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => [
							"ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_",
						],
						"arPath" => [
							"PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])),
						],
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					]);
					?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?= $FIELD_ID ?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?= $FIELD_ID ?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?= $FIELD_ID ?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?php
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							[
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							]
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION") ?>:&nbsp;<?php
						echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?php
				break;
			case "SECTION_CODE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UNIQUE"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][UNIQUE]"
						><?= GetMessage("IB_E_FIELD_CODE_UNIQUE")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
							<?php
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_LEN]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_CASE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_SPACE]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_OTHER]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]').style.display =
								BX('SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANSLITERATION]"
						><?= GetMessage("IB_E_FIELD_SEC_TRANSLITERATION")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_LEN]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_LEN")?>:&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_LEN]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_LEN"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_CASE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_CASE")?>:&nbsp;<select name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_CASE]">
						<option value=""><?= GetMessage("IB_E_FIELD_TRANS_CASE_LEAVE") ?>
						</option>
						<option value="L"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"] === "L" ? ' selected' : '') ?>>
							<?= GetMessage("IB_E_FIELD_TRANS_CASE_LOWER")?>
						</option>
						<option value="U"<?= ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"] === "U" ? ' selected' : '') ?>>
							<?= GetMessage("IB_E_FIELD_TRANS_CASE_UPPER")?>
						</option>
					</select>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_SPACE]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_SPACE")?>&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_SPACE]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_SPACE"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_OTHER]"
					style="padding-left:16px;display:<?php
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?= GetMessage("IB_E_FIELD_TRANS_OTHER")?>&nbsp;<input
						name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_OTHER]"
						type="text"
						value="<?= htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_OTHER"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_EAT"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][TRANS_EAT]"
						><?= GetMessage("IB_E_FIELD_TRANS_EAT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
					style="padding-left:16px;display:<?php
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]_hidden"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
							name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
							<?php
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_GOOGLE"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE][USE_GOOGLE]"
						><?= GetMessage("IB_E_FIELD_EL_TRANS_USE_SERVICE") ?></label>
					</div>
				</div>
				</div>
				<?php
				break;
			default:
				?>
				<input type="hidden" value="" name="FIELDS[<?= $FIELD_ID ?>][DEFAULT_VALUE]">&nbsp;
				<?php
				break;
			}
			?>
			</td>
		</tr>
		<?php
		endforeach;
		?>
	</table> </td> </tr>
<?php
if($bTab3):
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td  width="40%"><label for="RSS_ACTIVE"><?= GetMessage("IB_E_RSS_ACTIVE")?></label></td>
		<td width="60%">
			<input type="hidden" name="RSS_ACTIVE" value="N">
			<input type="checkbox" id="RSS_ACTIVE" name="RSS_ACTIVE" value="Y"<?= ($str_RSS_ACTIVE == "Y" ? ' checked' : '') ?>>
		</td>
	</tr>
	<tr>
		<td ><?= GetMessage("IB_E_RSS_TTL")?></td>
		<td>
			<input type="text" name="RSS_TTL" size="20"  maxlength="40" value="<?= $str_RSS_TTL ?>">
		</td>
	</tr>
	<tr>
		<td><label for="RSS_FILE_ACTIVE"><?= GetMessage("IB_E_RSS_FILE_ACTIVE")?></label></td>
		<td>
			<input type="hidden" name="RSS_FILE_ACTIVE" value="N">
			<input type="checkbox" id="RSS_FILE_ACTIVE" name="RSS_FILE_ACTIVE" value="Y"<?= ($str_RSS_FILE_ACTIVE == "Y" ? ' checked' : '') ?>>
		</td>
	</tr>
	<tr>
		<td  ><?= GetMessage("IB_E_RSS_FILE_LIMIT")?></td>
		<td  >
			<input type="text" name="RSS_FILE_LIMIT"  size="20" maxlength="40" value="<?= $str_RSS_FILE_LIMIT ?>">
		</td>
	</tr>
	<tr>
		<td ><?= GetMessage("IB_E_RSS_FILE_DAYS")?></td>
		<td>
			<input type="text" name="RSS_FILE_DAYS"  size="20" maxlength="40" value="<?= $str_RSS_FILE_DAYS ?>">
		</td>
	</tr>

	<?php
	if ($canUseYandexMarket)
	{
		?>
		<tr>
			<td><label for="RSS_YANDEX_ACTIVE"><?= GetMessage("IB_E_RSS_YANDEX_ACTIVE")?></label></td>
			<td>
				<input type="hidden" name="RSS_YANDEX_ACTIVE" value="N">
				<input type="checkbox" id="RSS_YANDEX_ACTIVE" name="RSS_YANDEX_ACTIVE" value="Y"<?= ($str_RSS_YANDEX_ACTIVE == "Y" ? ' checked' : '') ?>>
			</td>
		</tr>
		<?php
	}
	?>

	<tr class="heading">
		<td colspan="2"><?= GetMessage("IB_E_RSS_TITLE")?>:</td>
	</tr>
	<tr>
		<td  colspan="2" align="center">
			<table class="internal">
				<tr class="heading">
					<td><?= GetMessage("IB_E_RSS_FIELD")?></td>
					<td><?= GetMessage("IB_E_RSS_TEMPL")?></td>
				</tr>
				<?php
				$arCurNodesRSS = CIBlockRSS::GetNodeList(intval($ID));
				$arNodesRSS = CIBlockRSS::GetRSSNodes();
				foreach($arNodesRSS as $key => $val):
					if($bVarsFromForm)
					{
						$DB->InitTableVarsForEdit("b_iblock_rss", "RSS_", "str_RSS_", "_" . $key);
					}
					?>
					<tr>
						<td>
							<input type="text" size="20" readonly maxlength="50" name="RSS_NODE_<?= $key ?>" value="<?= $val ?>">
						</td>
						<td><input type="text" size="20" name="RSS_NODE_VALUE_<?= $key ?>" value="<?= ($arCurNodesRSS[$val] ?? '') ?>"></td>
					</tr>
				<?php
				endforeach;
				?>
			</table>
		</td>
	</tr>
	<?php
endif;
if ($bCatalog)
{
	$arIBlockTypeIDList = [];
	$arIBlockTypeNameList = [];
	$rsIBlockTypes = CIBlockType::GetList(
		["sort"=>"asc"],
		["ACTIVE"=>"Y"]
	);
	while ($arIBlockType = $rsIBlockTypes->Fetch())
	{
		if ($ar = CIBlockType::GetByIDLang($arIBlockType["ID"], LANGUAGE_ID, true))
		{
			if ($str_OF_NEW_IBLOCK_TYPE_ID == $arIBlockType["ID"])
			{
				$str_OF_NEW_IBLOCK_TYPE_ID = '';
				$str_OF_IBLOCK_TYPE_ID = $arIBlockType["ID"];
				$str_OF_CREATE_IBLOCK_TYPE_ID = 'N';
			}
			$arIBlockTypeIDList[] = htmlspecialcharsbx($arIBlockType["ID"]);
			$arIBlockTypeNameList[] = htmlspecialcharsbx('['.$arIBlockType["ID"].'] '.$ar["~NAME"]);
		}
	}

	$arIBlockSitesList = [];
	$arIBlockFullInfo = [];

	$rsIBlocks = CIBlock::GetList(['IBLOCK_TYPE' => 'ASC','NAME' => 'ASC']);
	while ($arIBlock = $rsIBlocks->Fetch())
	{
		if (!isset($arIBlockSitesList[$arIBlock['ID']]))
		{
			$arLIDList = [];
			$arWithoutLinks = [];
			$rsIBlockSites = CIBlock::GetSite($arIBlock['ID']);
			while ($arIBlockSite = $rsIBlockSites->Fetch())
			{
				$arLIDList[] = $arIBlockSite['LID'];
				$arWithoutLinks[] = htmlspecialcharsbx($arIBlockSite['LID']);
			}
			$arIBlockSitesList[$arIBlock['ID']] = [
				'SITE_ID' => $arLIDList,
				'WITHOUT_LINKS' => implode(' ',$arWithoutLinks),
			];
		}
		$arIBlockItem = [
			'ID' => $arIBlock['ID'],
			'IBLOCK_TYPE_ID' => $arIBlock['IBLOCK_TYPE_ID'],
			'SITE_ID' => $arIBlockSitesList[$arIBlock['ID']]['SITE_ID'],
			'NAME' => htmlspecialcharsbx($arIBlock['NAME']),
			'ACTIVE' => $arIBlock['ACTIVE'],
			'FULL_NAME' => '['.$arIBlock['IBLOCK_TYPE_ID'].'] '.htmlspecialcharsbx($arIBlock['NAME']).' ('.$arIBlockSitesList[$arIBlock['ID']]['WITHOUT_LINKS'].')',
			'IS_CATALOG' => 'N',
			'SUBSCRIPTION' => 'N',
			'YANDEX_EXPORT' => 'N',
			'VAT_ID' => 0,
			'PRODUCT_IBLOCK_ID' => 0,
			'SKU_PROPERTY_ID' => 0,
			'OFFERS_IBLOCK_ID' => 0,
			'IS_OFFERS' => 'N',
		];
		$ar_res1 = CCatalog::GetByID($arIBlock['ID']);
		if (is_array($ar_res1))
		{
			$arIBlockItem['IS_CATALOG'] = 'Y';
			$arIBlockItem['SUBSCRIPTION'] = $ar_res1['SUBSCRIPTION'];
			$arIBlockItem['YANDEX_EXPORT'] = $ar_res1['YANDEX_EXPORT'];
			$arIBlockItem['VAT_ID'] = $ar_res1['VAT_ID'];
			$arIBlockItem['PRODUCT_IBLOCK_ID'] = $ar_res1['PRODUCT_IBLOCK_ID'];
			$arIBlockItem['SKU_PROPERTY_ID'] = $ar_res1['SKU_PROPERTY_ID'];
			$arIBlockItem['OFFERS_IBLOCK_ID'] = 0;
			if (0 < $ar_res1['PRODUCT_IBLOCK_ID'])
				$arIBlockItem['IS_OFFERS'] = 'Y';
		}

		$arIBlockFullInfo[$arIBlock['ID']] = $arIBlockItem;
	}
	foreach ($arIBlockFullInfo as $res)
	{
		if (0 < $res['PRODUCT_IBLOCK_ID'])
			$arIBlockFullInfo[$res['PRODUCT_IBLOCK_ID']]['OFFERS_IBLOCK_ID'] = $res['ID'];
	}

	$tabControl->BeginNextTab();
	?>
	<script>
	var obOFProps = new JCIBlockProperty({
		'PREFIX': '<?= $strPREFIX_OF_PROPERTY ?>',
		'FORM_ID': 'frm',
		'TABLE_PROP_ID': 'of_prop_list',
		'PROP_COUNT_ID': 'INT_OFFERS_PROPERTY_COUNT',
		'IBLOCK_ID': 0,
		'LANG': '<?= LANGUAGE_ID ?>',
		'TITLE': '<?= GetMessageJS('IB_E_OF_PROPERTY_DETAIL') ?>',
		'OBJ': 'obOFProps'
	});

	obOFProps.SetCells(CellTPL,8,CellAttr);
	</script>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("IB_E_CATALOG_TITLE")?></td>
	</tr>
	<tr>
		<td  width="40%"><label for="IS_CATALOG_Y"><?= GetMessage("IB_E_IS_CATALOG")?></label></td>
		<td width="60%">
			<?php
			$hiddenIsCatalog =
				$str_CATALOG_TYPE === \CCatalogSku::TYPE_OFFERS
					? 'Y'
					: 'N'
			;
			$checkboxAttrs =
				($str_IS_CATALOG === 'Y' ? ' checked' : '')
				. ($str_CATALOG_TYPE === \CCatalogSku::TYPE_OFFERS ? ' disabled="disabled"' : '')
			;
			?>
			<input type="hidden" name="IS_CATALOG" id="IS_CATALOG_N" value="<?= $hiddenIsCatalog ?>">
			<input type="checkbox" name="IS_CATALOG" id="IS_CATALOG_Y" value="Y"<?= $checkboxAttrs ?> onclick="ib_checkFldActivity(0);">
		</td>
	</tr><?php
	if (CBXFeatures::IsFeatureEnabled('SaleRecurring'))
	{
	?><tr>
		<td  width="40%"><label for="IS_CONTENT_Y"><?= GetMessage("IB_E_IS_CONTENT")?></label></td>
		<td width="60%">
			<input type="hidden" id="IS_CONTENT_N" name="SUBSCRIPTION" value="N">
			<input type="checkbox" id="IS_CONTENT_Y" name="SUBSCRIPTION" value="Y"<?= ('Y' == $str_SUBSCRIPTION ? ' checked' : '') ?> onclick="ib_checkFldActivity(1);">
		</td>
	</tr><?php
	}
	else
	{
		?><input type="hidden" id="IS_CONTENT_N" name="SUBSCRIPTION" value="N"><?php
	}

	if ($canUseYandexMarket)
	{
		?>
		<tr>
			<td  width="40%"><label for="YANDEX_EXPORT_Y"><?= GetMessage("IB_E_YANDEX_EXPORT")?></label></td>
			<td width="60%">
				<input type="hidden" id="YANDEX_EXPORT_N" name="YANDEX_EXPORT" value="N">
				<input type="checkbox" id="YANDEX_EXPORT_Y" name="YANDEX_EXPORT" value="Y"<?= ('Y' == $str_YANDEX_EXPORT ?  ' checked' : '') . ('Y' != $str_IS_CATALOG ? ' disabled="disabled"' : '') ?>>
			</td>
		</tr>
		<?php
	}
	?>
	<tr>
		<td  width="40%"><label for="VAT_ID"><?= GetMessage("IB_E_VAT_ID")?></label></td>
		<td width="60%"><?php
		$arVATRef = CatalogGetVATArray([], true);
		?><?=SelectBoxFromArray('VAT_ID', $arVATRef, $str_VAT_ID, '', ('Y' != $str_IS_CATALOG ? 'disabled="disabled"' : ''));?></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("IB_E_SKU_TITLE")?></td>
	</tr>
	<input type="hidden" name="CATALOG_TYPE" value="<?= htmlspecialcharsbx($str_CATALOG_TYPE) ?>" id="CATALOG_TYPE">
	<?php
	if ('O' == $str_CATALOG_TYPE)
	{
	?>
	<tr>
		<td  width="40%"><?= GetMessage("IB_E_IS_SKU")?></td>
		<td width="60%"><a href="/bitrix/admin/iblock_edit.php?type=<?= $str_PRODUCT_IBLOCK_TYPE_ID ?>&lang=<?= LANGUAGE_ID ?>&ID=<?= $str_PRODUCT_IBLOCK_ID ?>&admin=Y"><?= htmlspecialcharsbx($str_PRODUCT_IBLOCK_NAME) ?></a>
		<input type="hidden" id="USED_SKU_N" name="USED_SKU" value="N"></td>
	</tr>
	<?php
	}
	else
	{
	?>
	<tr>
		<td  width="40%"><label for="USED_SKU_Y"><?= GetMessage("IB_E_USED_SKU")?></label></td>
		<td width="60%">
			<input type="hidden" id="USED_SKU_N" name="USED_SKU" value="N">
			<input type="checkbox" id="USED_SKU_Y" name="USED_SKU" value="Y"<?= ('Y' == $str_USED_SKU ? ' checked' : '') ?> onclick="ib_skumaster(this)">
		</td>
	</tr>
	<tr>
	<td colspan="2">
	<div style="display: <?= ('Y' == $str_USED_SKU ? 'block' : 'none') ?>; width: 100%;" id="SKU-SETTINGS">
		<table style="width: 100%;"><tbody>
		<tr>
		<td  width="40%" class="field-name"><?= GetMessage("IB_E_OF_IBLOCK_INFO")?></td>
		<td width="60%"><select id="OF_IBLOCK_ID" name="OF_IBLOCK_ID" class="typeselect" onchange="show_add_offers(this);">
			<option value="0" <?= (0 == $str_OF_IBLOCK_ID ? 'selected' : '') ?>><?= GetMessage('IB_E_OF_IBLOCK_EMPTY')?></option>
			<option value="<?= CATALOG_NEW_OFFERS_IBLOCK_NEED ?>" <?= (CATALOG_NEW_OFFERS_IBLOCK_NEED == $str_OF_IBLOCK_ID ? 'selected' : '') ?>><?= GetMessage('IB_E_OF_IBLOCK_NEW')?></option><?php
			if (0 < $ID)
			{
				// for new iblock only new offers
				foreach ($arIBlockFullInfo as $value)
				{
					$boolAdd = true;
					if ($value['ID'] == $str_OF_IBLOCK_ID)
					{
						$boolAdd = true;
					}
					elseif (('N' == $value['ACTIVE']) || ('Y' == $value['IS_OFFERS']) || (0 < $value['OFFERS_IBLOCK_ID']) || ($ID == $value['ID']))
					{
						$boolAdd = false;
					}
					else
					{
						if (0 < $ID)
						{
							$arDiffParent = [];
							$arDiffParent = array_diff($value['SITE_ID'],$str_LID);
							$arDiffOffer = [];
							$arDiffOffer = array_diff($str_LID,$value['SITE_ID']);
							if (!empty($arDiffParent) || !empty($arDiffOffer))
							{
								$boolAdd = false;
							}
						}
					}
					if ($boolAdd)
					{
						?><option value="<?= (int)$value['ID'] ?>"<?= ($value['ID'] == $str_OF_IBLOCK_ID ? ' selected' : '') ?>><?= $value['FULL_NAME']; ?></option><?php
					}
				}
			}
		?></select>
		</td>
		</tr>
		</tbody></table>
		<?php
		/*
		?>
		<div id="offers_rights" style="display: <? echo (0 < intval($str_OF_IBLOCK_ID) ? 'display' : 'none'); ?>; width: 100%; text-align: center;">
			<table style="width: 100%;"><tbody>
			<tr>
			<td  width="40%" class="field-name"><label for="SKU_RIGHTS_Y"><?= GetMessage("IB_E_OF_RIGHTS"); ?></label></td>
			<td width="60%">
				<input type="hidden" name="SKU_RIGHTS" id="SKU_RIGHTS_N" value="N">
				<input type="checkbox" name="SKU_RIGHTS" id="SKU_RIGHTS_Y" value="Y"<?if('Y' == $str_SKU_RIGHTS) echo " checked"; ?>>
			</td>
			</tr>
			</tbody></table>
			</div>
			<?php
			*/
			?>
			<div id="offers_add_info" style="display: <?= (CATALOG_NEW_OFFERS_IBLOCK_NEED == $str_OF_IBLOCK_ID ? 'display' : 'none'); ?>; width: 100%; text-align: center;"><table style="margin: auto;"><tbody>
			<tr><td style="text-align: right; width: 25%;" class="field-name"><?= GetMessage('IB_E_OF_PR_TITLE'); ?>:</td><td style="text-align: left; width: 75%;"><input type="text" name="OF_IBLOCK_NAME" value="<?=htmlspecialcharsbx($str_OF_IBLOCK_NAME);?>" style="width: 100%;" /></td></tr>
			<tr><td style="text-align: left; width: 100%;" colspan="2" class="field-name"><input type="radio" value="N" id="OF_CREATE_IBLOCK_TYPE_ID_N" name="OF_CREATE_IBLOCK_TYPE_ID" <?= ('N' == $str_OF_CREATE_IBLOCK_TYPE_ID ? 'checked="checked"' : '') ?> onclick="change_offers_ibtype(this);"><label for="CREATE_OFFERS_TYPE_N"><?= GetMessage('IB_E_OF_PR_OLD_IBTYPE');?></label></td></tr>
			<tr><td style="text-align: right; width: 25%;" class="field-name"><?= GetMessage('IB_E_OF_PR_OFFERS_TYPE'); ?>:</td><td style="text-align: left; width: 75%;"><?= SelectBoxFromArray('OF_IBLOCK_TYPE_ID',array('REFERENCE' => $arIBlockTypeNameList,'REFERENCE_ID' => $arIBlockTypeIDList),$str_OF_IBLOCK_TYPE_ID,'',('N' == $str_OF_CREATE_IBLOCK_TYPE_ID ? '' : 'disabled="disabled"')); ?></td></tr>
			<tr><td style="text-align: left; width: 100%;" colspan="2" class="field-name"><input type="radio" value="Y" id="OF_CREATE_IBLOCK_TYPE_ID_Y" name="OF_CREATE_IBLOCK_TYPE_ID" <?= ('Y' == $str_OF_CREATE_IBLOCK_TYPE_ID ? 'checked="checked"' : '') ?> onclick="change_offers_ibtype(this);"><label for="CREATE_OFFERS_TYPE_Y"><?= GetMessage('IB_E_OF_PR_OFFERS_NEW_IBTYPE');?></label></td></tr>
			<tr><td style="text-align: right; width: 25%;" class="field-name"><?= GetMessage('IB_E_OF_PR_OFFERS_NEWTYPE'); ?>:</td><td style="text-align: left; width: 75%;"><input type="text" name="OF_NEW_IBLOCK_TYPE_ID" id="OF_NEW_IBLOCK_TYPE_ID" value="" style="width: 100%;" <?= ('Y' == $str_OF_CREATE_IBLOCK_TYPE_ID ? '' : 'disabled="disabled"') ?> /></td></tr>
			</tbody></table>
			<div><b><?= GetMessage('IB_E_OFFERS_PROPERTIES'); ?></b></div>
			<table class="internal" style="text-align: center; margin: auto;" id="of_prop_list">
				<tr class="heading">
					<td>ID</td>
					<td><?= GetMessage("IB_E_PROP_NAME_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_TYPE_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_ACTIVE_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_MULT_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_REQIRED_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_SORT_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_CODE_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_MODIFY_SHORT")?></td>
					<td><?= GetMessage("IB_E_PROP_DELETE_SHORT")?></td>
				</tr>
				<?php
				$arOFPropList = [];
				if (0 < intval($str_OF_IBLOCK_ID))
				{
					$rsProps = CIBlock::GetProperties(
						$str_OF_IBLOCK_ID,
						[
							'SORT' => 'ASC',
							'ID' => 'ASC',
						]
					);
					while ($arProp = $rsProps->Fetch())
					{
						ConvProp($arProp,$arHiddenPropFields);
						if ($bVarsFromForm)
						{
							$intPropID = $arProp['ID'];
							$arTempo = GetPropertyInfo($strPREFIX_OF_PROPERTY, $intPropID, false, $arHiddenPropFields);
							if (is_array($arTempo))
								$arProp = $arTempo;
							$arProp['ID'] = $intPropID;
						}
						$arProp = ConvertToSafe($arProp,$arDisabledPropFields);
						$arProp['SHOW_DEL'] = 'Y';
						$arOFPropList[$arProp['ID']] = $arProp;
					}
				}

				$intPropCount = 0;
				if ($request->isPost())
				{
					$intPropCount = (int)$request->getPost('OFFERS_PROPERTY_COUNT');
				}
				if ($intPropCount <= 0)
				{
					$intPropCount = PROPERTY_EMPTY_ROW_SIZE;
				}
				$intPropNumber = 0;
				for ($i = 0; $i < $intPropCount; $i++)
				{
					$arProp = GetPropertyInfo($strPREFIX_OF_PROPERTY, 'n'.$i, false, $arHiddenPropFields);
					if (is_array($arProp))
					{
						$arProp = ConvertToSafe($arProp,$arDisabledPropFields);
						$arProp['ID'] = 'n'.$intPropNumber;
						$arOFPropList['n'.$intPropNumber] = $arProp;
						$intPropNumber++;
					}
				}
				for ($i = 0; $intPropNumber < PROPERTY_EMPTY_ROW_SIZE; $intPropNumber++)
				{
					$arProp = $arDefPropInfo;
					ConvProp($arProp,$arHiddenPropFields);
					$arProp['ID'] = 'n'.$intPropNumber;
					$arOFPropList['n'.$intPropNumber] = $arProp;
				}
				foreach ($arOFPropList as $mxPropID => $arProp)
				{
					$arProp['IBLOCK_ID'] = $ID;
					echo __AddPropRow($mxPropID,$strPREFIX_OF_PROPERTY,$arProp);
				}
				?></table>
				<div style="width: 100%; text-align: center; margin: 10px 0;">
					<input class="adm-btn-big" onclick="obOFProps.addPropRow();" type="button" value="<?= GetMessage('IB_E_SHOW_ADD_PROP_ROW') ?>" title="<?= GetMessage('IB_E_SHOW_ADD_PROP_ROW_DESCR')?>">
				</div>
				<input type="hidden" name="OFFERS_PROPERTY_COUNT" id="INT_OFFERS_PROPERTY_COUNT" value="<?= $intPropNumber ?>">
			</div>
	</div>
	</td>
	</tr>
	<?php
	}
	?>
<script>
	var is_cat = BX('IS_CATALOG_Y'),
		is_cont = BX('IS_CONTENT_Y'),
		is_yand = BX('YANDEX_EXPORT_Y'),
		vat_id = BX('VAT_ID'),
		cat_type =  BX('CATALOG_TYPE'),
		ob_sku_settings = BX('SKU-SETTINGS'),
		ob_offers_add = BX('offers_add_info'),
		ob_of_iblock_type_id = BX('OF_IBLOCK_TYPE_ID'),
		ob_of_new_iblock_type_id = BX('OF_NEW_IBLOCK_TYPE_ID');

	//var ob_sku_rights = BX('offers_rights');

	function ib_checkFldActivity(flag)
	{
		if (
			!BX.type.isElementNode(is_cat)
			|| !BX.type.isElementNode(is_yand)
			|| !BX.type.isElementNode(vat_id)
		)
			return;
		if (flag === 0)
		{
			if (BX.type.isElementNode(cat_type))
			{
				if (cat_type.value === 'O')
					is_cat.checked = true;
			}
			if (!is_cat.checked)
			{
				if (BX.type.isElementNode(is_cont))
					is_cont.checked = false;
				is_yand.checked = false;
			}
		}
		if (flag === 1)
		{
			if (!BX.type.isElementNode(is_cont))
				return;
			if (is_cont.checked)
				is_cat.checked = true;
		}

		is_yand.disabled = !is_cat.checked;
		vat_id.disabled = !is_cat.checked;
	}
	function ib_skumaster(obj)
	{
		if (!BX.type.isElementNode(ob_sku_settings))
			return;
		ob_sku_settings.style.display = (obj.checked ? 'block' : 'none');
	}

	function show_add_offers(obj)
	{
		var value = obj.options[obj.selectedIndex].value;
		if (undefined !== ob_offers_add)
		{
			if (<?= CATALOG_NEW_OFFERS_IBLOCK_NEED ?> == value)
			{
				ob_offers_add.style.display = 'block';
			}
			else
			{
				ob_offers_add.style.display = 'none';
			}
		}
/*		if (undefined !== ob_sku_rights)
		{
			ob_sku_rights.style.display = (0 < ParseInt(value) ? 'block' : 'none');
		} */
	}
	function change_offers_ibtype(obj)
	{
		var value = obj.value;
		if (value !== 'Y' && value !== 'N')
			return;
		if (value === 'Y')
		{
			ob_of_iblock_type_id.disabled = true;
			ob_of_new_iblock_type_id.disabled = false;
		}
		else
		{
			ob_of_iblock_type_id.disabled = false;
			ob_of_new_iblock_type_id.disabled = true;
		}
	}
</script>
<?php
}

if(CIBlockRights::UserHasRightTo($ID, $ID, "iblock_rights_edit"))
{
	$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("IB_E_RIGHTS_MODE_SECTION_TITLE")?></td>
	</tr>
	<?php
	if($str_RIGHTS_MODE === Iblock\IblockTable::RIGHTS_EXTENDED):
		?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><label for="RIGHTS_MODE"><?= GetMessage("IB_E_RIGHTS_MODE")?></label></td>
			<td width="60%">
				<input type="hidden" name="RIGHTS_MODE" value="S">
				<input type="checkbox" id="RIGHTS_MODE" name="RIGHTS_MODE" value="E" checked="checked"><?php
				echo BeginNote(), GetMessage("IB_E_RIGHTS_MODE_NOTE1"), EndNote();
				?>
			</td>
		</tr>
		<?php
		$obIBlockRights = new CIBlockRights($ID);
		IBlockShowRights(
			'iblock',
			$ID,
			$ID,
			GetMessage("IB_E_RIGHTS_SECTION_TITLE"),
			"RIGHTS",
			$obIBlockRights->GetRightsList(),
			$obIBlockRights->GetRights(["count_overwrited" => true]),
			true
		);
		?>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<?php
	else:
		?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><label for="RIGHTS_MODE"><?= GetMessage("IB_E_RIGHTS_MODE")?></label></td>
			<td width="60%">
				<input type="hidden" name="RIGHTS_MODE" value="S">
				<input type="checkbox" id="RIGHTS_MODE" name="RIGHTS_MODE" value="E"><?php
				echo BeginNote(), GetMessage("IB_E_RIGHTS_MODE_NOTE2"), EndNote();
				?>
			</td>
		</tr>
		<?php
		if ($bWorkflow && $str_WORKFLOW=="Y") :
			$arPermType = [
				"D"=>GetMessage("IB_E_ACCESS_D"),
				"R"=>GetMessage("IB_E_ACCESS_R"),
				"S"=>GetMessage("IB_E_ACCESS_S"),
				"U"=>GetMessage("IB_E_ACCESS_U"),
				"W"=>GetMessage("IB_E_ACCESS_W"),
				"X"=>GetMessage("IB_E_ACCESS_X"),
			];
		elseif ($bBizprocTab) :
			$arPermType = [
				"D"=>GetMessage("IB_E_ACCESS_D"),
				"R"=>GetMessage("IB_E_ACCESS_R"),
				"S"=>GetMessage("IB_E_ACCESS_S"),
				"U"=>GetMessage("IB_E_ACCESS_U2"),
				"W"=>GetMessage("IB_E_ACCESS_W"),
				"X"=>GetMessage("IB_E_ACCESS_X"),
			];
		else :
			$arPermType = [
				"D"=>GetMessage("IB_E_ACCESS_D"),
				"R"=>GetMessage("IB_E_ACCESS_R"),
				"S"=>GetMessage("IB_E_ACCESS_S"),
				"T"=>GetMessage("IB_E_ACCESS_T"),
				"W"=>GetMessage("IB_E_ACCESS_W"),
				"X"=>GetMessage("IB_E_ACCESS_X"),
			];
		endif;
		$perm = CIBlock::GetGroupPermissions($ID);
		if(!array_key_exists(1, $perm))
			$perm[1] = "X";
		?>
		<tr class="heading">
			<td colspan="2"><?= GetMessage("IB_E_DEFAULT_ACCESS_TITLE")?></td>
		</tr>
		<tr>
			<td nowrap width="40%"><?= GetMessage("IB_E_EVERYONE")?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=2&amp;lang=<?=LANGUAGE_ID?>">2</a>]:</td>
			<td width="60%">

					<select name="GROUP[2]" id="group_2">
					<?php
					if ($bVarsFromForm)
					{
						$strSelected = $GROUP[2] ?? '';
					}
					else
					{
						$strSelected = $perm[2] ?? '';
					}
					foreach($arPermType as $key => $val):
					?>
						<option value="<?= $key ?>"<?= ($strSelected == $key ? ' selected' : '') ?>><?= htmlspecialcharsex($val)?></option>
					<?php
					endforeach;
					?>
					</select>

					<script>
					function OnGroupChange(control, message)
					{
						var all = document.getElementById('group_2');
						var msg = document.getElementById(message);
						if(all && all.value >= control.value && control.value != '')
						{
							if(msg) msg.innerHTML = '<?= CUtil::JSEscape(GetMessage("IB_E_ACCESS_WARNING")) ?>';
						}
						else
						{
							if(msg) msg.innerHTML = '';
						}
					}
					</script>

			</td>
		</tr>
		<tr class="heading">
			<td colspan="2"><?= GetMessage("IB_E_GROUP_ACCESS_TITLE")?></td>
		</tr>
		<?php
		$groups = CGroup::GetList("sort", "asc", ["ID"=>"~2"]);
		while($r = $groups->GetNext()):
			if ($bVarsFromForm)
			{
				$strSelected = $GROUP[$r["ID"]] ?? '';
			}
			else
			{
				$strSelected = $perm[$r["ID"]] ?? '';
			}

			if($strSelected=="U" && !$bWorkflow && !$bBizproc)
				$strSelected="R";

			if($strSelected!="R" &&
				$strSelected!="S" &&
				$strSelected!="T" &&
				$strSelected!="U" &&
				$strSelected!="W" &&
				$strSelected!="X" &&
				$ID>0 && !$bVarsFromForm)
					$strSelected="";
			?>
		<tr>
			<td nowrap width="40%"><?= $r["NAME"] ?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=<?=$r["ID"]?>&lang=<?=LANGUAGE_ID?>"><?=$r["ID"]?></a>]:</td>
			<td width="60%">

					<select name="GROUP[<?= $r["ID"] ?>]" OnChange="OnGroupChange(this, 'spn_group_<?= $r["ID"] ?>');">
						<option value=""><?= GetMessage("IB_E_DEFAULT_ACCESS")?></option>
					<?php
					foreach($arPermType as $key => $val):
						?>
						<option value="<?= $key?>"<?= ($strSelected == $key ? ' selected' : '') ?>><?= htmlspecialcharsex($val) ?></option>
						<?php
					endforeach;
					?>
					</select>
					<span id="spn_group_<?= $r["ID"] ?>"></span>
			</td>
		</tr>
		<?php
		endwhile;
	endif;
}//if(CIBlockRights::UserHasRightTo($ID, $ID, "iblock_rights_edit"))

$tabControl->BeginNextTab();
	$arMessages = CIBlock::GetMessages($ID);
	if($bVarsFromForm)
	{
		foreach($arMessages as $MESSAGE_ID => $MESSAGE_TEXT)
			$arMessages[$MESSAGE_ID] = $_REQUEST[$MESSAGE_ID];
	}
	if($arIBTYPE["SECTIONS"]=="Y"):?>
	<tr>
		<td width="40%"><?= GetMessage("IB_E_SECTIONS_NAME")?></td>
		<td width="60%">
			<input type="text" name="SECTIONS_NAME" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["SECTIONS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_SECTION_NAME")?></td>
		<td>
			<input type="text" name="SECTION_NAME" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["SECTION_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_SECTION_ADD")?></td>
		<td>
			<input type="text" name="SECTION_ADD" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["SECTION_ADD"])?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_SECTION_EDIT")?></td>
		<td>
			<input type="text" name="SECTION_EDIT" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["SECTION_EDIT"])?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_SECTION_DELETE")?></td>
		<td>
			<input type="text" name="SECTION_DELETE" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["SECTION_DELETE"])?>">
		</td>
	</tr>
	<?php
	endif;
	?>
	<tr>
		<td><?= GetMessage("IB_E_ELEMENTS_NAME")?></td>
		<td>
			<input type="text" name="ELEMENTS_NAME" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["ELEMENTS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_ELEMENT_NAME")?></td>
		<td>
			<input type="text" name="ELEMENT_NAME" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["ELEMENT_NAME"]) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_ELEMENT_ADD") ?></td>
		<td>
			<input type="text" name="ELEMENT_ADD" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["ELEMENT_ADD"]) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_ELEMENT_EDIT") ?></td>
		<td>
			<input type="text" name="ELEMENT_EDIT" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["ELEMENT_EDIT"]) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IB_E_ELEMENT_DELETE") ?></td>
		<td>
			<input type="text" name="ELEMENT_DELETE" size="40" maxlength="100" value="<?= htmlspecialcharsbx($arMessages["ELEMENT_DELETE"]) ?>">
		</td>
	</tr>
	<?php
if ($bBizprocTab):
	$tabControl->BeginNextTab();

	if (!isset($arWorkflowTemplates))
		$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(["iblock", "CIBlockDocument", "iblock_".$ID]);
	?>
	<tr>
		<td colspan="2">
			<?php
			if (!empty($arWorkflowTemplates)):
				?>
				<table border="0" cellspacing="0" cellpadding="0" class="internal">
					<tr class="heading">
						<td><?= GetMessage("IB_E_BP_NAME") ?></td>
						<td><?= GetMessage("IB_E_BP_CHANGED") ?></td>
						<td><?= GetMessage("IB_E_BP_AUTORUN") ?></td>
					</tr>
					<?php
					foreach ($arWorkflowTemplates as $arTemplate)
					{
						?>
						<tr>
							<td>
								<?php
								if(IsModuleInstalled("bizprocdesigner")):
									?>
									<a href="/bitrix/admin/iblock_bizproc_workflow_edit.php?document_type=iblock_<?= $ID ?>&lang=<?= LANGUAGE_ID ?>&ID=<?= $arTemplate["ID"] ?>&back_url_list=<?= urlencode($APPLICATION->GetCurPageParam("", [])) ?>" target="_blank"><?= $arTemplate["NAME"] ?> [<?= $arTemplate["ID"] ?>]</a>
									<?php
								else:
									echo $arTemplate["NAME"];
								endif;
								?>
								<br /><?= $arTemplate["DESCRIPTION"] ?></td>
							<td nowrap><?= $arTemplate["MODIFIED"] ?><br />[<a href="user_edit.php?ID=<?= $arTemplate["USER_ID"] ?>"><?= $arTemplate["USER_ID"] ?></a>] <?= $arTemplate["USER"] ?></td>
							<td nowrap>
								<?php
									if($bVarsFromForm)
										$checked = $_REQUEST["create_bizproc_".$arTemplate["ID"]] == "Y";
									else
										$checked = ($arTemplate["AUTO_EXECUTE"] & 1) != 0;
								?>
								<label><input type="checkbox" id="id_create_bizproc_<?= $arTemplate["ID"] ?>" name="create_bizproc_<?= $arTemplate["ID"] ?>" value="Y"<?= ($checked ? ' checked' : '') ?>><?= GetMessage("IB_E_BP_AUTORUN_CREATE") ?></label><br />
								<?php
									if($bVarsFromForm)
										$checked = $_REQUEST["edit_bizproc_".$arTemplate["ID"]] == "Y";
									else
										$checked = ($arTemplate["AUTO_EXECUTE"] & 2) != 0;
								?>
								<label><input type="checkbox" id="id_edit_bizproc_<?= $arTemplate["ID"] ?>" name="edit_bizproc_<?= $arTemplate["ID"] ?>" value="Y"<?= ($checked ? ' checked' : '') ?>><?= GetMessage("IB_E_BP_AUTORUN_UPDATE") ?></label><br />
							</td>
						</tr>
						<?php
					}
					?>
				</table>
				<br>
			<?php
			endif;
			if (IsModuleInstalled("bizprocdesigner")):
				?>
				<a href="/bitrix/admin/iblock_bizproc_workflow_admin.php?document_type=iblock_<?= $ID ?>&lang=<?= LANGUAGE_ID ?>&back_url_list=<?= urlencode($APPLICATION->GetCurPageParam("", [])) ?>" target="_blank"><?= GetMessage("IB_E_GOTO_BP") ?></a>
				<?php
			endif;
			?>
		</td>
	</tr>
	<?php
endif;

$tabControl->BeginNextTab();
	if($bVarsFromForm)
		$arFields = $_REQUEST["FIELDS"];
	else
		$arFields = CIBlock::GetFields($ID);
	$arDefFields = CIBlock::GetFieldsDefaults();
	foreach($arDefFields as $FIELD_ID => $arField):
		if ($arField["VISIBLE"] === "N")
		{
			continue;
		}
		if (!preg_match("/^LOG_/", $FIELD_ID))
		{
			continue;
		}
		$checkboxAttrs = '';
		if ($arFields[$FIELD_ID]['IS_REQUIRED'] === 'Y' || $arField['IS_REQUIRED'] !== false)
		{
			$checkboxAttrs .= ' checked';
		}
		if ($arField['IS_REQUIRED'] !== false)
		{
			$checkboxAttrs .=  ' disabled';
		}
		?>
		<tr>
			<td width="40%"><label for="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]"><?= GetMessage("IB_E_".$FIELD_ID) ?></label>:</td>
			<td>
				<input type="hidden" value="N" name="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]">
				<input type="checkbox" value="Y" id="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]" name="FIELDS[<?= $FIELD_ID ?>][IS_REQUIRED]"<?= $checkboxAttrs ?>>
			</td>
		</tr>
	<?php
	endforeach;

	$backUrl = $returnUrl;
	if ($backUrl === '')
	{
		$backUrl =
			'iblock_admin.php?lang=' . LANGUAGE_ID
			. '&type=' . urlencode($type)
			. '&admin=' . urlencode($isAdminUrl)
		;
	}
	$tabControl->Buttons([
		'disabled' => false,
		'back_url' => $backUrl,
	]);
	$tabControl->End();
	unset($backUrl);
	?>
</form>
<?php
else:
?>
<br>
<?php
	ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
