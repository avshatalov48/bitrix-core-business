<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load('date');

$name = $arResult['additionalParameters']['NAME'];
$value = $arResult['default_value'];
$type = $arResult['default_value_type'];
?>

<tr>
	<td class="adm-detail-valign-top">
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_D_DEFAULT_VALUE') ?>:</span>
	</td>
	<td>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DEFAULT_VALUE][TYPE]"
				value="<?= DateType::TYPE_NONE ?>"
				<?= (DateType::TYPE_NONE === $type ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_D_NONE') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DEFAULT_VALUE][TYPE]"
				value="<?= DateType::TYPE_NOW ?>"
				<?= (DateType::TYPE_NOW === $type ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_D_NOW') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DEFAULT_VALUE][TYPE]"
				value="<?= DateType::TYPE_FIXED ?>"
				<?= (DateType::TYPE_FIXED === $type ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text adm-detail-label-text-input"><?= CAdminCalendar::CalendarDate(
				$arResult['additionalParameters']['NAME'] . '[DEFAULT_VALUE][VALUE]',
				$value
				) ?></span>
		</label>
		<br>
	</td>
</tr>