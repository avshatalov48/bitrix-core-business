<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */

$APPLICATION->IncludeComponent(
	'bitrix:main.userconsent.selector',
	'',
	array(
		'ID' => $arParams['ID'],
		'INPUT_NAME' => $arParams['INPUT_NAME'],
		'PATH_TO_ADD' => $arParams['PATH_TO_ADD'],
		'PATH_TO_EDIT' => $arParams['PATH_TO_EDIT'],
		'PATH_TO_CONSENT_LIST' => $arParams['PATH_TO_CONSENT_LIST'],
		'ACTION_REQUEST_URL' => $arParams['ACTION_REQUEST_URL'],
		'CAN_EDIT' => $arResult['CAN_EDIT']
	)
);