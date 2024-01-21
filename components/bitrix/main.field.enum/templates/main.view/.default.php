<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\EnumType;

/**
 * @var EnumUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();

$isEmpty = true;
?>

<span class="fields field-wrap">
<?php
foreach($arResult['value'] as $res)
{
	$res = (int)$res;
	if (!array_key_exists($res, $arResult['userField']['USER_TYPE']['FIELDS']))
	{
		continue;
	}

	$textRes = $arResult['userField']['USER_TYPE']['FIELDS'][$res];
	$isEmpty = false;
	?>
	<span class="field-item" data-id="<?= $res ?>">
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