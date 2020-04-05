<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent("bitrix:imopenlines.livechat", "", Array(
	"CONTEXT" => $arResult["CONTEXT"],
	"CONFIG_ID" => $arResult["CONFIG_ID"]
), false, Array("HIDE_ICONS" => "Y"));
