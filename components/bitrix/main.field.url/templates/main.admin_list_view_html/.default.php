<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var UrlUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();

if($arResult['additionalParameters']['VALUE'])
{
	$url = $component->getHtmlBuilder()->encodeUrl($arResult['additionalParameters']['VALUE']);
	$popup = $arResult['userField']['SETTINGS']['POPUP'];
	?>
	<a
		href="<?= $url ?>"
		<?= ($popup !== 'N' ? ' target="_blank"' : '') ?>
	>
		<?= $arResult['additionalParameters']['VALUE'] ?>
	</a>
	<?php
}
else
{
	print '&nbsp;';
}