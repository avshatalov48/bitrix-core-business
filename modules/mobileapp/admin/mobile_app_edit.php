<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage mobileapp
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("mobileapp");
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
$APPLICATION->SetTitle(GetMessage("MOBILEAPP_APP_DESIGNER_TITLE"));

if (!$USER->isAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");


$action = $_REQUEST["action"];
if($action)
{
	switch ($action)
	{
		case "get_status":
			// Получаем статус заявки по ключу
			break;
		case "push_info":
			//Пушим информацию на сервер с указанием ключа
			/**
			 * Высылаем поля
			 * LICENSE_KEY
			 * DEV_ACCESS
			 * PLATFORMS
			 * CONTACT_EMAIL
			 */
			break;
		case "post_comment":
			/*
			//Пост коммента, проверяем ключ на сервере
			//Если
			*/
			break;
	}

	return;
}

CUtil::InitJSCore(Array('ajax', 'window', "popup"));

$tabs= array(
	array(
		"DIV" => "main_params",
		"TAB" => "Основные",
		"TITLE" => "Настройка основных параметров",
	),
	array(
		"DIV" => "images",
		"TAB" => "Изображения",
		"TITLE" => "Изображения",
	),
	array(
		"DIV" => "dev_access",
		"TAB" => "Доступ",
		"TITLE" => "Доступ",
	)
);


$tabControl = new CAdminForm("AppEditForm", $tabs);
$tabControl->Begin();

//Main Tab start
$tabControl->BeginNextFormTab();
$tabControl->AddEditField("NAME", "Название приложения", true, array("size" => 30, "maxlength" => 255));
$tabControl->AddEditField("SHORT_NAME", "Краткое приложения", true, array("size" => 30, "maxlength" => 12));
$tabControl->AddEditField("APP_FOLDER", "Папка приложения", true, array("size" => 30, "maxlength" => 255));
$tabControl->AddEditField("APP_FOLDER", "Папка приложения", true, array("size" => 30, "maxlength" => 255));
$tabControl->AddDropDownField("PLATFORM_LIST", "Платформы", true,array(
	"both"=>"Android и iOS",
	"android"=>"Android",
	"ios"=>"iOS"
));

$tabControl->AddCheckBoxField("SELF_PUBLISH","Буду публиковать сам",false,false,array());
$tabControl->AddTextField("DESC","Описание","",array("rows"=>10, "cols"=>55),true);
$tabControl->AddTextField("INFO", "Дополнительная информация", "",array("rows" => 10, "cols" => 55),false);


//Image Tab start
$tabControl->BeginNextFormTab();
$platforms = \Bitrix\MobileApp\AppTable::getSupportedPlatforms();

foreach($platforms as $platform)
{
	$tabControl->AddSection("res_" . $platform, $platform, array(), false);
	$resources = \Bitrix\MobileApp\AppResource::get($platform);

	foreach ($resources as $resGroupName => $resGroup)
	{
		if(!empty($resGroup))
		{
			foreach ($resGroup as $res)
			{
				$iconName = str_replace("#size#", $res["width"] . "x" . $res["height"], $res["name"]);
				$tabControl->AddFileField($platform . "_ICON_" . $res["width"], $iconName, array(), false);
			}
		}
	}
}

//Dev Access Tab start
$tabControl->BeginNextFormTab();
$tabControl->AddSection("google", "Консоль разарботчика Google Play", array(), false);
$tabControl->AddEditField("login_google", "Логин: ", true, array("size" => 30, "maxlength" => 255));
$tabControl->AddEditField("password_google", "Пароль: ", true, array("size" => 30, "maxlength" => 255));

$tabControl->AddSection("apple", "Консоль разарботчика Apple Developer Center", array(), false);

$tabControl->AddEditField("login_apple", "Логин: ", true, array("size" => 30, "maxlength" => 255));
$tabControl->AddEditField("password_apple", "Пароль: ", true, array("size" => 30, "maxlength" => 255));


$tabControl->ShowTabButtons();
$tabControl->Show();
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php"); ?>


