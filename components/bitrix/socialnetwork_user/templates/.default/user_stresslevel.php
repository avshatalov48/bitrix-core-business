<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user_stresslevel";
include("util_menu.php");

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	[
		'POPUP_COMPONENT_NAME' => "bitrix:intranet.stresslevel",
		"POPUP_COMPONENT_TEMPLATE_NAME" => "",
		"POPUP_COMPONENT_PARAMS" => [
			'USER_ID' => $arResult["VARIABLES"]["user_id"],
			'PAGE' => $_GET['page']
		],
		"USE_PADDING" => false,
		"POPUP_COMPONENT_PARENT" => $this->getComponent()
	]
);
?>