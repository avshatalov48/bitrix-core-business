<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Location\Entity\Address\Converter\StringConverter;
use Bitrix\Location\Service\FormatService;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

if (\Bitrix\Main\Loader::includeModule('location'))
{
	foreach($arResult['value'] as $value)
	{
		if ($value)
		{
			$address = \Bitrix\Location\Entity\Address::fromArray($value);
			$text = $address->toString(FormatService::getInstance()->findDefault(LANGUAGE_ID), StringConverter::STRATEGY_TYPE_TEMPLATE_COMMA);
			?>
			<span class="mobile-grid-data-span">
				<?= HtmlFilter::encode($text) ?>
			</span>
			<?php
		}
	}
}
else
{
	?>
	<span class="mobile-grid-data-span">
		The "location" module is not installed.
	</span>
	<?php
}
