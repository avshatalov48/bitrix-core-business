<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

$additionalParameters = $arResult['additionalParameters'];
?>

<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_URL_POPUP') ?>:</span>
	</td>
	<td>
		<input
			type="hidden"
			name="<?= $additionalParameters['NAME'] ?>[POPUP]"
			value="N"
		>
		<label class="adm-detail-label">
			<input
				type="checkbox"
				name="<?= $additionalParameters['NAME'] ?>[POPUP]"
				value="Y"
				<?= ($arResult['values']['popup'] === 'Y' ? ' checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('MAIN_YES') ?></span>
		</label>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_STRING_DEFAULT_VALUE') ?>:</span>
	</td>
	<td>
		<input
			type="text"
			name="<?= $additionalParameters['NAME'] ?>[DEFAULT_VALUE]"
			size="20"
			maxlength="225"
			value="<?= $arResult['values']['default_value'] ?>"
		>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_STRING_SIZE') ?>:</span>
	</td>
	<td>
		<input
			type="text"
			name="<?= $additionalParameters['NAME'] ?>[SIZE]"
			size="20"
			maxlength="20"
			value="<?= $arResult['values']['size'] ?>"
		>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_STRING_MIN_LEGTH') ?>:</span>
	</td>
	<td>
		<input
			type="text"
			name="<?= $additionalParameters['NAME'] ?>[MIN_LENGTH]"
			size="20"
			maxlength="20"
			value="<?= $arResult['values']['min_length'] ?>"
		>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_STRING_MAX_LENGTH') ?>:</span>
	</td>
	<td>
		<input
			type="text"
			name="<?= $additionalParameters['NAME'] ?>[MAX_LENGTH]"
			size="20"
			maxlength="20"
			value="<?= $arResult['values']['max_length'] ?>"
		>
	</td>
</tr>