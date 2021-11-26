<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach (GetModuleEvents('forum', 'OnCommentFormDisplay', true) as $arEvent)
{
	$arExt = ExecuteModuleEventEx($arEvent);
	if ($arExt !== null)
	{
		foreach($arExt as $arTpl)
			$APPLICATION->AddViewContent(implode('_', array($tplID, 'EDIT', $arTpl['DISPLAY'])), $arTpl['TEXT'], $arTpl['SORT']);
	}
}
?>
<a name="review_anchor"></a>
<?
if (!empty($arResult["ERROR_MESSAGE"]))
{
	?>
	<div class="feed-add-error">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span><?=$arResult["ERROR_MESSAGE"]?></span>
	</div>
<?
}
?>
<form name="<?=$arParams["FORM_ID"]?>" id="<?=$arParams["FORM_ID"]?>" action="<?=POST_FORM_ACTION_URI?>"<?
?> method="POST" enctype="multipart/form-data" class="comments-form">
	<input type="hidden" name="back_page" value="<?=$arResult["CURRENT_PAGE"]?>" />
	<input type="hidden" name="ENTITY_XML_ID" value="<?=$arParams["ENTITY_XML_ID"]?>" />
	<input type="hidden" name="ENTITY_TYPE" value="<?=$arParams["ENTITY_TYPE"]?>" />
	<input type="hidden" name="ENTITY_ID" value="<?=$arParams["ENTITY_ID"]?>" />
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="REVIEW_USE_SMILES" value="Y"  />
	<input type="hidden" name="comment_review" value="Y"  /><?
		ob_start();
		/* GUEST PANEL */
		if (!$GLOBALS["USER"]->IsAuthorized())
		{
			?>
			<div class="comments-reply-fields">
				<div class="comments-reply-field-user">
					<div class="comments-reply-field comments-reply-field-author"><label for="REVIEW_AUTHOR<?=$arParams["form_index"]?>"><?=GetMessage("OPINIONS_NAME")?><?
							?><span class="comments-required-field">*</span></label>
						<span><input name="REVIEW_AUTHOR" id="REVIEW_AUTHOR<?=$arParams["form_index"]?>" size="30" type="text" value="<?=$arResult["REVIEW_AUTHOR"]?>" tabindex="<?=$tabIndex++;?>" /></span></div>
					<?
					if ($arParams["ASK_GUEST_EMAIL"]=="Y")
					{
						?>
						<div class="comments-reply-field-user-sep">&nbsp;</div>
						<div class="comments-reply-field comments-reply-field-email"><label for="REVIEW_EMAIL<?=$arParams["form_index"]?>"><?=GetMessage("OPINIONS_EMAIL")?></label>
							<span><input type="text" name="REVIEW_EMAIL" id="REVIEW_EMAIL<?=$arParams["form_index"]?>" size="30" value="<?=$arResult["REVIEW_EMAIL"]?>" tabindex="<?=$tabIndex++;?>" /></span></div>
					<?
					}
					?>
					<div class="comments-clear-float"></div>
				</div>
			</div>
		<?
		}
		$html_before_textarea = ob_get_clean();
		ob_start();
		/* CAPTHCA */
		if (!empty($arResult["CAPTCHA_CODE"]))
		{
			?>
			<div class="comments-reply-field comments-reply-field-captcha">
				<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>"/>
				<div class="comments-reply-field-captcha-label">
					<label for="captcha_word"><?=GetMessage("F_CAPTCHA_PROMT")?><span class="comments-required-field">*</span></label>
					<input type="text" size="30" name="captcha_word" tabindex="<?=($tabIndex++)?>" autocomplete="off" />
				</div>
				<div class="comments-reply-field-captcha-image">
					<img src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" alt="<?=GetMessage("F_CAPTCHA_TITLE")?>" />
				</div>
			</div>
		<?
		}
		$html_after_textarea = ob_get_clean();

		$APPLICATION->IncludeComponent("bitrix:main.post.form", "",
			Array(
				"FORM_ID" => $arParams["FORM_ID"],
				"SHOW_MORE" => "Y",
				"PARSER" => forumTextParser::GetEditorToolbar(array('forum' => $arParams["ALLOW"])),
				"BUTTONS" => array_unique(
					(isset($arResult["USER_FIELDS"]["UF_FORUM_MESSAGE_DOC"]) ? array("UploadFile") : array() )
					+
					array_intersect(array("UploadFile", "CreateLink", "InputVideo", "Quote", "MentionUser"),
						forumTextParser::GetEditorButtons(array('forum' => $arParams["ALLOW"]))
				)),
				'ALLOW_MENTION_EMAIL_USER' => ($arParams['ALLOW_MENTION_EMAIL_USER'] ?? 'N'),
				"LHE" => array(
					'id' => $arParams["LheId"],
					'jsObjName' => $arParams["jsObjName"],
					'bSetDefaultCodeView' => ($arParams['EDITOR_CODE_DEFAULT'] == 'Y'),
					"documentCSS" => "body {color:#434343;}",
					"iframeCss" => "html body {padding-left: 14px!important;}",
					"fontFamily" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
					"fontSize" => "12px",
					"bInitByJS" => ($arParams['SHOW_MINIMIZED'] == "Y"),
					"height" => 80
				),
				"DESTINATION" => Array(
					"VALUE" => array(),
					"SHOW" => "N",
				),
				"TEXT" => Array(
					"ID" => "REVIEW_TEXT",
					"NAME" => "REVIEW_TEXT",
					"VALUE" => isset($arResult["REVIEW_TEXT"]) ? $arResult["REVIEW_TEXT"] : "",
					"HEIGHT" => "80px"),

				"UPLOAD_FILE" => (
					isset($arResult["USER_FIELDS"]["UF_FORUM_MESSAGE_DOC"]) || $arParams["ALLOW_UPLOAD"]=="N" ? false :
					array(
						"TAG" => "FILE ID",
						"INPUT_NAME" => 'FILE_NEW',
						"INPUT_VALUE" => array(),
						"MAX_FILE_SIZE" => COption::GetOptionString("forum", "file_max_size", 5242880),
						"MULTIPLE" => "Y",
						"MODULE_ID" => "forum",
						"ALLOW_UPLOAD" => ($arParams["ALLOW_UPLOAD"] == "Y" ? "I" : $arParams["ALLOW_UPLOAD"]),
						"ALLOW_UPLOAD_EXT" => $arParams["ALLOW_UPLOAD_EXT"]
					)
				),
				"UPLOAD_FILE_PARAMS" => array("width" => $arParams["IMAGE_SIZE"], "height" => $arParams["IMAGE_SIZE"]),
				"PROPERTIES" => array(
					array_merge(is_array($arResult["USER_FIELDS"]["UF_FORUM_MESSAGE_DOC"]) ? $arResult["USER_FIELDS"]["UF_FORUM_MESSAGE_DOC"] : array(), (is_array($arParams["USER_FIELDS_SETTINGS"]["UF_FORUM_MESSAGE_DOC"]) ? $arParams["USER_FIELDS_SETTINGS"]["UF_FORUM_MESSAGE_DOC"] : array())),
					array_merge(is_array($arResult["USER_FIELDS"]["UF_FORUM_MES_URL_PRV"]) ? $arResult["USER_FIELDS"]["UF_FORUM_MES_URL_PRV"] : array(), (is_array($arParams["USER_FIELDS_SETTINGS"]["UF_FORUM_MES_URL_PRV"]) ? $arParams["USER_FIELDS_SETTINGS"]["UF_FORUM_MES_URL_PRV"] : array())),
				),
				"SMILES" => (
						$arParams["ALLOW_SMILES"] == "Y"
							? \COption::GetOptionInt("forum", "smile_gallery_id", 0) :
							array("VALUE" => array())
				),
				"HTML_BEFORE_TEXTAREA" => $APPLICATION->GetViewContent(implode('_', array($tplID, 'EDIT', 'BEFORE'))).$html_before_textarea,
				"HTML_AFTER_TEXTAREA" => $APPLICATION->GetViewContent(implode('_', array($tplID, 'EDIT', 'AFTER'))).$html_after_textarea
			),
			false,
			Array("HIDE_ICONS" => "Y")
		);
		?>
</form>
<script type="text/javascript">
BX.ready(function(){
	BX.addCustomEvent(BX('<?=$arParams["FORM_ID"]?>'), 'OnUCFormAfterShow', __fcOnUCFormAfterShow);
});
BX.message({
	FCCID : '<?=$arParams["mfi"]?>'
});
</script>
