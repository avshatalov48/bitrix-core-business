<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var MoneyUfComponent $component
 * @var array $arResult
 */

?>
<span class="fields money field-wrap">
	<?php
	$isFirst = true;
	foreach ($arResult['value'] as $item)
	{
		if (!$isFirst)
		{
			print '<br>';
		}
		$isFirst = false;
		?>
		<span class="fields money field-item">
			<?= $item['value'] ?>
		</span>
		<?php
	}
	?>
</span>