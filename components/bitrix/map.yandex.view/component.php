<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['MAP_ID'] =
	($arParams["MAP_ID"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ?
	'MAP_'.$this->randString() : $arParams['MAP_ID'];

if (($strPositionInfo = $arParams['~MAP_DATA'])
	&& CheckSerializedData($strPositionInfo)
	&& ($arResult['POSITION'] = unserialize($strPositionInfo, ['allowed_classes' => false])))
{
	if (
		is_array($arResult['POSITION'] ?? null)
		&& is_array($arResult['POSITION']['PLACEMARKS'] ?? null)
		&& ($cnt = count($arResult['POSITION']['PLACEMARKS']))
	)
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$arResult['POSITION']['PLACEMARKS'][$i]['TEXT'] = str_replace('###RN###', "\r\n", $arResult['POSITION']['PLACEMARKS'][$i]['TEXT']);
		}
	}

	if (
		is_array($arResult['POSITION'] ?? null)
		&& is_array($arResult['POSITION']['POLYLINES'] ?? null)
		&& ($cnt = count($arResult['POSITION']['POLYLINES']))
	)
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$arResult['POSITION']['POLYLINES'][$i]['TITLE'] = str_replace('###RN###', "\r\n", $arResult['POSITION']['POLYLINES'][$i]['TITLE']);
		}
	}
}

$this->IncludeComponentTemplate();
?>