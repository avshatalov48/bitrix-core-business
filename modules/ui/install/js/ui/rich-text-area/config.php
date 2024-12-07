<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rich-text-area.bundle.css',
	'js' => 'dist/rich-text-area.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.vue3',
		'ui.uploader.core',
		'ui.uploader.vue',
		'ui.uploader.tile-widget',
		'ui.text-editor',
	],
	'skip_core' => false,
];
