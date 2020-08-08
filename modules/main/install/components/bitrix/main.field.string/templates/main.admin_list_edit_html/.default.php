<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\HtmlBuilder;

/**
 * @var StringUfComponent $component
 * @var array $arResult
 * @var HtmlBuilder $htmlBuilder
 */

$htmlBuilder = $this->getComponent()->getHtmlBuilder();

if($arResult['fieldValues']['tag'] === 'input')
{
	?>
	<input
		<?= $htmlBuilder->buildTagAttributes($arResult['fieldValues']['attrList']) ?>
	>
	<?php
}
else
{
	?>
	<textarea
		<?= $htmlBuilder->buildTagAttributes($arResult['fieldValues']['attrList']) ?>
	><?= $arResult['fieldValues']['value'] ?></textarea>
	<?php
}