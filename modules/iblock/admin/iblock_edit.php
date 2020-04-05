<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('iblock');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$ID = (isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : 0);
if (!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Main\Page\Asset::getInstance()->addJs('/bitrix/js/iblock/iblock_edit.js');

define('CATALOG_NEW_OFFERS_IBLOCK_NEED','-1');
define('PROPERTY_EMPTY_ROW_SIZE',5);
$strPREFIX_OF_PROPERTY = 'OF_PROPERTY_';
$strPREFIX_IB_PROPERTY = 'IB_PROPERTY_';

$arDefPropInfo = array(
	'ID' => 0,
	'IBLOCK_ID' => 0,
	'FILE_TYPE' => '',
	'LIST_TYPE' => 'L',
	'ROW_COUNT' => '1',
	'COL_COUNT' => '30',
	'LINK_IBLOCK_ID' => '0',
	'DEFAULT_VALUE' => '',
	'USER_TYPE_SETTINGS' => array(),
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
	'VALUES' => array(),
	'SECTION_PROPERTY' => 'Y',
	'SMART_FILTER' => 'N',
	'DISPLAY_TYPE' => '',
	'DISPLAY_EXPANDED' => 'N',
	'FILTER_HINT' => '',
);

$arDisabledPropFields = array(
	'ID',
	'IBLOCK_ID',
	'TIMESTAMP_X',
	'TMP_ID',
	'VERSION',
	'PROPINFO',
);

$arHiddenPropFields = array(
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
);

function CheckIBlockTypeID($strIBlockTypeID,$strNewIBlockTypeID,$strNeedAdd)
{
	$arResult = false;
	$strNeedAdd = ('Y' == $strNeedAdd ? 'Y': 'N');
	$strNewIBlockTypeID = trim($strNewIBlockTypeID);
	$strIBlockTypeID = trim($strIBlockTypeID);
	if ('Y' == $strNeedAdd)
	{
		$obIBlockType = new CIBlockType();
		if ('' != $strNewIBlockTypeID)
		{
			$rsIBlockTypes = CIBlockType::GetByID($strNewIBlockTypeID);
			if ($arIBlockType = $rsIBlockTypes->Fetch())
			{
				$arResult = array(
					'RESULT' => 'OK',
					'VALUE' => $strNewIBlockTypeID,
				);
			}
			else
			{
				$arFields = array(
					'ID' => $strNewIBlockTypeID,
					'SECTIONS' => 'N',
					'IN_RSS' => 'N',
					'SORT' => 500,
				);
				$rsLanguages = CLanguage::GetList($by="sort", $order="desc",array('ACTIVE' => 'Y'));
				while ($arLanguage = $rsLanguages->Fetch())
				{
					$arFields['LANG'][$arLanguage['LID']]['NAME'] = $strNewIBlockTypeID;
				}
				$mxOffersType = $obIBlockType->Add($arFields);
				if (false == $mxOffersType)
				{
					$arResult = array(
						'RESULT' => 'ERROR',
						'MESSAGE' => $obIBlockType->LAST_ERROR,
					);
				}
				else
				{
					$arResult = array(
						'RESULT' => 'OK',
						'VALUE' => $strNewIBlockTypeID,
					);
				}
			}
		}
		else
		{
			$arResult = array(
				'RESULT' => 'ERROR',
				'MESSAGE' => GetMessage('IB_E_OF_ERR_NEW_IBLOCK_TYPE_ABSENT'),
			);
		}
	}
	else
	{
		if ('' == $strIBlockTypeID)
		{
			$arResult = array(
				'RESULT' => 'ERROR',
				'MESSAGE' => GetMessage('IB_E_OF_ERR_IBLOCK_TYPE_ABSENT')
			);
		}
		else
		{
			$rsIBlockTypes = CIBlockType::GetByID($strIBlockTypeID);
			if (!($arIBlockType = $rsIBlockTypes->Fetch()))
			{
				$arResult = array(
					'RESULT' => 'ERROR',
					'MESSAGE' => GetMessage('IB_E_OF_ERR_IBLOCK_TYPE_BAD')
				);
			}
			else
			{
				$arResult = array(
					'RESULT' => 'OK',
					'VALUE' => $strIBlockTypeID,
				);
			}
		}
	}
	return $arResult;
}

function ConvProp(&$arProperty,$arHiddenPropFields)
{
	$arEncodedProp = array();
	foreach ($arHiddenPropFields as &$strPropField)
	{
		if (isset($arProperty[$strPropField]))
		{
			$arEncodedProp[$strPropField] = $arProperty[$strPropField];
			unset($arProperty[$strPropField]);
		}
	}
	$arProperty['PROPINFO'] = base64_encode(serialize($arEncodedProp));
}

function GetPropertyInfo($strPrefix, $ID, $boolUnpack = true, $arHiddenPropFields = array())
{
	global $arDefPropInfo;
	$boolUnpack = ($boolUnpack === true);
	$arResult = false;

	if (!is_array($arHiddenPropFields))
		return $arResult;

	if (isset($_POST[$strPrefix.$ID.'_NAME']) && (0 < strlen($_POST[$strPrefix.$ID.'_NAME'])) && isset($_POST[$strPrefix.$ID.'_PROPINFO']))
	{
		$strEncodePropInfo = $_POST[$strPrefix.$ID.'_PROPINFO'];
		$strPropInfo = base64_decode($strEncodePropInfo);
		if (CheckSerializedData($strPropInfo))
		{
			$arResult = array(
				'ID' => (isset($_POST[$strPrefix.$ID.'_ID']) && 0 < intval($_POST[$strPrefix.$ID.'_ID']) ? intval($_POST[$strPrefix.$ID.'_ID']) : 0),
				'NAME' => strval($_POST[$strPrefix.$ID."_NAME"]),
				'SORT' => (0 < intval($_POST[$strPrefix.$ID."_SORT"]) ? intval($_POST[$strPrefix.$ID."_SORT"]) : 500),
				'CODE' => (isset($_POST[$strPrefix.$ID."_CODE"]) ? strval($_POST[$strPrefix.$ID."_CODE"]) : ''),
				'MULTIPLE' => (isset($_POST[$strPrefix.$ID."_MULTIPLE"]) && 'Y' == $_POST[$strPrefix.$ID."_MULTIPLE"] ? 'Y' : 'N'),
				'IS_REQUIRED' => (isset($_POST[$strPrefix.$ID."_IS_REQUIRED"]) && 'Y' == $_POST[$strPrefix.$ID."_IS_REQUIRED"] ? 'Y' : 'N'),
				'ACTIVE' => (isset($_POST[$strPrefix.$ID."_ACTIVE"]) && 'Y' == $_POST[$strPrefix.$ID."_ACTIVE"] ? 'Y' : 'N'),
				'USER_TYPE' => false,
			);

			if (isset($_POST[$strPrefix.$ID."_PROPERTY_TYPE"]))
			{
				if (false !== strpos($_POST[$strPrefix.$ID."_PROPERTY_TYPE"], ":"))
				{
					list($arResult["PROPERTY_TYPE"], $arResult["USER_TYPE"]) = explode(':', $_POST[$strPrefix.$ID."_PROPERTY_TYPE"], 2);
				}
				else
				{
					$arResult["PROPERTY_TYPE"] = $_POST[$strPrefix.$ID."_PROPERTY_TYPE"];
				}
			}

			if ($boolUnpack)
			{
				$arPropInfo = unserialize($strPropInfo);
				foreach ($arHiddenPropFields as &$strFieldKey)
				{
					$arResult[$strFieldKey] = (isset($arPropInfo[$strFieldKey]) ? $arPropInfo[$strFieldKey] : $arDefPropInfo[$strFieldKey]);
				}
				$arResult['ROW_COUNT'] = intval($arResult['ROW_COUNT']);
				if (0 >= $arResult['ROW_COUNT'])
					$arResult['ROW_COUNT'] = $arDefPropInfo['ROW_COUNT'];
				$arResult['COL_COUNT'] = intval($arResult['COL_COUNT']);
				if (0 >= $arResult['COL_COUNT'])
					$arResult['COL_COUNT'] = $arDefPropInfo['COL_COUNT'];
				$arResult['LINK_IBLOCK_ID'] = intval($arResult['LINK_IBLOCK_ID']);
				if (0 > $arResult['LINK_IBLOCK_ID'])
					$arResult['LINK_IBLOCK_ID'] = $arDefPropInfo['LINK_IBLOCK_ID'];
				$arResult['WITH_DESCRIPTION'] = ('Y' == $arResult['WITH_DESCRIPTION'] ? 'Y' : 'N');
				$arResult['FILTRABLE'] = ('Y' == $arResult['FILTRABLE'] ? 'Y' : 'N');
				$arResult['SEARCHABLE'] = ('Y' == $arResult['SEARCHABLE'] ? 'Y' : 'N');
				$arResult['SECTION_PROPERTY'] = ('N' == $arResult['SECTION_PROPERTY'] ? 'N' : 'Y');
				$arResult['SMART_FILTER'] = ('Y' == $arResult['SMART_FILTER'] ? 'Y' : 'N');
				$arResult['DISPLAY_TYPE'] = substr($arResult['DISPLAY_TYPE'], 0, 1);
				$arResult['DISPLAY_EXPANDED'] = ('Y' == $arResult['DISPLAY_EXPANDED'] ? 'Y' : 'N');
				$arResult['MULTIPLE_CNT'] = intval($arResult['MULTIPLE_CNT']);
				if (0 >= $arResult['MULTIPLE_CNT'])
					$arResult['MULTIPLE_CNT'] = $arDefPropInfo['MULTIPLE_CNT'];
				$arResult['LIST_TYPE'] = ('C' == $arResult['LIST_TYPE'] ? 'C' : 'L');
				if ('Y' != COption::GetOptionString("iblock", "show_xml_id", "N") && isset($arResult["XML_ID"]))
					unset($arResult["XML_ID"]);
			}
			else
			{
				$arResult['PROPINFO'] = $strEncodePropInfo;
			}
			if (0 < intval($ID))
			{
				$arResult['DEL'] = (isset($_POST[$strPrefix.$ID."_DEL"]) && ('Y' == $_POST[$strPrefix.$ID."_DEL"]) ? 'Y' : 'N');
			}
		}
	}
	return $arResult;
}

function CheckSKUProperty($ID, $SKUID)
{
	$ID = (int)$ID;
	$SKUID = (int)$SKUID;
	if ($ID > 0 && $SKUID > 0)
	{
		$propertyId = CIBlockPropertyTools::createProperty($SKUID, CIBlockPropertyTools::CODE_SKU_LINK, array('LINK_IBLOCK_ID' => $ID));
		if ($propertyId)
		{
			$arResult = array(
				'RESULT' => 'OK',
				'VALUE' => $propertyId
			);
		}
		else
		{
			$arResult = array(
				'RESULT' => 'ERROR',
				'MESSAGE' => implode('. ',CIBlockPropertyTools::getErrors())
			);
		}
	}
	else
	{
		$arResult = array(
			'RESULT' => 'ERROR',
			'MESSAGE' => GetMessage('IB_E_OF_ERR_SKU_IBLOCKS_IS_ABSENT'),
		);
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
					$arTempo = array();
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

function __AddPropCellName($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="text" size="25" maxlength="255" name="<?echo $strPrefix.$intOFPropID?>_NAME" id="<?echo $strPrefix.$intOFPropID?>_NAME" value="<?echo $arPropInfo['NAME']?>"><?
	?><input type="hidden" name="<? echo $strPrefix.$intOFPropID?>_PROPINFO" id="<? echo $strPrefix.$intOFPropID?>_PROPINFO" value="<?=htmlspecialcharsbx($arPropInfo['PROPINFO']); ?>"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}

function __AddPropCellType($intOFPropID,$strPrefix,$arPropInfo)
{
	static $baseTypeList = null;
	static $arUserTypeList = null;
	
	if ($baseTypeList === null)
		$baseTypeList = Iblock\Helpers\Admin\Property::getBaseTypeList(true);
	if ($arUserTypeList === null)
	{
		$arUserTypeList = CIBlockProperty::GetUserType();
		\Bitrix\Main\Type\Collection::sortByColumn($arUserTypeList, array('DESCRIPTION' => SORT_STRING));
	}
	$boolUserPropExist = !empty($arUserTypeList);
	ob_start();
	?><select name="<?echo $strPrefix.$intOFPropID?>_PROPERTY_TYPE" id="<?echo $strPrefix.$intOFPropID?>_PROPERTY_TYPE" style="width:150px"><?
	if ($boolUserPropExist)
	{
		?><optgroup label="<? echo GetMessage('IB_E_PROP_BASE_TYPE_GROUP'); ?>"><?
	}
	foreach ($baseTypeList as $typeId => $typeTitle)
	{
		?><option value="<?=$typeId; ?>" <?=($arPropInfo['PROPERTY_TYPE'] == $typeId && !$arPropInfo['USER_TYPE'] ? ' selected' : '');?>><?=htmlspecialcharsbx($typeTitle); ?></option><?
	}
	unset($typeTitle);
	unset($typeId);

	if ($boolUserPropExist)
	{
		?></optgroup><optgroup label="<? echo GetMessage('IB_E_PROP_USER_TYPE_GROUP'); ?>"><?
	}
	foreach($arUserTypeList as $ar)
	{
		?><option value="<?=htmlspecialcharsbx($ar["PROPERTY_TYPE"].":".$ar["USER_TYPE"])?>" <?if($arPropInfo['PROPERTY_TYPE']==$ar["PROPERTY_TYPE"] && $arPropInfo['USER_TYPE']==$ar["USER_TYPE"])echo " selected"?>><?=htmlspecialcharsbx($ar["DESCRIPTION"])?></option>
		<?
	}
	if ($boolUserPropExist)
	{
		?></optgroup><?
	}
	?>
	</select><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}

function __AddPropCellActive($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="hidden" name="<?echo $strPrefix.$intOFPropID?>_ACTIVE" id="<?echo $strPrefix.$intOFPropID?>_ACTIVE_N" value="N">
	<input type="checkbox" name="<?echo $strPrefix.$intOFPropID?>_ACTIVE" id="<?echo $strPrefix.$intOFPropID?>_ACTIVE_Y" value="Y"<?
	if ($arPropInfo['ACTIVE']=="Y") echo " checked"; ?> title="<?=htmlspecialcharsbx(GetMessage("IB_E_PROP_ACTIVE_SHORT")); ?>"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}

function __AddPropCellMulti($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="hidden" name="<?echo $strPrefix.$intOFPropID?>_MULTIPLE" id="<?echo $strPrefix.$intOFPropID?>_MULTIPLE_N" value="N">
	<input type="checkbox" name="<?echo $strPrefix.$intOFPropID?>_MULTIPLE" id="<?echo $strPrefix.$intOFPropID?>_MULTIPLE_Y" value="Y"<?
	if($arPropInfo['MULTIPLE']=="Y")echo " checked"?> title="<?=htmlspecialcharsbx(GetMessage("IB_E_PROP_MULT_SHORT")); ?>">
	<?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}

function __AddPropCellReq($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="hidden" name="<?echo $strPrefix.$intOFPropID?>_IS_REQUIRED" id="<?echo $strPrefix.$intOFPropID?>_IS_REQUIRED_N" value="N">
	<input type="checkbox" name="<?echo $strPrefix.$intOFPropID?>_IS_REQUIRED" id="<?echo $strPrefix.$intOFPropID?>_IS_REQUIRED_Y" value="Y"<?
	if($arPropInfo['IS_REQUIRED']=="Y")echo " checked"?> title="<?=htmlspecialcharsbx(GetMessage("IB_E_PROP_REQIRED_SHORT")); ?>"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}

function __AddPropCellSort($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="text" size="3" maxlength="10"  name="<?echo $strPrefix.$intOFPropID?>_SORT" id="<?echo $strPrefix.$intOFPropID?>_SORT" value="<?echo $arPropInfo['SORT']?>"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}

function __AddPropCellCode($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="text" size="20" maxlength="50" name="<?echo $strPrefix.$intOFPropID?>_CODE" id="<?echo $strPrefix.$intOFPropID?>_CODE" value="<?echo $arPropInfo['CODE']?>"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}

function __AddPropCellDetail($intOFPropID,$strPrefix,$arPropInfo)
{
	return '<input type="button" title="'.GetMessage("IB_E_PROP_EDIT_TITLE").'" name="'.$strPrefix.$intOFPropID.'_BTN" id="'.$strPrefix.$intOFPropID.'_BTN" value="..." data-propid="'.$intOFPropID.'">';
}

function __AddPropCellDelete($intOFPropID,$strPrefix,$arPropInfo)
{
	$strResult = '&nbsp;';
	if (isset($arPropInfo['SHOW_DEL']) && $arPropInfo['SHOW_DEL'] == 'Y')
		$strResult = '<input type="checkbox" name="'.$strPrefix.$intOFPropID.'_DEL" id="'.$strPrefix.$intOFPropID.'_DEL" value="Y">';
	return $strResult;
}

function __AddPropRow($intOFPropID,$strPrefix,$arPropInfo)
{
	$strResult = '<tr id="'.$strPrefix.$intOFPropID.'">
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
	return $strResult;
}

$arNewPropInfo = $arDefPropInfo;
ConvProp($arNewPropInfo,$arHiddenPropFields);
$arCellTemplates = array();
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

$arCellAttr = array(4,5,6,9,10);

$bBizproc = Loader::includeModule('bizproc');
$bCatalog = Loader::includeModule('catalog');

$arIBTYPE = CIBlockType::GetByIDLang($type, LANGUAGE_ID);

if($arIBTYPE!==false):

$strWarning="";
$bVarsFromForm = false;

if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit")
	&& strlen($_POST["Update"]) > 0
	&& !isset($_POST["propedit"])
)
{
	$DB->StartTransaction();

	$arPICTURE = $_FILES["PICTURE"];
	$arPICTURE["del"] = ${"PICTURE_del"};
	$arPICTURE["MODULE_ID"] = "iblock";

	if ($VERSION != 2) $VERSION = 1;
	if ($RSS_ACTIVE != "Y") $RSS_ACTIVE = "N";
	if ($RSS_FILE_ACTIVE != "Y") $RSS_FILE_ACTIVE = "N";
	if ($RSS_YANDEX_ACTIVE != "Y") $RSS_YANDEX_ACTIVE = "N";

	$ib = new CIBlock();
	$arFields = array(
		"ACTIVE"=>$ACTIVE,
		"NAME"=>$NAME,
		"CODE"=>$CODE,
		"LIST_PAGE_URL"=>$LIST_PAGE_URL,
		"DETAIL_PAGE_URL"=>$DETAIL_PAGE_URL,
		"CANONICAL_PAGE_URL"=>$CANONICAL_PAGE_URL,
		"INDEX_ELEMENT"=>$INDEX_ELEMENT,
		"IBLOCK_TYPE_ID"=>$type,
		"LID"=>$LID,
		"SORT"=>$_POST['SORT'],
		"PICTURE"=>$arPICTURE,
		"DESCRIPTION"=>$DESCRIPTION,
		"DESCRIPTION_TYPE"=>$DESCRIPTION_TYPE,
		"EDIT_FILE_BEFORE"=>$EDIT_FILE_BEFORE,
		"EDIT_FILE_AFTER"=>$EDIT_FILE_AFTER,
		"WORKFLOW"=>$WF_TYPE=="WF"? "Y": "N",
		"BIZPROC"=>$WF_TYPE=="BP"? "Y": "N",
		"SECTION_CHOOSER"=>$SECTION_CHOOSER,
		"LIST_MODE"=>$LIST_MODE,
		"FIELDS" => $_REQUEST["FIELDS"],
		//MESSAGES
		"ELEMENTS_NAME"=>$ELEMENTS_NAME,
		"ELEMENT_NAME"=>$ELEMENT_NAME,
		"ELEMENT_ADD"=>$ELEMENT_ADD,
		"ELEMENT_EDIT"=>$ELEMENT_EDIT,
		"ELEMENT_DELETE"=>$ELEMENT_DELETE,
	);

	if($arIBTYPE["SECTIONS"]=="Y")
	{
		$arFields["SECTION_PAGE_URL"]=$SECTION_PAGE_URL;
		$arFields["INDEX_SECTION"]=$INDEX_SECTION;
		//MESSAGES
		$arFields["SECTIONS_NAME"]=$SECTIONS_NAME;
		$arFields["SECTION_NAME"]=$SECTION_NAME;
		$arFields["SECTION_ADD"]=$SECTION_ADD;
		$arFields["SECTION_EDIT"]=$SECTION_EDIT;
		$arFields["SECTION_DELETE"]=$SECTION_DELETE;
	}

	if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
		$arFields["XML_ID"] = $_POST["XML_ID"];

	if($arIBTYPE["IN_RSS"]=="Y")
	{
		$arFields = array_merge($arFields, array(
			"RSS_ACTIVE"=>$RSS_ACTIVE,
			"RSS_FILE_ACTIVE"=>$RSS_FILE_ACTIVE,
			"RSS_YANDEX_ACTIVE"=>$RSS_YANDEX_ACTIVE,
			"RSS_FILE_LIMIT"=>$RSS_FILE_LIMIT,
			"RSS_FILE_DAYS"=>$RSS_FILE_DAYS,
			"RSS_TTL"=>$RSS_TTL)
			);
	}

	if(CIBlockRights::UserHasRightTo($ID, $ID, "iblock_rights_edit"))
	{
		$arFields["RIGHTS_MODE"] = $RIGHTS_MODE;
		if($arFields["RIGHTS_MODE"] == "E")
		{
			if(is_array($_POST["RIGHTS"]))
				$arFields["RIGHTS"] = CIBlockRights::Post2Array($_POST["RIGHTS"]);
			elseif(is_array($_POST["GROUP"]))
				$arFields["GROUP_ID"] = $_POST["GROUP"];
			else
				$arFields["RIGHTS"] = array();
		}
		else
		{
			$arFields["GROUP_ID"] = $GROUP;
		}
	}

	//Assembly properties for check followed by add/update

	$ibp = new CIBlockProperty();
	$arProperties = array();
	if($ID > 0)
	{
		$props = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $ID, "CHECK_PERMISSIONS" => "N"));
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
				if(!$res)
				{
					$strWarning .= GetMessage("IB_E_PROPERTY_ERROR").": ".$ibp->LAST_ERROR."<br>";
					$bVarsFromForm = true;
				}
			}
			$arProperties[$p["ID"]] = $arProperty;
		}
	}

	$intPropCount = intval($_POST['IBLOCK_PROPERTY_COUNT']);
	for($i=0; $i<$intPropCount; $i++)
	{
		$arProperty = GetPropertyInfo($strPREFIX_IB_PROPERTY, 'n'.$i, true, $arHiddenPropFields);
		if (!is_array($arProperty))
			continue;
		$res = $ibp->CheckFields($arProperty, false, true);
		if(!$res)
		{
			$strWarning .= $ibp->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}

		$arProperties["n".$i] = $arProperty;
	}

	$bDublicate = false;
	$arDublicateCodes = array();
	$arPropertyCodes = array();
	$bSectionProperty = false;
	foreach($arProperties as $i => $arProperty)
	{
		if($arProperty["SECTION_PROPERTY"] === "N")
			$bSectionProperty = true;
		if($arProperty["SMART_FILTER"] === "Y")
			$bSectionProperty = true;
		if ('' != $arProperty['CODE'])
		{
			$strPropertyCode = strtoupper($arProperty['CODE']);
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

	if (is_array($_POST["IPROPERTY_TEMPLATES"]))
	{
		$SECTION_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_NAME"]);
		$SECTION_DETAIL_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_NAME"]);
		$ELEMENT_PREVIEW_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_NAME"]);
		$ELEMENT_DETAIL_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_NAME"]);

		$arFields["IPROPERTY_TEMPLATES"] = array(
			"SECTION_META_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_META_TITLE"]["TEMPLATE"],
			"SECTION_META_KEYWORDS" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_META_KEYWORDS"]["TEMPLATE"],
			"SECTION_META_DESCRIPTION" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_META_DESCRIPTION"]["TEMPLATE"],
			"SECTION_PAGE_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_PAGE_TITLE"]["TEMPLATE"],
			"ELEMENT_META_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_TITLE"]["TEMPLATE"],
			"ELEMENT_META_KEYWORDS" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_KEYWORDS"]["TEMPLATE"],
			"ELEMENT_META_DESCRIPTION" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_DESCRIPTION"]["TEMPLATE"],
			"ELEMENT_PAGE_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PAGE_TITLE"]["TEMPLATE"],
			"SECTION_PICTURE_FILE_ALT" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_ALT"]["TEMPLATE"],
			"SECTION_PICTURE_FILE_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_TITLE"]["TEMPLATE"],
			"SECTION_PICTURE_FILE_NAME" => $SECTION_PICTURE_FILE_NAME,
			"SECTION_DETAIL_PICTURE_FILE_ALT" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_ALT"]["TEMPLATE"],
			"SECTION_DETAIL_PICTURE_FILE_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_TITLE"]["TEMPLATE"],
			"SECTION_DETAIL_PICTURE_FILE_NAME" => $SECTION_DETAIL_PICTURE_FILE_NAME,
			"ELEMENT_PREVIEW_PICTURE_FILE_ALT" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]["TEMPLATE"],
			"ELEMENT_PREVIEW_PICTURE_FILE_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]["TEMPLATE"],
			"ELEMENT_PREVIEW_PICTURE_FILE_NAME" => $ELEMENT_PREVIEW_PICTURE_FILE_NAME,
			"ELEMENT_DETAIL_PICTURE_FILE_ALT" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]["TEMPLATE"],
			"ELEMENT_DETAIL_PICTURE_FILE_TITLE" => $_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]["TEMPLATE"],
			"ELEMENT_DETAIL_PICTURE_FILE_NAME" => $ELEMENT_DETAIL_PICTURE_FILE_NAME,
		);
	}

	$bCreateRecord = $ID <= 0;

	if(!$bVarsFromForm)
	{
		$res_log["NAME"] = $NAME;
		if($ID>0)
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
			$arFields["VERSION"]=$VERSION;
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

		if(!$res)
		{
			$strWarning .= $ib->LAST_ERROR."<br>";
			$bVarsFromForm = true;
		}
		else
		{
			// RSS agent creation
			if ($RSS_FILE_ACTIVE == "Y")
			{
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock");
				CAgent::AddAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock", "N", IntVal($RSS_TTL)*60*60, "", "Y");
			}
			else
			{
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", false);", "iblock");
			}

			if ($RSS_YANDEX_ACTIVE == "Y")
			{
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock");
				CAgent::AddAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock", "N", IntVal($RSS_TTL)*60*60, "", "Y");
			}
			else
			{
				CAgent::RemoveAgent("CIBlockRSS::PreGenerateRSS(".$ID.", true);", "iblock");
			}

			if ($_POST["IPROPERTY_CLEAR_VALUES"] === "Y")
			{
				$ipropValues = new \Bitrix\Iblock\InheritedProperty\IblockValues($ID);
				$ipropValues->clearValues();
			}

			/********************/
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
							$strWarning .= GetMessage("IB_E_PROPERTY_ERROR").": ".$ibp->LAST_ERROR."<br>";
							$bVarsFromForm = true;
						}
					}
				}
				else
				{
					$PropID = (int)$ibp->Add($arProperty);
					if($PropID <= 0)
					{
						$strWarning .= $ibp->LAST_ERROR."<br>";
						$bVarsFromForm = true;
					}
				}
			}
			/*******************************************/

			if(!CIBlockSectionPropertyLink::HasIBlockLinks($ID))
				CIBlockSectionPropertyLink::DeleteByIBlock($ID);

			if(!$bVarsFromForm && $arIBTYPE["IN_RSS"]=="Y")
			{
				CIBlockRSS::Delete($ID);
				$arNodesRSS = CIBlockRSS::GetRSSNodes();
				foreach($arNodesRSS as $key => $val)
				{
					if(strlen(${"RSS_NODE_VALUE_".$key}) > 0)
						CIBlockRSS::Add($ID, $val, ${"RSS_NODE_VALUE_".$key});
				}
			}

			if(!$bVarsFromForm && !$bCreateRecord && $bBizproc)
			{
				$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$ID));
				foreach ($arWorkflowTemplates as $t)
				{
					$create_bizproc = (array_key_exists("create_bizproc_".$t["ID"], $_REQUEST) && $_REQUEST["create_bizproc_".$t["ID"]] == "Y");
					$edit_bizproc = (array_key_exists("edit_bizproc_".$t["ID"], $_REQUEST) && $_REQUEST["edit_bizproc_".$t["ID"]] == "Y");

					$create_bizproc1 = (($t["AUTO_EXECUTE"] & 1) != 0);
					$edit_bizproc1 = (($t["AUTO_EXECUTE"] & 2) != 0);

					if ($create_bizproc != $create_bizproc1 || $edit_bizproc != $edit_bizproc1)
					{
						CBPDocument::UpdateWorkflowTemplate(
							$t["ID"],
							array("iblock", "CIBlockDocument", "iblock_".$ID),
							array(
								"AUTO_EXECUTE" => (($create_bizproc ? 1 : 0) | ($edit_bizproc ? 2 : 0))
							),
							$arErrorsTmp
						);
					}
				}
			}

			if (!$bVarsFromForm && $bCatalog)
			{
				$boolNeedAgent = false;
				$boolFlag = true;
				$obCatalog = new CCatalog();
				$arCatalog = $obCatalog->GetByIDExt($ID);

				if (!isset($IS_CATALOG) || ('Y' != $IS_CATALOG && 'N' != $IS_CATALOG))
				{
					$bVarsFromForm = true;
					$strWarning .= GetMessage('IB_E_OF_ERR_IS_CATALOG').'<br>';
				}
				if (!isset($SUBSCRIPTION) || ('Y' != $SUBSCRIPTION && 'N' != $SUBSCRIPTION))
				{
					$bVarsFromForm = true;
					$strWarning .= GetMessage('IB_E_OF_ERR_SUBSCRIPTION').'<br>';
				}

				if (!$bVarsFromForm)
				{
					if (('Y' == $IS_CATALOG) || ('Y' == $SUBSCRIPTION))
					{
						if (!isset($YANDEX_EXPORT) || ('Y' != $YANDEX_EXPORT && 'N' != $YANDEX_EXPORT))
						{
							$bVarsFromForm = true;
							$strWarning .= GetMessage('IB_E_OF_ERR_YANDEX_EXPORT').'<br>';
						}
						if (!isset($VAT_ID))
						{
							$bVarsFromForm = true;
							$strWarning .= GetMessage('IB_E_OF_ERR_VAT_ID').'<br>';
						}
					}
				}
				if (!isset($USED_SKU) || ('Y' != $USED_SKU && 'N' != $USED_SKU))
				{
					$bVarsFromForm = true;
					$strWarning .= GetMessage('IB_E_OF_ERR_USED_SKU').'<br>';
				}

				if (!$bVarsFromForm)
				{
					$IS_CATALOG = ('Y' == $IS_CATALOG ? 'Y' : 'N');
					$SUBSCRIPTION = ('Y' == $SUBSCRIPTION ? 'Y' : 'N');
					if (!(CBXFeatures::IsFeatureEnabled('SaleRecurring')))
						$SUBSCRIPTION = 'N';
					$YANDEX_EXPORT = ('Y' == $YANDEX_EXPORT ? 'Y' : 'N');
					$VAT_ID = (0 < intval($VAT_ID) ? intval($VAT_ID) : 0);

					//$SKU_RIGHTS = ('Y' == $SKU_RIGHTS ? 'Y' : 'N');
					$SKU_RIGHTS = 'N';

					if (is_array($arCatalog) && $arCatalog['CATALOG_TYPE'] == 'O')
					{
						$IS_CATALOG = 'Y';
						$arOffersFields = array(
							'IBLOCK_ID' => $ID,
							'SUBSCRIPTION' => $SUBSCRIPTION,
							'YANDEX_EXPORT' => $YANDEX_EXPORT,
							'VAT_ID' => $VAT_ID,
						);
						$boolFlag = $obCatalog->Update($ID,$arOffersFields);
						if (!$boolFlag)
						{
							$bVarsFromForm = true;
							if ($ex = $APPLICATION->GetException())
							{
								$strWarning .= $ex->GetString()."<br>";
							}
						}
						else
						{
							$boolNeedAgent = ($YANDEX_EXPORT != $arCatalog['YANDEX_EXPORT']);
						}
					}
					else
					{
						$arOffersFields = array(
							'IBLOCK_ID' => $ID,
							'SUBSCRIPTION' => $SUBSCRIPTION,
							'YANDEX_EXPORT' => $YANDEX_EXPORT,
							'VAT_ID' => $VAT_ID,
						);

						if (false == $arCatalog || 'P' == $arCatalog['CATALOG_TYPE'])
						{
							if ($IS_CATALOG == 'Y')
							{
								$boolFlag = $obCatalog->Add($arOffersFields);
							}
							if ($boolFlag && $arOffersFields['YANDEX_EXPORT'] == 'Y')
								$boolNeedAgent = true;
						}
						else
						{
							if ($IS_CATALOG == 'Y' || $SUBSCRIPTION == 'Y')
							{
								$boolFlag = $obCatalog->Update($ID,$arOffersFields);
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
							if($ex = $APPLICATION->GetException())
							{
								$strWarning .= $ex->GetString()."<br>";
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
									$strWarning .= GetMessage('IB_E_OF_ERR_OFFERS_IS_ABSENT').'<br>';
								}
								elseif (CATALOG_NEW_OFFERS_IBLOCK_NEED == $OF_IBLOCK_ID)
								{
									$arCheckIBlockType = CheckIBlockTypeID($OF_IBLOCK_TYPE_ID,$OF_NEW_IBLOCK_TYPE_ID,$OF_CREATE_IBLOCK_TYPE_ID);
									if (!$arCheckIBlockType)
									{
										$bVarsFromForm = true;
										$strWarning .= GetMessage('IB_E_OF_ERR_IBLOCK_TYPE_UNKNOWN_ERR').'<br>';
									}
									else
									{
										if ('ERROR' == $arCheckIBlockType['RESULT'])
										{
											$bVarsFromForm = true;
											$strWarning .= $arCheckIBlockType['MESSAGE'].'<br>';
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
										$arOfPropList = array();
										for ($i = 0; $i < $intCountOFProp; $i++)
										{
											$arOFProperty = GetPropertyInfo($strPREFIX_OF_PROPERTY, 'n'.$i, true, $arHiddenPropFields);
											if (false !== $arOFProperty)
											{
												$res = $ibp->CheckFields($arOFProperty, false, true);
												if(!$res)
												{
													$strWarning .= GetMessage("IB_E_PROPERTY_ERROR").": ".$ibp->LAST_ERROR."<br>";
													$bVarsFromForm = true;
												}
												$arOfPropList[] = $arOFProperty;
											}
										}
									}

									if (!$bVarsFromForm)
									{
										$arOffersFields = array(
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
										);
										$arOffersFields["RIGHTS_MODE"] = $RIGHTS_MODE;
										if ($arOffersFields["RIGHTS_MODE"] == "E")
										{
											if(is_array($_POST["RIGHTS"]))
											{
												$arOffersFields["RIGHTS"] = array();
												$s_rights = new CIBlockRights($ID);
												foreach($s_rights->GetRights() as $k=>$v)
													$arOffersFields["RIGHTS"]["n".$k] = $v;
											}
											elseif(is_array($_POST["GROUP"]))
											{
												$arGroup = $_POST["GROUP"];
												foreach ($arGroup as &$value)
												if ('U' == $value)
													$value = 'W';

												$arOffersFields["GROUP_ID"] = $arGroup;
											}
											else
											{
												$arOffersFields["RIGHTS"] = array();
											}
										}
										else
										{
											$arGroup = $GROUP;
											foreach ($arGroup as &$value)
												if ('U' == $value)
													$value = 'W';

											$arOffersFields["GROUP_ID"] = $arGroup;
										}
										$arLogFields = array();
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
										if (false == $mxOffersID)
										{
											$strWarning .= $obIBlock->LAST_ERROR."<br>";
											$bVarsFromForm = true;
										}
										else
										{
											$res_log = array();
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
										foreach ($arOfPropList as $arOFProperty)
										{
											$arOFProperty['IBLOCK_ID'] = $OF_IBLOCK_ID;
											$PropID = $ibp->Add($arOFProperty);
											if (intval($PropID)<=0)
											{
												$strWarning .= $ibp->LAST_ERROR."<br>";
												$bVarsFromForm = true;
											}
										}
									}
								}
								else
								{
									if (CIBlockRights::UserHasRightTo($OF_IBLOCK_ID, $OF_IBLOCK_ID, "iblock_edit"))
									{
										$arOffersFields = array();
										$arOffersFields['LID'] = $LID;

										if ('Y' == $SKU_RIGHTS)
										{
											$arOffersFields["RIGHTS_MODE"] = $RIGHTS_MODE;
											if ($arOffersFields["RIGHTS_MODE"] == "E")
											{
												if(is_array($_POST["RIGHTS"]))
												{
													$arOffersFields["RIGHTS"] = array();
													$s_rights = new CIBlockRights($ID);
													foreach($s_rights->GetRights() as $k=>$v)
														$arOffersFields["RIGHTS"]["n".$k] = $v;
												}
												elseif(is_array($_POST["GROUP"]))
												{
													$arGroup = $_POST["GROUP"];
													foreach ($arGroup as &$value)
													if ('U' == $value)
														$value = 'W';

													$arOffersFields["GROUP_ID"] = $arGroup;
												}
												else
												{
													$arOffersFields["RIGHTS"] = array();
												}
											}
											else
											{
												$arGroup = $GROUP;
												foreach ($arGroup as &$value)
													if ('U' == $value)
														$value = 'W';

												$arOffersFields["GROUP_ID"] = $arGroup;
											}
										}
										$arLogFields = array();
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
										if (false == $mxOffersID)
										{
											$strWarning .= $obIBlock->LAST_ERROR."<br>";
											$bVarsFromForm = true;
										}
										else
										{
											$res_log = array();
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
										$strWarning .= str_replace(array('#ID#'),array($OF_IBLOCK_ID),GetMessage('IB_E_RIGHTS_IBLOCK_ACCESS_DENIED')).'<br>';
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
										$arOffersFields = array(
											'IBLOCK_ID' => $OF_IBLOCK_ID,
											'PRODUCT_IBLOCK_ID' => $ID,
											'SKU_PROPERTY_ID' => $intSKUPropID
										);
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
									if($ex = $APPLICATION->GetException())
									{
										$strWarning .= $ex->GetString()."<br>";
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
										if (true == is_object($ex))
										{
											$strWarning .= $ex->GetString()."<br>";
										}
									}
								}
							}
						}
					}
				}

				if (!$boolFlag)
				{
					if($ex = $APPLICATION->GetException())
					{
						$strWarning .= $ex->GetString()."<br>";
						$bVarsFromForm = true;
					}
				}
			}

			if(!$bVarsFromForm)
			{
				if(
					$bBizproc
					&& $_REQUEST['BIZ_PROC_ADD_DEFAULT_TEMPLATES']=='Y'
					&& CBPDocument::GetNumberOfWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$ID))<=0
					&& $arFields["BIZPROC"] == "Y"
				)
					CBPDocument::AddDefaultWorkflowTemplates(array("iblock", "CIBlockDocument", "iblock_".$ID));

				$DB->Commit();

				//Check if index needed
				CIBlock::CheckForIndexes($ID);

				if ($bCatalog)
				{
					if (isset($boolNeedAgent) && $boolNeedAgent == true)
					{
						$intYandexExport = CCatalog::GetList(array(),array('YANDEX_EXPORT' => 'Y'),array());
						CAgent::RemoveAgent("CCatalog::PreGenerateXML(\"yandex\");", "catalog");
						if (0 < $intYandexExport)
							CAgent::AddAgent("CCatalog::PreGenerateXML(\"yandex\");", "catalog", "N", IntVal(COption::GetOptionString("catalog", "yandex_xml_period", "24"))*60*60, "", "Y");
					}
				}

				$ob = new CAutoSave();
				if(strlen($apply)<=0)
				{
					if(strlen($_REQUEST["return_url"])>0)
						LocalRedirect($_REQUEST["return_url"]);
					else
						LocalRedirect("/bitrix/admin/iblock_admin.php?type=".$type."&lang=".LANG."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N"));
				}
				LocalRedirect("/bitrix/admin/iblock_edit.php?type=".$type."&tabControl_active_tab=".urlencode($tabControl_active_tab)."&lang=".LANG."&ID=".$ID."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N").(strlen($_REQUEST["return_url"])>0? "&return_url=".urlencode($_REQUEST["return_url"]): ""));
			}
		}
	}

	$DB->Rollback();
}

