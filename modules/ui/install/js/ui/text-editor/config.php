<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/text-editor.bundle.css',
	'js' => 'dist/text-editor.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.bbcode.parser',
		'ui.bbcode.model',
		'ui.smiley',
		'ui.code-parser',
		'ui.video-service',
		'ui.typography',
		'ui.icon-set.main',
		'ui.icon-set.editor',
		'ui.icon-set.actions',
		'ui.design-tokens',
		'ui.forms',
		'ui.lexical',
	],
	'skip_core' => false,
];
