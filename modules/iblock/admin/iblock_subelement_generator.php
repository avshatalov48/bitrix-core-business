<?
/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Highloadblock as HL;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

if(!Main\Loader::includeModule('catalog'))
	die();
Main\Loader::includeModule('fileman');

Main\Page\Asset::getInstance()->addJs('/bitrix/js/catalog/tbl_edit.js');

$arJSDescription = array(
	'js' => '/bitrix/js/iblock/sub_generator.js',
	'css' => '/bitrix/panel/iblock/sub-generator.css',
	'lang' => '/bitrix/modules/iblock/lang/'.LANGUAGE_ID.'/admin/iblock_subelement_generator.php'
);
CJSCore::RegisterExt('iblock_generator', $arJSDescription);
CJSCore::Init(array('iblock_generator', 'file_input'));

define('IB_SEG_ROW_PREFIX','IB_SEG_');

$subIBlockId = intval($_REQUEST["subIBlockId"]);
$subPropValue = intval($_REQUEST["subPropValue"]);
$subTmpId = intval($_REQUEST["subTmpId"]);
$iBlockId = intval($_REQUEST["iBlockId"]);
$findSection = intval($_REQUEST["findSection"]);
$arSKUInfo = CCatalogSKU::GetInfoByOfferIBlock($subIBlockId);
CUtil::decodeURIComponent($_POST['PRODUCT_NAME']);
$parentProductName = trim($_POST['PRODUCT_NAME']);

$useStoreControl = ((string)Main\Config\Option::get('catalog', 'default_use_store_control') == 'Y');

if($arSKUInfo == false)
{
	ShowError("SKU error!");
}

$APPLICATION->SetTitle(GetMessage("IB_SEG_MAIN_TITLE"));

/**
 * @param $intRangeID
 * @param $strPrefix
 * @return string
 */
function __AddCellPriceType($intRangeID, $strPrefix)
{
	$dbCatalogGroups = CCatalogGroup::GetList(array("SORT" => "ASC","NAME" => "ASC","ID" => "ASC"));
	$priceTypeCellOption = '';
	while($arCatalogGroup = $dbCatalogGroups->Fetch())
	{
		$priceTypeCellOption .= "<option value=".$arCatalogGroup['ID'].">".htmlspecialcharsbx($arCatalogGroup["NAME"])."</option>";
	}

	return <<<"PRICETYPECELL"
	<td width="30%">
		<span class="adm-select-wrap">
			<select id="IB_SEG_PRICE_TYPE" class="adm-select" style="width: 169px; max-width: 300px;" name="{$strPrefix}PRICETYPE[{$intRangeID}]" />
				$priceTypeCellOption
			</select>
		</span>
	</td>
PRICETYPECELL;
}

/**
 * @param $intRangeID
 * @param $strPrefix
 * @return string
 */
function __AddCellPrice($intRangeID, $strPrefix)
{
	return <<<"PRICECELL"
	<td width="30%">
		<input type="text"  name="{$strPrefix}PRICE[{$intRangeID}]" />
	</td>
PRICECELL;
}

/**
 * @param $intRangeID
 * @param $strPrefix
 * @return string
 */
function __AddCellCurrency($intRangeID, $strPrefix)
{
	$currencySelectbox = CCurrency::SelectBox("{$strPrefix}CURRENCY[{$intRangeID}]", '', "", true, "", "class=\"adm-select\" style=\"width: 169px;\"");

	return <<<"CURRENCYCELL"
	<td width="30%">
		<span class="adm-select-wrap">
			$currencySelectbox
		</span>
	</td>
CURRENCYCELL;
}

function __showPopup($element_id, $items)
{
	echo
		'<script type="text/javascript">
			top.BX.ready(function(){
				top.BX.bind(top.BX("'.$element_id.'"), "click", function() {
					top.BX.adminShowMenu(this, '.CAdminPopup::PhpToJavaScript($items).');
				});
			});
		</script>';
}
/**
 * @param $intRangeID
 * @param $strPrefix
 * @return string
 */
function __AddRangeRow($intRangeID, $strPrefix)
{
	return '<tr id="'.$strPrefix.$intRangeID.'">'.__AddCellPriceType($intRangeID, $strPrefix).__AddCellPrice($intRangeID, $strPrefix).__AddCellCurrency($intRangeID, $strPrefix).'</tr>';
}

/**
 * @param $arr
 * @param int $index
 * @return array
 */
