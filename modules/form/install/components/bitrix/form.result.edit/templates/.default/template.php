<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?
if ($arParams["VIEW_URL"])
{
	$href = $arParams["SEF_MODE"] == "Y" ? str_replace("#RESULT_ID#", $arParams["RESULT_ID"], $arParams["VIEW_URL"]) : $arParams["VIEW_URL"].(strpos($arParams["VIEW_URL"], "?") === false ? "?" : "&")."RESULT_ID=".$arParams["RESULT_ID"]."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"];
?>
<p>[&nbsp;<a href="<?=$href?>"><?=GetMessage("FORM_VIEW")?></a>&nbsp;]</p>
<?
}
?>
<table class="form-info-table data-table">
	<?
	if ($arResult["isAccessFormParams"] == "Y")
	{?>
	<thead>
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
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
			<td><?=FormatDateFromDB($arResult["RESULT_DATE_CREATE"])?>
				<?
		if ($arResult["isAccessFormParams"] == "Y")
		{
			?>&nbsp;&nbsp;&nbsp;<?
			if (intval($arResult["RESULT_USER_ID"])>0)
			{
				$userName = array("NAME" => $arResult["RESULT_USER_FIRST_NAME"], "LAST_NAME" => $arResult["RESULT_USER_LAST_NAME"], "SECOND_NAME" => $arResult["RESULT_USER_SECOND_NAME"], "LOGIN" => $arResult["RESULT_USER_LOGIN"]);
			?>
				[<a title='<?=GetMessage("FORM_EDIT_USER")?>' href='/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$arResult["RESULT_USER_ID"]?>'><?=$arResult["RESULT_USER_ID"]?></a>] (<?=$arResult["RESULT_USER_LOGIN"]?>) <?=CUser::FormatName($arParams["NAME_TEMPLATE"], $userName)?>
				<?if($arResult["RESULT_USER_AUTH"] == "N"): ?> <?=GetMessage("FORM_NOT_AUTH")?><?endif;?>
			<?
			}
			else
			{
			?>
				<?=GetMessage("FORM_NOT_REGISTERED")?>
			<?
			}
			?>
		<?
		}
				?></td>
		</tr>
		<tr>
			<td><b><?=GetMessage("FORM_TIMESTAMP")?></b></td>
			<td><?=FormatDateFromDB($arResult["RESULT_TIMESTAMP_X"])?></td>
		</tr>
		<?
		if ($arResult["isAccessFormParams"] == "Y")
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
<?=$arResult["FORM_HEADER"]?>
<?=bitrix_sessid_post()?>

<?if($arResult["FORM_SIMPLE"] == "N" && $arResult["isResultStatusChangeAccess"] == "Y" && $arParams["EDIT_STATUS"] == "Y")
{
?>
<p>
			<b><?=GetMessage("FORM_CURRENT_STATUS")?></b>
			[<?=$arResult["RESULT_STATUS"]?>]
			<?=GetMessage("FORM_CHANGE_TO")?>
			<?=$arResult["RESULT_STATUS_FORM"]?>
</p>
<?
}
?>
<table>
<?
if ($arResult["isFormDescription"] == "Y" || $arResult["isFormTitle"] == "Y" || $arResult["isFormImage"] == "Y")
{
?>
	<tr>
		<td><?
/***********************************************************************************
					form header
***********************************************************************************/
if ($arResult["isFormTitle"])
{
?>
			<h3><?=$arResult["FORM_TITLE"]?></h3>
<?
} //endif ;

if ($arResult["isFormImage"] == "Y")
{
?>
<a href="<?=$arResult["FORM_IMAGE"]["URL"]?>" target="_blank" alt="<?=GetMessage("FORM_ENLARGE")?>"><img src="<?=$arResult["FORM_IMAGE"]["URL"]?>" <?if($arResult["FORM_IMAGE"]["WIDTH"] > 300):?>width="300"<?elseif($arResult["FORM_IMAGE"]["HEIGHT"] > 200):?>height="200"<?else:?><?=$arResult["FORM_IMAGE"]["ATTR"]?><?endif;?> hspace="3" vscape="3" border="0" /></a>
<?//=$arResult["FORM_IMAGE"]["HTML_CODE"]?>
<?
} //endif
?>

			<p><?=$arResult["FORM_DESCRIPTION"]?></p>
		</td>
	</tr>
	<?
} // endif
	?>
</table>
<br />
<?

/***********************************************************************************
					Form questions
***********************************************************************************/
		?>
<?if ($arResult["FORM_NOTE"]):?><?=$arResult["FORM_NOTE"]?><?endif?>
<?if ($arResult["isFormErrors"] == "Y"):?><?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>
<table class="form-table data-table">
	<thead>
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	<?
	foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion)
	{
	?>
	<tr>
		<td>
			<?if (is_array($arResult["FORM_ERRORS"]) && array_key_exists($FIELD_SID, $arResult['FORM_ERRORS'])):?>
			<span class="error-fld" title="<?=htmlspecialcharsbx($arResult["FORM_ERRORS"][$FIELD_SID])?>"></span>
			<?endif;?>
			<?=$arQuestion["CAPTION"]?><?=$arResult["arQuestions"][$FIELD_SID]["REQUIRED"] == "Y" ? $arResult["REQUIRED_SIGN"] : ""?>
			<?=$arQuestion["IS_INPUT_CAPTION_IMAGE"] == "Y" ? "<br />".$arQuestion["IMAGE"]["HTML_CODE"] : ""?>
		</td>
		<td><?=$arQuestion["HTML_CODE"]?></td>
	</tr>
	<?
	} //endwhile
	?>
	</tbody>
	<tfoot>
	<tr>
		<th colspan="2">
			<input type="submit" name="web_form_submit" value="<?=htmlspecialcharsbx(strlen(trim($arResult["arForm"]["BUTTON"])) <= 0 ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]);?>" />
			&nbsp;<input type="hidden" name="web_form_apply" value="Y" /><input type="submit" name="web_form_apply" value="<?=GetMessage("FORM_APPLY")?>" />
			&nbsp;<input type="reset" value="<?=GetMessage("FORM_RESET");?>" />
		</th>
	</tr>
	</tfoot>
</table>
<p>
<?=$arResult["REQUIRED_SIGN"]?> - <?=GetMessage("FORM_REQUIRED_FIELDS")?>
</p>
<?=$arResult["FORM_FOOTER"]?>
