<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>
<div class="content-form mfeedback">
<?if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
		ShowError($v);
}
if($arResult["OK_MESSAGE"] <> '')
{
	?><div class="mf-ok-text"><?=$arResult["OK_MESSAGE"]?></div><?
}
?>

<form action="" method="POST">
<?=bitrix_sessid_post()?>
	<div class="field mf-name">
		<label class="field-title">
			<?=GetMessage("MFT_NAME")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>		
		</label>
		<div class="form-input">
			<input type="text" name="user_name" value="<?=$arResult["AUTHOR_NAME"]?>">
		</div>
	</div>
	<div class="field mf-email">
		<label class="field-title">
			<?=GetMessage("MFT_EMAIL")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>
		</label>
		<div class="form-input">
			<input type="text" name="user_email" value="<?=$arResult["AUTHOR_EMAIL"]?>">
		</div>
	</div>

	<div class="field mf-message">
		<label class="field-title">
			<?=GetMessage("MFT_MESSAGE")?><?if(empty($arParams["REQUIRED_FIELDS"]) || in_array("MESSAGE", $arParams["REQUIRED_FIELDS"])):?><span class="mf-req">*</span><?endif?>
		</label>
		<div class="form-input">
			<textarea name="MESSAGE" rows="5" cols="40"><?=$arResult["MESSAGE"]?></textarea>
		</div>
	</div>

	<?if($arParams["USE_CAPTCHA"] == "Y"):?>
	<div class="field mf-captcha">
		<label class="field-title"><?=GetMessage("MFT_CAPTCHA")?></label>
		<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>">
		<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA">
	</div>
	<div class="field">
		<label class="field-title"><?=GetMessage("MFT_CAPTCHA_CODE")?><span class="mf-req">*</span></label>
		<div class="form-input"><input type="text" name="captcha_word" size="30" maxlength="50" value=""  style="width:auto;"></div>
	</div>
	<?endif;?>
	<input type="submit" name="submit" value="<?=GetMessage("MFT_SUBMIT")?>">
</form>
</div>