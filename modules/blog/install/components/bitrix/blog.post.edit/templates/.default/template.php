<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/styles/additional.css');
endif;
?>
<div class="blog-post-edit">
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
}
if($arResult["FATAL_MESSAGE"] <> '')
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["FATAL_MESSAGE"]?>
		</div>
	</div>
	<?
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
}
else
{
		if($arResult["preview"] == "Y" && !empty($arResult["PostToShow"])>0)
		{
			echo "<p><b>".GetMessage("BLOG_PREVIEW_TITLE")."</b></p>";
			$className = "blog-post";
			$className .= " blog-post-first";
			$className .= " blog-post-alt";
			$className .= " blog-post-year-".$arResult["postPreview"]["DATE_PUBLISH_Y"];
			$className .= " blog-post-month-".intval($arResult["postPreview"]["DATE_PUBLISH_M"]);
			$className .= " blog-post-day-".intval($arResult["postPreview"]["DATE_PUBLISH_D"]);
			?>
			<div class="<?=$className?>">
				<h2 class="blog-post-title"><span><?=$arResult["postPreview"]["TITLE"]?></span></h2>
				<div class="blog-post-info-back blog-post-info-top">
					<div class="blog-post-info">
						<div class="blog-author"><div class="blog-author-icon"></div><?=$arResult["postPreview"]["AuthorName"]?></div>
						<div class="blog-post-date"><span class="blog-post-day"><?=$arResult["postPreview"]["DATE_PUBLISH_DATE"]?></span><span class="blog-post-time"><?=$arResult["postPreview"]["DATE_PUBLISH_TIME"]?></span><span class="blog-post-date-formated"><?=$arResult["postPreview"]["DATE_PUBLISH_FORMATED"]?></span></div>
					</div>
				</div>
				<div class="blog-post-content">
					<div class="blog-post-avatar"><?=$arResult["postPreview"]["BlogUser"]["AVATAR_img"]?></div>
					<?=$arResult["postPreview"]["textFormated"]?>
					<br clear="all" />
				</div>
				<div class="blog-post-meta">
					<div class="blog-post-info-bottom">
						<div class="blog-post-info">
							<div class="blog-author"><div class="blog-author-icon"></div><?=$arResult["postPreview"]["AuthorName"]?></div>
							<div class="blog-post-date"><span class="blog-post-day"><?=$arResult["postPreview"]["DATE_PUBLISH_DATE"]?></span><span class="blog-post-time"><?=$arResult["postPreview"]["DATE_PUBLISH_TIME"]?></span><span class="blog-post-date-formated"><?=$arResult["postPreview"]["DATE_PUBLISH_FORMATED"]?></span></div>
						</div>
					</div>
					<div class="blog-post-meta-util">
						<span class="blog-post-views-link"><a href=""><span class="blog-post-link-caption"><?=GetMessage("BLOG_VIEWS")?>:</span><span class="blog-post-link-counter">0</span></a></span>
						<span class="blog-post-comments-link"><a href=""><span class="blog-post-link-caption"><?=GetMessage("BLOG_COMMENTS")?>:</span><span class="blog-post-link-counter">0</span></a></span>
					</div>

					<?if(!empty($arResult["postPreview"]["Category"]))
					{
						?>
						<div class="blog-post-tag">
							<span><?=GetMessage("BLOG_BLOG_BLOG_CATEGORY")?></span>
							<?
							$i=0;
							foreach($arResult["postPreview"]["Category"] as $v)
							{
								if($i!=0)
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

		?>
		<form action="<?=POST_FORM_ACTION_URI?>" id=<?=$component->createPostFormId()?> name="REPLIER" method="post" enctype="multipart/form-data">
		<?=bitrix_sessid_post();?>
		<?
		if($arParams["USE_AUTOSAVE"] == "Y")
		{
			$as = new CAutoSave();
			$as->Init(false);
			?>
			<script>
			BX.message({'BLOG_POST_AUTOSAVE':'<?=GetMessage("BLOG_POST_AUTOSAVE")?>'});
			</script>
			<?
		}
		?>
		<div class="blog-edit-form blog-edit-post-form blog-post-edit-form">
		<div class="blog-post-fields blog-edit-fields">
			<div class="blog-post-field blog-post-field-title blog-edit-field blog-edit-field-title">
				<input maxlength="255" size="70" tabindex="1" type="text" name="POST_TITLE" id="POST_TITLE" value="<?=$arResult["PostToShow"]["TITLE"]?>">
			</div>
			<div class="blog-clear-float"></div>	
			<div id="blog-post-autosave-hidden" style="display:none;"></div>
			<?if($arParams["ALLOW_POST_CODE"]):?>
				<?CUtil::InitJSCore(array('translit'));
				$bLinked = $arParams["ID"] <= 0 && $_POST["linked_state"]!=='N';
				
				?>
				<input type="hidden" name="linked_state" id="linked_state" value="<?if($bLinked) echo 'Y'; else echo 'N';?>">
				<script>
				var oldValue = '';
				var linked=<?if($bLinked) echo 'true'; else echo 'false';?>;
				
				function set_linked()
				{
					linked=!linked;
					var code_link = document.getElementById('code_link');
					if(code_link)
					{
						if(linked)
							code_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
						else
							code_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
					}
					var linked_state = document.getElementById('linked_state');
					if(linked_state)
					{
						if(linked)
							linked_state.value='Y';
						else
							linked_state.value='N';
					}
				}

				function transliterate()
				{
					if(linked)
					{
						var from = document.getElementById('POST_TITLE');
						var to = document.getElementById('CODE');
						var toText = document.getElementById('post-code-text');
						if(from && to && oldValue != from.value)
						{
							BX.translit(from.value, {
								'max_len' : 70,
								'change_case' : 'L',
								'replace_space' : '-',
								'replace_other' : '',
								'delete_repeat_replace' : true,
								'use_google' : <?echo $arParams['USE_GOOGLE_CODE'] == 'Y'? 'true': 'false'?>,
								'callback' : function(result){
										if(result.length > 0 && isFinite(result)) result = '_'+result;
										to.value = result;
										toText.innerHTML = result;
										setTimeout('transliterate()', 250);
										}
							});
							oldValue = from.value;
						}
						else
						{
							setTimeout('transliterate()', 250);
						}
					}
					else
					{
						setTimeout('transliterate()', 250);
					}
				}
				
				function changeCode()
				{
					document.getElementById("post-code-text").style.display = "none";
					document.getElementById("post-code-input").style.display = "inline";
				}
				transliterate();
				</script>
				<div class="blog-post-field blog-post-field-code blog-edit-field blog-edit-field-code">
					<label for="CODE" class="blog-edit-field-caption"><?=GetMessage("BLOG_P_CODE")?>:</label> <?=$arResult["PATH_TO_POST1"]?><a href="javascript:changeCode()" title="<?=GetMessage("BLOG_CHANGE_CODE")?>" id="post-code-text"><?=($arResult["PostToShow"]["CODE"] <> '') ? $arResult["PostToShow"]["CODE"] : GetMessage("BLOG_P_CODE");?></a><span id="post-code-input"><input maxlength="255" size="70" tabindex="2" type="text" name="CODE" id="CODE" value="<?=$arResult["PostToShow"]["CODE"]?>"><image id="code_link" title="<?echo GetMessage("BLOG_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" /> </span><?=$arResult["PATH_TO_POST2"]?>
					
				</div>
				<div class="blog-clear-float"></div>
			<?endif;?>
			<div class="blog-post-field blog-post-field-date blog-edit-field blog-edit-field-post-date">
				<span><input type="hidden" id="DATE_PUBLISH_DEF" name="DATE_PUBLISH_DEF" value="<?=$arResult["PostToShow"]["DATE_PUBLISH"];?>">
				<div id="date-publ-text">
					<a href="javascript:changeDate()" title="<?=GetMessage("BLOG_DATE")?>"><?=$arResult["PostToShow"]["DATE_PUBLISH"];?></a>
				</div>
				<div id="date-publ" style="display:none;">
				<?
					$APPLICATION->IncludeComponent(
						'bitrix:main.calendar',
						'',
						array(
							'SHOW_INPUT' => 'Y',
							'FORM_NAME' => 'REPLIER',
							'INPUT_NAME' => 'DATE_PUBLISH',
							'INPUT_VALUE' => $arResult["PostToShow"]["DATE_PUBLISH"],
							'SHOW_TIME' => 'Y'
						),
						null,
						array('HIDE_ICONS' => 'Y')
					);
				?>
				</div></span>
			</div>
			<div class="blog-clear-float"></div>
		</div>

		<div class="blog-post-message blog-edit-editor-area blog-edit-field-text">
			<div class="blog-comment-field blog-comment-field-bbcode">
				<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/neweditor.php"); ?>
				<input type="hidden" name="USE_NEW_EDITOR" value="Y">
				<div style="width:0; height:0; overflow:hidden;"><input type="text" tabindex="3" onFocus="window.oBlogLHE.SetFocus()" name="hidden_focus"></div>
			</div>
			<br />
			<?if($arResult["POST_PROPERTIES"]["SHOW"] == "Y" && !empty($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"])):?>
				<?
				$eventHandlerID = false;
				$eventHandlerID = AddEventHandler('main', 'system.field.edit.file', array('CBlogTools', 'blogUFfileEdit'));
				if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
					RemoveEventHandler('main', 'system.field.edit.file', $eventHandlerID);
				unset($arResult["POST_PROPERTIES"]["DATA"]["UF_BLOG_POST_DOC"]);
				?>
			<?endif;?>

				<?foreach($arResult["Images"] as $image):
					$image["DEL_URL"] = CUtil::JSEscape($image["DEL_URL"]);
					$arJSFiles[strval($image["ID"])] = array(
						"element_id" => $image["ID"],
						"element_name" => $image["FILE_NAME"],
						"element_size" => $image["FILE_SIZE"],
						"element_url" => $image["THUMBNAIL"]["src"],
						"element_content_type" => $image["CONTENT_TYPE"],
						"element_thumbnail" => $image["SRC"],
						"element_image" => $image["THUMBNAIL"]["src"],
						"isImage" => (mb_substr($image["CONTENT_TYPE"], 0, 6) == "image/"),
						"del_url" => $image["DEL_URL"]
					);
				
					$imageTitle = GetMessage("MPF_INSERT_FILE");
					?>
				<span class="feed-add-photo-block" id="wd-doc<?=$image["ID"]?>">
					<span class="feed-add-img-wrap" title="<?=$imageTitle?>">
						<img src="<?=$image["THUMBNAIL"]["src"]?>" border="0" width="90" height="90" />
					</span>
					<span class="feed-add-img-title" title="<?=$imageTitle?>"><?=$image["TITLE"]?></span>
					<span class="feed-add-post-del-but"></span>
				</span>
				<?endforeach?>
				<? $arJSFiles = CUtil::PhpToJSObject($arJSFiles); ?>
				<script>window['<?=$component->createEditorId()?>Files']=<?=$arJSFiles?>;</script>
		</div>
		
		<div class="blog-clear-float"></div>
		<div class="blog-post-field blog-post-field-category blog-edit-field blog-edit-field-tags">
			<div class="blog-post-field-text">
			<label for="TAGS" class="blog-edit-field-caption"><?=GetMessage("BLOG_CATEGORY")?></label>
			</div>
			<span><?
					if(IsModuleInstalled("search"))
					{
						$arSParams = Array(
							"NAME"	=>	"TAGS",
							"VALUE"	=>	$arResult["PostToShow"]["CategoryText"],
							"arrFILTER"	=>	"blog",
							"PAGE_ELEMENTS"	=>	"10",
							"SORT_BY_CNT"	=>	"Y",
							"TEXT" => 'size="30" tabindex="4"'
							);
						$APPLICATION->IncludeComponent("bitrix:search.tags.input", ".default", $arSParams);
					}
					else
					{
						?><input type="text" id="TAGS" tabindex="4" name="TAGS" size="30" value="<?=htmlspecialcharsbx($arResult["PostToShow"]["CategoryText"])?>">
						<?
					}?>
			</span>
		</div>
		<div class="blog-clear-float"></div>
		
		<div class="blog-post-field blog-post-field-enable-comments blog-edit-field">
			<span><input name="ENABLE_COMMENTS" id="ENABLE_COMMENTS" type="checkbox" value="N"<?if($arResult["PostToShow"]["ENABLE_COMMENTS"] == "N") echo " checked"?>></span>
			<div class="blog-post-field-text"><label for="ENABLE_COMMENTS"><?=GetMessage("BLOG_ENABLE_COMMENTS")?></label></div>
		</div>
		<div class="blog-clear-float"></div>
		<div class="blog-post-field blog-post-field-favorite blog-edit-field">
			<span><input name="FAVORITE_SORT" id="FAVORITE_SORT" type="checkbox" value="100"<?if(intval($arResult["PostToShow"]["FAVORITE_SORT"]) > 0) echo " checked"?>></span>
			<div class="blog-post-field-text"><label for="FAVORITE_SORT"><?=GetMessage("BLOG_FAVORITE_SORT")?></label></div>
		</div>
		<div class="blog-clear-float"></div>
		<div class="blog-post-params">
			<?
			function ShowSelectPerms($type, $id, $def, $arr)
			{

				$res = "<select name='perms_".$type."[".$id."]'>";
				while(list(,$key)=each($arr))
					if ($id > 1 || ($type=='p' && $key <= BLOG_PERMS_READ) || ($type=='c' && $key <= BLOG_PERMS_WRITE))
						$res.= "<option value='$key'".($key==$def?' selected':'').">".$GLOBALS["AR_BLOG_PERMS"][$key]."</option>";
				$res.= "</select>";
				return $res;
			}

			?>
			<div class="blog-post-field blog-post-field-access blog-edit-field">
				<div class="blog-post-field-access-title"><?=GetMessage("BLOG_ACCESS")?></div>
				<input name="blog_perms" value="0" onClick="show_special()" id="blog_perms_0" type="radio"<?=$arResult["PostToShow"]["ExtendedPerms"]=="Y" ? "" : " checked"?>> <label for="blog_perms_0"><?=GetMessage("BLOG_DEFAULT_PERMS")?></label>
				<br />
				<input name="blog_perms" value="1" onClick="show_special()" id="blog_perms_1" type="radio"<?=$arResult["PostToShow"]["ExtendedPerms"]=="Y" ? " checked" : ""?>> <label for="blog_perms_1"><?=GetMessage("BLOG_SPECIAL_PERMS")?></label>

				<div id="special_perms"<?=($arResult["PostToShow"]["ExtendedPerms"]=="Y" ? "" : "style=\"display:none;\"")?>>
				<table class="blog-post-perm-table">
					<tr>
						<th><?=GetMessage("BLOG_GROUPS")?></th>
						<th><?=GetMessage("BLOG_POST_MESSAGE")?></th>
						<th><?=GetMessage("BLOG_COMMENTS")?></th>

					</tr>
					<tr>
						<td><?=GetMessage("BLOG_ALL_USERS")?></td>
						<td><?
							if(!empty($arResult["ar_post_everyone_rights"]))
								echo ShowSelectPerms('p', 1, $arResult["PostToShow"]["arUGperms_p"][1], $arResult["ar_post_everyone_rights"]);
							else
								echo ShowSelectPerms('p', 1, $arResult["PostToShow"]["arUGperms_p"][1], $arResult["BLOG_POST_PERMS"]);
						?></td>
						<td><?
							if(!empty($arResult["ar_comment_everyone_rights"]))
								echo ShowSelectPerms('c', 1, $arResult["PostToShow"]["arUGperms_c"][1], $arResult["ar_comment_everyone_rights"]);
							else
								echo ShowSelectPerms('c', 1, $arResult["PostToShow"]["arUGperms_c"][1], $arResult["BLOG_COMMENT_PERMS"]);
						?></td>
					</tr>
					<tr>
						<td><?=GetMessage("BLOG_REG_USERS")?></td>
						<td><?
							if(!empty($arResult["ar_post_auth_user_rights"]))
								echo ShowSelectPerms('p', 2, $arResult["PostToShow"]["arUGperms_p"][2], $arResult["ar_post_auth_user_rights"]);
							else
								echo ShowSelectPerms('p', 2, $arResult["PostToShow"]["arUGperms_p"][2], $arResult["BLOG_POST_PERMS"]);
						?></td>
						<td><?
							if(!empty($arResult["ar_comment_auth_user_rights"]))
								echo ShowSelectPerms('c', 2, $arResult["PostToShow"]["arUGperms_c"][2], $arResult["ar_comment_auth_user_rights"]);
							else
								echo ShowSelectPerms('c', 2, $arResult["PostToShow"]["arUGperms_c"][2], $arResult["BLOG_COMMENT_PERMS"]);
						?></td>

					</tr>


					<?
					foreach($arResult["UserGroups"] as $aUGroup)
					{
						?>
						<tr>
							<td><?=$aUGroup["NAME"]?></td>
							<td><?
								if(!empty($arResult["ar_post_group_user_rights"]))
									echo ShowSelectPerms('p', $aUGroup["ID"], $arResult["PostToShow"]["arUGperms_p"][$aUGroup["ID"]], $arResult["ar_post_group_user_rights"]);
								else
									echo ShowSelectPerms('p', $aUGroup["ID"], $arResult["PostToShow"]["arUGperms_p"][$aUGroup["ID"]], $arResult["BLOG_POST_PERMS"]);
							?></td>
							<td><?
								if(!empty($arResult["ar_comment_group_user_rights"]))
									echo ShowSelectPerms('c', $aUGroup["ID"], $arResult["PostToShow"]["arUGperms_c"][$aUGroup["ID"]], $arResult["ar_comment_group_user_rights"]);
								else
									echo ShowSelectPerms('c', $aUGroup["ID"], $arResult["PostToShow"]["arUGperms_c"][$aUGroup["ID"]], $arResult["BLOG_COMMENT_PERMS"]);
							?></td>

						</tr>
						<?
					}
					?>
				</table>
				</div>
			</div>
			<?
			if(!empty($arResult["avBlog"]) && intval($arParams["ID"]) > 0)
			{
				?>
				<br />
				<div class="blog-post-params">
					<div class="blog-post-field blog-post-field-access blog-edit-field">
						<div class="blog-post-field-access-title"><?=GetMessage("BPET_MOVE")?></div>
						<select name="move2blog">
							<option value=""><?=GetMessage("BPET_MOVE_NO")?></option>
							<?
							foreach($arResult["avBlogCategory"] as $cat => $blogs)
							{
								if($cat == "socnet_groups")
								{
									?><optgroup label="<?=GetMessage("BPET_MOVE_SOCNET_GROUPS")?>"><?
								}
								elseif($cat == "socnet_users")
								{
									?><optgroup label="<?=GetMessage("BPET_MOVE_SOCNET_USERS")?>"><?
								}
								$bF = true;
								foreach($blogs as $blog)
								{
									if($cat != "socnet_users" && $cat != "socnet_groups" && $bF)
									{
										?><optgroup label="<?=$blog["GROUP_NAME"]?>"><?
										$bF = false;
									}
									?><option value="<?=$blog["ID"]?>"<?if($blog["ID"] == $arResult["PostToShow"]["move2blog"]) echo " selected"?>><?=$blog["NAME"]?></option><?
								}
								?></optgroup><?
							}
							?>
						</select>
						<br />
						<input type="checkbox" id="move2blogcopy" name="move2blogcopy" value="Y"<?if($arResult["PostToShow"]["move2blogcopy"] == "Y") echo " checked=\"checked\""?>><label for="move2blogcopy"><?=GetMessage("BPET_MOVE_COPY")?></label>
					</div>
				</div>
				<?
			}
			?>

			<div class="blog-clear-float"></div>
			<?if($arResult["POST_PROPERTIES"]["SHOW"] == "Y"):?>
				<div class="blog-post-field blog-post-field-user-prop blog-edit-field">
					<?foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):
					?>
					<div id="blog-post-user-fields-<?=$FIELD_NAME?>"><?=$arPostField["EDIT_FORM_LABEL"].":"?>
						<?$APPLICATION->IncludeComponent(
							"bitrix:system.field.edit",
							$arPostField["USER_TYPE"]["USER_TYPE_ID"],
							array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
					</div>
					<?endforeach;?>
				</div>
				<div class="blog-clear-float"></div>
			<?endif;?>
		</div>
		<script type="text/javascript">
//			init editor for work with images previews
			BX.BlogPostInit('<?=$component->createPostFormId()?>', {
				editorID : '<?=$component->createEditorId()?>',
				showTitle : true,	//todo: need namana
				autoSave : "<?=$arParams["USE_AUTOSAVE"]?>",
				text : '<?=CUtil::JSEscape(isset($arResult['Post']["~DETAIL_TEXT"]) ? $arResult['Post']["~DETAIL_TEXT"] : "")?>',
				restoreAutosave : <?=(empty($arResult["ERROR_MESSAGE"]) ? 'true' : 'false')?>
			});
		</script>
			
		<?if($arParams["SEO_USE"] == "Y"):
			$bShowSEO = false;
			if(!empty($arResult["PostToShow"]["SEO_TITLE"]) || !empty($arResult["PostToShow"]["SEO_TAGS"]) || !empty($arResult["PostToShow"]["SEO_DESCRIPTION"]))
				$bShowSEO = true;
			?>
			<div class="blog-clear-float"></div>
			<div class="blog-post-field blog-post-field-seo blog-edit-field">
				<div class="blog-post-field-text">
					<label><a id="blog-show-seo" class="blog-edit-field-caption" href="javascript:void(0)" onclick="BX.toggle(BX('blog-show-seo-fields'));"><?=GetMessage("BLOG_SHOW_SEO")?></a></label>
				</div>
				<??>

				<div id="blog-show-seo-fields"<?if(!$bShowSEO):?> style="display:none;"<?endif;?>>
					<div class="blog-post-field-text"><?=GetMessage("BLOG_SHOW_SEO_TITLE");?></div>
					<input type="text" maxlength="255" size="30" name="SEO_TITLE" value="<?=$arResult["PostToShow"]["SEO_TITLE"]?>">
					<div class="blog-clear-float"></div>
					<div class="blog-post-field-text"><?=GetMessage("BLOG_SHOW_SEO_TAGS");?></div>
					<input type="text" maxlength="255" size="30" name="SEO_TAGS" value="<?=$arResult["PostToShow"]["SEO_TAGS"]?>">
					<div class="blog-clear-float"></div>
					<div class="blog-post-field-text"><?=GetMessage("BLOG_SHOW_SEO_DESCRIPTION");?></div>
					<textarea rows="6" name="SEO_DESCRIPTION"><?=$arResult["PostToShow"]["SEO_DESCRIPTION"]?></textarea>
					<div class="blog-clear-float"></div>
				</div>
			</div>
		<?endif;?>
		<?
//		userconsent only for once for registered early users
		if ($arParams['USER_CONSENT'] == 'Y' && !$arParams['USER_CONSENT_WAS_GIVEN'])
		{
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
						'button_caption' => GetMessage("BLOG_PUBLISH"),
						'fields' => array('Alias', 'Personal site', 'Birthday', 'Photo')
					),
				)
			);
		}
		?>
		<div class="blog-post-buttons blog-edit-buttons">
			<input type="hidden" name="save" value="Y">
			<input tabindex="5" type="submit" name="save" value="<?=GetMessage("BLOG_PUBLISH")?>">
			<input type="submit" name="apply" value="<?=GetMessage("BLOG_APPLY")?>">
			<input type="hidden" name="blog_upload_cid" id="upload-cid" value="">
			<?if($arResult["perms"] >= BLOG_PERMS_WRITE):?>
				<input type="submit" name="draft" value="<?=GetMessage("BLOG_TO_DRAFT")?>">
			<?endif;?>
		</div>
		</div>
		</form>

		<script>
		<!--
		document.REPLIER.POST_TITLE.focus();
		//-->
		</script>
		<?
}
?>
</div>