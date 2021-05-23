<?
use \Bitrix\Main\Config\Option;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!isset($arParams['GOOGLE_VERSION']))
	$arParams['GOOGLE_VERSION'] = '3';

$arParams['DEV_MODE'] = $arParams['DEV_MODE'] == 'Y' ? 'Y' : 'N';

if($arParams['API_KEY'] == '')
	$arParams['API_KEY'] =  Option::get('fileman', 'google_map_api_key', '');

if (!defined('BX_GMAP_SCRIPT_LOADED'))
{
	CUtil::InitJSCore();

	if ($arParams['DEV_MODE'] != 'Y')
	{
		$scheme = (CMain::IsHTTPS() ? "https" : "http");
		$language = LANGUAGE_ID;

		//https://developers.google.com/maps/faq#languagesupport
		$languageReplaces = ['ua' => 'uk'];

		if(isset($languageReplaces[$language]))
		{
			$language = $languageReplaces[$language];
		}

		$APPLICATION->AddHeadString('<script src="'.$scheme.'://maps.google.com/maps/api/js?key='.htmlspecialcharsbx($arParams['API_KEY']).'&language='.$language.'" charset="utf-8"></script>');

		define('BX_GMAP_SCRIPT_LOADED', 1);
	}
}

$arParams['MAP_ID'] =
	($arParams["MAP_ID"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["MAP_ID"])) ?
	'MAP_'.$this->randString() : $arParams['MAP_ID'];

$arParams['INIT_MAP_LON'] = floatval($arParams['INIT_MAP_LON']);
$arParams['INIT_MAP_LON'] = $arParams['INIT_MAP_LON'] ? $arParams['INIT_MAP_LON'] : 37.64;
$arParams['INIT_MAP_LAT'] = floatval($arParams['INIT_MAP_LAT']);
$arParams['INIT_MAP_LAT'] = $arParams['INIT_MAP_LAT'] ? $arParams['INIT_MAP_LAT'] : 55.76;
$arParams['INIT_MAP_SCALE'] = intval($arParams['INIT_MAP_SCALE']);
$arParams['INIT_MAP_SCALE'] = $arParams['INIT_MAP_SCALE'] ? $arParams['INIT_MAP_SCALE'] : 10;

//echo '<pre>'; print_r($arParams); echo '</pre>';

$arResult['ALL_MAP_TYPES'] = array('NORMAL' => 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN');
$arResult['ALL_MAP_OPTIONS'] = array(
	'ENABLE_SCROLL_ZOOM' => 'scrollwheel: #true#',
	'ENABLE_DBLCLICK_ZOOM' => 'disableDoubleClickZoom: #false#',
	'ENABLE_DRAGGING' => 'draggable: #true#',
	'ENABLE_KEYBOARD' => 'keyboardShortcuts: #true#'
);

$arResult['ALL_MAP_CONTROLS'] = array(
	'TYPECONTROL' => 'mapTypeControl: #true#',
	'SMALL_ZOOM_CONTROL' => 'zoomControl: #true#',
	'SCALELINE' => 'scaleControl: #true#',
	/*
'LARGE_MAP_CONTROL' => 'LargeMap', 'SMALL_MAP_CONTROL' => 'SmallMap', 'SMALL_ZOOM_CONTROL' => 'SmallZoom', 'MINIMAP' => 'OverviewMap', , 'HTYPECONTROL' => 'HierarchicalMapType', 'SCALELINE' => 'Scale'*/
);

if ($arResult['ALL_MAP_TYPES'][$arParams['INIT_MAP_TYPE']]) // compatibility
	$arParams['INIT_MAP_TYPE'] = $arResult['ALL_MAP_TYPES'][$arParams['INIT_MAP_TYPE']];
elseif (!$arParams['INIT_MAP_TYPE'] || !in_array($arParams['INIT_MAP_TYPE'], $arResult['ALL_MAP_TYPES']))
	$arParams['INIT_MAP_TYPE'] = 'ROADMAP';

if (!is_array($arParams['OPTIONS']))
	$arParams['OPTIONS'] = array('ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING');
else
{
	foreach ($arParams['OPTIONS'] as $key => $option)
	{
		if (!$arResult['ALL_MAP_OPTIONS'][$option])
			unset($arParams['OPTIONS'][$key]);
	}

	$arParams['OPTIONS'] = array_values($arParams['OPTIONS']);
}

if (!is_array($arParams['CONTROLS']))
	$arParams['CONTROLS'] = array('TYPECONTROL', 'SMALL_ZOOM_CONTROL', 'SCALELINE');
else
{
	foreach ($arParams['CONTROLS'] as $key => $control)
	{
		if (!$arResult['ALL_MAP_CONTROLS'][$control])
			unset($arParams['CONTROLS'][$key]);
	}

	$arParams['CONTROLS'] = array_values($arParams['CONTROLS']);
}

$arParams['MAP_WIDTH'] = trim($arParams['MAP_WIDTH']);
if (ToUpper($arParams['MAP_WIDTH']) != 'AUTO' && mb_substr($arParams['MAP_WIDTH'], -1, 1) != '%')
{
	$arParams['MAP_WIDTH'] = intval($arParams['MAP_WIDTH']);
	if ($arParams['MAP_WIDTH'] <= 0) $arParams['MAP_WIDTH'] = 600;
	$arParams['MAP_WIDTH'] .= 'px';
}

$arParams['MAP_HEIGHT'] = trim($arParams['MAP_HEIGHT']);
if (mb_substr($arParams['MAP_HEIGHT'], -1, 1) != '%')
{
	$arParams['MAP_HEIGHT'] = intval($arParams['MAP_HEIGHT']);
	if ($arParams['MAP_HEIGHT'] <= 0) $arParams['MAP_HEIGHT'] = 500;
	$arParams['MAP_HEIGHT'] .= 'px';
}

CJSCore::Init();

$this->IncludeComponentTemplate();
?>