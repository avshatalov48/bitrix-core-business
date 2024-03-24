<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!isset($arParams['YANDEX_VERSION']))
	$arParams['YANDEX_VERSION'] = '2.0';

$arParams['DEV_MODE'] = ($arParams['DEV_MODE'] ?? null) == 'Y' ? 'Y' : 'N';

if(($arParams['API_KEY'] ?? null) == '')
{
	$arParams['API_KEY'] =  \Bitrix\Main\Config\Option::get('fileman', 'yandex_map_api_key', '');
}

if (!($arParams['LOCALE'] ?? null))
{
	switch (LANGUAGE_ID)
	{
		case 'ru':
			$arParams['LOCALE'] = 'ru-RU';
		break;
		case 'ua':
			$arParams['LOCALE'] = 'ru-UA';
		break;
		case 'tk':
			$arParams['LOCALE'] = 'tr-TR';
		break;
		default:
			$arParams['LOCALE'] = 'en-US';
		break;
	}
}

if (!defined('BX_YMAP_SCRIPT_LOADED'))
{
	$scheme = (CMain::IsHTTPS() ? "https" : "http");

	if($arParams['API_KEY'] == '')
	{
		$host = 'api-maps.yandex.ru';
	}
	else
	{
		$host = 'enterprise.api-maps.yandex.ru';
		$arParams['API_KEY'] = CUtil::JSEscape($arParams['API_KEY']);
	}

	$arResult['MAPS_SCRIPT_URL'] = $scheme.'://'.$host.'/'.$arParams['YANDEX_VERSION'].'/?load=package.full&mode=release&lang='.$arParams['LOCALE'].'&wizard=bitrix';

	if($arParams['API_KEY'] <> '')
	{
		$arResult['MAPS_SCRIPT_URL'] .= '&apikey='.$arParams['API_KEY'];
	}

	if ($arParams['DEV_MODE'] != 'Y')
	{
		?>
		<script>
			var script = document.createElement('script');
			script.src = '<?=$arResult['MAPS_SCRIPT_URL']?>';
			(document.head || document.documentElement).appendChild(script);
			script.onload = function () {
				this.parentNode.removeChild(script);
			};
		</script>
		<?
		define('BX_YMAP_SCRIPT_LOADED', 1);
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

$arResult['ALL_MAP_TYPES'] = array('MAP' => 'map', 'SATELLITE' => 'satellite', 'HYBRID' => 'hybrid', 'PUBLIC' => 'publicMap', 'PUBLIC_HYBRID' => 'publicMapHybrid');

$arResult['ALL_MAP_OPTIONS'] = array('ENABLE_SCROLL_ZOOM' => 'scrollZoom', 'ENABLE_DBLCLICK_ZOOM' => 'dblClickZoom', 'ENABLE_DRAGGING' => 'drag', /*'ENABLE_RULER' => 'ruler', */'ENABLE_RIGHT_MAGNIFIER' => 'rightMouseButtonMagnifier'/*, 'ENABLE_LEFT_MAGNIFIER' => 'leftMouseButtonMagnifier'*/);
$arResult['ALL_MAP_CONTROLS'] = array('ZOOM' => 'zoomControl', 'SMALLZOOM' => 'smallZoomControl', 'MINIMAP' => 'miniMap', 'TYPECONTROL' => 'typeSelector', 'SCALELINE' => 'scaleLine', 'SEARCH' => 'searchControl');

if (!$arParams['INIT_MAP_TYPE'] || !array_key_exists($arParams['INIT_MAP_TYPE'], $arResult['ALL_MAP_TYPES']))
	$arParams['INIT_MAP_TYPE'] = 'MAP';

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
	$arParams['CONTROLS'] = array('TOOLBAR', 'ZOOM', 'MINIMAP', 'TYPECONTROL', 'SCALELINE');
else
{
	foreach ($arParams['CONTROLS'] as $key => $control)
	{
		if (!($arResult['ALL_MAP_CONTROLS'][$control] ?? null))
		{
			unset($arParams['CONTROLS'][$key]);
		}
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