<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!$arResult["CanUserComment"])
	return;

//$rand = randString(4);	// may use rand in form id
$formParams = Array(
	"FORM_ID" => $component->createPostFormId(),
	"SHOW_MORE" => "Y",
	"PARSER" => blogTextParser::GetEditorToolbar(array('blog' => $arResult['Blog'])),
	"BUTTONS" => blogTextParser::getEditorButtons($arResult['Blog'], $arResult),
	"TEXT" => Array(
		"ID" => "POST_MESSAGE",
		"NAME" => "comment",
		"VALUE" => "",
		"HEIGHT" => "80px"
	),
//dbg: comments field
//	"UPLOAD_FILE" => !empty($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]) ? false :
//		$arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_DOC"],
//	"UPLOAD_WEBDAV_ELEMENT" => $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"],
	"UPLOAD_FILE_PARAMS" => array('width' => $arParams["IMAGE_MAX_WIDTH"], 'height' => $arParams["IMAGE_MAX_HEIGHT"]),
	"SMILES" => COption::GetOptionInt("blog", "smile_gallery_id", 0),
	"LHE" => array(
		'id' => $component->createEditorId(),
		"documentCSS" => "body {color:#434343;}",
		'ctrlEnterHandler' => 'blogCommentCtrlEnterHandler',
		"fontFamily" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
		"fontSize" => "12px",
		"bInitByJS" => true,
		"height" => 80,
		'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'],
		'bResizable' => true,
		'bAutoResize' => true,
		'setFocusAfterShow' => false
	),
	"IS_BLOG" => true,
	"PROPERTIES" => array(
		array_merge(
			(is_array($arResult["COMMENT_PROPERTIES"]["DATA"][CBlogComment::UF_NAME]) ? $arResult["COMMENT_PROPERTIES"]["DATA"][CBlogComment::UF_NAME] : array()),
			(is_array($_POST[CBlogComment::UF_NAME]) ? array("VALUE" => $_POST[CBlogComment::UF_NAME]) : array()),
			array("POSTFIX" => "file")
		),
		is_array($arResult["COMMENT_PROPERTIES"]["DATA"]) &&
		array_key_exists("UF_BLOG_POST_URL_PRV", $arResult["COMMENT_PROPERTIES"]["DATA"]) ?
			array_merge(
				$arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_POST_URL_PRV"],
				array(
					'ELEMENT_ID' => 'url_preview_'.$component->createEditorId(),
					'STYLE' => 'margin: 0 18px'
				)
			)
			:
			array()
	),
//	"DISABLE_LOCAL_EDIT" => $arParams["bPublicPage"]
);
//===WebDav===
//dbg: webdav?
if(!array_key_exists("USER", $GLOBALS) || !$GLOBALS["USER"]->IsAuthorized())
{
	unset($formParams["UPLOAD_WEBDAV_ELEMENT"]);
	foreach($formParams["BUTTONS"] as $keyT => $valT)
	{
		if($valT == "UploadFile")
		{
			unset($formParams["BUTTONS"][$keyT]);
		}
	}
}
//===WebDav===

//USER CONSENT, if needed;
if ($arParams['USER_CONSENT'] == 'Y' && (empty($arResult["User"]) || !$arParams['USER_CONSENT_WAS_GIVEN']))
{
	ob_start();
//	userconsent only for unregistered users or once for registered early
	$APPLICATION->IncludeComponent(
		"bitrix:main.userconsent.request",
		"",
		array(
			"ID" => $arParams["USER_CONSENT_ID"],
			"IS_CHECKED" => $arParams["USER_CONSENT_IS_CHECKED"],
			"AUTO_SAVE" => "Y",
			"IS_LOADED" => $arParams["USER_CONSENT_IS_LOADED"],
			"ORIGIN_ID" => "sender/sub",
			"ORIGINATOR_ID" => "",
			"REPLACE" => array(
				'button_caption' => GetMessage("B_B_MS_SEND"),
				'fields' => array(GetMessage("B_B_MS_NAME"), 'E-mail')
			),
			"SUBMIT_EVENT_NAME" => "OnUCFormCheckConsent"
		)
	);
	$formParams["AT_THE_END_HTML"] = ob_get_clean();
}



