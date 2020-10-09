<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

if ($arResult['userField']['VALUE'])
{
	foreach($arResult['userField']['VALUE'] as $res)
	{
		?>
		<input
			type="text"
			name="<?= $arParams['userField']['FIELD_NAME'] ?>"
			value="<?= HtmlFilter::encode($res) ?>"
			<?= ((int)$arParams['userField']['SETTINGS']['SIZE'] > 0 ? 'size="' . $arParams['userField']['SETTINGS']['SIZE'] . '"' : '') ?>
			<?= ((int)$arParams['userField']['SETTINGS']['MAX_LENGTH'] > 0 ? 'maxlength="' . $arParams['userField']['SETTINGS']['MAX_LENGTH'] . '"' : '') ?>
			<?= ($arParams['userField']['EDIT_IN_LIST'] !== 'Y' ? 'disabled="disabled"' : '') ?>
			class='fields string'>
		<?php
	}
}