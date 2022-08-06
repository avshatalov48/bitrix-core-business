<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\BooleanType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var BooleanUfComponent $component
 * @var array $arResult
 */
$component = $this->getComponent();
$value = $arResult['value'];
?>

<span class='field-wrap fields boolean'>
	<span class='field-item fields boolean'>
		<?php
		if($arResult['userField']['SETTINGS']['DISPLAY'] === BooleanType::DISPLAY_DROPDOWN)
		{
			?>
			<select
				class="fields boolean"
				name="<?= $arResult['fieldName'] ?>"
			>
				<?php
				foreach($arResult['valueList'] as $key => $title)
				{
					?>
					<option
						value="<?= (int)$key ?>"
						<?= ($value === $key ? ' selected="selected"' : '') ?>
					><?= htmlspecialcharsbx($title) ?></option>
					<?php
				}
				?>
			</select>
			<?php
		}
		else if($arResult['userField']['SETTINGS']['DISPLAY'] === BooleanType::DISPLAY_CHECKBOX)
		{
			$label = Loc::getMessage('MAIN_YES');
			if (!empty($arResult['userField']['EDIT_FORM_LABEL']))
			{
				$label = $arResult['userField']['EDIT_FORM_LABEL'];
			}
			elseif(isset($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']))
			{
				if(is_array($arResult['userField']['SETTINGS']['LABEL_CHECKBOX']))
				{
					$arResult['userField']['SETTINGS']['LABEL_CHECKBOX'] =
						$arResult['userField']['SETTINGS']['LABEL_CHECKBOX'][LANGUAGE_ID];
				}

				if($arResult['userField']['SETTINGS']['LABEL_CHECKBOX'] !== '')
				{
					$label = $arResult['userField']['SETTINGS']['LABEL_CHECKBOX'];
				}
			}
			?>
			<input
				class="fields boolean"
				type="hidden"
				value="0"
				name="<?= $arResult['fieldName'] ?>"
			>
			<label>
				<input
					type="checkbox"
					value="1"
					name="<?= $arResult['fieldName'] ?>"
					<?= $value ? ' checked' : '' ?>
				>
				<?= HtmlFilter::encode($label) ?>
			</label>
			<?php
		}
		else if($arResult['userField']['SETTINGS']['DISPLAY'] === BooleanType::DISPLAY_RADIO)
		{
			$first = true;
			foreach($arResult['valueList'] as $key => $title)
			{
				if($first)
				{
					$first = false;
				}
				elseif($arResult['userField']['SETTINGS']['MULTIPLE'] === 'N')
				{
					print $component->getHtmlBuilder()->getMultipleValuesSeparator();
				}
				?>
				<label>
					<input
						type="radio"
						class="fields boolean"
						value="<?= (int)$key ?>"
						name="<?= $arResult['fieldName'] ?>"
						<?= ($value === $key ? ' checked="checked"' : '') ?>
					/>
					<?= htmlspecialcharsbx($title) ?>
				 </label>
				<?php
			}
		}
		?>
	</span>
</span>
