<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (defined('USE_UI_DESIGN_TOKENS') && USE_UI_DESIGN_TOKENS === true)
{
	return [
		'css' => 'dist/ui.design-tokens.bundle.css',
	];
}

return [];