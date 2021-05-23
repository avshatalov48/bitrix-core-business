<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["FatalErrorMessage"] <> '')
{
	?>
	<span class='errortext'><?= $arResult["FatalErrorMessage"] ?></span><br /><br />
	<?
}
else
{
	if ($arResult["ErrorMessage"] <> '')
	{
		?>
		<span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br /><br />
		<?
	}
	if ($arResult["AdminAccess"])
	{
		$arButtons = array(
			array(
				"TEXT"=>GetMessage("BPWC_WICT_NEW_BP"),
				"TITLE"=>GetMessage("BPWC_WICT_NEW_BP"),
				"LINK"=>$arResult["NEW_URL"],
				"ICON"=>"btn-new",
			),
		);
		$APPLICATION->IncludeComponent(
			"bitrix:main.interface.toolbar",
			"",
			array(
				"BUTTONS" => $arButtons
			),
			$component
		);
	}
	?>
	<table cellpadding="2" border="0" cellspacing="2" width="100%">
		<?
		$ind = -1;
		foreach ($arResult["Blocks"] as $arBlock)
		{
			$ind++;
			if ($ind % $arParams["COLUMNS_COUNT"] == 0)
				echo "<tr>";
			?>
			<td>
				<div class="bpcw-item-container">
					<?
					if ($arResult["AdminAccess"])
					{
						?>
						<div class="bpcw-item-menu-container" onmouseover="this.firstChild.style.display='block';" onmouseout="this.firstChild.style.display='none';" onclick="jsUtils.Redirect([], '<?= Cutil::JSEscape(htmlspecialcharsbx($arBlock["LIST_URL"])) ?>');"><div class="bpcw-item-menu" style="display:none;" onmouseout="this.style.display='none';"><a rel="nofollow" href="<?= htmlspecialcharsbx($arBlock["EDIT_URL"]) ?>" class="bpcw-item-menu-edit" onclick="jsUtils.PreventDefault(event); jsUtils.Redirect([], '<?= Cutil::JSEscape(htmlspecialcharsbx($arBlock["EDIT_URL"])) ?>'); return false;" title="<?= GetMessage("BPWC_WICT_EDIT") ?>"><span style="display:none">Edit</span></a><a rel="nofollow" href="javascript:if(confirm('<?= GetMessageJS("BPWC_WICT_DELETE_PROMT") ?>'))jsUtils.Redirect([], '<?= Cutil::JSEscape(htmlspecialcharsbx($arBlock["DELETE_URL"])) ?>')" class="bpcw-item-menu-delete" onclick="jsUtils.PreventDefault(event); if(confirm('<?= GetMessageJS("BPWC_WICT_DELETE_PROMT") ?>'))jsUtils.Redirect([], '<?= Cutil::JSEscape(htmlspecialcharsbx($arBlock["DELETE_URL"])) ?>'); return false;"  title="<?= GetMessage("BPWC_WICT_DELETE") ?>"><span style="display:none">Delete</span></a></div></div>
						<?
					}
					?>

					<div class="bpcw-item-info-container">
						<div style="height:160px;">
						<?= CFile::ShowImage($arBlock["PICTURE"], 150, 150, null, $arBlock["LIST_URL"]) ?>
						</div>
						<a href="<?= htmlspecialcharsbx($arBlock["LIST_URL"]) ?>"><b><?= $arBlock["NAME"] ?></b></a><br><br>
						<?
						if ($arBlock["START_URL"] <> '')
						{
							?><a href="<?= htmlspecialcharsbx($arBlock["START_URL"]) ?>"><?= ($arBlock["CreateTitle"] <> '') ? $arBlock["CreateTitle"] : GetMessage("BPWC_WICT_CREATE") ?></a><?
						}
						?>
					</div>
				</div>
				<br><br>
			</td>
			<?
			if ($ind % $arParams["COLUMNS_COUNT"] == $arParams["COLUMNS_COUNT"] - 1)
				echo "</tr>";
		}
		if ($ind == -1)
		{
			?><tr><td><?= GetMessage("BPWC_WICT_EMPTY") ?></td></tr><?
		}
		?>
	</table>
	<?
}
?>