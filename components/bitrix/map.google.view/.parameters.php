<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	'GROUPS' => array(
	),
	'PARAMETERS' => array(

		'API_KEY' => array(
			'NAME' => GetMessage('MYMS_PARAM_API_KEY').' https://developers.google.com/maps/documentation/javascript/get-api-key',
			'TYPE' => 'STRING',
			'DEFAULT' => '',
			'PARENT' => 'BASE',
		),

		'INIT_MAP_TYPE' => array(
			'NAME' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'ROADMAP' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_MAP'),
				'SATELLITE' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_SATELLITE'),
				'HYBRID' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_HYBRID'),
				'TERRAIN' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_TERRAIN')
			),
			'DEFAULT' => 'NORMAL',
			'ADDITIONAL_VALUES' => 'N',
			'PARENT' => 'BASE',
		),

		'MAP_DATA' => array(
			'NAME' => GetMessage('MYMS_PARAM_DATA'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => '/bitrix/components/bitrix/map.google.view/settings/settings.js',
			'JS_EVENT' => 'OnGoogleMapSettingsEdit',
			'JS_DATA' => LANGUAGE_ID.'||'.GetMessage('MYMS_PARAM_DATA_SET'),
			'DEFAULT' => serialize(array(
				'google_lat' => GetMessage('MYMS_PARAM_DATA_DEFAULT_LAT'),
				'google_lon' => GetMessage('MYMS_PARAM_DATA_DEFAULT_LON'),
				'google_scale' => 13
			)),
			'PARENT' => 'BASE',
		),

		'MAP_WIDTH' => array(
			'NAME' => GetMessage('MYMS_PARAM_MAP_WIDTH'),
			'TYPE' => 'STRING',
			'DEFAULT' => '600',
			'PARENT' => 'BASE',
		),
		
		'MAP_HEIGHT' => array(
			'NAME' => GetMessage('MYMS_PARAM_MAP_HEIGHT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '500',
			'PARENT' => 'BASE',
		),
		
		'CONTROLS' => array(
			'NAME' => GetMessage('MYMS_PARAM_CONTROLS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => array(
				'SMALL_ZOOM_CONTROL' => GetMessage('MYMS_PARAM_CONTROLS_SMALL_ZOOM_CONTROL'), 
				'TYPECONTROL' => GetMessage('MYMS_PARAM_CONTROLS_TYPECONTROL'), 
				'SCALELINE' => GetMessage('MYMS_PARAM_CONTROLS_SCALELINE')
			),
			
			'DEFAULT' => array('SMALL_ZOOM_CONTROL', 'TYPECONTROL', 'SCALELINE'),
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),
		
		'OPTIONS' => array(
			'NAME' => GetMessage('MYMS_PARAM_OPTIONS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => array(
				'ENABLE_SCROLL_ZOOM' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_SCROLL_ZOOM'),
				'ENABLE_DBLCLICK_ZOOM' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_DBLCLICK_ZOOM'),
				'ENABLE_DRAGGING' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_DRAGGING'),
				'ENABLE_KEYBOARD' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_KEYBOARD'),
			),
			
			'DEFAULT' => array('ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING', 'ENABLE_KEYBOARD'),
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),
		
		'MAP_ID' => array(
			'NAME' => GetMessage('MYMS_PARAM_MAP_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),
	),
);
?>