<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent("bitrix:im.messenger", "content", Array(
	"CONTEXT" => "PAGE",
	"RECENT" => "Y"
), false, Array("HIDE_ICONS" => "Y"));