function arraysCombination (&$arr, $index = 0)
{
	static $line = array();
	static $keys;
	static $max;
	static $results;
	if($index == 0)
	{
		$keys = array_keys($arr);
		$max = count($arr);
		$results = array();
	}
	if($index < $max)
	{
		$values = $arr[$keys[$index]];
		foreach($values as $key => $value)
		{
			$line[$keys[$index]] = $value;
			arraysCombination($arr, $index + 1);
			array_pop($line);
		}
	}
	else
	{
		$results[] = $line;
	}
	if($index == 0)
		return $results;
	return array();
}

$boolHighLoad = null;
$arResult = array();
$arAllProperties = $arAllParentProperties = array();
$arFileProperties = array();
$arFilePropertiesExt = array();
$arDirProperties = array();
$dbIBlockProperty = CIBlockProperty::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("IBLOCK_ID" => $subIBlockId, "ACTIVE" => 'Y'));
while($arIBlockProperty = $dbIBlockProperty->Fetch())
{
	$arIBlockProperty['ID'] = (int)$arIBlockProperty['ID'];
	$propertyType = $arIBlockProperty["PROPERTY_TYPE"];
	$userType = (string)$arIBlockProperty["USER_TYPE"];
	$isMultiply = ($arIBlockProperty["MULTIPLE"] == 'Y');
	$arAllProperties[] = $arIBlockProperty;

	if ('L' != $propertyType && 'F' != $propertyType && !('S' == $propertyType && 'directory' == $userType))
		continue;
	if ('S' == $propertyType && 'directory' == $userType)
	{
		if (!isset($arIBlockProperty['USER_TYPE_SETTINGS']['TABLE_NAME']) || empty($arIBlockProperty['USER_TYPE_SETTINGS']['TABLE_NAME']))
			continue;
		if (null === $boolHighLoad)
			$boolHighLoad = CModule::IncludeModule('highloadblock');
		if (!$boolHighLoad)
			continue;
	}

	if ('F' == $propertyType)
	{
		$arFileProperties[] = $arIBlockProperty;
		$arFilePropertiesExt[$arIBlockProperty['ID']] = $arIBlockProperty;
	}
	elseif ('L' == $propertyType)
	{
		$arIBlockProperty['VALUE'] = array();
		$dbIBlockPropertyEnum = CIBlockPropertyEnum::GetList(array("SORT" => "ASC"), array("PROPERTY_ID" => $arIBlockProperty["ID"]));
		while($arIBlockPropertyEnum = $dbIBlockPropertyEnum->Fetch())
		{
			$arIBlockProperty['VALUE'][] = $arIBlockPropertyEnum;
		}
		if (!empty($arIBlockProperty['VALUE']))
		{
			$arResult[] = $arIBlockProperty;
		}
	}
	else
	{
		$arIBlockProperty['VALUE'] = array();
		$arConvert = array();
		if (isset($arIBlockProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]) && !empty($arIBlockProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]))
		{
			$hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => $arIBlockProperty['USER_TYPE_SETTINGS']['TABLE_NAME'])))->fetch();
			if (!empty($hlblock) && is_array($hlblock))
			{
				$entity = HL\HighloadBlockTable::compileEntity($hlblock);
				$entity_data_class = $entity->getDataClass();
				$fieldsList = $entity_data_class::getEntity()->getFields();
				$order = array();
				if (isset($fieldsList['UF_SORT']))
					$order['UF_SORT'] = 'ASC';
				$order['UF_NAME'] = 'ASC';
				$order['ID'] = 'ASC';
				$rsData = $entity_data_class::getList(array('order' => $order));
				while($arData = $rsData->fetch())
				{
					$arData["VALUE"] = $arData["UF_NAME"];
					$arData["PROPERTY_ID"] = $arIBlockProperty["ID"];
					$arData["SORT"] = (isset($arData['UF_SORT']) ? $arData['UF_SORT'] : $arData['ID']);
					$arIBlockProperty['VALUE'][] = $arData;
					$arConvert[$arData["ID"]] = $arData["UF_XML_ID"];
				}
				unset($order, $fieldsList);
			}
		}
		if (!empty($arIBlockProperty['VALUE']))
		{
			$arResult[] = $arIBlockProperty;
			$arDirProperties[$arIBlockProperty['ID']] = $arIBlockProperty;
			$arDirProperties[$arIBlockProperty['ID']]['CONVERT'] = $arConvert;
		}
	}
}

$dbParentIBlockProperty = CIBlockProperty::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("IBLOCK_ID" => $iBlockId, "ACTIVE" => 'Y'));
while($arParentIBlockProperty = $dbParentIBlockProperty->Fetch())
{
	if($arParentIBlockProperty['PROPERTY_TYPE'] == 'L' || $arParentIBlockProperty['PROPERTY_TYPE'] == 'S')
		$arAllParentProperties[] = $arParentIBlockProperty;
}

