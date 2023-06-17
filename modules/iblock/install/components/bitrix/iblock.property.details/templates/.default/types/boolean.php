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

$value = $values[$name] ?? '';

$attrs = '';
if (isset($field['disabled']) && $field['disabled'])
{
	$attrs .= ' disabled';
}

if ($value === 'Y')
{
	$attrs .= ' checked';
}

?>
<div class="iblock-property-details-input iblock-property-details-boolean">
	<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
		<input
			type="hidden"
			name="<?= HtmlFilter::encode($name) ?>"
			value="N"
		/>
		<input
			type="checkbox"
			class="ui-ctl-element"
			name="<?= HtmlFilter::encode($name) ?>"
			value="Y"
			<?= $attrs ?>
		/>
		<div class="ui-ctl-label-text">
		<?= HtmlFilter::encode($label) ?>
		<?php if (!empty($hint)): ?>
			<span data-hint="<?= HtmlFilter::encode($hint) ?>"></span>
		<?php endif; ?>
	</div>
	</label>
</div>
