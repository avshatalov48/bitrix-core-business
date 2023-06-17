<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 * @var array $arParams
 */

$isFirst = true;

$nodes = [$arResult['userField']['~id']];

?>

<select
	name="<?= $arResult['fieldName'] ?>"
	id="<?= $arResult['userField']['~id'] ?>"
	class="mobile-grid-data-select"
	<?= ($arResult['userField']['MULTIPLE'] === 'Y' ? ' multiple' : '') ?>
>
	<?php
	if ($arResult['userField']['MULTIPLE'] !== 'Y')
	{
		?>
		<option value=""><?= Loc::getMessage('USER_TYPE_ENUM_NO_VALUE') ?></option>
		<?php
	}
	foreach($arResult['userField']['USER_TYPE']['FIELDS'] as $optionValue => $optionName)
	{
		if($optionValue)
		{
			?>
			<option
				value="<?= HtmlFilter::encode($optionValue) ?>"
				<?= in_array($optionValue, $arResult['value']) ? ' selected="selected"' : '' ?>
			><?= $optionName ?></option>
			<?php
		}
	}
	?>
</select>
<a
	href="#"
	id="<?= $arResult['userField']['~id'] ?>_select"
>
	<?php
	if(!empty($arResult['value']))
	{
		foreach($arResult['value'] as $value)
		{
			if(!$isFirst)
			{
				print '<br>';
			}
			$isFirst = false;
			print HtmlFilter::encode($arResult['userField']['USER_TYPE']['FIELDS'][$value]);
		}
	}
	?>
</a>

<?php
if ($arResult['isEnabled'])
{
	?>

	<script>

		BX.message(<?= \CUtil::PhpToJSObject([
			'USER_TYPE_ENUM_NO_VALUE' => Loc::getMessage('USER_TYPE_ENUM_NO_VALUE')
		]) ?>);

		BX.ready(function ()
		{
			new BX.Mobile.Field.Enum(
				<?=CUtil::PhpToJSObject([
					'name' => 'BX.Mobile.Field.Enum',
					'nodes' => $nodes,
					'restrictedMode' => true,
					'formId' => $arParams['additionalParameters']['formId'],
					'gridId' => $arParams['additionalParameters']['gridId'],
				])?>
			);
		});
	</script>

	<?php
}
