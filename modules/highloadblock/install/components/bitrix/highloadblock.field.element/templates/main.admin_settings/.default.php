<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var HighloadblockElementUfComponent $component
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

$name = $arResult['settingsName'];
$safeName = HtmlFilter::encode($name);
$multiple = $arResult['multiple'];
$settings = $arResult['settings'];

$highloadblockIncluded = $component->isHighloadblockIncluded();

if ($highloadblockIncluded):
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_HLEL_ENTITY') ?></td>
		<td>
			<?= \CUserTypeHlblock::getDropDownHtml($settings['hlblockId'], $settings['hlfieldId'], $name) ?>
		</td>
	</tr>
	<?php
endif;

if (
	$highloadblockIncluded
	&& $settings['hlblockId'] > 0
	&& $settings['hlfieldId'] > 0
):
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_HLEL_DEFAULT_VALUE') ?></td>
		<td>
			<select name="<?= $safeName ?>[DEFAULT_VALUE]<?= ($multiple ? '[]' : '') ?>"<?= ($multiple ? ' multiple' : '') ?> size="5">
				<option value=""><?= Loc::getMessage('USER_TYPE_HLEL_NO_VALUE') ?></option>
				<?php
				$list = \CUserTypeHlblock::getHlRows($component->getUserField());
				foreach ($list as $row):
					if ($multiple):
						$selected =
							in_array($row['ID'], $settings['defaultValue'])
								?' selected'
								: ''
						;
					else:
						$selected =
							$row['ID'] === $settings['defaultValue']
								?' selected'
								: ''
						;
					endif;
					?>
					<option value="<?= $row['ID'] ?>"<?= $selected ?>><?= HtmlFilter::encode($row['VALUE']) ?></option>
					<?php
				endforeach;
				unset($row, $rows);
				?>
			</select>
		</td>
	</tr>
	<?php
else:
	?>
	<tr>
		<td><?= Loc::getMessage('USER_TYPE_HLEL_DEFAULT_VALUE') ?></td>
		<td>
			<?php
			if ($multiple):
				foreach ($settings['defaultValue'] as $value):
					?>
					<input
						type="text"
						name="<?= $safeName ?>[DEFAULT_VALUE][]"
						value="<?= HtmlFilter::encode((string)$value) ?>"
						size="8"
					><br>
					<?php
				endforeach;
				?>
				<input
					type="text"
					name="<?= $safeName ?>[DEFAULT_VALUE][]"
					value=""
					size="8"
				>
				<?php
			else:
				?>
				<input
					type="text"
					name="<?= $safeName ?>[DEFAULT_VALUE]"
					value="<?= HtmlFilter::encode((string)$settings['defaultValue']) ?>"
					size="8"
				>
				<?php
			endif;
			?>
		</td>
	</tr>
	<?php
endif;

?>
<tr>
	<td class="adm-detail-valign-top"><?= Loc::getMessage('USER_TYPE_HLEL_DISPLAY') ?></td>
	<td>
		<?php
		foreach (\CUserTypeHlblock::getDisplayTypeList() as $type => $title):
			?><label><input
				type="radio"
				name="<?= $safeName ?>[DISPLAY]"
				value="<?= HtmlFilter::encode($type) ?>"
				<?= ($type === $settings['display'] ? ' checked' : '') ?>
			><?= $title ?></label><br>
			<?php
		endforeach;
		?>
	</td>
</tr>
<tr>
	<td><?= Loc::getMessage('USER_TYPE_HLEL_LIST_HEIGHT') ?></td>
	<td>
		<input type="text" name="<?= $safeName ?>[LIST_HEIGHT]" size="10" value="<?= (int)$settings['listHeight'] ?>">
	</td>
</tr>
