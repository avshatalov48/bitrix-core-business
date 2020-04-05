<?
CModule::AddAutoloadClasses(
	"storeassist",
	array(
		"CStoreAssist" => "classes/general/storeassist.php",
	)
);

CJSCore::RegisterExt('storeassist', array(
	'js' => '/bitrix/js/storeassist/storeassist.js',
	'css' => '/bitrix/js/storeassist/css/storeassist.css',
	'lang' => BX_ROOT.'/modules/storeassist/lang/'.LANGUAGE_ID.'/jsmessages.php',
));
?>