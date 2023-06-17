<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 */

if (!$arResult["CanUserComment"])
{
	return;
}

$formID = $arResult['FORM_ID'];

$formParams = [
	"FORM_ID" => $formID,
	"SHOW_MORE" => "Y",
	"PARSER" => Array(
		"Bold", "Italic", "Underline", "Strike", "ForeColor",
		"FontList", "FontSizeList", "RemoveFormat", "Quote",
		"Code", (!($arResult['NoCommentUrl'] ?? '') ? 'CreateLink' : ''),
		"Image",
		(($arResult['allowImageUpload'] ?? null) === 'Y' ? 'UploadImage' : 'UploadFile'),
		($arResult['allowVideo'] === 'Y' ? 'InputVideo' : ''),
		"Table", "Justify", "InsertOrderedList",
		"InsertUnorderedList",
		"MentionUser", "Spoiler", "SmileList", "Source"
	),
	"BUTTONS" => [
		(
			in_array("UF_BLOG_COMMENT_FILE", $arParams["COMMENT_PROPERTY"], true)
			|| in_array("UF_BLOG_COMMENT_DOC", $arParams["COMMENT_PROPERTY"], true)
				? "UploadFile"
				: ""
		),
		(!($arResult["NoCommentUrl"] ?? '') ? 'CreateLink' : ''),
		($arResult['allowVideo'] === 'Y' ? 'InputVideo' : ''),
		//(($arResult["allowImageUpload"] == "Y") ? 'UploadImage' : ''),
		"Quote",
		/*, "BlogTag"*/
		(!$arParams["bPublicPage"] ? "MentionUser" : ""),
		(
			in_array("UF_BLOG_COMMENT_FILE", $arParams["COMMENT_PROPERTY"], true)
			|| in_array("UF_BLOG_COMMENT_DOC", $arParams["COMMENT_PROPERTY"], true)
				? "VideoMessage"
				: ""
		),
	],
	"BUTTONS_HTML" => Array("VideoMessage" => '<span class="feed-add-post-form-but-cnt feed-add-videomessage" onclick="BX.VideoRecorder.start(\''.$formID.'\', \'comment\');">'.GetMessage('BLOG_VIDEO_RECORD_BUTTON').'</span>'),
	"TEXT" => Array(
		"NAME" => "comment",
		"VALUE" => "",
		"HEIGHT" => "80px"
	),
	"DESTINATION" => Array(
		"VALUE" => (!$arParams["bPublicPage"] ? ($arResult["FEED_DESTINATION"] ?? []) : []),
		"SHOW" => "N",
		"USE_CLIENT_DATABASE" => ($arParams["bPublicPage"] ? "N" : "Y")
	),
	"UPLOAD_FILE" => !empty($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]) ? false :
		$arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_DOC"],
		"UPLOAD_WEBDAV_ELEMENT" => $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"],
	"UPLOAD_FILE_PARAMS" => array("width" => 400, "height" => 400),
	"FILES" => Array(
		"VALUE" => array(),
		"DEL_LINK" => $arResult["urlToDelImage"] ?? '',
		"SHOW" => "N",
		"POSTFIX" => "file"
	),
	"SMILES" => COption::GetOptionInt("blog", "smile_gallery_id", 0),
	"LHE" => array(
		"documentCSS" => "body {color:#434343;}",
		"iframeCss" => "html body {padding-left: 14px!important; line-height: 18px!important;}",
//		"ctrlEnterHandler" => "__submit" . $arResult['FORM_ID'],
		"id" => "idLHE_blogCommentForm" . $arResult['FORM_ID'],
		"fontSize" => "14px",
		"bInitByJS" => true,
		"height" => 80
	),
	"IS_BLOG" => true,
	"PROPERTIES" => array(
		array_merge(
			(
				isset($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMM_URL_PRV"])
				&& is_array($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMM_URL_PRV"])
					? $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMM_URL_PRV"]
					: []
			),
			array('ELEMENT_ID' => 'url_preview_' . $arResult['FORM_ID'])
		)
	),
	"DISABLE_LOCAL_EDIT" => $arParams["bPublicPage"],
	"SELECTOR_VERSION" => $arResult["SELECTOR_VERSION"],
	"DISABLE_CREATING_FILE_BY_CLOUD" => $arParams["bPublicPage"],
	'MENTION_ENTITIES' => [
		[
			'id' => 'user',
			'options' => [
				'emailUsers' => true,
				'inviteEmployeeLink' => false,
			],
			'itemOptions' => [
				'default' => [
					'link' => '',
					'linkTitle' => '',
				],
			],
		],
		[
			'id' => 'department',
			'options' => [
				'selectMode' => 'usersAndDepartments',
			],
		],
		[
			'id' => 'project',
			'itemOptions' => [
				'default' => [
					'link' => '',
					'linkTitle' => '',
				],
			],
		],
	],
];
//===WebDav===
if(!array_key_exists("USER", $GLOBALS) || !$GLOBALS["USER"]->IsAuthorized())
{
	unset($formParams["UPLOAD_WEBDAV_ELEMENT"]);
	foreach($formParams["BUTTONS"] as $keyT => $valT)
	{
		if($valT === "UploadFile" || $valT === "VideoMessage")
		{
			unset($formParams["BUTTONS"][$keyT]);
		}
	}
}
//===WebDav===

__sbpc_bind_post_to_form(($tmp1 = null), $formParams["FORM_ID"], ($tmp2 = null));
?>
<div style="display:none;">
	<form action="/bitrix/urlrewrite.php" <?php
		?>id="<?=$formParams["FORM_ID"]?>" name="<?=$formParams["FORM_ID"]?>" <?php
		?>method="POST" enctype="multipart/form-data" class="comments-form">
		<input type="hidden" name='ENTITY_XML_ID' value="<?= $arParams['ENTITY_XML_ID'] ?>" />


		<input type="hidden" name="comment_post_id" id="postId" value="" />
		<input type="hidden" name="log_id" id="logId" value="" />
		<input type="hidden" name="parentId" id="parentId" value="" />
		<input type="hidden" name="edit_id" id="edit_id" value="" />
		<input type="hidden" name="act" id="act" value="add" />
		<input type="hidden" name="as" id="as" value="<?=$arParams['AVATAR_SIZE_COMMENT']?>" />
		<input type="hidden" name="post" id="" value="Y" />
		<input type="hidden" name="blog_upload_cid" id="upload-cid" value="" />
		<?= bitrix_sessid_post() ?>
		<?php
if(empty($arResult["User"]))
{
?>
	<div class="blog-comment-field blog-comment-field-user">
		<div class="blog-comment-field blog-comment-field-author"><div class="blog-comment-field-text"><?php
			?><label for="user_name"><?=GetMessage("B_B_MS_NAME")?></label><?php
			?><span class="blog-required-field">*</span></div><span><?php
			?><input maxlength="255" size="30" tabindex="3" type="text" name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>"></span></div>
		<div class="blog-comment-field-user-sep">&nbsp;</div>
		<div class="blog-comment-field blog-comment-field-email"><div class="blog-comment-field-text"><label for="">E-mail</label></div><span><input maxlength="255" size="30" tabindex="4" type="text" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>"></span></div>
		<div class="blog-clear-float"></div>
	</div>
	<?php
}
?>
	<div id="blog-post-autosave-hidden" <?php /*?>style="display:none;"<?*/?>></div>
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.post.form',
		'',
		$formParams,
		false,
		[ 'HIDE_ICONS' => 'Y' ]
	);
	?>
		<?php
