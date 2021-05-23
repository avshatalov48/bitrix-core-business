<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif(!empty($arResult["FATAL_ERROR"])>0)
{
	foreach($arResult["FATAL_ERROR"] as $v)
	{
		?>
		<span class='errortext'><?=$v?></span><br /><br />
		<?
	}
}
else
{
	if(!empty($arResult["ERROR_MESSAGE"])>0)
	{
		foreach($arResult["ERROR_MESSAGE"] as $v)
		{
			?>
			<span class='errortext'><?=$v?></span><br /><br />
			<?
		}
	}
	?>
	<form method="post" action="<?=POST_FORM_ACTION_URI?>" ENCTYPE="multipart/form-data">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="BLOG_URL" value="<?=$arResult["BLOG"]["URL"]?>">
	<table class="blog-blog-edit">
		<tr>
			<th><span class="blog-req">*</span> <b><?=GetMessage('BLOG_TITLE')?></b></th>
			<td><input type="text" name="NAME" maxlength="100" size="40" value="<?= $arResult["BLOG"]["NAME"]?>" style="width:98%"></td>
			<td class="blog-small"><?=GetMessage('BLOG_TITLE_DESCR')?></td>
		</tr>
		<tr>
			<th><b><?=GetMessage('BLOG_DESCR')?></b></th>
			<td>
				<textarea name="DESCRIPTION" rows="5" cols="40" style="width:98%"><?=$arResult["BLOG"]["DESCRIPTION"]?></textarea>
			</td>
			<td class="blog-small"><?=GetMessage('BLOG_DESCR_TITLE')?></td>
		</tr>		
		<?
		if($arResult["useCaptcha"] == "U")
		{
			?>
			<tr>
				<th><b><?=GetMessage('BLOG_AUTO_MSG')?></b></th>
				<td>
					<input id="IMG_VERIF" type="checkbox" name="ENABLE_IMG_VERIF" value="Y"<?if ($arResult["BLOG"]["ENABLE_IMG_VERIF"] != "N") echo " checked";?>>
					<label for="IMG_VERIF"><?=GetMessage('BLOG_AUTO_MSG_TITLE')?></label>
				</td>
				<td class="blog-small"><?=GetMessage('BLOG_CAPTHA')?></td>
			</tr>
			<?
		}
		?>

		<tr>
			<th><b><?=GetMessage('BLOG_EMAIL_NOTIFY')?></b></th>
			<td>
				<input id="EMAIL_NOTIFY" type="checkbox" name="EMAIL_NOTIFY" value="Y"<?if ($arResult["BLOG"]["EMAIL_NOTIFY"] != "N") echo " checked";?>>
				<label for="EMAIL_NOTIFY"><?=GetMessage('BLOG_EMAIL_NOTIFY_TITLE')?></label>
			</td>
			<td class="blog-small"><?=GetMessage('BLOG_EMAIL_NOTIFY_HELP')?></td>
		</tr>
		</table>
		<br />

		<input type="submit" name="save" value="<?= (intval($arResult["BLOG"]["ID"])>0 ? GetMessage('BLOG_SAVE') : GetMessage('BLOG_CREATE')) ?>">
		<?
		if ($arResult["CAN_UPDATE"]=="Y")
		{
			?>
			<input type="submit" name="apply" value="<?=GetMessage('BLOG_APPLY')?>">
			<input type="submit" name="reset" value="<?=GetMessage('BLOG_CANCEL')?>">
			<?
		}
		?>
		<input type="hidden" name="do_blog" value="Y">
	</form>
	
	<span class="blogtext">
	<br /><br /><?echo GetMessage("STOF_REQUIED_FIELDS_NOTE")?><br /><br />
	</span>
	<?
}
?>
