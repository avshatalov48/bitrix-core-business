<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach($arResult["CommentsResult"] as $key=>$arPost)
{
	if($key==0) continue;
	foreach($arPost as $Post)
		$arResult["CommentsResult"][0][] = $Post;

	unset($arResult["CommentsResult"][$key]);
}

if(!function_exists('offical_answer_blog_cmp')):
function offical_answer_blog_cmp($a, $b)
{
	if ($a == $b)
	{
		return 0;
	}
	return ($a < $b) ? -1 : 1;
}
endif;

if(is_array($arResult["CommentsResult"][0]))
	usort($arResult["CommentsResult"][0], "offical_answer_blog_cmp");
?>
<div class="blog-comments-official">
<?if(!defined("IDEA_LIST_FORM") && false): /*dbg: wtf?*/?>
<div id="form_comment_" >
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
									<div class="blog-comment-field blog-comment-field-email"><div class="blog-comment-field-text"><label for="">E-mail</label><span class="blog-required-field">*</span></div><span><input maxlength="255" size="30" tabindex="4" type="text" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>"></span></div>
									<div class="blog-clear-float"></div>
							</div>
							<?
					}

					include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/lhe.php");

					if($arResult["use_captcha"]===true)
					{
							?>
							<div class="blog-comment-field blog-comment-field-captcha">
									<div class="blog-comment-field-captcha-label">
											<label for=""><?=GetMessage("B_B_MS_CAPTCHA_SYM")?></label><span class="blog-required-field">*</span><br>
											<input type="hidden" name="captcha_code" id="captcha_code" value="<?=$arResult["CaptchaCode"]?>">
											<input type="text" size="30" name="captcha_word" id="captcha_word" value=""  tabindex="7">
											</div>
									<div class="blog-comment-field-captcha-image"><div id="div_captcha"></div></div>
							</div>
							<?
					}
					?>
					<div class="blog-comment-buttons">
							<input tabindex="10" value="<?=GetMessage("B_B_MS_SEND")?>" type="submit" name="post">
					</div>
		</div>
			</form>
		</div>
	</div>
