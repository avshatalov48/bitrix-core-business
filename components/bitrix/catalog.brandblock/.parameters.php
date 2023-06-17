<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */
use Bitrix\Main\Loader;
use Bitrix\Iblock;

if (!Loader::includeModule('iblock'))
	return;
if (!Loader::includeModule('highloadblock'))
	return;

$iblockExists = (!empty($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID'] > 0);

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$iblockFilter = [
	'ACTIVE' => 'Y',
];
if (!empty($arCurrentValues['IBLOCK_TYPE']))
{
	$iblockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
	$arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
unset($arr, $rsIBlock, $iblockFilter);

$arProps = array();
if ($iblockExists)
{
	$propertyIterator = Iblock\PropertyTable::getList(array(
		'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'USER_TYPE'),
		'filter' => array(
			'=IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'],
			'=ACTIVE' => 'Y',
			'=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
			'=USER_TYPE' => 'directory'
		),
		'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
	));
	while ($property = $propertyIterator->fetch())
	{
		$propertyCode = (string)$property['CODE'];
		if ($propertyCode == '')
			$propertyCode = $property['ID'];
		$propertyName = '['.$propertyCode.'] '.$property['NAME'];

		$arProps[$propertyCode] = $propertyName;
	}
	unset($propertyCode, $propertyName, $property, $propertyIterator);
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_CB_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_CB_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_CB_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		),
		"ELEMENT_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_CB_ELEMENT_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""
		),
		"PROP_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_CB_PROP_CODE"),
			"TYPE" => "LIST",
			"VALUES" => $arProps,
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y"
		),
		"SHOW_DEACTIVATED" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_CB_SHOW_DEACTIVATED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"
		),
		"WIDTH" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("IBLOCK_CB_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "120"
		),
		"HEIGHT" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("IBLOCK_CB_HEIGHT"),
			"TYPE" => "STRING",
			"DEFAULT" => "50"
		),
		"WIDTH_SMALL" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("IBLOCK_CB_WIDTH_SMALL"),
			"TYPE" => "STRING",
			"DEFAULT" => "21"
		),
		"HEIGHT_SMALL" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("IBLOCK_CB_HEIGHT_SMALL"),
			"TYPE" => "STRING",
			"DEFAULT" => "17"
		),
		"CACHE_TIME"  =>  array(
			"DEFAULT" => 36000000
		),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_CB_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SINGLE_COMPONENT" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("IBLOCK_CB_SINGLE_COMPONENT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"ELEMENT_COUNT" => array(
			"PARENT" => "BASKET",
			"NAME" => GetMessage("IBLOCK_CB_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "3"
		)
	)
);