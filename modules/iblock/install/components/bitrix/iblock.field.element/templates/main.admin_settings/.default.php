<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Iblock\UserField\Types\ElementType;
use Bitrix\Main\Localization\Loc;

$name = $arResult['additionalParameters']['NAME'];
$iblockId = (int)$arResult['iblockId'];
$value = (int)$arResult['value'];
$activeFilter = $arResult['activeFilter'];


/**
 * @var ElementUfComponent $component
 */
if($component->isIblockIncluded())
{
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_IBEL_DISPLAY') ?>:</td>
		<td>
			<?=
			GetIBlockDropDownListEx(
				$iblockId,
				$name . '[IBLOCK_TYPE_ID]',
				$name . '[IBLOCK_ID]',
				[
					'CHECK_PERMISSIONS' => 'Y',
					'MIN_PERMISSION' => 'E',
				],
				'',
				'',
				'class="adm-detail-iblock-types"',
				'class="adm-detail-iblock-list" onchange="showUsertypeElementNote(this);"'
			) ?>
		</td>
	</tr>
	<tr
		id="tr_usertype_element_note"
		style="display: <?= ($iblockId ? 'none' : 'table-row') ?>"
	>
		<td></td>
		<td> <?= Loc::getMessage('USER_TYPE_IBEL_DISPLAY_NOTE') ?>  </td>
	</tr>
	<script>
		function showUsertypeElementNote(selector)
		{
			BX.style(BX('tr_usertype_element_note'), 'display', (selector.value !== '-1' && selector.value !== '0' ? 'none' : 'table-row'));
		}
	</script>
	<?php
}
else
{
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_IBEL_DISPLAY') ?>:</td>
		<td>
			<input
				type="text"
				size="6"
				name="<?= $name ?>[IBLOCK_ID]"
				value=" <?= $iblockId ?>"
			>
		</td>
	</tr>
	<?php
}

if($component->isIblockIncluded() && $iblockId)
{
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_IBEL_DEFAULT_VALUE') ?>:</td>
		<td>
			<select name="<?= $name ?>[DEFAULT_VALUE]" size="5">
				<option value=""><?= Loc::getMessage('USER_TYPE_IBEL_VALUE_ANY') ?></option>
				<?php
				foreach($arResult['options'] as $optionId => $optionValue)
				{
					?>
					<option
						value="<?= $optionId ?>"
						<?= ($optionId === $value ? ' selected' : '') ?>
					>
						<?= $optionValue ?>
					</option>
					<?php
				}
				?>
			</select>
		</td>
	</tr>
	<?php
}
else
{
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_IBEL_DEFAULT_VALUE') ?>:</td>
		<td>
			<input
				type="text"
				size="8"
				name="<?= $name ?>[DEFAULT_VALUE]"
				value="<?= $value ?>"
			>
		</td>
	</tr>
	<?php
}
?>

<tr>
	<td class="adm-detail-valign-top"><?= Loc::getMessage('USER_TYPE_ENUM_DISPLAY') ?>:</td>
	<td>
		<label>
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= ElementType::DISPLAY_DIALOG ?>"
				<?= (ElementType::DISPLAY_DIALOG === $arResult['display'] ? 'checked="checked"' : '') ?>
			>
			<?= Loc::getMessage('USER_TYPE_IBEL_DIALOG') ?>
		</label>
		<br>
		<label>
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= ElementType::DISPLAY_UI ?>"
				<?= (ElementType::DISPLAY_UI === $arResult['display'] ? 'checked="checked"' : '') ?>
			>
			<?= Loc::getMessage('USER_TYPE_IBEL_UI') ?>
		</label>
		<br>
		<label>
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= ElementType::DISPLAY_LIST ?>"
				<?= (ElementType::DISPLAY_LIST === $arResult['display'] ? 'checked="checked"' : '') ?>
			>
			<?= Loc::getMessage('USER_TYPE_IBEL_LIST') ?>
		</label>
		<br>
		<label>
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= ElementType::DISPLAY_CHECKBOX ?>"
				<?= (ElementType::DISPLAY_CHECKBOX === $arResult['display'] ? 'checked="checked"' : '') ?>
			>
			<?= Loc::getMessage('USER_TYPE_IBEL_CHECKBOX') ?>
		</label>
		<br>
	</td>
</tr>

<tr>
	<td><?= Loc::getMessage('USER_TYPE_IBEL_LIST_HEIGHT') ?>:</td>
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
	<td><?= Loc::getMessage('USER_TYPE_IBEL_ACTIVE_FILTER') ?>:</td>
	<td>
		<input
			type="checkbox"
			name="<?= $name ?>[ACTIVE_FILTER]"
			value="Y"
			<?= ($arResult['activeFilter'] === 'Y' ? 'checked="checked"' : '') ?>
		>
	</td>
</tr>
