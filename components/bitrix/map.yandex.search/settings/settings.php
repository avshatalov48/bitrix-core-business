<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

__IncludeLang($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/map.yandex.search/lang/'.LANGUAGE_ID.'/settings.php');

//if(!$USER->IsAdmin())
//	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$obJSPopup = new CJSPopup('',
	array(
		'TITLE' => GetMessage('MYMV_SET_POPUP_TITLE'),
		'SUFFIX' => 'yandex_map',
		'ARGS' => ''
	)
);

$arData = array();
if ($_REQUEST['MAP_DATA'])
{
	CUtil::JSPostUnescape();
	if (CheckSerializedData($_REQUEST['MAP_DATA']))
	{
		$arData = unserialize($_REQUEST['MAP_DATA'], ['allowed_classes' => false]);
	}
}
?>
<script type="text/javascript" src="/bitrix/components/bitrix/map.yandex.search/settings/settings_load.js"></script>
<script type="text/javascript">
jsUtils.loadCSSFile('/bitrix/components/bitrix/map.yandex.search/settings/settings.css');
var arPositionData = <?echo is_array($arData) && count($arData) > 0 ? CUtil::PhpToJsObject($arData) : '{}'?>;
window._global_BX_UTF = <?echo defined('BX_UTF') && BX_UTF == true ? 'true' : 'false'?>;
window.jsYandexMess = {
	noname: '<?echo CUtil::JSEscape(GetMessage('MYMV_SET_NONAME'))?>',
	MAP_VIEW_MAP: '<?echo CUtil::JSEscape(GetMessage('MYMS_PARAM_INIT_MAP_TYPE_MAP'))?>',
	MAP_VIEW_SATELLITE: '<?echo CUtil::JSEscape(GetMessage('MYMS_PARAM_INIT_MAP_TYPE_SATELLITE'))?>',
	MAP_VIEW_HYBRID: '<?echo CUtil::JSEscape(GetMessage('MYMS_PARAM_INIT_MAP_TYPE_HYBRID'))?>',
	MAP_VIEW_PUBLIC: '<?echo CUtil::JSEscape(GetMessage('MYMS_PARAM_INIT_MAP_TYPE_PUBLIC'))?>',
	MAP_VIEW_PUBLIC_HYBRID: '<?echo CUtil::JSEscape(GetMessage('MYMS_PARAM_INIT_MAP_TYPE_PUBLIC_HYBRID'))?>',
	current_view: '<?echo CUtil::JSEscape($_REQUEST['INIT_MAP_TYPE'])?>',
	nothing_found: '<?echo CUtil::JSEscape(GetMessage('MYMS_PARAM_INIT_MAP_NOTHING_FOUND'))?>'
};
</script>
<form name="bx_popup_form_yandex_map">
<?
$obJSPopup->ShowTitlebar();
$obJSPopup->StartDescription('bx-edit-menu');
?>
	<p><b><?echo GetMessage('MYMV_SET_POPUP_WINDOW_TITLE')?></b></p>
	<p class="note"><?echo GetMessage('MYMV_SET_POPUP_WINDOW_DESCRIPTION')?></p>
<?
$obJSPopup->StartContent();
?>
<div id="bx_yandex_map_control" style="position: absolute; margin-right: 275px; margin-top: -2px; margin-left: -2px; border: solid 1px #B8C1DD;">
<?
$APPLICATION->IncludeComponent('bitrix:map.yandex.system', '', array(
	'INIT_MAP_TYPE' => $_REQUEST['INIT_MAP_TYPE'],
	'MAP_WIDTH' => 500,
	'MAP_HEIGHT' => 385,
	'INIT_MAP_LAT' => $arData['yandex_lat'],
	'INIT_MAP_LON' => $arData['yandex_lon'],
	'INIT_MAP_SCALE' => $arData['yandex_scale'],
	'CONTROLS' => array('TOOLBAR', 'TYPECONTROL', 'ZOOM'),
	'OPTIONS' => array('ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING'),
	'MAP_ID' => 'system_search_edit',
	'API_KEY' => $arParams['API_KEY'],
	'DEV_MODE' => 'Y',
	'ONMAPREADY' => 'jsYandexCE_search.init',
	'ONMAPREADY_PROPERTY' => 'jsYandexCE_search.map',
), false, array('HIDE_ICONS' => 'Y'));
?>
</div><div class="bx-yandex-map-address-search" id="bx_yandex_map_address_search" style="visibility: hidden; ">
	<?echo GetMessage('MYMV_SET_ADDRESS_SEARCH')?>: <input type="text" name="address" value="" style="width: 380px;" onkeypress="jsYandexCESearch.setTypingStarted(this)" autocomplete="off" />
</div><div class="bx-yandex-map-controls" id="bx_yandex_map_controls" style="margin-left: 510px; visibility: hidden;">
	<div class="bx-yandex-map-controls-group">
		<b><?echo GetMessage('MYMV_SET_START_POS')?></b><br />
			<ul id="bx_yandex_position">
				<li><?echo GetMessage('MYMV_SET_START_POS_LAT')?>: <span class="bx-yandex-map-controls-value" id="bx_yandex_lat_value"></span><input type="hidden" name="bx_yandex_lat" value="<?echo htmlspecialcharsbx($arData['yandex_lat'])?>" /></li>
				<li><?echo GetMessage('MYMV_SET_START_POS_LON')?>: <span class="bx-yandex-map-controls-value" id="bx_yandex_lon_value"></span><input type="hidden" name="bx_yandex_lon" value="<?echo htmlspecialcharsbx($arData['yandex_lon'])?>" /></li>
				<li><?echo GetMessage('MYMV_SET_START_POS_SCALE')?>: <span class="bx-yandex-map-controls-value" id="bx_yandex_scale_value"></span><input type="hidden" name="bx_yandex_scale" value="<?echo htmlspecialcharsbx($arData['yandex_scale'])?>" /></li>
				<li><?echo GetMessage('MYMV_SET_START_POS_VIEW')?>: <span class="bx-yandex-map-controls-value" id="bx_yandex_view_value"></span><input type="hidden" name="bx_yandex_view" value="<?echo htmlspecialcharsbx($_REQUEST['INIT_MAP_TYPE'])?>" /></li>
				<li><input type="checkbox" id="bx_yandex_position_fix" name="bx_yandex_position_fix" value="Y"<?if ($arData['yandex_scale']):?> checked="checked"<?endif;?> /> <label for="bx_yandex_position_fix"><?echo GetMessage('MYMV_SET_START_POS_FIX')?></label>&nbsp;|&nbsp;<a href="javascript:void(0)" id="bx_restore_position"><?echo GetMessage('MYMV_SET_START_POS_RESTORE')?></a>
			</ul>
	</div>
</div>
<script type="text/javascript">
if (null != window.jsYandexCESearch)
	jsYandexCESearch.clear();
</script>
<?
$obJSPopup->StartButtons();
?>
<input type="submit" value="<?echo GetMessage('MYMV_SET_SUBMIT')?>" onclick="return jsYandexCE_search.__saveChanges();"/>
<?
$obJSPopup->ShowStandardButtons(array('cancel'));
$obJSPopup->EndButtons();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>