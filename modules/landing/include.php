<?php

use Bitrix\Landing\Manager;

$pathJS = '/bitrix/js/landing';
$pathTemplate24 = 'templates/';
$pathTemplate24 .= Manager::getTemplateId(
	Manager::getMainSiteId()
);
$pathTemplate24 = getLocalPath($pathTemplate24);
$pathCSS = '/bitrix/js/landing/css';
$pathLang = BX_ROOT . '/modules/landing/lang/' . LANGUAGE_ID;

$jsConfig = array(
	'landing_master' => array(
		'rel' => array(
			'landing.master',
			'landing_icon_fonts',
			// 'landing_jquery',		// need jq?
			// 'landing_popup_link', 	// todo: in editor need fancybox for video?
		),
	),

	'landing_note' => [
		'js' => [
			$pathJS . '/ui/note/menu.js'
		],
		'rel' => [
			'sidepanel',
			'ui.notification'
		],
		'lang' => $pathLang . '/js/note.php'
	],

	'mediaplayer' => array(
		'js' => array(
			'https://www.youtube.com/iframe_api',
			$pathJS . '/mediaplayer/base_mediaplayer.js',
			$pathJS . '/mediaplayer/youtube_mediaplayer.js',
			$pathJS . '/mediaplayer/mediaplayer_factory.js'
		),
		'rel' => [
			'landing.utils',
		]
	),

	'landing_inline_video' => [
		'js' => [
			$pathTemplate24 . '/assets/js/helpers/inline-video.js',
		],
		'rel' => ['mediaplayer']
	],
	'map_provider' => array(
		'js' => array(
			$pathJS . '/collection/base_collection.js',
			$pathJS . '/provider/map/base-map-provider.js',
			$pathJS . '/provider/map/google-map.js',
			$pathJS . '/provider/map/google-map/theme/silver.theme.js',
			$pathJS . '/provider/map/google-map/theme/retro.theme.js',
			$pathJS . '/provider/map/google-map/theme/dark.theme.js',
			$pathJS . '/provider/map/google-map/theme/night.theme.js',
			$pathJS . '/provider/map/google-map/theme/aubergine.theme.js'
		),
		'css' => array(
			$pathCSS . '/provider/map/google-map.css',
		),
		'rel' => [
			'landing.utils',
			'landing.loc'
		],
	),

	'polyfill' => array(
		'js' => array(
			$pathJS . '/polyfill.js',
		)
	),

	'action_dialog' => array(
		'js' => array(
			$pathJS . '/ui/tool/action_dialog.js'
		),
		'css' => array(
			$pathCSS . '/ui/tool/action_dialog.css',
		),
		'rel' => array(
			'polyfill',
			'popup'
		),
		'lang' => $pathLang . '/js/action_dialog.php'
	),
	
	'landing_public' => [
		'js' => [
			$pathJS . '/events/block_event.js',
			$pathJS . '/public.js',
		],
		'css' => [
			$pathCSS . '/landing_public.css',
		],
		'rel' => [
			'landing_event_tracker',
			'mediaplayer',
			'polyfill',
			'landing.utils',
		],
	],
	
	'landing_event_tracker' => array(
		'js' => array(
			$pathJS . '/event-tracker/event-tracker.js',
			$pathJS . '/event-tracker/services/base-service.js',
			$pathJS . '/event-tracker/services/google-analytics-service.js'
		),
		'rel' => [
			'landing.utils',
		],
	),

	// vendors scripts for ALL blocks, included always
	'landing_core' => array(
		'js' => array(
			// todo: del
			// $pathTemplate24 . '/assets/vendor/jquery/jquery-3.2.1.js',
			// $pathTemplate24 . '/assets/vendor/jquery.easing/js/jquery.easing.js',
			// $pathTemplate24 . '/assets/vendor/bootstrap/js/dist/util.js',
			// $pathTemplate24 . '/assets/vendor/bootstrap/js/dist/collapse.js',
			// $pathTemplate24 . '/assets/vendor/fancybox/jquery.fancybox.js',
			$pathTemplate24 . '/assets/js/helpers/onscroll-animation_init.js',
		),
		'css' => array(
			$pathTemplate24 . '/assets/vendor/bootstrap/bootstrap.css',
			$pathTemplate24 . '/themes/themes_core.css',
			$pathTemplate24 . '/assets/css/custom.css',
			$pathTemplate24 . '/assets/css/themes_custom.css',
			$pathTemplate24 . '/assets/vendor/animate.css',
		),
		'rel' => [
			'main.core',
			'mediaplayer',
			'main.polyfill.intersectionobserver',
			'landing.utils',
		],
	),
	
	'landing_critical_grid' => [
		'css' => [
			$pathTemplate24 . '/assets/vendor/bootstrap/bootstrap.css',
			$pathTemplate24 . '/assets/css/custom-grid.css',
			$pathTemplate24 . '/themes/themes_core.css',
		],
	],

	'landing_jquery' => [
		'js' => [
			$pathTemplate24 . '/assets/vendor/jquery/jquery-3.2.1.js',
			$pathTemplate24 . '/assets/vendor/jquery.easing/js/jquery.easing.js',
		],
	],

	'landing_fancybox' => [
		'js' => [
			$pathTemplate24 . '/assets/vendor/fancybox/jquery.fancybox.js',
		],
		'css' => [
			$pathTemplate24 . '/assets/vendor/fancybox/jquery.fancybox.css',
		],
		'rel' => ['landing_jquery']
	],

	// todo: in editor need for video?
	'landing_popup_link' => [
		'js' => [
			$pathTemplate24 . '/assets/js/helpers/popup_init.js',
		],
		'rel' => ['landing_fancybox'],
	],

	'landing_upper' => array(
		'js' => array(
			$pathTemplate24 . '/assets/js/helpers/upper_init.js',
		),
	),
	
	'landing_icon_fonts' => array(
		'css' => array(
			$pathTemplate24 . '/assets/vendor/icon-awesome/css/font-awesome.css',
			$pathTemplate24 . '/assets/vendor/icon-etlinefont/style.css',
			$pathTemplate24 . '/assets/vendor/icon-hs/style.css',
			$pathTemplate24 . '/assets/vendor/icon-line/css/simple-line-icons.css',
			$pathTemplate24 . '/assets/vendor/icon-line-pro/style.css'
		),
	),

	'landing_menu' => [
		'js' => [
			$pathTemplate24 . '/assets/vendor/bootstrap/js/dist/util.js',
			$pathTemplate24 . '/assets/vendor/bootstrap/js/dist/collapse.js',
			$pathTemplate24 . '/assets/js/helpers/menu/hamburgers.js',
			$pathTemplate24 . '/assets/js/helpers/menu/scrollspy.js',
			$pathTemplate24 . '/assets/js/helpers/menu/menu_init.js',
		],
		'css' => [
			$pathTemplate24 . '/assets/vendor/hamburgers/hamburgers.css',
		],
		'lang' => $pathLang . '/js/navbars.php',
		// todo: jquery need just for collapse - to native
		'rel' => ['landing_core', 'landing_jquery'],
	],

	'landing_header' => [
		'js' => [
			$pathTemplate24 . '/assets/js/helpers/menu/block-header-entry.js',
			$pathTemplate24 . '/assets/js/helpers/menu/block-header-init.js',
		],
		'rel' => ['landing_core'],
	],

	'landing_form' => array(
		'js' => array(
			$pathTemplate24 . '/assets/js/helpers/form_init.js',
		),
		'lang' => $pathLang . '/js/webform_alerts.php',
	),

	'landing_gallery_cards' => [
		'js' => [
			$pathTemplate24 . '/assets/js/helpers/gallery_cards_init.js',
		],
		'rel' => ['landing_core', 'landing_fancybox'],
	],

	'landing_chat' => array(
		'js' => array(
			$pathTemplate24 . '/assets/js/helpers/chat_init.js',
		)
	),

	'landing_carousel' => array(
		'js' => array(
			$pathTemplate24 . '/assets/vendor/slick-carousel/slick/slick.js',
			$pathTemplate24 . '/assets/js/components/hs.core_landing.js',
			$pathTemplate24 . '/assets/js/components/hs.carousel.js',
			$pathTemplate24 . '/assets/js/helpers/carousel/carousel_helper.js',
			$pathTemplate24 . '/assets/js/helpers/carousel/base_carousel_init.js',
		),
		'css' => array(
			$pathTemplate24 . '/assets/vendor/slick-carousel/slick/slick.css',
		),
		'rel' => ['landing_core','landing_jquery'],
	),

	'landing_countdown' => array(
		'js' => array(
			$pathTemplate24 . '/assets/vendor/jquery.countdown/jquery.countdown.js',
			$pathTemplate24 . '/assets/js/components/hs.core_landing.js',
			$pathTemplate24 . '/assets/js/components/hs.countdown.js',
			$pathTemplate24 . '/assets/js/helpers/countdown_init.js',
		),
		'rel' => ['landing_core', 'landing_jquery'],
	),

	'landing_google_maps_new' => array(
		'js' => array(
			$pathTemplate24 . '/assets/js/helpers/google_maps_new.js'
		),
		'rel' => array(
			'map_provider'
		)
	),
	
	'landing_lazyload' => [
		'js' => [
			$pathTemplate24 . '/assets/js/helpers/lazyload.js',
		],
	],
	
	'landing_auto_font_scale' => [
		'js' => [
			$pathJS . '/ui/tool/auto-font-scale.js',
			$pathJS . '/ui/tool/auto-font-scale-entry.js',
			$pathTemplate24 . '/assets/js/helpers/auto_font_scale_init.js',
		],
	],
	
	'landing_bootstrap_modal' => array(
		'js' => array(
			$pathTemplate24 . '/assets/vendor/bootstrap/js/dist/util.js',
			$pathTemplate24 . '/assets/vendor/bootstrap/js/dist/modal.js',
		),
		'rel' => ['landing_core','landing_jquery'],
	),
);


foreach ($jsConfig as $code => $ext)
{
	\CJSCore::registerExt($code, $ext);
}