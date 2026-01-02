<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @deprecated Since iblock 25.100.0
 * @see ui.field-selector
 */

return [
	'js' => 'dist/field-selector.bundle.js',
	'rel' => [
		'ui.entity-selector',
		'main.core',
	],
	'skip_core' => false,
];
