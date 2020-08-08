<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\UserField\Types\ElementType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$name = $arResult['additionalParameters']['NAME'];
$iblockId = $arResult['iblockId'];
$value = $arResult['value'];
$activeFilter = $arResult['activeFilter'];

/**
 * @var ElementUfComponent $component
 */

if($component->isIblockIncluded())
{
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_IBSEC_DISPLAY') ?>:</td>
		<td>
			<?= getIBlockDropDownList(
				$iblockId,
				$name . '[IBLOCK_TYPE_ID]',
				$name . '[IBLOCK_ID]',
				false,
				'class="adm-detail-iblock-types"',
				'class="adm-detail-iblock-list"'
			) ?>
		</td>
	</tr>
	<?php
}
else
{
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_IBSEC_DISPLAY') ?>:</td>
		<td>
			<input
				type="text"
				size="6"
				name="<?= $name ?>[IBLOCK_ID]"
				value="<?= HtmlFilter::encode($value) ?>"
			>
		</td>
	</tr>
	<?php
}

if($iblockId && $component->isIblockIncluded())
{
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_IBEL_DEFAULT_VALUE') ?>:</td>
		<td>
			<select
				name="<?= $name ?>[DEFAULT_VALUE]"
				size="5"
			>
				<option value="">
					<?= Loc::getMessage('USER_TYPE_IBEL_VALUE_ANY') ?>
				</option>
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
				value="<?= HtmlFilter::encode($value) ?>"
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