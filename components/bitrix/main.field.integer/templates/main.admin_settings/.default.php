<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$name = $arResult['additionalParameters']['NAME'] ?? '';
?>

<tr>
	<td><?= Loc::getMessage('USER_TYPE_INTEGER_DEFAULT_VALUE') ?>:</td>
	<td>
		<input
			type="text"
			name="<?= $name ?>[DEFAULT_VALUE]"
			size="20"
			maxlength="225"
			value="<?= ($arResult['userField']['SETTINGS']['DEFAULT_VALUE'] ?? '') ?>"
		>
	</td>
</tr>
<tr>
	<td><?= Loc::getMessage('USER_TYPE_INTEGER_SIZE') ?>:</td>
	<td>
		<input
			type="text"
			name="<?= $name ?>[SIZE]"
			size="20"
			maxlength="20"
			value="<?= $arResult['size'] ?>"
		>
	</td>
</tr>
<tr>
	<td><?= Loc::getMessage('USER_TYPE_INTEGER_MIN_VALUE') ?>:</td>
	<td>
		<input
			type="text"
			name="<?= $name ?>[MIN_VALUE]"
			size="20"
			maxlength="20"
			value="<?= $arResult['min'] ?>"
		>
	</td>
</tr>
<tr>
	<td><?= Loc::getMessage('USER_TYPE_INTEGER_MAX_VALUE') ?>:</td>
	<td>
		<input
			type="text"
			name="<?= $name ?>[MAX_VALUE]"
			size="20"
			maxlength="20"
			value="<?= $arResult['max'] ?>"
		>
	</td>
</tr>