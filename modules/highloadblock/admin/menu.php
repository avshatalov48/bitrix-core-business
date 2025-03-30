<?php
use Bitrix\Main\Localization\Loc;

/** @var CUser $USER */

if (!\Bitrix\Main\Loader::includeModule('highloadblock'))
{
	return false;
}

// items
$items = [];
$res = \Bitrix\Highloadblock\HighloadBlockTable::getList([
	'select' => [
		'*',
		'NAME_LANG' => 'LANG.NAME',
	],
	'order' => [
		'NAME_LANG' => 'ASC',
		'NAME' => 'ASC',
	],
]);
while ($row = $res->fetch())
{
	$items[$row['ID']] = [
		'text' => $row['NAME_LANG'] != '' ? $row['NAME_LANG'] : $row['NAME'],
		'url' => 'highloadblock_rows_list.php?ENTITY_ID='.$row['ID'].'&lang=' . LANGUAGE_ID,
		'module_id' => 'highloadblock',
		'more_url' => [
			'highloadblock_row_edit.php?ENTITY_ID='.$row['ID'],
			'highloadblock_entity_edit.php?ID='.$row['ID'],
		],
	];
}

// check rights
if (!$USER->isAdmin() && !empty($items))
{
	$rights = \Bitrix\HighloadBlock\HighloadBlockRightsTable::getOperationsName(array_keys($items));
	if (!empty($rights))
	{
		foreach ($items as $hlId => $item)
		{
			if (!isset($rights[$hlId]))
			{
				unset($items[$hlId]);
			}
		}
	}
	else
	{
		return false;
	}
}

// export / import
if ($USER->isAdmin())//@todo add access
{
	$ieItems = [];
	$ieItems[] = [
		'text' => Loc::getMessage('HLBLOCK_ADMIN_MENU_IMPORT'),
		'url' => 'highloadblock_import.php?lang='.LANGUAGE_ID,
		'module_id' => 'highloadblock',
		'items_id' => 'highloadblock_import',
	];
	if (!empty($items))
	{
		$ieItems[] = [
			'text' => Loc::getMessage('HLBLOCK_ADMIN_MENU_EXPORT'),
			'url' => 'highloadblock_export.php?lang=' . LANGUAGE_ID,
			'module_id' => 'highloadblock',
			'items_id' => 'menu_highloadblock_export',
		];
	}
	$items[] = [
		'text' => Loc::getMessage('HLBLOCK_ADMIN_MENU_IE'),
		'url' => '',
		'module_id' => 'highloadblock',
		'items_id' => 'highloadblock_tools',
		'items' => $ieItems,
		'more_url' => [
			'highloadblock_import.php',
			'highloadblock_export.php',
		],
	];
}

// menu
if (!empty($items))
{
	return [
		'parent_menu' => 'global_menu_content',
		'section' => 'highloadblock',
		'sort' => 350,
		'text' => Loc::getMessage('HLBLOCK_ADMIN_MENU_TITLE'),
		'url' => $USER->isAdmin() ? 'highloadblock_index.php?lang=' . LANGUAGE_ID : '',
		'icon' => 'highloadblock_menu_icon',
		'page_icon' => 'highloadblock_page_icon',
		'more_url' => [
			'highloadblock_entity_edit.php',
			'highloadblock_rows_list.php',
			'highloadblock_row_edit.php'
		],
		'items_id' => 'menu_highloadblock',
		'items' => $items,
	];
}
else
{
	return false;
}