</div>
<?endif;?>
<?
{
	$prevTab = 0;
		if(!function_exists('ShowComment')):
	function ShowComment($comment, $tabCount=0, $tabSize=2.5, $canModerate=false, $User=Array(), $use_captcha=false, $bCanUserComment=false, $errorComment=false, $arParams = array())
	{
		if($comment["SHOW_AS_HIDDEN"] == "Y" || $comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $comment["SHOW_SCREENNED"] == "Y" || $comment["ID"] == "preview")
		{
			global $prevTab;
			$tabCount = IntVal($tabCount);
			if($tabCount <= 5)
				$paddingSize = 2.5 * $tabCount;
			elseif($tabCount > 5 && $tabCount <= 10)
				$paddingSize = 2.5 * 5 + ($tabCount - 5) * 1.5;
			elseif($tabCount > 10)
				$paddingSize = 2.5 * 5 + 1.5 * 5 + ($tabCount-10) * 1;
				
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
			<div class="blog-comment-line-official"></div>
			<a name="<?=$comment["ID"]?>"></a>
			<div class="blog-comment-<?=$comment["COMMENT_STATUS"]?>">
			<?
			if($comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $comment["SHOW_SCREENNED"] == "Y" || $comment["ID"] == "preview")
			{?>
				<div class="idea-comment-cont">
				<div class="idea-answer">
										<?if ($arParams["SHOW_RATING"] == "Y"):?>
											<div class="blog-post-rating idea-comment-rating-content-<?=$arParams['RATING_TEMPLATE']?>">
											<span class="idea-comment-rating-title"><?=GetMessage("IDEA_RATING_TITLE")?>:</span>
											<?$GLOBALS["APPLICATION"]->IncludeComponent(
													"bitrix:rating.vote", $arParams['RATING_TEMPLATE'],
													Array(
															"ENTITY_TYPE_ID" => "BLOG_COMMENT",
															"ENTITY_ID" => $comment["ID"],
															"OWNER_ID" => $comment["arUser"]["ID"],
															"USER_VOTE" => $arParams["RATING"][$comment["ID"]]["USER_VOTE"],
															"USER_HAS_VOTED" => $arParams["RATING"][$comment["ID"]]["USER_HAS_VOTED"],
															"TOTAL_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_VOTES"],
															"TOTAL_POSITIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_POSITIVE_VOTES"],
															"TOTAL_NEGATIVE_VOTES" => $arParams["RATING"][$comment["ID"]]["TOTAL_NEGATIVE_VOTES"],
															"TOTAL_VALUE" => $arParams["RATING"][$comment["ID"]]["TOTAL_VALUE"]
													),
													null,
													array("HIDE_ICONS" => "Y")
											);?>
											</div>
					<?endif;?>
										<?=GetMessage("IDEA_ANSWER_TITLE")?>
										<img class="idea-user-avatar" src="<?=$comment["AUTHOR_AVATAR"]?>" align="top">
					<?
					if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && (strlen($comment["urlToBlog"]) > 0 || strlen($comment["urlToAuthor"]) > 0) && array_key_exists("ALIAS", $comment["BlogUser"]) && strlen($comment["BlogUser"]["ALIAS"]) > 0)
						$arTmpUser = array(
							"NAME" => "",
							"LAST_NAME" => "",
							"SECOND_NAME" => "",
							"LOGIN" => "",
							"NAME_LIST_FORMATTED" => $comment["BlogUser"]["~ALIAS"],
						);
					elseif (strlen($comment["urlToBlog"]) > 0 || strlen($comment["urlToAuthor"]) > 0)
						$arTmpUser = array(
							"NAME" => $comment["arUser"]["~NAME"],
							"LAST_NAME" => $comment["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $comment["arUser"]["~SECOND_NAME"],
							"LOGIN" => $comment["arUser"]["~LOGIN"],
							"NAME_LIST_FORMATTED" => "",
						);

					if(strlen($comment["urlToBlog"])>0)
					{
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
								//"PROFILE_URL_LIST" => $comment["urlToBlog"],
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
					}
					elseif(strlen($comment["urlToAuthor"])>0)
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
					if(strlen($comment["urlToDelete"])>0 && strlen($comment["AuthorEmail"])>0)
					{
						?>
						(<a href="mailto:<?=$comment["AuthorEmail"]?>"><?=$comment["AuthorEmail"]?></a>)
						<?
					}

					?>
					<?=$comment["DateFormated"]?>
				</div>
				<div class="blog-clear-float"></div>
				<div class="blog-comment-content-official">
					<?if(strlen($comment["TitleFormated"])>0)
					{
						?>
						<b><?=$comment["TitleFormated"]?></b><br />
						<?
					}
					?>
					<?=$comment["TextFormated"]?>

					<div class="blog-post-meta<?if((strlen($comment["urlToShow"])==0 || strlen($comment["urlToHide"])==0) && $comment["CAN_EDIT"] != "Y" && strlen($comment["urlToDelete"])==0):?> blog-post-meta-empty<?endif?>">
					<?
					if(strlen($comment["urlToShow"])>0)
					{
						?>
						<span class="blog-comment-show"><a href="<?=$comment["urlToShow"]."&".bitrix_sessid_get()?>"><?=GetMessage("BPC_MES_SHOW")?></a></span>
						<?
					}
					if(strlen($comment["urlToHide"])>0)
					{
						?>
						<span class="blog-comment-show"><a href="<?=$comment["urlToHide"]."&".bitrix_sessid_get()?>"><?=GetMessage("BPC_MES_HIDE")?></a></span>
						<?
					}
					if($comment["CAN_EDIT"] == "Y")
					{
						$Text = CUtil::JSEscape($comment["~POST_TEXT"]);
						$Title = CUtil::JSEscape($comment["TITLE"]);
						?>
						<script>
						var Text<?=$comment["ID"]?> = '<?=$Text?>';
						var Title<?=$comment["ID"]?> = '<?=$Title?>';
						</script>
						<span class="blog-comment-edit"><a href="javascript:void(0)" onclick="return editComment('<?=$comment["ID"]?>', Title<?=$comment["ID"]?>, Text<?=$comment["ID"]?>)"><?=GetMessage("BPC_MES_EDIT")?></a></span>
						<?
					}
					if(strlen($comment["urlToUnBind"])>0)
					{
						?>
						<span class="blog-comment-unbind"><a href="<?=$comment["urlToUnBind"]."&".bitrix_sessid_get()?>"><?=GetMessage("BPC_MES_UNBIND")?></a></span>
						<?
					}
					if(strlen($comment["urlToDelete"])>0)
					{
						?>
						<span class="blog-comment-delete"><a href="javascript:if(confirm('<?=GetMessage("BPC_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$comment["urlToDelete"]."&".bitrix_sessid_get()?>'"><?=GetMessage("BPC_MES_DELETE")?></a></span>
						<?
					}
					?>
					</div>

				</div>
				</div>
					<div class="blog-clear-float"></div>

				<?
				if(strlen($errorComment) <= 0 && strlen($_POST["preview"]) > 0 && (IntVal($_POST["parentId"]) > 0 || IntVal($_POST["edit_id"]) > 0)
					&& ( (IntVal($_POST["parentId"])==$comment["ID"] && IntVal($_POST["edit_id"]) <= 0)
						|| (IntVal($_POST["edit_id"]) > 0 && IntVal($_POST["edit_id"]) == $comment["ID"] && $comment["CAN_EDIT"] == "Y")))
				{
					$commentPreview = Array(
							"ID" => "preview",
							"TitleFormated" => htmlspecialcharsEx($_POST["subject"]),
							"TextFormated" => $_POST["commentFormated"],
							"AuthorName" => $User["NAME"],
							"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
						);
					ShowComment($commentPreview, (IntVal($_POST["edit_id"]) == $comment["ID"] && $comment["CAN_EDIT"] == "Y") ? $level : ($level+1), 2.5, false, Array(), false, false, false, $arParams);
				}

				if(strlen($errorComment)>0 && $bCanUserComment===true
					&& (IntVal($_POST["parentId"])==$comment["ID"] || IntVal($_POST["edit_id"]) == $comment["ID"]))
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
				<div id="form_comment_<?=$comment['ID']?>" class="idea-comment-block-wrapper"></div>

				<?
				if((strlen($errorComment) > 0 || strlen($_POST["preview"]) > 0)
					&& (IntVal($_POST["parentId"])==$comment["ID"] || IntVal($_POST["edit_id"]) == $comment["ID"])
					&& $bCanUserComment===true)
				{
					$form1 = CUtil::JSEscape($_POST["comment"]);

					$subj = CUtil::JSEscape($_POST["subject"]);
					$user_name = CUtil::JSEscape($_POST["user_name"]);
					$user_email = CUtil::JSEscape($_POST["user_email"]);
					?>
					<script>
					<?
					if(IntVal($_POST["edit_id"]) == $comment["ID"])
					{
						?>editComment('<?=$comment["ID"]?>', '<?=$subj?>', '<?=$form1?>');<?
					}
					else
					{
						?>showComment('<?=$comment["ID"]?>', '<?=$subj?>', 'Y', '<?=$form1?>', '<?=$user_name?>', '<?=$user_email?>');<?
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
		endif;

		if(!function_exists('RecursiveComments')):
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
		endif;
	?>
	<?
	$arParams["RATING"] = $arResult["RATING"];
	RecursiveComments($arResult["CommentsResult"], $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arResult["Comments"], $arParams);
}
?>
</div>