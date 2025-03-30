<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle(GetMessage('SEARCH_INSTALL_PUBLIC_MAP_TITLE'));
?><?php $APPLICATION->IncludeComponent('bitrix:main.map', '.default', [
	'LEVEL'	=>	'3',
	'COL_NUM'	=>	'2',
	'SHOW_DESCRIPTION'	=>	'Y',
	'SET_TITLE'	=>	'Y',
	'CACHE_TIME'	=>	'3600'
]
);?><?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
