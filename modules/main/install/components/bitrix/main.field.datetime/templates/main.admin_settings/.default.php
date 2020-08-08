<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\DateType;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load('date');

$name = $arResult['additionalParameters']['NAME'];
$defaultDateTime = $arResult['defaultDateTime'];
$useSeconds = $arResult['useSeconds'];
$useTimezone = $arResult['useTimezone'];
$type = $arResult['type'];
?>

<tr>
	<td	class="adm-detail-valign-top">
		<?= Loc::getMessage('USER_TYPE_DT_DEFAULT_VALUE') ?>:
	</td>
	<td>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DEFAULT_VALUE][TYPE]"
				value="<?= DateType::TYPE_NONE ?>"
				<?= (DateType::TYPE_NONE === $type ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_DT_NONE') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DEFAULT_VALUE][TYPE]"
				value="<?= DateType::TYPE_NOW ?>"
				<?= (DateType::TYPE_NOW === $type ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_DT_NOW') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DEFAULT_VALUE][TYPE]"
				value="<?= DateType::TYPE_FIXED ?>"
				<?= (DateType::TYPE_FIXED === $type ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label adm-detail-label-input"><?= CAdminCalendar::CalendarDate(
				$arResult['additionalParameters']['NAME'] . '[DEFAULT_VALUE][VALUE]',
				$defaultDateTime,
                    20,
                    true
			) ?></span>
		</label>
		<br>
	</td>
</tr>

<tr>
	<td class="adm-detail-valign-top">
		<?= Loc::getMessage('USER_TYPE_DT_USE_SECOND') ?>:
	</td>
	<td>
		<input type="hidden" name="<?= $name ?>[USE_SECOND]" value="N"/>
		<label class="adm-detail-label">
			<input
				type="checkbox"
				value="Y"
				name="<?= $name ?>[USE_SECOND]"
				<?= ($useSeconds === 'Y' ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('MAIN_YES') ?></span>
		</label>
	</td>
</tr>

<tr>
	<td class="adm-detail-valign-top">
		<?= Loc::getMessage('USER_TYPE_DT_USE_TIMEZONE') ?>:
	</td>
	<td>
		<input type="hidden" name="<?= $name ?>[USE_TIMEZONE]" value="N"/>
		<label class="adm-detail-label">
			<input
				type="checkbox"
				value="Y"
				name="<?= $name ?>[USE_TIMEZONE]"
				<?= ($useTimezone !== 'N' ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('MAIN_YES') ?></span>
		</label>
	</td>
</tr>