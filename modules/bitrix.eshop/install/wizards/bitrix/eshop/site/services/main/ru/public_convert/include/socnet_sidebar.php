<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->IncludeComponent(
	"bitrix:eshop.socnet.links",
	".default",
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"FACEBOOK" => "https://www.facebook.com/1CBitrix",
		"VKONTAKTE" => "https://vk.com/bitrix_1c",
		"TWITTER" => "https://twitter.com/1c_bitrix",
		"GOOGLE" => "https://plus.google.com/111119180387208976312/",
		"INSTAGRAM" => "https://instagram.com/1CBitrix/"
	),
	false,
	array(
		"HIDE_ICONS" => "N"
	)
);?>