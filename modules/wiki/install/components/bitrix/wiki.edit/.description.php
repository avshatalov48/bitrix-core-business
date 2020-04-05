<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('WIKI_EDIT_NAME'),
	'DESCRIPTION' => GetMessage('WIKI_EDIT_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 20,
	'PATH' => array(
		'ID' => 'content',
		'CHILD' => array(
			'ID' => 'wiki',
			'NAME' => GetMessage('WIKI_NAME')
		)
	),
);
?>