if(
	check_bitrix_sessid()
	&& $_SERVER["REQUEST_METHOD"] ==="GET"
	&& intval($_REQUEST["delete_bizproc_template"]) > 0
	&& $bBizproc
	&& CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit")
)
{
	$arErrorTmp = array();
	CBPDocument::DeleteWorkflowTemplate($_REQUEST["delete_bizproc_template"], array("iblock", "CIBlockDocument", "iblock_".$ID), $arErrorTmp);
	if (count($arErrorTmp) > 0)
	{
		foreach ($arErrorTmp as $e)
			$strWarning .= $e["message"]."<br />";
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPageParam("", Array("delete_bizproc_template", "sessid")));
		die();
	}
}


if($ID>0)
	$APPLICATION->SetTitle(GetMessage("IB_E_EDIT_TITLE", array("#IBLOCK_TYPE#"=>$arIBTYPE["NAME"])));
else
	$APPLICATION->SetTitle(GetMessage("IB_E_NEW_TITLE", array("#IBLOCK_TYPE#"=>$arIBTYPE["NAME"])));


ClearVars("str_");
$str_ACTIVE="Y";
$str_WORKFLOW="N";
$str_BIZPROC="N";
$str_SECTION_CHOOSER="L";
$str_LIST_MODE="";
$str_INDEX_ELEMENT="Y";
$str_INDEX_SECTION="Y";
$str_PROPERTY_FILE_TYPE = "jpg, gif, bmp, png, jpeg";
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

