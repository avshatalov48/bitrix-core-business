<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @global CMain $APPLICATION
 * @var MoneyUfComponent $component
 * @var array $arResult
 */

use Bitrix\Main\Security\Random;

$component = $this->getComponent();
?>
<span class="fields money field-wrap">
	<?php
	$userField = $arResult['userField'];
	$first = true;

	foreach ($arResult['value'] as $value)
	{
		if (!$first)
		{
			echo $component->getHtmlBuilder()->getMultipleValuesSeparator();
		}
		$first = false;
		?>
		<span class="fields money field-item">
			<?php
			$APPLICATION->IncludeComponent(
				'bitrix:currency.money.input',
				'',
				[
					'CONTROL_ID' => $userField['FIELD_NAME'] . '_' . Random::getString(5),
					'FIELD_NAME' => $arResult['fieldName'],
					'VALUE' => $value['value'],
					'EXTENDED_CURRENCY_SELECTOR' => 'Y',
				],
				null,
				['HIDE_ICONS' => 'Y']
			);
			?>
		</span>
		<?php
	}

	if (
		$userField['MULTIPLE'] === 'Y'
		&& ($arResult['additionalParameters']['SHOW_BUTTON'] ?? 'Y') !== 'N'
	)
	{
		echo $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
	}
	?>
</span>