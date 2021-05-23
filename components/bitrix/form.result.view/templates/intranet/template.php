<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?=$arResult["FormErrors"]?><?=$arResult["FORM_NOTE"]?>
<?
if ($arResult["isAccessFormResultEdit"] == "Y" && $arParams["EDIT_URL"] <> '') 
{
	$href = $arParams["SEF_MODE"] == "Y" ? str_replace("#RESULT_ID#", $arParams["RESULT_ID"], $arParams["EDIT_URL"]) : $arParams["EDIT_URL"].(mb_strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")."RESULT_ID=".$arParams["RESULT_ID"]."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"];
?>
<p>
[&nbsp;<a href="<?=$href?>"><?=GetMessage("FORM_EDIT")?></a>&nbsp;]
</p>
<?
}
?>
<table class="form-info-table data-table">
	<thead>
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><b><?echo GetMessage('FORM_RESULT_ID')?></b></td>
			<td><?=$arResult["RESULT_ID"]?></td>
		</tr>
		<tr>
			<td><b><?=GetMessage("FORM_FORM_NAME")?></b></td>
			<td><?=$arResult["FORM_TITLE"]?></td>
		</tr>
		<tr>
			<td><b><?=GetMessage("FORM_DATE_CREATE")?></b></td>
			<td><?=$arResult["RESULT_DATE_CREATE"]?></td>
		</tr>
		<tr>
			<td><b><?=GetMessage("FORM_TIMESTAMP")?></b></td>
			<td><?=$arResult["RESULT_TIMESTAMP_X"]?></td>
		</tr>
	</tbody>
</table>
<br />
<?
if ($arParams["SHOW_STATUS"] == "Y")
{
?>
<p>
<b><?=GetMessage("FORM_CURRENT_STATUS")?></b>&nbsp;[<span class="<?=htmlspecialcharsbx($arResult["RESULT_STATUS_CSS"])?>"><?=htmlspecialcharsbx($arResult["RESULT_STATUS_TITLE"])?></span>]
</p>
<?
}
?>

<table class="form-table data-table">
	<thead>
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?
		foreach ($arResult["RESULT"] as $FIELD_SID => $arQuestion)
		{
		?>
		<tr>
			<td><?=$arQuestion["CAPTION"]?><?=$arResult["arQuestions"][$FIELD_SID]["REQUIRED"] == "Y" ? $arResult["REQUIRED_SIGN"] : ""?>
			<?=$arQuestion["IS_INPUT_CAPTION_IMAGE"] == "Y" ? "<br />".$arQuestion["IMAGE"]["HTML_CODE"] : ""?>			
			</td>
			<td><?//=$arQuestion["ANSWER_HTML_CODE"]?>
			<?
			if (is_array($arQuestion['ANSWER_VALUE'])):
			foreach ($arQuestion['ANSWER_VALUE'] as $key => $arAnswer)
			{
			?>
			<?if ($arAnswer["ANSWER_IMAGE"]):?>
				<?if ($arAnswer["USER_TEXT"] <> ''):?><?=$arAnswer["USER_TEXT"]?><br /><?endif?>
				<img src="<?=$arAnswer["ANSWER_IMAGE"]["URL"]?>" <?=$arAnswer["ANSWER_IMAGE"]["ATTR"]?> border="0" />
			<?elseif ($arAnswer["ANSWER_FILE"]):?>
				<a title="<?=GetMessage("FORM_VIEW_FILE")?>" target="_blank" href="<?=$arAnswer["ANSWER_FILE"]["URL"]?>"><?=$arAnswer["ANSWER_FILE"]["NAME"]?></a><br />(<?=$arAnswer["ANSWER_FILE"]["SIZE_FORMATTED"]?>)<br />[&nbsp;<a title="<?=str_replace("#FILE_NAME#", $arAnswer["ANSWER_FILE"]["NAME"], GetMessage("FORM_DOWNLOAD_FILE"))?>" href="<?=$arAnswer["ANSWER_FILE"]["URL"]?>&action=download"><?=GetMessage("FORM_DOWNLOAD")?></a>&nbsp;]
			<? elseif($arAnswer["USER_TEXT"] <> ''):?>
				<?= $arAnswer["USER_TEXT"] ?>
			<?else:?>
				<?if ($arAnswer["ANSWER_TEXT"] <> ''):?>
				[<span class="form-anstext"><?=$arAnswer["ANSWER_TEXT"]?></span>]
					<?if ($arAnswer["ANSWER_VALUE"] <> ''):?>&nbsp;(<span class="form-ansvalue"><?=$arAnswer["ANSWER_VALUE"]?></span>)<?endif?>
				<br />
				<?endif;?>
			<?endif;?>
			<?
			} //foreach ($arQuestions)
			endif;
			?>
			</td>
		</tr>
		<?
		} // foreach ($arResult["RESULT"])
		?>
	</tbody>
</table>