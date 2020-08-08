<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arResult
 */

foreach($arResult['value'] as $value)
{
	?>
	<input
		type="text"
		name="<?= $arResult['additionalParameters']['NAME'] ?>"
		size="<?= $arResult['userField']['SETTINGS']['SIZE'] ?>"
		value="<?= $value ?>"
	>
	<?php
}
?>