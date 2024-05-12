<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams, $arResult
 * @var CBitrixComponentTemplate $this
 * @var CMain $APPLICATION
 * @var CUser $USER
 */
$tabIndex = 1;
?>
<div class="reviews-reply-form" <?=(empty($arResult["ERROR_MESSAGE"]) ? ' style="display: none;"' : '')?>>
<div data-bx-role="preview">
		<?php
		if (!empty($arResult["MESSAGE_VIEW"]))
		{
		?>
		<div class="reviews-preview">
			<a name="review_anchor"></a>
			<div class="reviews-header-box">
				<div class="reviews-header-title"><a name="postform"><span><?=GetMessage("F_PREVIEW")?></span></a></div>
			</div>
			<div class="reviews-info-box reviews-post-preview">
				<div class="reviews-info-box-inner">
					<div class="reviews-post-entry">
						<div class="reviews-post-text"><?=$arResult["MESSAGE_VIEW"]["POST_MESSAGE_TEXT"]?></div>
						<?php
						if (!empty($arResult["REVIEW_FILES"])):
							?>
							<div class="reviews-post-attachments">
								<label><?=GetMessage("F_ATTACH_FILES")?></label>
								<?php
								foreach ($arResult["REVIEW_FILES"] as $arFile):
									?>
									<div class="reviews-post-attachment">
										<?php $GLOBALS["APPLICATION"]->IncludeComponent(
											"bitrix:forum.interface", "show_file",
											Array(
												"FILE" => $arFile,
												"WIDTH" => $arResult["PARSER"]->image_params["width"],
												"HEIGHT" => $arResult["PARSER"]->image_params["height"],
												"CONVERT" => "N",
												"FAMILY" => "FORUM",
												"SINGLE" => "Y",
												"RETURN" => "N",
												"SHOW_LINK" => "Y"),
											null,
											array("HIDE_ICONS" => "Y"));
										?></div>
								<?php
								endforeach;
								?>
							</div>
						<?php
						endif;
						?>
					</div>
				</div>
			</div>
			<div class="reviews-br"></div>
	</div><?php
	}
