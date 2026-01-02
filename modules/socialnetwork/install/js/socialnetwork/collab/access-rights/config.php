<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/access-rights.bundle.css',
	'js' => 'dist/access-rights.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.form-elements.view',
		'ui.forms',
		'ui.hint',
		'main.core',
		'ui.design-tokens',
		'ui.sidepanel-content',
	],
	'skip_core' => false,
];
