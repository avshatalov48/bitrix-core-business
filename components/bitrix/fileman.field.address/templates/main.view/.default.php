<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arResult
 */
?>

<span class="fields address field-wrap">
	<?php
	foreach($arResult['value'] as $item)
	{
		?>
		<span class="fields address field-item">
			<?php
			if(!empty($item['text']))
			{
				if(
					!$arResult['additionalParameters']['printable']
					&&
					$item['coords']
					&&
					AddressType::getApiKey()
				)
				{
					$mouseOverParams = HtmlFilter::encode(
						\CUtil::PhpToJSObject(
							[
								'text' => $item['text'],
								'coords' => $item['coords']
							]
						)
					);
					?>
					<a
						href="javascript:void(0)"
						onmouseover="BX.Fileman.UserField.addressSearchResultDisplayMap.showHover(this, <?= $mouseOverParams ?>);"
						onmouseout="BX.Fileman.UserField.addressSearchResultDisplayMap.closeHover(this)"
					>
						<?= HtmlFilter::encode($item['text']) ?>
					</a>
					<?php
				}
				else
				{
					print HtmlFilter::encode($item['text']);
				}
			}
			?>
		</span>
	<?php } ?>
</span>