<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('WIKI_COMPLEX_NAME'),
	'DESCRIPTION' => GetMessage('WIKI_COMPLEX_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'COMPLEX' => 'Y',
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