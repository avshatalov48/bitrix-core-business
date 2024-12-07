<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Text\HtmlFilter;

/**
 * @var array $arResult
 */
?>

<span class="fields field-wrap">
	<?php
	$isEmpty = true;
	if (!empty($arResult['userField']['USER_TYPE']['FIELDS']))
	{
		foreach($arResult['value'] as $res)
		{
			if($res === false || !array_key_exists($res, $arResult['userField']['USER_TYPE']['FIELDS']))
			{
				continue;
			}

			$textRes = $arResult['userField']['USER_TYPE']['FIELDS'][$res];
			$isEmpty = false;
			?>
			<span class="field-item" data-id="<?= (int)$res ?>">
				<?php
				if(!empty($arResult['userField']['PROPERTY_VALUE_LINK']))
				{
					$href = HtmlFilter::encode(
						str_replace('#VALUE#', $res, $arResult['userField']['PROPERTY_VALUE_LINK'])
					);
					$textRes = HtmlFilter::encode($textRes);
					?>
					<a href="<?= $href ?>"><?= $textRes ?></a>
					<?php
				}
				else
				{
					print HtmlFilter::encode($textRes);
				}
				?>
			</span>
			<?php
		}
	}

	if($isEmpty)
	{
		$emptyCaption = \CUserTypeHlblock::getEmptyCaption($arResult['userField']);

		/** @var HighloadblockElementUfComponent $component */
		$component = $this->getComponent();
		print $component->getHtmlBuilder()->wrapSingleField($emptyCaption);
	}
	?>
</span>
