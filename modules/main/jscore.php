<?
$pathJS = '/bitrix/js/main/core';
$pathCSS = '/bitrix/js/main/core/css';
$pathCSSPanel = '/bitrix/panel/main';
$pathLang = BX_ROOT.'/modules/main/lang/'.LANGUAGE_ID;
//WARNING: Don't use CUserOptions here! CJSCore::Init can be called from php_interface/init.php where no $USER exists

$amChartsPath = '/bitrix/js/main/amcharts/3.21/';

$arJSCoreConfig = array(
	'ajax' => array(
		'js' => $pathJS.'/core_ajax.js',
	),
	'admin' => array(
		'js' => $pathJS.'/core_admin.js',
		'css' => array($pathCSS.'/core_panel.css', $pathCSSPanel.'/admin-public.css'),
		'lang' => $pathLang.'/js_core_admin.php',
		'rel' => array('ajax'),
		'use' => CJSCore::USE_PUBLIC,
	),
	'admin_interface' => array(
		'js' => $pathJS.'/core_admin_interface.js',
		'lang' => $pathLang.'/js_core_admin_interface.php',
		'css' => $pathCSSPanel.'/admin-public.css',
		'rel' => array('ajax', 'popup', 'window', 'date', 'fx'),
		'lang_additional' => array('TITLE_PREFIX' => CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))." - ")
	),
	"admin_login" => array(
		'js' => $pathJS."/core_admin_login.js",
		'css' => $pathCSSPanel."/login.css",
		'rel' => array("ajax", "window"),
	),
	'autosave' => array(
		'js' => $pathJS.'/core_autosave.js',
		'lang' => $pathLang.'/js_core_autosave.php',
		'rel' => array('ajax'),
	),
	'fx' => array(
		'js' => $pathJS.'/core_fx.js',
	),
	'dd' => array(
		'js' => $pathJS.'/core_dd.js',
	),
	'dnd' => array(
		'js' => '/bitrix/js/main/dd.js',
	),
	'webrtc' => array(
		'js' => $pathJS.'/core_webrtc.js',
		'rel' => array('webrtc_adapter')
	),
	'popup' => array(
		'js' => $pathJS.'/core_popup.js',
		'css' => $pathCSS.'/core_popup.css',
	),
	'tags' => array(
		'js' => $pathJS.'/core_tags.js',
		'css' => $pathCSS.'/core_tags.css',
		'lang' => $pathLang.'/js_core_tags.php',
		'rel' => array('popup'),
	),
	'timer' => array(
		'js' => $pathJS.'/core_timer.js',
	),
	'tooltip' => array(
		'js' => $pathJS.'/core_tooltip.js',
		'css' => $pathCSS.'/core_tooltip.css',
		'rel' => array('ajax'),
		'lang_additional' => array('TOOLTIP_ENABLED' => (IsModuleInstalled("socialnetwork") && COption::GetOptionString("socialnetwork", "allow_tooltip", "Y") == "Y" ? "Y" : "N")),
	),
	'translit' => array(
		'js' => $pathJS.'/core_translit.js',
		'lang' => $pathLang.'/js_core_translit.php',
/*		'lang_additional' => array('BING_KEY' => COption::GetOptionString('main', 'translate_key_bing', '')),*/
		'lang_additional' => array('YANDEX_KEY' => COption::GetOptionString('main', 'translate_key_yandex', '')),
	),
	'image' => array(
		'js' => $pathJS.'/core_image.js',
		'css' => $pathCSS.'/core_image.css',
		'rel' => array('ls'),
	),
	'viewer' => array(
		'js' => $pathJS.'/core_viewer.js',
		'css' => $pathCSS.'/core_viewer.css',
		'lang' => $pathLang.'/js_core_viewer.php',
		'rel' => array('ls', 'ajax', 'popup'),
		'lang_additional' => array('DISK_MYOFFICE' => COption::GetOptionString('disk', 'demo_myoffice', false))
	),
	'window' => array(
		'js' => $pathJS.'/core_window.js',
		//'css' => $pathCSS.'/core_window.css',
		'css' => $pathCSSPanel.'/popup.css',
		'rel' => array('ajax'),
	),
	'access' => array(
		'js' => $pathJS.'/core_access.js',
		'css' => $pathCSS.'/core_access.css',
		'rel' => array('popup', 'ajax', 'finder'),
		'lang' => $pathLang.'/js_core_access.php',
	),
	'finder' => array(
		'js' => $pathJS.'/core_finder.js',
		'css' => $pathCSS.'/core_finder.css',
		'rel' => array('popup', 'ajax', 'db_indexeddb'),
	),
	'user' => array(
		'js' => $pathJS.'/core_user.js',
		'lang' => $pathLang.'/js_core_user.php',
		'rel' => array('date'),
		'lang_additional' => array(
			'LIMIT_ONLINE' => method_exists('CUser', 'GetSecondsForLimitOnline')? CUser::GetSecondsForLimitOnline(): 1440 // we use this condition because has a fatal error while running updater "main 17.5.0" witch calling the OnAfterEpilog event.
		),
	),
	'date' => array(
		'js' => $pathJS.'/core_date.js',
		'css' => $pathCSS.'/core_date.css',
		'lang' => $pathLang.'/date_format.php',
		'lang_additional' => array(
			'WEEK_START' => CSite::GetWeekStart(),
			'AMPM_MODE' => IsAmPmMode(true),
		),
		'rel' => array('popup'),
	),
	'ls' => array(
		'js' => $pathJS.'/core_ls.js',
		'rel' => array('json')
	),
	'db' => array(
		'js' => $pathJS.'/core_db.js',
	),
	'db_indexeddb' => array(
		'js' => $pathJS.'/core_db_indexeddb.js',
	),
	'fc' => array(
		'js' => $pathJS . '/core_frame_cache.js',
		'rel' => array('db','ajax', 'ls', 'fx')
	),
	'avatar_editor' => array(
		'js' => $pathJS.'/core_avatar_editor.js',
		'css' => $pathCSS.'/core_avatar_editor.css',
		'lang' => $pathLang.'/js_core_avatar_editor.php',
		'rel' => array('canvas', 'popup', 'dd', 'uploader'),
	),
	'canvas' => array(
		'js' => $pathJS.'/core_canvas.js',
		'css' => $pathCSS.'/core_canvas.css',
		'lang' => $pathLang.'/js_core_canvas.php',
		'rel' => array('popup'),
	),
	'uploader' => array(
		'js' => array(
			$pathJS.'/core_uploader/common.js',
			$pathJS.'/core_uploader/uploader.js',
			$pathJS.'/core_uploader/file.js',
			$pathJS.'/core_uploader/queue.js',
		),
		'lang_additional' => array(
			"phpMaxFileUploads" => ini_get("max_file_uploads"),
			"phpPostMaxSize" => CUtil::Unformat(ini_get("post_max_size")),
			"phpUploadMaxFilesize" => CUtil::Unformat(ini_get("upload_max_filesize")),
			"bxImageExtensions" => CFile::GetImageExtensions(),
			"bxUploaderLog" => COption::GetOptionString("main", "uploaderLog", "N"),
			"bxQuota"=> CDiskQuota::getInstance()->GetDiskQuota()
		),
		'lang' => $pathLang.'/js_core_uploader.php',
		'rel' => array('ajax', 'dd'),
		'bundle_js' => 'coreuploader'
	),
	'site_speed' => array(
		'js' => $pathJS.'/site_speed/site_speed.js',
		'lang' => $pathLang.'/js_site_speed.php',
		'rel' => array('amcharts_serial', 'ajax', "date")
	),
	'qrcode' => array(
		'js' => array(
			'/bitrix/js/main/qrcode/qrcode.js'
		)
	),
	'fileinput' => array(
		'js' => $pathJS.'/core_fileinput.js',
		'css' => $pathCSS.'/core_fileinput.css',
		'lang' => $pathLang.'/js_core_fileinput.php',
		'rel' => array("ajax", "window", "popup", "uploader", "canvas", "dd")
	),
	'clipboard' => array(
		'js' => $pathJS.'/core_clipboard.js',
		'lang' => $pathLang.'/js_core_clipboard.php',
		'rel' => array("popup")
	),
	'recorder' => array(
		'js' => '/bitrix/js/main/recorder/recorder.js',
		'rel' => array('lamejs')
	),
	'pin' => array(
		'js' => '/bitrix/js/main/pin/pin.js',
		'css' => '/bitrix/js/main/pin/css/pin.css'
	),
	'ui_select' => array(
		'js' => $pathJS.'/core_ui_select.js',
		'css' => $pathCSS.'/core_ui_select.css',
		'rel' => array('popup')
	),
	'ui_date' => array(
		'js' => $pathJS.'/core_ui_date.js',
		'css' => $pathCSS.'/core_ui_date.css',
		'rel' => array('ui_factory')
	),
	'ui_factory' => array(
		'js' => $pathJS.'/core_ui_factory.js',
		'css' => $pathCSS.'/core_ui_control.css',
		'rel' => array('decl')
	),
	'ui' => array(
		'rel' => array(
			'ui_factory',
			'ui_select',
			'ui_date'
		)
	),
	'resize_observer' => array(
		'js' => array(
			$pathJS.'/resize_observer/resize_observer_collection.js',
			$pathJS.'/resize_observer/resize_observer_item_collection.js',
			$pathJS.'/resize_observer/resize_observer_item_rect.js',
			$pathJS.'/resize_observer/resize_observer_item.js',
			$pathJS.'/resize_observer/resize_observer.js'
		)
	),
	'decl' => array(
		'js' => $pathJS.'/core_decl.js'
	),
	'drag_drop' => array(
		'js' => $pathJS.'/core_dragdrop.js'
	),
	'kanban' => array(
		'js'  => array(
			'/bitrix/js/main/kanban/grid.js',
			'/bitrix/js/main/kanban/column.js',
			'/bitrix/js/main/kanban/item.js',
			'/bitrix/js/main/kanban/dropzone-area.js',
			'/bitrix/js/main/kanban/dropzone.js',
			'/bitrix/js/main/kanban/utils.js'
		),
		'css' => array(
			'/bitrix/js/main/kanban/css/kanban.css',
		),
		'lang' => $pathLang.'/js/kanban.php',
		'rel' => array('color_picker', 'dnd'),
		'bundle_js' => 'kanban',
		'bundle_css' => 'kanban'
	),
	'color_picker' => array(
		'js'  => array(
			'/bitrix/js/main/colorpicker/colorpicker.js',
		),
		'css' => array(
			'/bitrix/js/main/colorpicker/css/colorpicker.css',
		),
		'lang' => $pathLang.'/js/colorpicker.php',
		'rel' => array('popup'),
	),
	'masked_input' => array(
		'js' => array(
			'/bitrix/js/main/masked_input.js'
		)
	),
	'fullscreen' => array(
		'js' => $pathJS.'/core_fullscreen.js'
	),
	'spotlight' => array(
		'js' => '/bitrix/js/main/spotlight/spotlight.js',
		'css' => '/bitrix/js/main/spotlight/css/spotlight.css',
		'lang' => $pathLang.'/js/spotlight.php',
		'rel' => array('popup', 'ajax'),
		'bundle_js' => 'spotlight',
		'bundle_css' => 'spotlight',
	),
	'sidepanel' => array(
		'js' => array(
			'/bitrix/js/main/sidepanel/manager.js',
			'/bitrix/js/main/sidepanel/slider.js'
		),
		'css' => '/bitrix/js/main/sidepanel/css/sidepanel.css',
		'rel' => array('ajax', 'fx'),
		'lang' => $pathLang.'/js/sidepanel.php',
		'bundle_js' => 'sidepanel',
		'bundle_css' => 'sidepanel'
	),

	/* external libs */
	'jquery' => array(
		'js' => '/bitrix/js/main/jquery/jquery-1.8.3.min.js',
		'skip_core' => true,
	),
	'jquery_src' => array(
		'js' => '/bitrix/js/main/jquery/jquery-1.8.3.js',
		'skip_core' => true,
	),
	'jquery2' => array(
		'js' => '/bitrix/js/main/jquery/jquery-2.1.3.min.js',
		'skip_core' => true,
	),
	'jquery2_src' => array(
		'js' => '/bitrix/js/main/jquery/jquery-2.1.3.js',
		'skip_core' => true,
	),
	'json' => array(
		'js' => '/bitrix/js/main/json/json2.min.js',
		'skip_core' => true,
	),
	'json_src' => array(
		'js' => '/bitrix/js/main/json/json2.js',
		'skip_core' => true,
	),
	'amcharts' => array(
		'js' => $amChartsPath.'amcharts.js',
		'lang_additional' => array(
			'AMCHARTS_PATH' => $amChartsPath, // will be needed in 3.14
			'AMCHARTS_IMAGES_PATH' => $amChartsPath.'images/',
		),
		'skip_core' => true,
	),
	'amcharts_i18n' => array(
		'js' => $amChartsPath.LANGUAGE_ID.'/'.LANGUAGE_ID.'.js',
		'skip_core' => true,
	),
	'amcharts_funnel' => array(
		'js' => $amChartsPath.'funnel.js',
		'rel' => array('amcharts'),
		'skip_core' => true,
	),
	'amcharts_gauge' => array(
		'js' => $amChartsPath.'gauge.js',
		'rel' => array('amcharts'),
		'skip_core' => true,
	),
	'amcharts_pie' => array(
		'js' => $amChartsPath.'pie.js',
		'rel' => array('amcharts'),
		'skip_core' => true,
	),
	'amcharts_radar' => array(
		'js' => $amChartsPath.'radar.js',
		'rel' => array('amcharts'),
		'skip_core' => true,
	),
	'amcharts_serial' => array(
		'js' => $amChartsPath.'serial.js',
		'rel' => array('amcharts'),
		'skip_core' => true,
	),
	'amcharts_xy' => array(
		'js' => $amChartsPath.'xy.js',
		'rel' => array('amcharts'),
		'skip_core' => true,
	),
	'helper' => array(
		'js' => '/bitrix/js/main/helper/helper.js',
		'css' => '/bitrix/js/main/helper/css/helper.css',
		'rel' => array('sidepanel', 'ajax')
	),
	'webrtc_adapter' => array(
		'js' => '/bitrix/js/main/webrtc/adapter.js'
	),
	'lamejs' => array(
		'js' => '/bitrix/js/main/recorder/recorder.js'
	),
	'update_stepper' => array(
		'js' => $pathJS.'/core_update_stepper.js',
		'css' => $pathCSS.'/core_update_stepper.css',
		'lang' => $pathLang.'/js_core_update_stepper.php',
		'rel' => array('ajax'),
	),
	'uf' => array(
		'js' => $pathJS.'/core_uf.js',
		'css' => $pathCSS.'/core_uf.css',
		'rel' => array('ajax'),
		'oninit' => function()
		{
			return array(
				'lang_additional' => array(
					'UF_SITE_TPL' => SITE_TEMPLATE_ID,
					'UF_SITE_TPL_SIGN' => \Bitrix\Main\UserField\Dispatcher::instance()->getSignatureManager()->getSignature(SITE_TEMPLATE_ID),
				),
			);
		}
	),
	'phone_number' => array(
		'js' => '/bitrix/js/main/phonenumber/phonenumber.js',
		'css' => '/bitrix/js/main/phonenumber/css/phonenumber.css',
		'oninit' => function()
		{
			return array(
				'lang_additional' => array(
					'phone_number_default_country' => \Bitrix\Main\PhoneNumber\Parser::getDefaultCountry(),
					'user_default_country' => \Bitrix\Main\PhoneNumber\Parser::getUserDefaultCountry()
				)
			);
		},
		'rel' => array('popup'),
	),
	'loader' => array(
		'js' => '/bitrix/js/main/loader/loader.js',
		'css' => '/bitrix/js/main/loader/loader.css'
	)
);

