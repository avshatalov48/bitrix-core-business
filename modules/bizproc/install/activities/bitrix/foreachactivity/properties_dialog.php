<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<tbody>
	<tr>
		<td align="right" width="40%"><?= GetMessage("BPFEA_PD_VARIABLE") ?>:</td>
		<td width="60%">
			<select name="variable">
				<option value="">...</option>
				<?
				foreach ($workflowVariables as $varName => $varInfo)
				{
					?><option value="<?=htmlspecialcharsbx($varName)?>"<?= ($arCurrentValues["variable"] == $varName) ? " selected" : "" ?>><?= $varInfo["Name"] ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
</tbody>