<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$APPLICATION->IncludeComponent("bitrix:im.jitsi.conference", "", [
	"ALIAS" => $arResult["ALIAS"],
	"CHAT_ID" => $arResult["CHAT_ID"],
	"WRONG_ALIAS" => $arResult["WRONG_ALIAS"]
], false, Array("HIDE_ICONS" => "Y"));
