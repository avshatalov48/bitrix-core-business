<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
?>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPSFA_PD_STATE") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField(
				"text",
				'target_state_title',
				$arCurrentValues['target_state_title'],
				array('rows' => 2, 'maxlength' => 255))?>
	</td>
</tr>
