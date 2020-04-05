<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$pageId = "user";
include("util_menu.php");

$path = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER"], array("user_id" => $arResult["VARIABLES"]["user_id"]));

$APPLICATION->IncludeComponent("bitrix:security.user.otp.init", "", array(
	"SUCCESSFUL_URL" => $path
));
?>