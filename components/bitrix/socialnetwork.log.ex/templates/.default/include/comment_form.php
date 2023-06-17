<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$component = $this->getComponent();

$arParams["ALLOW_VIDEO"] = (($arParams["ALLOW_VIDEO"] ?? '') === "Y" ? "Y" : "N");

if (is_array($arResult["Smiles"]))
{
	$arSmiles = [];
	foreach($arResult["Smiles"] as $arSmile)
	{
		$arSmiles[] = [
			'name' => $arSmile["NAME"],
			'path' => $arSmile["IMAGE"],
			'code' => str_replace("\\\\","\\",$arSmile["TYPE"]),
			'codes' => str_replace("\\\\","\\",$arSmile["TYPING"]),
			'width' => $arSmile["IMAGE_WIDTH"],
			'height' => $arSmile["IMAGE_HEIGHT"],
		];
	}
	$smiles = [ "VALUE" => $arSmiles ];
}
else
{
	$smiles = (int)$arResult["Smiles"];
}

if (
	\Bitrix\Main\ModuleManager::isModuleInstalled('tasks')
	&& class_exists("Bitrix\\Tasks\\Internals\\Task\\Result\\ResultManager")
)
{
	$APPLICATION->IncludeComponent(
		'bitrix:tasks.widget.result.field',
		'',
		[
			'HIDDEN' => 'Y',
		],
		$this->getComponent(),
		[
			'HIDE_ICONS' => 'Y',
		]
	);
}

