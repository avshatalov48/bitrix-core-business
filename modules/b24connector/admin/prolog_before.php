<?
define("ADMIN_MODULE_NAME", "b24connector");

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

$moduleAccess = $APPLICATION->GetGroupRight(ADMIN_MODULE_NAME);
$errorMsgs = array();

if($moduleAccess < "R")
	$APPLICATION->AuthForm(Loc::getMessage("B24C_PB_ACCESS_DENIED"));

if(!\Bitrix\Main\Loader::includeModule(ADMIN_MODULE_NAME))
	$errorMsgs[] = Loc::getMessage('B24C_PB_MODULE_B24C_NOT_INSTALLED');

if(!\Bitrix\Main\Loader::includeModule("socialservices"))
{
	$errorMsgs[] = Loc::getMessage('B24C_PB_MODULE_SS_NOT_INSTALLED',array(
		'#A1#' => '<a href="/bitrix/admin/module_admin.php?lang='.LANGUAGE_ID.'">',
		'#A2#' => '</a>'
	));
}

\Bitrix\Main\UI\Extension::load("main.core");
Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="/bitrix/css/b24connector/style.css">');
Asset::getInstance()->addJs("/bitrix/js/b24connector/connector.js");

$jsLangMesIds = array(
	"B24C_PB_CHOOSE_PORTAL",
	"B24C_PB_CHOOSE_PORTALT",
	"B24C_PB_MY_B24",
	"B24C_PB_MY_SITE"
);

$jsLang = '<script type="text/javascript">BX.ready(function(){'."\n";

foreach($jsLangMesIds as $langMesId)
	$jsLang .= 'BX.message["'.$langMesId.'"] = "'.\CUtil::JSEscape(Loc::getMessage($langMesId)).'";'."\n";

$jsLang .= '});</script>'."\n";

Asset::getInstance()->addString($jsLang);