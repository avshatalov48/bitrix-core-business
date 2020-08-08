<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 */

?>

<select
	name="<?= $arResult['fieldName'] ?>"
	<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>
	size="<?= $arResult['size'] ?>"
>
	<option
		value="" <?= (!$arResult['additionalParameters']['VALUE'] ? ' selected' : '') ?>
	>
		<?= Loc::getMessage('MAIN_ALL') ?>
	</option>
	<?php
	foreach($arResult['additionalParameters']['items'] as $id => $name)
	{
		?>
		<option
			value="<?= $id ?>"
			<?= (in_array($id, $arResult['additionalParameters']['VALUE']) ? ' selected' : '') ?>
		>
			<?= $name ?>
		</option>
		<?php
	}
	?>
</select>