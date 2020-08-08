<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var UrlUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

$isFirst = true;
?>

<span class="fields url field-wrap">
    <?php
		foreach($arResult['value'] as $item)
		{
			if($isFirst)
			{
				$isFirst = false;
			}
			else
			{
				print $component->getHtmlBuilder()->getMultipleValuesSeparator();
			}
			?>

			<span class="fields url field-item">
				<a
					<?= $component->getHtmlBuilder()->buildTagAttributes($item['attrList']) ?>
				>
					<?= $item['value'] ?>
				</a>
			</span>
			<?php
		}
		?>
</span>