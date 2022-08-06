<?php
\CJSCore::RegisterExt("sender_admin", Array(
	"js" =>    "/bitrix/js/sender/admin.js",
	"lang" =>    "/bitrix/modules/sender/lang/" . LANGUAGE_ID . "/js_admin.php",
	"rel" =>   array()
));

\CJSCore::RegisterExt("ajax_action", Array(
	"js" =>    array("/bitrix/js/sender/ajax_action/script.js"),
	"css" =>    array("/bitrix/js/sender/ajax_action/style.css"),
	"lang" =>    "/bitrix/modules/sender/lang/" . LANGUAGE_ID . "/js_ajax_action.php",
	"rel" =>   array('core', 'ui.design-tokens', 'ajax', 'popup')
));

CJSCore::RegisterExt('sender_stat', array(
	'js' => array(
		'/bitrix/js/main/amcharts/3.3/amcharts.js',
		'/bitrix/js/main/amcharts/3.3/serial.js',
		'/bitrix/js/main/amcharts/3.3/themes/light.js',
		'/bitrix/js/sender/heatmap/script.js',
		'/bitrix/js/sender/stat/script.js'
	),
	'css' => array(
		'/bitrix/js/sender/stat/style.css'
	),
	'rel' => array('core', 'ui.design-tokens', 'ui.fonts.opensans', 'ajax', 'date')
));

CJSCore::RegisterExt('sender_page', array(
	'js' => array(
		'/bitrix/js/sender/page/script.js'
	),
	'css' => array(
		'/bitrix/js/sender/page/style.css'
	),
	'rel' => array('core', 'sidepanel', 'fx', 'date')
));

CJSCore::RegisterExt('sender_helper', array(
	'js' => array(
		'/bitrix/js/sender/helper/script.js'
	),
	'css' => array(
		'/bitrix/js/sender/helper/style.css'
	),
	'rel' => array('core', 'fx', 'ui.fonts.opensans')
));

CJSCore::RegisterExt('sender_agreement', array(
	'js' => '/bitrix/js/sender/agreement/script.js',
	'css' => '/bitrix/js/sender/agreement/style.css',
	'lang' => '/bitrix/modules/sender/lang/' . LANGUAGE_ID . '/js_agreement.php',
	'lang_additional' => array(
		'SENDER_AGREEMENT_IS_REQUIRED' => false,
		'SENDER_AGREEMENT_TEXT' => \Bitrix\Sender\Security\Agreement::getText(true)
	),
	'rel' => array('core', 'ui.design-tokens', 'popup', 'ajax_action', 'sender_helper')
));

CJSCore::RegisterExt('sender_b24_license', array(
	'js' => '/bitrix/js/sender/b24_license/script.js',
	'css' => '/bitrix/js/sender/b24_license/style.css',
	'lang' => '/bitrix/modules/sender/lang/' . LANGUAGE_ID . '/js_b24_license.php',
));

CJSCore::RegisterExt('sender_b24_feedback', array(
	'js' => '/bitrix/js/sender/b24_feedback.js',
	'rel' => array('core')
));