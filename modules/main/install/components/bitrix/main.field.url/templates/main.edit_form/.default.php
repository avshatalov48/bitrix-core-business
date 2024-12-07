<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var UrlUfComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
?>

<table id="table_<?= $arResult['userField']['FIELD_NAME'] ?>">
	<?php
	foreach($arResult['value'] as $value)
	{
		?>
		<tr>
			<td>
				<input
					<?= $component->getHtmlBuilder()->buildTagAttributes($value['attrList']) ?>
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
					value="<?= GetMessage('USER_TYPE_PROP_ADD') ?>"
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
	?>
</table>