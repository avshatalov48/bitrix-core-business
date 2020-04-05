<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
if (!array_key_exists("condition_type", $arCurrentValues)
	|| strlen($arCurrentValues["condition_type"]) <= 0
	|| !array_key_exists($arCurrentValues["condition_type"], $arActivities))
{
	$arCurrentValues["condition_type"] = $firstConditionType;
}
?>
<tbody>
	<tr>
		<td align="right" width="40%"><?= GetMessage("BPWA_PD_TYPE") ?>:</td>
		<td width="60%">
			<select name="condition_type" onchange="BWFIBAChangeType(this.options[this.selectedIndex].value)">
				<?
				foreach ($arActivities as $key => $value)
				{
					?><option value="<?= $key ?>"<?= ($arCurrentValues["condition_type"] == $key) ? " selected" : "" ?>><?= $value["NAME"] ?></option><?
				}
				?>
			</select>
			<script language="JavaScript">
			function BWFIBAChangeType(newType)
			{
				<?
				foreach ($arActivities as $key => $value)
				{
					?>document.getElementById('id_bwfiba_type_<?= $key ?>').style.display = "none";
					<?
				}
				?>
				document.getElementById('id_bwfiba_type_' + newType).style.display = "";
			}
			</script>
		</td>
	</tr>
</tbody>

<?
foreach ($arActivities as $key => $value)
{
	?><tbody id="id_bwfiba_type_<?= $key ?>" style="display:<?= ($arCurrentValues["condition_type"] == $key) ? "" : "none" ?>"><?
	echo $arActivities[$key]["PROPERTIES_DIALOG"];
	?></tbody><?
}
?>