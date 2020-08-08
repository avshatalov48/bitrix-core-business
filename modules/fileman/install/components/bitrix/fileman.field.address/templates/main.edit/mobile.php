<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

foreach($arResult['value'] as $value)
{
	if($value)
	{
		list($text, $coord) = AddressType::parseValue($value);
		?>
		<span class="mobile-grid-data-span">
			<?= HtmlFilter::encode($text) ?>
		</span>
		<?php
	}
}