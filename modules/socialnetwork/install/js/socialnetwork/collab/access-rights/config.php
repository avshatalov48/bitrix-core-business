<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/access-rights.bundle.css',
	'js' => 'dist/access-rights.bundle.js',
	'rel' => [
		'ui.form-elements.view',
		'ui.forms',
		'ui.hint',
		'main.core',
		'main.core.events',
		'ui.design-tokens',
		'ui.sidepanel-content',
	],
	'skip_core' => false,
];
