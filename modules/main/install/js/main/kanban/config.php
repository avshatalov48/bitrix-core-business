<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js'  => [
		'grid.js',
		'column.js',
		'item.js',
		'dropzone-area.js',
		'dropzone.js',
		'utils.js'
	],
	'css' => [
		'css/kanban.css',
	],
	'lang' => BX_ROOT.'/modules/main/js/kanban.php',
	'rel' => [
		'color_picker',
		'dnd',
	],
	'bundle_js' => 'kanban',
	'bundle_css' => 'kanban'
];