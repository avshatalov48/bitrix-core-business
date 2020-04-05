<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
foreach ($arAllowableOperations as $operationKey => $operationValue)
{
	?>
	<tr>
		<td align="right" width="40%"><?= str_replace("#OP#", $operationValue, GetMessage("BPSA_PD_PERM")) ?>:</td>
		<td width="60%">
			<?=CBPDocument::ShowParameterField("user", 'permission_'.$operationKey, $arCurrentValues["permission_".$operationKey], Array())?>
		</td>
	</tr>
	<?
}
?>
