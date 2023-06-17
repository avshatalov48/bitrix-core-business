<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Localization\Loc;

$name = $arResult['additionalParameters']['NAME'] ?? '';
$value = $arResult['default_value'] ?? '';
$type = $arResult['default_value_type'] ?? '';
?>

<tr>
	<td class="adm-detail-valign-top">
		<?= Loc::getMessage('USER_TYPE_ENUM_DISPLAY') ?>:
	</td>
	<td>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= EnumType::DISPLAY_LIST ?>"
				<?= ($arResult['display'] === EnumType::DISPLAY_LIST ? 'checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_LIST') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= EnumType::DISPLAY_CHECKBOX ?>"
				<?= ($arResult['display'] === EnumType::DISPLAY_CHECKBOX ? 'checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_CHECKBOX') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= EnumType::DISPLAY_UI ?>"
				<?= ($arResult['display'] === EnumType::DISPLAY_UI ? 'checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_UI') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= EnumType::DISPLAY_DIALOG ?>"
				<?= ($arResult['display'] === EnumType::DISPLAY_DIALOG ? 'checked="checked"' : '') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_DIALOG') ?></span>
		</label>
		<br>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_LIST_HEIGHT') ?>:</span>
	</td>
	<td>
		<input
			type="text"
			name="<?= $name ?>[LIST_HEIGHT]"
			size="10"
			value="<?= $arResult['listHeight'] ?>"
		>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_CAPTION_NO_VALUE') ?>:</span>
	</td>
	<td>
		<input
			type="text"
			name="<?= $name ?>[CAPTION_NO_VALUE]"
			size="10"
			value="<?= $arResult['captionNoValue'] ?>"
		>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_SHOW_NO_VALUE') ?>:</span>
	</td>
	<td>
		<input
			type="hidden"
			name="<?= $name ?>[SHOW_NO_VALUE]"
			value="N"
		>
		<label class="adm-detail-label">
			<input
				type="checkbox"
				name="<?= $name ?>[SHOW_NO_VALUE]"
				value="Y"
				<?= ($arResult['showNoValue']	===	'N'	?	''	:	'	checked="checked"') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('MAIN_YES') ?></span>
		</label>
	</td>
</tr>