if($arResult["use_captcha"]===true)
{
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
	<?php
}
?>
</form>
</div>
<script>
BX.ready(function(){
	window["UC"] = (!!window["UC"] ? window["UC"] : {});
/*
	window["UC"]["f<?=$formParams["FORM_ID"]?>"] = new FCForm({
		entitiesId : {},
		formId : '<?=$formParams["FORM_ID"]?>',
		editorId : '<?=$formParams["LHE"]["id"]?>',
		editorName : '<?=$formParams["LHE"]["jsObjName"] ?? ''?>'
	});

	window["__submit<?= $arResult['FORM_ID'] ?>"] = function ()
	{
		if (!!window["UC"]["f<?=$formParams["FORM_ID"]?>"] && !!window["UC"]["f<?=$formParams["FORM_ID"]?>"].eventNode)
		{
			BX.onCustomEvent(window["UC"]["f<?=$formParams["FORM_ID"]?>"].eventNode, 'OnButtonClick', ['submit']);
		}
		return false;
	}

	if (!!window["UC"]["f<?=$formParams["FORM_ID"]?>"].eventNode)
	{
		BX.addCustomEvent(window["UC"]["f<?=$formParams["FORM_ID"]?>"].eventNode, 'OnUCFormAfterShow', __blogOnUCFormAfterShow);
		BX.addCustomEvent(window["UC"]["f<?=$formParams["FORM_ID"]?>"].eventNode, 'OnUCFormSubmit', __blogOnUCFormSubmit);
	}
	BX.addCustomEvent(window, "OnBeforeSocialnetworkCommentShowedUp", function(entity){ if (entity == 'socialnetwork') { window["UC"]["f<?=$formParams["FORM_ID"]?>"].hide(true); } } );
*/
	BX.addCustomEvent(BX('<?= $formParams["FORM_ID"] ?>'), 'OnUCFormAfterShow', __blogOnUCFormAfterShow);

	window["SBPC"] = {
		form : BX('<?=$formParams["FORM_ID"]?>'),
		actionUrl : '<?=(
			$arParams["SEF"] === "Y"
				? '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL='.str_replace("%23", "#", urlencode($arResult["urlToPost"]))
				: CUtil::JSEscape($arResult["urlToPost"])
		)?>',
		editorId : '<?=$formParams["LHE"]["id"]?>',

		jsMPFName : 'PlEditor<?=$formParams["FORM_ID"]?>'
	};
});
</script>
