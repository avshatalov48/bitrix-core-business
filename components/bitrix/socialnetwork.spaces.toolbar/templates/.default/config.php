<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'script.css',
	'js' => 'script.js',
	'rel' => [
		'ui.short-view',
		'pull.client',
		'tasks.kanban-sort',
		'ui.label',
		'ui.entity-selector',
		'tasks.creation-menu',
		'calendar.entry',
		'socialnetwork.post-form',
		'main.popup',
		'ui.popupcomponentsmaker',
		'ui.switcher',
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];