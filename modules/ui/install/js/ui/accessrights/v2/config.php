<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/v2.bundle.css',
	'js' => 'dist/v2.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.dialogs.messagebox',
		'ui.buttons',
		'ui.vue3.components.popup',
		'ui.entity-selector',
		'ui.vue3',
		'ui.vue3.directives.hint',
		'ui.vue3.components.switcher',
		'main.popup',
		'ui.ears',
		'ui.hint',
		'ui.vue3.components.rich-menu',
		'ui.notification',
		'ui.analytics',
		'ui.vue3.vuex',
		'main.core',
		'ui.fonts.opensans',
		'ui.design-tokens',
		'ui.icon-set.main',
		'ui.icon-set.actions',
		'ui.icon-set.crm',
		'ui.icons.b24',
		'ui.forms',
		'ui.icon',
	],
	'skip_core' => false,
];
