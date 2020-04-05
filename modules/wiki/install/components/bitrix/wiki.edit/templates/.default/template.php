<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
?>
<script>
	BX.message({
		'FILE_NAME': '<?=GetMessageJS('FILE_NAME')?>',
		'CATEGORY_NAME': '<?=GetMessageJS('CATEGORY_NAME')?>',
		'WIKI_INSERT_IMAGE': '<?=GetMessageJS('WIKI_INSERT_IMAGE')?>',
		'WIKI_BUTTON_INSERT': '<?=GetMessageJS('WIKI_BUTTON_INSERT')?>',
		'WIKI_INSERT_CATEGORY': '<?=GetMessageJS('WIKI_INSERT_CATEGORY')?>',
		'WIKI_INSERT_HYPERLINK': '<?=GetMessageJS('WIKI_INSERT_HYPERLINK')?>',
		'WIKI_INSERT_EXTERANL_HYPERLINK': '<?=GetMessageJS('WIKI_INSERT_EXTERANL_HYPERLINK')?>',
		'WIKI_IMAGE_UPLOAD': '<?=GetMessageJS('WIKI_IMAGE_UPLOAD')?>',
		'WIKI_SAVE': '<?=GetMessageJS('WIKI_SAVE')?>'
	});

	window.wikiMainEditor = new BXWikiEditor({
		elementId: 'wiki-editor',
		wikiTextHtmlInit: <?=($arResult['ELEMENT']['DETAIL_TEXT_TYPE'] == 'html' && $arResult['ALLOW_HTML'] == 'Y' ? 'true' : 'false')?>,
		editUrl: "<?=$arResult['PATH_TO_POST_EDIT']?>",
		editorId: 'pLEditorWiki',
		charset: '<?=LANG_CHARSET?>',
		maxImageWidth: <?=COption::GetOptionInt("wiki", "image_max_width", 600)?>
	});
	<?
	foreach($arResult['IMAGES'] as $aImg)
	{
		?>wikiMainEditor.arWikiImg[<?=$aImg['ID']?>] = '<?=CUtil::JSEscape($aImg['ORIGINAL_NAME'])?>';<?
	}
	?>
</script>
<?
if(strlen($arResult['ERROR_MESSAGE'])>0 && $arResult['WIKI_oper'] != 'delete'):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['ERROR_MESSAGE']?>
		</div>
	</div>
	<?
