<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!IsModuleInstalled("im")):

	$APPLICATION->RestartBuffer();
	?><html>
	<head>
	<?$APPLICATION->ShowHead();?>
	<title><?$APPLICATION->ShowTitle()?></title>
	</head>
	<body class="socnet-chat"><?
	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.messages_chat", 
		"", 
		Array(
			"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_MESSAGE_FORM_MESS" => $arResult["PATH_TO_MESSAGE_FORM_MESS"],
			"PATH_TO_SMILE" => $arResult["PATH_TO_SMILE"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
			"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		),
		$component 
	);
	?></body>
	</html><?
	die();

endif;
?>