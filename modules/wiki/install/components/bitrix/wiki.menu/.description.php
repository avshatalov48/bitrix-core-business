<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('WIKI_MENU_NAME'),
	'DESCRIPTION' => GetMessage('WIKI_MENU_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 5,
	'PATH' => array(
		'ID' => 'content',
		'CHILD' => array(
			'ID' => 'wiki',
			'NAME' => GetMessage('WIKI_NAME')
		)
	)
);
?>