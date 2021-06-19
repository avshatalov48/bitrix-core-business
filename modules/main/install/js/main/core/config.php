<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'core.js',
	'lang' => BX_ROOT.'/modules/main/js_core.php',
	'rel' => [],
	'includes' => [
		'ajax',
		'promise',
		'loadext',
		'main.polyfill.promise',
		'main.polyfill.includes',
		'main.polyfill.closest',
		'main.polyfill.fill',
		'main.polyfill.find',
		'main.polyfill.matches',
		'main.polyfill.core',
		'main.lazyload',
		'main.parambag',
	],
];