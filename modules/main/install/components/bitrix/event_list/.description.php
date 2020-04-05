<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('EVENT_LIST_NAME'),
	'DESCRIPTION' => GetMessage('EVENT_LIST_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 250,
	'PATH' => array(
		'ID' => 'utility',//'event-list',		
	),   
	'CACHE_PATH' => 'Y'
);
?>