endif;
if(strlen($arResult['FATAL_MESSAGE'])>0):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	include($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/dialogs_content.php');
	?>
	<div id="wiki-post">
		<div id="wiki-post-content">
			<div class="wiki-post-title">
				<div class="wiki-post-title-text">
				<h1><?=htmlspecialcharsbx($arResult['ELEMENT']['NAME_LOCALIZE'], ENT_QUOTES)?></h1>
				</div>
			</div>
			<?
			if($arResult['PREVIEW'] == 'Y' && !empty($arResult['ELEMENT_PREVIEW'])):
			?>
				<div class="wiki-prereview-header">
					<div class="wiki-prereview-header-title"><span><?=GetMessage('WIKI_PREVIEW_TITLE')?></span></div>
				</div>
				<div class="wiki-prereview-post-content">
					<div class="wiki-prereview-post-text"><?=$arResult['ELEMENT_PREVIEW']['DETAIL_TEXT']?></div>
				</div>

			<?endif;?>
			<form action="<?=$arResult['PATH_TO_POST_EDIT_SUBMIT']?>" name="REPLIER" method="post" >
			<?=bitrix_sessid_post();?>
			<div class="wiki-post-fields">

			<?
			if (!$arResult["IS_CATEGORY_PAGE"]):
			?>
				<div class="wiki-post-header">
					<?=GetMessage('WIKI_NAME')?><font color="#ff0000">*</font>
				</div>
				<div class="wiki-post-area">
					<input maxlength="255" size="70" tabindex="1" type="text" name="POST_TITLE" id="POST_TITLE" value="<?=htmlspecialcharsbx($arResult['ELEMENT']['NAME_LOCALIZE'], ENT_QUOTES)?>"/>
				</div>
			<?endif?>

				<div class="wiki-post-header"><?=GetMessage('WIKI_PAGE_TEXT')?><font color="#ff0000">*</font></div>

				<div class="wiki-post-area" id="wiki-editor">
					<div class="wiki-post-textarea">
						<?
						if($arResult['ALLOW_HTML'] == 'Y'):
						?>
							<input type="radio"
								   id="wki-text-text"
								   name="POST_MESSAGE_TYPE"
								   value="text"<?if($arResult['ELEMENT']['DETAIL_TEXT_TYPE'] != 'html') echo " checked";?>
							/>
							<label for="wki-text-text"><?=GetMessage('WIKI_TEXT_TEXT')?></label>
							<input type="radio"
								   id="wki-text-html"
								   name="POST_MESSAGE_TYPE"
								   value="html"<?if($arResult['ELEMENT']['DETAIL_TEXT_TYPE'] == 'html') echo " checked";?>
							/>
							<label for="wki-text-html"><?=GetMessage('WIKI_TEXT_HTML')?></label>
							<div id="edit-post-html" style="display:none;">
								<?include($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/lhe_custom.php')?>
							</div>
						<?
						endif;
						?>
						<div id="edit-post-text"  style="display:none;">
							<div class="wiki-post-wcode-line">
								<div class="wiki-wcode-line">
									<a id="bold" class="wiki-wcode-bold" title="<?=GetMessage('WIKI_BUTTON_BOLD')?>"></a>
									<a id="italic" class="wiki-wcode-italic" title="<?=GetMessage('WIKI_BUTTON_ITALIC')?>"></a>
									<a id="wheader" class="wiki-wcode-wheader" title="<?=GetMessage('WIKI_BUTTON_HEADER')?>"></a>
									<a id="category" class="wiki-wcode-category" title="<?=GetMessage('WIKI_BUTTON_CATEGORY')?>"></a>
									<a id="url" class="wiki-wcode-url" title="<?=GetMessage('WIKI_BUTTON_HYPERLINK')?>"></a>
									<a id="signature" class="wiki-wcode-signature" title="<?=GetMessage('WIKI_BUTTON_SIGNATURE')?>"></a>
									<a id="line" class="wiki-wcode-line" title="<?=GetMessage('WIKI_BUTTON_LINE')?>"></a>
									<a id="ignore" class="wiki-wcode-ignore" title="<?=GetMessage('WIKI_BUTTON_NOWIKI')?>"></a>
									<a id="url" class="wiki-wcode-external-url" title="<?=GetMessage('WIKI_BUTTON_EXTERNAL_HYPERLINK')?>"></a>
									<a id="image" class="wiki-wcode-img" title="<?=GetMessage('WIKI_BUTTON_IMAGE_LINK')?>"></a>
									<a id="image-upload" class="wiki-wcode-img-upload" title="<?=GetMessage('WIKI_BUTTON_IMAGE_UPLOAD')?>"></a>
									<a id="wiki-code" class="wiki-wcode-code" title="<?=GetMessage('WIKI_BUTTON_INSERT_CODE')?>"></a>
									<div class="wiki-clear-float"></div>
								</div>
								<div class="wiki-clear-float"></div>
							</div>
							<div class="wiki-comment-field wiki-comment-field-text">
								<textarea cols="55" rows="15" tabindex="2" name="POST_MESSAGE" id="MESSAGE"><?=htmlspecialcharsbx($arResult["ELEMENT"]["~DETAIL_TEXT"], ENT_QUOTES)?></textarea>
							</div>
						</div>
						<?
						if($arResult['ALLOW_HTML'] == 'Y')
						{
							$APPLICATION->AddHeadScript("/bitrix/js/main/ajax.js");
							$APPLICATION->AddHeadScript("/bitrix/js/main/admin_tools.js");
							$APPLICATION->AddHeadScript("/bitrix/js/main/utils.js");
							$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/pubstyles.css');
							$APPLICATION->SetAdditionalCSS('/bitrix/admin/htmleditor2/editor.css');
							$APPLICATION->SetTemplateCSS('ajax/ajax.css');
						}
						?>
					</div>
					<div class="wiki-post-image" id="wiki-post-image">
						<?
						if (!empty($arResult['IMAGES'])):
							?>
						<div><?=GetMessage('WIKI_IMAGES')?></div>
						<?
						foreach($arResult['IMAGES'] as $aImg)
						{
							?>
							<div class="wiki-post-image-item">
								<div class="wiki-post-image-item-border"><?=$aImg['FILE_SHOW']?></div>
								<div>
									<input type="checkbox" name="IMAGE_ID_del[<?=$aImg['ID']?>]" id="img_del_<?=$aImg['ID']?>"/>
									<label for="img_del_<?=$aImg['ID']?>"><?=GetMessage('WIKI_IMAGE_DELETE')?></label>
								</div>
							</div>
							<?
						}
					endif;
					?>
					</div>
				</div>
				<div class="wiki-clear-float"></div>
				<div class="wiki-post-area" style="height:1em;">
					<div class="wiki-post-div-animate wiki-post-div-show">
						<a class="wiki-post-link-dashed" onclick="return wikiMainEditor.replaceLinkByInput(this,'wiki-input-comments');" href="#" title="<?=GetMessage('WIKI_ADD_MODIFY_COMMENT_LINK')?>"><?=GetMessage('WIKI_ADD_MODIFY_COMMENT_LINK')?></a>
					</div>
					<div class="wiki-post-div-animate wiki-post-div-hide wiki-post-div-nonedisplay" id="wiki-input-comments">
						<label for="MODIFY_COMMENT"><?=GetMessage('WIKI_ADD_MODIFY_COMMENT_INPUT')?></label><br>
						<input type="text" class="wiki-input" id="MODIFY_COMMENT" tabindex="3" name="MODIFY_COMMENT" size="30" value="<?=$arResult['ELEMENT']['~MODIFY_COMMENT']?>" />
					</div>
				</div>
				<div class="wiki-post-area" style="height:1em;">
					<div class="wiki-post-div-animate wiki-post-div-show">
						<?=GetMessage('WIKI_TAGS').":"?>&nbsp;&nbsp;
						<?=CWikiUtils::GetTagsAsLinks($arResult['ELEMENT']['_TAGS'])?>
						<a class="wiki-post-link-dashed" onclick="return wikiMainEditor.replaceLinkByInput(this,'wiki-input-tags');" href="#" title="<?=GetMessage('WIKI_ADD_TAGS_LINK_TITLE')?>"><?=GetMessage('WIKI_ADD_TAGS_LINK')?></a>
					</div>
					<div class="wiki-post-div-animate wiki-post-div-hide wiki-post-div-nonedisplay" id="wiki-input-tags">
						<label for="TAGS"><?=GetMessage('WIKI_TAGS')?></label><br>
						<?
						if(IsModuleInstalled('search')):
							$arSParams = Array(
								'NAME'	=>	'TAGS',
								'VALUE'	=>	$arResult['ELEMENT']['~TAGS'],
								'arrFILTER'	=>	'wiki',
								'PAGE_ELEMENTS'	=>	'10',
								'SORT_BY_CNT'	=>	'Y',
								'TEXT' => 'size="30" tabindex="4"'
							);

							$APPLICATION->IncludeComponent('bitrix:search.tags.input', '.default', $arSParams);
						else:
							?><input type="text" class="wiki-input" id="TAGS" tabindex="4" name="TAGS" size="30" value="<?=$arResult['ELEMENT']['~TAGS']?>"/><?
						endif?>
					</div>
				</div>
				<div class="wiki-post-buttons wiki-edit-buttons">
					<? if(!$arResult["IS_CATEGORY_PAGE"] && ($arResult['SOCNET'] && ($arResult['WIKI_oper'] == 'edit' || $arResult['WIKI_oper'] == 'add'))):?>
						<label><input type="checkbox" id="cb_post_to_feed" <?=($arResult['POST_TO_FEED'] == "Y" ? "checked" : "")?> onclick="wikiMainEditor.wikiPostToFeedTogle();") title="<?=GetMessage('WIKI_POST_TO_FEED_CB_TITLE')?>"/><?=GetMessage('WIKI_POST_TO_FEED_CB')?></label><br><br>
					<?endif;?>
					<input type="hidden" name="<?=$arResult['PAGE_VAR']?>" value="<?=htmlspecialcharsbx($arResult['ELEMENT']['NAME'],ENT_QUOTES)?>"/>
					<input type="hidden" name="<?=htmlspecialcharsbx($arResult['OPER_VAR'],ENT_QUOTES)?>" value="<?=htmlspecialcharsbx($arResult['WIKI_oper'],ENT_QUOTES)?>"/>
					<input type="hidden" name="save" value="Y"/>
					<input type="hidden" name="post_to_feed" id="post_to_feed" value="<?=htmlspecialcharsbx($arResult['POST_TO_FEED'])?>">
					<input tabindex="5" type="submit" name="save" value="<?=GetMessage($arResult['WIKI_oper'] == 'add' || $arResult['WIKI_oper'] == 'edit' ? 'WIKI_PUBLISH' : 'WIKI_SAVE')?>"/>
					<? if ($arResult['WIKI_oper'] == 'edit' || $arResult['WIKI_oper'] == 'add'): ?>
						<input type="submit" name="apply" value="<?=GetMessage('WIKI_APPLY')?>"/>
						<input type="submit" name="preview" value="<?=GetMessage('WIKI_PREVIEW')?>"/>
					<? endif; ?>
				</div>
			</div>
			</form>
			<div class="wiki-post-note">
			<?
			if ($arResult['WIKI_oper'] != 'delete')
				echo GetMessage('WIKI_REQUIED_FIELDS_NOTE')
			?>
			</div>
		</div>
	</div>
	<script>
		wikiMainEditor.init();
	</script>
	<?

endif;
?>
