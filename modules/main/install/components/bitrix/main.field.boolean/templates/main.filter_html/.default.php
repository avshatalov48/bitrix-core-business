<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BooleanType;

/**
 * @var array $arResult
 * @var BooleanUfComponent $component
 */

$component = $this->getComponent();
$label = BooleanType::getLabels($arResult['userField']);
$value = $arResult['additionalParameters']['VALUE'];
?>

<select
	name="<?= $arResult['additionalParameters']['NAME'] ?>"
>
	<option
		value=""
		<?= (empty($value) ? ' selected' : '') ?>>
		<?= Loc::getMessage('MAIN_ALL') ?>
	</option>
	<option
		value="1"
		<?= ($value ? ' selected' : '') ?>
	>
		<?= HtmlFilter::encode($label[1]) ?>
	</option>
	<option
		value="0"
		<?= (mb_strlen($value) && !$value ? ' selected' : '') ?>
	>
		<?= HtmlFilter::encode($label[0]) ?>
	</option>
</select>
