<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */
foreach (GetModuleEvents('forum', 'OnCommentFormDisplay', true) as $arEvent)
{
	$arExt = ExecuteModuleEventEx($arEvent);
	if ($arExt !== null)
	{
		foreach($arExt as $arTpl)
			$APPLICATION->AddViewContent(implode('_', array($tplID, 'EDIT', $arTpl['DISPLAY'])), $arTpl['TEXT'], $arTpl['SORT']);
	}
}
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

$arSmiles = array();
if(!empty($arResult["Smiles"]))
{
	foreach($arResult["Smiles"] as $arSmile)
	{
		$arSmiles[] = array(
			'name' => $arSmile["~LANG_NAME"],
			'path' => "/bitrix/images/blog/smile/".$arSmile["IMAGE"],
			'code' => str_replace("\\\\","\\",$arSmile["TYPE"]),
			'codes' => str_replace("\\\\","\\",$arSmile["TYPING"]),
			'width' => $arSmile["IMAGE_WIDTH"],
			'height' => $arSmile["IMAGE_HEIGHT"],
		);
	}
}
?>
	<form action="<?=POST_FORM_ACTION_URI?>" <?
		?>id="<?=$arParams["FORM_ID"]?>" <?
		?>name="<?=$arParams["FORM_ID"]?>" <?
		?>method="POST" enctype="multipart/form-data" class="comments-form">
		<input type="hidden" name="back_page" value="<?=$arResult["CURRENT_PAGE"]?>" />
		<input type="hidden" name="ENTITY_XML_ID" value="<?=$arParams["ENTITY_XML_ID"]?>" />
		<input type="hidden" name="ENTITY_TYPE" value="<?=$arParams["ENTITY_TYPE"]?>" />
		<input type="hidden" name="ENTITY_ID" value="<?=$arParams["ENTITY_ID"]?>" />
		<input type="hidden" name="REVIEW_USE_SMILES" value="Y"  />
		<input type="hidden" name="comment_review" value="Y"  />
	</form>
<?
$APPLICATION->IncludeComponent("bitrix:main.post.form",
	"",
	array(
		"FORM_ID" => $arParams["FORM_ID"],
		"PARSER" => array(
			"Bold", "Italic", "Underline", "Strike", "ForeColor",
			"FontList", "FontSizeList", "RemoveFormat", "Quote",
			"Code", ((!$arResult["NoCommentUrl"]) ? 'CreateLink' : ''),
			"Image", (($arResult["allowImageUpload"] == "Y") ? 'UploadImage' : ''),
			(($arResult["allowVideo"] == "Y") ? "InputVideo" : ""),
			"Table", "Justify", "InsertOrderedList",
			"InsertUnorderedList",
			"MentionUser", "SmileList", "Source"),
		"TEXT" => array(
			"ID" => "REVIEW_TEXT",
			"NAME" => "REVIEW_TEXT",
			"VALUE" => isset($arResult["REVIEW_TEXT"]) ? $arResult["REVIEW_TEXT"] : "",
		),
		"DESTINATION" => array(
			"VALUE" => $arResult["FEED_DESTINATION"],
			"SHOW" => "N",
		),
		"UPLOADS" => array(
			(isset($arResult["USER_FIELDS"]["UF_FORUM_MESSAGE_DOC"]) || $arParams["ALLOW_UPLOAD"]=="N" ? false :
			array(
				"TAG" => "FILE ID",
				"INPUT_NAME" => 'FILE_NEW',
				"INPUT_VALUE" => array(),
				"MAX_FILE_SIZE" => COption::GetOptionString("forum", "file_max_size", 5242880),
				"MULTIPLE" => "Y",
				"MODULE_ID" => "forum",
				"ALLOW_UPLOAD" => ($arParams["ALLOW_UPLOAD"] == "Y" ? "I" : $arParams["ALLOW_UPLOAD"]),
				"ALLOW_UPLOAD_EXT" => $arParams["ALLOW_UPLOAD_EXT"]
			)),

			array_merge((is_array($arResult["USER_FIELDS"]["UF_FORUM_MESSAGE_DOC"]) ? $arResult["USER_FIELDS"]["UF_FORUM_MESSAGE_DOC"] : array()), (is_array($arParams["USER_FIELDS_SETTINGS"]["UF_FORUM_MESSAGE_DOC"]) ? $arParams["USER_FIELDS_SETTINGS"]["UF_FORUM_MESSAGE_DOC"] : array())),
		),
		"SMILES" => array("VALUE" => $arSmiles),
		"HTML_BEFORE_TEXTAREA" => $APPLICATION->GetViewContent(implode('_', array($tplID, 'EDIT', 'BEFORE'))).$html_before_textarea,
		"HTML_AFTER_TEXTAREA" => $APPLICATION->GetViewContent(implode('_', array($tplID, 'EDIT', 'AFTER'))).$html_after_textarea
	),
	false,
	array("HIDE_ICONS" => "Y")
);
