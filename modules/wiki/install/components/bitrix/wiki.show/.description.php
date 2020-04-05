<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('WIKI_SHOW_NAME'),
	'DESCRIPTION' => GetMessage('WIKI_SHOW_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 10,
	'PATH' => array(
		'ID' => 'content',
		'CHILD' => array(
			'ID' => 'wiki',
			'NAME' => GetMessage('WIKI_NAME')
		)
	),
	'CACHE_PATH' => 'Y'
);
?>