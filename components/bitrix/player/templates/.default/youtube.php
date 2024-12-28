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
	src="<?=$arResult['YOUTUBE_EMBEDDED']?>"
	width="<?=$arParams['WIDTH']?>"
	style="aspect-ratio: <?=$aspectRatio?>"
	frameborder="0"
	allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
	referrerpolicy="strict-origin-when-cross-origin"
	allowfullscreen
></iframe>
