<?php

use Bitrix\Main\Text\HtmlFilter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $field
 * @var array $values
 */

$name = $field['name'] ?? '';
$label = $field['title'] ?? $name;
$hint = $field['hint'] ?? null;
$options = (array)($field['data']['items'] ?? []);

$attrs = '';
if (isset($field['disabled']) && $field['disabled'])
{
	$attrs .= ' disabled';
}

$value = $values[$name] ?? '';

?>
<div class="iblock-property-details-input">
	<div class="ui-ctl-label-text">
		<?= HtmlFilter::encode($label) ?>
		<?php if (!empty($hint)): ?>
			<span data-hint="<?= HtmlFilter::encode($hint) ?>"></span>
		<?php endif; ?>
	</div>
	<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
		<div class="ui-ctl-after ui-ctl-icon-angle"></div>
		<select
			class="ui-ctl-element"
			name="<?= HtmlFilter::encode($name) ?>"
			<?= $attrs ?>
		>
			<?php foreach($options as $option): ?>
				<?php
				$selected = $option['VALUE'] === $value ? 'selected' : '';
				?>
				<option
					value="<?= HtmlFilter::encode($option['VALUE']) ?>"
					<?= $selected ?>
				>
					<?= HtmlFilter::encode($option['NAME']) ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
