<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/info-deleted.bundle.css',
	'js' => 'dist/info-deleted.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'sale.checkout.view.element.button.resotre',
		'sale.checkout.view.product.props-list',
	],
	'skip_core' => true,
];