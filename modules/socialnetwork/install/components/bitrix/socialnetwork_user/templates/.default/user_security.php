<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user";
include("util_menu.php");

if (isset($arResult["VARIABLES"]["user_id"]) && $USER->GetID() !== $arResult["VARIABLES"]["user_id"])
{
	ShowError(GetMessage("SONET_PASS_ACCESS_ERROR"));
	return;
}

$path = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER"], array("user_id" => $arResult["VARIABLES"]["user_id"]));
$path = CHTTP::urlAddParams($path, array("otp" => "Y"));

$APPLICATION->IncludeComponent("bitrix:security.user.otp.init", "", array(
	"SUCCESSFUL_URL" => $path
));
?>