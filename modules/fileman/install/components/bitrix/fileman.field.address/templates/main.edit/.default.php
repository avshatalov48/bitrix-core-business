<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Text\HtmlFilter;

/**
 * @var AddressUfComponent $component
 * @var array $arResult
 * @var array $arParams
 */

$component = $this->getComponent();
$userField = $arResult['userField'];
$additionalParameters = $arResult['additionalParameters'];

$randString = $this->randString();
if ($component->isAjaxRequest())
{
	$randString .= time();
}

if($arResult['canUseMap'])
{
	$controlId = HtmlFilter::encode($userField['FIELD_NAME']) . '_' . $randString;
	$nodeId = $controlId . '_result';
	?>
	<div id="<?= $controlId ?>"></div>
	<span style="display: none;" id="<?= $nodeId ?>"></span>

	<script>
		BX.ready(function ()
		{
			new BX.Default.Field.Address(
				<?=CUtil::PhpToJSObject([
					'controlId' => $controlId,
					'value' => $arResult['value'],
					'isMultiple' => ($userField['MULTIPLE'] === 'Y' ? 'true' : 'false'),
					'nodeJs' => $nodeId,
					'fieldNameJs' => $arResult['fieldName'],
					'fieldName' => $arParams['userField']['FIELD_NAME'],
					'showMap' => $arResult['showMap'],
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
