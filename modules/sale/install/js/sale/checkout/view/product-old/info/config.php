<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/info.bundle.css',
	'js' => 'dist/info.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'sale.checkout.view.element.button.remove',
		'sale.checkout.view.element.button.plus',
		'sale.checkout.view.element.button.minus',
		'sale.checkout.view.product.props-list',
	],
	'skip_core' => true,
];