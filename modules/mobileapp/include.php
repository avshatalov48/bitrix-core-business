<?

CModule::AddAutoloadClasses(
	"mobileapp",
	array(
		"CMobile"                => "classes/general/mobile.php",
		"CAdminMobileDetail"     => "classes/general/interface.php",
		"CAdminMobileDetailTmpl" => "classes/general/interface.php",
		"CAdminMobileMenu"       => "classes/general/interface.php",
		"CAdminMobileFilter"     => "classes/general/filter.php",
		"CMobileLazyLoad"        => "classes/general/interface.php",
		"CAdminMobileEdit"       => "classes/general/interface.php",
		"CMobileAppPullSchema"   => "classes/general/pull.php",
		"CAdminMobilePush"       => "classes/general/push.php",
	)
);

$GLOBALS["APPLICATION"]->AddJSKernelInfo('mobileapp', array('/bitrix/js/mobileapp/fastclick.js'));

CJSCore::RegisterExt('mobile_webrtc', array(
		'js'   => '/bitrix/js/mobileapp/mobile_webrtc.js',
		'lang' => '/bitrix/modules/mobileapp/lang/'.LANGUAGE_ID.'/mobile_webrtc.php',
		'rel'=>array("ajax","ls")
	));

CJSCore::RegisterExt('mdesigner', array(
		'js'   => '/bitrix/js/mobileapp/designer.js',
		'css'  => '/bitrix/js/mobileapp/app_designer.css',
		'lang' => '/bitrix/modules/mobileapp/lang/'.LANGUAGE_ID.'/mobile_designer.php',
		'rel' => array('ajax','popup','qrcode')
	));

CJSCore::RegisterExt('mobile_fastclick', array(
		'js'   => '/bitrix/js/mobileapp/fastclick.js',
	));
CJSCore::RegisterExt('mobile_gesture', array(
		'js'   => '/bitrix/js/mobileapp/gesture.js',
	));

?>
