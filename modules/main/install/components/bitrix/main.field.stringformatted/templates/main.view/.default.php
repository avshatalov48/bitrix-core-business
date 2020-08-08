<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */
?>

<span class="fields string_formatted field-wrap">
	<?php
	foreach($arResult['value'] as $value)
	{
		?>
		<span class="fields string_formatted field-item">
			<?= $value ?>
		</span>
	<?php } ?>
</span>