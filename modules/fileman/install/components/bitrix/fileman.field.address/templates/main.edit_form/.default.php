<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
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

<table id='table_<?= $arResult['userField']['FIELD_NAME'] ?>'>

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

	foreach($arResult['value'] as $key => $value)
	{
	?>
		<tr>
			<td>
				<?php
				list($text, $coords) = AddressType::parseValue($value);

				$attrList = [
					'type' => 'text',
					'class' => $this->getComponent()->getHtmlBuilder()->getCssClassName(),
					'name' => str_replace('[]', '[' . $key . ']', $arResult['fieldName']),
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
			</td>
		</tr>
		<?php
	}

	if($arResult['userField']['MULTIPLE'] === 'Y')
	{
		$rowClass = '';
		$fieldNameX = str_replace('_', 'x', $arResult['userField']['FIELD_NAME']);
		?>
		<tr>
			<td style='padding-top: 6px;'>
				<input
					type="button"
					value="<?= Loc::getMessage('USER_TYPE_PROP_ADD') ?>"
					onClick="
						addNewRow(
						'table_<?= $arResult['userField']['FIELD_NAME'] ?>',
						'<?= $fieldNameX ?>|<?= $arResult['userField']['FIELD_NAME'] ?>|<?= $arResult['userField']['FIELD_NAME'] ?>_old_id'
						)"
				>
			</td>
		</tr>
		<?php
	}
	}
	?>
</table>