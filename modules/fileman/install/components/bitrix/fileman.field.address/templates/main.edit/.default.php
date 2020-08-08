<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
$userField = $arResult['userField'];
$additionalParameters = $arResult['additionalParameters'];
?>

<?php
if($arResult['canUseMap'])
{
	$controlId = HtmlFilter::encode($userField['FIELD_NAME']);
	?>
	<div id="<?= $controlId ?>">
	</div>
	<span
		style="display: none;"
		id="<?= $controlId ?>_result"
	>
	</span>

	<script>
		BX.ready(function ()
		{
			new BX.Default.Field.Address(
				<?=CUtil::PhpToJSObject([
					'controlId' => $controlId,
					'value' => $arResult['value'],
					'isMultiple' => ($userField['MULTIPLE'] === 'Y' ? 'true' : 'false'),
					'nodeJs' => \CUtil::JSEscape($userField['FIELD_NAME']) . '_result',
					'fieldNameJs' => \CUtil::JSEscape($arResult['fieldName'])
				])?>
			);
		});
	</script>
	<?php
}
else
{
	?>
	<span class="fields address field-wrap">
		<?php
		foreach($arResult['value'] as $key => $value)
		{
			?>
			<span class="fields address field-item">
				<?php
				list($text, $coords) = AddressType::parseValue($value);

				$attrList = [
					'type' => 'text',
					'class' => $this->getComponent()->getHtmlBuilder()->getCssClassName(),
					'name' => $arResult['fieldName'],
					'value' => HtmlFilter::encode($text),
				];

				if($arResult['useRestriction'] && !$arResult['checkRestriction'])
				{
					$attrList['onfocus'] = 'BX.Fileman.UserField.addressSearchRestriction.show(this)';
				}
				elseif($arResult['apiKey'] === null)
				{
					$attrList['onfocus'] = 'BX.Fileman.UserField.addressKeyRestriction.show(this)';
				}
				?>
				<input
					<?= $this->getComponent()->getHtmlBuilder()->buildTagAttributes($attrList) ?>
				>
			</span>
			<?php
		}

		if(
			$arResult['userField']['MULTIPLE'] === 'Y'
			&&
			$arResult['additionalParameters']['SHOW_BUTTON'] !== 'N'
		)
		{
			print $component->getHtmlBuilder()->getCloneButton($arResult['fieldName']);
		}
		?>
	</span>
	<?php
}
?>
