<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('WIKI_HISTORY_DIFF_NAME'),
	'DESCRIPTION' => GetMessage('WIKI_HISTORY_DIFF_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 60,
	'PATH' => array(
		'ID' => 'content',
		'CHILD' => array(
			'ID' => 'wiki',
			'NAME' => GetMessage('WIKI_NAME')
		)
	)
);
?>