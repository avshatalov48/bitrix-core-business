<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;

?>

<span class="fields money field-wrap">
	<?php
	foreach($arResult['value'] as $value)
	{
		?>
		<span class="fields money field-item">
				<?php
				$APPLICATION->IncludeComponent(
					'bitrix:currency.money.input',
					'',
					[
						'CONTROL_ID' => $arResult['userField']['FIELD_NAME'] . '_' . Random::getString(5),
						'FIELD_NAME' => $arResult['fieldName'],
						'VALUE' => $value,
						'EXTENDED_CURRENCY_SELECTOR' => 'Y'
					],
					null,
					['HIDE_ICONS' => 'Y']
				);
				?>
			</span>
		<?php
	}

	if($arResult['userField']['MULTIPLE'] === 'Y')
	{
		/**
		 * @var MoneyUfComponent $component
		 */
		$component = $this->getComponent();
		print $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
	}
	?>
</span>