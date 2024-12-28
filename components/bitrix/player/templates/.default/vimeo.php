<?
/** @var array $arResult */
/** @var array $arParams */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$aspectRatio = $arParams['WIDTH'] > 0 && $arParams['HEIGHT'] > 0 ? "{$arParams['WIDTH']} / {$arParams['HEIGHT']}" : 'auto';
?>

<iframe
	src="<?=$arResult['VIMEO_EMBEDDED']?>"
	width="<?=$arParams["WIDTH"]?>"
	style="aspect-ratio: <?=$aspectRatio?>"
	frameborder="0"
	allow="autoplay; fullscreen; picture-in-picture; clipboard-write"
></iframe>
