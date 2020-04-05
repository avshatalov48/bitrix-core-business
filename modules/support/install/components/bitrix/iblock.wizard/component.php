<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!isset($arParams['NEXT_URL']))
	$arParams['NEXT_URL'] = 'ticket_edit.php';

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);

if (!CModule::IncludeModule('iblock'))
{
	ShowError(GetMessage("WZ_ERR_NOIBLOCK"));
	return;
}

$arResult = array();

$SECTION_ID = intval($_POST['SECTION_ID']);
$CURRENT_STEP = intval($_POST['CURRENT_STEP']);

if ($CURRENT_STEP < 1)
	$CURRENT_STEP = 1;

if($_POST['back_x'] || $_POST['back'])
{
	$CURRENT_STEP--;

	$rs = CIBlockSection::GetById($_POST['LAST_SECTION_ID']);
	$f=$rs->Fetch();
	$SECTION_ID = intval($f['IBLOCK_SECTION_ID']);
}
elseif ($SECTION_ID)
	$CURRENT_STEP++;
else
{
	if ($_POST['next_x'] || $_POST['next'])
		$arResult['ERROR'] = GetMessage("WZ_ERR_NOSECTION");

	$SECTION_ID = intval($_POST['LAST_SECTION_ID']);
}

$arResult['CURRENT_STEP'] = $CURRENT_STEP;
$arResult['LAST_SECTION_ID'] = $arResult['SECTION_ID']	= $SECTION_ID;

$arSteps = array('',GetMessage("WZ_S1"),GetMessage("WZ_S2"),GetMessage("WZ_S3"), GetMessage("WZ_S4"), GetMessage("WZ_S5"), GetMessage("WZ_S6"), GetMessage("WZ_S7"));
$arResult['CURRENT_STEP_TEXT'] = $arSteps[$CURRENT_STEP];

if ($CURRENT_STEP > 1 && is_array($_POST['wizard']))
{
	foreach($_POST['wizard'] as $k=>$v)
	{
		if (is_array($v))
		{
			foreach($v as $k1=>$v1)
				if (trim($v1))
					$arHidden[$k][$k1] = htmlspecialcharsbx($v1);
		}
		elseif (trim($v))
			$arHidden[$k] = htmlspecialcharsbx($v);
	}
}

// NavChain 
if ($CURRENT_STEP > 1 && $arParams['INCLUDE_IBLOCK_INTO_CHAIN']=='Y')
{
	$rs = CIBlockSection::GetNavChain($IBLOCK_ID, $SECTION_ID);
	while($f = $rs->Fetch())
		$APPLICATION->AddChainItem($f['NAME']);
}

// Step decription
if ($CURRENT_STEP==1)
	$rs = CIBlock::GetById($arParams['IBLOCK_ID']);
else
	$rs = CIBlockSection::GetById($SECTION_ID);

if ($f = $rs->GetNext())
	$arResult['TOP_MESSAGE'] = $f['DESCRIPTION'];


// Sections
$arResult['SECTIONS'] = array();
$arFilter = array(
	'IBLOCK_ID' => $arParams['IBLOCK_ID'],
	'DEPTH_LEVEL' => $CURRENT_STEP,
	'ACTIVE' => 'Y'
);

if ($SECTION_ID)
	$arFilter['SECTION_ID'] = $SECTION_ID;

//if (!empty($arParams['ALLOWED_IBLOCK_SECTIONS']))
//{
//	$arFilter['=ID'] = $arParams['ALLOWED_IBLOCK_SECTIONS'];
//}

if (!empty($arParams['RESTRICTED_IBLOCK_SECTIONS']))
{
	$arFilter['!ID'] = $arParams['RESTRICTED_IBLOCK_SECTIONS'];
}

$rs = CIBlockSection::GetList(array('sort'=>'asc'),$arFilter);
while($f = $rs->GetNext())
	$arResult['SECTIONS'][] = $f;


// Elements
$arResult['FIELDS'] = array();
$arFilter = array(
	'IBLOCK_ID'=>$arParams['IBLOCK_ID'],
	'SECTION_ID'=>$SECTION_ID
);
if (!$USER->IsAdmin())
	$arFilter['ACTIVE'] = 'Y';

$arSelect = array('*');

$rs = CIBlockElement::GetList(array('sort'=>'asc'),$arFilter,false,false,$arSelect);
while($el=$rs->GetNextElement())
{
	$arField = $el->GetFields();

	$arField['FIELD_ID'] = 'wizard_field_'.$arField['ID'];
	$arField['FIELD_VALUE'] = $arHidden[$arField['FIELD_ID']];
	unset($arHidden[$arField['FIELD_ID']]);

	if ($arParams['PROPERTY_FIELD_TYPE'])
	{
		$prop = $el->GetProperties();
		$type = $prop[$arParams['PROPERTY_FIELD_TYPE']]['CODE'];
		if ($type)
		{
			$arField['FIELD_TYPE'] = $prop[$type]['VALUE_XML_ID'];
			$arField['FIELD_VALUES'] = $prop[$arParams['PROPERTY_FIELD_VALUES']]['VALUE'];
		}
	}

	$arResult['FIELDS'][] = $arField;
}
$arResult['HIDDEN'] = $arHidden;

$this->IncludeComponentTemplate();
?>
