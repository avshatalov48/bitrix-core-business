<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\EnumType;

$name = $arResult['additionalParameters']['NAME'];
$size = $arResult['size'];
$enumItems = $arResult['additionalParameters']['enumItems'];

?>
<select
	name="<?= $name ?>"
	size="<?= $size ?>"
	<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>
>
<?php
	$isWasSelect = false;
	foreach($enumItems as $id => $value)
	{
		if (in_array($id, $arResult['additionalParameters']['VALUE']))
		{
			$isWasSelect = true;
			break;
		}
	}

	if($arResult['userField']['MANDATORY'] !== 'Y')
	{
	?>
		<option value="" <?= (!$isWasSelect ? ' selected' : '') ?>>
			<?= HtmlFilter::encode(EnumType::getEmptyCaption($arResult['userField'])) ?>
		</option>
	<?php
	}

	foreach($enumItems as $id => $value)
	{
		?>
		<option
			value="<?= $id ?>"
			<?= (in_array($id, $arResult['additionalParameters']['VALUE']) ? ' selected' : '') ?>
			<?= ($arResult['userField']['EDIT_IN_LIST'] !== 'Y' ? ' disabled="disabled" ' : '') ?>
		>
			<?= HtmlFilter::encode($value) ?>
		</option>
		<?php
	}
?>
</select>