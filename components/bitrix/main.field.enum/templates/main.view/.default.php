<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var EnumUfComponent $component
 * @var array $arResult
 */

use \Bitrix\Main\UserField\Types\EnumType;

$component = $this->getComponent();

$isEmpty = true;
?>

<span class="field-wrap">
<?php
foreach($arResult['value'] as $res)
{
	$res = (int)$res;
	if(array_key_exists($res, $arResult['userField']['USER_TYPE']['FIELDS']))
	{
		$textRes = $arResult['userField']['USER_TYPE']['FIELDS'][$res];
		$isEmpty = false;
	}
	else
	{
		continue;
	}
	?>
	<span class="field-item">
		<?php
		if(!empty($arResult['userField']['PROPERTY_VALUE_LINK']))
		{
			$href = HtmlFilter::encode(
				str_replace('#VALUE#', $res, $arResult['userField']['PROPERTY_VALUE_LINK'])
			);
			?>
			<a
				href="<?= $href ?>"
			>
				<?= $textRes ?>
			</a>
			<?php
		}
		else
		{
			print $textRes;
		}
		?>
	</span>
	<?php
	if (!empty($arParams['additionalParameters']['showInputs']))
	{
		print '<input type="hidden" name="'.$arResult['fieldName'].'" value="'.$res.'">';
	}
}

if($isEmpty)
{
	$emptyCaption = EnumType::getEmptyCaption($arResult['userField']);
	print $component->getHtmlBuilder()->wrapSingleField($emptyCaption);
}
?>
</span>