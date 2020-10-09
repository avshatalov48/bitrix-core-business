<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arResult
 * @var array $arParams
 * @var CMain $APPLICATION
 * @var string $templateFolder
 */
$arParams['IMAGE_MAX_WIDTH'] = 600;
if (array_key_exists('SUCCESS_MESSAGE', $arResult))
{
?><script>
	window.bxIdeaId = top.bxIdeaId = '<?=$arResult["ID"]?>';
</script><?
	return;
}
else if($arResult["FATAL_MESSAGE_CODE"] == "NO_RIGHTS")
{
	unset($_POST["AJAX"], $_POST["ACTION"]);
	unset($_GET["AJAX"], $_GET["ACTION"]);
?>
<div class="blog-errors blog-note-box blog-note-error">
	<div class="blog-error-text">
		<?=$arResult["FATAL_MESSAGE"]?>
	</div>
</div>
<?$APPLICATION->IncludeComponent(
	"bitrix:system.auth.form",
	$arParams["AUTH_TEMPLATE"],
	Array(
		"REGISTER_URL" => $arParams["REGISTER_URL"],
		"FORGOT_PASSWORD_URL" => $arParams["FORGOT_PASSWORD_URL"],
		"PROFILE_URL" => "",
		"SHOW_ERRORS" => "N"
	),
	$component->__parent,
	array(
		"HIDE_ICONS" => "Y"
	)
);?>
<?
	return;
}
else if($arResult["FATAL_MESSAGE"] <> '')
{
?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["FATAL_MESSAGE"]?>
		</div>
	</div>
<?
	return;
}
elseif($arResult["UTIL_MESSAGE"] <> '')
{
?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["UTIL_MESSAGE"]?>
		</div>
	</div>
<?
	return;
}
// Frame with file input to ajax uploading in WYSIWYG editor dialog
?>
<form action="<?=POST_FORM_ACTION_URI?>" name="<?=$arResult['FORM_NAME']?>" id="<?=$arResult['FORM_NAME']?>" method="post" enctype="multipart/form-data">
<?=bitrix_sessid_post();?>
<? if($arParams["ALLOW_POST_CODE"])
{
	CUtil::InitJSCore(array('translit'));
	?><input type="hidden" name="USE_GOOGLE_CODE" value="<?=($arParams['USE_GOOGLE_CODE'] ? "Y" : "N")?>" /><?
	?><input maxlength="255" type="hidden" name="CODE" value="<?=$arResult["PostToShow"]["CODE"]?>" /><?
}
?>
<div class="blog-post-edit idea-post-edit-light">
<?
if($arResult["MESSAGE"] <> '')
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
<?
}
if($arResult["ERROR_MESSAGE"] <> '')
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["ERROR_MESSAGE"]?>
		</div>
	</div>
