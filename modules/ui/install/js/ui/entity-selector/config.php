<?

use Bitrix\Main\Loader;
use Bitrix\UI\EntitySelector\Configuration;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loader::includeModule('ui');
$extensions = Configuration::getExtensions();

return [
	'css' => 'dist/entity-selector.bundle.css',
	'js' => 'dist/entity-selector.bundle.js',
	'rel' => [
		'main.popup',
		'main.core',
		'main.loader',
		'ui.fonts.opensans',
		'ui.design-tokens',
	],
	'post_rel' => $extensions,
	'settings' => [
		'extensions' => $extensions
	],
	'skip_core' => false,
];