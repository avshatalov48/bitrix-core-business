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
$html = $field['data']['html'] ?? '';
$hint = $field['hint'] ?? null;

?>
<div class="iblock-property-details-input iblock-property-details-custom">
	<div class="ui-ctl-label-text">
		<?= HtmlFilter::encode($label) ?>
		<?php if (!empty($hint)): ?>
			<span data-hint="<?= HtmlFilter::encode($hint) ?>"></span>
		<?php endif; ?>
	</div>
	<?= $html ?>
</div>