$errorMessage = '';

if(!$bReadOnly && check_bitrix_sessid())
{
	$arImageCombinationResult = $arPropertyValueCombinationResult = array();
	if (isset($_FILES['PROP']) && is_array($_FILES['PROP']))
	{
		CFile::ConvertFilesToPost($_FILES['PROP'], $arImageCombinationResult);
		// this code for fill description
		if (!empty($arImageCombinationResult) && is_array($arImageCombinationResult))
		{
			$fileDescription = array();
			if (!empty($_POST['DESCRIPTION_PROP']) && is_array($_POST['DESCRIPTION_PROP']))
				$fileDescription = $_POST['DESCRIPTION_PROP'];
			elseif (!empty($_POST['PROP_descr']) && is_array($_POST['PROP_descr']))
				$fileDescription = $_POST['PROP_descr'];
			if (!empty($fileDescription))
			{
				foreach ($arImageCombinationResult as $fieldCode => $fieldValues)
				{
					if (empty($fieldValues) || !is_array($fieldValues))
						continue;
					if (empty($fileDescription[$fieldCode]))
						continue;
					foreach ($fieldValues as $valueCode => $valueData)
					{
						if (empty($valueData) || !is_array($valueData))
							continue;
						if (!isset($fileDescription[$fieldCode][$valueCode]))
							continue;
						if (array_key_exists('tmp_name', $valueData))
						{
							$arImageCombinationResult[$fieldCode][$valueCode]['description'] = $fileDescription[$fieldCode][$valueCode];
						}
						else
						{
							foreach ($valueData as $valueIndex => $value)
							{
								if (empty($value) || !is_array($value))
									continue;
								if (!isset($fileDescription[$fieldCode][$valueCode][$valueIndex]))
									continue;
								if (!array_key_exists('tmp_name', $value))
									continue;
								$arImageCombinationResult[$fieldCode][$valueCode][$valueIndex]['description'] = $fileDescription[$fieldCode][$valueCode][$valueIndex];
							}
							unset($valueIndex, $value);
						}
					}
					unset($valueCode, $valueData);
				}
				unset($fieldCode, $fieldValues);
			}
		}
	}

	if (isset($_POST["PROP"]) && is_array($_POST["PROP"]))
	{
		foreach($_POST["PROP"] as $propKey => $arTmpProperty)
		{
			$rowId = 0;
			if (is_array($arTmpProperty))
			{
				foreach($arTmpProperty as $eachPropertyValue)
				{
					$arPropertyValueCombinationResult[$rowId][$propKey] = $eachPropertyValue;
					$rowId++;
				}
			}
		}
	}
	$arCombinationResult = $arPropertyValue = $arPriceGroup = array();
	$idNewElement = false;
	$obIBlockElement = new CIBlockElement();
	$arPropertyValues = (isset($_POST["PROPERTY_VALUE"]) && is_array($_POST["PROPERTY_VALUE"])) ? $_POST["PROPERTY_VALUE"] : array();
	$arPropertyChecks = (isset($_POST["PROPERTY_CHECK"]) && is_array($_POST["PROPERTY_CHECK"])) ? $_POST["PROPERTY_CHECK"] : array();
	$title = $_POST["IB_SEG_TITLE"];
	if(trim($title) == '')
		$title = '{=this.property.CML2_LINK.NAME} ';
	if(is_array($_POST['IB_SEG_PRICETYPE']))
	{
		foreach($_POST['IB_SEG_PRICETYPE'] as $key => $priceTypeId)
		{
			$arPriceGroup[$priceTypeId] = array("TYPE" => $_POST['IB_SEG_PRICETYPE'][$key], "PRICE" => $_POST['IB_SEG_PRICE'][$key], "CURRENCY" => $_POST['IB_SEG_CURRENCY'][$key]);
		}
	}
	foreach($arPropertyValues as $propertyId => $arValues)
	{
		if(isset($arPropertyChecks[$propertyId]))
			$arPropertyValue[$propertyId]= array_intersect_key($arValues, $arPropertyChecks[$propertyId]);
	}
	$arCombinationResult = arraysCombination($arPropertyValue);

	if($_POST['AJAX_MODE'] == 'Y')
	{
		$APPLICATION->RestartBuffer();
		foreach($arPropertyValue as &$value)
			foreach($value as &$value2)
				if(!defined("BX_UTF"))
					$value2 = CharsetConverter::ConvertCharset($value2, "utf-8", LANG_CHARSET);

		echo CUtil::PhpToJSObject(array($arPropertyValue));
		exit;
	}
	$dbIBlockElement = CIBlockElement::GetList(array(), array("ID" => $subPropValue));

	$arIBlockElement = $dbIBlockElement->Fetch();

	if(strlen($_POST['save']) > 0)
	{
		$parentElementId = (0 < $subPropValue ? $subPropValue : -$subTmpId);
		$parentElement = new \Bitrix\Iblock\Template\Entity\Element($parentElementId);
		if($parentElementId < 0)
		{
			$arFields = array(
				"NAME" => htmlspecialcharsbx($_POST['PRODUCT_NAME_HIDDEN']),
			);
			$parentElement->setFields($arFields);
		}

		$productData = array(
			'WEIGHT' => $_POST['IB_SEG_WEIGHT'],
			'LENGTH' => $_POST['IB_SEG_BASE_LENGTH'],
			'WIDTH' => $_POST['IB_SEG_BASE_WIDTH'],
			'HEIGHT' => $_POST['IB_SEG_BASE_HEIGHT'],
			'VAT_ID' => $_POST['IB_SEG_VAT_ID'],
			'VAT_INCLUDED' => $_POST['IB_SEG_VAT_INCLUDED'],
			'MEASURE' => $_POST['IB_SEG_MEASURE']
		);
		if (!$useStoreControl)
			$productData['QUANTITY'] = $_POST['IB_SEG_QUANTITY'];

		foreach($arCombinationResult as $arPropertySaveValues)
		{
			$imageRowId = null;
			foreach($arPropertyValueCombinationResult as $keyRow => $propertyValueCombinationResult)
			{
				$compare = true;
				foreach ($arPropertySaveValues as $srcKey => $srcValue)
				{
					if (!isset($propertyValueCombinationResult[$srcKey]) || ($propertyValueCombinationResult[$srcKey] != $srcValue && $propertyValueCombinationResult[$srcKey] != '-1'))
					{
						$compare = false;
						break;
					}
				}
				unset($srcValue, $srcKey);
				if ($compare)
				{
					$imageRowId = $keyRow;
					break;
				}
			}

			$arPropertySaveValues[$arSKUInfo['SKU_PROPERTY_ID']] = $parentElementId;

			if (!empty($arPropertyPopup) && is_array($arPropertyPopup))
			{
				foreach ($arPropertyPopup as $action => $acValue)
				{
					if ($action != 'CODE')
						continue;
					foreach ($arAllProperties as $key => $value)
					{
						if ($value["CODE"] != $acValue["CODE"])
							continue;
						$arReplace['#'.$acValue["CODE"].'#'] = $arPropertySaveValues[$arAllProperties[$key]['ID']];
					}
					unset($key, $value);
				}
				unset($action, $acValue);
			}

			$arIBlockElementAdd = array("NAME" => null, "IBLOCK_ID" => $subIBlockId, "ACTIVE" => "Y");
			if (0 >= $subPropValue)
				$arIBlockElementAdd['TMP_ID'] = $subTmpId;

			if (is_array($arImageCombinationResult) && $imageRowId !== null)
			{
				foreach($arImageCombinationResult as $propertyId => $arImageType)
				{
					if(CFile::CheckImageFile($arImageType[$imageRowId]) == '')
					{
						switch($propertyId)
						{
							case 'DETAIL' :
								$arIBlockElementAdd['DETAIL_PICTURE'] = $arImageType[$imageRowId];
								break;
							case 'ANNOUNCE' :
								$arIBlockElementAdd['PREVIEW_PICTURE'] = $arImageType[$imageRowId];
								break;
							default :
								$arPropertySaveValues[$propertyId] = $arImageType[$imageRowId];
						}
					}
				}
			}

			if (!empty($arDirProperties))
			{
				foreach ($arDirProperties as $arOneConvert)
				{
					if (isset($arPropertySaveValues[$arOneConvert['ID']]))
					{
						$arPropertySaveValues[$arOneConvert['ID']] = $arOneConvert['CONVERT'][$arPropertySaveValues[$arOneConvert['ID']]];
					}
				}
			}
			if ($imageRowId !== null)
			{
				foreach ($arPropertyValueCombinationResult[$imageRowId] as $srcKey => $srcValue)
				{
					if ($srcValue == '-1')
						continue;
					if (!isset($arFilePropertiesExt[$srcKey]) && $srcKey != 'DETAIL' && $srcKey != 'ANNOUNCE')
						continue;
					switch ($srcKey)
					{
						case 'ANNOUNCE':
							$arIBlockElementAdd['PREVIEW_PICTURE'] = CIBlock::makeFileArray(
								$srcValue,
								false
							);
							if ($arIBlockElementAdd['PREVIEW_PICTURE']['error'] == 0)
								$arIBlockElementAdd['PREVIEW_PICTURE']['COPY_FILE'] = 'Y';
							break;
						case 'DETAIL':
							$arIBlockElementAdd['DETAIL_PICTURE'] = CIBlock::makeFileArray(
								$srcValue,
								false
							);
							if ($arIBlockElementAdd['DETAIL_PICTURE']['error'] == 0)
								$arIBlockElementAdd['DETAIL_PICTURE']['COPY_FILE'] = 'Y';
							break;
						default:
							if (is_array($srcValue))
							{
								$arPropertySaveValues[$srcKey] = array();
								foreach ($srcValue as $fileID => $fileValue)
								{
									$arPropertySaveValues[$srcKey][$fileID] = CIBlock::makeFilePropArray(
										$srcValue[$fileID],
										false
									);
								}
							}
							else
							{
								$arPropertySaveValues[$srcKey] = CIBlock::makeFilePropArray(
									$srcValue,
									false
								);
							}
							break;
					}
				}
			}

			$arPropertySaveValues["CML2_LINK"] = $parentElement;
			$arIBlockElementAdd['PROPERTY_VALUES'] = $arPropertySaveValues;
			$sku = new \Bitrix\Iblock\Template\Entity\Element(0);
			$sku->setFields($arIBlockElementAdd);
			$arIBlockElementAdd["NAME"] = htmlspecialcharsback(\Bitrix\Iblock\Template\Engine::process($sku, $title));
			unset($arIBlockElementAdd['PROPERTY_VALUES']["CML2_LINK"]);
			$idNewElement = $obIBlockElement->Add($arIBlockElementAdd, false, true, true);
			if($idNewElement)
			{
				$productData['ID'] = $idNewElement;
				CCatalogProduct::Add($productData, false);
				foreach($arPriceGroup as $price)
					CPrice::Add(array("PRODUCT_ID" => $idNewElement, "CURRENCY" => $price["CURRENCY"], "PRICE" => $price["PRICE"], "CATALOG_GROUP_ID" => $price["TYPE"]));
				$element = new \Bitrix\Iblock\InheritedProperty\ElementValues($subIBlockId, $idNewElement);
				$template = new \Bitrix\Iblock\InheritedProperty\BaseTemplate($element);
				$template->set(array(
					"MY_TEMPLATE" => $title,
				));
			}
			else
			{
				$errorMessage .= $obIBlockElement->LAST_ERROR;
				break;
			}
		}
		unset($productData);

		if($idNewElement)
		{
			?>
			<script type="text/javascript">
				top.BX.closeWait();
				if (!!top.BX.WindowManager.Get())
				{
					top.BX.WindowManager.Get().AllowClose(); top.BX.WindowManager.Get().Close();
					if (!!top.ReloadSubList)
						top.ReloadSubList();
				}
			</script>
			<?
			die();
		}
		if ($ex = $APPLICATION->GetException())
		{
			$errorMessage .= $ex->GetString()."<br>";
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arPropertyPopup = array();
foreach($arResult as $key => $property)
{
	$arPropertyPopup[] = array(
			"TEXT" => htmlspecialcharsbx($property["NAME"]),
			"ONCLICK" => "obPropertyTable.addPropertyTable('".$key."')",
	);
}
if(count($arPropertyPopup) > 0)
	__showPopup("mnu_ADD_PROPERTY",	$arPropertyPopup);

$arPropertyPopupIB1 = array();
foreach($arResult as $key => $property)
{
	$arPropertyPopupIB1[$property["CODE"]] = array(
			"TEXT" => htmlspecialcharsbx($property["NAME"]),
			"ONCLICK" => "obPropertyTable.addPropertyInTitle('{=this.property.".$property["ID"]."}')",
			"CODE" => $property["CODE"],
	);
}
if(!empty($arPropertyPopupIB1))
	__showPopup("IB_SEG_ADD_PROP_IN_TITLE",	$arPropertyPopupIB1);

$arPropertyPopupIB2 = array("NAME" => array(
		"TEXT" => GetMessage("IB_SEG_TITLE"),
		"ONCLICK" => "obPropertyTable.addPropertyInTitle('{=this.property.CML2_LINK.NAME}')",
		"CODE" => 'NAME',
));
foreach($arAllParentProperties as $key => $property)
{
	$arPropertyPopupIB2[$property["CODE"]] = array(
			"TEXT" => htmlspecialcharsbx($property["NAME"]),
			"ONCLICK" => "obPropertyTable.addPropertyInTitle('{=this.property.CML2_LINK.property.".$property["CODE"]."}')",
			"CODE" => $property["CODE"],
	);
}
if(!empty($arPropertyPopupIB2))
	__showPopup("IB_SEG_ADD_PROP_IN_TITLE2", $arPropertyPopupIB2);

if($errorMessage)
{
	CAdminMessage::ShowMessage($errorMessage);
}
else
{
	$arCellTemplates = array();
	$arCellTemplates[] = CUtil::JSEscape(__AddCellPriceType('tmp_xxx', 'PREFIX'));
	$arCellTemplates[] = CUtil::JSEscape(__AddCellPrice('tmp_xxx', 'PREFIX'));
	$arCellTemplates[] = CUtil::JSEscape(__AddCellCurrency('tmp_xxx', 'PREFIX'));

	$aTabs = array(
		array("DIV" => "edit", "TAB" => GetMessage("IB_SEG_TAB_TITLE")),
	);

	CAdminMessage::ShowMessage($errorMessage);
	?>
	<form enctype="multipart/form-data" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="iblock_generator_form" id="iblock_generator_form">
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID; ?>">
	<input type="hidden" name="subIBlockId" value="<?echo $subIBlockId?>">
	<input type="hidden" name="subPropValue" value="<?echo $subPropValue?>">
	<input type="hidden" name="iBlockId" value="<?echo $iBlockId?>">
	<input type="hidden" name="findSection" value="<?echo $findSection?>">
	<input type="hidden" name="subTmpId" value="<?echo $subTmpId?>">
	<input type="hidden" name="PRODUCT_NAME_HIDDEN" value="<?echo htmlspecialcharsbx($parentProductName)?>">
	<?=bitrix_sessid_post();

	$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
<script type="text/javascript">
	BX('edit_edit_table').className += ' adm-shop-page-table';

	var CellTPL = [];
	<?
	foreach ($arCellTemplates as $key => $value)
	{
		?>CellTPL[<? echo $key; ?>] = '<? echo $value; ?>';
	<?
	}
	?>

	var CellAttr = [];
	<?
	foreach ($arCellTemplates as $key => $value)
	{
		?>CellAttr[<? echo $key; ?>] = '<? echo $value; ?>';
	<?
	}
	?>
	var obPricesTable = new JCCatTblEdit({
		'PREFIX': 'IB_SEG_',
		'TABLE_PROP_ID': 'generator_price_table',
		'PROP_COUNT_ID': 'generator_price_table_max_id'
	});
	obPricesTable.SetCells(CellTPL, CellAttr);

	var obPropertyTable = new JCIBlockGenerator({
		'PREFIX': 'IB_SEG_',
		'TABLE_PROP_ID': 'generator_property_table',
		'PROP_COUNT_ID': 'generator_price_table_max_id',
		'AR_ALL_PROPERTIES': <?=CUtil::PhpToJSObject($arResult)?>,
		'IMAGE_TABLE_ID': "adm-shop-table",
		'AR_FILE_PROPERTIES': <?=CUtil::PhpToJSObject($arFileProperties)?>
	});

	function addProperty(arFileProperties)
	{
		var fileProperties = eval(arFileProperties),
			id = 0;
		if(BX('ib_seg_max_property_id'))
		{
			id = BX('ib_seg_max_property_id').value;
			if(id >= obPropertyTable.AR_FILE_PROPERTIES.length + 2)
			{
				return;
			}
			BX('ib_seg_max_property_id').value = Number(BX('ib_seg_max_property_id').value) + 1;
		}
		obPropertyTable.SELECTED_PROPERTIES[id] = 'DETAIL';

		var propertySpan = BX('ib_seg_property_span');
		if(propertySpan)
		{
			var options = [];
			for(var key in fileProperties)
			{
				if(fileProperties.hasOwnProperty(key))
					options[options.length] = BX.create('OPTION', {
							'props': {'value':fileProperties[key]["ID"], 'selected':(fileProperties[key]["ID"] == 'DETAIL')},
							'text': fileProperties[key]["NAME"]
						}
					);
			}
			var span = BX.create('span', {
				props: {
					className: 'adm-select-wrap'
				}
			});
			var content = BX.create('select', {
				props: {
					name:"SELECTED_PROPERTY[]",
					id:"SELECTED_PROPERTY[]",
					className:"adm-select ib_seg_add_property_but"
				},
				style : {
					width : '130px'
				},
				children : options,
				'events': {
					change : function()
					{
						obPropertyTable.SELECTED_PROPERTIES[id] = this.value;
					}
				}
			});
			span.appendChild(content);
			propertySpan.appendChild(span);
		}
	}
</script>
<tr><td colspan="2" class="adm-detail-content-cell">
	<div class="adm-detail-content-item-block-view-tab"><div class="adm-shop-block-wrap">
	<table width="100%" border="0" cellspacing="7" cellpadding="0">
		<tr>
			<td class="adm-detail-content-cell-l"><?= GetMessage("IB_SEG_TITLE") ?>:</td>
			<td class="adm-detail-content-cell-r" style="white-space: nowrap !important;">
				<input type="text" style="width: 637px;" class="adm-input" id="IB_SEG_TITLE" name="IB_SEG_TITLE" >
				<input type="button" id="IB_SEG_ADD_PROP_IN_TITLE" title="..." value="<?= GetMessage("IB_SEG_SKU_PROPERTIES") ?>">
				<input type="button" id="IB_SEG_ADD_PROP_IN_TITLE2" title="..." value="<?= GetMessage("IB_SEG_PARENT_PROPERTIES") ?>">
				<a class="adm-input-help-icon" onmouseover="BX.hint(this, '<?=GetMessage('IB_SEG_TOOLTIP_TITLE')?>')" href="#"></a>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?= GetMessage("IB_SEG_WEIGHT") ?>:</td>
			<td class="adm-detail-content-cell-r">
				<input type="text" style="width: 120px; margin-right: 10px" class="adm-input" name="IB_SEG_WEIGHT">
				<?echo GetMessage("IB_SEG_BASE_LENGTH")?>:
				<input type="text" id="CAT_BASE_LENGTH" name="IB_SEG_BASE_LENGTH" style="width: 120px;  margin-right: 10px">
				<?echo GetMessage("IB_SEG_BASE_WIDTH")?>:
				<input type="text" id="CAT_BASE_WIDTH" name="IB_SEG_BASE_WIDTH" style="width: 120px;  margin-right: 10px">
				<?echo GetMessage("IB_SEG_BASE_HEIGHT")?>:
				<input type="text" id="CAT_BASE_HEIGHT" name="IB_SEG_BASE_HEIGHT" style="width: 120px;">
				<a class="adm-input-help-icon" onmouseover="BX.hint(this, '<?=GetMessage('IB_SEG_TOOLTIP_WEIGHT')?>')" href="#"></a>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?= GetMessage((!$useStoreControl ? 'IB_SEG_QUANTITY' : 'IB_SEG_MEASURE')); ?></td>
			<td class="adm-detail-content-cell-r"><?
			if (!$useStoreControl)
			{
				?><input type="text" style="width: 120px; margin-right: 10px" class="adm-input" name="IB_SEG_QUANTITY">
				<? echo GetMessage('IB_SEG_MEASURE');
			}
			?> <span class="adm-select-wrap" style="vertical-align: top;"><select name="IB_SEG_MEASURE" class="adm-select" style="width: 169px;"><?
			$measureIterator = CCatalogMeasure::getList(
				array(), array(), false, false, array("ID", "CODE", "MEASURE_TITLE", "SYMBOL_INTL", "IS_DEFAULT")
			);
			while($measure = $measureIterator->Fetch())
			{
				?><option value="<?=$measure['ID']?>"<? echo ($measure['IS_DEFAULT'] == 'Y' ? ' selected' : '');?>><?
				echo htmlspecialcharsEx($measure['MEASURE_TITLE']); ?></option><?
			}
			unset($measure, $measureIterator);
			?></select></span></td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("IB_SEG_VAT")?>:</td>
			<td class="adm-detail-content-cell-r">
				<span class="adm-select-wrap">
				<?
					$arVATRef = CatalogGetVATArray(array(), true);
					echo SelectBoxFromArray('IB_SEG_VAT_ID', $arVATRef, '', "", ($bReadOnly ? "disabled readonly" : '').'class="adm-select" style="width: 169px;"');
				?>
				</span>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?echo GetMessage("IB_SEG_VAT_INCLUDED")?></td>
			<td class="adm-detail-content-cell-r">
				<input type="hidden" name="IB_SEG_VAT_INCLUDED" id="IB_SEG_VAT_INCLUDED_N" value="N">
				<input class="adm-designed-checkbox" type="checkbox" name="IB_SEG_VAT_INCLUDED" id="IB_SEG_VAT_INCLUDED" value="Y" />
				<label class="adm-designed-checkbox-label" for="IB_SEG_VAT_INCLUDED"></label>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-content-cell-l"><?= GetMessage("IB_SEG_PRICE_SHORT") ?>:</td>
			<td class="adm-detail-content-cell-r">
				<table class="internal" id="generator_price_table" style="width: auto;">
					<tr class="heading">
						<td><?= GetMessage("IB_SEG_PRICE_TYPE") ?>:</td>
						<td><?= GetMessage("IB_SEG_PRICE") ?>:</td>
						<td><?= GetMessage("IB_SEG_CURRENCY") ?>:</td>
					</tr>
					<tbody>
					<?
						$intCount = 0;
						echo __AddRangeRow($intCount, IB_SEG_ROW_PREFIX);
					?>
					</tbody>
				</table>
				<span class="adm-btn adm-btn-add" style="margin-top: 12px;" onclick="obPricesTable.addRow();"><?= GetMessage("IB_SEG_PRICE_ROW_ADD") ?></span>
				<input type="hidden" value="1" id="generator_price_table_max_id">
			</td>
		</tr>
	</table>
	</div></div>
</td></tr>
<tr>
	<td colspan="2" class="adm-detail-content-cell" style="padding-bottom: 0;">
		<div class="adm-shop-toolbar">
			<span class="adm-btn adm-btn-add" id="mnu_ADD_PROPERTY"><?= GetMessage("IB_SEG_PROPERTY_ADD") ?></span><span class="adm-btn adm-btn-download" id="mnu_ADD_ALL_PROPERTY" onclick="obPropertyTable.loadAllProperties()"><?= GetMessage("IB_SEG_PROPERTY_ADD_ALL") ?></span><a class="adm-input-help-icon" onmouseover="BX.hint(this, '<?=GetMessage('IB_SEG_TOOLTIP_PROPERTIES')?>')" href="#"></a>
		</div>
	</td>
</tr>
<tr>
	<td colspan="2" class="adm-detail-content-cell" style="padding-top: 0;">
		<div class="adm-detail-content-item-block-view-tab">
			<div class="adm-detail-title-view-tab"><?= GetMessage("IB_SEG_SELECTED_PROPERTIES") ?></div>
			<input type="hidden" value="0" id="generator_property_table_max_id">
			<div class="adm-shop-table-block" id="generator_property_table">
				<script type="text/javascript">
					<?
					foreach($arResult as $key => $arProperty)
					{?>
					obPropertyTable.addPropertyTable(<?=$key?>);
					<?
					}
					?>
				</script>
			</div>
		</div>
	</td>
</tr>
<tr>
	<td colspan="2" class="adm-detail-content-cell">
		<div class="adm-detail-content-item-block-view-tab">
			<div class="adm-detail-title-view-tab"><?= GetMessage("IB_SEG_PICTURES") ?></div>
			<div class="adm-shop-block-wrap">
				<div class="adm-shop-select-bar" id="ib_seg_select_prop_bar">
					<input type="hidden" value="0" id="ib_seg_max_property_id">
					<input type="hidden" value="0" id="ib_seg_max_image_row_id">
					<?$arFileProperties[]=array("ID" => "DETAIL", "NAME" => GetMessage("IB_SEG_DETAIL"), "SELECTED" => 'Y');?>
					<?$arFileProperties[]=array("ID" => "ANNOUNCE", "NAME" => GetMessage("IB_SEG_ANNOUNCE")); ?>
					<span class="adm-btn" onclick="obPropertyTable.addPropertyImages();" id='ib_seg_add_images_button'><?= GetMessage("IB_SEG_ADD_PICTURES") ?></span>
						<span class="adm-shop-bar-btn-wrap" id='ib_seg_property_span'>
							<script type="text/javascript">
								addProperty(<?=CUtil::PhpToJSObject($arFileProperties)?>);
							</script>
						</span>
					<span id='ib_seg_property_add_button_span'>
						<span id="ib_seg_property_add_button_span_click" class="adm-btn adm-btn-add" onclick="addProperty(<?=CUtil::PhpToJSObject($arFileProperties)?>)"></span>
					</span>
				</div>
				<table class="internal adm-shop-page-internal" id="adm-shop-table">
				</table>
			</div>
		</div>
	</td>
</tr>
	<?
	$tabControl->EndTab();
	$tabControl->End();
	?>
	</form>
	<?
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");