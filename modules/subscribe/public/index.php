<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/* @var CMain APPLICATION */
$APPLICATION->SetTitle(GetMessage('SUBSCRIBE_INSTALL_PUBLIC_TITLE'));
?><?php $APPLICATION->IncludeComponent('bitrix:subscribe.index', '.default', [
	'SHOW_COUNT' => 'N',
	'SHOW_HIDDEN' => 'N',
	'PAGE' => 'subscr_edit.php',
	'CACHE_TIME' => '3600',
	'SET_TITLE' => 'Y'
]
);?><?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
