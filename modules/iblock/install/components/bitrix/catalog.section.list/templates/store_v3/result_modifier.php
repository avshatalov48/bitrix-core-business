<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!isset($arParams['OFFSET_MODE']))
{
	$arParams['OFFSET_MODE'] = 'N';
}
if (
	$arParams['OFFSET_MODE'] !== 'N'
	&& $arParams['OFFSET_MODE'] !== 'F'
	&& $arParams['OFFSET_MODE'] !== 'D'
)
{
	$arParams['OFFSET_MODE'] = 'N';
}
switch ($arParams['OFFSET_MODE'])
{
	case 'F':
		if (!isset($arParams['OFFSET_VALUE']))
		{
			$arParams['OFFSET_VALUE'] = 0;
		}
		$arParams['OFFSET_VALUE'] = (float)$arParams['OFFSET_VALUE'];
		if ($arParams['OFFSET_VALUE'] <= 0)
		{
			$arParams['OFFSET_MODE'] = 'N';
			$arParams['OFFSET_VALUE'] = 0;
			$arParams['OFFSET_VARIABLE'] = '';
		}
		break;
	case 'D':
		if (!isset($arParams['OFFSET_VARIABLE']))
		{
			$arParams['OFFSET_VARIABLE'] = '';
		}
		if (
			$arParams['OFFSET_VARIABLE'] === ''
			|| !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['OFFSET_VARIABLE'])
		)
		{
			$arParams['OFFSET_MODE'] = 'N';
			$arParams['OFFSET_VALUE'] = 0;
			$arParams['OFFSET_VARIABLE'] = '';
		}
		break;
}

$areaId = '';
if (isset($arParams['AREA_ID']) && is_string($arParams['AREA_ID']) && $arParams['AREA_ID'] !== '')
{
	$areaId = $arParams['AREA_ID'];
}
$areaId .= '_'.$arParams['OFFSET_MODE'].'_'.$arParams['OFFSET_VALUE'].'_'.$arParams['OFFSET_VARIABLE'];

$arResult['AREA_ID_ADDITIONAL_SALT'] = $areaId;

if ($arResult['SECTIONS_COUNT'] > 0)
{
	$boolPicture = false;
	$arSelect = array('ID');
	$arMap = array();

	$arCurrent = reset($arResult['SECTIONS']);
	if (!isset($arCurrent['PICTURE']))
	{
		$boolPicture = true;
		$arSelect[] = 'PICTURE';
	}
	unset($arCurrent);

	if ($boolPicture)
	{
		foreach ($arResult['SECTIONS'] as $key => $arSection)
		{
			$arMap[$arSection['ID']] = $key;
		}
		unset($key, $arSection);
		$rsSections = CIBlockSection::GetList(array(), array('ID' => array_keys($arMap)), false, $arSelect);
		while ($arSection = $rsSections->Fetch())
		{
			if (!isset($arMap[$arSection['ID']]))
				continue;
			$key = $arMap[$arSection['ID']];
			$pictureId = (int)$arSection['PICTURE'];
			$arResult['SECTIONS'][$key]['PICTURE'] = ($pictureId > 0 ? CFile::GetFileArray($pictureId) : false);
			$arResult['SECTIONS'][$key]['~PICTURE'] = $arSection['PICTURE'];
		}
		unset($pictureId, $key, $arSection, $rsSections);
	}
	unset($arMap, $arSelect, $boolPicture);
}