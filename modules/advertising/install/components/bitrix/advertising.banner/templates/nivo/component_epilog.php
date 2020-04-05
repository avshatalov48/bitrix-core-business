<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if ($arParams['JQUERY'] === 'Y' || isset($arParams['PREVIEW']))
{
	CJSCore::Init(['jquery2']);
}