<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	'GROUPS' => array(),
	'PARAMETERS' => array(
		'INIT_MAP_TYPE' => array(
			'NAME' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'MAP' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_MAP'),
				'SATELLITE' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_SATELLITE'),
				'HYBRID' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_HYBRID'),
				'PUBLIC' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_PUBLIC'),
				'PUBLIC_HYBRID' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_PUBLIC_HYBRID'),
			),
			'DEFAULT' => 'MAP',
			'ADDITIONAL_VALUES' => 'N',
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

		'MAP_DATA' => array(
			'NAME' => GetMessage('MYMS_PARAM_DATA'),
			'TYPE' => 'CUSTOM',
			'JS_FILE' => '/bitrix/components/bitrix/map.yandex.search/settings/settings.js',
			'JS_EVENT' => 'OnYandexMapSettingsEdit_search',
			'JS_DATA' => LANGUAGE_ID.'||'.GetMessage('MYMS_PARAM_DATA_SET'),
			'PARENT' => 'BASE',
			'DEFAULT' => serialize(array(
				'yandex_lat' => 55.7383,
				'yandex_lon' => 37.5946,
				'yandex_scale' => 10
			)),
		),

		'CONTROLS' => array(
			'NAME' => GetMessage('MYMS_PARAM_CONTROLS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => array(
				'TOOLBAR' => GetMessage('MYMS_PARAM_CONTROLS_TOOLBAR'), 
				'ZOOM' => GetMessage('MYMS_PARAM_CONTROLS_ZOOM'), 
				'SMALLZOOM' => GetMessage('MYMS_PARAM_CONTROLS_SMALLZOOM'), 
				'MINIMAP' => GetMessage('MYMS_PARAM_CONTROLS_MINIMAP'), 
				'TYPECONTROL' => GetMessage('MYMS_PARAM_CONTROLS_TYPECONTROL'), 
				'SCALELINE' => GetMessage('MYMS_PARAM_CONTROLS_SCALELINE')
			),
			'DEFAULT' => array(/*'TOOLBAR', */'ZOOM', 'MINIMAP', 'TYPECONTROL', 'SCALELINE'),
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),

		'OPTIONS' => array(
			'NAME' => GetMessage('MYMS_PARAM_OPTIONS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => array(
				'ENABLE_SCROLL_ZOOM' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_SCROLL_ZOOM'),
				'ENABLE_DBLCLICK_ZOOM' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_DBLCLICK_ZOOM'),
				'ENABLE_RIGHT_MAGNIFIER' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_RIGHT_MAGNIFIER'),
				'ENABLE_DRAGGING' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_DRAGGING'),
				/*'ENABLE_HOTKEYS' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_HOTKEYS'),*/
				/*'ENABLE_RULER' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_RULER'),*/
			),
			'DEFAULT' => array('ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING'),
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),

		'MAP_ID' => array(
			'NAME' => GetMessage('MYMS_PARAM_MAP_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
			'PARENT' => 'ADDITIONAL_SETTINGS',
		
		),

		'API_KEY' => array(
			'NAME' => GetMessage('MYMS_PARAM_API_KEY'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
			'PARENT' => 'ADDITIONAL_SETTINGS',
		)
	),
);
?>