<?
}?>
<div>
	<div class="blog-post-fields blog-edit-fields">
		<div class="field-title-idea-title"><label for="POST_TITLE"><?=GetMessage("IDEA_TITLE_TITLE")?></label></div>
		<div class="blog-post-field blog-post-field-title blog-edit-field blog-edit-field-title">
			<input maxlength="255" size="70" tabindex="1" type="text" name="POST_TITLE" id="POST_TITLE" value="<?=$arResult["PostToShow"]["TITLE"]?>" />
		</div>
		<div class="blog-clear-float"></div>
		<div class="blog-post-field blog-post-field-date blog-edit-field blog-edit-field-post-date">
			<input type="hidden" id="DATE_PUBLISH_DEF" name="DATE_PUBLISH_DEF" value="<?=$arResult["PostToShow"]["DATE_PUBLISH"];?>">
			<div id="date-publ" style="display:none;">
				<?$APPLICATION->IncludeComponent(
					'bitrix:main.calendar',
					'.default',
					array(
						'SHOW_INPUT' => 'Y',
						'FORM_NAME' => $arResult['FORM_NAME'],
						'INPUT_NAME' => 'DATE_PUBLISH',
						'INPUT_VALUE' => $arResult["PostToShow"]["DATE_PUBLISH"],
						'SHOW_TIME' => 'Y'
					),
					null,
					array('HIDE_ICONS' => 'Y')
				);?>
			</div>
		</div>
		<div class="blog-clear-float"></div>
	</div>
	<div class="field-title-idea-text"><?=GetMessage("IDEA_DESCRIPTION_TITLE")?></div>
	<div class="blog-post-message blog-edit-editor-area blog-edit-field-text">
		<div class="blog-comment-field">
			<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/neweditor.php"); ?>
		</div>
		<div class="blog-post-field blog-post-field-images blog-edit-field" id="blog-post-image"><?
		if (!empty($arResult["Images"]))
		{
			?><div class="blog-field-title-images"><?=GetMessage("BLOG_P_IMAGES")?></div><?
			foreach($arResult["Images"] as $aImg)
			{
				?><div class="blog-post-image-item">
					<div class="blog-post-image-item-border">
						<label for="IMAGE_ID_title_<?=$aImg["ID"]?>"><? // do not do spaces between this nodes
							?><img src="<?=$aImg["PARAMS"]["SRC"]?>" id="<?=$aImg["ID"]?>" />
						</label></div>
					<div class="blog-post-image-item-text">
						<input id="IMAGE_ID_title_<?=$aImg["ID"]?>" name="IMAGE_ID_title[<?=$aImg["ID"]?>]" value="<?=$aImg["TITLE"]?>" title="<?=GetMessage("BLOG_BLOG_IN_IMAGES_TITLE")?>" />
					</div>
				<div class="blog-post-image-item-act">
						<input type="checkbox" name="IMAGE_ID_del[<?=$aImg["ID"]?>]" id="img_del_<?=$aImg["ID"]?>"><?
						?><label for="img_del_<?=$aImg["ID"]?>"><?=GetMessage("BLOG_DELETE")?></label>
					</div>
				</div><?
			}
		}
		?>
		</div>
	</div>
	<div class="blog-clear-float"></div>
	<div class="blog-post-field blog-post-field-category blog-edit-field blog-edit-field-tags">
		<div class="field-title-idea-tags"><label for="TAGS"><?=GetMessage("BLOG_CATEGORY")?></label></div>
		<?
		if(IsModuleInstalled("search"))
		{
			$arSParams = Array(
				"NAME" => "TAGS",
				"VALUE" => $arResult["PostToShow"]["CategoryText"],
				"arrFILTER" => "blog",
				"PAGE_ELEMENTS" => "10",
				"SORT_BY_CNT" => "Y",
				"TEXT" => 'size="30" tabindex="3"'
			);
			if($arResult["bSoNet"] && $arResult["bGroupMode"])
			{
				$arSParams["arrFILTER"] = "socialnetwork";
				$arSParams["arrFILTER_socialnetwork"] = $arParams["SOCNET_GROUP_ID"];
			}
			$APPLICATION->IncludeComponent("bitrix:search.tags.input", ".default", $arSParams, array('HIDE_ICONS' => 'Y'));
		}
		else
		{
			?><input type="text" id="TAGS" tabindex="3" name="TAGS" size="30" value="<?=$arResult["PostToShow"]["CategoryText"]?>" /><?
		}?>
	</div>
	<div class="blog-clear-float"></div>
	<?if($arResult["POST_PROPERTIES"]["UF_SHOW_BLOCK"]):?>
		<div class="blog-post-params">
			<div class="blog-post-field blog-post-field-user-prop blog-edit-field">
				<?foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):
					if ($arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"]===false)
						continue;
					?>
					<div class="field-title-idea-<?=$FIELD_NAME?>"><?=$arPostField["EDIT_FORM_LABEL"]?></div>
						<?$APPLICATION->IncludeComponent(
							"bitrix:system.field.edit",
							$arPostField["UF_TEMPLATE"],
							array(
								"arUserField" => $arPostField,
								"POST_BIND_USER" => $arParams["POST_BIND_USER"]
							),
							$component->__parent,
							array("HIDE_ICONS" => "Y"));
						?>
					</div>
					<? if($FIELD_NAME == "UF_CATEGORY_CODE"):?><br style="clear: both;" /><br/><? endif;?>
				<? endforeach; ?>
				<br style="clear: both;"/>
			</div>
			<div class="blog-clear-float"></div>
		</div>
	<?endif;?>
	<input type="hidden" name="save" value="Y">
</div>
</div>
</form>