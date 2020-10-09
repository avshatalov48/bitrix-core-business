<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;

CUtil::InitJSCore(array("ajax"));

?>
#BLOG_COMMENTS_BEGIN#
<div class="blog-comments">
<?if($arResult["NEED_NAV"] == "Y"):?>
	<input id="bx-blog-com-cur" type="hidden" value="<?= $arResult["PAGE_COUNT"]?>" />
	<a  id="bx-blog-com-show-more-link" class="photo-more-comments" onclick="bcShowMoreCom();" href="javascript:void(0);"><?= GetMessage("SHOW_MORE_COMMENTS")?></a>
<?endif;?>

<a name="comments"></a>
<?
if ($arParams['FETCH_USER_ALIAS'])
{
	$arUserIds = array();
	foreach ($arResult['USER_CACHE'] as $u)
		$arUserIds[] = $u['arUser']['ID'];
	CPGalleryInterface::HandleUserAliases($arUserIds, $arParams['IBLOCK_ID']);
}


if($arResult["is_ajax_post"] != "Y")
	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");
else
{
	$APPLICATION->RestartBuffer();
	?><script>window.BX = top.BX;
		<?
		if($arResult["use_captcha"]===true)
		{
			?>
				BX('captcha').src='/bitrix/tools/captcha.php?captcha_code=' + '<?=$arResult["CaptchaCode"]?>';
				BX('captcha_code').value = '<?=$arResult["CaptchaCode"]?>';
				BX('captcha_word').value = "";
			<?
		}
		?>
		BX.onCustomEvent('onAddNewPhotoBlogComment', [{
			count: '<?= intval($arResult["Post"]["NUM_COMMENTS"])?>',
			editId: '<?= intval($_REQUEST["edit_id"])?>',
			deletedComment: '<?= intval($_REQUEST["delete_comment_id"])?>'
		}]);
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

/*
if(strlen($arResult["MESSAGE"])>0)
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
	<?
}
*/

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
	if($arResult["imageUploadFrame"] == "Y")
	{
		?>
		<script>
			<?if(!empty($arResult["Image"])):?>
				top.bxBlogImageId = top.arImagesId.push('<?=$arResult["Image"]["ID"]?>');
				top.arImages.push('<?=CUtil::JSEscape($arResult["Image"]["SRC"])?>');
				top.bxBlogImageIdWidth = '<?=CUtil::JSEscape($arResult["Image"]["WIDTH"])?>';
			<?elseif($arResult["ERROR_MESSAGE"] <> ''):?>
				top.bxBlogImageError = '<?=CUtil::JSEscape($arResult["ERROR_MESSAGE"])?>';
			<?endif;?>
		</script>
		<?
		die();
	}
	else
	{
		if($arResult["is_ajax_post"] != "Y" && $arResult["CanUserComment"])
		{
			?>
			<div id="form_comment_" style="display:none;">
				<div id="form_c_del" style="display:none;">
				<div class="blog-comment-form">

				<form method="POST" name="form_comment" id="form_comment" action="<?=POST_FORM_ACTION_URI?>">
				<input type="hidden" name="parentId" id="parentId" value="">
				<input type="hidden" name="edit_id" id="edit_id" value="">
				<input type="hidden" name="act" id="act" value="add">
				<input type="hidden" name="post" value="Y">
				<?=bitrix_sessid_post()?>

				<div class="blog-comment-fields">
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

					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/lhe.php");
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
								<label for="captcha_code"><?=GetMessage("B_B_MS_CAPTCHA_SYM")?></label><span class="blog-required-field">*</span><br>
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
					?>

					<div class="blog-comment-buttons">
						<a href="#add-comment" class="photo-comment-add" id="post-button" title="<?= GetMessage("ADD_COMMENT_TITLE")?>" onclick="submitComment(); return false;"><span><?= GetMessage("ADD_COMMENT")?></span><i></i></a>
						<input value="<?=GetMessage("B_B_MS_SEND")?>" type="hidden" name="sub-post" />
					</div>
				</div>
				</form>
				</div>
			</div>
			</div>
			<?
		}

		$prevTab = 0;
		if (!function_exists('ShowComment') && !function_exists('RecursiveComments'))
		{
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
						<div class="blog-comment-cont-white" onmouseout="BX.removeClass(this, 'photo-comment-hover')" onmouseover="BX.addClass(this, 'photo-comment-hover')">
							<?if($bCanUserComment === true):?>
								<script>
								top.title<?=$comment["ID"]?> = title<?=$comment["ID"]?> = '<?=CUtil::JSEscape($comment["CommentTitle"])?>';
								</script>
								<a href="" class="photo-comment-reply"  onclick="return showComment('<?=$comment["ID"]?>', '', '', '')" title="<?=GetMessage("B_B_MS_REPLY")?>"></a>
							<?endif;?>
							<? if($comment["CAN_EDIT"] == "Y"):?>
								<script>
								top.text<?=$comment["ID"]?> = text<?=$comment["ID"]?> = '<?=CUtil::JSEscape($comment["~POST_TEXT"])?>';
								top.title<?=$comment["ID"]?> = title<?=$comment["ID"]?> = '<?=CUtil::JSEscape($comment["TITLE"])?>';
								</script>
								<a href="" class="photo-comment-edit" onclick="return editComment('<?=$comment["ID"]?>')" title="<?=GetMessage("BPC_MES_EDIT")?>"></a>
							<?endif;?>

							<?if($comment["urlToDelete"] !== ''):?>
								<a href="" class="photo-comment-remove" onclick="if(confirm('<?=GetMessage("BPC_MES_DELETE_POST_CONFIRM")?>')) deleteComment('<?=$comment["urlToDelete"]."&".bitrix_sessid_get()?>', '<?=$comment["ID"]?>'); return false;" title="<?=GetMessage("BPC_MES_DELETE")?>"></a>
							<?endif;?>

						<div class="photo-comment-avatar <?if ($comment["AVATAR_img"] == ''){echo 'photo-comment-avatar-none';}?>" >
							<?if ($comment["AVATAR_img"] != ''):?>
								<?= $comment["AVATAR_img"]?>
							<?endif;?>
						</div>

						<div class="blog-comment-info">
							<?
							if ($arParams['FETCH_USER_ALIAS'])
								$comment["urlToAuthor"] = CPGalleryInterface::GetPathWithUserAlias($comment["urlToAuthor"], $comment["arUser"]["ID"], $arParams['IBLOCK_ID']);
							?>
							<?if (intval($comment["arUser"]["ID"]) > 0 && !empty($comment["urlToAuthor"])):?>
							<a class="photo-comment-name" href="<?=$comment["urlToAuthor"]?>"><?= $comment["AuthorName"]?></a>
							<?else:?>
							<span class="photo-comment-name"><?= $comment["AuthorName"]?></span>
							<?endif;?>
							<span class="photo-info-date"><?=$comment["DateFormated"]?></span>
							<?if ($arParams["SHOW_RATING"] == "Y"):?>
							<span class="review-rating rating_vote_text">
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
									"AJAX_MODE" => "Y"
								),
								$arParams["component"],
								array("HIDE_ICONS" => "Y")
							);?>
							</span>
							<?endif;?>
						</div>

						<div class="blog-comment-content">
							<?if($comment["TitleFormated"] <> ''):?>
								<b><?=$comment["TitleFormated"]?></b><br />
							<?endif;?>
							<?=$comment["TextFormated"]?>
						</div>
						</div>
						</div>
							<div class="blog-clear-float"></div>

						<?
						if($errorComment <> '' && $bCanUserComment === true && (intval($_POST["parentId"])==$comment["ID"] || intval($_POST["edit_id"]) == $comment["ID"]))
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
						</div>
						<div id="err_comment_<?=$comment['ID']?>"></div>
						<div id="form_comment_<?=$comment['ID']?>"></div>
						<div id="new_comment_cont_<?=$comment['ID']?>" style="padding-left:<?=$paddingSizeNew?>em;"></div>
						<div id="new_comment_<?=$comment['ID']?>" style="display:none;"></div>
						<?
						if(($errorComment <> '' || $_POST["preview"] <> '')
							&& (intval($_POST["parentId"])==$comment["ID"] || intval($_POST["edit_id"]) == $comment["ID"])
							&& $bCanUserComment===true)
						{
							?>
							<script>
							top.text<?=$comment["ID"]?> = text<?=$comment["ID"]?> = '<?=CUtil::JSEscape($_POST["comment"])?>';
							top.title<?=$comment["ID"]?> = title<?=$comment["ID"]?> = '<?=CUtil::JSEscape($_POST["subject"])?>';
							<?
							if(intval($_POST["edit_id"]) == $comment["ID"])
							{
								?>editComment('<?=$comment["ID"]?>');<?
							}
							else
							{
								?>showComment('<?=$comment["ID"]?>', 'Y', '<?=CUtil::JSEscape($_POST["user_name"])?>', '<?=CUtil::JSEscape($_POST["user_email"])?>', 'Y');<?
							}
							?>
							</script>
							<?
						}
					}
					elseif($comment["SHOW_AS_HIDDEN"] == "Y")
						echo "<b>".GetMessage("BPC_HIDDEN_COMMENT")."</b>";
					?>
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
		}

		if($arResult["is_ajax_post"] != "Y")
		{
			if($arResult["CanUserComment"])
			{
				$postTitle = "";
				if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
					$postTitle = "RE: ".CUtil::JSEscape($arResult["Post"]["TITLE"]);
				?>
				<a class="photo-comments-add" href="#comments" onclick="return showComment('0')"><?=GetMessage("B_B_MS_ADD_COMMENT")?></a>
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

			if($arResult["CanUserComment"])
			{
				?>
				<div id="form_comment_0">
					<div id="err_comment_0"></div>
					<div id="form_comment_0"></div>
					<div id="new_comment_cont_0"></div>
					<div id="new_comment_0" style="display:none;"></div>
				</div>
				<?
				if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '')
					&& intval($_POST["parentId"]) == 0 && mb_strlen($_POST["parentId"]) < 2 && intval($_POST["edit_id"]) <= 0)
				{
					?>
					<script>
					top.text0 = text0 = '<?=CUtil::JSEscape($_POST["comment"])?>';
					top.title0 = title0 = '<?=CUtil::JSEscape($_POST["subject"])?>';
					showComment('0', 'Y', '<?=CUtil::JSEscape($_POST["user_name"])?>', '<?=CUtil::JSEscape($_POST["user_email"])?>', 'Y');
					</script>
					<?
				}
			}
		}

		$arParams["RATING"] = $arResult["RATING"];
		$arParams["component"] = $component;
		if($arResult["is_ajax_post"] == "Y")
			$arParams["is_ajax_post"] = "Y";

		if($arResult["is_ajax_post"] != "Y" && $arResult["NEED_NAV"] == "Y")
		{
			for($i = 1; $i <= $arResult["PAGE_COUNT"]; $i++)
			{
				$tmp = $arResult["CommentsResult"];
				$tmp[0] = $arResult["PagesComment"][$i];
				?>
					<div id="blog-comment-page-<?=$i?>"<?if($arResult["PAGE_COUNT"] != $i) echo "style=\"display:none;\""?>><?RecursiveComments($tmp, $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arResult["Comments"], $arParams);?></div>
				<?
			}
		}
		else
		{
			RecursiveComments($arResult["CommentsResult"], $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arResult["Comments"], $arParams);
		}

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
				</div><br />

				<?
				if(($arResult["COMMENT_ERROR"] <> '' || $_POST["preview"] <> '')
					&& $_POST["parentId"] == "00" && mb_strlen($_POST["parentId"]) > 1)
				{
					?>
					<script>
					top.text00 = text00 = '<?=CUtil::JSEscape($_POST["comment"])?>';
					top.title00 = title00 = '<?=CUtil::JSEscape($_POST["subject"])?>';

					showComment('00', 'Y', '<?=CUtil::JSEscape($_POST["user_name"])?>', '<?=CUtil::JSEscape($_POST["user_email"])?>', "Y");
					</script>
					<?
				}

				if(count($arResult["Comments"]) > 3)
				{
					?><a class="photo-comments-add" href="#comments" onclick="return showComment('00')"><?=GetMessage("B_B_MS_ADD_COMMENT")?></a><?
				}
			}
		}
	}
}
?>
</div>
<?if($arResult["is_ajax_post"] == "Y")
	die();
?>
#BLOG_COMMENTS_END#
