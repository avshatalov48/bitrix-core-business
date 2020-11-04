<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?CUtil::InitJSCore(array("image", "ajax"));?>
<script>
BX.viewImageBind(
	'blg-comment-<?=$arParams["ID"]?>',
	false, 
	{tag:'IMG', attr: 'data-bx-image'}
);

BX.message({'BPC_ERROR_NO_TEXT':'<?=GetMessage("BPC_ERROR_NO_TEXT")?>'});
</script>
<div class="blog-comments" id="blg-comment-<?=$arParams["ID"]?>">
<a name="comments"></a>
<?
if($arResult["is_ajax_post"] != "Y")
{
	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");
	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/scripts_for_editor.php");
}
else
{
	$APPLICATION->RestartBuffer();
	?><script>window.BX = top.BX;
	if(!top.arImages)
		top.arImages = [];
	if(!top.arImagesId)
		top.arImagesId = [];
	<?
	if(!empty($arResult["Images"]))
	{
		foreach($arResult["Images"] as $aImg)
		{
			?>
			top.arImages['<?=$aImg["ID"]?>'] = "<?=CUtil::JSEscape($aImg["SRC"])?>";
			top.arImagesId['<?=$aImg["ID"]?>'] = '<?=$aImg["ID"]?>';
			<?
		}
	}
	?>
	</script><?
	if($arResult["COMMENT_ERROR"] <> '')
	{
		?>
		<script>top.commentEr = 'Y';</script>
		<div class="blog-errors blog-note-box blog-note-error">
			<div class="blog-error-text">
				<?=$arResult["COMMENT_ERROR"]?>
			</div>
		</div>
		<?
	}
}

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
		<div class="blog-error-text" id="blg-com-err">
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
else
{
	if($arResult["is_ajax_post"] != "Y" && $arResult["CanUserComment"])
	{
		?>
		<div id="form_comment_" style="display:none;">
			<div id="form_c_del" style="display:none;">
			<div class="blog-comment-form">

			<form method="POST" name="form_comment" id="<?=$component->createPostFormId()?>" action="<?=POST_FORM_ACTION_URI?>">
			<input type="hidden" name="parentId" id="parentId" value="">
			<input type="hidden" name="edit_id" id="edit_id" value="">
			<input type="hidden" name="act" id="act" value="add">
			<input type="hidden" name="post" value="Y">
			<?=bitrix_sessid_post()?>

			<div class="blog-comment-fields feed-comments-block">
				<?
				if(empty($arResult["User"]))
				{
					?>
					<div class="blog-comment-field blog-comment-field-user">
						<div class="blog-comment-field blog-comment-field-author"><div class="blog-comment-field-text"><label for="user_name"><?=GetMessage("B_B_MS_NAME")?></label><span class="blog-required-field">*</span></div><span><input maxlength="255" size="30" tabindex="3" type="text" name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>"></span></div>
						<div class="blog-comment-field-user-sep">&nbsp;</div>
						<div class="blog-comment-field blog-comment-field-email"><div class="blog-comment-field-text"><label for="">E-mail</label></div><span><input maxlength="255" size="30" tabindex="4" type="text" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>"></span></div>
						<div class="blog-clear-float"></div>
					</div>
					<?
				}
				?>
				<?if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
				{
					?>
					<div class="blog-comment-field blog-comment-field-title">
						<div class="blog-comment-field">
						<div class="blog-comment-field-text"><label for="user_name"><?=GetMessage("BPC_SUBJECT")?></label></div>
						<span><input size="70" type="text" class="blog-comment-field-subject" name="subject" id="subject" value=""></span>
						<div class="blog-clear-float"></div>
						</div>
					</div>
					<?
				}

				echo '<input type="hidden" name="USE_NEW_EDITOR" value="Y">';
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/neweditor.php");

				if($arResult["COMMENT_PROPERTIES"]["SHOW"] == "Y")
				{
					$eventHandlerID = false;
					$eventHandlerID = AddEventHandler('main', 'system.field.edit.file', array('CBlogTools', 'blogUFfileEdit'));
//						todo: we show props here, or can delete this block? maybe use this in edit comment?
					foreach($arResult["COMMENT_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
					{
						if (false)
						{
							?>
							<div
							id="blog-comment-user-fields-<?= $FIELD_NAME ?>"><?= ($FIELD_NAME == CBlogComment::UF_NAME ? "" : $arPostField["EDIT_FORM_LABEL"] . ":") ?>
							<? $APPLICATION->IncludeComponent(
							"bitrix:system.field.edit",
							$arPostField["USER_TYPE"]["USER_TYPE_ID"],
							array("arUserField" => $arPostField), NULL, array("HIDE_ICONS" => "Y")); ?>
							</div><?
						}
					}
					if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
						RemoveEventHandler('main', 'system.field.edit.file', $eventHandlerID);
				}

				if($arResult["NoCommentReason"] <> '')
				{
					?>
					<div id="nocommentreason" style="display:none;"><?=$arResult["NoCommentReason"]?></div>
					<?
				}
				if($arResult["use_captcha"]===true)
				{
					?>
					<div class="blog-comment-field blog-comment-field-captcha">
						<div class="blog-comment-field-captcha-label">
							<label for=""><?=GetMessage("B_B_MS_CAPTCHA_SYM")?></label><span class="blog-required-field">*</span><br>
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
				}
				
				if ($arParams['USER_CONSENT'] == 'Y' && (empty($arResult["User"]) || !$arParams['USER_CONSENT_WAS_GIVEN']))
				{
//						userconsent only for unregistered users or once for registered early
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
				}
				?>
				
				<div class="blog-comment-buttons">
					<input tabindex="10" value="<?=GetMessage("B_B_MS_SEND")?>" type="button" name="sub-post" id="post-button" onclick="submitCommentNew()">
					<a href="javascript:void(0)" onclick="cancelComment();" class="blg-cancel-com"><?=GetMessage("BLOG_PC_COMMENT");?></a>
				</div>
			</div>
			<input type="hidden" name="blog_upload_cid" id="upload-cid" value="">
			</form>
			</div>
		</div>
		</div>

		<?
	}

	$prevTab = 0;
	function ShowComment($comment, $tabCount=0, $tabSize=2.5, $canModerate=false, $User=Array(), $use_captcha=false, $bCanUserComment=false, $errorComment=false, $arParams = array())
	{
		if($comment["SHOW_AS_HIDDEN"] == "Y" || $comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $comment["SHOW_SCREENNED"] == "Y" || $comment["ID"] == "preview")
		{
			global $prevTab;
			$tabCount = intval($tabCount);
			if($tabCount <= 5)
				$paddingSize = 2.5 * $tabCount;
			elseif($tabCount > 5 && $tabCount <= 10)
				$paddingSize = 2.5 * 5 + ($tabCount - 5) * 1.5;
			elseif($tabCount > 10)
				$paddingSize = 2.5 * 5 + 1.5 * 5 + ($tabCount-10) * 1;
			
			if(($tabCount+1) <= 5)
				$paddingSizeNew = 2.5 * ($tabCount+1);
			elseif(($tabCount+1) > 5 && ($tabCount+1) <= 10)
				$paddingSizeNew = 2.5 * 5 + (($tabCount+1) - 5) * 1.5;
			elseif(($tabCount+1) > 10)
				$paddingSizeNew = 2.5 * 5 + 1.5 * 5 + (($tabCount+1)-10) * 1;
			$paddingSizeNew -= $paddingSize;
			
			if($prevTab > $tabCount)
				$prevTab = $tabCount;
			if($prevTab <= 5)
				$prevPaddingSize = 2.5 * $prevTab;
			elseif($prevTab > 5 && $prevTab <= 10)
				$prevPaddingSize = 2.5 * 5 + ($prevTab - 5) * 1.5;
			elseif($prevTab > 10)
				$prevPaddingSize = 2.5 * 5 + 1.5 * 5 + ($prevTab-10) * 1;

				$prevTab = $tabCount;
			?>
			<div class="blog-comment-line" style="margin-left:<?=$prevPaddingSize?>em;"></div>
			<a name="<?=$comment["ID"]?>"></a>
			<div class="blog-comment" style="padding-left:<?=$paddingSize?>em;">
			<div id="blg-comment-<?=$comment["ID"]?>">
			<?
			if($comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $comment["SHOW_SCREENNED"] == "Y" || $comment["ID"] == "preview")
			{
				$aditStyle = "";
				if($arParams["is_ajax_post"] == "Y" || $comment["NEW"] == "Y")
					$aditStyle .= " blog-comment-new";
				if($comment["AuthorIsAdmin"] == "Y")
					$aditStyle = " blog-comment-admin";
				if(intval($comment["AUTHOR_ID"]) > 0)
					$aditStyle .= " blog-comment-user-".intval($comment["AUTHOR_ID"]);
				if($comment["AuthorIsPostAuthor"] == "Y")
					$aditStyle .= " blog-comment-author";
				if($comment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH && $comment["ID"] != "preview")
					$aditStyle .= " blog-comment-hidden";
				if($comment["ID"] == "preview")
					$aditStyle .= " blog-comment-preview";
				?>
				<div class="blog-comment-cont<?=$aditStyle?>">
				<div class="blog-comment-cont-white">
				<div class="blog-comment-info">
					<div class="blog-comment-avatar"><?
					if($comment["AVATAR_img"] <> '')
						echo $comment["AVATAR_img"];
					else
						echo '<img src="/bitrix/components/bitrix/blog/templates/.default/images/noavatar.gif" border="0">';
						?></div>
					<?if ($arParams["SHOW_RATING"] == "Y"):?>
					<div class="blog-post-rating rating_vote_graphic">
					<?
					$GLOBALS["APPLICATION"]->IncludeComponent(
						"bitrix:rating.vote", $arParams["RATING_TYPE"],
						Array(
							"ENTITY_TYPE_ID" => "BLOG_COMMENT",
							"ENTITY_ID" => $comment["ID"],
							"OWNER_ID" => $comment["arUser"]["ID"],
							"USER_VOTE" => $arParams["RATING"][$comment["ID"]]["USER_VOTE"],
							"USER_HAS_VOTED" => $arParams["RATING"][$comment["ID"]]["USER_HAS_VOTED"],
							"TOTAL_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_VOTES"],
							"TOTAL_POSITIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_POSITIVE_VOTES"],
							"TOTAL_NEGATIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_NEGATIVE_VOTES"],
							"TOTAL_VALUE" => $arParams["RATING"][$comment["ID"]]["TOTAL_VALUE"],
							"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
						),
						$arParams["component"],
						array("HIDE_ICONS" => "Y")
					);?>
					</div>
					<?endif;?>
					<?
					if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && ($comment["urlToBlog"] <> '' || $comment["urlToAuthor"] <> '') && array_key_exists("ALIAS", $comment["BlogUser"]) && $comment["BlogUser"]["ALIAS"] <> '')
						$arTmpUser = array(
							"NAME" => "",
							"LAST_NAME" => "",
							"SECOND_NAME" => "",
							"LOGIN" => "",
							"NAME_LIST_FORMATTED" => $comment["BlogUser"]["~ALIAS"],
						);
					elseif ($comment["urlToBlog"] <> '' || $comment["urlToAuthor"] <> '')
						$arTmpUser = array(
							"NAME" => $comment["arUser"]["~NAME"],
							"LAST_NAME" => $comment["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $comment["arUser"]["~SECOND_NAME"],
							"LOGIN" => $comment["arUser"]["~LOGIN"],
							"NAME_LIST_FORMATTED" => "",
						);

					if($comment["urlToBlog"] <> '')
					{
						?>
						<div class="blog-author">
						<?
						
						$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $comment["arUser"]["ID"],
								"HTML_ID" => "blog_post_comment_".$comment["arUser"]["ID"],
								"NAME" => $arTmpUser["NAME"],
								"LAST_NAME" => $arTmpUser["LAST_NAME"],
								"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
								"LOGIN" => $arTmpUser["LOGIN"],
								"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
								"USE_THUMBNAIL_LIST" => "N",
								"PROFILE_URL" => $comment["urlToAuthor"],
								"PROFILE_URL_LIST" => $comment["urlToBlog"],
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								"PATH_TO_SONET_USER_PROFILE" => ($arParams["USE_SOCNET"] == "Y" ? $comment["urlToAuthor"] : $arParams["~PATH_TO_SONET_USER_PROFILE"]),
								"INLINE" => "Y",
								"SEO_USER" => $arParams["SEO_USER"],
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						?>
						</div>
						<?
					}
					elseif($comment["urlToAuthor"] <> '')
					{
						?><div class="blog-author">
						<?if($arParams["SEO_USER"] == "Y"):?>
							<noindex>
						<?endif;?>
						<?
						$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
							'',
							array(
								"ID" => $comment["arUser"]["ID"],
								"HTML_ID" => "blog_post_comment_".$comment["arUser"]["ID"],
								"NAME" => $arTmpUser["NAME"],
								"LAST_NAME" => $arTmpUser["LAST_NAME"],
								"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
								"LOGIN" => $arTmpUser["LOGIN"],
								"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
								"USE_THUMBNAIL_LIST" => "N",
								"PROFILE_URL" => $comment["urlToAuthor"],
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
								"PATH_TO_SONET_USER_PROFILE" => ($arParams["USE_SOCNET"] == "Y" ? $comment["urlToAuthor"] : $arParams["~PATH_TO_SONET_USER_PROFILE"]),
								"INLINE" => "Y",
								"SEO_USER" => $arParams["SEO_USER"],
							),
							false,
							array("HIDE_ICONS" => "Y")
						);
						?>
						<?if($arParams["SEO_USER"] == "Y"):?>
							</noindex>
						<?endif;?>
						</div>
						<?
					}
					else
					{
						?>
						<div class="blog-author"><?=$comment["AuthorName"]?></div>
						<?
					}

					if($comment["urlToDelete"] <> '' && $comment["AuthorEmail"] <> '')
					{
						?>
						(<a href="mailto:<?=$comment["AuthorEmail"]?>"><?=$comment["AuthorEmail"]?></a>)
						<?
					}

					?>
					<div class="blog-comment-date"><?=$comment["DateFormated"]?></div>
				</div>
				<div class="blog-clear-float"></div>
				<div class="blog-comment-content">
					<?if($comment["TitleFormated"] <> '')
					{
						?>
					<div class="blog-comment-content-title">
						<b><?=$comment["TitleFormated"]?></b><br />
					</div>
						<?
					}
					?>
					<div class="blog-comment-content-text"><?=$comment["TextFormated"]?></div>
					<?
					if(!empty($arParams["arImages"][$comment["ID"]]))
					{
						?>
						<div class="feed-com-files">
							<div class="feed-com-files-title"><?=GetMessage("BLOG_PHOTO")?></div>
							<div class="feed-com-files-cont">
								<?
								$arJSFiles = array();
								foreach($arParams["arImages"][$comment["ID"]] as $imageId => $val)
								{
									$val["DEL_URL"] = CUtil::JSEscape($val["DEL_URL"]);
									$arJSFiles[strval($imageId)] = array(
										"element_id" => $imageId,
										"element_name" => $val["ORIGINAL_NAME"],
										"element_size" => $val["FILE_SIZE"],
										"element_url" => $val["small"],
										"element_content_type" => $val["CONTENT_TYPE"],
										"element_thumbnail" => $val["SRC"],
										"element_image" => $val["small"],
										"isImage" => (mb_substr($val["CONTENT_TYPE"], 0, 6) == "image/"),
										"del_url" => $val["DEL_URL"]
									);
									?><span id="wd-doc<?=$imageId?>" class="feed-com-files-photo"><img src="<?=$val["small"]?>" alt="" border="0" data-bx-image="<?=$val["full"]?>"></span><?
								}
								$arJSFiles = CUtil::PhpToJSObject($arJSFiles);
								if($arJSFiles <> ''): ?>
									<script>top.arImages=<?=$arJSFiles?></script>
								<?endif;?>
							</div>
						</div>
						<?
					}
					
					if($comment["COMMENT_PROPERTIES"]["SHOW"] == "Y")
					{
						$eventHandlerID = AddEventHandler('main', 'system.field.view.file', Array('CBlogTools', 'blogUFfileShow'));
						?><div><?
						foreach ($comment["COMMENT_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
						{
							if(!empty($arPostField["VALUE"]))
							{
								$GLOBALS["APPLICATION"]->IncludeComponent(
									"bitrix:system.field.view",
									$arPostField["USER_TYPE"]["USER_TYPE_ID"],
									array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));
							}
						}
						?></div><?
						if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
							RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
					}
					?>
					<div class="blog-comment-meta">
					<?
					if($bCanUserComment===true)
					{
						?>
						<span class="blog-comment-answer"><a href="javascript:void(0)" onclick="return replyCommentNew('<?=$comment["ID"]?>', '<?=$comment["POST_ID"]?>')"><?=GetMessage("B_B_MS_REPLY")?></a></span>
						<span class="blog-vert-separator"></span>
						<?
					}

					if(intval($comment["PARENT_ID"])>0)
					{
						?>
						<span class="blog-comment-parent"><a href="#<?=$comment["PARENT_ID"]?>"><?=GetMessage("B_B_MS_PARENT")?></a></span>
						<span class="blog-vert-separator"></span>
						<?
					}
					?>
					<span class="blog-comment-link"><a href="#<?=$comment["ID"]?>"><?=GetMessage("B_B_MS_LINK")?></a></span>
					<?
					if($comment["CAN_EDIT"] == "Y")
					{
						?>
						<script>
							top.text<?=$comment["ID"]?> = text<?=$comment["ID"]?> = '<?=CUtil::JSEscape($comment["~POST_TEXT"])?>';
							top.title<?=$comment["ID"]?> = title<?=$comment["ID"]?> = '<?=CUtil::JSEscape($comment["TITLE"])?>';
						</script>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-edit"><a href="javascript:void(0)" onclick="return editCommentNew('<?=$comment["ID"]?>', '<?=$comment["POST_ID"]?>')"><?=GetMessage("BPC_MES_EDIT")?></a></span>
						<?
					}
					if($comment["urlToShow"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-show">
							<a href="javascript:void(0)" onclick="return hideShowComment('<?=$comment["urlToShow"]."&".bitrix_sessid_get()?>', '<?=$comment["ID"]?>');" title="<?=GetMessage("BPC_MES_SHOW")?>">
							<?=GetMessage("BPC_MES_SHOW")?></a></span>
						<?
					}
					if($comment["urlToHide"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-show">
							<a href="javascript:void(0)" onclick="return hideShowComment('<?=$comment["urlToHide"]."&".bitrix_sessid_get()?>', '<?=$comment["ID"]?>');" title="<?=GetMessage("BPC_MES_HIDE")?>">
							<?=GetMessage("BPC_MES_HIDE")?></a></span>
						<?
					}
					if($comment["urlToDelete"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-delete">
							<a href="javascript:void(0)" onclick="if(confirm('<?=GetMessage("BPC_MES_DELETE_POST_CONFIRM")?>')) deleteComment('<?=$comment["urlToDelete"]."&".bitrix_sessid_get()?>', '<?=$comment["ID"]?>');" title="<?=GetMessage("BPC_MES_DELETE")?>">
							<?=GetMessage("BPC_MES_DELETE")?></a></span>
						<?
					}
					if($comment["urlToSpam"] <> '')
					{
						?>
						<span class="blog-vert-separator"></span>
						<span class="blog-comment-delete blog-comment-spam"><a href="<?=$comment["urlToSpam"]?>" title="<?=GetMessage("BPC_MES_SPAM_TITLE")?>"><?=GetMessage("BPC_MES_SPAM")?></a></span>
						<?
					}
					if ($arParams["SHOW_RATING"] == "Y")
					{
						?>
						<span class="rating_vote_text">
						<span class="blog-vert-separator"></span>
						<?$GLOBALS["APPLICATION"]->IncludeComponent(
							"bitrix:rating.vote", $arParams["RATING_TYPE"],
							Array(
								"ENTITY_TYPE_ID" => "BLOG_COMMENT",
								"ENTITY_ID" => $comment["ID"],
								"OWNER_ID" => $comment["arUser"]["ID"],
								"USER_VOTE" => $arParams["RATING"][$comment["ID"]]["USER_VOTE"],
								"USER_HAS_VOTED" => $arParams["RATING"][$comment["ID"]]["USER_HAS_VOTED"],
								"TOTAL_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_VOTES"],
								"TOTAL_POSITIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_POSITIVE_VOTES"],
								"TOTAL_NEGATIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_NEGATIVE_VOTES"],
								"TOTAL_VALUE" => $arParams["RATING"][$comment["ID"]]["TOTAL_VALUE"],
								"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
							),
							$arParams["component"],
							array("HIDE_ICONS" => "Y")
						);?>
						</span>
						<?
					}
					?>
					</div>

				</div> <!--end blog-comment-cont-style-->
				</div> <!--end blog-comment-cont-white-->
				</div> <!--end blog-comment-info-->
					<div class="blog-clear-float"></div>

				<?
				if($errorComment == '' && ($_POST["preview"] <> '' && $_POST["show_preview"] != "N") && (intval($_POST["parentId"]) > 0 || intval($_POST["edit_id"]) > 0)
					&& ( (intval($_POST["parentId"])==$comment["ID"] && intval($_POST["edit_id"]) <= 0)
						|| (intval($_POST["edit_id"]) > 0 && intval($_POST["edit_id"]) == $comment["ID"] && $comment["CAN_EDIT"] == "Y")))
				{
					$commentPreview = Array(
							"ID" => "preview",
							"TitleFormated" => htmlspecialcharsbx($_POST["subject"]),
							"TextFormated" => htmlspecialcharsbx($_POST["commentFormated"]),
							"AuthorName" => htmlspecialcharsbx($User["NAME"]),
							"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
						);
					ShowComment($commentPreview, (intval($_POST["edit_id"]) == $comment["ID"] && $comment["CAN_EDIT"] == "Y") ? $level : ($level+1), 2.5, false, Array(), false, false, false, $arParams);
				}

				if($errorComment <> '' && $bCanUserComment===true
					&& (intval($_POST["parentId"])==$comment["ID"] || intval($_POST["edit_id"]) == $comment["ID"]))
				{
					?>
					<div class="blog-errors blog-note-box blog-note-error">
						<div class="blog-error-text">
							<?=$errorComment?>
						</div>
					</div>
					<?
				}
				?>
				</div>	<!--end blg-comment-ID-->
				<div id="err_comment_<?=$comment['ID']?>"></div>
				<div id="form_comment_<?=$comment['ID']?>"></div>
				<div id="new_comment_cont_<?=$comment['ID']?>" style="padding-left:<?=$paddingSizeNew?>em;"></div>
				<div id="new_comment_<?=$comment['ID']?>" style="display:none;"></div>
<!--					placeholder for past editor					-->
				<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-<?=$comment["ID"]?>-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit" style="display:none;"></div>

				<?
				if(($errorComment <> '' || $_POST["preview"] <> '')
					&& (intval($_POST["parentId"])==$comment["ID"] || intval($_POST["edit_id"]) == $comment["ID"])
					&& $bCanUserComment===true)
				{
//						todo: change showComment
					?>
					<script>
					top.text<?=$comment["ID"]?> = text<?=$comment["ID"]?> = '<?=CUtil::JSEscape($_POST["comment"])?>';
					top.title<?=$comment["ID"]?> = title<?=$comment["ID"]?> = '<?=CUtil::JSEscape($_POST["subject"])?>';
					<?
					if(intval($_POST["edit_id"]) == $comment["ID"])
					{
						?>editCommentNew('<?=$comment["ID"]?>',<?=$arParams["ID"]?>);<?
					}
					else
					{
//							todo: not showComment, use new function
						?>showComment('<?=$comment["ID"]?>', 'Y', '<?=CUtil::JSEscape($_POST["user_name"])?>', '<?=CUtil::JSEscape($_POST["user_email"])?>', 'Y');<?
					}
					?>
					</script>
					<?
				}
			}
			elseif($comment["SHOW_AS_HIDDEN"] == "Y") {?>
				<b><?=GetMessage("BPC_HIDDEN_COMMENT")?></b>
				</div> <!--end blg-comment-ID-->
			<?}?>
			</div>
			<?
		}
	}

	function RecursiveComments($sArray, $key, $level=0, $first=false, $canModerate=false, $User, $use_captcha, $bCanUserComment, $errorComment, $arSumComments, $arParams)
	{
		if(!empty($sArray[$key]))
		{
			foreach($sArray[$key] as $comment)
			{
				if(!empty($arSumComments[$comment["ID"]]))
				{
					$comment["CAN_EDIT"] = $arSumComments[$comment["ID"]]["CAN_EDIT"];
					$comment["SHOW_AS_HIDDEN"] = $arSumComments[$comment["ID"]]["SHOW_AS_HIDDEN"];
					$comment["SHOW_SCREENNED"] = $arSumComments[$comment["ID"]]["SHOW_SCREENNED"];
					$comment["NEW"] = $arSumComments[$comment["ID"]]["NEW"];
				}
				ShowComment($comment, $level, 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);
				if(!empty($sArray[$comment["ID"]]))
				{
					foreach($sArray[$comment["ID"]] as $key1)
					{
						if(!empty($arSumComments[$key1["ID"]]))
						{
							$key1["CAN_EDIT"] = $arSumComments[$key1["ID"]]["CAN_EDIT"];
							$key1["SHOW_AS_HIDDEN"] = $arSumComments[$key1["ID"]]["SHOW_AS_HIDDEN"];
							$key1["SHOW_SCREENNED"] = $arSumComments[$key1["ID"]]["SHOW_SCREENNED"];
							$key1["NEW"] = $arSumComments[$key1["ID"]]["NEW"];
						}
						ShowComment($key1, ($level+1), 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);

						if(!empty($sArray[$key1["ID"]]))
						{
							RecursiveComments($sArray, $key1["ID"], ($level+2), false, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arSumComments, $arParams);
						}
					}
				}
				if($first)
					$level=0;
			}
		}
	}
	?>
	<?
	if($arResult["is_ajax_post"] != "Y")
	{
		if($arResult["CanUserComment"])
		{
			$postTitle = "";
			if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
				$postTitle = "RE: ".CUtil::JSEscape($arResult["Post"]["TITLE"]);
			?>
			<div class="blog-add-comment"><a href="javascript:void(0)" onclick="return editCommentNew('0', <?=$arParams["ID"]?>)"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div>
			<a name="0"></a>
			<?
			if($arResult["COMMENT_ERROR"] <> '' && mb_strlen($_POST["parentId"]) < 2
				&& intval($_POST["parentId"])==0 && intval($_POST["edit_id"]) <= 0)
			{
				?>
				<div class="blog-errors blog-note-box blog-note-error">
					<div class="blog-error-text"><?=$arResult["COMMENT_ERROR"]?></div>
				</div>
				<?
			}
		}

		if($arResult["NEED_NAV"] == "Y")
		{
			$component->printPaging();
		}
		
		if($arResult["CanUserComment"])
		{
			?>
			<div id="form_comment_0">
				<div id="err_comment_0"></div>
				<div id="form_comment_0"></div>
				<div id="new_comment_cont_0"></div>
				<div id="new_comment_0" style="display:none;"></div>
<!--				placeholder for past editor					-->
				<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-0-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit" style="display:none;"></div>

			</div>
			<?
			if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '')
				&& intval($_POST["parentId"]) == 0 && mb_strlen($_POST["parentId"]) < 2 && intval($_POST["edit_id"]) <= 0)
			{
				?>
				<script>
				top.text0 = text0 = '<?=CUtil::JSEscape($_POST["comment"])?>';
				top.title0 = title0 = '<?=CUtil::JSEscape($_POST["subject"])?>';
//					todo: need show comment?
				showComment('0', 'Y', '<?=CUtil::JSEscape($_POST["user_name"])?>', '<?=CUtil::JSEscape($_POST["user_email"])?>', 'Y');
				</script>
				<?
			}
		}
	}

	$arParams["RATING"] = $arResult["RATING"];
	$arParams["component"] = $component;
	$arParams["arImages"] = $arResult["arImages"];
	if($arResult["is_ajax_post"] == "Y")
		$arParams["is_ajax_post"] = "Y";

	if($arResult["is_ajax_post"] != "Y" && $arResult["NEED_NAV"] == "Y")
	{
		$component->printCommentPages();
		
	}
	else
		RecursiveComments($arResult["CommentsResult"], $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arResult["Comments"], $arParams);

	if($arResult["is_ajax_post"] != "Y")
	{
		if($arResult["CanUserComment"] && count($arResult["Comments"])>2)
		{
			if($arResult["COMMENT_ERROR"] <> '' && $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1)
			{
				?>
					<div class="blog-errors blog-note-box blog-note-error">
					<div class="blog-error-text">
						<?=$arResult["COMMENT_ERROR"]?>
					</div>
				</div>
				<?
			}
			?>

			<div id="form_comment_00">
				<div id="err_comment_00"></div>
				<div id="form_comment_00"></div>
				<div id="new_comment_cont_00"></div>
				<div id="new_comment_00" style="display:none;"></div>
<!--				placeholder for past editor					-->
				<div id="record-<?=$arParams["ENTITY_XML_ID"]?>-00-placeholder" class="blog-comment-edit feed-com-add-block blog-post-edit" style="display:none;"></div>

			</div><br />
			
			<?
			if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '')
				&& $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1)
			{
				?>
				<script>
				top.text00 = text00 = '<?=CUtil::JSEscape($_POST["comment"])?>';
				top.title00 = title00 = '<?=CUtil::JSEscape($_POST["subject"])?>';
//					todo: need show comment?
				showComment('00', 'Y', '<?=CUtil::JSEscape($_POST["user_name"])?>', '<?=CUtil::JSEscape($_POST["user_email"])?>', "Y");
				</script>
				<?
			}
		}
		

		if($arResult["NEED_NAV"] == "Y")
		{
			$component->printPaging(false);
		}

		if($arResult["CanUserComment"] && count($arResult["Comments"])>2)
		{
			?><div class="blog-add-comment"><a href="#comments" onclick="return editCommentNew('00', <?=$arParams["ID"]?>)"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div><a name="00"></a><?
		}
	}
}
?>
</div>

<?
//bind entity to new editor js object
echo $component->bindPostToEditorForm($arParams["ENTITY_XML_ID"], null, $arParams);

if($arResult["is_ajax_post"] == "Y")
	die();
?>