<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;
?>

<span class="fields date field-wrap">
    <?php
	foreach($arResult['value'] as $item)
	{
		?>
		<span class="fields date field-item">
			<?= HtmlFilter::encode($item['value']) ?>
		</span>
		<?php
	}
	?>
</span>