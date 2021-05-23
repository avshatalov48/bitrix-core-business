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
			"PATH_TO_USER" => $arResult["PATH_TO_USER"],
			"PATH_TO_MESSAGES_USERS_MESSAGES" => $arResult["PATH_TO_MESSAGES_USERS_MESSAGES"],
			"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_MESSAGE_FORM_MESS" => $arResult["PATH_TO_MESSAGE_FORM_MESS"],
			"PATH_TO_SMILE" => $arResult["PATH_TO_SMILE"],
			"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
			"PAGE_VAR" => $arResult["ALIASES"]["page"],
			"USER_VAR" => $arResult["ALIASES"]["user_id"],
			"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
			"SET_TITLE" => $arResult["SET_TITLE"],
			"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"MESSAGE_ID" => $arResult["VARIABLES"]["message_id"],
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