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
	$arParams['INIT_MAP_LON'] = $arResult['POSITION']['google_lon'];
	$arParams['INIT_MAP_LAT'] = $arResult['POSITION']['google_lat'];
	$arParams['INIT_MAP_SCALE'] = $arResult['POSITION']['google_scale'];
}
elseif($arParams['BX_EDITOR_RENDER_MODE'] != 'Y' && $arParams['SKIP_POSITION_CHECK'] !== 'Y')
{
	ShowError(GetMessage('MYMS_NO_POSITION'));
	return;
}

$this->IncludeComponentTemplate();

?>