<?php
use Bitrix\Disk\Desktop;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arResult["VARIABLES"]["group_id"], "tasks"))
{
	$APPLICATION->IncludeComponent('bitrix:tasks.import', '', array_merge( array(
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
	), $arResult ), $component);
}
