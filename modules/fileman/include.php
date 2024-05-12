<?php

/*patchlimitationmutatormark1*/
CModule::AddAutoloadClasses(
	"fileman",
	array(
		"CLightHTMLEditor" => "classes/general/light_editor.php",
		"CEditorUtils" => "classes/general/editor_utils.php",
		"CMedialib" => "classes/general/medialib.php",
		"CEventFileman" => "classes/general/fileman_event_list.php",
		"CCodeEditor" => "classes/general/code_editor.php",
		"CFileInput" => "classes/general/file_input.php",
		"CMedialibTabControl" => "classes/general/medialib.php",
		"CSticker" => "classes/general/sticker.php",
		"CSnippets" => "classes/general/snippets.php",
		"CAdminContextMenuML" => "classes/general/medialib_admin.php",
		"CHTMLEditor" => "classes/general/html_editor.php",
		"CComponentParamsManager" => "classes/general/component_params_manager.php",
		"CSpellchecker" => "classes/general/spellchecker.php"
	)
);

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/lang.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin_tools.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/fileman.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/properties.php");
/*patchlimitationmutatormark2*/

CJSCore::RegisterExt('file_input', array(
	'js' => '/bitrix/js/fileman/core_file_input.js',
	'lang' => '/bitrix/modules/fileman/lang/'.LANGUAGE_ID.'/classes/general/file_input.php',
	'rel' => array('window') //BX.COpener
));

CJSCore::RegisterExt('map_google', array(
	'js' => '/bitrix/js/fileman/core_map_google.js'
));

CJSCore::RegisterExt('google_loader', array(
	'js' => '/bitrix/js/fileman/google/loader.js',
	'oninit' => function()
	{
		$additionalLang = array(
			'GOOGLE_MAP_API_KEY' => \Bitrix\Fileman\UserField\Address::getApiKey(),
			'GOOGLE_MAP_API_KEY_HINT' => \Bitrix\Fileman\UserField\Address::getApiKeyHint(),
		);

		return array(
			'lang_additional' => $additionalLang,
		);
	}
));

CJSCore::RegisterExt('google_map', array(
	'js' => '/bitrix/js/fileman/google/map.js',
	'rel' => array('google_loader'),
));

CJSCore::RegisterExt('google_geocoder', array(
	'js' => '/bitrix/js/fileman/google/geocoder.js',
	'rel' => array('google_loader'),
));

CJSCore::RegisterExt('google_autocomplete', array(
	'js' => '/bitrix/js/fileman/google/autocomplete.js',
	'rel' => array('google_loader'),
));

CJSCore::RegisterExt('userfield_address', array(
	'js' => array('/bitrix/js/fileman/userfield/address.js'),
	'css' => array('/bitrix/js/fileman/userfield/address.css'),
	'lang' => '/bitrix/modules/fileman/lang/'.LANGUAGE_ID.'/js_userfield_address.php',
	'rel' => array('uf', 'google_map', 'google_geocoder', 'google_autocomplete', 'popup'),
));

CJSCore::RegisterExt('player', [
	'js' => [
		'/bitrix/js/fileman/player/fileman_player.js',
		'/bitrix/js/fileman/player/videojs/video.js',
	],
	'css' => [
		'/bitrix/js/fileman/player/videojs/video-js.css',
	],
	'rel' => [
		'ui.design-tokens',
	]
]);

//on update method still not exist
if(method_exists($GLOBALS["APPLICATION"], 'AddJSKernelInfo'))
{
	$GLOBALS["APPLICATION"]->AddJSKernelInfo(
		'fileman',
		array(
			'/bitrix/js/fileman/light_editor/le_dialogs.js', '/bitrix/js/fileman/light_editor/le_controls.js',
			'/bitrix/js/fileman/light_editor/le_toolbarbuttons.js', '/bitrix/js/fileman/light_editor/le_core.js'
		)
	);

	$GLOBALS["APPLICATION"]->AddCSSKernelInfo('fileman',array('/bitrix/js/fileman/light_editor/light_editor.css'));

	// Park new html-editor
	$GLOBALS["APPLICATION"]->AddJSKernelInfo(
		'htmleditor',
		array(
			'/bitrix/js/fileman/html_editor/range.js',
			'/bitrix/js/fileman/html_editor/html-actions.js',
			'/bitrix/js/fileman/html_editor/html-views.js',
			'/bitrix/js/fileman/html_editor/html-parser.js',
			'/bitrix/js/fileman/html_editor/html-base-controls.js',
			'/bitrix/js/fileman/html_editor/html-controls.js',
			'/bitrix/js/fileman/html_editor/html-components.js',
			'/bitrix/js/fileman/html_editor/html-snippets.js',
			'/bitrix/js/fileman/html_editor/html-editor.js'
		)
	);
}
