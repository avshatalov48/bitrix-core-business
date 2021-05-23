<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$name = $arResult['additionalParameters']['NAME'];
$size = $arResult['size'];
$enumItems = $arResult['additionalParameters']['enumItems'];
?>

<select
	name="<?= $name ?>"
	size="<?= $size ?>"
	<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>
>
	<?php foreach($enumItems as $id => $value)
	{
		?>
		<option
			value="<?= $id ?>"
			<?= (in_array($id, $arResult['additionalParameters']['VALUE']) ? ' selected' : '') ?>
			<?= ($arResult['userField']['EDIT_IN_LIST'] !== 'Y' ? ' disabled="disabled" ' : '') ?>
		>
			<?= $value ?>
		</option>
		<?php
	}
	?>
</select>