$str_IS_CATALOG = 'N';
$str_SUBSCRIPTION = 'N';
$str_YANDEX_EXPORT = 'N';
$str_VAT_ID = 0;
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

$str_IPROPERTY_TEMPLATES = array();

$str_SKU_RIGHTS = 'N';

$boolRecurringError = false;

$bCurrentBPDisabled = true;

$ib_result = CIBlock::GetList(array(), array("=ID" => $ID, "CHECK_PERMISSIONS"=>"N"));
if(!$ib_result->ExtractFields("str_"))
{
	$ID = 0;
}
else
{
	$bCurrentBPDisabled = ($str_BIZPROC!='Y');

	$str_LID = Array();
	$db_LID = CIBlock::GetSite($ID);
	while($ar_LID = $db_LID->Fetch())
		$str_LID[] = $ar_LID["LID"];

	$ipropTemlates = new \Bitrix\Iblock\InheritedProperty\IblockTemplates($ID);
	$str_IPROPERTY_TEMPLATES = $ipropTemlates->findTemplates();
	$str_IPROPERTY_TEMPLATES["SECTION_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["SECTION_PICTURE_FILE_NAME"]);
	$str_IPROPERTY_TEMPLATES["SECTION_DETAIL_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["SECTION_DETAIL_PICTURE_FILE_NAME"]);
	$str_IPROPERTY_TEMPLATES["ELEMENT_PREVIEW_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["ELEMENT_PREVIEW_PICTURE_FILE_NAME"]);
	$str_IPROPERTY_TEMPLATES["ELEMENT_DETAIL_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["ELEMENT_DETAIL_PICTURE_FILE_NAME"]);

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
			if (in_array($arCatalog['CATALOG_TYPE'],array('P','X')))
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

endif; //$arIBTYPE!==false

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($arIBTYPE!==false):

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
	$aMenu = array(
		array(
			"TEXT"=>GetMessage("IBLOCK_BACK_TO_ADMIN"),
			"LINK"=>'/bitrix/admin/iblock_admin.php?lang='.LANGUAGE_ID.'&type='.urlencode($type).'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N"),
			"ICON"=>"btn_list",
		)
	);

$context = new CAdminContextMenu($aMenu);
$context->Show();

$u = new CAdminPopupEx(
	"mnu_LIST_PAGE_URL",
	CIBlockParameters::GetPathTemplateMenuItems("LIST", "__SetUrlVar", "mnu_LIST_PAGE_URL", "LIST_PAGE_URL"),
	array("zIndex" => 2000)
);
$u->Show();

$u = new CAdminPopupEx(
	"mnu_SECTION_PAGE_URL",
	CIBlockParameters::GetPathTemplateMenuItems("SECTION", "__SetUrlVar", "mnu_SECTION_PAGE_URL", "SECTION_PAGE_URL"),
	array("zIndex" => 2000)
);
$u->Show();

$arItems = CIBlockParameters::GetPathTemplateMenuItems("DETAIL", "__SetUrlVar", "mnu_DETAIL_PAGE_URL", "DETAIL_PAGE_URL");
if($str_CATALOG_TYPE == 'O')
{
	$arItems[] = array("SEPARATOR" => true);
	$arItems[] = array(
		"TEXT" => GetMessage("IB_E_URL_PRODUCT_ID"),
		"TITLE" => "#PRODUCT_URL# - ".GetMessage("IB_E_URL_PRODUCT_ID"),
		"ONCLICK" => "__SetUrlVar('#PRODUCT_URL#', 'mnu_DETAIL_PAGE_URL', 'DETAIL_PAGE_URL')",
	);
}
$u = new CAdminPopupEx(
	"mnu_DETAIL_PAGE_URL",
	$arItems,
	array("zIndex" => 2000)
);
$u->Show();

$arItems = CIBlockParameters::GetPathTemplateMenuItems("DETAIL", "__SetUrlVar", "mnu_CANONICAL_PAGE_URL", "CANONICAL_PAGE_URL");
array_unshift($arItems, array("SEPARATOR" => true));
array_unshift($arItems, array(
	"TEXT" => "https://",
	"TITLE" => "",
	"ONCLICK" => "__SetUrlVar('https://', 'mnu_CANONICAL_PAGE_URL', 'CANONICAL_PAGE_URL')",
));
array_unshift($arItems, array(
	"TEXT" => "http://",
	"TITLE" => "",
	"ONCLICK" => "__SetUrlVar('http://', 'mnu_CANONICAL_PAGE_URL', 'CANONICAL_PAGE_URL')",
));
$u = new CAdminPopupEx(
	"mnu_CANONICAL_PAGE_URL",
	$arItems,
	array("zIndex" => 2000)
);
$u->Show();
?>
<script>
	var InheritedPropertiesTemplates = new JCInheritedPropertiesTemplates(
		'frm',
		'/bitrix/admin/iblock_templates.ajax.php?ENTITY_TYPE=B&ENTITY_ID=<?echo intval($ID)?>'
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
<script type="text/javascript">
var CellTPL = [],
	CellAttr = [];
<?
foreach ($arCellTemplates as $key => $value)
{
	?>CellTPL[<? echo $key; ?>] = '<? echo $value; ?>';
<?
}
foreach ($arCellAttr as $key => $value)
{
	?>CellAttr[<? echo $key; ?>] = '<? echo $value; ?>';
<?
}
?>
</script>

<form method="POST" name="frm" id="frm" action="/bitrix/admin/iblock_edit.php?type=<?echo htmlspecialcharsbx($type)?>&amp;lang=<?echo LANG?>&amp;admin=<?echo ($_REQUEST["admin"]=="Y"? "Y": "N")?>"  ENCTYPE="multipart/form-data">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<?if($bBizproc && $bCurrentBPDisabled):?>
<input type="hidden" name="BIZ_PROC_ADD_DEFAULT_TEMPLATES" value="Y">
<?endif?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if(strlen($_REQUEST["return_url"])>0):?><input type="hidden" name="return_url" value="<?=htmlspecialcharsbx($_REQUEST["return_url"])?>"><?endif?>
<?CAdminMessage::ShowOldStyleError($strWarning);?>
<?
$bTab3 = ($arIBTYPE["IN_RSS"]=="Y");
$bWorkflow = Loader::includeModule("workflow");
$bBizprocTab = $bBizproc && $str_BIZPROC == "Y";

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("IB_E_TAB2"),
		"ICON" => "iblock",
		"TITLE" => GetMessage("IB_E_TAB2_T"),
	),
	array(
		"DIV" => "edit10",
		"TAB" => GetMessage("IB_E_TAB10"),
		"ICON" => "iblock_iprops",
		"TITLE" => GetMessage("IB_E_TAB10_T"),
	),
	array(
		"DIV" => "edit6",
		"TAB" => GetMessage("IB_E_TAB6"),
		"ICON" => "iblock_fields",
		"TITLE" => GetMessage("IB_E_TAB6_T"),
	),
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("IB_E_TAB3"),
		"ICON" => "iblock_props",
		"TITLE" => GetMessage("IB_E_TAB3_T"),
	),
	array(
		"DIV" => "edit8",
		"TAB" => GetMessage("IB_E_TAB8"),
		"ICON" => "section_fields",
		"TITLE" => GetMessage("IB_E_TAB8_T"),
	),
);
if($bTab3)
{
	$aTabs[] = array(
		"DIV" => "edit3",
		"TAB" => GetMessage("IB_E_TAB7"),
		"ICON" => "iblock_rss",
		"TITLE" => GetMessage("IB_E_TAB7_T"),
	);
}
if($bCatalog)
{
	$aTabs[] = array(
		"DIV" => "edit9",
		"TAB" => GetMessage("IB_E_TAB9"),
		"ICON" => "iblock",
		"TITLE" => GetMessage("IB_E_TAB9_T"),
	);
}
if(CIBlockRights::UserHasRightTo($ID, $ID, "iblock_rights_edit"))
{
	$aTabs[] = array(
		"DIV" => "edit4",
		"TAB" => GetMessage("IB_E_TAB4"),
		"ICON"=>"iblock_access",
		"TITLE"=>GetMessage("IB_E_TAB4_T"),
	);
}
$aTabs[] = array(
	"DIV" => "edit5",
	"TAB" => GetMessage("IB_E_TAB5"),
	"ICON" => "iblock",
	"TITLE" => GetMessage("IB_E_TAB5_T"),
);
if ($bBizprocTab)
{
	$aTabs[] = array(
		"DIV" => "edit7",
		"TAB" => GetMessage("IB_E_TAB7_BP"),
		"ICON" => "iblock",
		"TITLE" => GetMessage("IB_E_TAB7_BP"),
	);
}
$aTabs[] = array(
	"DIV" => "log",
	"TAB" => GetMessage("IB_E_TAB_LOG"),
	"ICON" => "iblock",
	"TITLE" => GetMessage("IB_E_TAB_LOG_TITLE"),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<?if($ID>0):?>
		<tr>
			<td width="40%"><?=GetMessage("IB_E_ID")?>:</td>
			<td width="60%"><?echo $str_ID?></td>
		</tr>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><?=GetMessage("IB_E_PROPERTY_STORAGE")?></td>
			<td width="60%">
				<input type="hidden" name="VERSION" value="<?=$str_VERSION?>">
				<?if($str_VERSION==1)echo GetMessage("IB_E_COMMON_STORAGE")?>
				<?if($str_VERSION==2)echo GetMessage("IB_E_SEPARATE_STORAGE")?>
				<br><a href="iblock_convert.php?lang=<?=LANG?>&amp;IBLOCK_ID=<?echo $str_ID?>"><?=$str_LAST_CONV_ELEMENT>0?"<span class=\"required\">".GetMessage("IB_E_CONVERT_CONTINUE"):GetMessage("IB_E_CONVERT_START")."</span>"?></a>
			</td>
		</tr>
		<tr>
			<td ><?echo GetMessage("IB_E_LAST_UPDATE")?></td>
			<td><?echo $str_TIMESTAMP_X?></td>
		</tr>
	<? else: ?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><?=GetMessage("IB_E_PROPERTY_STORAGE")?></td>
			<td width="60%">
				<label><input type="radio" name="VERSION" value="1" <?if($str_VERSION==1)echo " checked"?>><?=GetMessage("IB_E_COMMON_STORAGE")?></label><br>
				<label><input type="radio" name="VERSION" value="2" <?if($str_VERSION==2)echo " checked"?>><?=GetMessage("IB_E_SEPARATE_STORAGE")?></label>
			</td>
		</tr>
	<? endif; ?>
	<tr>
		<td><label for="ACTIVE"><?echo GetMessage("IB_E_ACTIVE")?>:</label></td>
		<td>
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" id="ACTIVE" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>>
			<span style="display:none;"><input type="submit" name="save" value="Y" style="width:0px;height:0px"></span>
		</td>
	</tr>
	<tr>
		<td width="40%"><? echo GetMessage("IB_E_CODE")?>:</td>
		<td width="60%">
			<input type="text" name="CODE" size="50" maxlength="50" value="<?echo $str_CODE?>" >
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo GetMessage("IB_E_SITES")?></td>
		<td>
			<?
		if ('O' == $str_CATALOG_TYPE)
		{
			?><div class="adm-list"><?
			$by="sort";
			$order="asc";
			$l = CLang::GetList($by, $order);
			$arLidValue = $str_LID;
			if(!is_array($arLidValue))
				$arLidValue = array($arLidValue);
			while($l_arr = $l->Fetch())
			{
				?><div class="adm-list-item">
					<div class="adm-list-control"><input type="checkbox" name="LID_SHOW[]" value="<? echo htmlspecialcharsex($l_arr["LID"]); ?>" id="<? echo htmlspecialcharsex($l_arr["LID"]);?>" class="typecheckbox"<? echo (in_array($l_arr["LID"], $arLidValue) ? ' checked':''); ?> disabled></div>
					<div class="adm-list-label"><label for="<? echo htmlspecialcharsex($l_arr["LID"]); ?>">[<? echo htmlspecialcharsex($l_arr["LID"]); ?>]&nbsp;<? echo htmlspecialcharsex($l_arr["NAME"]); ?></label></div>
				</div><?
			}
			echo "<br>".str_replace('#LINK#','/bitrix/admin/iblock_edit.php?type='.$str_PRODUCT_IBLOCK_TYPE_ID.'&lang='.LANGUAGE_ID.'&ID='.$str_PRODUCT_IBLOCK_ID.'&admin=Y',GetMessage('IB_E_OF_SITES'));

			foreach ($arLidValue as &$strLid)
			{
				?><input type="hidden" name="LID[]" value="<? echo htmlspecialcharsex($strLid); ?>"><?
			}
			?></div><?
		}
		else
		{
			?><?=CLang::SelectBoxMulti("LID", $str_LID);?><?
		}
		?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td ><? echo GetMessage("IB_E_NAME")?>:</td>
		<td>
			<input type="text" name="NAME" size="55" maxlength="255" value="<?echo $str_NAME?>">
		</td>
	</tr>
	<tr>
		<td ><? echo GetMessage("IB_E_SORT")?>:</td>
		<td>
			<input type="text" name="SORT" size="10" maxlength="10" value="<?echo $str_SORT?>">
		</td>
	</tr>
	<?if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y"):?>
		<tr>
			<td ><?echo GetMessage("IB_E_XML_ID")?>:</td>
			<td>
				<input type="text" name="XML_ID"  size="55" maxlength="255" value="<?echo $str_XML_ID?>">
			</td>
		</tr>
	<?endif?>
	<tr>
		<td ><?echo GetMessage("IB_E_LIST_PAGE_URL")?></td>
		<td>
			<input type="text" name="LIST_PAGE_URL" id="LIST_PAGE_URL" size="55" maxlength="255" value="<?echo $str_LIST_PAGE_URL?>">
			<input type="button" id="mnu_LIST_PAGE_URL" value='...'>
		</td>
	</tr>
	<?if($arIBTYPE["SECTIONS"]=="Y"):?>
		<tr>
			<td ><?echo GetMessage("IB_E_SECTION_PAGE_URL")?></td>
			<td>
				<input type="text" name="SECTION_PAGE_URL" id="SECTION_PAGE_URL" size="55" maxlength="255" value="<?echo $str_SECTION_PAGE_URL?>">
				<input type="button" id="mnu_SECTION_PAGE_URL" value='...'>
			</td>
		</tr>
	<?endif?>
	<tr>
		<td ><?echo GetMessage("IB_E_DETAIL_PAGE_URL")?></td>
		<td>
			<input type="text" name="DETAIL_PAGE_URL" id="DETAIL_PAGE_URL" size="55" maxlength="255" value="<?echo $str_DETAIL_PAGE_URL?>">
			<input type="button" id="mnu_DETAIL_PAGE_URL" value='...'>
		</td>
	</tr>
	<tr>
		<td ><?echo GetMessage("IB_E_CANONICAL_PAGE_URL")?></td>
		<td>
			<input type="text" name="CANONICAL_PAGE_URL" id="CANONICAL_PAGE_URL" size="55" maxlength="255" value="<?echo $str_CANONICAL_PAGE_URL?>">
			<input type="button" id="mnu_CANONICAL_PAGE_URL" value='...'>
		</td>
	</tr>
	<?if($arIBTYPE["SECTIONS"]=="Y"):?>
		<tr>
			<td><label for="INDEX_SECTION"><?echo GetMessage("IB_E_INDEX_SECTION")?></label></td>
			<td>
				<input type="hidden" name="INDEX_SECTION" value="N">
				<input type="checkbox" id="INDEX_SECTION" name="INDEX_SECTION" value="Y"<?if($str_INDEX_SECTION=="Y")echo " checked"?>>
			</td>
		</tr>
	<?endif?>
	<tr>
		<td><label for="INDEX_ELEMENT"><?echo GetMessage("IB_E_INDEX_ELEMENT")?></label></td>
		<td>
			<input type="hidden" name="INDEX_ELEMENT" value="N">
			<input type="checkbox" id="INDEX_ELEMENT" name="INDEX_ELEMENT" value="Y"<?if($str_INDEX_ELEMENT=="Y")echo " checked"?>>
		</td>
	</tr>
	<?if($bWorkflow && $bBizproc):?>
		<tr>
			<td><?echo GetMessage("IB_E_WF_TYPE")?></td>
			<td>
				<select name="WF_TYPE">
					<option value="N" <? if($str_WORKFLOW != "Y" && $str_BIZPROC !="Y") echo "selected"; ?>><?echo GetMessage("IB_E_WF_TYPE_NONE")?></option>
					<option value="WF" <?if($str_WORKFLOW=="Y")echo "selected"?>><?echo GetMessage("IB_E_WF_TYPE_WORKFLOW")?></option>
					<option value="BP" <?if($str_BIZPROC=="Y")echo "selected"?>><?echo GetMessage("IB_E_WF_TYPE_BIZPROC")?></option>
				</select>
			</td>
		</tr>
	<?elseif($bWorkflow && !$bBizproc):?>
		<tr>
			<td><label for="WF_TYPE"><?echo GetMessage("IB_E_WORKFLOW")?></label></td>
			<td>
				<input type="hidden" name="WF_TYPE" value="N">
				<input type="checkbox" id="WF_TYPE" name="WF_TYPE" value="WF"<?if($str_WORKFLOW=="Y")echo " checked"?>>
			</td>
		</tr>
	<?elseif($bBizproc && !$bWorkflow):?>
		<tr>
			<td><label for="WF_TYPE"><?echo GetMessage("IB_E_BIZPROC")?></label></td>
			<td>
				<input type="hidden" name="WF_TYPE" value="N">
				<input type="checkbox" id="WF_TYPE" name="WF_TYPE" value="BP"<?if($str_BIZPROC=="Y")echo " checked"?>>
			</td>
		</tr>
	<?endif?>
	<tr>
		<td><?echo GetMessage("IB_E_SECTION_CHOOSER")?>:</td>
		<td>
			<select name="SECTION_CHOOSER">
			<option value="L"<?if($str_SECTION_CHOOSER=="L")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_LIST")?></option>
			<option value="D"<?if($str_SECTION_CHOOSER=="D")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_DROPDOWNS")?></option>
			<option value="P"<?if($str_SECTION_CHOOSER=="P")echo " selected"?>><?echo GetMessage("IB_E_SECTION_CHOOSER_POPUP")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_LIST_MODE")?>:</td>
		<td>
			<select name="LIST_MODE">
			<option value=""><?echo GetMessage("IB_E_LIST_MODE_GLOBAL")?></option>
			<option value="S"<?if($str_LIST_MODE=="S") echo " selected"?>><?echo GetMessage("IB_E_LIST_MODE_SECTIONS")?></option>
			<option value="C"<?if($str_LIST_MODE=="C") echo " selected"?>><?echo GetMessage("IB_E_LIST_MODE_COMBINED")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
		<?
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick",
				"arResultDest" => array("FORM_NAME" => "frm", "FORM_ELEMENT_NAME" => "EDIT_FILE_BEFORE"),
				"arPath" => array("PATH" => GetDirPath($str_EDIT_FILE_BEFORE)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?echo GetMessage("IB_E_FILE_BEFORE")?></td>
		<td><input type="text" name="EDIT_FILE_BEFORE" size="55"  maxlength="255" value="<?echo $str_EDIT_FILE_BEFORE?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick()"></td>
	</tr>
	<tr>
		<td>
		<?
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick2",
				"arResultDest" => array("FORM_NAME" => "frm", "FORM_ELEMENT_NAME" => "EDIT_FILE_AFTER"),
				"arPath" => array("PATH" => GetDirPath($str_EDIT_FILE_AFTER)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?echo GetMessage("IB_E_FILE_AFTER")?></td>
		<td><input type="text" name="EDIT_FILE_AFTER" size="55"  maxlength="255" value="<?echo $str_EDIT_FILE_AFTER?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick2()"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_DESCRIPTION")?></td>
	</tr>
	<tr class="adm-detail-file-row">
		<td class="adm-detail-valign-top"><?echo GetMessage("IB_E_PICTURE")?></td>
		<td>
			<?echo CFileInput::Show('PICTURE', $str_PICTURE, array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => array(
					"W" => COption::GetOptionString("iblock", "detail_image_size"),
					"H" => COption::GetOptionString("iblock", "detail_image_size"),
				),
			), array(
				'upload' => true,
				'medialib' => false,
				'file_dialog' => false,
				'cloud' => false,
				'del' => true,
				'description' => false,
			));?>
		</td>
	</tr>
	<?if(COption::GetOptionString("iblock", "use_htmledit", "Y")=="Y" && Loader::includeModule("fileman")):?>
		<tr>
			<td colspan="2" align="center">
				<?CFileMan::AddHTMLEditorFrame(
					"DESCRIPTION",
					$str_DESCRIPTION,
					"DESCRIPTION_TYPE",
					$str_DESCRIPTION_TYPE,
					array(
						'height' => 450,
						'width' => '100%'
					)
				);?>
			</td>
		</tr>
	<?else:?>
		<tr>
			<td ><?echo GetMessage("IB_E_DESCRIPTION_TYPE")?></td>
			<td >
				<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE1" value="text"<?if($str_DESCRIPTION_TYPE!="html")echo " checked"?>><label for="DESCRIPTION_TYPE1"> <?echo GetMessage("IB_E_DESCRIPTION_TYPE_TEXT")?></label> /
				<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE2" value="html"<?if($str_DESCRIPTION_TYPE=="html")echo " checked"?>><label for="DESCRIPTION_TYPE2"> <?echo GetMessage("IB_E_DESCRIPTION_TYPE_HTML")?></label>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<textarea cols="60" rows="15" name="DESCRIPTION" style="width:100%;"><?echo $str_DESCRIPTION?></textarea>
			</td>
		</tr>
	<?endif?>
<?
$tabControl->BeginNextTab();
?>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("IB_E_SEO_FOR_SECTIONS")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_META_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_META_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_META_KEYWORDS")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_META_DESCRIPTION")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_SECTION_PAGE_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("IB_E_SEO_FOR_ELEMENTS")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_META_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_META_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_META_KEYWORDS")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_META_DESCRIPTION")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_PAGE_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("IB_E_SEO_FOR_SECTIONS_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("IB_E_SEO_FOR_SECTIONS_DETAIL_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "SECTION_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("IB_E_SEO_FOR_ELEMENTS_PREVIEW_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_PREVIEW_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_PREVIEW_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_PREVIEW_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("IB_E_SEO_FOR_ELEMENTS_DETAIL_PICTURE")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_ALT")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_TITLE")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo GetMessage("IB_E_SEO_FILE_NAME")?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($ID, "ELEMENT_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E")?></td>
</tr>
<tr class="heading">
	<td colspan="2"><?echo GetMessage("IB_E_SEO_MANAGEMENT")?></td>
</tr>
<tr>
	<td width="40%"><label for="IPROPERTY_CLEAR_VALUES"><?echo GetMessage("IB_E_SEO_CLEAR_VALUES")?></label></td>
	<td width="60%">
		<input type="checkbox" id="IPROPERTY_CLEAR_VALUES" name="IPROPERTY_CLEAR_VALUES" value="Y" />
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr><td colspan="2"><table border="0" cellspacing="0" cellpadding="0" class="internal" style="width:690px; margin: 0 auto;">
		<tr class="heading">
			<td width="125" style="text-align: left !important;"><?echo GetMessage("IB_E_FIELD_NAME")?></td>
			<td width="40"><?echo GetMessage("IB_E_FIELD_IS_REQUIRED")?></td>
			<td width="450" style="text-align: left !important;"><?echo GetMessage("IB_E_FIELD_DEFAULT_VALUE")?></td>
		</tr>
		<?
		if($bVarsFromForm)
			$arFields = $_REQUEST["FIELDS"];
		else
			$arFields = CIBlock::GetFields($ID);
		$arDefFields = CIBlock::GetFieldsDefaults();
		foreach($arDefFields as $FIELD_ID => $arField):
			if ($arField["VISIBLE"] == "N")
				continue;
			if(preg_match("/^(SECTION_|LOG_)/", $FIELD_ID))
				continue;
			?>
		<tr <?
			if (
				$FIELD_ID === "PREVIEW_PICTURE"
				|| $FIELD_ID === "PREVIEW_TEXT"
				|| $FIELD_ID === "DETAIL_PICTURE"
				|| $FIELD_ID === "DETAIL_TEXT"
				|| $FIELD_ID === "CODE"
			)
				echo  'class="adm-detail-valign-top"';
		?>>
			<td><?echo $arDefFields[$FIELD_ID]["NAME"]?></td>
			<td style="text-align:center">
				<input type="hidden" value="N" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]">
				<input type="checkbox" value="Y" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" <?if($arFields[$FIELD_ID]["IS_REQUIRED"]==="Y" || $arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "checked"?> <?if($arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "disabled"?>>
			</td>
			<td>
			<?
			switch($FIELD_ID)
			{
			case "IBLOCK_SECTION":
				?>
				<input type="hidden" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]" value="N">
				<input type="checkbox"
					name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]"
					id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]"
					value="Y"
					<?if ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["KEEP_IBLOCK_SECTION_ID"] === "Y") echo 'checked="checked"'?>
				/><label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][KEEP_IBLOCK_SECTION_ID]">
				<?echo GetMessage("IB_E_FIELD_IBLOCK_SECTION_KEEP_IBLOCK_SECTION_ID")?>
				</label>
				<?
				break;
			case "ACTIVE":
				?>
				<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
					<option value="Y" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="Y") echo "selected"?>><?echo GetMessage("MAIN_YES")?></option>
					<option value="N" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="N") echo "selected"?>><?echo GetMessage("MAIN_NO")?></option>
				</select>
				<?
				break;
			case "ACTIVE_FROM":
				?>
				<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
					<option value="" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_EMPTY")?></option>
					<option value="=now" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="=now") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_NOW")?></option>
					<option value="=today" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="=today") echo "selected"?>><?echo GetMessage("IB_E_FIELD_ACTIVE_FROM_TODAY")?></option>
				</select>
				<?
				break;
			case "ACTIVE_TO":
				?>
				<label for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]"><?echo GetMessage("IB_E_FIELD_ACTIVE_TO")?></label>
				<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="5">
				<?
				break;
			case "NAME":
				?>
				<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="60">
				<?
				break;
			case "SORT":
				?>
				<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="hidden" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>">
				<?
				break;
			case "DETAIL_TEXT_TYPE":
			case "PREVIEW_TEXT_TYPE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
						<option value="text" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="text") echo "selected"?>>text</option>
						<option value="html" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="html") echo "selected"?>>html</option>
					</select>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							name="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							<?
							if($arFields[$FIELD_ID."_ALLOW_CHANGE"]["DEFAULT_VALUE"]!=="N")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						><?echo GetMessage("IB_E_FIELD_TEXT_TYPE_ALLOW_CHANGE")?></label>
					</div>
				</div>
				</div>
				<?
				break;
			case "DETAIL_TEXT":
			case "PREVIEW_TEXT":
				?>
				<textarea name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" rows="5" cols="47"><?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?></textarea>
				<?
				break;
			case "PREVIEW_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"
						><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_FROM_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
						><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_DELETE_WITH_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
						><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_UPDATE_WITH_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?echo $FIELD_ID?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?echo $FIELD_ID?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?echo $FIELD_ID?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							array(
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							)
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?
				break;
			case "DETAIL_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?echo $FIELD_ID?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?echo $FIELD_ID?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?echo $FIELD_ID?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							array(
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							)
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?
				break;
			case "CODE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UNIQUE"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"
						><?echo GetMessage("IB_E_FIELD_CODE_UNIQUE")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"
						><?echo GetMessage("IB_E_FIELD_EL_TRANSLITERATION")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_LEN")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_LEN"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_CASE")?>:&nbsp;<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]">
						<option value=""><?echo GetMessage("IB_E_FIELD_TRANS_CASE_LEAVE")?>
						</option>
						<option value="L" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="L") echo "selected"?>>
							<?echo GetMessage("IB_E_FIELD_TRANS_CASE_LOWER")?>
						</option>
						<option value="U" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="U") echo "selected"?>>
							<?echo GetMessage("IB_E_FIELD_TRANS_CASE_UPPER")?>
						</option>
					</select>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_SPACE")?>&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_SPACE"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_OTHER")?>&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_OTHER"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_EAT"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
						><?echo GetMessage("IB_E_FIELD_TRANS_EAT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_GOOGLE"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
						><?echo GetMessage("IB_E_FIELD_EL_TRANS_USE_SERVICE")?></label>
					</div>
				</div>
				</div>
				<?
				break;
			default:
				?>
				<input type="hidden" value="" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]">&nbsp;
				<?
				break;
			}
			?>
			</td>
		</tr>
		<?endforeach?>
	</table> </td> </tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td>
			<script type="text/javascript">
			var obIBProps = new JCIBlockProperty({
				'PREFIX': '<? echo $strPREFIX_IB_PROPERTY ?>',
				'FORM_ID': 'frm',
				'TABLE_PROP_ID': 'ib_prop_list',
				'PROP_COUNT_ID': 'INT_IBLOCK_PROPERTY_COUNT',
				'IBLOCK_ID': <? echo $ID; ?>,
				'LANG': '<? echo LANGUAGE_ID; ?>',
				'TITLE': '<? echo GetMessageJS('IB_E_IB_PROPERTY_DETAIL'); ?>',
				'OBJ': 'obIBProps'
			});
			obIBProps.SetCells(CellTPL,8,CellAttr);
			</script>
			<table class="internal" style="margin: 0 auto" id="ib_prop_list">
				<tr class="heading">
					<td>ID</td>
					<td><?echo GetMessage("IB_E_PROP_NAME_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_TYPE_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_ACTIVE_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_MULT_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_REQIRED_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_SORT_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_CODE_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_MODIFY_SHORT"); ?></td>
					<td><?echo GetMessage("IB_E_PROP_DELETE_SHORT"); ?></td>
				</tr>
				<?
				$arPropList = array();
				if (0 < $ID)
				{
					$arPropLinks = CIBlockSectionPropertyLink::GetArray($ID, 0);
					$rsProps =  CIBlockProperty::GetList(array("SORT"=>"ASC",'ID' => 'ASC'), array("IBLOCK_ID" => $ID, "CHECK_PERMISSIONS" => "N"));
					while ($arProp = $rsProps->Fetch())
					{
						if ('L' == $arProp['PROPERTY_TYPE'])
						{
							$arProp['VALUES'] = array();
							$rsLists = CIBlockProperty::GetPropertyEnum($arProp['ID'],array('SORT' => 'ASC','ID' => 'ASC'));
							while($res = $rsLists->Fetch())
							{
								$arProp['VALUES'][$res["ID"]] = array(
									'ID' => $res["ID"],
									'VALUE' => $res["VALUE"],
									'SORT' => $res['SORT'],
									'XML_ID' => $res["XML_ID"],
									'DEF' => $res['DEF'],
								);
							}
						}

						if(array_key_exists($arProp["ID"], $arPropLinks))
						{
							$arProp["SECTION_PROPERTY"] = "Y";
							$arProp["SMART_FILTER"] = $arPropLinks[$arProp["ID"]]["SMART_FILTER"];
							$arProp["DISPLAY_TYPE"] = $arPropLinks[$arProp["ID"]]["DISPLAY_TYPE"];
							$arProp["DISPLAY_EXPANDED"] = $arPropLinks[$arProp["ID"]]["DISPLAY_EXPANDED"];
							$arProp["FILTER_HINT"] = $arPropLinks[$arProp["ID"]]["FILTER_HINT"];
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
				$intPropCount = intval($_POST['IBLOCK_PROPERTY_COUNT']);
				if (0 >= $intPropCount)
					$intPropCount = PROPERTY_EMPTY_ROW_SIZE;
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
				<input class="adm-btn-big" onclick="obIBProps.addPropRow();" type="button" value="<? echo GetMessage('IB_E_SHOW_ADD_PROP_ROW')?>" title="<? echo GetMessage('IB_E_SHOW_ADD_PROP_ROW_DESCR')?>">
			</div>
			<input type="hidden" name="IBLOCK_PROPERTY_COUNT" id="INT_IBLOCK_PROPERTY_COUNT" value="<? echo $intPropNumber; ?>">
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr><td colspan="2"><table border="0" cellspacing="0" cellpadding="0" class="internal" style="width:690px; margin: 0 auto;">
		<tr class="heading">
			<td width="125" style="text-align: left !important;"><?echo GetMessage("IB_E_SECTION_FIELD_NAME")?></td>
			<td width="40"><?echo GetMessage("IB_E_SECTION_FIELD_IS_REQUIRED")?></td>
			<td width="450" style="text-align: left !important;"><?echo GetMessage("IB_E_SECTION_FIELD_DEFAULT_VALUE")?></td>
		</tr>
		<?
		if($bVarsFromForm)
			$arFields = $_REQUEST["FIELDS"];
		else
			$arFields = CIBlock::GetFields($ID);
		$arDefFields = CIBlock::GetFieldsDefaults();
		foreach($arDefFields as $FIELD_ID => $arField):
			if ($arField["VISIBLE"] == "N")
				continue;
			if(!preg_match("/^SECTION_/", $FIELD_ID)) continue;
			?>
		<tr <?
			if (
				$FIELD_ID === "SECTION_DESCRIPTION"
				|| $FIELD_ID === "SECTION_PICTURE"
				|| $FIELD_ID === "SECTION_DETAIL_PICTURE"
				|| $FIELD_ID === "SECTION_CODE"
			)
				echo  'class="adm-detail-valign-top"';
		?>>
			<td><?echo $arDefFields[$FIELD_ID]["NAME"]?></td>
			<td style="text-align:center">
				<input type="hidden" value="N" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]">
				<input type="checkbox" value="Y" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" <?if($arFields[$FIELD_ID]["IS_REQUIRED"]==="Y" || $arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "checked"?> <?if($arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "disabled"?>>
			</td>
			<td>
			<?
			switch($FIELD_ID)
			{
			case "SECTION_NAME":
				?>
				<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?>" size="60">
				<?
				break;
			case "SECTION_DESCRIPTION_TYPE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" height="1">
						<option value="text" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="text") echo "selected"?>>text</option>
						<option value="html" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]==="html") echo "selected"?>>html</option>
					</select>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							name="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
							<?
							if($arFields[$FIELD_ID."_ALLOW_CHANGE"]["DEFAULT_VALUE"]!=="N")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>_ALLOW_CHANGE][DEFAULT_VALUE]"
						><?echo GetMessage("IB_E_FIELD_TEXT_TYPE_ALLOW_CHANGE")?></label>
					</div>
				</div>
				</div>
				<?
				break;
			case "SECTION_DESCRIPTION":
				?>
				<textarea name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]" rows="5" cols="47"><?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"])?></textarea>
				<?
				break;
			case "SECTION_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["FROM_DETAIL"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][FROM_DETAIL]"
						><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_FROM_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][DELETE_WITH_DETAIL]"
						><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_DELETE_WITH_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"]==="Y")
								echo "checked"
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UPDATE_WITH_DETAIL]"
						><?echo GetMessage("IB_E_FIELD_PREVIEW_PICTURE_UPDATE_WITH_DETAIL")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?echo $FIELD_ID?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?echo $FIELD_ID?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?echo $FIELD_ID?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							array(
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							)
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?
				break;
			case "SECTION_DETAIL_PICTURE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][SCALE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_SCALE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WIDTH")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WIDTH]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WIDTH"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_HEIGHT")?>:&nbsp;<input name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][HEIGHT]" type="text" value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["HEIGHT"])?>" size="7">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["IGNORE_ERRORS"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][IGNORE_ERRORS]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_IGNORE_ERRORS")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD_DIV]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="resample"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["METHOD"]==="resample")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][METHOD]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_METHOD")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["SCALE"]==="Y")? 'block': 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_COMPRESSION")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][COMPRESSION]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["COMPRESSION"])?>"
						style="width: 30px"
					>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_FILE")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_FILE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClick".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_FILE_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'jpg,jpeg,png,gif',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_FILE_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE"])?>"
						size="35"
					>&nbsp;<input type="button" value="..." onClick="BtnClick<?echo $FIELD_ID?>()">
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_FILE_ALPHA")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_ALPHA]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_ALPHA"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_FILE_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_FILE"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_FILE_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_FILE_POSITION"]
					);?>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
						><?echo GetMessage("IB_E_FIELD_PICTURE_USE_WATERMARK_TEXT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_WATERMARK_TEXT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT"])?>"
						size="35"
					>
					<?CAdminFileDialog::ShowScript(array(
						"event" => "BtnClickFont".$FIELD_ID,
						"arResultDest" => array("ELEMENT_ID" => "FIELDS_".$FIELD_ID."__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"),
						"arPath" => array("PATH" => GetDirPath(($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"]))),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'ttf',
						"allowAllFiles" => false,
						"SaveConfig" => true,
					));?>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_FONT")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_FONT]"
						id="FIELDS_<?echo $FIELD_ID?>__DEFAULT_VALUE__WATERMARK_TEXT_FONT_"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_FONT"])?>"
						size="35">&nbsp;<input
						type="button"
						value="..."
						onClick="BtnClickFont<?echo $FIELD_ID?>()"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_TEXT_COLOR")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_COLOR"])?>"
						size="7"
					><script>
						function <?echo $FIELD_ID?>WATERMARK_TEXT_COLOR(color)
						{
							BX('FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_COLOR]').value = color.substring(1);
						}
					</script>&nbsp;<input
						type="button"
						value="..."
						onclick="BX.findChildren(this.parentNode, {'tag': 'IMG'}, true)[0].onclick();"
					><span style="float:left;width:1px;height:1px;visibility:hidden;position:absolute;"><?
						$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							array(
								"SHOW_BUTTON" =>"Y",
								"ONSELECT" => $FIELD_ID."WATERMARK_TEXT_COLOR",
							)
						);
					?></span>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_SIZE")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_SIZE]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_SIZE"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_WATERMARK_TEXT"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_PICTURE_WATERMARK_POSITION")?>:&nbsp;<?echo SelectBox(
						"FIELDS[".$FIELD_ID."][DEFAULT_VALUE][WATERMARK_TEXT_POSITION]",
						IBlockGetWatermarkPositions(),
						"",
						$arFields[$FIELD_ID]["DEFAULT_VALUE"]["WATERMARK_TEXT_POSITION"]
					);?>
				</div>
				</div>
				<?
				break;
			case "SECTION_CODE":
				?>
				<div class="adm-list">
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["UNIQUE"]==="Y")
								echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][UNIQUE]"
						><?echo GetMessage("IB_E_FIELD_CODE_UNIQUE")?></label>
					</div>
				</div>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"
							<?
							if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")
								echo "checked";
							?>
							onclick="
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]').style.display =
								BX('SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]').style.display =
								this.checked? 'block': 'none';
							"
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANSLITERATION]"
						><?echo GetMessage("IB_E_FIELD_SEC_TRANSLITERATION")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_LEN")?>:&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_LEN]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_LEN"])?>"
						size="3"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_CASE")?>:&nbsp;<select name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_CASE]">
						<option value=""><?echo GetMessage("IB_E_FIELD_TRANS_CASE_LEAVE")?>
						</option>
						<option value="L" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="L") echo "selected"?>>
							<?echo GetMessage("IB_E_FIELD_TRANS_CASE_LOWER")?>
						</option>
						<option value="U" <?if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_CASE"]==="U") echo "selected"?>>
							<?echo GetMessage("IB_E_FIELD_TRANS_CASE_UPPER")?>
						</option>
					</select>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_SPACE")?>&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_SPACE]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_SPACE"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"
					style="padding-left:16px;display:<?
						if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y") echo 'block'; else echo 'none';
					?>"
				>
					<?echo GetMessage("IB_E_FIELD_TRANS_OTHER")?>&nbsp;<input
						name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_OTHER]"
						type="text"
						value="<?echo htmlspecialcharsbx($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_OTHER"])?>"
						size="2"
					>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="hidden"
							value="N"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
						>
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANS_EAT"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][TRANS_EAT]"
						><?echo GetMessage("IB_E_FIELD_TRANS_EAT")?></label>
					</div>
				</div>
				<div class="adm-list-item"
					id="SETTINGS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
					style="padding-left:16px;display:<?
						echo ($arFields[$FIELD_ID]["DEFAULT_VALUE"]["TRANSLITERATION"]==="Y")? 'block': 'none';
					?>"
				>
					<div class="adm-list-control">
						<input
							type="checkbox"
							value="Y"
							id="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
							name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
							<?
								if($arFields[$FIELD_ID]["DEFAULT_VALUE"]["USE_GOOGLE"]==="Y")
									echo "checked";
							?>
						>
					</div>
					<div class="adm-list-label">
						<label
							for="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE][USE_GOOGLE]"
						><?echo GetMessage("IB_E_FIELD_EL_TRANS_USE_SERVICE")?></label>
					</div>
				</div>
				</div>
				<?
				break;
			default:
				?>
				<input type="hidden" value="" name="FIELDS[<?echo $FIELD_ID?>][DEFAULT_VALUE]">&nbsp;
				<?
				break;
			}
			?>
			</td>
		</tr>
		<?endforeach;?>
	</table> </td> </tr>
