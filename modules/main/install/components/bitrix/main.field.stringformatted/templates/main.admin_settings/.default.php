<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 * @var array $additionalParameters
 */
$additionalParameters = $arResult['additionalParameters'];
?>

<tr>
	<td>
		<?= Loc::getMessage('USER_TYPE_STRINGFMT_PATTERN') ?>:
	</td>
	<td>
		<textarea
			name="<?= $additionalParameters['NAME'] ?>[PATTERN]"
			cols="40"
			rows="5"
		><?= $arResult['values']['pattern'] ?></textarea>
	</td>
</tr>
<tr>
	<td>
		<?= Loc::getMessage('USER_TYPE_STRINGFMT_DEFAULT_VALUE') ?>:
	</td>
	<td>
		<input
			type="text"
			name="<?= $additionalParameters['NAME'] ?>[DEFAULT_VALUE]"
			size="20"
			maxlength="225"
			value="<?= $arResult['values']['defaultValue'] ?>"
		>
	</td>
</tr>
<tr>
	<td>
		<?= Loc::getMessage('USER_TYPE_STRINGFMT_SIZE') ?>:
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
		<?= Loc::getMessage('USER_TYPE_STRINGFMT_ROWS') ?>:
	</td>
	<td>
		<input
			type="text"
			name="<?= $additionalParameters["NAME"] ?>[ROWS]"
			size="20"
			maxlength="20"
			value="<?= $arResult['values']['rows'] ?>"
		>
	</td>
</tr>
<tr>
	<td>
		<?= Loc::getMessage('USER_TYPE_STRINGFMT_MIN_LEGTH') ?>:
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
		<?= Loc::getMessage('USER_TYPE_STRINGFMT_MAX_LENGTH') ?>:
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
<tr>
	<td>
		<?= Loc::getMessage('USER_TYPE_STRINGFMT_REGEXP') ?>:
	</td>
	<td>
		<input
			type="text"
			name="<?= $additionalParameters["NAME"] ?>[REGEXP]"
			size="20"
			maxlength="200"
			value="<?= $arResult['values']['regexp'] ?>"
		>
	</td>
</tr>