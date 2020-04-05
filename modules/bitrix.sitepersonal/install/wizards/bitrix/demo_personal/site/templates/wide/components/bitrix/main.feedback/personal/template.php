<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>
<?if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
		ShowError($v);
}
if(strlen($arResult["OK_MESSAGE"]) > 0)
{
	?><div class="blog-note-box"><?=$arResult["OK_MESSAGE"]?></div><?
}
?>

<div class="content-form feedback-form">
<form action="" method="POST">
<?=bitrix_sessid_post()?>
	<div class="fields">
		<div class="field">
			<label class="field-title">
				<?=GetMessage("MFT_NAME")?>
			</label>
			<div class="form-input">
				<input type="text" name="user_name" value="<?=$arResult["AUTHOR_NAME"]?>" class="input-field">
			</div>
		</div>
		<div class="field">
			<label class="field-title">
				<?=GetMessage("MFT_EMAIL")?>
			</label>
			<div class="form-input">
				<input type="text" name="user_email" value="<?=$arResult["AUTHOR_EMAIL"]?>" class="input-field">
			</div>
		</div>

		<div class="field">
			<label class="field-title">
				<?=GetMessage("MFT_MESSAGE")?>
			</label>
			<div class="form-input">
				<textarea name="MESSAGE" rows="5" cols="40"><?=$arResult["MESSAGE"]?></textarea>
			</div>
		</div>

		<?if($arParams["USE_CAPTCHA"] == "Y"):?>
		<div class="field">
			<label class="field-title"><?=GetMessage("MFT_CAPTCHA")?></label>
			<div class="form-input">
				<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>">
				<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA"><br />
				<?=GetMessage("MFT_CAPTCHA_CODE")?>
				<input type="text" name="captcha_word" size="30" maxlength="50" value="">
			</div>
		</div>
		<?endif;?>
		<input type="submit" name="submit" value="<?=GetMessage("MFT_SUBMIT")?>">
	</div>
</form>
</div>