$formParams = [
	"FORM_ID" => $arParams["FORM_ID"],
	"SHOW_MORE" => "Y",
	"PARSER" => [
		"Bold", "Italic", "Underline", "Strike", "ForeColor",
		"FontList", "FontSizeList", "RemoveFormat", "Quote",
		"Code", "CreateLink",
		"Image", "UploadFile",
		"InputVideo",
		"Table", "Justify", "InsertOrderedList",
		"InsertUnorderedList",
		"Source", "MentionUser", "Spoiler"
	],
	"BUTTONS" => [
		(
			(
				in_array("UF_SONET_COM_FILE", $arParams["COMMENT_PROPERTY"])
				|| in_array("UF_SONET_COM_DOC", $arParams["COMMENT_PROPERTY"])
			)
				? "UploadFile"
				: ""
		),
		"CreateLink",
		"InputVideo",
		"Quote", "MentionUser"
	],
	"TEXT" => [
		"NAME" => "comment",
		"VALUE" => "",
		"HEIGHT" => "80px"
	],
	"UPLOAD_FILE" => (
		isset($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_DOC"])
			? false
			: (
				is_array($arResult["COMMENT_PROPERTIES"]["DATA"])
					? $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_FILE"]
					: false
			)
	),
	"UPLOAD_WEBDAV_ELEMENT" => $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_DOC"],
	"UPLOAD_FILE_PARAMS" => [
		"width" => 400,
		"height" => 400
	],
	"FILES" => [
		"VALUE" => [],
		"DEL_LINK" => $arResult["urlToDelImage"] ?? '',
		"SHOW" => "N"
	],
	"SMILES" => $smiles,
	"LHE" => [
		"id" => "id".$arParams["FORM_ID"],
		"documentCSS" => "body {color:#434343;}",
		"iframeCss" => "html body {padding-left: 14px!important; line-height: 18px!important;}",
//		"ctrlEnterHandler" => "__logSubmitCommentForm".$arParams["UID"],
		"fontSize" => "14px",
		"bInitByJS" => true,
		"height" => 80
	],
	"PROPERTIES" => [
		array_merge(
			(
				isset($arResult["COMMENT_PROPERTIES"])
				&& isset($arResult["COMMENT_PROPERTIES"]["DATA"])
				&& isset($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_URL_PRV"])
				&& is_array($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_URL_PRV"])
					? $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_URL_PRV"]
					: []
			),
			[ 'ELEMENT_ID' => 'url_preview_'.$arParams["FORM_ID"] ]
		)
	],
	"SELECTOR_VERSION" => 2,
	"DISABLE_CREATING_FILE_BY_CLOUD" => ($arParams["PUBLIC_MODE"] === "Y")
];

?><div style="display: none;">
<form action="" id="<?=$arParams["FORM_ID"]?>" name="<?=$arParams["FORM_ID"]?>" method="POST" enctype="multipart/form-data" target="_self" class="comments-form">
	<?=bitrix_sessid_post();?>
	<input type="hidden" name="sonet_log_comment_logid" id="sonet_log_comment_logid" value="">
	<?php
	$APPLICATION->IncludeComponent(
		"bitrix:main.post.form",
		".default",
		$formParams,
		false,
		array(
			"HIDE_ICONS" => "Y"
		)
	);
	?>
	<input type="hidden" name="cuid" id="upload-cid" value="" />
</form>
</div>
<script>
	BX.ready(function(){
/*
		window["__logSubmitCommentForm<?=$arParams["UID"]?>"] = function ()
		{
			if (!!window["UC"]["f<?=$arParams["FORM_ID"]?>"] && !!window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode)
			{
				BX.onCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnButtonClick', ['submit']);
			}
			return false;
		};
*/
		if (!!window["FCForm"])
		{
/*
			window["UC"]["f<?=$arParams["FORM_ID"]?>"] = new FCForm({
				entitiesId : <?=CUtil::PhpToJSObject($component->arResult["ENTITIES_XML_ID"] ?? [])?>,
				formId : '<?=$arParams["FORM_ID"]?>',
				editorId : 'id<?=$arParams["FORM_ID"]?>'});

			window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"] = <?=CUtil::PhpToJSObject($component->arResult["ENTITIES_CORRESPONDENCE"])?>;

			window.__logGetNextPageFormName = "f<?=$arParams["FORM_ID"]?>";

			if (!!window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode)
			{
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormAfterShow', BX.Livefeed.CommentForm.onAfterShow.bind(BX.Livefeed.CommentForm));
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormSubmit', BX.Livefeed.CommentForm.onSubmit);
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormResponse', BX.Livefeed.CommentForm.onResponse);
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormInit', BX.Livefeed.CommentForm.onInit);
			}

			BX.addCustomEvent(window, "OnBeforeSocialnetworkCommentShowedUp", function(entity) {
				if (entity != 'socialnetwork')
				{
					window["UC"]["f<?=$arParams["FORM_ID"]?>"].hide(true);
				}
			});
*/
			BX.addCustomEvent(BX('<?= $arParams["FORM_ID"]?>'), 'OnUCFormAfterShow', BX.Livefeed.CommentForm.onAfterShow.bind(BX.Livefeed.CommentForm));



/*
			BX.addCustomEvent(window, 'OnUCAddEntitiesCorrespondence', function(key, arValue)
			{
				window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"][key] = arValue;
			});

			BX.addCustomEvent(window, 'OnUCAfterRecordAdd', function(id, data, responce_data)
			{
				if (typeof responce_data.arComment != 'undefined')
				{
					window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"][id + '-' + data.messageId[1]] = [responce_data.arComment.LOG_ID, responce_data.arComment.ID];
				}
			});

			BX.addCustomEvent(window, 'OnUCBeforeCommentWillBePulled', function(arId, data)
			{
				if (typeof data.SONET_FULL_ID != 'undefined')
				{
					window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"][arId.join('-')] = [data.SONET_FULL_ID[0], data.SONET_FULL_ID[1]];
				}
			});
*/
			BX.addCustomEvent(window, 'OnUCFeedChanged', function(data)
			{
				BX.LazyLoad.showImages(true);
			});

			window.SLEC = {
				form : BX('<?=$formParams["FORM_ID"]?>'),
				actionUrl : '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=<?=str_replace("%23", "#", urlencode(($arResult["urlToPost"] ?? '')))?>',
				editorId : '<?=$formParams["LHE"]["id"]?>',
				jsMPFName : 'PlEditor<?=$formParams["FORM_ID"]?>',
				formKey : 'f<?=$formParams["FORM_ID"]?>'
			};
		}
	});
</script>