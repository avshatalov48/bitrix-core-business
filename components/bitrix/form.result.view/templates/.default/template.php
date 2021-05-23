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
<?
	if ($arResult["isAccessFormResultEdit"] == "Y")
	{
	?>
		<tr>
			<td><b>ID:</b></td>
			<td><?=$arResult["RESULT_ID"]?></td>
		</tr>
		<tr>
			<td><b><?=GetMessage("FORM_FORM_NAME")?></b></td>
			<td>[<a href='/bitrix/admin/form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arResult["WEB_FORM_ID"]?>'><?=$arResult["WEB_FORM_ID"]?></a>]&nbsp;(<?=$arResult["WEB_FORM_NAME"]?>)&nbsp;<?=$arResult["FORM_TITLE"]?></td>
		</tr>
		<?
		}
		?>
		<tr>
			<td><b><?=GetMessage("FORM_DATE_CREATE")?></b></td>
			<td><?=$arResult["RESULT_DATE_CREATE"]?>
				<?
				if ($arResult["isAccessFormResultEdit"] == "Y")
				{
					?>&nbsp;&nbsp;&nbsp;<?
					if (intval($arResult["RESULT_USER_ID"])>0)
					{
						$userName = array("NAME" => $arResult["RESULT_USER_FIRST_NAME"], "LAST_NAME" => $arResult["RESULT_USER_LAST_NAME"], "SECOND_NAME" => $arResult["RESULT_USER_SECOND_NAME"], "LOGIN" => $arResult["RESULT_USER_LOGIN"]);
					?>
						[<a title='<?=GetMessage("FORM_EDIT_USER")?>' href='/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arResult["RESULT_USER_ID"]?>'><?=$arResult["RESULT_USER_ID"]?></a>] (<?=$arResult["RESULT_USER_LOGIN"]?>) <?=CUser::FormatName($arParams["NAME_TEMPLATE"], $userName)?>
						<?if($arResult["RESULT_USER_AUTH"]=="N")
						{
							?> <?=GetMessage("FORM_NOT_AUTH")?><?
						}
					}
					else
					{
					?>
						<?=GetMessage("FORM_NOT_REGISTERED")?>
					<?
					}
				}
				?></td>
		</tr>
		<tr>
			<td><b><?=GetMessage("FORM_TIMESTAMP")?></b></td>
			<td><?=$arResult["RESULT_TIMESTAMP_X"]?></td>
		</tr>
		<?
		if ($arResult["isAccessFormResultEdit"] == "Y")
		{
			if ($arResult["isStatisticIncluded"] == "Y")
			{
		?>
		<tr>
			<td><b><?=GetMessage("FORM_GUEST")?></b></td>
			<td>[<a title="<?=GetMessage("FORM_GUEST_ALT")?>" href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$arResult["RESULT_STAT_GUEST_ID"]?>&find_id_exact_match=Y&set_filter=Y"><?=$arResult["RESULT_STAT_GUEST_ID"]?></a>]</td>
		</tr>
		<tr>
			<td><b><?=GetMessage("FORM_SESSION")?></b></td>
			<td>[<a href="/bitrix/admin/session_list.php?lang=<?=LANGUAGE_ID?>&find_id=<?=$arResult["RESULT_STAT_SESSION_ID"]?>&find_id_exact_match=Y&set_filter=Y"><?=$arResult["RESULT_STAT_SESSION_ID"]?></a>]</td>
		</tr>
			<?
			}
			?>
		<?
		}
		?>
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
			<?elseif ($arAnswer["USER_TEXT"] <> ''):?>
				<?=$arAnswer["USER_TEXT"]?>
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