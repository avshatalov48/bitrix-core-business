<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 */

if(strlen($arResult["FATAL_MESSAGE"]) > 0)
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
elseif(strlen($arResult["UTIL_MESSAGE"]) > 0)
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

?>
<div class="blog-post-edit">
<?
if(strlen($arResult["MESSAGE"]) > 0)
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
<?
}
if(strlen($arResult["ERROR_MESSAGE"]) > 0)
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["ERROR_MESSAGE"]?>
		</div>
	</div>
<?
}

if ($arResult["preview"] == "Y" && !empty($arResult["PostToShow"]) > 0)
{
$className = "blog-post";
$className .= " blog-post-first";
$className .= " blog-post-alt";
$className .= " blog-post-year-" . $arResult["postPreview"]["DATE_PUBLISH_Y"];
$className .= " blog-post-month-" . IntVal($arResult["postPreview"]["DATE_PUBLISH_M"]);
$className .= " blog-post-day-" . IntVal($arResult["postPreview"]["DATE_PUBLISH_D"]);
?>
	<p><b><?=GetMessage("BLOG_PREVIEW_TITLE")?></b></p>
	<div class="<?=$className?>">
		<h2 class="blog-post-title"><span><?=$arResult["postPreview"]["TITLE"]?></span></h2>
		<div class="blog-post-info-back blog-post-info-top">
			<div class="blog-post-info">
				<div class="blog-author"><div class="blog-author-icon"></div><?=$arResult["postPreview"]["AuthorName"]?></div>
				<div class="blog-post-date"><?
					?><span class="blog-post-day"><?=$arResult["postPreview"]["DATE_PUBLISH_DATE"]?></span><?
					?><span class="blog-post-time"><?=$arResult["postPreview"]["DATE_PUBLISH_TIME"]?></span><?
					?><span class="blog-post-date-formated"><?=$arResult["postPreview"]["DATE_PUBLISH_FORMATED"]?></span>
				</div>
			</div>
		</div>
		<div class="blog-post-content">
			<div class="blog-post-avatar"><?=$arResult["postPreview"]["BlogUser"]["AVATAR_img"]?></div>
			<?=$arResult["postPreview"]["textFormated"]?>
			<br style="clear: both;" />
		</div>
		<div class="blog-post-meta">
			<div class="blog-post-info-bottom">
				<div class="blog-post-info">
					<div class="blog-author"><div class="blog-author-icon"></div><?=$arResult["postPreview"]["AuthorName"]?></div>
					<div class="blog-post-date"><?
						?><span class="blog-post-day"><?=$arResult["postPreview"]["DATE_PUBLISH_DATE"]?></span><?
						?><span class="blog-post-time"><?=$arResult["postPreview"]["DATE_PUBLISH_TIME"]?></span><?
						?><span class="blog-post-date-formated"><?=$arResult["postPreview"]["DATE_PUBLISH_FORMATED"]?></span>
					</div>
				</div>
			</div>
			<div class="blog-post-meta-util">
				<span class="blog-post-views-link"><a href=""><?
					?><span class="blog-post-link-caption"><?=GetMessage("BLOG_VIEWS")?>:</span><?
					?><span class="blog-post-link-counter">0</span></a></span>
				<span class="blog-post-comments-link"><a href=""><?
					?><span class="blog-post-link-caption"><?=GetMessage("BLOG_COMMENTS")?>:</span><?
					?><span class="blog-post-link-counter">0</span></a></span>
			</div>

			<?if(!empty($arResult["postPreview"]["Category"]))
			{
				?>
				<div class="blog-post-tag">
					<span><?=GetMessage("BLOG_BLOG_BLOG_CATEGORY")?></span>
					<?
					$i = 0;
					foreach($arResult["postPreview"]["Category"] as $v)
					{
						if($i != 0)
							echo ",";
						?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
						$i++;
					}
					?>
				</div>
			<?
			}
			?>
		</div>
	</div>
<?
}
//dbg
//include_once(__DIR__. "/lhe.php");
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
<div class="blog-edit-form blog-edit-post-form blog-post-edit-form">
	<div class="blog-post-fields blog-edit-fields">
		<div class="blog-field-title-title"><label for="POST_TITLE"><?=GetMessage("IDEA_TITLE_TITLE")?></label></div>
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
						'FORM_NAME' => 'REPLIER',
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
	<div class="blog-field-title-idea"><?=GetMessage("IDEA_DESCRIPTION_TITLE")?></div>
	<div class="blog-post-message blog-edit-editor-area blog-edit-field-text">
		<div class="blog-comment-field blog-comment-field-bbcode">
			<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/neweditor.php"); ?>
<!--			--><?//initLHEForIdea('LHEIdeaId', $arResult, $arParams)?>
			<input type="hidden" name="USE_NEW_EDITOR" value="Y">
			<div style="width:0; height:0; overflow:hidden;"><input type="text" tabindex="3" onFocus="window.oBlogLHE.SetFocus()" name="hidden_focus"></div>
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
		<div class="blog-field-title-tags"><label for="TAGS"><?=GetMessage("BLOG_CATEGORY")?></label></div>
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
					if ($arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME]["UF_SHOW"] === false)
						continue;
					?>
					<div style="float:left; margin-right:20px;"><?=$arPostField["EDIT_FORM_LABEL"]?>:
						<?$APPLICATION->IncludeComponent(
							"bitrix:system.field.edit",
							$arPostField["UF_TEMPLATE"],
							array(
								"arUserField" => $arPostField,
								"POST_BIND_USER" => $arParams["POST_BIND_USER"]
							),
							$component,
							array("HIDE_ICONS" => "Y"));
						?>
					</div>
					<? if($FIELD_NAME == "UF_CATEGORY_CODE"):?><br style="clear: both;" /><br/><? endif;?>
				<? endforeach;?>
				<br style="clear: both;" />
			</div>
			<div class="blog-clear-float"></div>
		</div>
	<? endif;?>
	<input type="hidden" name="save" value="Y">
	<div class="idea-add-comment">
		<a class="idea-add-button" onclick="this.disabled=true;BX.submit(BX('<?=$arResult['FORM_NAME']?>'));" onmouseup="BX.removeClass(this,'feed-add-button-press')" onmousedown="BX.addClass(this, 'feed-add-button-press')" href="javascript:void(0)">
			<span class="l"></span><span class="t"><?=GetMessage("IDEA_ADD_IDEA_BUTTON_TITLE")?></span><span class="r"></span>
		</a>
	</div>
</div>
</form>
</div>