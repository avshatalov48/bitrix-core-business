<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\UserField\Types\MoneyType;
use Bitrix\Main\Security\Random;
?>

<tr>
	<td><?= Loc::getMessage('USER_TYPE_MONEY_DEFAULT_VALUE') ?>:</td>
	<td>
		<?php
		$APPLICATION->IncludeComponent(
			'bitrix:currency.money.input',
			'',
			[
				'CONTROL_ID' => $arResult['userField']['FIELD_NAME'] . '_' . Random::getString(5),
				'FIELD_NAME' => $arResult['additionalParameters']['NAME']. '[DEFAULT_VALUE]',
				'VALUE' => $arResult['value'],
				'EXTENDED_CURRENCY_SELECTOR' => 'Y'
			],
			null,
			['HIDE_ICONS' => 'Y']
		);
		?>
	</td>
</tr>