if($arResult["use_captcha"]===true)
{
	ob_start();
	?>
	<div class="blog-comment-field blog-comment-field-captcha">
		<div class="blog-comment-field-captcha-label">
			<label for="captcha_word"><?=GetMessage("B_B_MS_CAPTCHA_SYM")?></label><span class="blog-required-field">*</span><br>
			<input type="hidden" name="captcha_code" id="captcha_code" value="">
			<input type="text" size="30" name="captcha_word" id="captcha_word" value=""  tabindex="7">
		</div>
		<div class="blog-comment-field-captcha-image">
			<div id="div_captcha">
				<img src="" width="180" height="40" id="captcha" style="display:none;">
			</div>
		</div>
	</div>
	<?
	$formParams["AT_THE_END_HTML"] .= ob_get_clean();
}

?>
<div style="display:none;">
	<form action=<?=POST_FORM_ACTION_URI?> id="<?=$component->createPostFormId()?>" name="<?=$component->createPostFormId()?>" <?
		?>method="POST" enctype="multipart/form-data" class="comments-form">
		<input type="hidden" name="comment_post_id" id="postId" value="" />
		<input type="hidden" name="log_id" id="logId" value="" />
		<input type="hidden" name="parentId" id="parentId" value="" />
		<input type="hidden" name="edit_id" id="edit_id" value="" />
		<input type="hidden" name="act" id="act" value="add" />
		<input type="hidden" name="as" id="as" value="<?=$arParams['AVATAR_SIZE_COMMENT']?>" />
		<input type="hidden" name="post" id="" value="Y" />
		<input type="hidden" name="blog_upload_cid" id="upload-cid" value="" />
		<?=bitrix_sessid_post();?>
<?
if(empty($arResult["User"]))
{
?>
	<div class="blog-comment-field blog-comment-field-user">
		<div class="blog-comment-field blog-comment-field-author"><div class="blog-comment-field-text"><?
			?><label for="user_name"><?=GetMessage("B_B_MS_NAME")?></label><?
			?><span class="blog-required-field">*</span></div><span><?
			?><input maxlength="255" size="30" tabindex="3" type="text" name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>"></span></div>
		<div class="blog-comment-field-user-sep">&nbsp;</div>
		<div class="blog-comment-field blog-comment-field-email"><div class="blog-comment-field-text"><label for="">E-mail</label></div><span><input maxlength="255" size="30" tabindex="4" type="text" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>"></span></div>
		<div class="blog-clear-float"></div>
	</div>
<?
}
?>
	<div id="blog-post-autosave-hidden" <?/*?>style="display:none;"<?*/?>></div>
	<?$APPLICATION->IncludeComponent("bitrix:main.post.form", "", $formParams, false, Array("HIDE_ICONS" => "Y"));?>
</form>
</div>
<script>
BX.ready(function(){
	window["UC"] = (!!window["UC"] ? window["UC"] : {});
	window["UC"]["f<?=$component->createPostFormId()?>"] = new FCForm({
		entitiesId : {},
		formId : '<?=$component->createPostFormId()?>',
		editorId : '<?=$component->createEditorId()?>',
		editorName : ''
	});

	if (!!window["UC"]["f<?=$component->createPostFormId()?>"].eventNode)
	{
		BX.addCustomEvent(window["UC"]["f<?=$component->createPostFormId()?>"].eventNode, 'OnUCFormAfterShow', __blogOnUCFormAfterShow);
		BX.addCustomEvent(window["UC"]["f<?=$component->createPostFormId()?>"].eventNode, 'OnClickBeforeSubmit', __blogOnClickBeforeSubmit);
		BX.addCustomEvent(window["UC"]["f<?=$component->createPostFormId()?>"].eventNode, 'OnUCFormSubmit', __blogOnUCFormSubmit);
	}
	BX.addCustomEvent(window, 'OnUCAfterRecordAdd', __blogOnUCAfterRecordAdd);
});
</script>