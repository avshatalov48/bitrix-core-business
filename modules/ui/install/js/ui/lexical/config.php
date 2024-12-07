<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}


$debugMode = defined('LEXICAL_DEBUG') && LEXICAL_DEBUG === true;

return [
	'js' => $debugMode ? './dev/dist/lexical.dev.bundle.js' : './prod/dist/lexical.prod.bundle.min.js',
	'skip_core' => true,
];
