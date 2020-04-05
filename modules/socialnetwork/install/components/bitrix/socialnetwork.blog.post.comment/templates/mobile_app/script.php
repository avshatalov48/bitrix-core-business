<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 */
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
	<form action="/bitrix/urlrewrite.php" <?
		?>id="<?=$this->__component->__name?>" <?
		?>name="<?=$this->__component->__name?>" <?
		?>method="POST" enctype="multipart/form-data" class="comments-form">
		<input type="hidden" name="comment_post_id" id="postId" value="" />
		<input type="hidden" name="log_id" id="logId" value="" />
		<input type="hidden" name="parentId" id="parentId" value="" />
		<input type="hidden" name="edit_id" id="edit_id" value="" />
		<input type="hidden" name="act" id="act" value="add" />
		<input type="hidden" name="as" id="as" value="<?=$arParams['AVATAR_SIZE_COMMENT']?>" />
		<input type="hidden" name="post" id="" value="Y" />
		<input type="hidden" name="blog_upload_cid" id="upload-cid" value="" />
		<input type="hidden" name="decode" value="Y" />

	</form>
<?
$APPLICATION->IncludeComponent("bitrix:main.post.form",
	".default",
	array(
		"FORM_ID" => $this->__component->__name,
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
			"NAME" => "comment",
			"VALUE" => "",
		),
		"DESTINATION" => array(
			"VALUE" => $arResult["FEED_DESTINATION"],
			"SHOW" => "N",
		),
		"UPLOADS" => array(
			$arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"],
			$arResult["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_DOC"],

		),
		"SMILES" => array("VALUE" => $arSmiles)
	),
	false,
	array("HIDE_ICONS" => "Y")
);
