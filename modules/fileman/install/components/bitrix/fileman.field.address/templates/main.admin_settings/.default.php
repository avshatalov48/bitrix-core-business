<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 * @var array $additionalParameters
 */
$additionalParameters = $arResult['additionalParameters'];
?>

<tr>
	<td><?= Loc::getMessage('USER_TYPE_ADDRESS_SHOW_MAP') ?>:</td>
	<td>
		<input
			type="hidden"
			name="<?= $additionalParameters['NAME'] ?>[SHOW_MAP]"
			value="N"
		>
		<label>
			<input
				type="checkbox"
				name="<?= $additionalParameters['NAME'] ?>[SHOW_MAP]"
				value="Y"
				<?= ($arResult['value'] === 'Y' ? ' checked="checked"' : '') ?>
			>
			<?= Loc::getMessage('MAIN_YES') ?>
		</label>
	</td>
</tr>