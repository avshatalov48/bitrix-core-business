<?php

use Bitrix\Catalog\Config\State;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isProductBatchMethodSelected = false;
if (Loader::includeModule('catalog'))
{
	$isProductBatchMethodSelected = State::isProductBatchMethodSelected();
}

return [
	'css' => 'dist/document-grid.bundle.css',
	'js' => 'dist/document-grid.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.buttons',
		'catalog.store-use',
		'ui.dialogs.messagebox',
	],
	'skip_core' => false,
	'settings' => [
		'isProductBatchMethodSelected' => $isProductBatchMethodSelected,
	],
];