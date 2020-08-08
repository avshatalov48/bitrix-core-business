<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$name = $arResult['additionalParameters']['NAME'];
?>

<tr>
	<td>
		<?= Loc::getMessage('USER_TYPE_BOOL_LABELS') ?>:
	</td>
	<td>
		<table border="0">
			<tr>
				<td>
					<span class="adm-detail-label-text"><?= Loc::getMessage('MAIN_YES') ?>:</span>
				</td>
				<td>
					<input
						type="text"
						name="<?= $name ?>[LABEL][1]"
						value="<?= HtmlFilter::encode($arResult['labels'][1]) ?>"
					>
				</td>
			</tr>
			<tr>
				<td>
					<span><?= Loc::getMessage('MAIN_NO') ?>:</span>
				</td>
				<td>
					<input
						type="text"
						name="<?= $name ?>[LABEL][0]"
						value="<?= HtmlFilter::encode($arResult['labels'][0]) ?>"
					>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_BOOL_DEFAULT_VALUE') ?>:</span>
	</td>
	<td>
		<select class="adm-detail-select" name="<?= $name ?>[DEFAULT_VALUE]">
			<option
				value="1"
				<?= ($arResult['defaultValue'] ? 'selected = "selected"' : '') ?>
			>
				<?= Loc::getMessage('MAIN_YES') ?>
			</option>
			<option
				value="0"
				<?= (!$arResult['defaultValue'] ? 'selected = "selected"' : '') ?>
			>
				<?= Loc::getMessage('MAIN_NO') ?>
			</option>
		</select>
	</td>
</tr>
<tr>
	<td class="adm-detail-valign-top">
		<span><?= Loc::getMessage('USER_TYPE_BOOL_DISPLAY') ?>:</span>
	</td>
	<td>
		<?php
		foreach($arResult['displays'] as $display)
		{
			?>
			<label class="adm-detail-label">
				<input
					type="radio"
					name="<?= $name ?>[DISPLAY]"
					value="<?= $display ?>"
					<?= ($arResult['display'] === $display ? 'checked="checked"' : '') ?>
				>
				<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_BOOL_' . $display) ?></span>
			</label>
			<br/>
			<?php
		}
		?>
	</td>
</tr>
<tr>
	<td>
		<span class="adm-detail-label-text"><?= Loc::getMessage('USER_TYPE_BOOL_LABEL_CHECKBOX') ?>:</span>
	</td>
	<td>
		<input
			type="text"
			name="<?= $name ?>[LABEL_CHECKBOX]"
			value="<?= HtmlFilter::encode($arResult['labelCheckbox']) ?>"
		>
	</td>
</tr>