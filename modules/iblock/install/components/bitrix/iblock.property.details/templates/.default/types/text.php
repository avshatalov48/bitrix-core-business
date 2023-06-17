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

$attrs = '';
if (isset($field['disabled']) && $field['disabled'])
{
	$attrs .= ' disabled';
}

$value = (string)($values[$name] ?? '');

?>
<div class="iblock-property-details-input">
	<div class="ui-ctl-label-text">
		<?= HtmlFilter::encode($label) ?>
		<?php if (!empty($hint)): ?>
			<span data-hint="<?= HtmlFilter::encode($hint) ?>"></span>
		<?php endif; ?>
	</div>
	<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
		<input
			type="text"
			class="ui-ctl-element"
			name="<?= HtmlFilter::encode($name) ?>"
			value="<?= HtmlFilter::encode($value) ?>"
			<?= $attrs ?>
		>
	</div>
</div>
