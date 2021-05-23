<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */

?>
<label
	for="<?= $arResult['userField']['~id'] ?>"
>
	<input
		type="checkbox"
		id="<?= $arResult['userField']['~id'] ?>"
		name="<?= $arResult['fieldName'] ?>"
		value="Y"
		<?= ($arResult['userField']['VALUE'] ? ' checked="checked"' : '') ?>
	>
	<span><?= $arResult['userField']['EDIT_FORM_LABEL'] ?></span>
</label>