<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\ElementType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$name = $arResult['additionalParameters']['NAME'];
$iblockId = $arResult['iblockId'];
$value = $arResult['value'];
$activeFilter = $arResult['activeFilter'];


/*
 * @var ElementUfComponent $component
 */
if($component->isIblockIncluded())
{
	?>
	<tr>
		<td>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_DISPLAY') ?>:</span>
		</td>
		<td>
			<?=
			GetIBlockDropDownList(
				$iblockId,
				$name . '[IBLOCK_TYPE_ID]',
				$name . '[IBLOCK_ID]',
				false,
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
		<td>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_DISPLAY_NOTE') ?></span>
		</td>
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
		<td>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_DISPLAY') ?>:</span>
		</td>
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
		<td>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_DEFAULT_VALUE') ?>:</span>
		</td>
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
		<td>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_DEFAULT_VALUE') ?>:</span>
		</td>
		<td>
			<input
				type="text"
				size="8"
				name="<?= $name ?>[DEFAULT_VALUE]"
				value="<?= HtmlFilter::encode($value) ?>"
			>
		</td>
	</tr>
	<?php
}
?>

<tr>
	<td class="adm-detail-valign-top">
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_ENUM_DISPLAY') ?>:</span>
	</td>
	<td>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= ElementType::DISPLAY_LIST ?>"
				<?= (
				ElementType::DISPLAY_LIST === $arResult['display']
					? 'checked="checked"' : ''
				)
				?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_LIST') ?></span>
		</label>
		<br>
		<label class="adm-detail-label">
			<input
				type="radio"
				name="<?= $name ?>[DISPLAY]"
				value="<?= ElementType::DISPLAY_CHECKBOX ?>"
				<?= (
				ElementType::DISPLAY_CHECKBOX === $arResult['display']
					? 'checked="checked"' : ''
				)
				?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_CHECKBOX') ?></span>
		</label>
		<br>
	</td>
</tr>

<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_LIST_HEIGHT') ?>:</span>
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
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_IBEL_ACTIVE_FILTER') ?>:</span>
	</td>
	<td>
		<input
			type="checkbox"
			name="<?= $name ?>[ACTIVE_FILTER]"
			value="Y"
			<?= ($arResult['activeFilter'] === 'Y' ? 'checked="checked"' : '') ?>
		>
	</td>
</tr>