\Bitrix\Main\Page\Asset::getInstance()->addJsKernelInfo(
	'main',
	array(
		'/bitrix/js/main/core/core.js', '/bitrix/js/main/core/core_ajax.js', '/bitrix/js/main/json/json2.min.js',
		'/bitrix/js/main/core/core_ls.js', '/bitrix/js/main/core/core_popup.js', '/bitrix/js/main/core/core_tooltip.js',
		'/bitrix/js/main/core/core_date.js','/bitrix/js/main/core/core_timer.js', '/bitrix/js/main/core/core_fx.js',
		'/bitrix/js/main/core/core_window.js', '/bitrix/js/main/core/core_autosave.js', '/bitrix/js/main/rating_like.js',
		'/bitrix/js/main/session.js', '/bitrix/js/main/dd.js', '/bitrix/js/main/utils.js',
		'/bitrix/js/main/core/core_dd.js', '/bitrix/js/main/core/core_webrtc.js',
		'/bitrix/js/main/core/core_uf.js'
	)
);

\Bitrix\Main\Page\Asset::getInstance()->addCssKernelInfo(
	'main',
	array(
		'/bitrix/js/main/core/css/core.css', '/bitrix/js/main/core/css/core_popup.css',
		'/bitrix/js/main/core/css/core_tooltip.css', '/bitrix/js/main/core/css/core_date.css',
		'/bitrix/js/main/core/css/core_uf.css'
	)
);

foreach ($arJSCoreConfig as $ext => $arExt)
{
	CJSCore::RegisterExt($ext, $arExt);
}
