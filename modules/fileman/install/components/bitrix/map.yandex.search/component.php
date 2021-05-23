<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['MAP_ID'] =
	($arParams["MAP_ID"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ?
	'MAP_'.$this->randString() : $arParams['MAP_ID'];

$current_search = $_GET['ys'];

if (($strPositionInfo = $arParams['~MAP_DATA'])
	&& CheckSerializedData($strPositionInfo)
	&& ($arResult['POSITION'] = unserialize($strPositionInfo, ['allowed_classes' => false])))
{
	$arParams['INIT_MAP_LON'] = $arResult['POSITION']['yandex_lon'];
	$arParams['INIT_MAP_LAT'] = $arResult['POSITION']['yandex_lat'];
	$arParams['INIT_MAP_SCALE'] = $arResult['POSITION']['yandex_scale'];
}

CJSCore::Init();

$this->IncludeComponentTemplate();
?>