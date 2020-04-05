<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$APPLICATION->includeComponent(
	'bitrix:mail.client.message.list', '',
	$arResult,
	$component
);
