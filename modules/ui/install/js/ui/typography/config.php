<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/typography.bundle.css',
	'rel' => [
		'ui.design-tokens',
		'ui.icon-set.actions',
		'ui.icon-set.main',
	],
	'skip_core' => true,
];
