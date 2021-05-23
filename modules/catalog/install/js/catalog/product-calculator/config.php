<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/product.calculator.bundle.css',
	'js' => 'dist/product.calculator.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];