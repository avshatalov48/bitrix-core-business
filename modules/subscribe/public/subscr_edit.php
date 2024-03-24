<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/* @var CMain APPLICATION */
$APPLICATION->SetTitle('');
?><?php $APPLICATION->IncludeComponent(
	'bitrix:subscribe.edit',
	'',
	[
		'SHOW_HIDDEN' => 'N',
		'ALLOW_ANONYMOUS' => 'Y',
		'SHOW_AUTH_LINKS' => 'Y',
		'CACHE_TIME' => '3600',
		'SET_TITLE' => 'Y'
	]
);?><?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
