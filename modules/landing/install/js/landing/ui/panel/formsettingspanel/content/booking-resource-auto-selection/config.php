<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/booking-resource-auto-selection.bundle.css',
	'js' => 'dist/booking-resource-auto-selection.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'booking.const',
		'landing.loc',
		'landing.ui.card.headercard',
		'landing.ui.card.messagecard',
		'landing.ui.panel.basepresetpanel',
		'landing.ui.form.formsettingsform',
		'ui.sidepanel-content',
	],
	'skip_core' => false,
];