<?
if($bTab3):
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td  width="40%"><label for="RSS_ACTIVE"><?echo GetMessage("IB_E_RSS_ACTIVE")?></label></td>
		<td width="60%">
			<input type="hidden" name="RSS_ACTIVE" value="N">
			<input type="checkbox" id="RSS_ACTIVE" name="RSS_ACTIVE" value="Y"<?if($str_RSS_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td ><? echo GetMessage("IB_E_RSS_TTL")?></td>
		<td>
			<input type="text" name="RSS_TTL" size="20"  maxlength="40" value="<?echo $str_RSS_TTL?>">
		</td>
	</tr>
	<tr>
		<td><label for="RSS_FILE_ACTIVE"><?echo GetMessage("IB_E_RSS_FILE_ACTIVE")?></label></td>
		<td>
			<input type="hidden" name="RSS_FILE_ACTIVE" value="N">
			<input type="checkbox" id="RSS_FILE_ACTIVE" name="RSS_FILE_ACTIVE" value="Y"<?if($str_RSS_FILE_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td  ><? echo GetMessage("IB_E_RSS_FILE_LIMIT")?></td>
		<td  >
			<input type="text" name="RSS_FILE_LIMIT"  size="20" maxlength="40" value="<?echo $str_RSS_FILE_LIMIT?>">
		</td>
	</tr>
	<tr>
		<td ><? echo GetMessage("IB_E_RSS_FILE_DAYS")?></td>
		<td>
			<input type="text" name="RSS_FILE_DAYS"  size="20" maxlength="40" value="<?echo $str_RSS_FILE_DAYS?>">
		</td>
	</tr>
	<tr>
		<td><label for="RSS_YANDEX_ACTIVE"><?echo GetMessage("IB_E_RSS_YANDEX_ACTIVE")?></label></td>
		<td>
			<input type="hidden" name="RSS_YANDEX_ACTIVE" value="N">
			<input type="checkbox" id="RSS_YANDEX_ACTIVE" name="RSS_YANDEX_ACTIVE" value="Y"<?if($str_RSS_YANDEX_ACTIVE=="Y")echo " checked"?>>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_RSS_TITLE")?>:</td>
	</tr>
	<tr>
		<td  colspan="2" align="center">
			<table class="internal">
				<tr class="heading">
					<td><?echo GetMessage("IB_E_RSS_FIELD")?></td>
					<td><?echo GetMessage("IB_E_RSS_TEMPL")?></td>
				</tr>
				<?
				$arCurNodesRSS = CIBlockRSS::GetNodeList(IntVal($ID));
				$arNodesRSS = CIBlockRSS::GetRSSNodes();
				foreach($arNodesRSS as $key => $val):
					if($bVarsFromForm)
						$DB->InitTableVarsForEdit("b_iblock_rss", "RSS_", "str_RSS_", "_".$key);
					?>
					<tr>
						<td>
							<input type="text" size="20" readonly maxlength="50" name="RSS_NODE_<?echo $key?>" value="<?echo $val?>">
						</td>
						<td><input type="text" size="20" name="RSS_NODE_VALUE_<?echo $key?>" value="<?echo $arCurNodesRSS[$val]?>"></td>
					</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr>
	<?
endif;
if ($bCatalog)
{
	$arIBlockTypeIDList = array();
	$arIBlockTypeNameList = array();
	$rsIBlockTypes = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
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

	$arIBlockSitesList = array();
	$arIBlockFullInfo = array();

	$rsIBlocks = CIBlock::GetList(array('IBLOCK_TYPE' => 'ASC','NAME' => 'ASC'));
	while ($arIBlock = $rsIBlocks->Fetch())
	{
		if (false == array_key_exists($arIBlock['ID'],$arIBlockSitesList))
		{
			$arLIDList = array();
			$arWithoutLinks = array();
			$rsIBlockSites = CIBlock::GetSite($arIBlock['ID']);
			while ($arIBlockSite = $rsIBlockSites->Fetch())
			{
				$arLIDList[] = $arIBlockSite['LID'];
				$arWithoutLinks[] = htmlspecialcharsbx($arIBlockSite['LID']);
			}
			$arIBlockSitesList[$arIBlock['ID']] = array(
				'SITE_ID' => $arLIDList,
				'WITHOUT_LINKS' => implode(' ',$arWithoutLinks),
			);
		}
		$arIBlockItem = array(
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
		);
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
	<script type="text/javascript">
	BX.message({
		'IB_E_CAT_CONFIRM': '<? echo CUtil::JSEscape(GetMessage('IB_E_CAT_CONFIRM'));?>'
	});
	</script>
	<script type="text/javascript">
	var obOFProps = new JCIBlockProperty({
		'PREFIX': '<? echo $strPREFIX_OF_PROPERTY ?>',
		'FORM_ID': 'frm',
		'TABLE_PROP_ID': 'of_prop_list',
		'PROP_COUNT_ID': 'INT_OFFERS_PROPERTY_COUNT',
		'IBLOCK_ID': 0,
		'LANG': '<? echo LANGUAGE_ID; ?>',
		'TITLE': '<? echo GetMessageJS('IB_E_OF_PROPERTY_DETAIL'); ?>',
		'OBJ': 'obOFProps'
	});

	obOFProps.SetCells(CellTPL,8,CellAttr);
	</script>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_CATALOG_TITLE")?></td>
	</tr>
	<tr>
		<td  width="40%"><label for="IS_CATALOG_Y"><?echo GetMessage("IB_E_IS_CATALOG")?></label></td>
		<td width="60%">
			<input type="hidden" name="IS_CATALOG" id="IS_CATALOG_N" value="N">
			<input type="checkbox" name="IS_CATALOG" id="IS_CATALOG_Y" value="Y"<?if('Y' == $str_IS_CATALOG)echo " checked"?><? if ('O' == $str_CATALOG_TYPE) echo ' disabled="disabled"'; ?> onclick="ib_checkFldActivity(0,'<? echo $str_IS_CATALOG; ?>');">
		</td>
	</tr><?
	if (CBXFeatures::IsFeatureEnabled('SaleRecurring'))
	{
	?><tr>
		<td  width="40%"><label for="IS_CONTENT_Y"><?echo GetMessage("IB_E_IS_CONTENT")?></label></td>
		<td width="60%">
			<input type="hidden" id="IS_CONTENT_N" name="SUBSCRIPTION" value="N">
			<input type="checkbox" id="IS_CONTENT_Y" name="SUBSCRIPTION" value="Y"<?if('Y' == $str_SUBSCRIPTION)echo " checked"?> onclick="ib_checkFldActivity(1,'<? echo $str_IS_CATALOG; ?>')">
		</td>
	</tr><?
	}
	else
	{
		?><input type="hidden" id="IS_CONTENT_N" name="SUBSCRIPTION" value="N"><?
	}
	?><tr>
		<td  width="40%"><label for="YANDEX_EXPORT_Y"><?echo GetMessage("IB_E_YANDEX_EXPORT")?></label></td>
		<td width="60%">
			<input type="hidden" id="YANDEX_EXPORT_N" name="YANDEX_EXPORT" value="N">
			<input type="checkbox" id="YANDEX_EXPORT_Y" name="YANDEX_EXPORT" value="Y"<?if('Y' == $str_YANDEX_EXPORT)echo " checked"?> <? if ('Y' != $str_IS_CATALOG) echo 'disabled="disabled"'; ?>>
		</td>
	</tr>
	<tr>
		<td  width="40%"><label for="VAT_ID"><?echo GetMessage("IB_E_VAT_ID")?></label></td>
		<td width="60%"><?
		$arVATRef = CatalogGetVATArray(array(), true);
		?><?=SelectBoxFromArray('VAT_ID', $arVATRef, $str_VAT_ID, '', ('Y' != $str_IS_CATALOG ? 'disabled="disabled"' : ''));?></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_SKU_TITLE")?></td>
	</tr>
	<input type="hidden" name="CATALOG_TYPE" value="<? echo htmlspecialcharsbx($str_CATALOG_TYPE);?>" id="CATALOG_TYPE">
	<?
	if ('O' == $str_CATALOG_TYPE)
	{
	?>
	<tr>
		<td  width="40%"><?echo GetMessage("IB_E_IS_SKU")?></td>
		<td width="60%"><a href="/bitrix/admin/iblock_edit.php?type=<? echo $str_PRODUCT_IBLOCK_TYPE_ID; ?>&lang=<? echo LANGUAGE_ID; ?>&ID=<? echo $str_PRODUCT_IBLOCK_ID; ?>&admin=Y"><? echo htmlspecialcharsbx($str_PRODUCT_IBLOCK_NAME); ?></a>
		<input type="hidden" id="USED_SKU_N" name="USED_SKU" value="N"></td>
	</tr>
	<?
	}
	else
	{
	?>
	<tr>
		<td  width="40%"><label for="USED_SKU_Y"><?echo GetMessage("IB_E_USED_SKU")?></label></td>
		<td width="60%">
			<input type="hidden" id="USED_SKU_N" name="USED_SKU" value="N">
			<input type="checkbox" id="USED_SKU_Y" name="USED_SKU" value="Y"<?if('Y' == $str_USED_SKU) echo " checked"?> onclick="ib_skumaster(this)">
		</td>
	</tr>
	<tr>
	<td colspan="2">
	<div style="display: <? echo ('Y' == $str_USED_SKU ? 'block' : 'none');?>; width: 100%;" id="SKU-SETTINGS">
		<table style="width: 100%;"><tbody>
		<tr>
		<td  width="40%" class="field-name"><?echo GetMessage("IB_E_OF_IBLOCK_INFO")?></td>
		<td width="60%"><select id="OF_IBLOCK_ID" name="OF_IBLOCK_ID" class="typeselect" onchange="show_add_offers(this);">
			<option value="0" <? echo (0 == $str_OF_IBLOCK_ID ? 'selected' : '');?>><? echo GetMessage('IB_E_OF_IBLOCK_EMPTY')?></option>
			<option value="<? echo CATALOG_NEW_OFFERS_IBLOCK_NEED; ?>" <? echo (CATALOG_NEW_OFFERS_IBLOCK_NEED == $str_OF_IBLOCK_ID ? 'selected' : '');?>><? echo GetMessage('IB_E_OF_IBLOCK_NEW')?></option><?
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
							$arDiffParent = array();
							$arDiffParent = array_diff($value['SITE_ID'],$str_LID);
							$arDiffOffer = array();
							$arDiffOffer = array_diff($str_LID,$value['SITE_ID']);
							if ((false == empty($arDiffParent)) || (false == empty($arDiffOffer)))
							{
								$boolAdd = false;
							}
						}
					}
					if ($boolAdd)
					{
						?><option value="<? echo intval($value['ID']); ?>"<? echo ($value['ID'] == $str_OF_IBLOCK_ID ? ' selected' : ''); ?>><? echo $value['FULL_NAME']; ?></option><?
					}
				}
			}
		?></select>
		</td>
		</tr>
		</tbody></table>
		<?
		/*
		?>
		<div id="offers_rights" style="display: <? echo (0 < intval($str_OF_IBLOCK_ID) ? 'display' : 'none'); ?>; width: 100%; text-align: center;">
			<table style="width: 100%;"><tbody>
			<tr>
			<td  width="40%" class="field-name"><label for="SKU_RIGHTS_Y"><?echo GetMessage("IB_E_OF_RIGHTS"); ?></label></td>
			<td width="60%">
				<input type="hidden" name="SKU_RIGHTS" id="SKU_RIGHTS_N" value="N">
				<input type="checkbox" name="SKU_RIGHTS" id="SKU_RIGHTS_Y" value="Y"<?if('Y' == $str_SKU_RIGHTS) echo " checked"; ?>>
			</td>
			</tr>
			</tbody></table>
			</div>
			<?
			*/
			?>
			<div id="offers_add_info" style="display: <? echo (CATALOG_NEW_OFFERS_IBLOCK_NEED == $str_OF_IBLOCK_ID ? 'display' : 'none'); ?>; width: 100%; text-align: center;"><table style="margin: auto;"><tbody>
			<tr><td style="text-align: right; width: 25%;" class="field-name"><? echo GetMessage('IB_E_OF_PR_TITLE'); ?>:</td><td style="text-align: left; width: 75%;"><input type="text" name="OF_IBLOCK_NAME" value="<?=htmlspecialcharsbx($str_OF_IBLOCK_NAME);?>" style="width: 100%;" /></td></tr>
			<tr><td style="text-align: left; width: 100%;" colspan="2" class="field-name"><input type="radio" value="N" id="OF_CREATE_IBLOCK_TYPE_ID_N" name="OF_CREATE_IBLOCK_TYPE_ID" <? echo ('N' == $str_OF_CREATE_IBLOCK_TYPE_ID ? 'checked="checked"' : '')?> onclick="change_offers_ibtype(this);"><label for="CREATE_OFFERS_TYPE_N"><? echo GetMessage('IB_E_OF_PR_OLD_IBTYPE');?></label></td></tr>
			<tr><td style="text-align: right; width: 25%;" class="field-name"><? echo GetMessage('IB_E_OF_PR_OFFERS_TYPE'); ?>:</td><td style="text-align: left; width: 75%;"><? echo SelectBoxFromArray('OF_IBLOCK_TYPE_ID',array('REFERENCE' => $arIBlockTypeNameList,'REFERENCE_ID' => $arIBlockTypeIDList),$str_OF_IBLOCK_TYPE_ID,'',('N' == $str_OF_CREATE_IBLOCK_TYPE_ID ? '' : 'disabled="disabled"')); ?></td></tr>
			<tr><td style="text-align: left; width: 100%;" colspan="2" class="field-name"><input type="radio" value="Y" id="OF_CREATE_IBLOCK_TYPE_ID_Y" name="OF_CREATE_IBLOCK_TYPE_ID" <? echo ('Y' == $str_OF_CREATE_IBLOCK_TYPE_ID ? 'checked="checked"' : '')?> onclick="change_offers_ibtype(this);"><label for="CREATE_OFFERS_TYPE_Y"><? echo GetMessage('IB_E_OF_PR_OFFERS_NEW_IBTYPE');?></label></td></tr>
			<tr><td style="text-align: right; width: 25%;" class="field-name"><? echo GetMessage('IB_E_OF_PR_OFFERS_NEWTYPE'); ?>:</td><td style="text-align: left; width: 75%;"><input type="text" name="OF_NEW_IBLOCK_TYPE_ID" id="OF_NEW_IBLOCK_TYPE_ID" value="" style="width: 100%;" <? echo ('Y' == $str_OF_CREATE_IBLOCK_TYPE_ID ? '' : 'disabled="disabled"') ?> /></td></tr>
			</tbody></table>
			<div><b><? echo GetMessage('IB_E_OFFERS_PROPERTIES'); ?></b></div>
			<table class="internal" style="text-align: center; margin: auto;" id="of_prop_list">
				<tr class="heading">
					<td>ID</td>
					<td><?echo GetMessage("IB_E_PROP_NAME_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_TYPE_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_ACTIVE_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_MULT_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_REQIRED_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_SORT_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_CODE_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_MODIFY_SHORT")?></td>
					<td><?echo GetMessage("IB_E_PROP_DELETE_SHORT")?></td>
				</tr>
				<?
				$arOFPropList = array();
				if (0 < intval($str_OF_IBLOCK_ID))
				{
					$rsProps = CIBlock::GetProperties($str_OF_IBLOCK_ID, array("SORT"=>"ASC",'ID' => 'ASC'));
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

				$intPropCount = intval($_POST['OFFERS_PROPERTY_COUNT']);
				if (0 >= $intPropCount)
					$intPropCount = PROPERTY_EMPTY_ROW_SIZE;
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
					<input class="adm-btn-big" onclick="obOFProps.addPropRow();" type="button" value="<? echo GetMessage('IB_E_SHOW_ADD_PROP_ROW')?>" title="<? echo GetMessage('IB_E_SHOW_ADD_PROP_ROW_DESCR')?>">
				</div>
				<input type="hidden" name="OFFERS_PROPERTY_COUNT" id="INT_OFFERS_PROPERTY_COUNT" value="<? echo $intPropNumber; ?>">
			</div>
	</div>
	</td>
	</tr>
	<?
	}
	?>
<script type="text/javascript">
	var is_cat = BX('IS_CATALOG_Y'),
		is_cont = BX('IS_CONTENT_Y'),
		is_yand = BX('YANDEX_EXPORT_Y'),
		vat_id = BX('VAT_ID'),
		cat_type =  BX('CATALOG_TYPE'),
		use_sku = BX('USED_SKU_Y'),
		ob_sku_settings = BX('SKU-SETTINGS'),
		ob_offers_add = BX('offers_add_info'),
		ob_of_iblock_type_id = BX('OF_IBLOCK_TYPE_ID'),
		ob_of_new_iblock_type_id = BX('OF_NEW_IBLOCK_TYPE_ID');

	//var ob_sku_rights = BX('offers_rights');

	function ib_checkFldActivity(flag,catalog)
	{
		catalog = (catalog == 'Y' ? 'Y' : 'N');
		if (0 == flag)
		{
			if (undefined != cat_type)
			{
				if ('O' == cat_type.value)
					is_cat.checked = true;
			}
			if (catalog == 'Y' && !is_cat.checked)
			{
				is_cat.checked = !confirm(BX.message('IB_E_CAT_CONFIRM'));
			}
			if (!is_cat.checked)
			{
				if (!!is_cont)
					is_cont.checked = false;
				is_yand.checked = false;
			}
		}
		if (1 == flag)
		{
			if (!!is_cont && is_cont.checked)
				is_cat.checked = true;
		}

		var bActive = is_cat.checked;
		is_yand.disabled = !bActive;
		vat_id.disabled = !bActive;
	}
	function ib_skumaster(obj)
	{
		if (undefined != ob_sku_settings)
		{
			var bActive = obj.checked;
			ob_sku_settings.style.display = (true == bActive ? 'block' : 'none');
		}
	}

	function show_add_offers(obj)
	{
		var value = obj.options[obj.selectedIndex].value;
		if (undefined !== ob_offers_add)
		{
			if (<? echo CATALOG_NEW_OFFERS_IBLOCK_NEED; ?> == value)
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
		if ('Y' == value)
		{
			ob_of_iblock_type_id.disabled = true;
			ob_of_new_iblock_type_id.disabled = false;
		}
		else if ('N' == value)
		{
			ob_of_iblock_type_id.disabled = false;
			ob_of_new_iblock_type_id.disabled = true;
		}
	}
</script>	<?
}

if(CIBlockRights::UserHasRightTo($ID, $ID, "iblock_rights_edit"))
{
	$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("IB_E_RIGHTS_MODE_SECTION_TITLE")?></td>
	</tr>
	<?if($str_RIGHTS_MODE === "E"):?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><label for="RIGHTS_MODE"><?echo GetMessage("IB_E_RIGHTS_MODE")?></label></td>
			<td width="60%">
				<input type="hidden" name="RIGHTS_MODE" value="S">
				<input type="checkbox" id="RIGHTS_MODE" name="RIGHTS_MODE" value="E" checked="checked"><?echo BeginNote(), GetMessage("IB_E_RIGHTS_MODE_NOTE1"), EndNote()?>
			</td>
		</tr>
		<?
		$obIBlockRights = new CIBlockRights($ID);
		IBlockShowRights(
			'iblock',
			$ID,
			$ID,
			GetMessage("IB_E_RIGHTS_SECTION_TITLE"),
			"RIGHTS",
			$obIBlockRights->GetRightsList(),
			$obIBlockRights->GetRights(array("count_overwrited" => true)),
			true
		);
		?>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	<?else:?>
		<tr>
			<td width="40%" class="adm-detail-valign-top"><label for="RIGHTS_MODE"><?echo GetMessage("IB_E_RIGHTS_MODE")?></label></td>
			<td width="60%">
				<input type="hidden" name="RIGHTS_MODE" value="S">
				<input type="checkbox" id="RIGHTS_MODE" name="RIGHTS_MODE" value="E"><?echo BeginNote(), GetMessage("IB_E_RIGHTS_MODE_NOTE2"), EndNote()?>
			</td>
		</tr>
		<?
		if ($bWorkflow && $str_WORKFLOW=="Y") :
			$arPermType = array(
				"D"=>GetMessage("IB_E_ACCESS_D"),
				"R"=>GetMessage("IB_E_ACCESS_R"),
				"S"=>GetMessage("IB_E_ACCESS_S"),
				"U"=>GetMessage("IB_E_ACCESS_U"),
				"W"=>GetMessage("IB_E_ACCESS_W"),
				"X"=>GetMessage("IB_E_ACCESS_X"));
		elseif ($bBizprocTab) :
			$arPermType = array(
				"D"=>GetMessage("IB_E_ACCESS_D"),
				"R"=>GetMessage("IB_E_ACCESS_R"),
				"S"=>GetMessage("IB_E_ACCESS_S"),
				"U"=>GetMessage("IB_E_ACCESS_U2"),
				"W"=>GetMessage("IB_E_ACCESS_W"),
				"X"=>GetMessage("IB_E_ACCESS_X"));
		else :
			$arPermType = array(
				"D"=>GetMessage("IB_E_ACCESS_D"),
				"R"=>GetMessage("IB_E_ACCESS_R"),
				"S"=>GetMessage("IB_E_ACCESS_S"),
				"T"=>GetMessage("IB_E_ACCESS_T"),
				"W"=>GetMessage("IB_E_ACCESS_W"),
				"X"=>GetMessage("IB_E_ACCESS_X"));
		endif;
		$perm = CIBlock::GetGroupPermissions($ID);
		if(!array_key_exists(1, $perm))
			$perm[1] = "X";
		?>
		<tr class="heading">
			<td colspan="2"><?echo GetMessage("IB_E_DEFAULT_ACCESS_TITLE")?></td>
		</tr>
		<tr>
			<td nowrap width="40%"><?echo GetMessage("IB_E_EVERYONE")?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=2&amp;lang=<?=LANGUAGE_ID?>">2</a>]:</td>
			<td width="60%">

					<select name="GROUP[2]" id="group_2">
					<?
					if($bVarsFromForm)
						$strSelected = $GROUP[2];
					else
						$strSelected = $perm[2];
					foreach($arPermType as $key => $val):
					?>
						<option value="<?echo $key?>"<?if($strSelected == $key)echo " selected"?>><?echo htmlspecialcharsex($val)?></option>
					<?endforeach?>
					</select>

					<script type="text/javascript">
					function OnGroupChange(control, message)
					{
						var all = document.getElementById('group_2');
						var msg = document.getElementById(message);
						if(all && all.value >= control.value && control.value != '')
						{
							if(msg) msg.innerHTML = '<?echo CUtil::JSEscape(GetMessage("IB_E_ACCESS_WARNING"))?>';
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
			<td colspan="2"><?echo GetMessage("IB_E_GROUP_ACCESS_TITLE")?></td>
		</tr>
		<?
		$groups = CGroup::GetList($by="sort", $order="asc", Array("ID"=>"~2"));
		while($r = $groups->GetNext()):
			if($bVarsFromForm)
				$strSelected = $GROUP[$r["ID"]];
			else
				$strSelected = $perm[$r["ID"]];

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
			<td nowrap width="40%"><?echo $r["NAME"]?> [<a class="tablebodylink" href="/bitrix/admin/group_edit.php?ID=<?=$r["ID"]?>&lang=<?=LANGUAGE_ID?>"><?=$r["ID"]?></a>]:</td>
			<td width="60%">

					<select name="GROUP[<?echo $r["ID"]?>]" OnChange="OnGroupChange(this, 'spn_group_<?echo $r["ID"]?>');">
						<option value=""><?echo GetMessage("IB_E_DEFAULT_ACCESS")?></option>
					<?
					foreach($arPermType as $key => $val):
					?>
						<option value="<?echo $key?>"<?if($strSelected == $key)echo " selected"?>><?echo htmlspecialcharsex($val)?></option>
					<?endforeach?>
					</select>
					<span id="spn_group_<?echo $r["ID"]?>"></span>
			</td>
		</tr>
		<?endwhile?>
	<?endif?>
	<?
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
		<td width="40%"><?echo GetMessage("IB_E_SECTIONS_NAME")?></td>
		<td width="60%">
			<input type="text" name="SECTIONS_NAME" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["SECTIONS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_SECTION_NAME")?></td>
		<td>
			<input type="text" name="SECTION_NAME" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["SECTION_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_SECTION_ADD")?></td>
		<td>
			<input type="text" name="SECTION_ADD" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["SECTION_ADD"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_SECTION_EDIT")?></td>
		<td>
			<input type="text" name="SECTION_EDIT" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["SECTION_EDIT"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_SECTION_DELETE")?></td>
		<td>
			<input type="text" name="SECTION_DELETE" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["SECTION_DELETE"])?>">
		</td>
	</tr>
	<?endif?>
	<tr>
		<td><?echo GetMessage("IB_E_ELEMENTS_NAME")?></td>
		<td>
			<input type="text" name="ELEMENTS_NAME" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["ELEMENTS_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_ELEMENT_NAME")?></td>
		<td>
			<input type="text" name="ELEMENT_NAME" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["ELEMENT_NAME"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_ELEMENT_ADD")?></td>
		<td>
			<input type="text" name="ELEMENT_ADD" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["ELEMENT_ADD"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_ELEMENT_EDIT")?></td>
		<td>
			<input type="text" name="ELEMENT_EDIT" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["ELEMENT_EDIT"])?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IB_E_ELEMENT_DELETE")?></td>
		<td>
			<input type="text" name="ELEMENT_DELETE" size="40" maxlength="100" value="<?echo htmlspecialcharsbx($arMessages["ELEMENT_DELETE"])?>">
		</td>
	</tr>
	<?
if ($bBizprocTab):
$tabControl->BeginNextTab();

	if (!isset($arWorkflowTemplates))
		$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$ID));
	?>
	<tr>
		<td colspan="2">
			<?if (count($arWorkflowTemplates) > 0):?>
				<table border="0" cellspacing="0" cellpadding="0" class="internal">
					<tr class="heading">
						<td><?echo GetMessage("IB_E_BP_NAME")?></td>
						<td><?echo GetMessage("IB_E_BP_CHANGED")?></td>
						<td><?echo GetMessage("IB_E_BP_AUTORUN")?></td>
					</tr>
					<?
					foreach ($arWorkflowTemplates as $arTemplate)
					{
						?>
						<tr>
							<td>
								<?if(IsModuleInstalled("bizprocdesigner")):?>
									<a href="/bitrix/admin/iblock_bizproc_workflow_edit.php?document_type=iblock_<?= $ID ?>&lang=<?=LANGUAGE_ID?>&ID=<?=$arTemplate["ID"]?>&back_url_list=<?= urlencode($APPLICATION->GetCurPageParam("", array()))?>" target="_blank"><?= $arTemplate["NAME"] ?> [<?=$arTemplate["ID"]?>]</a>
								<?else:?>
									<?= $arTemplate["NAME"] ?>
								<?endif?>
								<br /><?= $arTemplate["DESCRIPTION"] ?></td>
							<td nowrap><?= $arTemplate["MODIFIED"] ?><br />[<a href="user_edit.php?ID=<?= $arTemplate["USER_ID"] ?>"><?= $arTemplate["USER_ID"] ?></a>] <?= $arTemplate["USER"] ?></td>
							<td nowrap>
								<?
									if($bVarsFromForm)
										$checked = $_REQUEST["create_bizproc_".$arTemplate["ID"]] == "Y";
									else
										$checked = ($arTemplate["AUTO_EXECUTE"] & 1) != 0;
								?>
								<label><input type="checkbox" id="id_create_bizproc_<?= $arTemplate["ID"] ?>" name="create_bizproc_<?= $arTemplate["ID"] ?>" value="Y"<?echo $checked? " checked" : ""?>><?echo GetMessage("IB_E_BP_AUTORUN_CREATE")?></label><br />
								<?
									if($bVarsFromForm)
										$checked = $_REQUEST["edit_bizproc_".$arTemplate["ID"]] == "Y";
									else
										$checked = ($arTemplate["AUTO_EXECUTE"] & 2) != 0;
								?>
								<label><input type="checkbox" id="id_edit_bizproc_<?= $arTemplate["ID"] ?>" name="edit_bizproc_<?= $arTemplate["ID"] ?>" value="Y"<?echo $checked? " checked" : ""?>><?echo GetMessage("IB_E_BP_AUTORUN_UPDATE")?></label><br />
							</td>
						</tr>
						<?
					}
					?>
				</table>
				<br>
			<?endif;?>
			<?if(IsModuleInstalled("bizprocdesigner")):?>
			<a href="/bitrix/admin/iblock_bizproc_workflow_admin.php?document_type=iblock_<?= $ID ?>&lang=<?=LANGUAGE_ID?>&back_url_list=<?= urlencode($APPLICATION->GetCurPageParam("", array())) ?>" target="_blank"><?echo GetMessage("IB_E_GOTO_BP")?></a>
			<?endif?>
		</td>
	</tr>
	<?
endif;

$tabControl->BeginNextTab();
	if($bVarsFromForm)
		$arFields = $_REQUEST["FIELDS"];
	else
		$arFields = CIBlock::GetFields($ID);
	$arDefFields = CIBlock::GetFieldsDefaults();
	foreach($arDefFields as $FIELD_ID => $arField):
		if ($arField["VISIBLE"] == "N")
			continue;
		if(!preg_match("/^LOG_/", $FIELD_ID))
			continue;
		?>
		<tr>
			<td width="40%"><label for="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]"><?echo GetMessage("IB_E_".$FIELD_ID)?></label>:</td>
			<td>
				<input type="hidden" value="N" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]">
				<input type="checkbox" value="Y" id="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" name="FIELDS[<?echo $FIELD_ID?>][IS_REQUIRED]" <?if($arFields[$FIELD_ID]["IS_REQUIRED"]==="Y" || $arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "checked"?> <?if($arDefFields[$FIELD_ID]["IS_REQUIRED"]!==false) echo "disabled"?>>
			</td>
		</tr>
	<?endforeach?>
<?
	$tabControl->Buttons(array("disabled"=>false, "back_url"=>'iblock_admin.php?lang='.LANGUAGE_ID.'&type='.urlencode($type).'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N")));
	$tabControl->End();
	?>
</form>

<?else:?>
<br>
<? ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>

<?
endif;

else: //if($arIBTYPE!==false):?>
<br>
<?	ShowError(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));?>

<?
endif;// if($arIBTYPE!==false):

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");