?>
</div>
<div data-bx-role="error"><?php
if (!empty($arResult["ERROR_MESSAGE"]))
{
	$arResult["ERROR_MESSAGE"] = preg_replace(array("/<br(.*?)><br(.*?)>/is", "/<br(.*?)>$/is"), array("<br />", ""), $arResult["ERROR_MESSAGE"]);
	?>
		<div data-bx-role="error-message" class="reviews-note-box reviews-note-error">
			<div class="reviews-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "reviews-note-error");?></div>
		</div>
	<?php
}
?></div>
<form name="<?=$arParams["FORM_ID"] ?>" id="<?=$arParams["FORM_ID"]?>" <?php
	?>action="<?=POST_FORM_ACTION_URI?>#postform" method="POST" enctype="multipart/form-data" class="reviews-form">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="index" value="<?=htmlspecialcharsbx($arParams["form_index"])?>" />
	<input type="hidden" name="back_page" value="<?=$arResult["CURRENT_PAGE"]?>" />
	<input type="hidden" name="ELEMENT_ID" value="<?=$arParams["ELEMENT_ID"]?>" />
	<input type="hidden" name="SECTION_ID" value="<?=$arResult["ELEMENT_REAL"]["IBLOCK_SECTION_ID"]?>" />
	<input type="hidden" name="save_product_review" value="Y" />
	<input type="hidden" name="preview_comment" value="N" />
	<input type="hidden" name="AJAX_POST" value="<?=$arParams["AJAX_POST"]?>" />
	<?
	if ($arParams['AUTOSAVE'])
		$arParams['AUTOSAVE']->Init();
	?>
	<div style="position:relative; display: block; width:100%;">
		<?
		/* GUEST PANEL */
		if (!$arResult["IS_AUTHORIZED"]):
			?>
			<div class="reviews-reply-fields">
				<div class="reviews-reply-field-user">
					<div class="reviews-reply-field reviews-reply-field-author"><label for="REVIEW_AUTHOR<?=$arParams["form_index"]?>"><?=GetMessage("OPINIONS_NAME")?><?
							?><span class="reviews-required-field">*</span></label>
						<span><input name="REVIEW_AUTHOR" id="REVIEW_AUTHOR<?=$arParams["form_index"]?>" size="30" type="text" value="<?=$arResult["REVIEW_AUTHOR"]?>" tabindex="<?=$tabIndex++;?>" /></span></div>
					<?
					if ($arResult["FORUM"]["ASK_GUEST_EMAIL"]=="Y"):
						?>
						<div class="reviews-reply-field-user-sep">&nbsp;</div>
						<div class="reviews-reply-field reviews-reply-field-email"><label for="REVIEW_EMAIL<?=$arParams["form_index"]?>"><?=GetMessage("OPINIONS_EMAIL")?></label>
							<span><input type="text" name="REVIEW_EMAIL" id="REVIEW_EMAIL<?=$arParams["form_index"]?>" size="30" value="<?=$arResult["REVIEW_EMAIL"]?>" tabindex="<?=$tabIndex++;?>" /></span></div>
					<?
					endif;
					?>
					<div class="reviews-clear-float"></div>
				</div>
			</div>
		<?
		endif;
		?>
		<div class="reviews-reply-header"><span><?=$arParams["MESSAGE_TITLE"]?></span><span class="reviews-required-field">*</span></div>
		<div class="reviews-reply-field reviews-reply-field-text">
			<?

			$APPLICATION->IncludeComponent(
				"bitrix:main.post.form",
				"",
				Array(
					"FORM_ID" => $arParams["FORM_ID"],
					"PARSER" => forumTextParser::GetEditorToolbar(array("forum" => $arResult["FORUM"])),

					"LHE" => array(
						'id' => 'lhe'.$arParams["FORM_ID"],
						'bResizable' => true,
						'bAutoResize' => true,
						"documentCSS" => "body {color:#434343; font-size: 14px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 20px;}",
						'setFocusAfterShow' => false,
						'lazyLoad' => empty($arResult["ERROR_MESSAGE"]),
					),

					"ADDITIONAL" => array(),

					"TEXT" => Array(
						"ID" => "REVIEW_TEXT",
						"NAME" => "REVIEW_TEXT",
						"VALUE" => isset($arResult["REVIEW_TEXT"]) ? $arResult["REVIEW_TEXT"] : "",
						"SHOW" => "Y",
						"HEIGHT" => "200px"),

					"SMILES" => COption::GetOptionInt("forum", "smile_gallery_id", 0),
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);
			?>
		</div>
		<?

		/* CAPTHCA */
		if ($arResult["CAPTCHA_CODE"] <> ''):
			?>
			<div class="reviews-reply-field reviews-reply-field-captcha">
				<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>"/>
				<div class="reviews-reply-field-captcha-label">
					<label for="captcha_word"><?=GetMessage("F_CAPTCHA_PROMT")?><span class="reviews-required-field">*</span></label>
					<input type="text" size="30" name="captcha_word" tabindex="<?=$tabIndex++;?>" autocomplete="off" />
				</div>
				<div class="reviews-reply-field-captcha-image">
					<img name="captcha_image" src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" alt="<?=GetMessage("F_CAPTCHA_TITLE")?>" />
				</div>
			</div>
		<?
		endif;
		/* ATTACH FILES */
		if ($arResult["SHOW_PANEL_ATTACH_IMG"] == "Y")
		{
			?><div class="reviews-reply-field reviews-reply-field-upload">
				<input type="checkbox" data-bx-role="attach-visibility" id="attaches-<?=$arParams["form_index"]?>">
				<div data-bx-role="attach-form">
					<div class="reviews-upload-info"><?php
						$acceptExtensions = "";
						if ($arParams["FORUM"]["ALLOW_UPLOAD"] == "F")
						{
							$acceptExtensions = $arParams["FORUM"]["ALLOW_UPLOAD_EXT"];
							?>
							<div><?=str_replace(
								"#EXTENSION#",
								$arParams["FORUM"]["ALLOW_UPLOAD_EXT"], GetMessage("F_FILE_EXTENSION"))?>
							</div>
							<?php
						}
						?>
						<div><?=GetMessage(
								"F_FILE_SIZE",
								["#SIZE#" => CFile::FormatSize(COption::GetOptionString("forum", "file_max_size", 5242880))])?>
						</div>
					</div><?php
					$ii = 0;
					while (($ii++) < $arParams["FILES_COUNT"])
					{
						?>
						<div class="reviews-upload-file">
							<input name="FILE_NEW_<?=$ii?>" type="file" accept="<?=htmlspecialcharsbx($acceptExtensions)?>" size="30" />
						</div>
						<?php
					}
					?>
				</div>
				<label for="attaches-<?=$arParams["form_index"]?>" class="forum-upload-file-attach">
					<?=($arResult["FORUM"]["ALLOW_UPLOAD"] == "Y" ? GetMessage("F_LOAD_IMAGE")
						: GetMessage("F_LOAD_FILE"))?>
				</label>
			</div><?php
		}
		?>
		<div class="reviews-reply-field reviews-reply-field-settings">
			<?
			/* SMILES */
			if ($arResult["FORUM"]["ALLOW_SMILES"] == "Y"):
				?>
				<div class="reviews-reply-field-setting">
					<input type="checkbox" name="REVIEW_USE_SMILES" id="REVIEW_USE_SMILES<?=$arParams["form_index"]?>" <?
					?>value="Y" <?=($arResult["REVIEW_USE_SMILES"]=="Y") ? "checked=\"checked\"" : "";?> <?
					?>tabindex="<?=$tabIndex++;?>" /><?
					?>&nbsp;<label for="REVIEW_USE_SMILES<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_ALLOW_SMILES")?></label></div>
			<?
			endif;
			/* SUBSCRIBE */
			if ($arResult["SHOW_SUBSCRIBE"] == "Y"):
				?>
				<div class="reviews-reply-field-setting">
					<input type="checkbox" name="TOPIC_SUBSCRIBE" id="TOPIC_SUBSCRIBE<?=$arParams["form_index"]?>" value="Y" <?
					?><?=($arResult["TOPIC_SUBSCRIBE"] == "Y")? "checked disabled " : "";?> tabindex="<?=$tabIndex++;?>" /><?
					?>&nbsp;<label for="TOPIC_SUBSCRIBE<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_SUBSCRIBE_TOPIC")?></label></div>
			<?
			endif;
			?>
		</div>
		<div class="reviews-reply-buttons">
			<input type="submit" tabindex="<?=$tabIndex++;?>" value="<?=GetMessage("OPINIONS_SEND")?>">
		</div>
	</div>
</form>
<script>
	BX.ready(function(){
		BX.Forum.Reviews.Form.create({
			formId: '<?=$arParams["FORM_ID"]?>',
			editorId: 'lhe<?=$arParams["FORM_ID"]?>',
			formNode: BX('<?=$arParams["FORM_ID"]?>'),
			useAjax : <?=($arParams["AJAX_POST"] === 'Y' ? 'true' : 'false')?>,
		});
	});
</script>
</div>
<?
if ($arParams['AUTOSAVE'])
	$arParams['AUTOSAVE']->LoadScript(array(
		"formID" => CUtil::JSEscape($arParams["FORM_ID"]),
		"controlID" => "REVIEW_TEXT"
	));
