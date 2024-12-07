<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var HighloadblockElementUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
?>
<span class="fields enumeration field-wrap">
	<?php
	$multipleClass = (
		$arResult['userField']['MULTIPLE'] === 'Y' ? '-multiselect' : '-select'
	);
	if ($arResult['userField']['SETTINGS']['DISPLAY'] === \CUserTypeHlblock::DISPLAY_LIST)
	{
		?>
		<span class="enumeration<?= $multipleClass ?> field-item">
			<select
				<?= $component->getHtmlBuilder()->buildTagAttributes($arResult['attrList']) ?>
			>
			<?php
			if (
				isset($arResult['userField']['USER_TYPE']['FIELDS'])
				&& is_array($arResult['userField']['USER_TYPE']['FIELDS'])
			)
			{
				$isWasSelect = false;
				foreach ($arResult['userField']['USER_TYPE']['FIELDS'] as $key => $val)
				{
					$isSelected = (
						in_array($key, $arResult['value'])
						&&
						(
							!$isWasSelect
							|| $arResult['userField']['MULTIPLE'] === 'Y'
						)
					);
					$isWasSelect = $isWasSelect || $isSelected;
					?>
					<option
						value="<?= $key ?>"
						<?= ($isSelected ? ' selected="selected"' : '') ?>
					><?= HtmlFilter::encode($val) ?></option>
					<?php
				}
			}
			?>
			</select>
		</span>
		<?php
	}
	elseif ($arResult['userField']['SETTINGS']['DISPLAY'] === \CUserTypeHlblock::DISPLAY_CHECKBOX)
	{
		$isFirst = true;
		if ($arResult['userField']['MULTIPLE'] === 'Y')
		{
			?>
			<input
				type="hidden"
				name="<?= $arResult['fieldName'] ?>"
				value=""
			>
			<?php
		}

		$isWasSelect = false;
		foreach ((array)$arResult['userField']['USER_TYPE']['FIELDS'] as $key => $val)
		{
			?>
			<span
				class="fields enumeration enumeration-checkbox field-item"
			>
			<?php
			if ($isFirst)
			{
				$isFirst = false;
			}
			else
			{
				echo $component->getHtmlBuilder()->getMultipleValuesSeparator();
			}

			$isSelected = (
				in_array($key, $arResult['value'])
				&&
				(
					!$isWasSelect
					|| $arResult['userField']['MULTIPLE'] === 'Y'
				));

			$isWasSelect = $isWasSelect || $isSelected;
			?>
				<label>
					<input
						value="<?= $key ?>"
						type="<?= $arResult['userField']['MULTIPLE'] === 'Y' ? 'checkbox' : 'radio' ?>"
						name="<?= $arResult['fieldName'] ?>"
						<?= ($isSelected ? 'checked="checked"' : '') ?>
						tabindex="0"
					>
					<?= HtmlFilter::encode($val) ?>
				</label>
				<br>
			</span>
			<?php
		}
	}
	?>
</span>
